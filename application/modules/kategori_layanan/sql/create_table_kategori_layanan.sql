-- Tabel untuk menyimpan kategori layanan
CREATE TABLE IF NOT EXISTS `layanan_kategori` (
  `kategori_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary key',
  `kategori_nama` varchar(255) NOT NULL COMMENT 'Nama kategori layanan',
  `kategori_deskripsi` text DEFAULT NULL COMMENT 'Deskripsi kategori layanan',
  `kategori_urutan` int(3) NOT NULL DEFAULT 0 COMMENT 'Urutan tampil',
  `kategori_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Aktif, 0=Tidak Aktif',
  `kategori_created_at` datetime DEFAULT NULL,
  `kategori_updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`kategori_id`),
  KEY `idx_status` (`kategori_status`),
  KEY `idx_urutan` (`kategori_urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel kategori layanan';

-- Insert sample data untuk testing
INSERT INTO layanan_kategori (kategori_nama, kategori_deskripsi, kategori_urutan, kategori_status, kategori_created_at, kategori_updated_at) VALUES
('Administrasi', 'Kategori layanan administrasi seperti surat keterangan domisili, pengantar nikah, dll', 1, 1, NOW(), NOW()),
('Sosial', 'Kategori layanan sosial seperti bantuan sosial, keterangan tidak mampu', 2, 1, NOW(), NOW()),
('Ekonomi', 'Kategori layanan ekonomi seperti izin usaha, keterangan usaha', 3, 1, NOW(), NOW()),
('Kesehatan', 'Kategori layanan kesehatan seperti surat rujukan, keterangan sehat', 4, 1, NOW(), NOW()),
('Pendidikan', 'Kategori layanan pendidikan seperti beasiswa, keterangan siswa', 5, 1, NOW(), NOW());

-- Insert menu untuk kategori layanan
INSERT INTO master_menu (menu_label, menu_icon, menu_url, menu_parent_id, menu_order, menu_access_code, menu_status) VALUES
('Kategori Layanan', 'fa fa-tags', 'kategori_layanan', 0, 7, 'admin.kategori_layanan.view', 1);