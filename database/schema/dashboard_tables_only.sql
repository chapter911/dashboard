DROP TABLE IF EXISTS `laporan_harian`;
CREATE TABLE `laporan_harian`  (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `NOAGENDA` bigint NULL DEFAULT NULL,
  `UNITUPI` int NULL DEFAULT NULL,
  `UNITAP` int NULL DEFAULT NULL,
  `UNITUP` int NULL DEFAULT NULL,
  `NOMORPDL` int NULL DEFAULT NULL,
  `IDPEL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ALAMAT` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KDDK` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_PROV` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_KAB` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_KEC` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_KEL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TARIF` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DAYA` int NULL DEFAULT NULL,
  `KDPT` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KDPT_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `JENIS_MK` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `RP_TOKEN` bigint NULL DEFAULT NULL,
  `RPTOTAL` bigint NULL DEFAULT NULL,
  `TGLPENGADUAN` date NULL DEFAULT NULL,
  `TGLTINDAKANPENGADUAN` date NULL DEFAULT NULL,
  `TGLBAYAR` date NULL DEFAULT NULL,
  `TGLAKTIVASI` date NULL DEFAULT NULL,
  `TGLPENANGGUHAN` date NULL DEFAULT NULL,
  `TGLRESTITUSI` date NULL DEFAULT NULL,
  `TGLREMAJA` date NULL DEFAULT NULL,
  `TGLNYALA` date NULL DEFAULT NULL,
  `TGLBATAL` date NULL DEFAULT NULL,
  `STATUS_PERMOHONAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ID_GANTI_METER` int NULL DEFAULT NULL,
  `ALASAN_GANTI_METER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ALASAN_PENANGGUHAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KETERANGAN_ALASAN_PENANGGUHAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NO_METER_BARU` bigint NULL DEFAULT NULL,
  `MERK_METER_BARU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TYPE_METER_BARU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `THTERA_METER_BARU` int NULL DEFAULT NULL,
  `THBUAT_METER_BARU` int NULL DEFAULT NULL,
  `NO_METER_LAMA` bigint NULL DEFAULT NULL,
  `MERK_METER_LAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TYPE_METER_LAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `THTERA_METER_LAMA` int NULL DEFAULT NULL,
  `THBUAT_METER_LAMA` int NULL DEFAULT NULL,
  `PETUGASPENGADUAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASTINDAKANPENGADUAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASAKTIVASI` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASPENANGGUHAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASRESTITUSI` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASREMAJA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PETUGASBATAL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TGLREKAP` date NULL DEFAULT NULL,
  `KDPEMBMETER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `CT_PRIMER_KWH` bigint NULL DEFAULT NULL,
  `CT_SEKUNDER_KWH` bigint NULL DEFAULT NULL,
  `PT_PRIMER_KWH` bigint NULL DEFAULT NULL,
  `PT_SEKUNDER_KWH` bigint NULL DEFAULT NULL,
  `KONSTANTA_KWH` bigint NULL DEFAULT NULL,
  `FAKMKWH` bigint NULL DEFAULT NULL,
  `TYPE_CT_KWH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `CT_PRIMER_KVARH` bigint NULL DEFAULT NULL,
  `CT_SEKUNDER_KVARH` bigint NULL DEFAULT NULL,
  `PT_PRIMER_KVARH` bigint NULL DEFAULT NULL,
  `PT_SEKUNDER_KVARH` bigint NULL DEFAULT NULL,
  `KONSTANTA_KVARH` bigint NULL DEFAULT NULL,
  `FAKMKVARH` bigint NULL DEFAULT NULL,
  `TYPE_CT_KVARH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 282290 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `menu_akses`;
CREATE TABLE `menu_akses`  (
  `group_id` int NOT NULL,
  `menu_id` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `FiturAdd` bit(1) NULL DEFAULT b'0',
  `FiturEdit` bit(1) NULL DEFAULT b'0',
  `FiturDelete` bit(1) NULL DEFAULT b'0',
  `FiturExport` bit(1) NULL DEFAULT b'0',
  `FiturImport` bit(1) NULL DEFAULT b'0',
  `FiturApproval` bit(1) NULL DEFAULT b'0',
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
  `urutan` int NULL DEFAULT NULL
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
  PRIMARY KEY (`username`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
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
  `ID_P2TL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `IDPEL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TARIF` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DAYA` bigint NULL DEFAULT NULL,
  `GARDU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TIANG` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `LATITUDE` double NULL DEFAULT NULL,
  `LONGITUDE` double NULL DEFAULT NULL,
  `SESUAI_MERK` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `MERK_METER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `STAND_LWBP` bigint NULL DEFAULT NULL,
  `STAND_WBP` bigint NULL DEFAULT NULL,
  `STAND_KVARH` bigint NULL DEFAULT NULL,
  `KODE_PESAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UPDATE_STATUS` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `PERUNTUKAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `CATATAN` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `PEMUTUSAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KWH_TS` bigint NULL DEFAULT NULL,
  `WAKTU_PERIKSA` datetime NULL DEFAULT NULL,
  `REGU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `SUMBER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DLPD` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `SUB_DLPD` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `MATERIAL_KWH` bigint NULL DEFAULT NULL,
  `JENISLAYANAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `JENISPENGUKURAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NOMOR_METER` bigint NULL DEFAULT NULL,
  `TEGANGAN_METER` bigint NULL DEFAULT NULL,
  `ARUS_METER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONSTANTA_METER` bigint NULL DEFAULT NULL,
  `WAKTU_METER` datetime NULL DEFAULT NULL,
  `MATERIAL_MCB` bigint NULL DEFAULT NULL,
  `MATERIAL_BOX` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TEGANGAN_R_N` double NULL DEFAULT NULL,
  `TEGANGAN_S_N` bigint NULL DEFAULT NULL,
  `TEGANGAN_T_N` bigint NULL DEFAULT NULL,
  `TEGANGAN_R_S` bigint NULL DEFAULT NULL,
  `TEGANGAN_S_T` bigint NULL DEFAULT NULL,
  `TEGANGAN_T_R` bigint NULL DEFAULT NULL,
  `BEBAN_PRIMER_R` bigint NULL DEFAULT NULL,
  `BEBAN_PRIMER_S` bigint NULL DEFAULT NULL,
  `BEBAN_PRIMER_T` bigint NULL DEFAULT NULL,
  `BEBAN_SEKUNDER_R` bigint NULL DEFAULT NULL,
  `BEBAN_SEKUNDER_S` bigint NULL DEFAULT NULL,
  `BEBAN_SEKUNDER_T` bigint NULL DEFAULT NULL,
  `COS_BEBAN_R` bigint NULL DEFAULT NULL,
  `COS_BEBAN_S` bigint NULL DEFAULT NULL,
  `COS_BEBAN_T` bigint NULL DEFAULT NULL,
  `DEVIASI` double NULL DEFAULT NULL,
  `ARUS_CT_PRIMER_R` bigint NULL DEFAULT NULL,
  `ARUS_CT_PRIMER_S` bigint NULL DEFAULT NULL,
  `ARUS_CT_PRIMER_T` bigint NULL DEFAULT NULL,
  `ARUS_CT_SEKUNDER_R` bigint NULL DEFAULT NULL,
  `ARUS_CT_SEKUNDER_S` bigint NULL DEFAULT NULL,
  `ARUS_CT_SEKUNDER_T` bigint NULL DEFAULT NULL,
  `RUPIAH_TS` bigint NULL DEFAULT NULL,
  `RUPIAH_KWH` bigint NULL DEFAULT NULL,
  `UNIT_ULP` bigint NULL DEFAULT NULL,
  `STATUS_KWH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NOMOR_BA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `MATERIAL_CTPT` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `GANTI_MATERIAL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DURASI_PERIKSA` bigint NULL DEFAULT NULL,
  `TRAFO_ARUS_KWH` bigint NULL DEFAULT NULL,
  `TRAFO_TEGANGAN_KWH` bigint NULL DEFAULT NULL,
  `FAKTOR_KALI_KWH` bigint NULL DEFAULT NULL,
  `FX_KWH` bigint NULL DEFAULT NULL,
  `FX_KVARH` bigint NULL DEFAULT NULL,
  `FX_PRIMER` bigint NULL DEFAULT NULL,
  `FX_SEKUNDER` bigint NULL DEFAULT NULL,
  `KVA` bigint NULL DEFAULT NULL,
  `N_KWH` double NULL DEFAULT NULL,
  `N_KVARH` bigint NULL DEFAULT NULL,
  `T_KWH` double NULL DEFAULT NULL,
  `T_KVARH` bigint NULL DEFAULT NULL,
  `C_KWH` bigint NULL DEFAULT NULL,
  `C_KVARH` bigint NULL DEFAULT NULL,
  `IRT_PRIMER` bigint NULL DEFAULT NULL,
  `IRT_SEKUNDER` bigint NULL DEFAULT NULL,
  `COS_IRT` bigint NULL DEFAULT NULL,
  `KWH_P1` double NULL DEFAULT NULL,
  `KVARH_P1` bigint NULL DEFAULT NULL,
  `KW_PRIMER` double NULL DEFAULT NULL,
  `FAKTOR_KALI_KWH_R` bigint NULL DEFAULT NULL,
  `DEVIASI_CT_R` bigint NULL DEFAULT NULL,
  `DEVIASI_CT_S` bigint NULL DEFAULT NULL,
  `DEVIASI_CT_T` bigint NULL DEFAULT NULL,
  `IRT_PRIMER_CT` bigint NULL DEFAULT NULL,
  `IRT_SEKUNDER_CT` bigint NULL DEFAULT NULL,
  `FAKTOR_KALI_KWH_IRT` bigint NULL DEFAULT NULL,
  `DEVIASI_CT` bigint NULL DEFAULT NULL,
  `TAHUN_MTR_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_MTR_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONDISI_MTR_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_MTR_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_MTR_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONDISI_MTR_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_MON_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_MON_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONDISI_MON_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_MON_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_MON_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONDISI_MON_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_CT_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_CT_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_CT_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_CT_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_CT_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KONDISI_CT_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_VT_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_VT_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_VT_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_VT_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_VT_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_VT_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_RELEY_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_RELEY_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_RELEY_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_RELEY_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_RELEY_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_RELEY_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_PEMBATAS_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_PEMBATAS_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_PEMBATAS_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_PEMBATAS_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_PEMBATAS_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_PEMBATAS_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_BOXAPP_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_BOXAPP_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_BOXAPP_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_BOXAPP_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_BOXAPP_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_BOXAPP_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_PLATAPP_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_PLATAPP_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_PLATAPP_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_PLATAPP_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_PLATAPP_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_PLATAPP_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_BOXAMR_BLM` bigint NULL DEFAULT NULL,
  `NOMOR_BOXAMR_BLM` bigint NULL DEFAULT NULL,
  `KONDISI_BOXAMR_BLM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_BOXAMR_SDH` bigint NULL DEFAULT NULL,
  `NOMOR_BOXAMR_SDH` bigint NULL DEFAULT NULL,
  `KONDISI_BOXAMR_SDH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UNIT_UP3` bigint NULL DEFAULT NULL,
  `UNIT_UID` bigint NULL DEFAULT NULL,
  `NIK_PELANGGAN` bigint NULL DEFAULT NULL,
  `MSISDN_PELANGGAN` bigint NULL DEFAULT NULL,
  `TS_AP2T` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NO_AGENDA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TANGGAL_SPH` datetime NULL DEFAULT NULL,
  `TINDAKLANJUT_PEMERIKSAAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `USERNAME` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_PETUGAS` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UPLOADED_BY` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UPLOADED_DATE` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`ID_P2TL`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 337 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_p2tl`;
CREATE TABLE `trn_p2tl`  (
  `NOAGENDA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `IDPEL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `GOL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ALAMAT` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DAYA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KWH` bigint NULL DEFAULT NULL,
  `TAGIHAN_BEBAN` bigint NULL DEFAULT NULL,
  `TAGIHAN_KWH` bigint NULL DEFAULT NULL,
  `TAGIHAN_TS` bigint NULL DEFAULT NULL,
  `MATERAI` bigint NULL DEFAULT NULL,
  `SEGEL` bigint NULL DEFAULT NULL,
  `MATERIA` bigint NULL DEFAULT NULL,
  `RPPPJ` bigint NULL DEFAULT NULL,
  `RPUJL` bigint UNSIGNED NULL DEFAULT NULL,
  `RPPPN` bigint NULL DEFAULT NULL,
  `RUPIAH_TOTAL` bigint NULL DEFAULT NULL,
  `RUPIAH_TUNAI` bigint NULL DEFAULT NULL,
  `RUPIAH_ANGSURAN` bigint NULL DEFAULT NULL,
  `TANGGAL_REGISTER` date NULL DEFAULT NULL,
  `NOMOR_REGISTER` bigint NULL DEFAULT NULL,
  `TANGGAL_SPH` date NULL DEFAULT NULL,
  `NOMOR_SPH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UNIT_ID` int NULL DEFAULT NULL,
  `UPLOAD_BY` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UPLOAD_DATE` date NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`NOAGENDA`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14257 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_saldo_pelanggan`;
CREATE TABLE `trn_saldo_pelanggan`  (
  `V_BULAN_REKAP` int NOT NULL,
  `UNITUP` int NULL DEFAULT NULL,
  `IDPEL` bigint NOT NULL,
  `NAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMAPNJ` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TARIF` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DAYA` bigint NULL DEFAULT NULL,
  `KDPT_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `THBLMUT` bigint NULL DEFAULT NULL,
  `JENIS_MK` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `JENISLAYANAN` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `FRT` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KOGOL` bigint NULL DEFAULT NULL,
  `FKMKWH` bigint NULL DEFAULT NULL,
  `NOMOR_METER_KWH` bigint NULL DEFAULT NULL,
  `TANGGAL_PASANG_RUBAH_APP` bigint NULL DEFAULT NULL,
  `MERK_METER_KWH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TYPE_METER_KWH` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TAHUN_TERA_METER_KWH` int NULL DEFAULT NULL,
  `TAHUN_BUAT_METER_KWH` int NULL DEFAULT NULL,
  `NOMOR_GARDU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NOMOR_JURUSAN_TIANG` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA_GARDU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KAPASITAS_TRAFO` bigint NULL DEFAULT NULL,
  `NOMOR_METER_PREPAID` bigint NULL DEFAULT NULL,
  `PRODUCT` bigint NULL DEFAULT NULL,
  `KOORDINAT_X` float NULL DEFAULT NULL,
  `KOORDINAT_Y` float NULL DEFAULT NULL,
  `KDAM` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KDPEMBMETER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KET_KDPEMBMETER` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `STATUS_DIL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `KRN` bigint NULL DEFAULT NULL,
  `VKRN` bigint NULL DEFAULT NULL,
  PRIMARY KEY (`IDPEL`, `V_BULAN_REKAP`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26310 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `trn_upload_to`;
CREATE TABLE `trn_upload_to`  (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `IDPEL` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `NAMA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TARIF` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `DAYA` int NULL DEFAULT NULL,
  `GARDU` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `TIANG` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `JAM_NYALA` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `JENIS_TO` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `LATITUDE` float NULL DEFAULT NULL,
  `LONGITUDE` float NULL DEFAULT NULL,
  `SUBDLPD` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `UNIT_ID` int NULL DEFAULT NULL,
  `CREATED_BY` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `CREATED_DATE` date NULL DEFAULT current_timestamp,
  PRIMARY KEY (`ID`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

