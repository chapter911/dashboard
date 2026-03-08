<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class PelangganMasterModel extends Model
{
    private const TABLE = 'mst_data_induk_langganan';

    protected $table = self::TABLE;
    protected $returnType = 'array';

    public function countAll(): int
    {
        return (int) $this->db->table(self::TABLE)->countAllResults();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db->table(self::TABLE);

        if (($filters['unit'] ?? '*') !== '*') {
            $builder->where('unitup', (int) $filters['unit']);
        }

        if (($filters['bulan'] ?? '*') !== '*') {
            $builder->where('v_bulan_rekap', (int) $filters['bulan']);
        }

        if (! empty($filters['idpel'])) {
            $builder->where('idpel', (int) $filters['idpel']);
        }

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $builder->groupStart()
                ->like('idpel', $search)
                ->orLike('nama', $search)
                ->orLike('nomor_meter_kwh', $search)
                ->groupEnd();
        }

        return $builder->orderBy('v_bulan_rekap', 'DESC')->orderBy('idpel', 'ASC');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getBulanOptions(): array
    {
        return $this->db->table(self::TABLE)
            ->select('v_bulan_rekap')
            ->groupBy('v_bulan_rekap')
            ->orderBy('v_bulan_rekap', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnits(): array
    {
        return $this->db->table('mst_unit')
            ->select('unit_id, unit_name')
            ->orderBy('unit_id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
