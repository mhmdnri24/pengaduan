<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Security Helper
 * Helper untuk keamanan data tanpa CSRF
 */

if (!function_exists('clean_input')) {
    /**
     * Membersihkan input dari karakter berbahaya
     */
    function clean_input($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = clean_input($value);
            }
            return $data;
        }
        
        // Trim whitespace
        $data = trim($data);
        
        // Remove null bytes
        $data = str_replace(chr(0), '', $data);
        
        // Basic XSS protection
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
}

if (!function_exists('validate_numeric')) {
    /**
     * Validasi input numerik
     */
    function validate_numeric($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }
        
        if ($min !== null && $value < $min) {
            return false;
        }
        
        if ($max !== null && $value > $max) {
            return false;
        }
        
        return true;
    }
}

if (!function_exists('validate_email')) {
    /**
     * Validasi format email
     */
    function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('validate_phone')) {
    /**
     * Validasi format nomor telepon Indonesia
     */
    function validate_phone($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Check if it's a valid Indonesian phone number
        if (preg_match('/^(\+62|62|0)[0-9]{8,13}$/', $phone)) {
            return true;
        }
        
        return false;
    }
}

if (!function_exists('validate_nik')) {
    /**
     * Validasi NIK Indonesia (16 digit)
     */
    function validate_nik($nik)
    {
        // Remove all non-numeric characters
        $nik = preg_replace('/[^0-9]/', '', $nik);
        
        // Check if it's exactly 16 digits
        return strlen($nik) === 16 && is_numeric($nik);
    }
}

if (!function_exists('json_response')) {
    /**
     * Standard JSON response format
     */
    function json_response($status = true, $message = '', $data = null)
    {
        $response = array(
            'status' => $status,
            'message' => $message
        );
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename untuk upload
     */
    function sanitize_filename($filename)
    {
        // Remove path information and dots around the filename
        $filename = basename($filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        // Remove any character that isn't alphanumeric, underscore, dash, or dot
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $filename);
        
        // Remove multiple dots
        $filename = preg_replace('/\.+/', '.', $filename);
        
        return $filename;
    }
}

if (!function_exists('generate_token')) {
    /**
     * Generate random token untuk keamanan
     */
    function generate_token($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('hash_password')) {
    /**
     * Hash password dengan salt
     */
    function hash_password($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

if (!function_exists('verify_password')) {
    /**
     * Verify password dengan hash
     */
    function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
