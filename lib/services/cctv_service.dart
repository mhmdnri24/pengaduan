import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/cctv.dart';

class CctvService {
  static CctvService? _instance;

  CctvService._internal();

  static CctvService get instance {
    _instance ??= CctvService._internal();
    return _instance!;
  }

  /// Get CCTV cameras list from API
  Future<CctvResponse> getCctvCameras({
    String? domain,
    int page = 1,
    int limit = 10,
  }) async {
    try {
      // Use the provided domain or default to lubuklinggaukota.go.id
      final url = domain ?? 'lubuklinggaukota.go.id';
      var uri = Uri.parse(
          'https://i-see.iconpln.co.id/backend/api/View/embedlink?url=$url');

      var response = await http.get(uri);
      // print('CCTV Service Response: ${response.body}');
      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);
        var fullResponse = CctvResponse.fromJson(responseData);

        // Simulate pagination by slicing the results
        // This is a workaround since the API doesn't support pagination yet
        if (fullResponse.success && fullResponse.cameras.isNotEmpty) {
          final startIndex = (page - 1) * limit;
          final endIndex = startIndex + limit;

          List<CctvCamera> paginatedCameras = [];
          if (startIndex < fullResponse.cameras.length) {
            paginatedCameras = fullResponse.cameras.sublist(
              startIndex,
              endIndex > fullResponse.cameras.length
                  ? fullResponse.cameras.length
                  : endIndex,
            );
          }

          // Calculate total pages
          final totalPages = (fullResponse.cameras.length / limit).ceil();

          return CctvResponse(
            success: true,
            cameras: paginatedCameras,
            sites: fullResponse.sites,
            message: fullResponse.message,
            page: page,
            maxPage: totalPages,
          );
        }

        return fullResponse;
      } else {
        return CctvResponse(
          success: false,
          cameras: [],
          sites: [],
          message: 'HTTP ${response.statusCode}: ${response.body}',
        );
      }
    } catch (e) {
      return CctvResponse(
        success: false,
        cameras: [],
        sites: [],
        message: 'Network error: $e',
      );
    }
  }

  /// Get CCTV cameras with custom headers for authentication
  Future<CctvResponse> getCctvCamerasWithAuth({
    String? domain,
    int page = 1,
    int limit = 10,
  }) async {
    try {
      final url = domain ?? 'lubuklinggaukota.go.id';
      var uri = Uri.parse(
          'https://i-see.iconpln.co.id/backend/api/View/embedlink?url=$url');

      var response = await http.get(uri, headers: _headers);
      print('CCTV Service Response: ${response.body}');
      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);
        var fullResponse = CctvResponse.fromJson(responseData);

        // Simulate pagination by slicing the results
        if (fullResponse.success && fullResponse.cameras.isNotEmpty) {
          final startIndex = (page - 1) * limit;
          final endIndex = startIndex + limit;

          List<CctvCamera> paginatedCameras = [];
          if (startIndex < fullResponse.cameras.length) {
            paginatedCameras = fullResponse.cameras.sublist(
              startIndex,
              endIndex > fullResponse.cameras.length
                  ? fullResponse.cameras.length
                  : endIndex,
            );
          }

          final totalPages = (fullResponse.cameras.length / limit).ceil();

          return CctvResponse(
            success: true,
            cameras: paginatedCameras,
            sites: fullResponse.sites,
            message: fullResponse.message,
            page: page,
            maxPage: totalPages,
          );
        }

        return fullResponse;
      } else {
        return CctvResponse(
          success: false,
          cameras: [],
          sites: [],
          message: 'HTTP ${response.statusCode}: ${response.body}',
        );
      }
    } catch (e) {
      return CctvResponse(
        success: false,
        cameras: [],
        sites: [],
        message: 'Network error: $e',
      );
    }
  }

  Map<String, String> get _headers => {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': ApiConfig.origin,
        'Referer': ApiConfig.origin,
      };
}
