-- =====================================================
-- SQL Script untuk Insert Sample Data Tarif Retribusi Pasar
-- =====================================================

-- Insert sample tarif untuk berbagai jenis pasar
-- Pastikan data master_pasar_jenis sudah ada

-- Tarif untuk Pasar Sayuran
INSERT INTO retribusi_pasar_tarif (
    pasar_jenis_id,
    tarif_nama,
    tarif_harian,
    tarif_bulanan,
    tarif_denda_persen,
    tarif_berlaku_dari,
    tarif_berlaku_sampai,
    tarif_status
) VALUES
(5, 'Tarif Retribusi KIOS Pasar Induk', 5000.00, 150000.00, 2.5, '2024-01-01', NULL, 1),
(6, 'Tarif Retribusi LOS Pasar Induk', 7500.00, 225000.00, 2.5, '2024-01-01', NULL, 1),
(7, 'Tarif Retribusi PELATARAN Pasar Induk', 3000.00, 90000.00, 2.0, '2024-01-01', NULL, 1),
(8, 'Tarif Retribusi LAPAK Pasar Induk', 2000.00, 60000.00, 1.5, '2024-01-01', NULL, 1),
(9, 'Tarif Retribusi KIOS Pasar Tradisional', 4000.00, 120000.00, 2.5, '2024-01-01', NULL, 1),
(10, 'Tarif Retribusi LOS Pasar Tradisional', 6000.00, 180000.00, 2.5, '2024-01-01', NULL, 1),
(11, 'Tarif Retribusi PELATARAN Pasar Tradisional', 2500.00, 75000.00, 2.0, '2024-01-01', NULL, 1),
(12, 'Tarif Retribusi LAPAK Pasar Tradisional', 1500.00, 45000.00, 1.5, '2024-01-01', NULL, 1);

-- Jika ada jenis pasar lain, tambahkan sesuai kebutuhan
-- Contoh untuk jenis pasar dengan ID yang berbeda:

-- INSERT INTO retribusi_pasar_tarif (
--     pasar_jenis_id, 
--     tarif_nama, 
--     tarif_harian, 
--     tarif_bulanan, 
--     tarif_denda_persen, 
--     tarif_berlaku_dari, 
--     tarif_berlaku_sampai, 
--     tarif_status
-- ) VALUES 
-- (6, 'Tarif Retribusi Pasar Kain', 4000.00, 120000.00, 2.0, '2024-01-01', NULL, 1),
-- (7, 'Tarif Retribusi Pasar Elektronik', 15000.00, 450000.00, 3.5, '2024-01-01', NULL, 1);

-- Verifikasi data yang sudah diinsert
SELECT 
    rpt.tarif_id,
    rpt.tarif_nama,
    rpt.tarif_harian,
    rpt.tarif_bulanan,
    rpt.tarif_denda_persen,
    rpt.tarif_berlaku_dari,
    rpt.tarif_status,
    mpj.pasar_jenis_nama,
    mp.pasar_nama
FROM retribusi_pasar_tarif rpt
LEFT JOIN master_pasar_jenis mpj ON rpt.pasar_jenis_id = mpj.pasar_jenis_id
LEFT JOIN master_pasar mp ON mpj.pasar_id = mp.pasar_id
WHERE rpt.tarif_status = 1
ORDER BY rpt.tarif_id;
