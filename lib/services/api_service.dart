import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/complaint.dart';

class ApiResponse<T> {
  final bool success;
  final T? data;
  final String? error;

  ApiResponse({required this.success, this.data, this.error});
}

class ApiService {
  static ApiService? _instance;

  ApiService._internal();

  static ApiService get instance {
    _instance ??= ApiService._internal();
    return _instance!;
  }

  Map<String, String> get _headers => {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': ApiConfig.origin,
        'Referer': ApiConfig.origin,
        'Cookie': 'krs_session=6egg5h8fo1co8b9lmoroui0pp4es97hb',
      };

  Future<ApiResponse<Map<String, dynamic>>> postComplaint({
    required String judul,
    required String deskripsi,
    required String alamat,
    required String kategori,
    required String pelaporNama,
    required String pelaporTelepon,
    required String pelaporNik,
    required String pelaporAlamat,
    List<File>? foto,
  }) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/pelaporan/create');
      var request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers.addAll(_headers);

      // Add form fields
      request.fields.addAll({
        'judul': judul,
        'deskripsi': deskripsi,
        'alamat': alamat,
        'kategori': kategori,
        'pelapor_nama': pelaporNama,
        'pelapor_telepon': pelaporTelepon,
        'pelapor_nik': pelaporNik,
        'pelapor_alamat': pelaporAlamat,
      });

      // Add photo files if provided
      if (foto != null && foto.isNotEmpty) {
        for (int i = 0; i < foto.length && i < 3; i++) {
          var file = foto[i];
          var fileName = 'foto_${i + 1}.jpg';
          var multipartFile = await http.MultipartFile.fromPath(
            'foto',
            file.path,
            filename: fileName,
          );
          request.files.add(multipartFile);
        }
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        var responseData = json.decode(response.body);
        return ApiResponse(success: true, data: responseData);
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> registerUser({
    required String namaLengkap,
    required String nik,
    required String noTelpon,
    required File fotoProfil,
    required File fotoKtp,
  }) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/register');
      var request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers.addAll(_headers);

      // Add form fields
      request.fields.addAll({
        'nama_lengkap': namaLengkap,
        'nik': nik,
        'no_telpon': noTelpon,
      });

      // Validate files exist
      if (!await fotoProfil.exists()) {
        return ApiResponse(success: false, error: 'Profile photo file not found');
      }
      if (!await fotoKtp.exists()) {
        return ApiResponse(success: false, error: 'ID card photo file not found');
      }
      
      print('Profile photo path: ${fotoProfil.path}');
      print('ID card photo path: ${fotoKtp.path}');
      print('Profile photo exists: ${await fotoProfil.exists()}');
      print('ID card photo exists: ${await fotoKtp.exists()}');

      // Add profile photo
      var profileMultipartFile = await http.MultipartFile.fromPath(
        'foto_profil',
        fotoProfil.path,
        filename: 'foto_profil.jpg',
      );
      request.files.add(profileMultipartFile);

      // Add ID card photo
      var ktpMultipartFile = await http.MultipartFile.fromPath(
        'foto_ktp',
        fotoKtp.path,
        filename: 'foto_ktp.jpg',
      );
      request.files.add(ktpMultipartFile);

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);
      
      print('Status Code: ${response.statusCode}');
      print('Response Body: ${response.body}');
      print('Request Headers: ${request.headers}');
      print('Request Fields: ${request.fields}');
      // print('Request Files: ${request.files.map((f) => f.field + ': ' + f.filename).toList()}');
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        var responseData = json.decode(response.body);
        return ApiResponse(success: true, data: responseData);
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  Future<ApiResponse<ComplaintListResponse>> getComplaints({
    int page = 1,
    int limit = 10,
    String? status,
    String? kategori,
    String? search,
  }) async {
    try {
      var queryParams = {
        'page': page.toString(),
        'limit': limit.toString(),
      };

      if (status != null && status.isNotEmpty) {
        queryParams['status'] = status;
      }

      if (kategori != null && kategori.isNotEmpty) {
        queryParams['kategori'] = kategori;
      }

      if (search != null && search.isNotEmpty) {
        queryParams['search'] = search;
      }

      var uri = Uri.parse('${ApiConfig.baseUrl}/pelaporan').replace(
        queryParameters: queryParams,
      );

      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);

        if (responseData is Map<String, dynamic>) {
          // Parse the complete response structure
          var complaintListResponse =
              ComplaintListResponse.fromJson(responseData);
          return ApiResponse(success: true, data: complaintListResponse);
        } else {
          return ApiResponse(success: false, error: 'Invalid response format');
        }
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> getComplaintHistory(String complaintId) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/pelaporan/pelaporan_history/$complaintId');
      
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);
        return ApiResponse(success: true, data: responseData);
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> registerDevice({
    required String masyarakatId,
    required String fcmToken,
    required String deviceId,
  }) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/device/insert_or_update');
      
      var request = http.MultipartRequest('POST', uri);
      
      // Add headers
      request.headers.addAll(_headers);
      
      // Add form fields
      request.fields['masyarakat_id'] = masyarakatId;
      request.fields['fcm_token'] = fcmToken;
      request.fields['device_id'] = deviceId;

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        var responseData = json.decode(response.body);
        return ApiResponse(success: true, data: responseData);
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }
}
