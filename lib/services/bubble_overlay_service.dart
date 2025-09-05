import 'package:flutter/services.dart';
import 'package:flutter/foundation.dart';
import 'package:permission_handler/permission_handler.dart';

class BubbleOverlayService {
  static const MethodChannel _channel = MethodChannel('bubble_overlay');
  static BubbleOverlayService? _instance;
  
  BubbleOverlayService._internal();
  
  static BubbleOverlayService get instance {
    _instance ??= BubbleOverlayService._internal();
    return _instance!;
  }

  /// Check if overlay permission is granted
  Future<bool> hasOverlayPermission() async {
    if (await Permission.systemAlertWindow.isGranted) {
      return true;
    }
    return false;
  }

  /// Request overlay permission from user
  Future<bool> requestOverlayPermission() async {
    final status = await Permission.systemAlertWindow.request();
    return status.isGranted;
  }

  /// Show the bubble overlay with complaint count
  Future<bool> showBubble({required int complaintCount}) async {
    try {
      if (!await hasOverlayPermission()) {
        final granted = await requestOverlayPermission();
        if (!granted) {
          return false;
        }
      }

      final result = await _channel.invokeMethod('showBubble', {
        'count': complaintCount,
      });
      return result == true;
    } catch (e) {
      debugPrint('Error showing bubble: $e');
      return false;
    }
  }

  /// Hide the bubble overlay
  Future<bool> hideBubble() async {
    try {
      final result = await _channel.invokeMethod('hideBubble');
      return result == true;
    } catch (e) {
      debugPrint('Error hiding bubble: $e');
      return false;
    }
  }

  /// Update the complaint count on the bubble
  Future<bool> updateComplaintCount(int count) async {
    try {
      final result = await _channel.invokeMethod('updateCount', {
        'count': count,
      });
      return result == true;
    } catch (e) {
      debugPrint('Error updating complaint count: $e');
      return false;
    }
  }

  /// Start the overlay service
  Future<bool> startService() async {
    try {
      if (!await hasOverlayPermission()) {
        final granted = await requestOverlayPermission();
        if (!granted) {
          return false;
        }
      }

      final result = await _channel.invokeMethod('startService');
      return result == true;
    } catch (e) {
      debugPrint('Error starting service: $e');
      return false;
    }
  }

  /// Stop the overlay service
  Future<bool> stopService() async {
    try {
      final result = await _channel.invokeMethod('stopService');
      return result == true;
    } catch (e) {
      debugPrint('Error stopping service: $e');
      return false;
    }
  }
}
