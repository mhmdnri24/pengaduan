// lib/controllers/landing_controller.dart
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart';

import '../config/api_config.dart';

class SendOtpResult {
  final bool success;
  final String message;

  SendOtpResult(this.success, this.message);
}

class VerifyOtpResult {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  VerifyOtpResult(this.success, this.message, [this.data]);
}

class LandingController {
  final TextEditingController nikController = TextEditingController();
  bool isSending = false;
  final _secureStorage = const FlutterSecureStorage();

  // Helpers that gracefully handle the case where the secure storage plugin
  // isn't registered (MissingPluginException). When the plugin is missing
  // we fall back to SharedPreferences so the app doesn't crash during
  // development (e.g. after a hot-reload when native plugins haven't been
  // fully registered).
  Future<void> _writeSecure(String key, String? value) async {
    try {
      if (value == null) {
        await _secureStorage.delete(key: key);
      } else {
        await _secureStorage.write(key: key, value: value);
      }
    } on MissingPluginException catch (e) {
      debugPrint(
          'Secure storage not available, falling back to prefs (MissingPlugin): $e');
      final prefs = await SharedPreferences.getInstance();
      if (value == null) {
        await prefs.remove(key);
      } else {
        await prefs.setString(key, value);
      }
    } on PlatformException catch (e) {
      debugPrint(
          'Secure storage channel error, falling back to prefs (PlatformException): $e');
      final prefs = await SharedPreferences.getInstance();
      if (value == null) {
        await prefs.remove(key);
      } else {
        await prefs.setString(key, value);
      }
    } catch (e) {
      // Generic fallback for any other exceptions during platform channel calls.
      debugPrint('Secure storage write failed, falling back to prefs: $e');
      final prefs = await SharedPreferences.getInstance();
      if (value == null) {
        await prefs.remove(key);
      } else {
        await prefs.setString(key, value);
      }
    }
  }

  Future<String?> _readSecure(String key) async {
    try {
      return await _secureStorage.read(key: key);
    } on MissingPluginException catch (e) {
      debugPrint(
          'Secure storage not available, reading from prefs (MissingPlugin): $e');
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(key);
    } on PlatformException catch (e) {
      debugPrint(
          'Secure storage channel error, reading from prefs (PlatformException): $e');
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(key);
    } catch (e) {
      debugPrint('Secure storage read failed, reading from prefs: $e');
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(key);
    }
  }

  Future<void> _deleteSecure(String key) async {
    try {
      await _secureStorage.delete(key: key);
    } on MissingPluginException catch (e) {
      debugPrint(
          'Secure storage not available, deleting from prefs (MissingPlugin): $e');
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(key);
    } on PlatformException catch (e) {
      debugPrint(
          'Secure storage channel error, deleting from prefs (PlatformException): $e');
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(key);
    } catch (e) {
      debugPrint('Secure storage delete failed, deleting from prefs: $e');
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(key);
    }
  }

  /// Sends an OTP request to the server.
  ///
  /// Does NOT use or depend on a BuildContext. Instead it returns a
  /// [SendOtpResult] so the caller (UI) can show SnackBars / navigation
  /// while respecting mounted/context rules.
  Future<SendOtpResult> sendOtp(
      VoidCallback onStart, VoidCallback onDone) async {
    final nik = nikController.text.trim();
    if (nik.isEmpty) {
      return SendOtpResult(false, 'Masukkan NIK terlebih dahulu');
    }
    if (kDebugMode) {
      debugPrint('debug: masuk verifyOtp $nik');
    }
    onStart();

    try {
      // Logging for debugging - replace with a proper logger in prod
      // ignore: avoid_print
      print('Sending OTP to $nik');

      final uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/login');
      final resp = await http.post(
        uri,
        headers: {
          'X-API-Key': ApiConfig.apiKey,
          'Origin': 'https://dashboard.nusakoding.com',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: {
          'nik': nik,
        },
        encoding: Encoding.getByName('utf-8'),
      );

      // ignore: avoid_print
      print('Status: ${resp.statusCode}');
      // ignore: avoid_print
      print('Body: ${resp.body}');

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        return SendOtpResult(true, 'OTP dikirim ke NIK $nik');
      }

      return SendOtpResult(
          false, 'Gagal mengirim OTP: ${resp.statusCode} - ${resp.body}');
    } catch (e) {
      return SendOtpResult(false, 'Terjadi kesalahan: $e');
    } finally {
      onDone();
    }
  }

  /// Verify the OTP using multipart/form-data to match the curl example.
  Future<VerifyOtpResult> verifyOtp(
      String otp, VoidCallback onStart, VoidCallback onDone) async {
    final nik = nikController.text.trim();
    if (nik.isEmpty || otp.trim().isEmpty) {
      return VerifyOtpResult(false, 'NIK dan OTP wajib diisi');
    }

    onStart();

    try {
      final uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/verify-otp');

      final req = http.MultipartRequest('POST', uri);
      req.headers.addAll({
        'X-API-Key': ApiConfig.apiKey,
        'Origin': 'https://dashboard.nusakoding.com',
      });
      // Add form fields
      req.fields['nik'] = nik;
      req.fields['otp'] = otp;

      final streamed = await req.send();
      final respBody = await streamed.stream.bytesToString();
      final status = streamed.statusCode;

      // try decoding JSON
      final Map<String, dynamic> jsonResp = respBody.isNotEmpty
          ? jsonDecode(respBody) as Map<String, dynamic>
          : {};
      print(jsonResp);
      if ((status == 200 || status == 201) && jsonResp['status'] == 'success') {
        final data = jsonResp['data'] as Map<String, dynamic>?;
        print(data);
        if (data != null) {
          await saveSession(data);
        }

        return VerifyOtpResult(
            true, jsonResp['message'] ?? 'Login berhasil', data);
      }

      final msg = jsonResp['message'] ?? 'Gagal verifikasi OTP: $status';
      return VerifyOtpResult(
          false, msg, jsonResp['data'] as Map<String, dynamic>?);
    } catch (e) {
      print('Error during OTP verification: $e');
      return VerifyOtpResult(false, 'Terjadi kesalahan: $e');
    } finally {
      onDone();
    }
  }

  /// Persist token and minimal user info to secure storage / shared prefs.
  Future<void> saveSession(Map<String, dynamic> data) async {
    // Save the entire data object as JSON in secure storage (safe helper)
    final jsonData = jsonEncode(data);
    await _writeSecure('session_data', jsonData);

    // Also persist token separately for convenience
    final token = data['token'] as String?;
    if (token != null) {
      await _writeSecure('session_token', token);
    }

    // Store selected non-sensitive fields in SharedPreferences for quick access
    final prefs = await SharedPreferences.getInstance();
    if (data.containsKey('id'))
      await prefs.setString('user_id', data['id'].toString());
    if (data.containsKey('nama_lengkap'))
      await prefs.setString(
          'user_name', data['nama_lengkap']?.toString() ?? '');
    if (data.containsKey('nik'))
      await prefs.setString('user_nik', data['nik']?.toString() ?? '');
    if (data.containsKey('no_telpon'))
      await prefs.setString('user_phone', data['no_telpon']?.toString() ?? '');
    if (data.containsKey('foto_profil_url'))
      await prefs.setString(
          'user_photo_url', data['foto_profil_url']?.toString() ?? '');
    if (token != null) await prefs.setBool('is_logged_in', true);
  }

  /// Return saved session information (token + user fields) or null if none.
  Future<Map<String, dynamic>?> getSession() async {
    // Prefer the full session_data JSON from secure storage (safe helper)
    final jsonData = await _readSecure('session_data');
    if (jsonData != null && jsonData.isNotEmpty) {
      try {
        final Map<String, dynamic> parsed =
            jsonDecode(jsonData) as Map<String, dynamic>;
        return parsed;
      } catch (_) {
        // fallthrough to prefs
      }
    }

    // Fallback: read individual fields from prefs
    final prefs = await SharedPreferences.getInstance();
    final token = await _readSecure('session_token');
    final loggedIn = prefs.getBool('is_logged_in') ?? false;
    if (token == null || !loggedIn) return null;

    return {
      'token': token,
      'id': prefs.getString('user_id'),
      'nama_lengkap': prefs.getString('user_name'),
      'nik': prefs.getString('user_nik'),
      'no_telpon': prefs.getString('user_phone'),
      'foto_profil_url': prefs.getString('user_photo_url'),
    };
  }

  /// Clear saved session (logout)
  Future<void> clearSession() async {
    await _deleteSecure('session_token');
    await _deleteSecure('session_data');
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('user_id');
    await prefs.remove('user_name');
    await prefs.remove('user_nik');
    await prefs.remove('user_phone');
    await prefs.remove('user_photo_url');
    await prefs.setBool('is_logged_in', false);
  }
}
