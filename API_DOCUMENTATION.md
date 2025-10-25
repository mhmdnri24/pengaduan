# API Documentation - Master Pasar Unit

## Overview
Dokumen ini berisi API endpoints untuk fitur unit blok pada module master_pasar_blok.

## Base URL
```
/base_url/master_pasar_blok/
```

## Authentication
Semua endpoint memerlukan session login yang valid dan hak akses yang sesuai.

## Endpoints

### 1. Get Units by Blok ID
Mendapatkan semua unit dalam sebuah blok.

**Endpoint:** `GET /master_pasar_blok/get_units_by_blok`

**Parameters:**
- `blok_id` (int, required) - ID dari blok

**Response:**
```json
[
    {
        "pasar_unit_id": 1,
        "pasar_blok_id": 5,
        "pasar_unit_nomor": "1",
        "pasar_unit_lebar": "10.00",
        "pasar_unit_panjang": "20.00",
        "pasar_unit_luas": "200.00",
        "pasar_unit_harga_sewa": "5000000",
        "pasar_unit_status": "TERISI",
        "pasar_unit_penyewa": "Ahmad Wijaya",
        "pasar_unit_tanggal_sewa": "2024-01-01",
        "pasar_unit_tanggal_jatuh_tempo": "2024-12-31",
        "pasar_unit_keterangan": "Depan pintu masuk",
        "pasar_unit_created_at": "2024-01-01 10:00:00",
        "pasar_unit_updated_at": "2024-01-01 10:00:00"
    },
    {
        "pasar_unit_id": 2,
        "pasar_blok_id": 5,
        "pasar_unit_nomor": "2",
        "pasar_unit_lebar": "10.00",
        "pasar_unit_panjang": "20.00",
        "pasar_unit_luas": "200.00",
        "pasar_unit_harga_sewa": "5000000",
        "pasar_unit_status": "TERSEDIA",
        "pasar_unit_penyewa": null,
        "pasar_unit_tanggal_sewa": null,
        "pasar_unit_tanggal_jatuh_tempo": null,
        "pasar_unit_keterangan": null,
        "pasar_unit_created_at": "2024-01-01 10:00:00",
        "pasar_unit_updated_at": "2024-01-01 10:00:00"
    }
]
```

**Error Response:**
```json
[]
```

### 2. Get Unit by ID
Mendapatkan detail unit berdasarkan ID.

**Endpoint:** `GET /master_pasar_blok/get_unit_by_id`

**Parameters:**
- `id` (int, required) - ID dari unit

**Response:**
```json
{
    "pasar_unit_id": 1,
    "pasar_blok_id": 5,
    "pasar_unit_nomor": "1",
    "pasar_unit_lebar": "10.00",
    "pasar_unit_panjang": "20.00",
    "pasar_unit_luas": "200.00",
    "pasar_unit_harga_sewa": "5000000",
    "pasar_unit_status": "TERISI",
    "pasar_unit_penyewa": "Ahmad Wijaya",
    "pasar_unit_tanggal_sewa": "2024-01-01",
    "pasar_unit_tanggal_jatuh_tempo": "2024-12-31",
    "pasar_unit_keterangan": "Depan pintu masuk",
    "pasar_unit_created_at": "2024-01-01 10:00:00",
    "pasar_unit_updated_at": "2024-01-01 10:00:00"
}
```

**Error Response:**
```json
{
    "status": false,
    "message": "Unit tidak ditemukan"
}
```

### 3. Save Unit
Menambah atau mengupdate unit.

**Endpoint:** `POST /master_pasar_blok/save_unit`

**Parameters:**
- `id` (int, optional) - ID unit (kosong untuk tambah baru)
- `pasar_blok_id` (int, required) - ID blok
- `pasar_unit_nomor` (string, required) - Nomor unit
- `pasar_unit_lebar` (decimal, optional) - Lebar dalam meter
- `pasar_unit_panjang` (decimal, optional) - Panjang dalam meter
- `pasar_unit_harga_sewa` (decimal, optional) - Harga sewa
- `pasar_unit_status` (string, required) - Status (TERSEDIA/TERISI/MAINTENANCE)
- `pasar_unit_penyewa` (string, optional) - Nama penyewa
- `pasar_unit_tanggal_sewa` (date, optional) - Tanggal mulai sewa
- `pasar_unit_tanggal_jatuh_tempo` (date, optional) - Tanggal jatuh tempo
- `pasar_unit_keterangan` (text, optional) - Keterangan

**Success Response:**
```json
{
    "status": true,
    "message": "Unit berhasil ditambahkan"
}
```

**Error Responses:**
```json
{
    "status": false,
    "message": "Blok dan Nomor Unit harus diisi"
}
```

```json
{
    "status": false,
    "message": "Nomor Unit sudah ada dalam blok ini"
}
```

### 4. Delete Unit
Menghapus unit.

**Endpoint:** `POST /master_pasar_blok/delete_unit`

**Parameters:**
- `id` (int, required) - ID unit yang akan dihapus

**Success Response:**
```json
{
    "status": true,
    "message": "Unit berhasil dihapus"
}
```

**Error Response:**
```json
{
    "status": false,
    "message": "ID tidak valid"
}
```

### 5. Generate Multiple Units
Membuat multiple unit sekaligus.

**Endpoint:** `POST /master_pasar_blok/generate_units`

**Parameters:**
- `blok_id` (int, required) - ID blok
- `start_nomor` (int, required) - Nomor awal
- `end_nomor` (int, required) - Nomor akhir
- `lebar` (decimal, optional) - Lebar default
- `panjang` (decimal, optional) - Panjang default
- `harga` (decimal, optional) - Harga sewa default

**Success Response:**
```json
{
    "status": true,
    "message": "Unit berhasil digenerate"
}
```

**Error Response:**
```json
{
    "status": false,
    "message": "Data tidak lengkap"
}
```

### 6. Get Unit Statistics
Mendapatkan statistik unit per blok.

**Endpoint:** `GET /master_pasar_blok/get_unit_stats`

**Parameters:**
- `blok_id` (int, optional) - ID blok (kosong untuk semua blok)

**Response:**
```json
{
    "total": 50,
    "tersedia": 20,
    "terisi": 25,
    "maintenance": 5
}
```

### 7. Get Expiring Units
Mendapatkan unit yang akan jatuh tempo dalam X hari.

**Endpoint:** `GET /master_pasar_blok/get_expiring_units`

**Parameters:**
- `days` (int, optional, default: 30) - Jumlah hari ke depan

**Response:**
```json
[
    {
        "pasar_unit_id": 1,
        "pasar_unit_nomor": "1",
        "pasar_unit_penyewa": "Ahmad Wijaya",
        "pasar_unit_tanggal_jatuh_tempo": "2024-02-15",
        "pasar_blok_nama": "A-01",
        "pasar_jenis_nama": "KIOS",
        "pasar_nama": "Pasar Tradisional X"
    }
]
```

### 8. Bulk Update Status
Mengubah status multiple unit sekaligus.

**Endpoint:** `POST /master_pasar_blok/bulk_update_status`

**Parameters:**
- `ids` (array, required) - Array dari ID unit
- `status` (string, required) - Status baru

**Success Response:**
```json
{
    "status": true,
    "message": "Status berhasil diperbarui"
}
```

## Error Codes

| Status | Description |
|--------|-------------|
| 200 | Success |
| 400 | Bad Request - Parameter tidak valid |
| 401 | Unauthorized - Tidak memiliki hak akses |
| 404 | Not Found - Data tidak ditemukan |
| 500 | Internal Server Error - Kesalahan server |

## Usage Examples

### JavaScript Example - Get Units by Blok
```javascript
$.ajax({
    url: '/master_pasar_blok/get_units_by_blok',
    type: 'GET',
    data: { blok_id: 5 },
    dataType: 'json',
    success: function(data) {
        console.log('Units:', data);
        // Render units to grid
    },
    error: function() {
        console.error('Failed to load units');
    }
});
```

### JavaScript Example - Save Unit
```javascript
var formData = {
    pasar_blok_id: 5,
    pasar_unit_nomor: '10',
    pasar_unit_lebar: 10,
    pasar_unit_panjang: 20,
    pasar_unit_status: 'TERSEDIA'
};

$.ajax({
    url: '/master_pasar_blok/save_unit',
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(response) {
        if (response.status) {
            toastr.success(response.message);
            // Reload units
        } else {
            toastr.error(response.message);
        }
    },
    error: function() {
        toastr.error('Terjadi kesalahan sistem');
    }
});
```

## Notes

1. **Date Format**: All date parameters should be in YYYY-MM-DD format
2. **Decimal Format**: Use dot (.) as decimal separator
3. **Currency**: All currency values should be in IDR without formatting
4. **Validation**: Client-side validation should be implemented but server-side validation is mandatory
5. **Permissions**: Make sure user has appropriate permissions before calling these endpoints

## Testing

Use Postman or similar tool to test these endpoints. Example collection:

```json
{
    "info": {
        "name": "Master Pasar Unit API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Get Units by Blok",
            "request": {
                "method": "GET",
                "header": [],
                "url": {
                    "raw": "{{base_url}}/master_pasar_blok/get_units_by_blok?blok_id=5",
                    "host": ["{{base_url}}"],
                    "path": ["master_pasar_blok", "get_units_by_blok"],
                    "query": [
                        {
                            "key": "blok_id",
                            "value": "5"
                        }
                    ]
                }
            }
        }
    ]
}