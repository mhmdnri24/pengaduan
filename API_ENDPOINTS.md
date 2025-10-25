# 📡 API ENDPOINTS - DASHBOARD PEMERINTAH KOTA LUBUKLINGGAU

## 🔑 KONFIGURASI API KEY

### Headers Wajib untuk Semua Request:
```bash
X-API-Key: API_HIJ973D4Nmgdbhy42
Origin: https://dashboard.nusakoding.com
```

### Headers untuk Mobile App (Bearer Token):
```bash
Authorization: [token_dari_login]
X-API-Key: API_HIJ973D4Nmgdbhy42
Origin: https://dashboard.nusakoding.com
```

### Headers untuk Testing dengan Postman:
```bash
X-API-Key: API_HIJ973D4Nmgdbhy42
User-Agent: PostmanRuntime/7.x.x  # <-- Penting untuk bypassing domain validation
```

## 🏠 API PENGATURAN

### GET /api/v1/pengaturan
Ambil data pengaturan aplikasi.

**Request dengan curl:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pengaturan" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Postman Setup:**
- Method: `GET`
- URL: `https://dashboard.nusakoding.com/api/v1/pengaturan`
- Headers:
  - `X-API-Key`: `API_HIJ973D4Nmgdbhy42`
  - `User-Agent`: `PostmanRuntime/7.x.x`

**Response:**
```json
{
  "status": "success",
  "message": "Data pengaturan berhasil diambil",
  "data": {
    "nama_situs": "Dashboard Pemerintah Kota Lubuk Linggau",
    "tagline": "Satu Data Pemerintah Kota Lubuk Linggau",
    "logo": "https://dashboard.nusakoding.com/uploads/pengaturan/logo_1756095477.png",
    "favicon": "https://dashboard.nusakoding.com/uploads/pengaturan/favicon_1756095484.png",
    "requested_by": {
      "ip_address": "124.158.168.98",
      "domain": "dashboard.nusakoding.com"
    },
    "timestamp": "2025-10-05 14:28:46",
    "api_version": "v1"
  }
}
```

### GET /api/v1/pengaturan/test
Endpoint test untuk verifikasi koneksi API.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pengaturan/test" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

## 👥 API MASYARAKAT

### POST /api/v1/masyarakat/register
Registrasi masyarakat baru dengan WhatsApp OTP.

**Request:**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/masyarakat/register" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "nama_lengkap=Ahmad Rahman" \
  -F "nik=1234567890123456" \
  -F "no_telpon=081234567890" \
  -F "foto_profil=@profil.jpg" \
  -F "foto_ktp=@ktp.jpg"
```

### POST /api/v1/masyarakat/login
Login dengan NIK untuk kirim OTP WhatsApp.

**Request:**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/masyarakat/login" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "nik=1234567890123456"
```

### POST /api/v1/masyarakat/verify-otp
Verifikasi OTP untuk login.

**Request:**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/masyarakat/verify-otp" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "nik=1234567890123456" \
  -F "otp=123456"
```

**Response Error - Account Not Verified (403):**
```json
{
  "status": "error",
  "message": "Mohon maaf, akun Anda sedang dalam proses verifikasi oleh admin. Kami akan mengirim notifikasi melalui WhatsApp setelah akun Anda diverifikasi dan diaktifkan.",
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Response Error - NIK Not Found (404):**
```json
{
  "status": "error",
  "message": "NIK tidak ditemukan dalam sistem kami",
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

### GET /api/v1/masyarakat/kecamatan
Ambil list kecamatan di Kota Lubuk Linggau.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/masyarakat/kecamatan" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Response:**
```json
{
  "status": "success",
  "message": "Data kecamatan berhasil diambil",
  "data": {
    "kecamatan": [
      {
        "id_kecamatan": "16.73.01",
        "nama_kecamatan": "Lubuk Linggau Timur I"
      },
      {
        "id_kecamatan": "16.73.02",
        "nama_kecamatan": "Lubuk Linggau Barat I"
      }
    ],
    "total": 8
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

### GET /api/v1/masyarakat/kelurahan/{id_kecamatan}
Ambil list kelurahan berdasarkan id_kecamatan.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/masyarakat/kelurahan/16.73.01" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Response:**
```json
{
  "status": "success",
  "message": "Data kelurahan berhasil diambil",
  "data": {
    "kecamatan": {
      "id_kecamatan": "16.73.01",
      "nama_kecamatan": "Lubuk Linggau Timur I"
    },
    "kelurahan": [
      {
        "id_kelurahan": "16.73.01.1001",
        "nama_kelurahan": "Air Kuti"
      },
      {
        "id_kelurahan": "16.73.01.1002",
        "nama_kelurahan": "Majapahit"
      }
    ],
    "total": 8
  }
}
```

### GET /api/v1/masyarakat/{nik}
Ambil data masyarakat berdasarkan NIK.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/masyarakat/1234567890123456" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Response Success:**
```json
{
  "status": "success",
  "message": "Data masyarakat berhasil diambil",
  "data": {
    "id": "9",
    "nama_lengkap": "Ahmad Rahman",
    "nik": "1234567890123456",
    "no_telpon": "081234567890",
    "tempat_lahir": "Lubuklinggau",
    "tanggal_lahir": "1990-05-15",
    "alamat": "Jl. Sudirman No. 123",
    "kecamatan": {
      "id_kecamatan": "16.73.01",
      "nama_kecamatan": "Lubuk Linggau Timur I"
    },
    "kelurahan": {
      "id_kelurahan": "16.73.01.1001",
      "nama_kelurahan": "Air Kuti"
    },
    "foto_profil_url": "https://dashboard.nusakoding.com/uploads/masyarakat1234567890123456/masyarakat_profil_1234567890.jpg",
    "foto_ktp_url": "https://dashboard.nusakoding.com/uploads/masyarakat1234567890123456/masyarakat_ktp_1234567890.jpg",
    "status_aktif": "1",
    "created_at": "2025-10-05 15:54:13",
    "updated_at": "2025-10-05 15:55:55"
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Response Error - NIK Not Found (404):**
```json
{
  "status": "error",
  "message": "Data masyarakat dengan NIK tersebut tidak ditemukan",
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Response Error - Invalid NIK Format (400):**
```json
{
  "status": "error",
  "message": "NIK harus 16 digit angka",
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Notes:**
- ✅ Endpoint ini mengembalikan data lengkap masyarakat berdasarkan NIK
- ✅ Termasuk data kecamatan dan kelurahan (jika ada)
- ✅ URL foto profil dan KTP akan ditampilkan jika ada
- ✅ NIK harus 16 digit angka
- ✅ Tidak memerlukan token autentikasi (public endpoint dengan API key)

### GET /api/v1/masyarakat/profile
Ambil data profile masyarakat yang sedang login.

**Headers:**
```
Authorization: [token_dari_login]
X-API-Key: API_HIJ973D4Nmgdbhy42
Origin: https://dashboard.nusakoding.com
```

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/masyarakat/profile" \
  -H "Authorization: 59bcb4071a05e907d7678c1a1ebcdfca" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Response:**
```json
{
  "status": "success",
  "message": "Profile berhasil diambil",
  "data": {
    "id": "9",
    "nama_lengkap": "Test User WhatsApp 081366997008",
    "nik": "5566778899001122",
    "no_telpon": "081366997008",
    "tempat_lahir": null,
    "tanggal_lahir": null,
    "alamat": null,
    "kecamatan": {
      "id_kecamatan": null,
      "nama_kecamatan": null
    },
    "kelurahan": {
      "id_kelurahan": null,
      "nama_kelurahan": null
    },
    "foto_profil_url": null,
    "foto_ktp_url": null,
    "status_aktif": "1",
    "created_at": "2025-10-05 15:54:13",
    "updated_at": "2025-10-05 15:55:55"
  }
}
```

### PUT /api/v1/masyarakat/profile
Update profile masyarakat (nama, foto_profil, no_telpon, tempat_lahir, tanggal_lahir, kecamatan, kelurahan).
NIK dan foto_ktp tidak dapat diupdate.

**Headers:**
```
Authorization: [token_dari_login]
X-API-Key: API_HIJ973D4Nmgdbhy42
Origin: https://dashboard.nusakoding.com
Content-Type: application/json
```

**Request dengan JSON:**
```bash
curl -X PUT "https://dashboard.nusakoding.com/api/v1/masyarakat/profile" \
  -H "Authorization: 59bcb4071a05e907d7678c1a1ebcdfca" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_lengkap": "Ahmad Rahman Updated",
    "no_telpon": "081234567891",
    "tempat_lahir": "Lubuklinggau",
    "tanggal_lahir": "1990-05-15",
    "id_kecamatan": "16.73.01",
    "id_kelurahan": "16.73.01.1001"
  }'
```

**Request dengan Form Data (untuk upload foto):**
```bash
curl -X PUT "https://dashboard.nusakoding.com/api/v1/masyarakat/profile" \
  -H "Authorization: 59bcb4071a05e907d7678c1a1ebcdfca" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "nama_lengkap=Test User Updated" \
  -F "tempat_lahir=Lubuklinggau" \
  -F "tanggal_lahir=1990-05-15" \
  -F "id_kecamatan=16.73.01" \
  -F "id_kelurahan=16.73.01.1001" \
  -F "foto_profil=@new_profil.jpg"
```

**Response Success:**
```json
{
  "status": "success",
  "message": "Profile berhasil diupdate",
  "data": {
    "id": "9",
    "nama_lengkap": "Ahmad Rahman Updated",
    "nik": "5566778899001122",
    "no_telpon": "081234567891",
    "tempat_lahir": "Lubuklinggau",
    "tanggal_lahir": "1990-05-15",
    "foto_profil_url": null,
    "updated_at": "2025-10-05 16:15:09"
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Notes:**
- ✅ **tempat_lahir** dan **tanggal_lahir** sudah tersedia di tabel database
- ✅ **id_kecamatan** dan **id_kelurahan** dapat diupdate dengan validasi
- ✅ **NIK** dan **foto_ktp** tidak dapat diupdate sesuai requirement
- ✅ **foto_profil** lama akan otomatis dihapus saat upload yang baru
- ✅ **no_telpon** akan divalidasi format dan unikness
- ✅ **id_kecamatan** akan divalidasi apakah termasuk Kota Lubuk Linggau (id_kota = '16.73')
- ✅ **id_kelurahan** akan divalidasi apakah termasuk dalam kecamatan yang dipilih
- ✅ Mendukung JSON body atau form-data untuk upload file

## 📱 API DEVICE

### GET /api/v1/device
Ambil list semua data device dengan pagination dan filter status.

**Request dengan curl:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/device?page=1&limit=10&status=ACTIVE" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Postman Setup:**
- Method: `GET`
- URL: `https://dashboard.nusakoding.com/api/v1/device`
- Headers:
  - `X-API-Key`: `API_HIJ973D4Nmgdbhy42`
  - `User-Agent`: `PostmanRuntime/7.x.x`
- Query Parameters (optional):
  - `page`: 1
  - `limit`: 20
  - `status`: ACTIVE

**Response Success:**
```json
{
  "status": "success",
  "message": "Data device berhasil diambil",
  "data": {
    "devices": [
      {
        "id": "1",
        "name": "Device 1",
        "status": "ACTIVE",
        "created_at": "2025-10-12 17:24:58",
        "updated_at": "2025-10-12 17:24:58",
        "created_at_formatted": "12/10/2025 17:24",
        "updated_at_formatted": "12/10/2025 17:24"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_records": 1,
      "total_pages": 1,
      "has_next": false,
      "has_prev": false
    },
    "filters_applied": {
      "status": "ACTIVE"
    }
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Response Not Found (Empty Data):**
```json
{
  "status": "error",
  "message": "Tidak ada data device ditemukan",
  "data": {
    "devices": [],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total_records": 0,
      "total_pages": 0,
      "has_next": false,
      "has_prev": false
    }
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

### POST /api/v1/device
Buat device baru atau update device berdasarkan ID yang disediakan.

**Request dengan curl (Create New):**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/device" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "name=Smart Device 001" \
  -F "status=ACTIVE"
```

**Request dengan curl (Update Existing):**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/device" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "id=1" \
  -F "name=Smart Device 001 Updated" \
  -F "status=INACTIVE"
```

**Response Success - Create:**
```json
{
  "status": "success",
  "message": "Device berhasil dibuat",
  "data": {
    "id": "2",
    "name": "Smart Device 001",
    "status": "ACTIVE",
    "created_at": "2025-10-12 17:30:00"
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Response Success - Update:**
```json
{
  "status": "success",
  "message": "Device berhasil diupdate",
  "data": {
    "id": "1",
    "name": "Smart Device 001 Updated",
    "status": "INACTIVE",
    "updated_at": "2025-10-12 17:31:00"
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Notes:**
- ✅ Untuk **CREATE**: Jangan sertakan parameter `id`
- ✅ Untuk **UPDATE**: Sertakan parameter `id` yang valid
- ✅ Field `name` wajib diisi
- ✅ Field `status` opsional, default: "ACTIVE", valid values: "ACTIVE", "INACTIVE", "MAINTENANCE"

## � API PELAPORAN
### POST /api/v1/pelaporan/{id}/create_comment
Membuat komentar baru untuk pelaporan yang sudah ada.

**Request:**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/pelaporan/1/create_comment" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "comment=Kapan laporan saya akan ditangani?" \
  -F "rating=4" \
  -F "is_internal=0" \
  -F "created_by=4"
```

**Response Success:**
```json
{
  "status": "success",
  "message": "Komentar berhasil dibuat",
  "data": {
    "id": "3",
    "pelaporan_id": "1",
    "comment": "Kapan laporan saya akan ditangani?",
    "rating": 4,
    "is_internal": false,
    "is_internal_formatted": "Public",
    "created_by": "4",
    "created_by_name": "Ahmad Rahman",
    "created_at": "2025-10-18 14:30:00",
    "created_at_formatted": "18/10/2025 14:30"
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

**Notes:**
- ✅ Masyarakat bisa kirim komentar untuk laporan yang sudah dibuat
- ✅ Komentar tersimpan di tabel `pelaporan_comments`
- ✅ Mendukung rating 1-5 untuk feedback
- ✅ Bisa komentar internal atau public
- ✅ Otomatis ambil nama user dari database


### GET /api/v1/pelaporan
Ambil list semua laporan dengan pagination.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pelaporan?page=1&limit=10&status=LAPOR" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

### GET /api/v1/pelaporan/{id}
Ambil detail laporan berdasarkan ID.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pelaporan/1" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

### POST /api/v1/pelaporan/create
Buat laporan baru.

**Request:**
```bash
curl -X POST "https://dashboard.nusakoding.com/api/v1/pelaporan/create" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com" \
  -F "judul=Jalan Berlubang" \
  -F "deskripsi=Jalan rusak parah" \
  -F "alamat=Jl. Sudirman No. 123" \
  -F "kategori=Infrastruktur" \
  -F "pelapor_nama=Ahmad Rahman" \
  -F "pelapor_telepon=081234567890" \
  -F "pelapor_nik=1234567890123456" \
  -F "pelapor_alamat=Jl. Sudirman No. 123" \
  -F "foto=@jalan_rusak.jpg"
```

### GET /api/v1/pelaporan/kategori
Ambil list kategori pelaporan.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pelaporan/kategori" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```
### GET /api/v1/pelaporan/pelaporan_history/{id}
Ambil history perubahan status pelaporan berdasarkan ID pelaporan.

**Request:**
```bash
curl -X GET "https://dashboard.nusakoding.com/api/v1/pelaporan/pelaporan_history/1" \
  -H "X-API-Key: API_HIJ973D4Nmgdbhy42" \
  -H "Origin: https://dashboard.nusakoding.com"
```

**Response:**
```json
{
  "status": "success",
  "message": "History pelaporan berhasil diambil",
  "data": {
    "pelaporan_id": "1",
    "history": [
      {
        "status_lama": "LAPOR",
        "status_baru": "DITERIMA",
        "updated_by": "Admin Operator",
        "updated_at": "2025-08-27 10:30:00",
        "updated_at_formatted": "27/08/2025 10:30"
      }
    ],
    "total_history": 1
  },
  "rate_limit": {
    "remaining_minute": 99,
    "remaining_hour": 999
  }
}
```

## ⚠️ ERROR RESPONSES

## ⚠️ ERROR RESPONSES

### Rate Limit Exceeded (429)
```json
{
  "status": "error",
  "message": "Too many requests. Please try again later."
}
```

### Invalid API Key (401)
```json
{
  "status": "error",
  "message": "Invalid API key"
}
```

### Domain Not Allowed (403)
```json
{
  "status": "error",
  "message": "Domain not allowed to access this API"
}
```

## 📊 RATE LIMITING
- **100 requests per menit** per IP address
- **1000 requests per jam** per IP address

Setiap response menyertakan informasi rate limit:
```json
{
  "rate_limit": {
    "remaining_minute": 95,
    "remaining_hour": 950
  }
}
```

## 🔒 SECURITY FEATURES
- ✅ API Key Authentication
- ✅ Domain Whitelist
- ✅ IP Whitelist
- ✅ Rate Limiting
- ✅ HTTPS Only
- ✅ Request Logging
- ✅ WhatsApp OTP Integration

---

**Base URL:** `https://dashboard.nusakoding.com/api/v1/`
**API Key:** `API_HIJ973D4Nmgdbhy42`
**Version:** v1
**Last Updated:** 12 Oktober 2025 (Added: API Device - GET & POST endpoints)