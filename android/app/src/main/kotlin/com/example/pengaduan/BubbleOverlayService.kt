package com.example.pengaduan

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
import android.widget.ImageView
import android.widget.TextView
import androidx.core.app.NotificationCompat
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.embedding.engine.FlutterEngineCache
import io.flutter.embedding.engine.dart.DartExecutor
import io.flutter.plugin.common.MethodChannel

class BubbleOverlayService : Service() {
    private var windowManager: WindowManager? = null
    private var bubbleView: View? = null
    private var isBubbleVisible = false
    private var complaintCount = 0
    private var flutterEngine: FlutterEngine? = null
    private var methodChannel: MethodChannel? = null
    private var mediaPlayer: MediaPlayer? = null
    private var audioManager: AudioManager? = null
    private var focusRequest: AudioFocusRequest? = null

    companion object {
        private const val NOTIFICATION_ID = 1001
        private const val CHANNEL_ID = "bubble_overlay_channel"
        private const val METHOD_CHANNEL = "bubble_overlay"
        const val ACTION_SHOW = "com.example.pengaduan.action.SHOW_BUBBLE"
        const val ACTION_HIDE = "com.example.pengaduan.action.HIDE_BUBBLE"
        const val ACTION_UPDATE = "com.example.pengaduan.action.UPDATE_BUBBLE"
        const val EXTRA_COUNT = "extra_count"
        
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
            .setContentTitle("Complaint Bubble Service")
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
                    else -> result.notImplemented()
                }
            }
        }
    }

    fun showBubble(count: Int) {
        if (isBubbleVisible) {
            updateComplaintCount(count)
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
        
        bubbleView?.let { view ->
            windowManager?.removeView(view)
        }
        bubbleView = null
        isBubbleVisible = false
    }

    private fun updateComplaintCount(count: Int) {
        complaintCount = count
        bubbleView?.findViewById<TextView>(R.id.badge_text)?.text = count.toString()
        bubbleView?.findViewById<TextView>(R.id.badge_text)?.visibility = 
            if (count > 0) View.VISIBLE else View.GONE
    }

    private fun createBubbleView() {
        val inflater = LayoutInflater.from(this)
        bubbleView = inflater.inflate(R.layout.bubble_overlay, null)
        
    val bubbleContainer = bubbleView!!.findViewById<FrameLayout>(R.id.bubble_container)
    val badgeText = bubbleView!!.findViewById<TextView>(R.id.badge_text)
    val bubbleIcon = bubbleView!!.findViewById<ImageView>(R.id.bubble_icon)
    val closeButton = bubbleView!!.findViewById<ImageView>(R.id.bubble_close)
    val openButton = bubbleView!!.findViewById<TextView>(R.id.bubble_open)
        
        // Set up badge
        badgeText.text = complaintCount.toString()
        badgeText.visibility = if (complaintCount > 0) View.VISIBLE else View.GONE
        
        // Set up drag functionality
        setupDragListener(bubbleContainer)
        
        // Set up click listener
        bubbleContainer.setOnClickListener {
            openComplaintScreen()
        }

        // Close button: hide bubble and stop the service
        closeButton?.setOnClickListener {
            hideBubble()
            try { stopSelf() } catch (e: Exception) { }
        }

        // Open app button: launch MainActivity and hide bubble
        openButton?.setOnClickListener {
            try {
                val intent = Intent(this, MainActivity::class.java).apply {
                    flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
                    putExtra("route", "/complaints")
                    putExtra("complaint_count", complaintCount)
                }
                startActivity(intent)
            } catch (e: Exception) {
                android.util.Log.e("BubbleOverlayService", "Failed to open app", e)
            }
            hideBubble()
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
            val bubbleWidth = bubbleView?.width ?: 100
            
            // Snap to left or right edge
            if (params.x < screenWidth / 2) {
                params.x = 0
            } else {
                params.x = screenWidth - bubbleWidth
            }
            
            windowManager?.updateViewLayout(bubbleView, params)
        }
    }

    private fun addBubbleToWindow() {
        val layoutParams = WindowManager.LayoutParams(
            WindowManager.LayoutParams.WRAP_CONTENT,
            WindowManager.LayoutParams.WRAP_CONTENT,
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
                WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY
            } else {
                @Suppress("DEPRECATION")
                WindowManager.LayoutParams.TYPE_PHONE
            },
            WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or
                    WindowManager.LayoutParams.FLAG_LAYOUT_IN_SCREEN or
                    WindowManager.LayoutParams.FLAG_HARDWARE_ACCELERATED,
            PixelFormat.TRANSLUCENT
        ).apply {
            gravity = Gravity.TOP or Gravity.START
            x = 0
            y = 200
        }

        windowManager?.addView(bubbleView, layoutParams)
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
        flutterEngine?.destroy()
    }

    private fun playNotificationSound() {
        try {
            android.util.Log.d("BubbleOverlayService", "playNotificationSound: start")
            // Prefer a bundled raw resource `res/raw/urgent.wav` if available (works in background).
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
}
