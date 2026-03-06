<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriTeganganModel extends Model
{
    protected $table = 'trn_kategori_tegangan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'tarif',
        'kategori_tegangan',
        'created_by',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getKategoriByTarif(): array
    {
        $sql = "
            SELECT
                src.tarif,
                kt.id,
                kt.kategori_tegangan,
                kt.created_by
            FROM (
                SELECT DISTINCT TRIM(tarif) AS tarif
                FROM trn_tul
                WHERE tarif IS NOT NULL AND TRIM(tarif) <> ''
                UNION
                SELECT DISTINCT TRIM(tarif) AS tarif
                FROM trn_kategori_tegangan
                WHERE tarif IS NOT NULL AND TRIM(tarif) <> ''
            ) src
            LEFT JOIN trn_kategori_tegangan kt
                ON kt.tarif = src.tarif
            ORDER BY src.tarif ASC
        ";

        $rows = $this->db->query($sql)->getResultArray();

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return list<string>
     */
    public function getTarifOptions(): array
    {
        $rows = $this->db->table('trn_tul')
            ->select('TRIM(tarif) AS tarif')
            ->where('tarif IS NOT NULL', null, false)
            ->where('TRIM(tarif) <>', '')
            ->groupBy('TRIM(tarif)')
            ->orderBy('TRIM(tarif)', 'ASC')
            ->get()
            ->getResultArray();

        $options = [];
        foreach ($rows as $row) {
            $tarif = trim((string) ($row['tarif'] ?? ''));
            if ($tarif !== '') {
                $options[] = $tarif;
            }
        }

        return $options;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByTarif(string $tarif): ?array
    {
        $row = $this->where('tarif', $tarif)->first();

        return is_array($row) ? $row : null;
    }
}
