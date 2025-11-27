class Pengumuman {
  final String id;
  final String judul;
  final String deskripsi;
  final String gambar;
  final String status;
  final String createdAt;
  final String updatedAt;
  final String statusFormatted;
  final String createdAtFormatted;
  final String updatedAtFormatted;
  final String gambarUrl;

  Pengumuman({
    required this.id,
    required this.judul,
    required this.deskripsi,
    required this.gambar,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    required this.statusFormatted,
    required this.createdAtFormatted,
    required this.updatedAtFormatted,
    required this.gambarUrl,
  });

  factory Pengumuman.fromJson(Map<String, dynamic> json) {
    return Pengumuman(
      id: json['id']?.toString() ?? '',
      judul: json['judul']?.toString() ?? '',
      deskripsi: json['deskripsi']?.toString() ?? '',
      gambar: json['gambar']?.toString() ?? '',
      status: json['status']?.toString() ?? '',
      createdAt: json['created_at']?.toString() ?? '',
      updatedAt: json['updated_at']?.toString() ?? '',
      statusFormatted: json['status_formatted']?.toString() ?? '',
      createdAtFormatted: json['created_at_formatted']?.toString() ?? '',
      updatedAtFormatted: json['updated_at_formatted']?.toString() ?? '',
      gambarUrl: json['gambar_url']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'judul': judul,
      'deskripsi': deskripsi,
      'gambar': gambar,
      'status': status,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'status_formatted': statusFormatted,
      'created_at_formatted': createdAtFormatted,
      'updated_at_formatted': updatedAtFormatted,
      'gambar_url': gambarUrl,
    };
  }
}

class PengumumanResponse {
  final List<Pengumuman> pengumuman;
  final int total;

  PengumumanResponse({
    required this.pengumuman,
    required this.total,
  });

  factory PengumumanResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as Map<String, dynamic>? ?? {};
    final pengumumanList = data['pengumuman'] as List<dynamic>? ?? [];
    
    return PengumumanResponse(
      pengumuman: pengumumanList
          .map((item) => Pengumuman.fromJson(item as Map<String, dynamic>))
          .toList(),
      total: (data['total'] as num?)?.toInt() ?? 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'data': {
        'pengumuman': pengumuman.map((p) => p.toJson()).toList(),
        'total': total,
      },
    };
  }
}
