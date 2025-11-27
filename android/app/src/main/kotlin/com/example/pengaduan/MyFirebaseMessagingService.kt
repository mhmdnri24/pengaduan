package com.example.pengaduan

import android.content.Context
import android.content.Intent
import android.util.Log
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.media.RingtoneManager
import android.os.Build
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MyFirebaseMessagingService : FirebaseMessagingService() {
    private val TAG = "MyFirebaseMsgService"

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        Log.d(TAG, "From: ${remoteMessage.from}")
        Log.d(TAG, "onMessageReceived data=${remoteMessage.data}")

        // Show notification if app is in foreground
        remoteMessage.notification?.let {
            Log.d(TAG, "Message Notification Body: ${it.body}")
            sendNotification(it.body, it.title)
        }

        // Extract complaint data from FCM payload

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

        // Check if body is an integer (Type 1 notification)
        val body = remoteMessage.data["body"]
        val isBodyInteger = body?.toIntOrNull() != null

        if (isBodyInteger) {
            // Start the BubbleOverlayService with ID and complaint data
            val intent = Intent(this, BubbleOverlayService::class.java).apply {
                action = BubbleOverlayService.ACTION_SHOW_WITH_ID
                putExtra(BubbleOverlayService.EXTRA_ID, complaintId)
                // Add complaint data as extra
                if (!complaintData.isNullOrEmpty()) {
                    // putExtra("complaint_data", complaintData)
                    putExtra(BubbleOverlayService.EXTRA_DATA, complaintData)

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
        } else {
            Log.d(TAG, "Skipping BubbleOverlayService: Body is not an integer ($body)")
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d(TAG, "FCM Token: $token")
        // You might want to send the token to your server here
    }

    private fun sendNotification(messageBody: String?, messageTitle: String?) {
        val intent = Intent(this, MainActivity::class.java)
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP)
        val pendingIntent = PendingIntent.getActivity(
            this, 0 /* Request code */, intent,
            PendingIntent.FLAG_IMMUTABLE
        )

        val channelId = "fcm_default_channel"
        val defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION)
        val notificationBuilder = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(R.mipmap.ic_launcher)
            .setContentTitle(messageTitle ?: "Pengaduan")
            .setContentText(messageBody)
            .setAutoCancel(true)
            .setSound(defaultSoundUri)
            .setContentIntent(pendingIntent)

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        // Since android Oreo notification channel is needed.
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                channelId,
                "Channel human readable title",
                NotificationManager.IMPORTANCE_DEFAULT
            )
            notificationManager.createNotificationChannel(channel)
        }

        notificationManager.notify(0 /* ID of notification */, notificationBuilder.build())
    }
}
