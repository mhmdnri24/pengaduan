package com.example.pengaduan

import android.content.Intent
import android.os.Bundle
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity : FlutterActivity() {
    private val CHANNEL = "bubble_overlay"

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        
        MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL).setMethodCallHandler { call, result ->
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
                else -> {
                    result.notImplemented()
                }
            }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // Handle deep link from bubble tap
        val route = intent.getStringExtra("route")
        if (route == "/complaints") {
            // The route will be handled by Flutter's routing system
        }
    }
}
