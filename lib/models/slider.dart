class SliderItem {
  final String id;
  final String title;
  final String description;
  final String fileName;
  final String status;
  final String createdAt;
  final String updatedAt;
  final String statusFormatted;
  final String createdAtFormatted;
  final String updatedAtFormatted;
  final String fileUrl;

  SliderItem({
    required this.id,
    required this.title,
    required this.description,
    required this.fileName,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    required this.statusFormatted,
    required this.createdAtFormatted,
    required this.updatedAtFormatted,
    required this.fileUrl,
  });

  factory SliderItem.fromJson(Map<String, dynamic> json) {
    return SliderItem(
      id: json['id']?.toString() ?? '',
      title: json['slider_judul'] ?? '',
      description: json['slider_deskripsi'] ?? '',
      fileName: json['slider_file'] ?? '',
      status: json['slider_status'] ?? '',
      createdAt: json['created_at'] ?? '',
      updatedAt: json['updated_at'] ?? '',
      statusFormatted: json['status_formatted'] ?? '',
      createdAtFormatted: json['created_at_formatted'] ?? '',
      updatedAtFormatted: json['updated_at_formatted'] ?? '',
      fileUrl: json['slider_file_url'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'slider_judul': title,
      'slider_deskripsi': description,
      'slider_file': fileName,
      'slider_status': status,
      'created_at': createdAt,
      'updated_at': updatedAt,
      'status_formatted': statusFormatted,
      'created_at_formatted': createdAtFormatted,
      'updated_at_formatted': updatedAtFormatted,
      'slider_file_url': fileUrl,
    };
  }
}

class SliderResponse {
  final List<SliderItem> sliders;
  final int total;

  SliderResponse({
    required this.sliders,
    required this.total,
  });

  factory SliderResponse.fromJson(Map<String, dynamic> json) {
    final sliderData = json['data'] as Map<String, dynamic>? ?? {};
    final sliderList = sliderData['slider'] as List<dynamic>? ?? [];

    final sliders = sliderList
        .map((slider) => SliderItem.fromJson(slider as Map<String, dynamic>))
        .toList();

    return SliderResponse(
      sliders: sliders,
      total: sliderData['total'] as int? ?? 0,
    );
  }
}
