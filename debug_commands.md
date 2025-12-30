# Debug Commands untuk "Lost Connection to Device"

## Commands untuk Debugging

### 1. Check Connected Devices
```bash
flutter devices
```

### 2. Run dengan Verbose Logging
```bash
flutter run -v
```

### 3. Run dengan Profile Mode untuk Memory Monitoring
```bash
flutter run --profile
```

### 4. Clean Build
```bash
flutter clean
flutter pub get
```

### 5. Check Gradle Issues
```bash
cd android
./gradlew clean
./gradlew build --stacktrace
```

### 6. Monitor Memory Usage
```bash
flutter run --profile
# Kemudian di terminal lain:
flutter attach --debug-url
```

### 7. Check Logcat untuk Android
```bash
adb logcat | grep flutter
adb logcat | grep pengaduan
adb logcat | grep -E "(ERROR|WARN)"
```

### 8. Check Device Resources
```bash
adb shell dumpsys meminfo com.example.pengaduan
adb shell dumpsys cpuinfo | grep com.example.pengaduan
```

### 9. Check Network Configuration
```bash
adb shell settings get global captive_portal_mode
adb shell settings get global http_proxy
```

### 10. Test dengan Specific Device
```bash
flutter run -d <device_id>
```

## Common Issues and Solutions

### Issue 1: Memory Overflow
**Symptoms:**
- App crashes after installation
- "Lost connection to device" during initialization

**Solution:**
- Reduced memory allocation in gradle.properties
- Optimized initialization sequence in main.dart
- Smaller bubble overlay size

### Issue 2: Firebase Initialization Timeout
**Symptoms:**
- App hangs during startup
- Connection lost during Firebase setup

**Solution:**
- Reduced timeout from 10s to 5s
- Moved heavy initialization to background
- Added proper error handling

### Issue 3: Multiple Service Initialization
**Symptoms:**
- Multiple FlutterJNI warnings
- Resource conflicts

**Solution:**
- Sequential initialization with delays
- Proper service lifecycle management
- Error boundaries

## Testing Scenarios

### Test 1: Fresh Install
1. Uninstall app completely
2. Clean build: `flutter clean && flutter pub get`
3. Run: `flutter run -v`
4. Monitor logs for initialization sequence

### Test 2: Memory Stress Test
1. Run app in profile mode
2. Use memory monitoring tools
3. Trigger bubble overlay multiple times
4. Check for memory leaks

### Test 3: Network Issues
1. Test with poor network connection
2. Test with airplane mode on/off
3. Monitor Firebase connection behavior

## Performance Monitoring

### Memory Usage Thresholds
- **Warning**: >200MB
- **Critical**: >400MB
- **Action Required**: >600MB

### Initialization Time Targets
- **App Start**: <3 seconds
- **Firebase Init**: <5 seconds
- **Service Ready**: <10 seconds

### Resource Usage Guidelines
- **CPU Usage**: <30% average
- **Battery Impact**: Minimal
- **Network Usage**: Optimized

## Log Analysis

### Key Log Patterns to Watch
```
W/FlutterJNI: FlutterJNI.loadLibrary called more than once
D/FlutterJNI: Beginning load of flutter...
Lost connection to device.
```

### Success Indicators
```
I/FlutterActivity: FlutterActivity created successfully
D/FlutterJNI: flutter (null) was loaded normally!
I/MainActivity: App initialization completed
```

## Recovery Commands

### If App Stuck During Initialization
```bash
adb shell am force-stop com.example.pengaduan
flutter clean
flutter pub get
flutter run
```

### If Device Connection Issues
```bash
adb kill-server
adb start-server
flutter devices
```

## Environment Setup

### Required Android SDK Versions
- **compileSdk**: 36
- **targetSdk**: 34
- **minSdk**: 21

### Java Configuration
- **JDK**: 17 (Temurin)
- **Gradle**: Compatible with Flutter version
- **Android Build Tools**: Latest stable

## Next Steps if Problem Persists

1. **Downgrade Flutter Version**
   ```bash
   flutter downgrade <stable_version>
   ```

2. **Test on Different Devices**
   - High-end device
   - Mid-range device
   - Low-end device

3. **Check Device-Specific Issues**
   - Android version compatibility
   - Hardware limitations
   - OEM customizations

4. **Consider Alternative Architecture**
   - Split into modules
   - Use background isolates
   - Implement lazy loading