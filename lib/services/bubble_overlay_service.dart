import 'package:flutter/services.dart';
import 'package:flutter/material.dart';
import '../pages/detail_pengaduan_page.dart';

class BubbleOverlayService {
  static const MethodChannel _channel = MethodChannel('bubble_overlay');
  static GlobalKey<NavigatorState>? _navigatorKey;

  /// Initialize the service with navigator key
  static void initialize(GlobalKey<NavigatorState> navigatorKey) {
    _navigatorKey = navigatorKey;
    _setupMethodCallHandler();
  }

  /// Setup method call handler to receive callbacks from Kotlin
  static void _setupMethodCallHandler() {
    _channel.setMethodCallHandler((call) async {
      switch (call.method) {
        case 'onBubbleClick':
          final String id = call.arguments['id'] as String;
          _navigateToDetail(id);
          break;
        case 'openComplaintDetail':
          final String id = call.arguments['id'] as String;
          print('Received openComplaintDetail with ID: $id');
          _navigateToDetail(id);
          break;
        case 'onPermissionError':
          final String error = call.arguments['error'] as String;
          _handlePermissionError(error);
          break;
        default:
          print('Unknown method: ${call.method}');
      }
    });
  }

  /// Send ID to Kotlin to show bubble overlay
  static Future<void> showBubbleWithId(String id) async {
    try {
      await _channel.invokeMethod('showBubbleWithId', {'id': id});
      print('Sent ID to Kotlin: $id');
    } catch (e) {
      print('Error sending ID to Kotlin: $e');
    }
  }

  /// Hide bubble overlay
  static Future<void> hideBubble() async {
    try {
      await _channel.invokeMethod('hideBubble');
    } catch (e) {
      print('Error hiding bubble: $e');
    }
  }

  /// Open overlay settings
  static Future<void> openOverlaySettings() async {
    try {
      await _channel.invokeMethod('openOverlaySettings');
    } catch (e) {
      print('Error opening overlay settings: $e');
    }
  }

  /// Check overlay permission
  static Future<bool> checkOverlayPermission() async {
    try {
      final bool hasPermission = await _channel.invokeMethod('checkOverlayPermission');
      return hasPermission;
    } catch (e) {
      print('Error checking overlay permission: $e');
      return false;
    }
  }

  /// Request overlay permission
  static Future<void> requestOverlayPermission() async {
    try {
      await _channel.invokeMethod('requestOverlayPermission');
    } catch (e) {
      print('Error requesting overlay permission: $e');
    }
  }

  /// Navigate to detail page with ID
  static void _navigateToDetail(String id) {
    print('Attempting to navigate to detail with ID: $id');
    print('Navigator key available: ${_navigatorKey != null}');
    print('Navigator state available: ${_navigatorKey?.currentState != null}');
    
    if (_navigatorKey?.currentState != null) {
      try {
        _navigatorKey!.currentState!.pushNamed('/detail', arguments: {'id': id});
        print('Successfully navigated to detail with ID: $id');
      } catch (e) {
        print('Error navigating to detail: $e');
        // Fallback: try to push the page directly
        _navigatorKey!.currentState!.push(
          MaterialPageRoute(
            builder: (context) => DetailPengaduanPage(complaintId: id),
          ),
        );
        print('Fallback navigation successful');
      }
    } else {
      print('Navigator key not available - cannot navigate');
    }
  }

  /// Handle permission error
  static void _handlePermissionError(String error) {
    print('Permission error: $error');
    if (error == 'OVERLAY_PERMISSION_DENIED') {
      // Request permission directly
      requestOverlayPermission();
    }
  }

  /// Show permission dialog
  static void _showPermissionDialog() {
    if (_navigatorKey?.currentState != null) {
      OverlayEntry? overlayEntry;
      
      overlayEntry = OverlayEntry(
        builder: (context) => Material(
          color: Colors.black54,
          child: Center(
            child: Container(
              margin: const EdgeInsets.all(20),
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.warning,
                    color: Colors.orange,
                    size: 48,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Permission Diperlukan',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Aplikasi memerlukan izin untuk menampilkan bubble overlay. Silakan aktifkan izin "Display over other apps" di pengaturan.',
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      TextButton(
                        onPressed: () {
                          overlayEntry?.remove();
                        },
                        child: const Text('Batal'),
                      ),
                      ElevatedButton(
                        onPressed: () {
                          overlayEntry?.remove();
                          requestOverlayPermission();
                        },
                        child: const Text('Buka Pengaturan'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      );
      
      _navigatorKey!.currentState!.overlay?.insert(overlayEntry);
    }
  }
}