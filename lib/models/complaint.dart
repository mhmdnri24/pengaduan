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

  ComplaintFile({
    required this.id,
    required this.pelaporanId,
    required this.fileName,
    required this.filePath,
    required this.fileType,
    required this.fileSize,
    this.description,
    required this.uploadedAt,
    required this.uploadedBy,
    required this.fileUrl,
  });

  factory ComplaintFile.fromJson(Map<String, dynamic> json) {
    return ComplaintFile(
      id: json['id'] ?? '',
      pelaporanId: json['pelaporan_id'] ?? '',
      fileName: json['file_name'] ?? '',
      filePath: json['file_path'] ?? '',
      fileType: json['file_type'] ?? '',
      fileSize: json['file_size'] ?? '',
      description: json['description'],
      uploadedAt: DateTime.parse(
          json['uploaded_at'] ?? DateTime.now().toIso8601String()),
      uploadedBy: json['uploaded_by'] ?? '',
      fileUrl: json['file_url'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'pelaporan_id': pelaporanId,
      'file_name': fileName,
      'file_path': filePath,
      'file_type': fileType,
      'file_size': fileSize,
      'description': description,
      'uploaded_at': uploadedAt.toIso8601String(),
      'uploaded_by': uploadedBy,
      'file_url': fileUrl,
    };
  }
}

class Complaint {
  final String id;
  final String kodeLaporan;
  final String title;
  final String description;
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
  final String? masyarakatId;
  final String? unitkerjaId;
  final String? operatorId;
  final String? tanggalSelesai;
  final DateTime createdAt;
  final DateTime updatedAt;
  final String? createdBy;
  final String? updatedBy;
  final String namaKategori;
  final String? fotoUrl;
  final String createdAtFormatted;
  final String updatedAtFormatted;
  final List<ComplaintFile>? files;
  final int filesCount;

  Complaint({
    required this.id,
    required this.kodeLaporan,
    required this.title,
    required this.description,
    required this.kategori,
    required this.alamat,
    this.lokasiLat,
    this.lokasiLng,
    this.foto,
    required this.status,
    required this.prioritas,
    required this.pelaporNama,
    required this.pelaporTelepon,
    required this.pelaporNik,
    required this.pelaporAlamat,
    this.masyarakatId,
    this.unitkerjaId,
    this.operatorId,
    this.tanggalSelesai,
    required this.createdAt,
    required this.updatedAt,
    this.createdBy,
    this.updatedBy,
    required this.namaKategori,
    this.fotoUrl,
    required this.createdAtFormatted,
    required this.updatedAtFormatted,
    this.files,
    required this.filesCount,
  });

  factory Complaint.fromJson(Map<String, dynamic> json) {
    return Complaint(
      id: json['id'] ?? '',
      kodeLaporan: json['kode_laporan'] ?? '',
      title: json['judul'] ?? '',
      description: json['deskripsi'] ?? '',
      kategori: json['kategori'] ?? '',
      alamat: json['alamat'] ?? '',
      lokasiLat: json['lokasi_lat'],
      lokasiLng: json['lokasi_lng'],
      foto: json['foto'],
      status: json['status'] ?? 'LAPOR',
      prioritas: json['prioritas'] ?? 'SEDANG',
      pelaporNama: json['pelapor_nama'] ?? '',
      pelaporTelepon: json['pelapor_telepon'] ?? '',
      pelaporNik: json['pelapor_nik'] ?? '',
      pelaporAlamat: json['pelapor_alamat'] ?? '',
      masyarakatId: json['masyarakat_id'],
      unitkerjaId: json['unitkerja_id'],
      operatorId: json['operator_id'],
      tanggalSelesai: json['tanggal_selesai'],
      createdAt: DateTime.parse(
          json['created_at'] ?? DateTime.now().toIso8601String()),
      updatedAt: DateTime.parse(
          json['updated_at'] ?? DateTime.now().toIso8601String()),
      createdBy: json['created_by'],
      updatedBy: json['updated_by'],
      namaKategori: json['nama_kategori'] ?? '',
      fotoUrl: json['foto_url'],
      createdAtFormatted: json['created_at_formatted'] ?? '',
      updatedAtFormatted: json['updated_at_formatted'] ?? '',
      files: json['files'] != null
          ? (json['files'] as List<dynamic>)
              .map((file) => ComplaintFile.fromJson(file))
              .toList()
          : null,
      filesCount: json['files_count'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'kode_laporan': kodeLaporan,
      'judul': title,
      'deskripsi': description,
      'kategori': kategori,
      'alamat': alamat,
      'lokasi_lat': lokasiLat,
      'lokasi_lng': lokasiLng,
      'foto': foto,
      'status': status,
      'prioritas': prioritas,
      'pelapor_nama': pelaporNama,
      'pelapor_telepon': pelaporTelepon,
      'pelapor_nik': pelaporNik,
      'pelapor_alamat': pelaporAlamat,
      'masyarakat_id': masyarakatId,
      'unitkerja_id': unitkerjaId,
      'operator_id': operatorId,
      'tanggal_selesai': tanggalSelesai,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'created_by': createdBy,
      'updated_by': updatedBy,
      'nama_kategori': namaKategori,
      'foto_url': fotoUrl,
      'created_at_formatted': createdAtFormatted,
      'updated_at_formatted': updatedAtFormatted,
      'files': files?.map((file) => file.toJson()).toList(),
      'files_count': filesCount,
    };
  }
}

class Pagination {
  final int currentPage;
  final int perPage;
  final int totalRecords;
  final int totalPages;
  final bool hasNext;
  final bool hasPrev;

  Pagination({
    required this.currentPage,
    required this.perPage,
    required this.totalRecords,
    required this.totalPages,
    required this.hasNext,
    required this.hasPrev,
  });

  factory Pagination.fromJson(Map<String, dynamic> json) {
    return Pagination(
      currentPage: json['current_page'] ?? 1,
      perPage: json['per_page'] ?? 10,
      totalRecords: json['total_records'] ?? 0,
      totalPages: json['total_pages'] ?? 1,
      hasNext: json['has_next'] ?? false,
      hasPrev: json['has_prev'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'current_page': currentPage,
      'per_page': perPage,
      'total_records': totalRecords,
      'total_pages': totalPages,
      'has_next': hasNext,
      'has_prev': hasPrev,
    };
  }
}

class FiltersApplied {
  final String? status;
  final String? kategori;
  final String? search;

  FiltersApplied({
    this.status,
    this.kategori,
    this.search,
  });

  factory FiltersApplied.fromJson(Map<String, dynamic> json) {
    return FiltersApplied(
      status: json['status'],
      kategori: json['kategori'],
      search: json['search'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'status': status,
      'kategori': kategori,
      'search': search,
    };
  }
}

class RateLimit {
  final int remainingMinute;
  final int remainingHour;

  RateLimit({
    required this.remainingMinute,
    required this.remainingHour,
  });

  factory RateLimit.fromJson(Map<String, dynamic> json) {
    return RateLimit(
      remainingMinute: json['remaining_minute'] ?? 0,
      remainingHour: json['remaining_hour'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'remaining_minute': remainingMinute,
      'remaining_hour': remainingHour,
    };
  }
}

class ComplaintListResponse {
  final String status;
  final String message;
  final List<Complaint> pelaporan;
  final Pagination pagination;
  final FiltersApplied filtersApplied;
  final RateLimit rateLimit;

  ComplaintListResponse({
    required this.status,
    required this.message,
    required this.pelaporan,
    required this.pagination,
    required this.filtersApplied,
    required this.rateLimit,
  });

  factory ComplaintListResponse.fromJson(Map<String, dynamic> json) {
    var data = json['data'] as Map<String, dynamic>;

    return ComplaintListResponse(
      status: json['status'] ?? 'success',
      message: json['message'] ?? '',
      pelaporan: (data['pelaporan'] as List<dynamic>)
          .map((item) => Complaint.fromJson(item as Map<String, dynamic>))
          .toList(),
      pagination:
          Pagination.fromJson(data['pagination'] as Map<String, dynamic>),
      filtersApplied: FiltersApplied.fromJson(
          data['filters_applied'] as Map<String, dynamic>),
      rateLimit: RateLimit.fromJson(json['rate_limit'] as Map<String, dynamic>),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'status': status,
      'message': message,
      'data': {
        'pelaporan': pelaporan.map((complaint) => complaint.toJson()).toList(),
        'pagination': pagination.toJson(),
        'filters_applied': filtersApplied.toJson(),
      },
      'rate_limit': rateLimit.toJson(),
    };
  }
}
