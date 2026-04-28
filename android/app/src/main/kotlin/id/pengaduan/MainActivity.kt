package com.parajuara.app

import android.content.Intent
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val CHANNEL = "bubble_overlay"
    private val OVERLAY_PERMISSION_REQUEST_CODE = 1234
    private var methodChannel: MethodChannel? = null

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        
        methodChannel = MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL)
        methodChannel?.setMethodCallHandler { call, result ->
            when (call.method) {
                "startService" -> {
                    BubbleOverlayService.startService(this)
                    result.success(true)
                }
                "stopService" -> {
                    BubbleOverlayService.stopService(this)
                    result.success(true)
                }
                "showBubble" -> {
                    val count = (call.arguments as? Map<*, *>)?.get("count") as? Int ?: 0
                    val intent = Intent(this, BubbleOverlayService::class.java).apply {
                        action = BubbleOverlayService.ACTION_SHOW
                        putExtra(BubbleOverlayService.EXTRA_COUNT, count)
                    }
                    if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                        startForegroundService(intent)
                    } else {
                        startService(intent)
                    }
                    result.success(true)
                }
                "hideBubble" -> {
                    val intent = Intent(this, BubbleOverlayService::class.java).apply {
                        action = BubbleOverlayService.ACTION_HIDE
                    }
                    if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                        startForegroundService(intent)
                    } else {
                        startService(intent)
                    }
                    result.success(true)
                }
                "updateCount" -> {
                    val count = (call.arguments as? Map<*, *>)?.get("count") as? Int ?: 0
                    val intent = Intent(this, BubbleOverlayService::class.java).apply {
                        action = BubbleOverlayService.ACTION_UPDATE
                        putExtra(BubbleOverlayService.EXTRA_COUNT, count)
                    }
                    if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                        startForegroundService(intent)
                    } else {
                        startService(intent)
                    }
                    result.success(true)
                }
                "checkOverlayPermission" -> {
                    result.success(canDrawOverlays())
                }
                "requestOverlayPermission" -> {
                    requestOverlayPermission()
                    result.success(true)
                }
                else -> {
                    result.notImplemented()
                }
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        handleIntent(intent)
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleIntent(intent)
    }

    private fun handleIntent(intent: Intent?) {
        intent?.let {
            // Handle complaint detail from bubble overlay
            if (it.getBooleanExtra("open_detail", false)) {
                val complaintId = it.getStringExtra("complaint_id")
                if (!complaintId.isNullOrEmpty()) {
                    android.util.Log.d("MainActivity", "Opening detail for complaint ID: $complaintId")
                    // Save to SharedPreferences for immediate access after splash
                    val prefs = getSharedPreferences("flutter SharedPreferences", MODE_PRIVATE)
                    prefs.edit().putString("pending_complaint_id", complaintId).apply()
                    prefs.edit().putBoolean("skip_splash_to_detail", true).apply()
                    
                    // Send to Flutter via MethodChannel
                    methodChannel?.invokeMethod("openComplaintDetail", mapOf("id" to complaintId))
                }
            }
            
            // Handle deep link from bubble tap (legacy)
            val route = it.getStringExtra("route")
            if (route == "/complaints") {
                // The route will be handled by Flutter's routing system
            }
        }
    }

    private fun canDrawOverlays(): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            Settings.canDrawOverlays(this)
        } else {
            true
        }
    }

    private fun requestOverlayPermission() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            if (!Settings.canDrawOverlays(this)) {
                val intent = Intent(
                    Settings.ACTION_MANAGE_OVERLAY_PERMISSION,
                    Uri.parse("package:$packageName")
                )
                startActivityForResult(intent, OVERLAY_PERMISSION_REQUEST_CODE)
            }
        }
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == OVERLAY_PERMISSION_REQUEST_CODE) {
            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                if (Settings.canDrawOverlays(this)) {
                    // Permission granted
                    android.util.Log.d("MainActivity", "Overlay permission granted")
                } else {
                    // Permission denied
                    android.util.Log.w("MainActivity", "Overlay permission denied")
                }
            }
        }
    }
}
