<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2025-10-25 01:34:43 --> 404 Page Not Found: /index
ERROR - 2025-10-25 01:34:43 --> 404 Page Not Found: /index
ERROR - 2025-10-25 09:33:08 --> 404 Page Not Found: /index
ERROR - 2025-10-25 09:33:08 --> 404 Page Not Found: /index
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_deskripsi /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kecamatan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kelurahan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_latitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_longitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_deskripsi /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kecamatan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kelurahan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_latitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_longitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_deskripsi /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kecamatan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_id_kelurahan /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_latitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:06:31 --> Severity: Notice --> Undefined property: stdClass::$pasar_longitude /www/wwwroot/dashboard.nusakoding.com/application/modules/master_pasar/controllers/Master_pasar.php 112
ERROR - 2025-10-25 11:10:47 --> Severity: Notice --> Undefined property: CI::$target_retribusi_m /www/wwwroot/dashboard.nusakoding.com/application/third_party/MX/Controller.php 65
ERROR - 2025-10-25 11:10:47 --> Severity: error --> Exception: Call to a member function count_all_target() on null /www/wwwroot/dashboard.nusakoding.com/application/modules/target_retribusi/controllers/Target_retribusi.php 28
ERROR - 2025-10-25 11:10:48 --> 404 Page Not Found: /index
ERROR - 2025-10-25 11:10:48 --> 404 Page Not Found: /index
ERROR - 2025-10-25 11:16:23 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:16:23 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:16:23 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:16:23 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:16:23 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:16:31 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:16:31 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:16:31 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:16:31 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:16:31 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:17:30 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:17:30 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:17:30 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:30 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_tahun_list
ERROR - 2025-10-25 11:17:30 --> 404 Page Not Found: ../modules/realisasi_retribusi/controllers/Realisasi_retribusi/get_jenis_retribusi
ERROR - 2025-10-25 11:17:36 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:36 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:37 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:37 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:17:53 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5...' at line 6 - Invalid query: SELECT `rr`.`id`, `rr`.`id_retribusi`, `rr`.`tahun`, `rr`.`triwulan`, `rr`.`bulan`, `rr`.`realisasi_jumlah`, `rr`.`realisasi_nominal`, `rr`.`tanggal_realisasi`, `rr`.`keterangan`, `rr`.`bukti_pembayaran`, `rr`.`created_at`, `mr`.`nama_retribusi`, `mr`.`kode_retribusi`, `mr`.`jenis_retribusi`, CASE 
                            WHEN rr.triwulan = 1 THEN "Triwulan 1 (Jan-Mar)"
                            WHEN rr.triwulan = 2 THEN "Triwulan 2 (Apr-Jun)"
                            WHEN rr.triwulan = 3 THEN "Triwulan 3 (Jul-Sep)"
                            WHEN rr.triwulan = 4 THEN "Triwulan 4 (Oct-Dec)"
                        END as nama_triwulan, `CASE WHEN rr`.`bulan = 1 THEN "Januari" WHEN rr`.`bulan = 2 THEN "Februari" WHEN rr`.`bulan = 3 THEN "Maret" WHEN rr`.`bulan = 4 THEN "April" WHEN rr`.`bulan = 5 THEN "Mei" WHEN rr`.`bulan = 6 THEN "Juni" WHEN rr`.`bulan = 7 THEN "Juli" WHEN rr`.`bulan = 8 THEN "Agustus" WHEN rr`.`bulan = 9 THEN "September" WHEN rr`.`bulan = 10 THEN "Oktober" WHEN rr`.`bulan = 11 THEN "November" WHEN rr`.`bulan = 12 THEN "Desember" END` as `nama_bulan`
FROM `realisasi_retribusi` `rr`
LEFT JOIN `master_retribusi` `mr` ON `rr`.`id_retribusi` = `mr`.`id`
ORDER BY `rr`.`tanggal_realisasi` DESC
 LIMIT 10
ERROR - 2025-10-25 11:30:34 --> Severity: error --> Exception: Unable to locate the model you have specified: Target_retribusi_m /www/wwwroot/dashboard.nusakoding.com/system/core/Loader.php 348
ERROR - 2025-10-25 11:30:42 --> Severity: error --> Exception: Unable to locate the model you have specified: Target_retribusi_m /www/wwwroot/dashboard.nusakoding.com/system/core/Loader.php 348
ERROR - 2025-10-25 11:31:22 --> 404 Page Not Found: /index
ERROR - 2025-10-25 11:31:22 --> 404 Page Not Found: /index
ERROR - 2025-10-25 11:32:00 --> Severity: error --> Exception: Unable to locate the model you have specified: Target_retribusi_m /www/wwwroot/dashboard.nusakoding.com/system/core/Loader.php 348
