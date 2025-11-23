import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SessionService {
  static SessionService? _instance;
  static const String _sessionKey = 'app_session';

  SessionService._internal();

  static SessionService get instance {
    _instance ??= SessionService._internal();
    return _instance!;
  }

  /// Save data to session storage
  Future<void> saveToSession(String key, dynamic value) async {
    try {
      // Get existing session data
      final sessionData = await getSessionData();

      // Update the specific key
      sessionData[key] = value;

      // Save back to storage
      await _saveSessionData(sessionData);

      debugPrint('Saved to session: $key = $value');
    } catch (e) {
      debugPrint('Error saving to session: $e');
    }
  }

  /// Get data from session storage
  Future<dynamic> getFromSession(String key) async {
    try {
      final sessionData = await getSessionData();
      return sessionData[key];
    } catch (e) {
      debugPrint('Error getting from session: $e');
      return null;
    }
  }

  /// Get all session data
  Future<Map<String, dynamic>> getSessionData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final prefsData = prefs.getString(_sessionKey);

      if (prefsData != null) {
        return json.decode(prefsData) as Map<String, dynamic>;
      }

      // Return empty map if no data found
      return <String, dynamic>{};
    } catch (e) {
      debugPrint('Error getting session data: $e');
      return <String, dynamic>{};
    }
  }

  /// Save session data to storage
  Future<void> _saveSessionData(Map<String, dynamic> sessionData) async {
    final jsonString = json.encode(sessionData);

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_sessionKey, jsonString);
    } catch (e) {
      debugPrint('Error saving session data: $e');
    }
  }

  /// Clear session data
  Future<void> clearSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_sessionKey);

      debugPrint('Session cleared');
    } catch (e) {
      debugPrint('Error clearing session: $e');
    }
  }

  /// Remove specific key from session
  Future<void> removeFromSession(String key) async {
    try {
      final sessionData = await getSessionData();
      sessionData.remove(key);
      await _saveSessionData(sessionData);

      debugPrint('Removed from session: $key');
    } catch (e) {
      debugPrint('Error removing from session: $e');
    }
  }

  /// Check if session has specific key
  Future<bool> hasSessionKey(String key) async {
    try {
      final sessionData = await getSessionData();
      return sessionData.containsKey(key);
    } catch (e) {
      debugPrint('Error checking session key: $e');
      return false;
    }
  }

  /// Get device ID from session
  Future<String?> getDeviceId() async {
    return await getFromSession('device_id') as String?;
  }

  /// Get FCM token from session
  Future<String?> getFcmToken() async {
    return await getFromSession('fcm_token') as String?;
  }

  /// Get user ID from session
  Future<String?> getUserId() async {
    return await getFromSession('user_id') as String?;
  }

  /// Save device ID to session
  Future<void> saveDeviceId(String deviceId) async {
    await saveToSession('device_id', deviceId);
  }

  /// Save FCM token to session
  Future<void> saveFcmToken(String token) async {
    await saveToSession('fcm_token', token);
  }

  /// Save user ID to session
  Future<void> saveUserId(String userId) async {
    await saveToSession('user_id', userId);
  }

  /// Get splash screen image from session
  Future<String?> getSplashScreenImage() async {
    return await getFromSession('splashscreen_image') as String?;
  }

  /// Save splash screen image to session
  Future<void> saveSplashScreenImage(String imageUrl) async {
    await saveToSession('splashscreen_image', imageUrl);
  }

  /// Get background image from session
  Future<String?> getBackgroundImage() async {
    return await getFromSession('background_image') as String?;
  }

  /// Save background image to session
  Future<void> saveBackgroundImage(String imageUrl) async {
    await saveToSession('background_image', imageUrl);
  }
}
