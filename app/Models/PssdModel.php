<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

class PssdModel extends Model
{
    protected $table = 'trn_pssd';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'periode',
        'unit_id',
        'nama_sheet',
        'jenis_peralatan',
        'daya',
        'jam_nyala',
        'jumlah',
        'total_kwh',
        'created_by',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnits(): array
    {
        return $this->db->table('mst_unit')
            ->select('unit_id, unit_name, unit_singkatan, urutan')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('unit_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDatatableRows(array $filters, int $start, int $length, ?string $search, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->buildDatatableBase($filters, $search, $isAdmin, $userUnitId);

        // Keep listing stable by DB primary key only.
        $builder->orderBy('p.id', 'ASC');

        if ($length > 0) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $filters, ?string $search, bool $isAdmin, ?int $userUnitId): int
    {
        return $this->buildDatatableBase($filters, $search, $isAdmin, $userUnitId)->countAllResults();
    }

    public function countTotalByScope(array $filters, bool $isAdmin, ?int $userUnitId): int
    {
        return $this->buildDatatableBase($filters, null, $isAdmin, $userUnitId)->countAllResults();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getSummaryPerUnit(?string $period, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->db->table('mst_unit u')
            ->select('u.unit_id, u.unit_name')
            ->select("ROUND(COALESCE(SUM(CAST(NULLIF(p.jumlah, '') AS DECIMAL(22,4))), 0), 0) AS total_jumlah", false)
            ->select("ROUND(COALESCE(SUM(CAST(NULLIF(p.total_kwh, '') AS DECIMAL(22,4))), 0), 0) AS total_kwh", false)
            ->where('u.is_active', 1)
            ->orderBy('u.urutan', 'ASC')
            ->orderBy('u.unit_name', 'ASC');

        $joinCondition = 'u.unit_id = p.unit_id';
        $periodDate = $this->normalizePeriodDate($period);
        if ($periodDate !== null) {
            $joinCondition .= ' AND p.periode = ' . $this->db->escape($periodDate);
        }

        $builder->join('trn_pssd p', $joinCondition, 'left');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('u.unit_id', $userUnitId);
        }

        $builder->groupBy('u.unit_id, u.unit_name');

        return $builder->get()->getResultArray();
    }

    public function replacePeriodUnitsData(string $periodDate, array $unitIds, array $rows): void
    {
        if ($unitIds === []) {
            throw new RuntimeException('Unit upload tidak tersedia.');
        }

        $this->db->transStart();

        $this->db->table('trn_pssd')
            ->where('periode', $periodDate)
            ->whereIn('unit_id', $unitIds)
            ->delete();

        if ($rows !== []) {
            $this->db->table('trn_pssd')->insertBatch($rows, null, 200);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Gagal menyimpan data PSSD.');
        }
    }

    public function findByIdScoped(int $id, bool $isAdmin, ?int $userUnitId): ?array
    {
        $builder = $this->db->table('trn_pssd p')
            ->select('p.*, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = p.unit_id', 'left')
            ->where('p.id', $id);

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('p.unit_id', $userUnitId);
        }

        $row = $builder->get()->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function updateByIdScoped(int $id, array $payload, bool $isAdmin, ?int $userUnitId): bool
    {
        $builder = $this->db->table('trn_pssd')->where('id', $id);
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', $userUnitId);
        }

        return (bool) $builder->update($payload);
    }

    public function deleteByIdScoped(int $id, bool $isAdmin, ?int $userUnitId): bool
    {
        $builder = $this->db->table('trn_pssd')->where('id', $id);
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', $userUnitId);
        }

        return (bool) $builder->delete();
    }

    private function buildDatatableBase(array $filters, ?string $search, bool $isAdmin, ?int $userUnitId): BaseBuilder
    {
        $builder = $this->db->table('trn_pssd p')
            ->select('p.*, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = p.unit_id', 'left');

        $periodDate = $this->normalizePeriodDate((string) ($filters['periode'] ?? ''));
        if ($periodDate !== null) {
            $builder->where('p.periode', $periodDate);
        }

        $unitId = isset($filters['unit_id']) && $filters['unit_id'] !== '' ? (int) $filters['unit_id'] : null;
        if ($unitId !== null) {
            $builder->where('p.unit_id', $unitId);
        }

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('p.unit_id', $userUnitId);
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('u.unit_name', $search)
                ->orLike('p.nama_sheet', $search)
                ->orLike('p.jenis_peralatan', $search)
                ->orLike('p.created_by', $search)
                ->groupEnd();
        }

        return $builder;
    }

    private function normalizePeriodDate(?string $period): ?string
    {
        $value = trim((string) $period);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            return $value . '-01';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        return null;
    }
}
