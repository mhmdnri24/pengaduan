class CctvCamera {
  final String id;
  final String name;
  final String location;
  final String embedUrl;
  final String? thumbnailUrl;
  final bool isActive;
  final String? camId;
  final bool? isPTZ;

  CctvCamera({
    required this.id,
    required this.name,
    required this.location,
    required this.embedUrl,
    this.thumbnailUrl,
    this.isActive = true,
    this.camId,
    this.isPTZ,
  });

  factory CctvCamera.fromJson(Map<String, dynamic> json) {
    return CctvCamera(
      id: json['cameraId']?.toString() ?? json['id']?.toString() ?? '',
      name: json['cameraName'] ??
          json['name'] ??
          json['title'] ??
          'Unknown Camera',
      location: json['location'] ?? json['address'] ?? 'Unknown Location',
      embedUrl: json['streamingURL'] ??
          json['embed_url'] ??
          json['url'] ??
          json['stream_url'] ??
          '',
      thumbnailUrl: json['thumbnail_url'],
      isActive: json['is_active'] ?? json['status'] == 'active' ?? true,
      camId: json['camId'],
      isPTZ: json['isPTZ'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'cameraId': id,
      'cameraName': name,
      'location': location,
      'streamingURL': embedUrl,
      'thumbnail_url': thumbnailUrl,
      'is_active': isActive,
      'camId': camId,
      'isPTZ': isPTZ,
    };
  }
}

class CctvArea {
  final String id;
  final String name;
  final List<CctvCamera> cameras;

  CctvArea({
    required this.id,
    required this.name,
    required this.cameras,
  });

  factory CctvArea.fromJson(Map<String, dynamic> json) {
    List<CctvCamera> cameras = [];

    if (json['cameraTree'] != null) {
      final cameraList = json['cameraTree'] as List;
      cameras = cameraList.map((item) => CctvCamera.fromJson(item)).toList();
    }

    return CctvArea(
      id: json['areaId']?.toString() ?? '',
      name: json['areaName'] ?? json['name'] ?? 'Unknown Area',
      cameras: cameras,
    );
  }
}

class CctvSite {
  final String id;
  final String name;
  final List<CctvArea> areas;

  CctvSite({
    required this.id,
    required this.name,
    required this.areas,
  });

  factory CctvSite.fromJson(Map<String, dynamic> json) {
    List<CctvArea> areas = [];

    if (json['areaTree'] != null) {
      final areaList = json['areaTree'] as List;
      areas = areaList.map((item) => CctvArea.fromJson(item)).toList();
    }

    return CctvSite(
      id: json['siteId']?.toString() ?? '',
      name: json['siteName'] ?? json['name'] ?? 'Unknown Site',
      areas: areas,
    );
  }

  List<CctvCamera> get allCameras {
    List<CctvCamera> allCameras = [];
    for (var area in areas) {
      allCameras.addAll(area.cameras);
    }
    return allCameras;
  }
}

class CctvResponse {
  final bool success;
  final List<CctvCamera> cameras;
  final List<CctvSite> sites;
  final String? message;
  final int? page;
  final int? maxPage;

  CctvResponse({
    required this.success,
    required this.cameras,
    required this.sites,
    this.message,
    this.page,
    this.maxPage,
  });

  factory CctvResponse.fromJson(Map<String, dynamic> json) {
    List<CctvCamera> cameras = [];
    List<CctvSite> sites = [];

    // Check for success using both possible field names
    final isSuccess = json['status'] == true || json['success'] == true;

    if (isSuccess) {
      final dataList = json['data'] as List?;

      if (dataList != null) {
        // Parse sites and extract all cameras
        for (var siteData in dataList) {
          final site = CctvSite.fromJson(siteData);
          sites.add(site);
          cameras.addAll(site.allCameras);
        }
      }
    }

    return CctvResponse(
      success: isSuccess,
      cameras: cameras,
      sites: sites,
      message: json['message'],
      page: json['page'],
      maxPage: json['maxPage'],
    );
  }
}
