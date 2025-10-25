CREATE TABLE `retribusi_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retribusi` int(11) NOT NULL,
  `tahun` int(4) NOT NULL,
  `bulan` varchar(20) DEFAULT NULL COMMENT 'NULL untuk target tahunan, nama bulan untuk target bulanan',
  `target` decimal(15,2) NOT NULL DEFAULT 0.00,
  `capaian` decimal(15,2) NOT NULL DEFAULT 0.00,
  `persentase` decimal(5,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_retribusi_tahun` (`id_retribusi`, `tahun`),
  KEY `idx_retribusi_tahun_bulan` (`id_retribusi`, `tahun`, `bulan`),
  CONSTRAINT `fk_retribusi_target_master` FOREIGN KEY (`id_retribusi`) REFERENCES `master_retribusi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel target dan capaian retribusi per tahun/bulan';