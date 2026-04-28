package com.parajuara.app

import android.app.*
import android.content.Context
import android.content.Intent
import android.graphics.PixelFormat
import android.os.Build
import android.os.IBinder
import android.view.*
import android.media.MediaPlayer
import android.media.RingtoneManager
import android.media.AudioAttributes
import android.media.AudioManager
import android.media.AudioFocusRequest
import android.net.Uri
import android.widget.FrameLayout
import android.widget.LinearLayout
import android.widget.ImageView
import android.widget.TextView
import android.provider.Settings
import androidx.core.app.NotificationCompat
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.engine.FlutterEngineCache
import io.flutter.embedding.engine.dart.DartExecutor
import io.flutter.plugin.common.MethodChannel
import com.google.android.gms.maps.CameraUpdateFactory
import com.google.android.gms.maps.GoogleMap
import com.google.android.gms.maps.MapView
import com.google.android.gms.maps.OnMapReadyCallback
import com.google.android.gms.maps.model.LatLng
import com.google.android.gms.maps.model.MarkerOptions

class BubbleOverlayService : Service(), OnMapReadyCallback {
    private var windowManager: WindowManager? = null
    private var bubbleView: View? = null
    private var isBubbleVisible = false
    private var complaintCount = 0
    private var complaintId: String? = null // Store the complaint ID
    private var complaintData: String? = null // Store the complaint data
    private var flutterEngine: FlutterEngine? = null
    private var methodChannel: MethodChannel? = null
    private var mediaPlayer: MediaPlayer? = null
    private var audioManager: AudioManager? = null
    private var focusRequest: AudioFocusRequest? = null
    private var googleMap: GoogleMap? = null
    private var mapView: MapView? = null
    private var complaintLat: Double? = null
    private var complaintLng: Double? = null

    companion object {
        private const val NOTIFICATION_ID = 1001
        private const val CHANNEL_ID = "bubble_overlay_channel"
        private const val METHOD_CHANNEL = "bubble_overlay"
        const val ACTION_SHOW = "com.example.pengaduan.action.SHOW_BUBBLE"
        const val ACTION_SHOW_WITH_ID = "com.example.pengaduan.action.SHOW_BUBBLE_WITH_ID"
        const val ACTION_HIDE = "com.example.pengaduan.action.HIDE_BUBBLE"
        const val ACTION_UPDATE = "com.example.pengaduan.action.UPDATE_BUBBLE"
        const val EXTRA_COUNT = "extra_count"
        const val EXTRA_ID = "extra_id"
        const val EXTRA_DATA = "extra_data"
        
        fun startService(context: Context) {
            val intent = Intent(context, BubbleOverlayService::class.java)
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                context.startForegroundService(intent)
            } else {
                context.startService(intent)
            }
        }
        
        fun stopService(context: Context) {
            val intent = Intent(context, BubbleOverlayService::class.java)
            context.stopService(intent)
        }
    }

    override fun onCreate() {
        super.onCreate()
        windowManager = getSystemService(WINDOW_SERVICE) as WindowManager
        createNotificationChannel()
        initializeFlutterEngine()
        audioManager = getSystemService(AUDIO_SERVICE) as AudioManager
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        // Ensure service runs in foreground
        startForeground(NOTIFICATION_ID, createNotification())

        // Handle commands from MainActivity via Intent actions
        intent?.action?.let { action ->
            android.util.Log.d("BubbleOverlayService", "onStartCommand action=$action extras=${intent.extras}")
            when (action) {
                ACTION_SHOW -> {
                    val count = intent.getIntExtra(EXTRA_COUNT, 0)
                    android.util.Log.d("BubbleOverlayService", "ACTION_SHOW count=$count")
                    showBubble(count)
                }
                ACTION_SHOW_WITH_ID -> {
                    val id = intent.getStringExtra(EXTRA_ID) ?: "1"
                    val data = intent.getStringExtra(EXTRA_DATA)
                    android.util.Log.d("BubbleOverlayService", "ACTION_SHOW_WITH_ID id=$id data=$data")
                    showBubbleWithId(id, data)
                }
                ACTION_HIDE -> {
                    android.util.Log.d("BubbleOverlayService", "ACTION_HIDE")
                    hideBubble()
                }
                ACTION_UPDATE -> {
                    val count = intent.getIntExtra(EXTRA_COUNT, 0)
                    android.util.Log.d("BubbleOverlayService", "ACTION_UPDATE count=$count")
                    updateComplaintCount(count)
                }
            }
        }

        return START_STICKY
    }

    override fun onBind(intent: Intent?): IBinder? = null

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val notificationManager = getSystemService(NotificationManager::class.java)

            // Primary service channel used by the foreground service
            val serviceChannel = NotificationChannel(
                CHANNEL_ID,
                "Bubble Overlay Service",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Service for managing complaint bubble overlay"
                setShowBadge(false)
            }
            notificationManager.createNotificationChannel(serviceChannel)

            // Ensure overlay_channel exists for incoming FCM notifications that reference it
            val overlayChannelId = "overlay_channel"
            val overlayChannel = NotificationChannel(
                overlayChannelId,
                "Overlay Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Notifications that trigger the overlay bubble"
                // Try to set a custom sound located in res/raw/urgent.wav if present.
                try {
                    val resId = resources.getIdentifier("urgent", "raw", packageName)
                    if (resId != 0) {
                       
                        val soundUri = Uri.parse("android.resource://$packageName/raw/urgent")
                        val audioAttributes = AudioAttributes.Builder()
                            .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                            .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                            .build()
                        setSound(soundUri, audioAttributes)
                    }
                } catch (e: Exception) {
                    // ignore - fallback will use default sound when playing manually
                }
            }
            notificationManager.createNotificationChannel(overlayChannel)
        }
    }

    private fun createNotification(): Notification {
        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("Service Init")
            .setContentText("Monitoring for new complaints")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .setOngoing(true)
            .build()
    }

    private fun initializeFlutterEngine() {
        flutterEngine = FlutterEngine(this).apply {
            dartExecutor.executeDartEntrypoint(
                DartExecutor.DartEntrypoint.createDefault()
            )
        }
        
        methodChannel = MethodChannel(flutterEngine!!.dartExecutor.binaryMessenger, METHOD_CHANNEL).apply {
            setMethodCallHandler { call, result ->
                when (call.method) {
                    "showBubbleWithId" -> {
                        val id = call.argument<String>("id")
                        if (id != null) {
                            showBubbleWithId(id)
                            result.success(true)
                        } else {
                            result.error("INVALID_ID", "ID is required", null)
                        }
                    }
                    "showBubble" -> {
                        val count = call.argument<Int>("count") ?: 0
                        showBubble(count)
                        result.success(true)
                    }
                    "hideBubble" -> {
                        hideBubble()
                        result.success(true)
                    }
                    "updateCount" -> {
                        val count = call.argument<Int>("count") ?: 0
                        updateComplaintCount(count)
                        result.success(true)
                    }
                    "openOverlaySettings" -> {
                        openOverlaySettings()
                        result.success(true)
                    }
                    else -> result.notImplemented()
                }
            }
        }
    }

    fun showBubbleWithId(id: String, data: String? = null) {
        if (isBubbleVisible) {
            // Update existing bubble with new ID and data
            complaintId = id
            complaintData = data
            return
        }

        // Check if overlay permission is granted
        if (!canDrawOverlays()) {
            android.util.Log.w("BubbleOverlayService", "Overlay permission not granted")
            // Send error to Flutter
            methodChannel?.invokeMethod("onPermissionError", mapOf("error" to "OVERLAY_PERMISSION_DENIED"))
            return
        }

        complaintId = id
        complaintData = data
        complaintCount = 1 // Set count to 1 for new complaint
        createBubbleView()
        addBubbleToWindow()
        isBubbleVisible = true
        // Play a short notification sound from the native side so it works
        // even when the app is backgrounded.
        playNotificationSound()
    }

    fun showBubble(count: Int) {
        if (isBubbleVisible) {
            updateComplaintCount(count)
            return
        }

        // Check if overlay permission is granted
        if (!canDrawOverlays()) {
            android.util.Log.w("BubbleOverlayService", "Overlay permission not granted")
            // Send error to Flutter
            methodChannel?.invokeMethod("onPermissionError", mapOf("error" to "OVERLAY_PERMISSION_DENIED"))
            return
        }

        complaintCount = count
        createBubbleView()
        addBubbleToWindow()
        isBubbleVisible = true
        // Play a short notification sound from the native side so it works
        // even when the app is backgrounded.
        playNotificationSound()
    }

    fun hideBubble() {
        if (!isBubbleVisible) return
        
        // Pause and destroy MapView before removing the bubble
        mapView?.let {
            try {
                it.onPause()
                it.onStop()
                it.onDestroy()
            } catch (e: Exception) {
                android.util.Log.e("BubbleOverlayService", "Error pausing MapView", e)
            }
        }
        
        bubbleView?.let { view ->
            windowManager?.removeView(view)
        }
        bubbleView = null
        mapView = null
        googleMap = null
        isBubbleVisible = false
    }

    private fun updateComplaintCount(count: Int) {
        complaintCount = count
        // The layout may not include a badge anymore; perform a runtime lookup
        val badgeId = resources.getIdentifier("badge_text", "id", packageName)
        if (badgeId != 0) {
            bubbleView?.findViewById<TextView>(badgeId)?.let { badge ->
                badge.text = count.toString()
                badge.visibility = if (count > 0) View.VISIBLE else View.GONE
            }
        }
    }

    private fun createBubbleView() {
        val inflater = LayoutInflater.from(this)
        bubbleView = inflater.inflate(R.layout.bubble_overlay, null)
        
    val bubbleContainer = bubbleView!!.findViewById<View>(R.id.bubble_container)
    val openButton = bubbleView!!.findViewById<TextView>(R.id.bubble_open)
    // Try to find close button by id if present
    val closeId = resources.getIdentifier("bubble_close", "id", packageName)
    val closeButton = if (closeId != 0) bubbleView!!.findViewById<ImageView>(closeId) else null
    
    // Initialize map fragment - we'll handle this differently in a service
    // The map fragment will be initialized when the bubble is shown
    initializeMapFragment()
        
    // Set up drag functionality
        setupDragListener(bubbleContainer)
        
    // Set up click listener
        bubbleContainer.setOnClickListener {
            sendBubbleClickToFlutter()
        }

    // Open app button: send ID to Flutter and hide bubble
        openButton?.setOnClickListener {
            sendBubbleClickToFlutter()
        }

    // Close button: hide bubble and stop the service
        closeButton?.setOnClickListener {
            hideBubble()
            try { stopSelf() } catch (e: Exception) { }
        }
        
        // Update complaint info if available
        updateComplaintInfo()
    }
    
    private fun initializeMapFragment() {
        try {
            // Initialize MapView
            val mapViewId = resources.getIdentifier("map_view", "id", packageName)
            if (mapViewId != 0) {
                mapView = bubbleView?.findViewById(mapViewId) as? MapView
                mapView?.onCreate(null)
                mapView?.getMapAsync(this)
                android.util.Log.d("BubbleOverlayService", "MapView initialized")
            }
        } catch (e: Exception) {
            android.util.Log.e("BubbleOverlayService", "Error initializing MapView", e)
        }
    }

    private fun setupDragListener(view: View) {
        var initialX = 0
        var initialY = 0
        var initialTouchX = 0f
        var initialTouchY = 0f

        view.setOnTouchListener { v, event ->
            when (event.action) {
                MotionEvent.ACTION_DOWN -> {
                    initialX = (bubbleView?.layoutParams as? WindowManager.LayoutParams)?.x ?: 0
                    initialY = (bubbleView?.layoutParams as? WindowManager.LayoutParams)?.y ?: 0
                    initialTouchX = event.rawX
                    initialTouchY = event.rawY
                    true
                }
                MotionEvent.ACTION_MOVE -> {
                    val layoutParams = bubbleView?.layoutParams as? WindowManager.LayoutParams
                    layoutParams?.let { params ->
                        params.x = initialX + (event.rawX - initialTouchX).toInt()
                        params.y = initialY + (event.rawY - initialTouchY).toInt()
                        windowManager?.updateViewLayout(bubbleView, params)
                    }
                    true
                }
                MotionEvent.ACTION_UP -> {
                    // Snap to edges
                    snapToEdge()
                    true
                }
                else -> false
            }
        }
    }

    private fun snapToEdge() {
        val layoutParams = bubbleView?.layoutParams as? WindowManager.LayoutParams
        layoutParams?.let { params ->
            val displayMetrics = resources.displayMetrics
            val screenWidth = displayMetrics.widthPixels
            val screenHeight = displayMetrics.heightPixels
            val bubbleWidth = bubbleView?.width ?: (screenWidth * 0.75).toInt()
            val bubbleHeight = bubbleView?.height ?: (screenHeight * 0.75).toInt()
            
            // For large bubble, snap to center or edges
            if (params.x < screenWidth / 2) {
                params.x = 0
            } else {
                params.x = screenWidth - bubbleWidth
            }
            
            // Keep bubble within screen bounds vertically
            if (params.y < 0) {
                params.y = 0
            } else if (params.y + bubbleHeight > screenHeight) {
                params.y = screenHeight - bubbleHeight
            }
            
            windowManager?.updateViewLayout(bubbleView, params)
        }
    }

    private fun addBubbleToWindow() {
        val displayMetrics = resources.displayMetrics
        val screenWidth = displayMetrics.widthPixels
        val screenHeight = displayMetrics.heightPixels
        
        // Calculate smaller bubble size to reduce memory usage
        // Reduced from 95% to 80% width and 60% height
        val bubbleWidth = (screenWidth * 0.95).toInt()
        val bubbleHeight = (screenHeight * 0.95).toInt()
        
        val layoutParams = WindowManager.LayoutParams(
            bubbleWidth,
            bubbleHeight,
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY
            } else {
                @Suppress("DEPRECATION")
                WindowManager.LayoutParams.TYPE_PHONE
            },
            WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or
                    WindowManager.LayoutParams.FLAG_LAYOUT_IN_SCREEN,
            PixelFormat.TRANSLUCENT
        ).apply {
            gravity = Gravity.CENTER
            x = 0
            y = 0
        }

        windowManager?.addView(bubbleView, layoutParams)
        
        // Start MapView lifecycle after adding to window
        mapView?.let {
            try {
                it.onStart()
                it.onResume()
            } catch (e: Exception) {
                android.util.Log.e("BubbleOverlayService", "Error starting MapView", e)
            }
        }
    }

    private fun sendBubbleClickToFlutter() {
        android.util.Log.d("BubbleOverlayService", "sendBubbleClickToFlutter called")
        android.util.Log.d("BubbleOverlayService", "complaintId: $complaintId")
        android.util.Log.d("BubbleOverlayService", "complaintData: $complaintData")
        
        complaintId?.let { id ->
            // Launch MainActivity with the complaint ID and data
            val intent = Intent(this, MainActivity::class.java).apply {
                flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
                putExtra("complaint_id", id)
                putExtra("open_detail", true)
                // Add complaint data if available
                complaintData?.let { data ->
                    putExtra("complaint_data", data)
                }
            }
            
            try {
                startActivity(intent)
                android.util.Log.d("BubbleOverlayService", "Successfully opened app with ID: $id")
            } catch (e: Exception) {
                android.util.Log.e("BubbleOverlayService", "Error opening app", e)
            }
        } ?: run {
            android.util.Log.w("BubbleOverlayService", "No complaint ID available to send")
        }
        
        // Hide bubble after opening the app
        hideBubble()
    }
    
    private fun updateComplaintInfo() {
        // Try to find and update complaint info views
        val titleId = resources.getIdentifier("complaint_title", "id", packageName)
        val descId = resources.getIdentifier("complaint_desc", "id", packageName)
        val locationId = resources.getIdentifier("complaint_location", "id", packageName)
        val categoryId = resources.getIdentifier("complaint_category", "id", packageName)
        val codeId = resources.getIdentifier("complaint_code", "id", packageName)
        
        if (titleId != 0 && descId != 0 && locationId != 0 && categoryId != 0 && codeId != 0) {
            val titleView = bubbleView?.findViewById<TextView>(titleId)
            val descView = bubbleView?.findViewById<TextView>(descId)
            val locationView = bubbleView?.findViewById<TextView>(locationId)
            val categoryView = bubbleView?.findViewById<TextView>(categoryId)
            val codeView = bubbleView?.findViewById<TextView>(codeId)
            
            if (!complaintData.isNullOrEmpty()) {
                try {
                    // Parse JSON data to extract complaint information
                    val jsonData = org.json.JSONObject(complaintData!!)
                    
                    val title = jsonData.optString("judul", "Pengaduan Baru")
                    val description = jsonData.optString("deskripsi", "")
                    val location = jsonData.optString("alamat", "")
                    val category = jsonData.optString("kategori", "")
                    val code = jsonData.optString("kode_laporan", "")
                    
                    // Extract location coordinates
                    complaintLat = jsonData.optDouble("lokasi_lat", Double.NaN)
                    complaintLng = jsonData.optDouble("lokasi_lng", Double.NaN)
                    
                    // Update views with extracted data
                    titleView?.text = title
                    descView?.text = if (description.isNotEmpty()) description else null
                    locationView?.text = if (location.isNotEmpty()) location else null
                    categoryView?.text = if (category.isNotEmpty()) category else null
                    codeView?.text = if (code.isNotEmpty()) "Kode: $code" else null
                    
                    // Set visibility based on data availability
                    titleView?.visibility = View.VISIBLE
                    descView?.visibility = if (description.isNotEmpty()) View.VISIBLE else View.GONE
                    locationView?.visibility = if (location.isNotEmpty()) View.VISIBLE else View.GONE
                    categoryView?.visibility = if (category.isNotEmpty()) View.VISIBLE else View.GONE
                    codeView?.visibility = if (code.isNotEmpty()) View.VISIBLE else View.GONE
                    
                    // Update map with location if available
                    updateMapLocation()
                    
                    android.util.Log.d("BubbleOverlayService", "Updated complaint info with title: $title, desc: $description, location: $location, category: $category, code: $code, lat: $complaintLat, lng: $complaintLng")
                } catch (e: Exception) {
                    android.util.Log.e("BubbleOverlayService", "Error parsing complaint data", e)
                    // Fallback to basic info
                    titleView?.text = "Pengaduan Baru"
                    descView?.visibility = View.GONE
                    locationView?.visibility = View.GONE
                    categoryView?.visibility = View.GONE
                    codeView?.visibility = View.GONE
                    titleView?.visibility = View.VISIBLE
                }
            } else {
                // Hide all info views if no data
                titleView?.visibility = View.GONE
                descView?.visibility = View.GONE
                locationView?.visibility = View.GONE
                categoryView?.visibility = View.GONE
                codeView?.visibility = View.GONE
            }
        }
    }

    private fun openComplaintScreen() {
        // Launch Flutter app with specific route
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
            putExtra("route", "/complaints")
            putExtra("complaint_count", complaintCount)
        }
        startActivity(intent)
        
        // Hide bubble temporarily
        hideBubble()
    }

    override fun onDestroy() {
        super.onDestroy()
        hideBubble()
        // Ensure media player is released
        mediaPlayer?.let {
            try {
                if (it.isPlaying) it.stop()
            } catch (e: Exception) {
                // ignore
            }
            try {
                it.release()
            } catch (e: Exception) {
                // ignore
            }
            mediaPlayer = null
        }
        
        // Clean up MapView
        mapView?.let {
            try {
                it.onDestroy()
            } catch (e: Exception) {
                // ignore
            }
        }
        
        flutterEngine?.destroy()
    }

    private fun playNotificationSound() {
        try {
            android.util.Log.d("BubbleOverlayService", "playNotificationSound: start")
            // Prefer a bundled raw resource `res/raw/urgent.mp3` if available (works in background).
            val resId = resources.getIdentifier("urgent", "raw", packageName)
            android.util.Log.d("BubbleOverlayService", "playNotificationSound: resId=$resId")

            // If there's an existing player, release it first
            mediaPlayer?.let {
                try { if (it.isPlaying) it.stop() } catch (ignored: Exception) {}
                try { it.release() } catch (ignored: Exception) {}
            }

            mediaPlayer = if (resId != 0) {
                android.util.Log.d("BubbleOverlayService", "Creating mediaPlayer from raw resource")
                MediaPlayer.create(this, resId)
            } else {
                android.util.Log.d("BubbleOverlayService", "Creating mediaPlayer from default notification sound")
                val notification: Uri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
                MediaPlayer.create(this, notification)
            }

            if (mediaPlayer == null) {
                android.util.Log.e("BubbleOverlayService", "playNotificationSound: mediaPlayer creation returned null")
            }

            // Request audio focus before playback
            var focusGranted = false
            try {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                    val attr = AudioAttributes.Builder()
                        .setUsage(AudioAttributes.USAGE_NOTIFICATION)
                        .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                        .build()
                    focusRequest = AudioFocusRequest.Builder(AudioManager.AUDIOFOCUS_GAIN_TRANSIENT)
                        .setAudioAttributes(attr)
                        .setAcceptsDelayedFocusGain(false)
                        .setOnAudioFocusChangeListener { /* no-op */ }
                        .build()
                    val res = audioManager?.requestAudioFocus(focusRequest!!)
                    focusGranted = res == AudioManager.AUDIOFOCUS_REQUEST_GRANTED
                } else {
                    val res = audioManager?.requestAudioFocus(
                        null,
                        AudioManager.STREAM_NOTIFICATION,
                        AudioManager.AUDIOFOCUS_GAIN_TRANSIENT
                    )
                    focusGranted = res == AudioManager.AUDIOFOCUS_REQUEST_GRANTED
                }
            } catch (e: Exception) {
                // ignore focus errors and proceed to play
            }
            mediaPlayer?.setOnErrorListener { mp, what, extra ->
                android.util.Log.e("BubbleOverlayService", "MediaPlayer error what=$what extra=$extra")
                try { mp.release() } catch (ignored: Exception) {}
                if (mediaPlayer === mp) mediaPlayer = null
                // Abandon audio focus on error
                try {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                        focusRequest?.let { audioManager?.abandonAudioFocusRequest(it) }
                    } else {
                        audioManager?.abandonAudioFocus(null)
                    }
                } catch (e: Exception) {
                    // ignore
                }
                true
            }

            mediaPlayer?.setOnCompletionListener { mp ->
                try { mp.release() } catch (ignored: Exception) {}
                if (mediaPlayer === mp) mediaPlayer = null
                // Abandon audio focus
                try {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                        focusRequest?.let { audioManager?.abandonAudioFocusRequest(it) }
                    } else {
                        audioManager?.abandonAudioFocus(null)
                    }
                } catch (e: Exception) {
                    // ignore
                }
            }
            try {
                mediaPlayer?.start()
                android.util.Log.d("BubbleOverlayService", "playNotificationSound: started playback")
            } catch (e: Exception) {
                android.util.Log.e("BubbleOverlayService", "Failed to start mediaPlayer", e)
            }
        } catch (e: Exception) {
            android.util.Log.e("BubbleOverlayService", "Failed to play notification sound", e)
        }
    }

    private fun canDrawOverlays(): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            Settings.canDrawOverlays(this)
        } else {
            true // For older versions, assume permission is granted
        }
    }

    fun openOverlaySettings() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            val intent = Intent(Settings.ACTION_MANAGE_OVERLAY_PERMISSION).apply {
                data = Uri.parse("package:$packageName")
                flags = Intent.FLAG_ACTIVITY_NEW_TASK
            }
            startActivity(intent)
        }
    }
    
    override fun onMapReady(map: GoogleMap) {
        googleMap = map
        android.util.Log.d("BubbleOverlayService", "Map is ready")
        updateMapLocation()
    }
    
    private fun updateMapLocation() {
        if (googleMap == null || complaintLat == null || complaintLng == null || complaintLat!!.isNaN() || complaintLng!!.isNaN()) {
            android.util.Log.d("BubbleOverlayService", "Cannot update map: map=${googleMap != null}, lat=$complaintLat, lng=$complaintLng")
            return
        }
        
        try {
            val location = LatLng(complaintLat!!, complaintLng!!)
            
            // Add marker for the complaint location
            googleMap?.addMarker(
                MarkerOptions()
                    .position(location)
                    .title("Lokasi Pengaduan")
                    .snippet("Laporan: ${complaintId ?: "Unknown"}")
            )
            
            // Move camera to the location
            googleMap?.moveCamera(CameraUpdateFactory.newLatLngZoom(location, 15f))
            
            android.util.Log.d("BubbleOverlayService", "Updated map with location: $location")
        } catch (e: Exception) {
            android.util.Log.e("BubbleOverlayService", "Error updating map location", e)
        }
    }
}
