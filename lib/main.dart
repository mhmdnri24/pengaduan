import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/services.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'services/complaint_service.dart';
import 'services/bubble_overlay_service.dart';
import 'screens/splash_screen.dart';
import 'pages/dashboard_page.dart';
import 'pages/landing_page.dart';
import 'pages/login_page.dart';
import 'pages/register_page.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase
  await Firebase.initializeApp();

  // Initialize the complaint service
  await ComplaintService.instance.initialize();

  // Request notification permissions (Android auto-grants but keep for completeness)
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  await messaging.requestPermission();

  // Print FCM token and listen for refreshes
  await _printFcmToken();
  FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
    debugPrint('FCM Token refreshed: $newToken');
    await _saveFcmToken(newToken);
  });

  // Handle background messages (required top-level handler)
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // Handle messages when app is opened from a terminated state via tap
  const platform = MethodChannel('bubble_overlay');
  FirebaseMessaging.instance
      .getInitialMessage()
      .then((RemoteMessage? message) async {
    if (message != null) {
      // Use native service to show bubble (native will play sound)
      try {
        await platform.invokeMethod('showBubble', {'count': 1});
      } catch (e) {
        // Fallback to existing Flutter helper if native channel isn't available
        await BubbleOverlayService.instance.showBubble(complaintCount: 1);
      }
    }
  });

  // Foreground message handler: show bubble immediately
  FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
    // Use native service to show bubble and play sound
    try {
      await platform.invokeMethod('showBubble', {'count': 1});
    } catch (e) {
      await BubbleOverlayService.instance.showBubble(complaintCount: 1);
      // optional: keep Dart audio as extra feedback when app is foreground
      await playNotificationSound();
    }
  });

  // When app in background but opened via notification tap
  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
    try {
      await platform.invokeMethod('showBubble', {'count': 1});
    } catch (e) {
      await BubbleOverlayService.instance.showBubble(complaintCount: 1);
      await playNotificationSound();
    }
  });

  runApp(const MyApp());
}

// Top-level background message handler
// Fungsi untuk memutar suara notifikasi
Future<void> playNotificationSound() async {
  final player = AudioPlayer();
  print('Playing notification sound...');
  await player.play(AssetSource('sound/urgent.wav'));
}

Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Important: initialize Firebase in background isolate
  await Firebase.initializeApp();
  // We cannot call UI code here. Instead, attempt to start the Android service via MethodChannel
  // This will work on Android if the app has permission and service setup.
  try {
    final dataCount = int.tryParse(message.data['count'] ?? '') ?? 1;
    // Use MethodChannel to invoke native start/show
    const platform = MethodChannel('bubble_overlay');
    await platform.invokeMethod('showBubble', {'count': dataCount});
  } catch (e) {
    // Background isolate might not have platform channel bound; fall back to nothing
  }
}

// Print FCM token and listen for token refreshes
Future<void> _printFcmToken() async {
  try {
    final fcm = FirebaseMessaging.instance;
    final token = await fcm.getToken();
    debugPrint('FCM Token: $token');
    if (token != null) await _saveFcmToken(token);
  } catch (e) {
    debugPrint('Error fetching FCM token: $e');
  }
}

/// Save FCM token to secure storage with SharedPreferences fallback.
Future<void> _saveFcmToken(String token) async {
  final secure = const FlutterSecureStorage();
  try {
    await secure.write(key: 'fcm_token', value: token);
  } catch (e) {
    debugPrint('Secure storage write failed, falling back to prefs: $e');
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('fcm_token', token);
  }
  // Also write to prefs for quick access
  try {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('fcm_token', token);
  } catch (e) {
    debugPrint('Failed to write fcm_token to prefs: $e');
  }
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  static const String _title = 'Complaint Management App';

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: _title,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        scaffoldBackgroundColor: const Color(0xFFF6F8FB),
        useMaterial3: true,
        textTheme: Typography.blackMountainView,
      ),
      home: const SplashScreen(),
      routes: {
        '/landing': (context) => const LandingPage(),
        '/login': (context) => const LoginPage(),
        '/register': (context) => const RegisterPage(),
        '/dashboard': (context) => const DashboardPage(),
        '/complaints': (context) => const DashboardPage(), // Redirect to dashboard for now
      },
    );
  }
}
