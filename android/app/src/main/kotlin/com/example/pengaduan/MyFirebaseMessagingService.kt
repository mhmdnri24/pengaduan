package com.example.pengaduan

import android.content.Context
import android.content.Intent
import android.util.Log
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MyFirebaseMessagingService : FirebaseMessagingService() {
    private val TAG = "MyFirebaseMsgService"

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        Log.d(TAG, "From: ${remoteMessage.from}")
        Log.d(TAG, "onMessageReceived data=${remoteMessage.data}")

        // Start the BubbleOverlayService when a message is received
        // Try to extract a count from the data payload (if supplied)
        val dataCount = remoteMessage.data["count"]?.toIntOrNull() ?: 1

        val intent = Intent(this, BubbleOverlayService::class.java).apply {
            action = BubbleOverlayService.ACTION_SHOW
            putExtra(BubbleOverlayService.EXTRA_COUNT, dataCount)
        }
        Log.d(TAG, "Starting BubbleOverlayService with count=$dataCount")

        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            Log.d(TAG, "Using startForegroundService")
            startForegroundService(intent)
        } else {
            Log.d(TAG, "Using startService")
            startService(intent)
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d(TAG, "FCM Token: $token")
        // You might want to send the token to your server here
    }
}
