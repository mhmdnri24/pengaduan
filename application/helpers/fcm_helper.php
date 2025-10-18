<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kirim FCM Data-only Notification HTTP v1 API (CodeIgniter 3)
 * Bisa kirim ke multiple token
 * 
 * @param array $tokens Array of device tokens
 * @param array $messageData Array payload data, misal ['data'=>..., 'android'=>...]
 * @param string $serviceAccountPath Path ke JSON Service Account
 * @return array hasil per token
 */
if (!function_exists('send_fcm_data_only')) {
    function send_fcm_data_only($tokens, $messageData, $serviceAccountPath = null) {
        $serviceAccountPath = APPPATH . 'config/service-account.json';
        if (!file_exists($serviceAccountPath)) {
            return ['error' => 'Service account file not found at ' . $serviceAccountPath];
        }

        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        $projectId = $serviceAccount['project_id'];

        // ===== JWT Helper =====
        function base64UrlEncode($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        // ===== Generate JWT =====
        $now = time();
        $header = ['alg'=>'RS256','typ'=>'JWT'];
        $claim = [
            'iss'=>$serviceAccount['client_email'],
            'scope'=>'https://www.googleapis.com/auth/firebase.messaging',
            'aud'=>'https://oauth2.googleapis.com/token',
            'iat'=>$now,
            'exp'=>$now + 3600
        ];

        $jwtHeader = base64UrlEncode(json_encode($header));
        $jwtClaim = base64UrlEncode(json_encode($claim));
        $unsignedJwt = $jwtHeader . '.' . $jwtClaim;

        $privateKey = $serviceAccount['private_key'];
        openssl_sign($unsignedJwt, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $jwt = $unsignedJwt . '.' . base64UrlEncode($signature);

        // ===== Get Access Token =====
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        $response = curl_exec($ch);
        curl_close($ch);

        $respData = json_decode($response, true);
        if (!isset($respData['access_token'])) {
            return ['error' => 'Gagal generate access token', 'raw' => $response];
        }
        $accessToken = $respData['access_token'];

        $fcmUrl = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

        $results = [];
        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token
                ]
            ];

            // Merge messageData (data-only + android) ke message
            if (isset($messageData['data'])) {
                $payload['message']['data'] = $messageData['data'];
            }
            if (isset($messageData['android'])) {
                $payload['message']['android'] = $messageData['android'];
            }

            $ch = curl_init($fcmUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($ch);
            curl_close($ch);

            $results[] = [
                'token' => $token,
                'response' => $result
            ];

            // Optional delay 1 detik
            sleep(1);
        }

        return $results;
    }
}
