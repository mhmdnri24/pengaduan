import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'bubble_overlay_service.dart';
import 'api_service.dart';

class FCMHandler {
  static final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  static final ValueNotifier<int> notificationCount = ValueNotifier<int>(0);


  /// Initialize FCM and setup message handlers
  static Future<void> initialize() async {
    // Request permission for notifications
    await _firebaseMessaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Setup message handlers
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
    FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessage);
    
    // Handle notification when app is terminated
    RemoteMessage? initialMessage = await _firebaseMessaging.getInitialMessage();
    if (initialMessage != null) {
      _handleBackgroundMessage(initialMessage);
    }
  }

  /// Handle FCM message when app is in foreground
  static Future<void> _handleForegroundMessage(RemoteMessage message) async {
    debugPrint('Received foreground message: ${message.data}');
    await updateNotificationCount();
    
    // Extract ID from message data (check both 'id' and 'body' fields)
    String? id = message.data['id'];
    if (id == null) {
      // If no 'id' field, try to extract from 'body' field
      id = message.data['body'];
    }
    
    if (id != null && id.isNotEmpty) {
      // Show bubble overlay with the ID
      await BubbleOverlayService.showBubbleWithId(id);
    }
  }

  /// Handle FCM message when app is in background or terminated
  static Future<void> _handleBackgroundMessage(RemoteMessage message) async {
    debugPrint('Received background message: ${message.data}');
    await updateNotificationCount();
    
    // Extract ID from message data (check both 'id' and 'body' fields)
    String? id = message.data['id'];
    if (id == null) {
      // If no 'id' field, try to extract from 'body' field
      id = message.data['body'];
    }
    
    if (id != null && id.isNotEmpty) {
      // Show bubble overlay with the ID
      await BubbleOverlayService.showBubbleWithId(id);
    }
  }

  /// Get FCM token
  static Future<String?> getToken() async {
    try {
      return await _firebaseMessaging.getToken();
    } catch (e) {
      debugPrint('Error getting FCM token: $e');
      return null;
    }
  }

  /// Update notification count from API
  static Future<void> updateNotificationCount() async {
    try {
      final response = await ApiService.instance.getActivePengumuman();
      debugPrint('updateNotificationCount: success=${response.success}, total=${response.data?.total}');
      if (response.success && response.data != null) {
        notificationCount.value = response.data!.total;
        debugPrint('Notification count updated to: ${notificationCount.value}');
      } else {
        debugPrint('Failed to update notification count: ${response.error}');
      }
    } catch (e) {
      debugPrint('Error updating notification count: $e');
    }
  }
}
