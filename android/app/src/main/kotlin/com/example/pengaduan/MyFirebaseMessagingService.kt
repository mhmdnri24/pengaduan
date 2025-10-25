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

        // Extract ID from data payload
        // Priority: id -> body -> fallback to "1"
        var complaintId = remoteMessage.data["id"]
        if (complaintId.isNullOrEmpty()) {
            complaintId = remoteMessage.data["body"]
        }
        if (complaintId.isNullOrEmpty()) {
            complaintId = "1" // fallback
        }

        Log.d(TAG, "Extracted complaint ID: $complaintId")

        // Start the BubbleOverlayService with ID
        val intent = Intent(this, BubbleOverlayService::class.java).apply {
            action = BubbleOverlayService.ACTION_SHOW_WITH_ID
            putExtra(BubbleOverlayService.EXTRA_ID, complaintId)
        }
        Log.d(TAG, "Starting BubbleOverlayService with ID=$complaintId")

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
