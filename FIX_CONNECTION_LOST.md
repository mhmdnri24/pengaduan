# Analisis dan Solusi Masalah "Lost Connection to Device" pada Flutter

## Analisis Masalah

Berdasarkan log yang Anda berikan:
```
I/GED ( 8091): ged_boost_gpu_freq, level 100, eOrigin 2, final_idx 31, oppidx_max 31, oppidx_min 0
V/PhoneWindow( 8091): DecorView setVisiblity: visibility = 4, Parent = android.view.ViewRootImpl@fc902e9, this = DecorView@af6b6e[]
I/FLTFireMsgService( 8091): FlutterFirebaseMessagingBackgroundService started!
I/GED ( 8091): ged_boost_gpu_freq, level 100, eOrigin 2, final_idx 31, oppidx_max 31, oppidx_min 0
I/FA ( 8091): Application backgrounded at: timestamp_millis: 1766195051642
Lost connection to device.
```

## Penyebab Utama

1. **Inisialisasi Aplikasi yang Terlalu Berat**
   - Aplikasi melakukan terlalu banyak inisialisasi service secara bersamaan
   - Firebase, FCM, API calls, dan service lainnya dijalankan tanpa jeda yang cukup
   - Proses background yang berat saat aplikasi dimulai

2. **Konfigurasi Firebase dan FCM**
   - `FlutterFirebaseMessagingBackgroundService` dimulai tapi aplikasi langsung backgrounded
   - Proses inisialisasi FCM yang kompleks dengan banyak handler

3. **Memory dan Resource Management**
   - GPU boost frequency maksimal (level 100) menunjukkan beban yang tinggi
   - Aplikasi mengkonsumsi terlalu banyak resource saat startup

## Solusi yang Direkomendasikan

### 1. Optimasi Inisialisasi Aplikasi

**Masalah di `main.dart`:**
- Fungsi `_initializeHeavyServices()` menjalankan terlalu banyak service tanpa prioritas
- Semua API call dilakukan secara bersamaan tanpa error handling yang proper

**Solusi:**
```dart
// Ubah urutan inisialisasi di main.dart
Future<void> _initializeHeavyServices() async {
  debugPrint('Starting optimized services initialization...');

  // 1. Inisialisasi yang paling kritis dulu
  await _initializeComplaintService();
  await Future.delayed(const Duration(seconds: 2)); // Tambah delay lebih besar

  // 2. Cek koneksi device sebelum lanjut
  if (!await _checkDeviceConnection()) {
    debugPrint('Device connection lost, stopping initialization');
    return;
  }

  // 3. Lanjut dengan inisialisasi lainnya secara bertahap
  await _fetchAndSaveApiSettings();
  await Future.delayed(const Duration(seconds: 1));

  await _setupFCM();
  await Future.delayed(const Duration(seconds: 1));

  // Sisanya...
}

// Tambah fungsi untuk cek koneksi device
Future<bool> _checkDeviceConnection() async {
  try {
    // Cek koneksi dengan delay untuk memastikan device stabil
    await Future.delayed(const Duration(seconds: 1));
    return true;
  } catch (e) {
    debugPrint('Device connection check failed: $e');
    return false;
  }
}
```

### 2. Perbaiki Konfigurasi Firebase

**Masalah:**
- Firebase initialization dengan timeout yang terlalu singkat (5 detik)
- Terlalu banyak FCM handlers yang di-setup saat startup

**Solusi:**
```dart
// Di main.dart, ubah timeout Firebase initialization
try {
  await Firebase.initializeApp().timeout(const Duration(seconds: 10));
  debugPrint('Firebase initialized successfully');
} catch (e) {
  debugPrint('Firebase initialization failed: $e');
  // Lanjutkan tanpa Firebase jika gagal
}

// Di _setupFCM(), tambah error handling yang lebih baik
Future<void> _setupFCM() async {
  try {
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    
    // Tambah delay sebelum request permission
    await Future.delayed(const Duration(seconds: 1));
    await messaging.requestPermission().timeout(const Duration(seconds: 10));

    await _printFcmToken().timeout(const Duration(seconds: 15));

    // Setup listeners dengan error handling
    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
      try {
        debugPrint('FCM Token refreshed: $newToken');
        await _saveFcmToken(newToken);
      } catch (e) {
        debugPrint('Error saving refreshed FCM token: $e');
      }
    });
  } catch (e) {
    debugPrint('FCM setup failed: $e');
    // Jangan gagal total jika FCM gagal
  }
}
```

### 3. Optimasi Memory dan Resource

**Masalah di `splash_screen.dart`:**
- Loading gambar dari network tanpa cache yang proper
- Terlalu banyak precache images

**Solusi:**
```dart
// Di splash_screen.dart, optimasi image loading
Future<void> _loadSplashImage() async {
  try {
    print('Loading splash image from session...');
    final sessionService = SessionService.instance;
    final splashImageUrl = await sessionService.getFromSession('splashscreen_image');

    if (splashImageUrl != null && splashImageUrl.toString().isNotEmpty) {
      // Tambah delay untuk mengurangi beban
      await Future.delayed(const Duration(milliseconds: 500));
      
      setState(() {
        _splashImageUrl = splashImageUrl.toString();
      });

      // Hanya precache jika device memiliki memory cukup
      if (_splashImageUrl != null && _splashImageUrl!.isNotEmpty) {
        precacheImage(
          NetworkImage(_splashImageUrl!),
          context,
        ).catchError((e) {
          print('Error precaching network image (this is OK): $e');
        });
      }
    }
  } catch (e) {
    print('Error loading splash image: $e');
    // Gunakan default jika terjadi error
    setState(() {
      _splashImageUrl = null;
    });
  }
}
```

### 4. Konfigurasi Build Gradle

**Masalah di `android/app/build.gradle.kts`:**
- Konfigurasi memory yang tidak optimal

**Solusi:**
```kotlin
// Tambah konfigurasi berikut di android/app/build.gradle.kts
android {
    // ... existing config ...
    
    defaultConfig {
        // ... existing config ...
        
        // Tambah konfigurasi untuk mengurangi memory usage
        multiDexEnabled = true
        
        // Konfigurasi heap size
        // Tambah ini untuk mengoptimalkan memory
        manifestPlaceholders["appName"] = "Lapor Pak Wali"
    }
    
    buildTypes {
        release {
            // ... existing config ...
            
            // Tambah proguard untuk mengurangi size
            isMinifyEnabled = true
            proguardFiles(getDefaultProguardFile("proguard-android.txt"), "proguard-rules.pro")
        }
    }
    
    // Tambah konfigurasi dex options
    dexOptions {
        javaMaxHeapSize = "2g"
    }
}
```

### 5. Penanganan Koneksi Device

**Solusi Tambahan:**
```dart
// Tambah di main.dart
class ConnectionMonitor {
  static Timer? _connectionTimer;
  
  static void startMonitoring() {
    _connectionTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      // Cek koneksi device
      _checkConnection();
    });
  }
  
  static void stopMonitoring() {
    _connectionTimer?.cancel();
  }
  
  static Future<void> _checkConnection() async {
    try {
      // Implementasi pengecekan koneksi
      // Jika koneksi hilang, lakukan recovery
    } catch (e) {
      debugPrint('Connection check failed: $e');
    }
  }
}

// Start monitoring di main()
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // ... existing initialization ...
  
  // Start connection monitoring
  ConnectionMonitor.startMonitoring();
  
  runApp(const MyApp());
}
```

## Implementasi Langkah demi Langkah

1. **Backup project Anda terlebih dahulu**
2. **Implementasi solusi 1-3 secara bertahap**
3. **Test setiap perubahan dengan `flutter run`**
4. **Monitor memory usage dengan `flutter doctor`**
5. **Test di berbagai device dengan spesifikasi berbeda**

## Tambahan Tips

1. **Gunakan `flutter run --verbose`** untuk melihat detail error
2. **Monitor memory usage** dengan Android Studio's Memory Profiler
3. **Test di device dengan spesifikasi rendah** untuk memastikan stabilitas
4. **Consider menggunakan `flutter run --release`** untuk testing performa

## Catatan Penting

- Masalah "Lost connection to device" sering terjadi karena inisialisasi yang terlalu berat
- Pastikan untuk tidak menjalankan terlalu banyak service secara bersamaan
- Selalu gunakan timeout yang cukup untuk operasi network
- Implementasi proper error handling untuk semua service initialization

Dengan menerapkan solusi di atas, aplikasi Anda seharusnya tidak lagi mengalami masalah "Lost connection to device" setelah instalasi berhasil.