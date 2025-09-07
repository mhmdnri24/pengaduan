import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/services.dart';
import 'package:audioplayers/audioplayers.dart';
import 'services/complaint_service.dart';
import 'services/bubble_overlay_service.dart';
import 'screens/complaints_list_screen.dart';

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
        primarySwatch: Colors.blue,
        useMaterial3: true,
      ),
      home: const MyHomePage(),
      routes: {
        '/complaints': (context) => const ComplaintsListScreen(),
      },
    );
  }
}

class MyHomePage extends StatefulWidget {
  const MyHomePage({super.key});
  // This widget is the home page of your application. It is stateful, meaning
  // that it has a State object (defined below) that contains fields that affect
  // how it looks.
  // This class is the configuration for the state.
  @override
  State<MyHomePage> createState() => _MyHomePageState();
}

class _MyHomePageState extends State<MyHomePage> {
  final ComplaintService _complaintService = ComplaintService.instance;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Complaint Management'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: <Widget>[
              const Icon(
                Icons.warning_amber_rounded,
                size: 80,
                color: Colors.blue,
              ),
              const SizedBox(height: 24),
              const Text(
                'Complaint Management System 2',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              const Text(
                'Monitor and manage complaints with floating bubble notifications',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 32),
              StreamBuilder<List<dynamic>>(
                stream: _complaintService.complaintsStream,
                builder: (context, snapshot) {
                  final pendingCount = _complaintService.pendingComplaintsCount;
                  return Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          const Text(
                            'Current Status',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Pending Complaints: $pendingCount',
                            style: const TextStyle(fontSize: 16),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Total Complaints: ${snapshot.data?.length ?? 0}',
                            style: const TextStyle(fontSize: 16),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pushNamed(context, '/complaints');
                  },
                  icon: const Icon(Icons.list),
                  label: const Text('View All Complaints'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () async {
                    final messenger = ScaffoldMessenger.of(context);
                      await _printFcmToken();
                      debugPrint('Simulating new complaint...');
                    await _complaintService.simulateNewComplaint();
                    if (!mounted) return;
                    messenger.showSnackBar(
                      const SnackBar(
                        content: Text('New complaint simulated! Check the bubble overlay.'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                  },
                  icon: const Icon(Icons.add_alert),
                  label: const Text('Simulate New Complaint'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.orange,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
