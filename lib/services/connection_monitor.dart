import 'dart:async';
import 'package:flutter/foundation.dart';

class ConnectionMonitor {
  static Timer? _connectionTimer;
  static bool _isConnected = true;
  static int _retryCount = 0;
  static const int _maxRetries = 3;

  static void startMonitoring() {
    debugPrint('Starting connection monitoring...');
    _connectionTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      _checkConnection();
    });
  }

  static void stopMonitoring() {
    debugPrint('Stopping connection monitoring...');
    _connectionTimer?.cancel();
    _connectionTimer = null;
  }

  static Future<void> _checkConnection() async {
    try {
      // Implementasi pengecekan koneksi
      final isConnected = await _performConnectionCheck();

      if (!isConnected && _isConnected) {
        debugPrint('Connection lost detected!');
        _isConnected = false;
        _handleConnectionLost();
      } else if (isConnected && !_isConnected) {
        debugPrint('Connection restored!');
        _isConnected = true;
        _retryCount = 0;
        _handleConnectionRestored();
      }
    } catch (e) {
      debugPrint('Connection check failed: $e');
      if (_isConnected) {
        _isConnected = false;
        _handleConnectionLost();
      }
    }
  }

  static Future<bool> _performConnectionCheck() async {
    try {
      // Simulasi pengecekan koneksi
      // Dalam implementasi nyata, bisa menggunakan http.get ke endpoint yang reliable
      await Future.delayed(const Duration(milliseconds: 500));

      // Return true untuk simulasi, ganti dengan implementasi nyata
      return true;
    } catch (e) {
      debugPrint('Error performing connection check: $e');
      return false;
    }
  }

  static void _handleConnectionLost() {
    debugPrint('Handling connection lost...');
    _retryCount++;

    if (_retryCount <= _maxRetries) {
      debugPrint(
          'Attempting to reconnect... Attempt $_retryCount/$_maxRetries');

      // Coba reconnect setelah delay
      Timer(Duration(seconds: _retryCount * 2), () async {
        if (await _performConnectionCheck()) {
          _isConnected = true;
          _retryCount = 0;
          _handleConnectionRestored();
        }
      });
    } else {
      debugPrint(
          'Max retry attempts reached. Connection monitoring will continue.');
    }
  }

  static void _handleConnectionRestored() {
    debugPrint('Connection restored successfully!');
    // Implementasi recovery logic jika diperlukan
    // Misalnya: refresh data, restart services, dll.
  }

  static bool get isConnected => _isConnected;
  static int get retryCount => _retryCount;
}
