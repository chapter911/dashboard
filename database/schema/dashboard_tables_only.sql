SET NAMES utf8mb4;
SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0;

DROP TABLE IF EXISTS `laporan_harian`;
CREATE TABLE `laporan_harian`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `no_agenda` bigint NULL DEFAULT NULL,
  `unit_upi` int NULL DEFAULT NULL,
  `unit_ap` int NULL DEFAULT NULL,
  `unit_up` int NULL DEFAULT NULL,
  `nomor_pdl` int NULL DEFAULT NULL,
  `idpel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kddk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_prov` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_kab` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_kec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_kel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tarif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` int NULL DEFAULT NULL,
  `kdpt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kdpt_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jenis_mk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rp_token` bigint NULL DEFAULT NULL,
  `rptotal` bigint NULL DEFAULT NULL,
  `tgl_pengaduan` date NULL DEFAULT NULL,
  `tgl_tindakan_pengaduan` date NULL DEFAULT NULL,
  `tgl_bayar` date NULL DEFAULT NULL,
  `tgl_aktivasi` date NULL DEFAULT NULL,
  `tgl_penangguhan` date NULL DEFAULT NULL,
  `tgl_restitusi` date NULL DEFAULT NULL,
  `tgl_remaja` date NULL DEFAULT NULL,
  `tgl_nyala` date NULL DEFAULT NULL,
  `tgl_batal` date NULL DEFAULT NULL,
  `status_permohonan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `id_ganti_meter` int NULL DEFAULT NULL,
  `alasan_ganti_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `alasan_penangguhan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `keterangan_alasan_penangguhan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `no_meter_baru` bigint NULL DEFAULT NULL,
  `merk_meter_baru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type_meter_baru` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `thtera_meter_baru` int NULL DEFAULT NULL,
  `thbuat_meter_baru` int NULL DEFAULT NULL,
  `no_meter_lama` bigint NULL DEFAULT NULL,
  `merk_meter_lama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type_meter_lama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `thtera_meter_lama` int NULL DEFAULT NULL,
  `thbuat_meter_lama` int NULL DEFAULT NULL,
  `petugas_pengaduan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_tindakan_pengaduan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_aktivasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_penangguhan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_restitusi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_remaja` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `petugas_batal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tgl_rekap` date NULL DEFAULT NULL,
  `kd_pemb_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ct_primer_kwh` bigint NULL DEFAULT NULL,
  `ct_sekunder_kwh` bigint NULL DEFAULT NULL,
  `pt_primer_kwh` bigint NULL DEFAULT NULL,
  `pt_sekunder_kwh` bigint NULL DEFAULT NULL,
  `konstanta_kwh` bigint NULL DEFAULT NULL,
  `fakm_kwh` bigint NULL DEFAULT NULL,
  `type_ct_kwh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ct_primer_kvarh` bigint NULL DEFAULT NULL,
  `ct_sekunder_kvarh` bigint NULL DEFAULT NULL,
  `pt_primer_kvarh` bigint NULL DEFAULT NULL,
  `pt_sekunder_kvarh` bigint NULL DEFAULT NULL,
  `konstanta_kvarh` bigint NULL DEFAULT NULL,
  `fakm_kvarh` bigint NULL DEFAULT NULL,
  `type_ct_kvarh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_laporan_harian_idpel`(`idpel` ASC) USING BTREE,
  INDEX `idx_laporan_harian_tglrekap_unitup`(`tgl_rekap` ASC, `unit_up` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 282290 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `menu_akses`;
CREATE TABLE `menu_akses`  (
  `group_id` int NOT NULL,
  `menu_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fitur_add` bit(1) NULL DEFAULT b'0',
  `fitur_edit` bit(1) NULL DEFAULT b'0',
  `fitur_delete` bit(1) NULL DEFAULT b'0',
  `fitur_export` bit(1) NULL DEFAULT b'0',
  `fitur_import` bit(1) NULL DEFAULT b'0',
  `fitur_approval` bit(1) NULL DEFAULT b'0',
  PRIMARY KEY (`group_id`, `menu_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `menu_lv1`;
CREATE TABLE `menu_lv1`  (
  `id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `old_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ordering` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `menu_lv2`;
CREATE TABLE `menu_lv2`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `header` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ordering` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `menu_lv3`;
CREATE TABLE `menu_lv3`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `header` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ordering` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_bulan`;
CREATE TABLE `mst_bulan`  (
  `bulan` int NOT NULL,
  `nama_bulan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `singkatan` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hari` int NULL DEFAULT NULL,
  PRIMARY KEY (`bulan`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_daya`;
CREATE TABLE `mst_daya`  (
  `daya` int UNSIGNED NOT NULL,
  `jenis` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`daya`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_jabatan`;
CREATE TABLE `mst_jabatan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `deskripsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_unit`;
CREATE TABLE `mst_unit`  (
  `unit_id` int NULL DEFAULT NULL,
  `unit_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit_singkatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_active` tinyint NULL DEFAULT 1,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` date NULL DEFAULT current_timestamp,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_date` date NULL DEFAULT NULL,
  `urutan` int NULL DEFAULT NULL,
  INDEX `idx_mst_unit_unit_id`(`unit_id` ASC) USING BTREE,
  INDEX `idx_mst_unit_urutan`(`urutan` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_user`;
CREATE TABLE `mst_user`  (
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit_id` int NULL DEFAULT NULL,
  `group_id` int NULL DEFAULT NULL,
  `jabatan_id` int NULL DEFAULT NULL,
  `is_active` tinyint NULL DEFAULT 1,
  `web_access` tinyint NULL DEFAULT 1,
  `android_access` tinyint NULL DEFAULT 1,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` datetime NULL DEFAULT current_timestamp,
  PRIMARY KEY (`username`) USING BTREE,
  INDEX `idx_mst_user_unit`(`unit_id` ASC) USING BTREE,
  INDEX `idx_mst_user_group`(`group_id` ASC) USING BTREE,
  INDEX `idx_mst_user_jabatan`(`jabatan_id` ASC) USING BTREE,
  INDEX `idx_mst_user_active_web`(`is_active` ASC, `web_access` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_user_group`;
CREATE TABLE `mst_user_group`  (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_active` bit(1) NOT NULL DEFAULT b'1',
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_date` date NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`group_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_analisa_pembelian`;
CREATE TABLE `trn_analisa_pembelian`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `periode` date NOT NULL,
  `metode` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NOT NULL,
  `urutan` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NOT NULL,
  `hubungan` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NULL DEFAULT NULL,
  `unit_id` int NULL DEFAULT NULL,
  `menteng` bigint NULL DEFAULT NULL,
  `bandengan` bigint NULL DEFAULT NULL,
  `cempaka_putih` bigint NULL DEFAULT NULL,
  `jati_negara` bigint NULL DEFAULT NULL,
  `pondok_kopi` bigint NULL DEFAULT NULL,
  `tanjung_priok` bigint NULL DEFAULT NULL,
  `marunda` bigint NULL DEFAULT NULL,
  `bulungan` bigint NULL DEFAULT NULL,
  `bintaro` bigint NULL DEFAULT NULL,
  `kebun_jeruk` bigint NULL DEFAULT NULL,
  `ciputat` bigint NULL DEFAULT NULL,
  `kramat_jati` bigint NULL DEFAULT NULL,
  `lenteng_agung` bigint NULL DEFAULT NULL,
  `pondok_gede` bigint NULL DEFAULT NULL,
  `ciracas` bigint NULL DEFAULT NULL,
  `cengkareng` bigint NULL DEFAULT NULL,
  `uid` bigint NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_analisa_periode_unit_metode`(`periode` ASC, `unit_id` ASC, `metode` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 732 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_uca1400_ai_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_emin`;
CREATE TABLE `trn_emin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_id` int NULL DEFAULT NULL,
  `periode` date NOT NULL,
  `periode_rekening` date NOT NULL,
  `tarif` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lembar` int NOT NULL,
  `pelanggan` int NOT NULL,
  `emin_awal` decimal(10, 2) NOT NULL,
  `kwh_rill` decimal(10, 2) NOT NULL,
  `emin` decimal(10, 2) NOT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_emin_unit_periode`(`unit_id` ASC, `periode` ASC) USING BTREE,
  INDEX `idx_trn_emin_periode_rekening`(`periode_rekening` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10165 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_hari_kerja`;
CREATE TABLE `trn_hari_kerja`  (
  `tahun` int NOT NULL,
  `bulan` int NOT NULL,
  `hari_kerja` int NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`tahun`, `bulan`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_hitrate`;
CREATE TABLE `trn_hitrate`  (
  `id_p2tl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idpel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tarif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` bigint NULL DEFAULT NULL,
  `gardu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tiang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` double NULL DEFAULT NULL,
  `longitude` double NULL DEFAULT NULL,
  `sesuai_merk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `merk_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `stand_lwbp` bigint NULL DEFAULT NULL,
  `stand_wbp` bigint NULL DEFAULT NULL,
  `stand_kvarh` bigint NULL DEFAULT NULL,
  `kode_pesan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `update_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `peruntukan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `pemutusan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kwh_ts` bigint NULL DEFAULT NULL,
  `waktu_periksa` datetime NULL DEFAULT NULL,
  `regu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `dlpd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sub_dlpd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `material_kwh` bigint NULL DEFAULT NULL,
  `jenis_layanan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jenis_pengukuran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nomor_meter` bigint NULL DEFAULT NULL,
  `tegangan_meter` bigint NULL DEFAULT NULL,
  `arus_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `konstanta_meter` bigint NULL DEFAULT NULL,
  `waktu_meter` datetime NULL DEFAULT NULL,
  `material_mcb` bigint NULL DEFAULT NULL,
  `material_box` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tegangan_r_n` double NULL DEFAULT NULL,
  `tegangan_s_n` bigint NULL DEFAULT NULL,
  `tegangan_t_n` bigint NULL DEFAULT NULL,
  `tegangan_r_s` bigint NULL DEFAULT NULL,
  `tegangan_s_t` bigint NULL DEFAULT NULL,
  `tegangan_t_r` bigint NULL DEFAULT NULL,
  `beban_primer_r` bigint NULL DEFAULT NULL,
  `beban_primer_s` bigint NULL DEFAULT NULL,
  `beban_primer_t` bigint NULL DEFAULT NULL,
  `beban_sekunder_r` bigint NULL DEFAULT NULL,
  `beban_sekunder_s` bigint NULL DEFAULT NULL,
  `beban_sekunder_t` bigint NULL DEFAULT NULL,
  `cos_beban_r` bigint NULL DEFAULT NULL,
  `cos_beban_s` bigint NULL DEFAULT NULL,
  `cos_beban_t` bigint NULL DEFAULT NULL,
  `deviasi` double NULL DEFAULT NULL,
  `arus_ct_primer_r` bigint NULL DEFAULT NULL,
  `arus_ct_primer_s` bigint NULL DEFAULT NULL,
  `arus_ct_primer_t` bigint NULL DEFAULT NULL,
  `arus_ct_sekunder_r` bigint NULL DEFAULT NULL,
  `arus_ct_sekunder_s` bigint NULL DEFAULT NULL,
  `arus_ct_sekunder_t` bigint NULL DEFAULT NULL,
  `rupiah_ts` bigint NULL DEFAULT NULL,
  `rupiah_kwh` bigint NULL DEFAULT NULL,
  `unit_ulp` bigint NULL DEFAULT NULL,
  `status_kwh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nomor_ba` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `material_ctpt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ganti_material` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `durasi_periksa` bigint NULL DEFAULT NULL,
  `trafo_arus_kwh` bigint NULL DEFAULT NULL,
  `trafo_tegangan_kwh` bigint NULL DEFAULT NULL,
  `faktor_kali_kwh` bigint NULL DEFAULT NULL,
  `fx_kwh` bigint NULL DEFAULT NULL,
  `fx_kvarh` bigint NULL DEFAULT NULL,
  `fx_primer` bigint NULL DEFAULT NULL,
  `fx_sekunder` bigint NULL DEFAULT NULL,
  `kva` bigint NULL DEFAULT NULL,
  `n_kwh` double NULL DEFAULT NULL,
  `n_kvarh` bigint NULL DEFAULT NULL,
  `t_kwh` double NULL DEFAULT NULL,
  `t_kvarh` bigint NULL DEFAULT NULL,
  `c_kwh` bigint NULL DEFAULT NULL,
  `c_kvarh` bigint NULL DEFAULT NULL,
  `irt_primer` bigint NULL DEFAULT NULL,
  `irt_sekunder` bigint NULL DEFAULT NULL,
  `cos_irt` bigint NULL DEFAULT NULL,
  `kwh_p1` double NULL DEFAULT NULL,
  `kvarh_p1` bigint NULL DEFAULT NULL,
  `kw_primer` double NULL DEFAULT NULL,
  `faktor_kali_kwh_r` bigint NULL DEFAULT NULL,
  `deviasi_ct_r` bigint NULL DEFAULT NULL,
  `deviasi_ct_s` bigint NULL DEFAULT NULL,
  `deviasi_ct_t` bigint NULL DEFAULT NULL,
  `irt_primer_ct` bigint NULL DEFAULT NULL,
  `irt_sekunder_ct` bigint NULL DEFAULT NULL,
  `faktor_kali_kwh_irt` bigint NULL DEFAULT NULL,
  `deviasi_ct` bigint NULL DEFAULT NULL,
  `tahun_mtr_blm` bigint NULL DEFAULT NULL,
  `nomor_mtr_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kondisi_mtr_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_mtr_sdh` bigint NULL DEFAULT NULL,
  `nomor_mtr_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kondisi_mtr_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_mon_blm` bigint NULL DEFAULT NULL,
  `nomor_mon_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kondisi_mon_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_mon_sdh` bigint NULL DEFAULT NULL,
  `nomor_mon_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kondisi_mon_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_ct_blm` bigint NULL DEFAULT NULL,
  `nomor_ct_blm` bigint NULL DEFAULT NULL,
  `kondisi_ct_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_ct_sdh` bigint NULL DEFAULT NULL,
  `nomor_ct_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kondisi_ct_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_vt_blm` bigint NULL DEFAULT NULL,
  `nomor_vt_blm` bigint NULL DEFAULT NULL,
  `kondisi_vt_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_vt_sdh` bigint NULL DEFAULT NULL,
  `nomor_vt_sdh` bigint NULL DEFAULT NULL,
  `kondisi_vt_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_reley_blm` bigint NULL DEFAULT NULL,
  `nomor_reley_blm` bigint NULL DEFAULT NULL,
  `kondisi_reley_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_reley_sdh` bigint NULL DEFAULT NULL,
  `nomor_reley_sdh` bigint NULL DEFAULT NULL,
  `kondisi_reley_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_pembatas_blm` bigint NULL DEFAULT NULL,
  `nomor_pembatas_blm` bigint NULL DEFAULT NULL,
  `kondisi_pembatas_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_pembatas_sdh` bigint NULL DEFAULT NULL,
  `nomor_pembatas_sdh` bigint NULL DEFAULT NULL,
  `kondisi_pembatas_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_boxapp_blm` bigint NULL DEFAULT NULL,
  `nomor_boxapp_blm` bigint NULL DEFAULT NULL,
  `kondisi_boxapp_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_boxapp_sdh` bigint NULL DEFAULT NULL,
  `nomor_boxapp_sdh` bigint NULL DEFAULT NULL,
  `kondisi_boxapp_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_platapp_blm` bigint NULL DEFAULT NULL,
  `nomor_platapp_blm` bigint NULL DEFAULT NULL,
  `kondisi_platapp_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_platapp_sdh` bigint NULL DEFAULT NULL,
  `nomor_platapp_sdh` bigint NULL DEFAULT NULL,
  `kondisi_platapp_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_boxamr_blm` bigint NULL DEFAULT NULL,
  `nomor_boxamr_blm` bigint NULL DEFAULT NULL,
  `kondisi_boxamr_blm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_boxamr_sdh` bigint NULL DEFAULT NULL,
  `nomor_boxamr_sdh` bigint NULL DEFAULT NULL,
  `kondisi_boxamr_sdh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit_up3` bigint NULL DEFAULT NULL,
  `unit_uid` bigint NULL DEFAULT NULL,
  `nik_pelanggan` bigint NULL DEFAULT NULL,
  `msisdn_pelanggan` bigint NULL DEFAULT NULL,
  `ts_ap2t` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `no_agenda` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tanggal_sph` datetime NULL DEFAULT NULL,
  `tindaklanjut_pemeriksaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_petugas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `uploaded_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `uploaded_date` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id_p2tl`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_kategori_tegangan`;
CREATE TABLE `trn_kategori_tegangan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `tarif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kategori_tegangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 44 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_login`;
CREATE TABLE `trn_login`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_logged_in` bit(1) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_login_username_created`(`username` ASC, `created_date` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 337 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_p2tl`;
CREATE TABLE `trn_p2tl`  (
  `no_agenda` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idpel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kwh` bigint NULL DEFAULT NULL,
  `tagihan_beban` bigint NULL DEFAULT NULL,
  `tagihan_kwh` bigint NULL DEFAULT NULL,
  `tagihan_ts` bigint NULL DEFAULT NULL,
  `materai` bigint NULL DEFAULT NULL,
  `segel` bigint NULL DEFAULT NULL,
  `materia` bigint NULL DEFAULT NULL,
  `rpppj` bigint NULL DEFAULT NULL,
  `rpujl` bigint UNSIGNED NULL DEFAULT NULL,
  `rpppn` bigint NULL DEFAULT NULL,
  `rupiah_total` bigint NULL DEFAULT NULL,
  `rupiah_tunai` bigint NULL DEFAULT NULL,
  `rupiah_angsuran` bigint NULL DEFAULT NULL,
  `tanggal_register` date NULL DEFAULT NULL,
  `nomor_register` bigint NULL DEFAULT NULL,
  `tanggal_sph` date NULL DEFAULT NULL,
  `nomor_sph` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit_id` int NULL DEFAULT NULL,
  `upload_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `upload_date` date NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`no_agenda`) USING BTREE,
  INDEX `idx_trn_p2tl_unit_tanggal`(`unit_id` ASC, `tanggal_register` ASC) USING BTREE,
  INDEX `idx_trn_p2tl_idpel`(`idpel` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_p2tl_analisa`;
CREATE TABLE `trn_p2tl_analisa`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `idpel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarif` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `daya` int NULL DEFAULT NULL,
  `periode` date NOT NULL,
  `pemakaian_kwh` int NULL DEFAULT NULL,
  `unit_id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_idpel_periode_unit`(`idpel` ASC, `periode` ASC, `unit_id` ASC) USING BTREE,
  INDEX `idx_periode`(`periode` ASC) USING BTREE,
  INDEX `idx_tarif_daya`(`tarif` ASC, `daya` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1922185 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_pssd`;
CREATE TABLE `trn_pssd`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `periode` date NULL DEFAULT NULL,
  `unit_id` int NULL DEFAULT NULL,
  `nama_sheet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jenis_peralatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jam_nyala` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jumlah` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `total_kwh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_pssd_unit_periode`(`unit_id` ASC, `periode` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14257 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `mst_data_induk_langganan`;
CREATE TABLE `mst_data_induk_langganan`  (
  `v_bulan_rekap` int NOT NULL,
  `unit_up` int NULL DEFAULT NULL,
  `idpel` bigint NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_pnj` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tarif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` bigint NULL DEFAULT NULL,
  `kdpt_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `thbl_mut` bigint NULL DEFAULT NULL,
  `jenis_mk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jenis_layanan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `frt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kogol` bigint NULL DEFAULT NULL,
  `fkmkwh` bigint NULL DEFAULT NULL,
  `nomor_meter_kwh` bigint NULL DEFAULT NULL,
  `tanggal_pasang_rubah_app` bigint NULL DEFAULT NULL,
  `merk_meter_kwh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type_meter_kwh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun_tera_meter_kwh` int NULL DEFAULT NULL,
  `tahun_buat_meter_kwh` int NULL DEFAULT NULL,
  `nomor_gardu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nomor_jurusan_tiang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_gardu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kapasitas_trafo` bigint NULL DEFAULT NULL,
  `nomor_meter_prepaid` bigint NULL DEFAULT NULL,
  `product` bigint NULL DEFAULT NULL,
  `koordinat_x` float NULL DEFAULT NULL,
  `koordinat_y` float NULL DEFAULT NULL,
  `kdam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `kd_pemb_meter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ket_kdpembmeter` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status_dil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `krn` bigint NULL DEFAULT NULL,
  `vkrn` bigint NULL DEFAULT NULL,
  PRIMARY KEY (`IDPEL`, `v_bulan_rekap`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_susut`;
CREATE TABLE `trn_susut`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `unit_id` int NULL DEFAULT NULL,
  `jenis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bulan` int NULL DEFAULT NULL,
  `tahun` int NULL DEFAULT NULL,
  `susut` double NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_susut_unit_bulan_tahun`(`unit_id` ASC, `bulan` ASC, `tahun` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3488 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_target_laporan`;
CREATE TABLE `trn_target_laporan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tahun` int NULL DEFAULT NULL,
  `target_tua` bigint NULL DEFAULT 0,
  `target_rusak` bigint NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_unit_tahun`(`unit_id` ASC, `tahun` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_target_realisasi`;
CREATE TABLE `trn_target_realisasi`  (
  `unit_id` int NOT NULL,
  `tahun` int NOT NULL,
  `target_tahunan` double NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_date` date NULL DEFAULT current_timestamp,
  `updated_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_date` date NULL DEFAULT NULL,
  PRIMARY KEY (`unit_id`, `tahun`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_target_susut`;
CREATE TABLE `trn_target_susut`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `bulan` int NOT NULL,
  `tahun` int NOT NULL,
  `nilai` double NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `created_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_unit_bulan_tahun`(`unit_id` ASC, `bulan` ASC, `tahun` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2449 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_uca1400_ai_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_tul`;
CREATE TABLE `trn_tul`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `periode` date NOT NULL,
  `tarif` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pelanggan` int NULL DEFAULT NULL,
  `daya` decimal(20, 2) NULL DEFAULT NULL,
  `pemakaian_jumlah` decimal(20, 2) NULL DEFAULT NULL,
  `pemakaian_lwbp` decimal(20, 2) NULL DEFAULT NULL,
  `pemakaian_wbp` decimal(20, 2) NULL DEFAULT NULL,
  `pemakaian_kelebihan_kvarh` decimal(20, 2) NULL DEFAULT NULL,
  `biaya_beban` decimal(20, 2) NULL DEFAULT NULL,
  `biaya_kwh` decimal(20, 2) NULL DEFAULT NULL,
  `biaya_kelebihan_kvarh` decimal(20, 2) NULL DEFAULT NULL,
  `biaya_ttlb` decimal(20, 2) NULL DEFAULT NULL,
  `jumlah` decimal(20, 2) NULL DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trn_tul_unit_periode`(`unit_id` ASC, `periode` ASC) USING BTREE,
  INDEX `idx_trn_tul_tarif`(`tarif` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26310 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_upload_to`;
CREATE TABLE `trn_upload_to`  (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `idpel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tarif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `daya` int NULL DEFAULT NULL,
  `gardu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tiang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jam_nyala` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `jenis_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `latitude` float NULL DEFAULT NULL,
  `longitude` float NULL DEFAULT NULL,
  `subdlpd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit_id` int NULL DEFAULT NULL,
  `created_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_date` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET SQL_NOTES = @OLD_SQL_NOTES;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;

