# Complaint List API Implementation

This document describes the implementation of the Complaint List API based on the provided curl request and JSON response.

## API Endpoint

```
GET https://dashboard.nusakoding.com/api/v1/pelaporan
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | Yes | Current page number (default: 1) |
| `limit` | integer | Yes | Items per page (default: 10) |
| `status` | string | No | Filter by status (e.g., 'LAPOR', 'PROSES', 'SELESAI') |
| `kategori` | string | No | Filter by category (e.g., 'infrastruktur') |
| `search` | string | No | Search query string |

## Request Headers

| Header | Value |
|--------|-------|
| `X-API-Key` | API_HIJ973D4Nmgdbhy42 |
| `Origin` | https://dashboard.nusakoding.com |

## Response Structure

The API returns a comprehensive response with the following structure:

```json
{
  "status": "success",
  "message": "Data pelaporan berhasil diambil",
  "data": {
    "pelaporan": [...],
    "pagination": {...},
    "filters_applied": {...}
  },
  "rate_limit": {...}
}
```

## Implementation

### 1. Models (`lib/models/complaint.dart`)

#### ComplaintFile
Represents a file attached to a complaint.

```dart
class ComplaintFile {
  final String id;
  final String pelaporanId;
  final String fileName;
  final String filePath;
  final String fileType;
  final String fileSize;
  final String? description;
  final DateTime uploadedAt;
  final String uploadedBy;
  final String fileUrl;
}
```

#### Complaint
Represents a single complaint/report.

```dart
class Complaint {
  final String id;
  final String kodeLaporan;
  final String title;  // from 'judul'
  final String description;  // from 'deskripsi'
  final String kategori;
  final String alamat;
  final String? lokasiLat;
  final String? lokasiLng;
  final String? foto;
  final String status;
  final String prioritas;
  final String pelaporNama;
  final String pelaporTelepon;
  final String pelaporNik;
  final String pelaporAlamat;
  final String namaKategori;
  final String? fotoUrl;
  final String createdAtFormatted;
  final String updatedAtFormatted;
  final List<ComplaintFile>? files;
  final int filesCount;
  // ... other fields
}
```

#### Pagination
Contains pagination metadata.

```dart
class Pagination {
  final int currentPage;
  final int perPage;
  final int totalRecords;
  final int totalPages;
  final bool hasNext;
  final bool hasPrev;
}
```

#### FiltersApplied
Shows which filters were applied to the query.

```dart
class FiltersApplied {
  final String? status;
  final String? kategori;
  final String? search;
}
```

#### RateLimit
Provides rate limiting information.

```dart
class RateLimit {
  final int remainingMinute;
  final int remainingHour;
}
```

#### ComplaintListResponse
The main response wrapper containing all data.

```dart
class ComplaintListResponse {
  final String status;
  final String message;
  final List<Complaint> pelaporan;
  final Pagination pagination;
  final FiltersApplied filtersApplied;
  final RateLimit rateLimit;
}
```

### 2. API Service (`lib/services/api_service.dart`)

The [`ApiService.getComplaints()`](lib/services/api_service.dart:89) method handles the HTTP request:

```dart
Future<ApiResponse<ComplaintListResponse>> getComplaints({
  int page = 1,
  int limit = 10,
  String? status,
  String? kategori,
  String? search,
}) async {
  // Builds query parameters
  // Makes GET request with proper headers
  // Parses response into ComplaintListResponse
  // Returns ApiResponse wrapper
}
```

### 3. Complaint Service (`lib/services/complaint_service.dart`)

The [`ComplaintService.fetchComplaints()`](lib/services/complaint_service.dart:54) method provides a higher-level interface:

```dart
Future<ApiResponse<ComplaintListResponse>> fetchComplaints({
  int page = 1,
  int limit = 10,
  String? status,
  String? kategori,
  String? search,
}) async {
  // Calls ApiService.getComplaints()
  // Returns result with proper error handling
}
```

## Usage Examples

### Basic Usage

```dart
final service = ComplaintService.instance;
final result = await service.fetchComplaints();

if (result.success && result.data != null) {
  final response = result.data!;
  
  // Access complaints
  for (var complaint in response.pelaporan) {
    print('${complaint.kodeLaporan}: ${complaint.title}');
  }
  
  // Access pagination
  print('Page ${response.pagination.currentPage} of ${response.pagination.totalPages}');
  
  // Access rate limit
  print('Remaining requests: ${response.rateLimit.remainingMinute}/minute');
}
```

### Filtering by Status

```dart
final result = await service.fetchComplaints(
  status: 'LAPOR',
  page: 1,
  limit: 10,
);
```

### Multiple Filters

```dart
final result = await service.fetchComplaints(
  status: 'PROSES',
  kategori: 'infrastruktur',
  search: 'jalan',
  page: 1,
  limit: 20,
);
```

### Pagination

```dart
int currentPage = 1;
final result = await service.fetchComplaints(page: currentPage);

if (result.success && result.data != null) {
  final pagination = result.data!.pagination;
  
  // Check if there's a next page
  if (pagination.hasNext) {
    // Load next page
    final nextResult = await service.fetchComplaints(page: currentPage + 1);
  }
  
  // Check if there's a previous page
  if (pagination.hasPrev) {
    // Load previous page
    final prevResult = await service.fetchComplaints(page: currentPage - 1);
  }
}
```

### Accessing Complaint Files

```dart
final result = await service.fetchComplaints(page: 1, limit: 1);

if (result.success && result.data!.pelaporan.isNotEmpty) {
  final complaint = result.data!.pelaporan.first;
  
  if (complaint.files != null && complaint.files!.isNotEmpty) {
    for (var file in complaint.files!) {
      print('File: ${file.fileName}');
      print('URL: ${file.fileUrl}');
      print('Type: ${file.fileType}');
      print('Size: ${file.fileSize}');
    }
  }
}
```

### Rate Limit Monitoring

```dart
final result = await service.fetchComplaints();

if (result.success && result.data != null) {
  final rateLimit = result.data!.rateLimit;
  
  print('Requests remaining this minute: ${rateLimit.remainingMinute}');
  print('Requests remaining this hour: ${rateLimit.remainingHour}');
  
  // Warning if approaching limit
  if (rateLimit.remainingMinute < 10) {
    print('WARNING: Approaching rate limit!');
  }
}
```

## Widget Example

See [`lib/examples/complaint_list_api_example.dart`](lib/examples/complaint_list_api_example.dart:1) for a complete Flutter widget implementation that demonstrates:

- Fetching complaints with pagination
- Applying filters (status, category, search)
- Displaying rate limit information
- Navigating between pages
- Handling loading and error states

## API Response Fields Mapping

| API Field | Model Property | Type | Description |
|-----------|---------------|------|-------------|
| `id` | `id` | String | Complaint ID |
| `kode_laporan` | `kodeLaporan` | String | Complaint code (e.g., LP202510110007) |
| `judul` | `title` | String | Complaint title |
| `deskripsi` | `description` | String | Complaint description |
| `kategori` | `kategori` | String | Category code |
| `nama_kategori` | `namaKategori` | String | Category display name |
| `alamat` | `alamat` | String | Address |
| `lokasi_lat` | `lokasiLat` | String? | Latitude |
| `lokasi_lng` | `lokasiLng` | String? | Longitude |
| `foto` | `foto` | String? | Photo filename |
| `foto_url` | `fotoUrl` | String? | Photo URL |
| `status` | `status` | String | Status (LAPOR, PROSES, SELESAI) |
| `prioritas` | `prioritas` | String | Priority (RENDAH, SEDANG, TINGGI, URGENT) |
| `pelapor_nama` | `pelaporNama` | String | Reporter name |
| `pelapor_telepon` | `pelaporTelepon` | String | Reporter phone |
| `pelapor_nik` | `pelaporNik` | String | Reporter NIK |
| `pelapor_alamat` | `pelaporAlamat` | String | Reporter address |
| `created_at` | `createdAt` | DateTime | Creation timestamp |
| `updated_at` | `updatedAt` | DateTime | Update timestamp |
| `created_at_formatted` | `createdAtFormatted` | String | Formatted creation date |
| `updated_at_formatted` | `updatedAtFormatted` | String | Formatted update date |
| `files` | `files` | List<ComplaintFile>? | Attached files |
| `files_count` | `filesCount` | int | Number of files |

## Error Handling

The API returns errors in the [`ApiResponse`](lib/services/api_service.dart:6) wrapper:

```dart
class ApiResponse<T> {
  final bool success;
  final T? data;
  final String? error;
}
```

Handle errors like this:

```dart
final result = await service.fetchComplaints();

if (result.success) {
  // Use result.data
} else {
  // Handle error: result.error
  print('Error: ${result.error}');
}
```

## Status Values

- `LAPOR` - Reported/Submitted
- `PROSES` - In Progress
- `SELESAI` - Completed
- Other custom statuses as defined by the API

## Priority Values

- `RENDAH` - Low
- `SEDANG` - Medium
- `TINGGI` - High
- `URGENT` - Urgent

## Testing

To test the implementation:

1. Ensure API configuration is correct in [`lib/config/api_config.dart`](lib/config/api_config.dart:1)
2. Run the example widget from [`lib/examples/complaint_list_api_example.dart`](lib/examples/complaint_list_api_example.dart:1)
3. Use the pagination controls to navigate between pages
4. Apply filters to test filtering functionality
5. Monitor rate limit information in the UI

## Notes

- The API requires authentication via the `X-API-Key` header
- Rate limits are enforced per minute and per hour
- Pagination starts at page 1
- Maximum items per page should be configured appropriately to avoid performance issues
- All file URLs are absolute and can be used directly in widgets
- Date formatting follows the pattern: `DD/MM/YYYY HH:mm`

## Files Modified

1. [`lib/models/complaint.dart`](lib/models/complaint.dart:1) - Added pagination, filters, and rate limit models
2. [`lib/services/api_service.dart`](lib/services/api_service.dart:1) - Updated getComplaints() method
3. [`lib/services/complaint_service.dart`](lib/services/complaint_service.dart:1) - Updated fetchComplaints() method
4. [`lib/screens/complaint_detail_screen.dart`](lib/screens/complaint_detail_screen.dart:1) - Fixed property references
5. [`lib/screens/complaints_list_screen.dart`](lib/screens/complaints_list_screen.dart:1) - Fixed property references

## Files Created

1. [`lib/examples/complaint_list_api_example.dart`](lib/examples/complaint_list_api_example.dart:1) - Complete example implementation
2. `COMPLAINT_LIST_API_README.md` - This documentation