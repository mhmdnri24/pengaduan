import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/services.dart';
import 'package:audioplayers/audioplayers.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'dart:io';
import 'services/complaint_service.dart';
import 'services/bubble_overlay_service.dart';
import 'services/session_service.dart';
import 'services/fcm_handler.dart';
import 'services/api_service.dart';
import 'screens/splash_screen.dart';
import 'pages/dashboard_page.dart';
import 'pages/landing_page.dart';
import 'pages/login_page.dart';
import 'pages/register_page.dart';
import 'pages/detail_pengaduan_page.dart';
import 'pages/cctv_list_page.dart';
import 'pages/cctv_video_page.dart';
import 'pages/pengumuman_detail_page.dart';
import 'utils/memory_monitor.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Firebase only - the most critical component
  try {
    await Firebase.initializeApp().timeout(const Duration(seconds: 5));
    debugPrint('Firebase initialized successfully');
  } catch (e) {
    debugPrint('Firebase initialization failed: $e');
  }

  // Start the app immediately with minimal initialization
  runApp(const MyApp());

  // Initialize heavy services after app is running
  WidgetsBinding.instance.addPostFrameCallback((_) {
    _initializeHeavyServices();
  });
}

// Separate function for heavy initialization to prevent blocking
Future<void> _initializeHeavyServices() async {
  debugPrint('Starting heavy services initialization...');

  // Add initial delay to let app fully load
  await Future.delayed(const Duration(seconds: 1));

  // Initialize services with proper error handling and delays
  await _initializeComplaintService();
  await Future.delayed(const Duration(milliseconds: 500));

  await _fetchAndSaveApiSettings();
  await Future.delayed(const Duration(milliseconds: 500));

  await _fetchAndSavePengaturan();
  await Future.delayed(const Duration(milliseconds: 500));

  await _setupFCM();
  await Future.delayed(const Duration(milliseconds: 500));

  await _getDeviceId();
  await Future.delayed(const Duration(milliseconds: 500));

  if (Platform.isAndroid) {
    await _checkOverlayPermission();
  }

  // Setup message handlers
  _setupMessageHandlers();

  // Start memory monitoring
  MemoryMonitor.startMonitoring();

  debugPrint('Heavy services initialization completed');
}

Future<void> _initializeComplaintService() async {
  try {
    await ComplaintService.instance
        .initialize()
        .timeout(const Duration(seconds: 10));
    debugPrint('Complaint service initialized successfully');
  } catch (e) {
    debugPrint('Complaint service initialization failed: $e');
  }
}

Future<void> _setupFCM() async {
  try {
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    await messaging.requestPermission().timeout(const Duration(seconds: 5));

    await _printFcmToken().timeout(const Duration(seconds: 10));

    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
      debugPrint('FCM Token refreshed: $newToken');
      await _saveFcmToken(newToken);
    });
  } catch (e) {
    debugPrint('FCM setup failed: $e');
  }
}

void _setupMessageHandlers() {
  try {
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    FirebaseMessaging.instance
        .getInitialMessage()
        .then((RemoteMessage? message) async {
      if (message != null) {
        await _handleMessage(message);
      }
    });

    FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
      await _handleMessage(message);
      await playNotificationSound();
    });

    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) async {
      debugPrint('Message opened app: ${message.data}');
      
      // Check if we have an ID to navigate to
      String? id = message.data['id'];
      if (id == null) {
        id = message.data['body'];
      }
      
      if (id != null && id.isNotEmpty) {
        // Navigate to pengumuman detail
        MyApp.navigatorKey.currentState?.pushNamed(
          '/pengumuman-detail',
          arguments: {'id': id},
        );
      } else {
        // Fallback to existing logic if needed, or just do nothing specific
        await _handleMessage(message);
      }
      
      await playNotificationSound();
    });
  } catch (e) {
    debugPrint('Message handlers setup failed: $e');
  }
}

Future<void> _handleMessage(RemoteMessage message) async {
  try {
    String id = message.data['id'] ?? message.data['body'] ?? '1';
    await BubbleOverlayService.showBubbleWithId(id);

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('pending_complaint_id', id);
    await prefs.setBool('skip_splash_to_detail', true);
  } catch (e) {
    debugPrint('Error handling message: $e');
  }
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
    // Extract ID from message data
    String? id = message.data['id'];
    if (id == null) {
      id = message.data['body'];
    }
    if (id == null || id.isEmpty) {
      id = '1'; // fallback ID
    }

    // Use MethodChannel to invoke native start/show with ID
    const platform = MethodChannel('bubble_overlay');
    await platform.invokeMethod('showBubbleWithId', {'id': id});
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

/// Save FCM token to secure storage, SharedPreferences, and session.
Future<void> _saveFcmToken(String token) async {
  // Save to secure storage
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

  // Save to session storage
  try {
    await SessionService.instance.saveFcmToken(token);
    debugPrint('FCM token saved to session: $token');
  } catch (e) {
    debugPrint('Failed to save fcm_token to session: $e');
  }
}

Future<String?> _getDeviceId() async {
  try {
    final DeviceInfoPlugin deviceInfo = DeviceInfoPlugin();

    if (Platform.isAndroid) {
      final AndroidDeviceInfo androidInfo = await deviceInfo.androidInfo;
      final String deviceId = androidInfo.id;
      debugPrint('Android Device ID: $deviceId');

      // Save device ID to secure storage
      await _saveDeviceId(deviceId);

      return deviceId;
    } else if (Platform.isIOS) {
      final IosDeviceInfo iosInfo = await deviceInfo.iosInfo;
      final String deviceId = iosInfo.identifierForVendor ?? 'unknown';
      debugPrint('iOS Device ID: $deviceId');

      // Save device ID to secure storage
      await _saveDeviceId(deviceId);

      return deviceId;
    } else {
      debugPrint('Unsupported platform for device ID');
      return null;
    }
  } catch (e) {
    debugPrint('Error getting device ID: $e');
    return null;
  }
}

/// Save device ID to secure storage, SharedPreferences, and session.
Future<void> _saveDeviceId(String deviceId) async {
  // Save to secure storage
  final secure = const FlutterSecureStorage();
  try {
    await secure.write(key: 'device_id', value: deviceId);
  } catch (e) {
    debugPrint('Secure storage write failed, falling back to prefs: $e');
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('device_id', deviceId);
  }

  // Also write to prefs for quick access
  try {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('device_id', deviceId);
  } catch (e) {
    debugPrint('Failed to write device_id to prefs: $e');
  }

  // Save to session storage
  try {
    await SessionService.instance.saveDeviceId(deviceId);
    debugPrint('Device ID saved to session: $deviceId');
  } catch (e) {
    debugPrint('Failed to save device_id to session: $e');
  }
}

// Fetch and save API settings (base_url) to session
Future<void> _fetchAndSaveApiSettings() async {
  try {
    // Check if session already has base_url
    final sessionService = SessionService.instance;
    final existingBaseUrl = await sessionService.getBaseUrl();

    if (existingBaseUrl != null && existingBaseUrl.isNotEmpty) {
      debugPrint('Base URL already exists in session: $existingBaseUrl');
      return;
    }

    debugPrint('No base URL in session, fetching from API settings...');
    final apiService = ApiService.instance;
    final result = await apiService.fetchAndSaveApiSettings();

    if (result.success) {
      debugPrint('API settings fetched and saved successfully');
    } else {
      debugPrint('Failed to fetch API settings: ${result.error}');
      debugPrint('Will use hardcoded base URL as fallback');
    }
  } catch (e) {
    debugPrint('Error fetching API settings: $e');
    debugPrint('Will use hardcoded base URL as fallback');
  }
}

// Fetch and save pengaturan data to session
Future<void> _fetchAndSavePengaturan() async {
  try {
    // Check if session already has pengaturan data
    final sessionService = SessionService.instance;
    final existingData =
        await sessionService.getFromSession('splashscreen_image');

    if (existingData != null) {
      debugPrint('Pengaturan data already exists in session, skipping fetch');
      return;
    }

    debugPrint('No pengaturan data in session, fetching from API...');
    final apiService = ApiService.instance;
    final result = await apiService.getPengaturanAndSaveToSession();

    if (result.success) {
      debugPrint('Pengaturan data fetched and saved successfully');
    } else {
      debugPrint('Failed to fetch pengaturan data: ${result.error}');
    }
  } catch (e) {
    debugPrint('Error fetching pengaturan data: $e');
  }
}

class MyApp extends StatelessWidget {
  const MyApp({super.key, this.skipToDetail = false, this.complaintId = ''});

  static const String _title = 'Complaint Management App';
  final bool skipToDetail;
  final String complaintId;

  // Global navigator key for navigation
  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  @override
  Widget build(BuildContext context) {
    // Initialize FCM handler
    FCMHandler.initialize();

    return MaterialApp(
      title: _title,
      navigatorKey: navigatorKey, // Use the global navigator key
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue),
        scaffoldBackgroundColor: const Color(0xFFF6F8FB),
        useMaterial3: true,
        textTheme: Typography.blackMountainView,
      ),
      home: skipToDetail == true
          ? DetailPengaduanPage(complaintId: complaintId)
          : const SplashScreen(),
      routes: {
        '/landing': (context) => const LandingPage(),
        '/login': (context) => const LoginPage(),
        '/register': (context) => const RegisterPage(),
        '/dashboard': (context) => const DashboardPage(),
        '/complaints': (context) =>
            const DashboardPage(), // Redirect to dashboard for now
        '/detail': (context) {
          final args = ModalRoute.of(context)?.settings.arguments
              as Map<String, dynamic>?;
          final String id = args?['id'] ?? '';
          return DetailPengaduanPage(complaintId: id);
        },
        '/cctv': (context) => const CctvListPage(),
        '/pengumuman-detail': (context) {
          final args = ModalRoute.of(context)?.settings.arguments
              as Map<String, dynamic>?;
          final String id = args?['id'] ?? '';
          return PengumumanDetailPage(id: id);
        },
      },
      builder: (context, child) {
        // Initialize bubble overlay service after MaterialApp is built
        WidgetsBinding.instance.addPostFrameCallback((_) {
          BubbleOverlayService.initialize(navigatorKey);
        });
        return child!;
      },
    );
  }
}

// Check overlay permission on Android
Future<void> _checkOverlayPermission() async {
  try {
    const platform = MethodChannel('bubble_overlay');

    // Check if permission is already granted
    final bool hasPermission =
        await platform.invokeMethod('checkOverlayPermission');

    if (!hasPermission) {
      print('Overlay permission not granted, requesting...');
      // Request permission
      await platform.invokeMethod('requestOverlayPermission');
    } else {
      print('Overlay permission already granted');
    }
  } catch (e) {
    print('Overlay permission check failed: $e');
  }
}
