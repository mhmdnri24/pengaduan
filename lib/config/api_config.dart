// lib/config/api_config.dart

import '../services/session_service.dart';

class ApiConfig {
  // Base URL for API (no trailing slash) - used as fallback
  static const String baseUrl = 'https://dashboard.nusakoding.com/api/v1';

  // API key used in requests
  static const String apiKey = 'API_HIJ973D4Nmgdbhy42';

  static const String origin = 'https://dashboard.nusakoding.com';

  /// Get base URL from session storage with fallback to hardcoded value
  static Future<String> getBaseUrl() async {
    try {
      final sessionService = SessionService.instance;
      final sessionBaseUrl = await sessionService.getBaseUrl();
      if (sessionBaseUrl != null && sessionBaseUrl.isNotEmpty) {
        return sessionBaseUrl;
      }
      return baseUrl;
    } catch (e) {
      // If there's any error, use the hardcoded base URL
      return baseUrl;
    }
  }
}
