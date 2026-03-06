<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimizeKategoriTeganganSchema extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_kategori_tegangan')) {
            return;
        }

        // Normalize tarif values before deduplication/indexing.
        $this->db->query("UPDATE trn_kategori_tegangan SET tarif = TRIM(tarif) WHERE tarif IS NOT NULL");

        // Keep the newest row (largest id) when duplicate tarif exists.
        $this->db->query(
            "DELETE old
             FROM trn_kategori_tegangan old
             INNER JOIN trn_kategori_tegangan latest
                ON old.tarif = latest.tarif
               AND old.id < latest.id
             WHERE old.tarif IS NOT NULL
               AND TRIM(old.tarif) <> ''"
        );

        $indexes = $this->db->getIndexData('trn_kategori_tegangan');
        if (! isset($indexes['uq_trn_kategori_tegangan_tarif'])) {
            $this->db->query('CREATE UNIQUE INDEX uq_trn_kategori_tegangan_tarif ON trn_kategori_tegangan (tarif)');
        }
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_kategori_tegangan')) {
            return;
        }

        $indexes = $this->db->getIndexData('trn_kategori_tegangan');
        if (isset($indexes['uq_trn_kategori_tegangan_tarif'])) {
            $this->db->query('DROP INDEX uq_trn_kategori_tegangan_tarif ON trn_kategori_tegangan');
        }
    }
}
