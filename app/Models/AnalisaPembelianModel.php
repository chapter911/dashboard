<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use RuntimeException;

class AnalisaPembelianModel extends Model
{
    protected $table = 'trn_analisa_pembelian';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'periode',
        'metode',
        'urutan',
        'hubungan',
        'unit_id',
        'menteng',
        'bandengan',
        'cempaka_putih',
        'jati_negara',
        'pondok_kopi',
        'tanjung_priok',
        'marunda',
        'bulungan',
        'bintaro',
        'kebun_jeruk',
        'ciputat',
        'kramat_jati',
        'lenteng_agung',
        'pondok_gede',
        'ciracas',
        'cengkareng',
        'uid',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getDatatableRows(array $filters, int $start, int $length, ?string $search): array
    {
        $builder = $this->buildDatatableBase($filters, $search);
        $builder->orderBy('a.id', 'ASC');

        if ($length > 0) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered(array $filters, ?string $search): int
    {
        return $this->buildDatatableBase($filters, $search)->countAllResults();
    }

    public function countTotal(array $filters): int
    {
        return $this->buildDatatableBase($filters, null)->countAllResults();
    }

    public function replacePeriodData(string $periodDate, array $rows): void
    {
        $this->db->transStart();

        $this->db->table('trn_analisa_pembelian')
            ->where('periode', $periodDate)
            ->delete();

        if ($rows !== []) {
            $this->db->table('trn_analisa_pembelian')->insertBatch($rows, null, 200);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Gagal menyimpan data Analisa Pembelian.');
        }
    }

    public function findById(int $id): ?array
    {
        $row = $this->db->table('trn_analisa_pembelian a')
            ->select('a.*')
            ->where('a.id', $id)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    public function updateById(int $id, array $payload): bool
    {
        return (bool) $this->db->table('trn_analisa_pembelian')
            ->where('id', $id)
            ->update($payload);
    }

    public function deleteById(int $id): bool
    {
        return (bool) $this->db->table('trn_analisa_pembelian')
            ->where('id', $id)
            ->delete();
    }

    private function buildDatatableBase(array $filters, ?string $search): BaseBuilder
    {
        $builder = $this->db->table('trn_analisa_pembelian a')
            ->select('a.*');

        $periodDate = $this->normalizePeriodDate((string) ($filters['periode'] ?? ''));
        if ($periodDate !== null) {
            $builder->where('a.periode', $periodDate);
        }

        $metode = strtolower(trim((string) ($filters['metode'] ?? '')));
        if (in_array($metode, ['penerimaan', 'pengiriman', 'netto'], true)) {
            $builder->where('a.metode', $metode);
        }

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('a.urutan', $search)
                ->orLike('a.hubungan', $search)
                ->orLike('a.created_by', $search)
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
