# Implementasi API Emergency Page

## Overview
Implementasi API untuk emergency page yang menggunakan struktur yang sama dengan add_complaint_page.dart dengan parameter yang disesuaikan untuk emergency.

## File yang Dibuat/Dimodifikasi

### 1. lib/services/emergency_service.dart (BARU)
- Service untuk menangani emergency reports
- Method `submitEmergencyReport()` untuk mengirim laporan darurat
- Stream controllers untuk notifikasi emergency

### 2. lib/services/api_service.dart (DIMODIFIKASI)
- Menambahkan method `postEmergencyReport()`
- Parameter yang dikirim:
  - `judul`: 'Darurat' (fixed)
  - `deskripsi`: 'Laporan darurat - {kategori}'
  - `alamat`: alamat dari GPS
  - `kategori`: 'darurat' (default)
  - `pelapor_nama`: nama pengguna
  - `pelapor_telepon`: telepon pengguna
  - `pelapor_nik`: NIK pengguna
  - `pelapor_alamat`: alamat dari GPS
  - `jenis_pelaporan`: 'DARURAT' (fixed)

### 3. lib/pages/emergency_page.dart (DIMODIFIKASI)
- Load user data dari SharedPreferences
- Validasi data pengguna sebelum mengirim emergency
- Error handling dan loading states
- UI feedback untuk status pengiriman
- Informasi pelapor ditampilkan di UI

## Fitur yang Diimplementasikan

1. **Load User Data**: Mengambil data pengguna dari session
2. **Location Detection**: Deteksi lokasi GPS otomatis
3. **API Integration**: Mengirim emergency report ke server
4. **Error Handling**: Validasi dan error handling lengkap
5. **Loading States**: UI feedback saat loading
6. **User Validation**: Validasi data pengguna sebelum submit

## Parameter API Emergency

```dart
{
  'judul': 'Darurat',
  'deskripsi': 'Laporan darurat - darurat',
  'alamat': 'Alamat dari GPS',
  'kategori': 'darurat',
  'pelapor_nama': 'Nama Pengguna',
  'pelapor_telepon': 'Telepon Pengguna',
  'pelapor_nik': 'NIK Pengguna',
  'pelapor_alamat': 'Alamat dari GPS',
  'jenis_pelaporan': 'DARURAT'
}
```

## Flow Emergency Report

1. User membuka emergency page
2. System load user data dan deteksi lokasi
3. User tekan tombol darurat
4. System validasi data pengguna
5. Konfirmasi dialog muncul
6. Jika dikonfirmasi, kirim ke API
7. Tampilkan feedback sukses/error

## Testing

Untuk testing implementasi:
1. Pastikan user sudah login
2. Buka emergency page
3. Tunggu lokasi terdeteksi
4. Tekan tombol darurat
5. Konfirmasi pengiriman
6. Periksa response dari server
