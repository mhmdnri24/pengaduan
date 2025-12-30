// lib/config/api_config.dart

import '../services/session_service.dart';

class ApiConfig {
  // Base URL for API (no trailing slash) - used as fallback
  static const String baseUrl = 'https://lss.lubuklinggaukota.go.id/api/v1';

  // API key used in requests
  static const String apiKey = 'API_HIJ973D4Nmgdbhy42';

  static const String origin = 'https://dashboard.nusakoding.com';

  /// Get base URL from session storage with fallback to hardcoded value
  static Future<String> getBaseUrl() async {
    try {
      final sessionService = SessionService.instance;
      final sessionBaseUrl = await sessionService.getBaseUrl();
      print('Session base URL: $sessionBaseUrl');
      if (sessionBaseUrl != null && sessionBaseUrl.isNotEmpty) {
        return sessionBaseUrl;
      }

      print('Using hardcoded base URL: $baseUrl');
      return baseUrl;
    } catch (e) {
      // If there's any error, use the hardcoded base URL
      print('Error getting base URL from session: $e');
      return baseUrl;
    }
  }
  static Future<String> getApiKey() async {
    try {
      final sessionService = SessionService.instance;
      final apiKeySession = await sessionService.getApiKey();
      print('Session base URL: $apiKeySession');
      if (apiKeySession != null && apiKeySession.isNotEmpty) {
        return apiKeySession;
      }

      print('Using hardcoded base URL: $apiKey');
      return apiKey;
    } catch (e) {
      // If there's any error, use the hardcoded base URL
      print('Error getting base URL from session: $e');
      return apiKey;
    }
  }
}
