# Floating Bubble Overlay Feature

This implementation adds a floating bubble overlay feature to your Flutter application that shows when new complaints are received, similar to Facebook Messenger chat heads.

## Features Implemented

✅ **Floating Bubble Overlay**: Shows a draggable bubble when new complaints arrive  
✅ **Drag Functionality**: Bubble can be moved around the screen and snaps to edges  
✅ **Badge Counter**: Displays the number of pending complaints  
✅ **Tap to Open**: Tapping the bubble opens the complaints screen  
✅ **Android Compatibility**: Works on Android 10+ with Bubbles API and older versions with SYSTEM_ALERT_WINDOW  
✅ **Permission Handling**: Automatically requests overlay permission when needed  
✅ **Service Integration**: Background service manages the bubble lifecycle  

## Project Structure

```
lib/
├── main.dart                           # Updated main app with complaint management
├── models/
│   └── complaint.dart                  # Complaint data model
├── services/
│   ├── bubble_overlay_service.dart     # Flutter service for bubble management
│   └── complaint_service.dart          # Complaint management service
└── screens/
    ├── complaints_list_screen.dart     # List of all complaints
    └── complaint_detail_screen.dart    # Individual complaint details

android/
├── app/src/main/
│   ├── AndroidManifest.xml            # Updated with permissions and service
│   ├── kotlin/.../MainActivity.kt     # Updated with method channel
│   └── res/
│       ├── layout/bubble_overlay.xml  # Bubble UI layout
│       └── drawable/                  # Bubble styling resources
└── BubbleOverlayService.kt            # Native Android service
```

## Dependencies Added

```yaml
dependencies:
  permission_handler: ^11.0.1      # For overlay permission handling
  flutter_overlay_window: ^0.4.6   # For overlay window management
  overlay_support: ^2.0.0          # Additional overlay support
```

## Android Permissions Added

```xml
<uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
<uses-permission android:name="android.permission.WAKE_LOCK" />
<uses-permission android:name="android.permission.INTERNET" />
```

## How It Works

### 1. Service Initialization
- The `ComplaintService` initializes on app startup
- Starts the `BubbleOverlayService` in the background
- Sets up method channels for communication

### 2. New Complaint Flow
```dart
// When a new complaint is received
await ComplaintService.instance.simulateNewComplaint();

// This triggers:
// 1. Complaint added to the list
// 2. Bubble overlay service called
// 3. Bubble appears with updated count
```

### 3. Bubble Interaction
- **Drag**: Touch and drag to move the bubble around
- **Snap**: Automatically snaps to left or right edge
- **Tap**: Opens the complaints screen in your Flutter app
- **Badge**: Shows pending complaint count

### 4. Permission Handling
```dart
// Check permission
bool hasPermission = await BubbleOverlayService.instance.hasOverlayPermission();

// Request permission
bool granted = await BubbleOverlayService.instance.requestOverlayPermission();
```

## Usage Examples

### Simulate New Complaint
```dart
// From anywhere in your app
await ComplaintService.instance.simulateNewComplaint();
```

### Show/Hide Bubble Manually
```dart
// Show bubble with count
await BubbleOverlayService.instance.showBubble(complaintCount: 5);

// Hide bubble
await BubbleOverlayService.instance.hideBubble();

// Update count
await BubbleOverlayService.instance.updateComplaintCount(3);
```

### Listen for New Complaints
```dart
ComplaintService.instance.newComplaintStream.listen((complaint) {
  // Handle new complaint
  print('New complaint: ${complaint.title}');
});
```

## Testing the Implementation

1. **Run the app**: `flutter run`
2. **Grant permissions**: When prompted, allow overlay permission
3. **Simulate complaint**: Tap "Simulate New Complaint" button
4. **See bubble**: A blue bubble should appear on screen
5. **Test interaction**: 
   - Drag the bubble around
   - Tap to open complaints screen
   - Create more complaints to see count update

## Customization Options

### Bubble Appearance
Edit `android/app/src/main/res/drawable/bubble_background.xml`:
```xml
<solid android:color="#YOUR_COLOR" />
```

### Badge Styling
Edit `android/app/src/main/res/drawable/badge_background.xml`:
```xml
<solid android:color="#YOUR_BADGE_COLOR" />
```

### Bubble Icon
Replace `android/app/src/main/res/drawable/ic_complaint.xml` with your custom icon.

### Bubble Size
Edit `android/app/src/main/res/layout/bubble_overlay.xml`:
```xml
android:layout_width="YOUR_SIZE"
android:layout_height="YOUR_SIZE"
```

## Integration with Your Existing App

### 1. Replace Simulated Data
In `ComplaintService`, replace `simulateNewComplaint()` with your actual complaint receiving logic:

```dart
// Replace this method with your real implementation
Future<void> receiveRealComplaint(Complaint complaint) async {
  await addComplaint(complaint);
  // Bubble will automatically show
}
```

### 2. Customize Navigation
In `BubbleOverlayService.kt`, modify the `openComplaintScreen()` method:

```kotlin
private fun openComplaintScreen() {
    val intent = Intent(this, MainActivity::class.java).apply {
        flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
        putExtra("route", "/your-custom-route")
    }
    startActivity(intent)
    hideBubble()
}
```

### 3. Add to Existing Screens
```dart
// In any existing screen
class YourExistingScreen extends StatefulWidget {
  @override
  _YourExistingScreenState createState() => _YourExistingScreenState();
}

class _YourExistingScreenState extends State<YourExistingScreen> {
  @override
  void initState() {
    super.initState();
    // Listen for new complaints
    ComplaintService.instance.newComplaintStream.listen((complaint) {
      // Handle in your existing screen
    });
  }
}
```

## Troubleshooting

### Bubble Not Appearing
1. Check if overlay permission is granted
2. Verify the service is running in background
3. Check Android logs for errors

### Permission Denied
1. Go to Settings > Apps > Your App > Permissions
2. Enable "Display over other apps"
3. Or use the permission request in the app

### Service Not Starting
1. Check AndroidManifest.xml has the service declared
2. Verify the service class exists and is properly implemented
3. Check for compilation errors in the Kotlin service

## Performance Considerations

- The bubble service runs as a foreground service to ensure it stays alive
- The bubble view is lightweight and doesn't impact performance
- Method channels are used for efficient communication between Flutter and native code
- The service automatically cleans up resources when destroyed

## Security Notes

- The overlay permission is required for system-level access
- The service only shows when complaints are present
- No sensitive data is stored in the bubble overlay
- All communication is handled through secure method channels

## Future Enhancements

- Add bubble animations (bounce, pulse)
- Implement bubble customization options
- Add support for multiple bubble types
- Integrate with notification system
- Add haptic feedback on bubble interactions
