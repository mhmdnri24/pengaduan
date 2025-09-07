package com.example.pengaduan

import android.app.*
import android.content.Context
import android.content.Intent
import android.graphics.PixelFormat
import android.os.Build
import android.os.IBinder
import android.view.*
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
        
        // Set up badge
        badgeText.text = complaintCount.toString()
        badgeText.visibility = if (complaintCount > 0) View.VISIBLE else View.GONE
        
        // Set up drag functionality
        setupDragListener(bubbleContainer)
        
        // Set up click listener
        bubbleContainer.setOnClickListener {
            openComplaintScreen()
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
        flutterEngine?.destroy()
    }
}
