<?php
// Database configuration for Lapor Pak Wali
// This file connects to the main application database

// Database configuration
$db_config = [
    'hostname' => 'localhost',
    'username' => 'dashboard_user',
    'password' => 'bhsPcKTdkhtskmFK',
    'database' => 'dashboard_db'
];

// Create connection
$conn = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to get site settings
function getSiteSettings($conn) {
    $settings = [];
    
    // Get nama_situs
    $result = $conn->query("SELECT nilai FROM opsi WHERE kunci = 'nama_situs' LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $settings['nama_situs'] = $row['nilai'];
    } else {
        $settings['nama_situs'] = 'Lapor Pak Wali';
    }
    
    // Get tagline
    $result = $conn->query("SELECT nilai FROM opsi WHERE kunci = 'tagline' LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $settings['tagline'] = $row['nilai'];
    } else {
        $settings['tagline'] = 'Sistem Pelaporan Masyarakat';
    }
    
    return $settings;
}

// Function to get user by NIK
function getUserByNIK($conn, $nik) {
    $stmt = $conn->prepare("SELECT * FROM masyarakat WHERE nik = ? AND status_aktif = 1 LIMIT 1");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    
    return null;
}

// Function to get user by phone
function getUserByPhone($conn, $phone) {
    $stmt = $conn->prepare("SELECT * FROM masyarakat WHERE no_telpon = ? AND status_aktif = 1 LIMIT 1");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row;
    }
    
    return null;
}

// Function to update OTP
function updateOTP($conn, $userId, $otp) {
    $stmt = $conn->prepare("UPDATE masyarakat SET otp = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $otp, $userId);
    return $stmt->execute();
}

// Function to verify OTP
function verifyOTP($conn, $userId, $otp) {
    $stmt = $conn->prepare("SELECT * FROM masyarakat WHERE id = ? AND otp = ? AND status_aktif = 1 LIMIT 1");
    $stmt->bind_param("is", $userId, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        // Clear OTP after successful verification
        $updateStmt = $conn->prepare("UPDATE masyarakat SET otp = NULL, token = ? WHERE id = ?");
        $token = md5($row['id'] . $row['nik'] . time());
        $updateStmt->bind_param("si", $token, $row['id']);
        $updateStmt->execute();
        
        $row['token'] = $token;
        return $row;
    }
    
    return null;
}

// Function to send WhatsApp OTP
function sendWhatsAppOTP($phoneNumber, $nama, $otp) {
    // For now, we'll just return true to simulate sending OTP
    // In a real implementation, you would integrate with a WhatsApp API service
    // This is a placeholder that logs the OTP for testing purposes
    
    // Log the OTP for debugging
    error_log("WhatsApp OTP for $nama ($phoneNumber): $otp");
    
    // Return true to indicate OTP was "sent"
    return true;
}
?>