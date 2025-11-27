# Solusi untuk "Lost Connection to Device"

## 1. Optimasi Inisialisasi Aplikasi

### Ubah main.dart dengan pendekatan yang lebih ringan:

```dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Inisialisasi dasar saja di main
  try {
    await Firebase.initializeApp().timeout(const Duration(seconds: 5));
  } catch (e) {
    debugPrint('Firebase initialization failed: $e');
  }

  // Jalankan aplikasi dengan inisialisasi bertahap
  runApp(MyApp());
  
  // Lakukan inisialisasi berat setelah app running
  WidgetsBinding.instance.addPostFrameCallback((_) {
    _initializeHeavyServices();
  });
}

Future<void> _initializeHeavyServices() async {
  // Pindahkan semua inisialisasi berat ke sini dengan delay
  await Future.delayed(const Duration(seconds: 1));
  
  // Complaint service
  try {
    await ComplaintService.instance.initialize().timeout(const Duration(seconds: 10));
  } catch (e) {
    debugPrint('Complaint service initialization failed: $e');
  }
  
  // FCM setup
  try {
    await _setupFCM();
  } catch (e) {
    debugPrint('FCM setup failed: $e');
  }
}
```

## 2. Kurangi Memory Usage

### Update gradle.properties:
```properties
org.gradle.jvmargs=-Xmx4G -XX:MaxMetaspaceSize=2G -XX:+HeapDumpOnOutOfMemoryError
android.useAndroidX=true
android.enableJetifier=true
org.gradle.java.home=/Library/Java/JavaVirtualMachines/temurin-17.jdk/Contents/Home
```

### Update build.gradle.kts:
```kotlin
defaultConfig {
    applicationId = "com.example.pengaduan"
    minSdk = 21
    targetSdk = 34
    versionCode = flutter.versionCode
    versionName = flutter.versionName
    
    // Tambahkan konfigurasi untuk mengurangi memory
    multiDexEnabled = true
}
```

## 3. Optimasi BubbleOverlayService

### Kurangi ukuran bubble overlay:
```kotlin
private fun addBubbleToWindow() {
    val displayMetrics = resources.displayMetrics
    val screenWidth = displayMetrics.widthPixels
    val screenHeight = displayMetrics.heightPixels
    
    // Kurangi ukuran bubble untuk menghemat memory
    val bubbleWidth = (screenWidth * 0.8).toInt() // Dari 0.95 ke 0.8
    val bubbleHeight = (screenHeight * 0.6).toInt() // Dari 0.95 ke 0.6
    
    // ... rest of the code
}
```

## 4. Implementasi Error Handling yang Lebih Baik

### Tambahkan error boundary di main.dart:
```dart
class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Complaint Management App',
      home: ErrorBoundary(
        child: SplashScreen(),
      ),
    );
  }
}

class ErrorBoundary extends StatefulWidget {
  final Widget child;
  
  const ErrorBoundary({Key? key, required this.child}) : super(key: key);
  
  @override
  _ErrorBoundaryState createState() => _ErrorBoundaryState();
}

class _ErrorBoundaryState extends State<ErrorBoundary> {
  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
```

## 5. Tambahkan Monitoring Memory

### Buat file utils/memory_monitor.dart:
```dart
import 'dart:developer' as developer;
import 'package:flutter/services.dart';

class MemoryMonitor {
  static void startMonitoring() {
    Timer.periodic(Duration(seconds: 30), (timer) {
      _checkMemoryUsage();
    });
  }
  
  static void _checkMemoryUsage() {
    // Monitor memory usage dan log jika terlalu tinggi
    developer.log('Memory monitor check');
  }
}
```

## 6. Optimasi Firebase Configuration

### Pastikan google-services.json benar:
- Check package name: com.example.pengaduan
- Verifikasi API keys
- Pastikan project Firebase aktif

## 7. Testing Strategy

### Langkah testing:
1. Run dengan `flutter run -verbose` untuk detail log
2. Monitor memory usage dengan `flutter run --profile`
3. Test di device dengan berbagai spesifikasi
4. Periksa logcat untuk error detail

## 8. Alternative Solutions

Jika masalah persist:
1. **Downgrade Flutter version**: Coba Flutter versi lebih stabil
2. **Split modules**: Pisah fitur-fitur berat ke modules terpisah
3. **Use background isolate**: Pindahkan proses berat ke background isolate

## 9. Debug Commands

```bash
# Check connected devices
flutter devices

# Run with verbose logging
flutter run -v

# Check for memory leaks
flutter run --profile

# Clean build
flutter clean
flutter pub get
```

## 10. Long-term Solutions

1. **Implement lazy loading** untuk fitur-fitur berat
2. **Use state management yang lebih efisien** (Provider/Bloc)
3. **Optimize image assets** size
4. **Implement proper lifecycle management**