-- Tabel untuk menyimpan detail penugasan kepengurusan
CREATE TABLE IF NOT EXISTS `kepengurusan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `kepengurusan_sosial_id` int(11) NOT NULL COMMENT 'ID dari tabel kepengurusan_sosial',
  `kategori_kepengurusan_id` int(11) NOT NULL COMMENT 'ID dari tabel kategori_kepengurusan',
  `id_fasilitas_umum` int(11) NOT NULL COMMENT 'ID dari tabel fasilitas_umum (masjid/mushola)',
  `tanggal_sk` date DEFAULT NULL COMMENT 'Tanggal SK penugasan',
  `nomor_sk` varchar(100) DEFAULT NULL COMMENT 'Nomor SK penugasan',
  `masa_jabatan` int(2) DEFAULT NULL COMMENT 'Masa jabatan dalam tahun',
  `tanggal_mulai` date DEFAULT NULL COMMENT 'Tanggal mulai penugasan',
  `tanggal_selesai` date DEFAULT NULL COMMENT 'Tanggal selesai penugasan',
  `file_sk` varchar(255) DEFAULT NULL COMMENT 'Nama file SK penugasan',
  `status` enum('AKTIF','SELESAI','NONAKTIF') NOT NULL DEFAULT 'AKTIF' COMMENT 'Status penugasan',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kepengurusan_sosial` (`kepengurusan_sosial_id`),
  KEY `idx_kategori_kepengurusan` (`kategori_kepengurusan_id`),
  KEY `idx_fasilitas_umum` (`id_fasilitas_umum`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_mulai` (`tanggal_mulai`),
  KEY `idx_tanggal_selesai` (`tanggal_selesai`),
  CONSTRAINT `fk_kepengurusan_detail_sosial` FOREIGN KEY (`kepengurusan_sosial_id`) REFERENCES `kepengurusan_sosial` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kepengurusan_detail_kategori` FOREIGN KEY (`kategori_kepengurusan_id`) REFERENCES `kategori_kepengurusan` (`kepengurusan_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kepengurusan_detail_fasilitas` FOREIGN KEY (`id_fasilitas_umum`) REFERENCES `fasilitas_umum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel detail penugasan kepengurusan';

-- Insert sample data untuk testing (setelah data kepengurusan_sosial dan kategori_kepengurusan tersedia)
-- Contoh data akan diinsert setelah tabel lain dibuat
INSERT INTO kepengurusan_detail (kepengurusan_sosial_id, kategori_kepengurusan_id, id_fasilitas_umum, tanggal_sk, nomor_sk, masa_jabatan, tanggal_mulai, tanggal_selesai, status, created_at, created_by) VALUES
-- Asumsi: kepengurusan_sosial_id=1 (Ahmad Yusuf), kategori_kepengurusan_id=1 (Marbot), id_fasilitas_umum=1 (Masjid)
(1, 1, 1, '2024-01-15', 'SK/001/2024', 2, '2024-02-01', '2026-01-31', 'AKTIF', NOW(), 1),
-- Asumsi: kepengurusan_sosial_id=2 (Siti Aminah), kategori_kepengurusan_id=2 (Takmir), id_fasilitas_umum=1 (Masjid)
(2, 2, 1, '2024-01-15', 'SK/002/2024', 3, '2024-02-01', '2027-01-31', 'AKTIF', NOW(), 1),
-- Asumsi: kepengurusan_sosial_id=3 (Budi Santoso), kategori_kepengurusan_id=1 (Marbot), id_fasilitas_umum=2 (Mushola)
(3, 1, 2, '2024-01-20', 'SK/003/2024', 1, '2024-02-15', '2025-02-14', 'AKTIF', NOW(), 1),
-- Asumsi: kepengurusan_sosial_id=4 (Dewi Sartika), kategori_kepengurusan_id=4 (Bendahara), id_fasilitas_umum=1 (Masjid)
(4, 4, 1, '2024-01-25', 'SK/004/2024', 2, '2024-03-01', '2026-02-28', 'AKTIF', NOW(), 1),
-- Asumsi: kepengurusan_sosial_id=5 (Hendra Wijaya), kategori_kepengurusan_id=5 (Sekretaris), id_fasilitas_umum=1 (Masjid)
(5, 5, 1, '2024-01-25', 'SK/005/2024', 2, '2024-03-01', '2026-02-28', 'AKTIF', NOW(), 1);
