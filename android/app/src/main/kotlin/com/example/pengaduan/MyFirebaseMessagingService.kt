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

        // Extract complaint data from FCM payload
        val complaintData = remoteMessage.data["pelaporan"]
        var complaintId = "1" // fallback ID
        
        // Try to extract complaint ID from various possible fields
        if (!remoteMessage.data["id"].isNullOrEmpty()) {
            complaintId = remoteMessage.data["id"]!!
        } else if (!remoteMessage.data["body"].isNullOrEmpty()) {
            complaintId = remoteMessage.data["body"]!!
        }
        
        Log.d(TAG, "Extracted complaint ID: $complaintId")
        Log.d(TAG, "Complaint data: $complaintData")

        // Start the BubbleOverlayService with ID and complaint data
        val intent = Intent(this, BubbleOverlayService::class.java).apply {
            action = BubbleOverlayService.ACTION_SHOW_WITH_ID
            putExtra(BubbleOverlayService.EXTRA_ID, complaintId)
            // Add complaint data as extra
            if (!complaintData.isNullOrEmpty()) {
                putExtra("complaint_data", complaintData)
            }
        }
        Log.d(TAG, "Starting BubbleOverlayService with ID=$complaintId and data=$complaintData")

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
