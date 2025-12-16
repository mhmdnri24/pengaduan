# Fixes for "Lost Connection to Device" Issue

## 1. Optimize BubbleOverlayService Memory Usage

### Reduce Overlay Size

```kotlin
// In BubbleOverlayService.kt, change from 95% to 30-40%
val bubbleWidth = (screenWidth * 0.4).toInt()
val bubbleHeight = (screenHeight * 0.3).toInt()
```

### Remove FlutterEngine from Service

Replace the FlutterEngine in BubbleOverlayService with native Android components:

```kotlin
// Remove this entire section from BubbleOverlayService.kt
private fun initializeFlutterEngine() {
    flutterEngine = FlutterEngine(this).apply {
        dartExecutor.executeDartEntrypoint(
            DartExecutor.DartEntrypoint.createDefault()
        )
    }
    // ... rest of FlutterEngine initialization
}
```

### Simplify Bubble UI

Remove the MapView from the bubble overlay and use a simple notification-style UI instead.

## 2. Improve Main.dart Initialization

### Add Error Handling and Delays

```dart
// In main.dart, add proper error handling and delays
void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  try {
    // Initialize Firebase with timeout
    await Firebase.initializeApp().timeout(Duration(seconds: 10));

    // Add delays between heavy operations
    await Future.delayed(Duration(milliseconds: 500));

    // Initialize complaint service with error handling
    try {
      await ComplaintService.instance.initialize();
    } catch (e) {
      debugPrint('Complaint service initialization failed: $e');
      // Continue without complaint service
    }

    await Future.delayed(Duration(milliseconds: 500));

    // Fetch settings with timeout
    try {
      await _fetchAndSavePengaturan().timeout(Duration(seconds: 5));
    } catch (e) {
      debugPrint('Failed to fetch settings: $e');
      // Continue with default settings
    }

    // ... continue with other initializations
  } catch (e) {
    debugPrint('App initialization failed: $e');
    // Start app with minimal functionality
  }

  runApp(MyApp());
}
```

## 3. Implement Proper Resource Management

### Add Memory Monitoring

```dart
// Add to main.dart
import 'package:flutter/services.dart';

void _monitorMemoryUsage() {
  Timer.periodic(Duration(seconds: 30), (timer) {
    final info = ProcessInfo.currentRss;
    debugPrint('Memory usage: ${info / 1024 / 1024} MB');

    // If memory usage is too high, clean up resources
    if (info > 200 * 1024 * 1024) { // 200MB
      debugPrint('High memory usage detected, cleaning up');
      _cleanupResources();
    }
  });
}

void _cleanupResources() {
  // Clear caches, release resources, etc.
  imageCache.clear();
  imageCache.clearLiveImages();
}
```

### Add Service Lifecycle Management

```kotlin
// In BubbleOverlayService.kt, add proper lifecycle management
override fun onTaskRemoved(rootIntent: Intent?) {
    // Clean up resources when app is removed from recent tasks
    hideBubble()
    stopSelf()
    super.onTaskRemoved(rootIntent)
}
```

## 4. Optimize Firebase and Notification Handling

### Simplify FCM Handler

```dart
// In fcm_handler.dart, simplify the message handling
class FCMHandler {
  static Future<void> initialize() async {
    // Remove redundant initialization
    // Main.dart already handles Firebase initialization

    // Only setup message handlers
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
    FirebaseMessaging.onMessageOpenedApp.listen(_handleBackgroundMessage);
  }

  // Simplify message handling to avoid heavy operations
  static Future<void> _handleForegroundMessage(RemoteMessage message) async {
    // Extract ID and show simple notification instead of bubble
    String? id = message.data['id'] ?? message.data['body'];
    if (id != null && id.isNotEmpty) {
      // Use local notification instead of bubble overlay
      await _showLocalNotification(id);
    }
  }
}
```

## 5. Add Connection Stability Improvements

### Implement Retry Logic

```dart
// In api_service.dart, add retry logic for critical operations
Future<ApiResponse<T>> _executeWithRetry<T>(
  Future<ApiResponse<T>> Function() operation, {
  int maxRetries = 3,
  Duration delay = const Duration(seconds: 1),
}) async {
  for (int attempt = 0; attempt < maxRetries; attempt++) {
    try {
      return await operation();
    } catch (e) {
      if (attempt == maxRetries - 1) rethrow;
      debugPrint('Operation failed, retrying in ${delay.inSeconds}s... ($attempt/$maxRetries)');
      await Future.delayed(delay * (attempt + 1)); // Exponential backoff
    }
  }
  throw Exception('Operation failed after $maxRetries attempts');
}
```

### Add Connection Health Check

```dart
// Add to main.dart
void _setupConnectionHealthCheck() {
  Timer.periodic(Duration(seconds: 30), (timer) async {
    try {
      final response = await http.get(
        Uri.parse('${await ApiConfig.getBaseUrl()}/health'),
        headers: {'X-API-Key': ApiConfig.apiKey},
      ).timeout(Duration(seconds: 5));

      if (response.statusCode != 200) {
        debugPrint('API health check failed');
      }
    } catch (e) {
      debugPrint('Connection health check failed: $e');
      // Implement reconnection logic
    }
  });
}
```

## 6. Reduce Startup Operations

### Move Non-Critical Operations

```dart
// In main.dart, move non-critical operations to after app starts
class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  void initState() {
    super.initState();

    // Move non-critical operations here
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initializeNonCriticalServices();
    });
  }

  Future<void> _initializeNonCriticalServices() async {
    // Initialize services that aren't critical for app startup
    // like analytics, crash reporting, etc.
  }
}
```

## Implementation Priority

1. **High Priority**: Reduce BubbleOverlayService memory usage (Fix #1)
2. **High Priority**: Add error handling to main.dart initialization (Fix #2)
3. **Medium Priority**: Implement resource management (Fix #3)
4. **Medium Priority**: Simplify FCM handling (Fix #4)
5. **Low Priority**: Add connection stability improvements (Fix #5)
6. **Low Priority**: Reduce startup operations (Fix #6)

## Testing

After implementing these fixes:

1. Test on low-end devices
2. Monitor memory usage during startup
3. Test with multiple background processes
4. Verify stability under memory pressure
5. Test notification handling when app is backgrounded
