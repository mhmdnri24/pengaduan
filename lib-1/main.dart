import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/services.dart';
import 'package:audioplayers/audioplayers.dart';
import 'services/complaint_service.dart';
import 'services/bubble_overlay_service.dart';
import 'screens/complaints_list_screen.dart';
import 'screens/splash_screen.dart';
import 'pages/dashboard_page.dart';

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
  FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
    debugPrint('FCM Token refreshed: $newToken');
  });

  // Handle background messages (required top-level handler)
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // Handle messages when app is opened from a terminated state via tap
  FirebaseMessaging.instance.getInitialMessage().then((RemoteMessage? message) {
    if (message != null) {
      // Show bubble when app opened from notification
      BubbleOverlayService.instance.showBubble(complaintCount: 1);
      playNotificationSound();
    }
  });

  // Foreground message handler: show bubble immediately
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    // You can inspect message.notification or message.data here
    BubbleOverlayService.instance.showBubble(complaintCount: 1);
    playNotificationSound();
  });

  // When app in background but opened via notification tap
  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    BubbleOverlayService.instance.showBubble(complaintCount: 1);
    playNotificationSound();
  });

  runApp(const MyApp());
}

// Top-level background message handler
// Fungsi untuk memutar suara notifikasi
Future<void> playNotificationSound() async {
  final player = AudioPlayer();
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
    await platform.invokeMethod('showBubble', { 'count': dataCount });
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

    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
      debugPrint('FCM Token refreshed: $newToken');
    });
  } catch (e) {
    debugPrint('Error fetching FCM token: $e');
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
        // '/complaints': (context) => const ComplaintsListScreen(),
        // '/dashboard': (context) => const DashboardPage(),
      },
    );
  }
}

 