import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/complaint.dart';
import '../models/slider.dart';
import '../models/menu_grid.dart';
import 'session_service.dart';

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

  /// Get list of pelaporan kategori
  Future<ApiResponse<List<Map<String, dynamic>>>> getCategories() async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/pelaporan/kategori');
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);
        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success' &&
            responseData['data'] != null) {
          final data = responseData['data'] as Map<String, dynamic>;
          final List<dynamic> kategori =
              data['kategori'] as List<dynamic>? ?? [];
          final parsed = kategori
              .map<Map<String, dynamic>>(
                  (e) => Map<String, dynamic>.from(e as Map))
              .toList();
          return ApiResponse(success: true, data: parsed);
        }
        return ApiResponse(success: false, error: 'Invalid response format');
      } else {
        return ApiResponse(
            success: false,
            error: 'HTTP ${response.statusCode}: ${response.body}');
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  Map<String, String> get _headers => {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': ApiConfig.origin,
        'Referer': ApiConfig.origin,
        'Cookie': 'krs_session=6egg5h8fo1co8b9lmoroui0pp4es97hb',
        'User-Agent':
            'Mozilla/5.0 (Linux; Android 10; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Mobile Safari/537.36',
        'Accept': 'image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache',
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
    required String lat,
    required String lng,
    required String masId,
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
        'latitude': lat,
        'longitude': lng,
        'masyarakat_id': masId,
      });

      // Add photo files if provided
      if (foto != null && foto.isNotEmpty) {
        print('Adding ${foto.length} photo files to request');
        for (int i = 0; i < foto.length && i < 3; i++) {
          var file = foto[i];
          var fileName = 'foto_${i + 1}.jpg';
          var fieldName =
              'foto_${i + 1}'; // Use different field name for each file
          print('Adding file ${i + 1}: $fileName with field name: $fieldName');
          var multipartFile = await http.MultipartFile.fromPath(
            fieldName,
            file.path,
            filename: fileName,
          );
          request.files.add(multipartFile);
        }
        print('Total files in request: ${request.files.length}');
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
        return ApiResponse(
            success: false, error: 'Profile photo file not found');
      }
      if (!await fotoKtp.exists()) {
        return ApiResponse(
            success: false, error: 'ID card photo file not found');
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
        return ApiResponse(success: false, error: response.body);
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
    String? userId,
  }) async {
    try {
      var queryParams = {
        'page': page.toString(),
        'limit': limit.toString(),
        'masyarakat_id': userId ?? '',
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
          print(complaintListResponse);
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

  Future<ApiResponse<Map<String, dynamic>>> getComplaintHistory(
      String complaintId) async {
    try {
      var uri = Uri.parse(
          '${ApiConfig.baseUrl}/pelaporan/pelaporan_history/$complaintId');

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

  Future<ApiResponse<Map<String, dynamic>>> createComment({
    required String pelaporanId,
    required String comment,
    int? rating,
    required String createdBy,
  }) async {
    try {
      var uri = Uri.parse(
          '${ApiConfig.baseUrl}/pelaporan/$pelaporanId/create_comment');

      var request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers.addAll(_headers);

      // Add form fields
      request.fields['comment'] = comment;
      request.fields['created_by'] = createdBy;

      // Add rating if provided
      if (rating != null) {
        request.fields['rating'] = rating.toString();
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

  /// Submit emergency report to API
  Future<ApiResponse<Map<String, dynamic>>> postEmergencyReport({
    required String kategori,
    required String alamat,
    required String pelaporNama,
    required String pelaporTelepon,
    required String pelaporNik,
    required String pelaporAlamat,
    List<File>? foto,
    required String latitude,
    required String longitude,
    required String masId,
  }) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/pelaporan/create');
      var request = http.MultipartRequest('POST', uri);

      // Add headers
      request.headers.addAll(_headers);

      // Add form fields with emergency-specific values
      request.fields.addAll({
        'judul': 'Darurat',
        'deskripsi': 'Laporan darurat - ${kategori}',
        'alamat': alamat,
        'kategori': kategori,
        'pelapor_nama': pelaporNama,
        'pelapor_telepon': pelaporTelepon,
        'pelapor_nik': pelaporNik,
        'pelapor_alamat': pelaporAlamat,
        'jenis_pelaporan': 'DARURAT',
        'latitude': latitude,
        'longitude': longitude,
        'masyarakat_id': masId,
      });

      // Add photo files if provided
      if (foto != null && foto.isNotEmpty) {
        print('Adding ${foto.length} photo files to emergency report request');
        for (int i = 0; i < foto.length && i < 3; i++) {
          var file = foto[i];
          var fileName = 'foto_${i + 1}.jpg';
          var fieldName =
              'foto_${i + 1}'; // Use different field name for each file
          print('Adding file ${i + 1}: $fileName with field name: $fieldName');
          var multipartFile = await http.MultipartFile.fromPath(
            fieldName,
            file.path,
            filename: fileName,
          );
          request.files.add(multipartFile);
        }
        print(
            'Total files in emergency report request: ${request.files.length}');
      }

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200 || response.statusCode == 201) {
        var responseData = json.decode(response.body);
        print(responseData);
        return ApiResponse(success: true, data: responseData);
      } else {
        print(response.body);
        var resError = json.decode(response.body);
        return ApiResponse(success: false, error: resError['message']);
      }
    } catch (e) {
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  /// Get pengaturan data from API
  Future<ApiResponse<Map<String, dynamic>>> getPengaturan() async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/pengaturan');
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);

        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success') {
          return ApiResponse(success: true, data: responseData['data']);
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

  /// Get pengaturan data from API and save to session
  Future<ApiResponse<Map<String, dynamic>>>
      getPengaturanAndSaveToSession() async {
    try {
      // First, get the data from API
      final apiResponse = await getPengaturan();

      if (apiResponse.success && apiResponse.data != null) {
        // Save all data to session
        final sessionService = SessionService.instance;

        // Save each field individually for easier access
        if (apiResponse.data!['nama_situs'] != null) {
          await sessionService.saveToSession(
              'nama_situs', apiResponse.data!['nama_situs']);
        }

        if (apiResponse.data!['tagline'] != null) {
          await sessionService.saveToSession(
              'tagline', apiResponse.data!['tagline']);
        }

        if (apiResponse.data!['logo'] != null) {
          await sessionService.saveToSession('logo', apiResponse.data!['logo']);
        }

        if (apiResponse.data!['favicon'] != null) {
          await sessionService.saveToSession(
              'favicon', apiResponse.data!['favicon']);
        }

        if (apiResponse.data!['splashscreen_image'] != null) {
          final splashImage =
              apiResponse.data!['splashscreen_image'].toString();
          // Validate the URL before saving
          if (splashImage.isNotEmpty &&
              (splashImage.startsWith('http://') ||
                  splashImage.startsWith('https://'))) {
            await sessionService.saveToSession(
                'splashscreen_image', splashImage);
            debugPrint('Splash screen image saved to session: $splashImage');
          } else {
            debugPrint('Invalid splash image URL format: $splashImage');
            // Don't save invalid URL to session
          }
        }
        if (apiResponse.data!['background_image'] != null) {
          final splashImage = apiResponse.data!['background_image'].toString();
          // Validate the URL before saving
          if (splashImage.isNotEmpty &&
              (splashImage.startsWith('http://') ||
                  splashImage.startsWith('https://'))) {
            await sessionService.saveToSession('background_image', splashImage);
            debugPrint('Splash screen image saved to session: $splashImage');
          } else {
            debugPrint('Invalid splash image URL format: $splashImage');
            // Don't save invalid URL to session
          }
        }

        // Also save the complete data object as a backup
        await sessionService.saveToSession(
            'pengaturan_data', apiResponse.data!);

        debugPrint('Pengaturan data saved to session successfully');

        return ApiResponse(success: true, data: apiResponse.data);
      } else {
        return ApiResponse(
            success: false,
            error: apiResponse.error ?? 'Failed to get pengaturan data');
      }
    } catch (e) {
      debugPrint('Error in getPengaturanAndSaveToSession: $e');
      return ApiResponse(success: false, error: 'Network error: $e');
    }
  }

  /// Get active sliders from API
  Future<ApiResponse<SliderResponse>> getActiveSliders({
    required String masyarakatId,
    required String fcmToken,
    required String deviceId,
  }) async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/slider/active');
      var request = http.MultipartRequest('GET', uri);

      // Add headers
      request.headers.addAll(_headers);

      // Add form fields
      request.fields['masyarakat_id'] = masyarakatId;
      request.fields['fcm_token'] = fcmToken;
      request.fields['device_id'] = deviceId;

      var streamedResponse = await request.send();
      var response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);

        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success') {
          final sliderResponse = SliderResponse.fromJson(responseData);
          return ApiResponse(success: true, data: sliderResponse);
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

  /// Get active menu grid from API
  Future<ApiResponse<MenuGridResponse>> getActiveMenuGrid() async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/menu_grid/active');
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);

        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success') {
          final menuGridResponse =
              MenuGridResponse.fromJson(responseData['data']);
          return ApiResponse(success: true, data: menuGridResponse);
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

  /// Get user profile from API
  Future<ApiResponse<Map<String, dynamic>>> getUserProfile() async {
    try {
      var uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/profile');
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);

        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success') {
          return ApiResponse(success: true, data: responseData['data']);
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

  /// Validate NIK to check if already registered
  Future<ApiResponse<Map<String, dynamic>>> validateNIK(String nik) async {
    try {
      var uri =
          Uri.parse('${ApiConfig.baseUrl}/masyarakat/validate-nik?nik=$nik');
      var response = await http.get(uri, headers: _headers);

      if (response.statusCode == 200) {
        var responseData = json.decode(response.body);
        if (responseData is Map<String, dynamic> &&
            responseData['status'] == 'success') {
          return ApiResponse(success: true, data: responseData['data']);
        }
        return ApiResponse(success: false, error: 'NIK validation failed');
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
