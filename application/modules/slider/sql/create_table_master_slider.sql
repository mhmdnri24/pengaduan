-- Migrasi Database untuk Tabel Master Slider
-- Dibuat pada: 2025-10-13
-- Deskripsi: Tabel untuk menyimpan data slider/gambar carousel

-- Buat tabel master_slider
CREATE TABLE `master_slider` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `slider_judul` VARCHAR(255) NOT NULL,
  `slider_deskripsi` TEXT NULL,
  `slider_file` VARCHAR(255) NULL,
  `slider_status` ENUM('AKTIF', 'NON AKTIF') NOT NULL DEFAULT 'NON AKTIF',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_slider_status` (`slider_status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data sample untuk testing
INSERT INTO `master_slider` (`slider_judul`, `slider_deskripsi`, `slider_file`, `slider_status`) VALUES
('Slider 1 - Selamat Datang', 'Ini adalah deskripsi untuk slider pertama yang menampilkan selamat datang di website kami.', 'slider1.jpg', 'AKTIF'),
('Slider 2 - Layanan Kami', 'Temukan berbagai layanan terbaik yang kami tawarkan untuk memenuhi kebutuhan Anda.', 'slider2.jpg', 'AKTIF'),
('Slider 3 - Kontak Kami', 'Hubungi kami untuk informasi lebih lanjut mengenai produk dan layanan yang tersedia.', 'slider3.jpg', 'NON AKTIF');