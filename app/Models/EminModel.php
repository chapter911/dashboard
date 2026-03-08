<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

class EminModel extends Model
{
    protected $table = 'trn_emin';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'unit_id',
        'periode',
        'periode_rekening',
        'tarif',
        'lembar',
        'pelanggan',
        'emin_awal',
        'kwh_rill',
        'emin',
        'created_by',
        'created_at',
        'updated_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnits(): array
    {
        return $this->db->table('mst_unit')
            ->select('unit_id, unit_name, urutan')
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('unit_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDatatableRows(array $filters, int $start, int $length, ?string $search, ?array $order, bool $isAdmin, ?int $userUnitId): array
    {
        $builder = $this->buildDatatableBase($filters, $search, $isAdmin, $userUnitId);

        // Keep listing stable by DB primary key only, regardless of DataTables requested sort.
        $builder->orderBy('e.id', 'ASC');

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
            ->select('COALESCE(SUM(e.lembar), 0) AS total_lembar', false)
            ->select('COALESCE(SUM(e.pelanggan), 0) AS total_pelanggan', false)
            ->select('COALESCE(SUM(e.emin_awal), 0) AS total_emin_awal', false)
            ->select('COALESCE(SUM(e.kwh_rill), 0) AS total_kwh_rill', false)
            ->select('COALESCE(SUM(e.emin), 0) AS total_emin', false)
            ->where('u.is_active', 1)
            ->orderBy('u.urutan', 'ASC')
            ->orderBy('u.unit_name', 'ASC');

        $joinCondition = 'u.unit_id = e.unit_id';
        $periodDate = $this->normalizePeriodDate($period);
        if ($periodDate !== null) {
            $joinCondition .= ' AND e.periode = ' . $this->db->escape($periodDate);
        }

        $builder->join('trn_emin e', $joinCondition, 'left');

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('u.unit_id', $userUnitId);
        }

        $builder->groupBy('u.unit_id, u.unit_name');

        return $builder->get()->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getDashboardData(int $year, ?int $unitId, bool $isAdmin, ?int $userUnitId): array
    {
        $sql = "SELECT
                    u.unit_id,
                    u.unit_name,
                    u.urutan,
                    e.periode,
                    SUBSTRING_INDEX(e.tarif, ' / ', 1) AS gol_tarif,
                    SUBSTRING_INDEX(e.tarif, ' / ', -1) AS daya,
                    SUM(COALESCE(e.emin, 0)) AS emin
                FROM trn_emin e
                LEFT JOIN mst_unit u ON e.unit_id = u.unit_id
                WHERE YEAR(e.periode) = ?";

        $binds = [$year];

        if ($unitId !== null) {
            $sql .= ' AND e.unit_id = ?';
            $binds[] = $unitId;
        }

        if (! $isAdmin && $userUnitId !== null) {
            $sql .= ' AND e.unit_id = ?';
            $binds[] = $userUnitId;
        }

        $sql .= " GROUP BY
                    u.unit_id,
                    u.unit_name,
                    u.urutan,
                    e.periode,
                    SUBSTRING_INDEX(e.tarif, ' / ', 1),
                    SUBSTRING_INDEX(e.tarif, ' / ', -1)
                  ORDER BY
                    u.urutan ASC,
                    u.unit_name ASC,
                    gol_tarif ASC,
                    daya ASC,
                    e.periode ASC";

        return $this->db->query($sql, $binds)->getResultArray();
    }

    public function replacePeriodUnitData(string $periodDate, string $periodRekeningDate, int $unitId, array $rows): void
    {
        $this->db->transStart();

        $this->db->table('trn_emin')
            ->where('periode', $periodDate)
            ->where('periode_rekening', $periodRekeningDate)
            ->where('unit_id', $unitId)
            ->delete();

        if ($rows !== []) {
            $this->db->table('trn_emin')->insertBatch($rows, null, 200);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Gagal menyimpan data EMIN.');
        }
    }

    public function findByIdScoped(int $id, bool $isAdmin, ?int $userUnitId): ?array
    {
        $builder = $this->db->table('trn_emin e')
            ->select('e.*, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = e.unit_id', 'left')
            ->where('e.id', $id);

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('e.unit_id', $userUnitId);
        }

        $row = $builder->get()->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function updateByIdScoped(int $id, array $payload, bool $isAdmin, ?int $userUnitId): bool
    {
        $builder = $this->db->table('trn_emin')->where('id', $id);
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', $userUnitId);
        }

        return (bool) $builder->update($payload);
    }

    public function deleteByIdScoped(int $id, bool $isAdmin, ?int $userUnitId): bool
    {
        $builder = $this->db->table('trn_emin')->where('id', $id);
        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('unit_id', $userUnitId);
        }

        return (bool) $builder->delete();
    }

    private function buildDatatableBase(array $filters, ?string $search, bool $isAdmin, ?int $userUnitId): BaseBuilder
    {
        $builder = $this->db->table('trn_emin e')
            ->select('e.*, u.unit_name')
            ->join('mst_unit u', 'u.unit_id = e.unit_id', 'left');

        $periodDate = $this->normalizePeriodDate((string) ($filters['periode'] ?? ''));
        if ($periodDate !== null) {
            $builder->where('e.periode', $periodDate);
        }

        $unitId = isset($filters['unit_id']) && $filters['unit_id'] !== '' ? (int) $filters['unit_id'] : null;
        if ($unitId !== null) {
            $builder->where('e.unit_id', $unitId);
        }

        if (! $isAdmin && $userUnitId !== null) {
            $builder->where('e.unit_id', $userUnitId);
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('e.tarif', $search)
                ->orLike('u.unit_name', $search)
                ->orLike('e.created_by', $search)
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
