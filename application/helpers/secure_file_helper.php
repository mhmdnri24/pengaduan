<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('secure_file_url')) {

    function secure_file_url($file_id, $expiration_minutes = 60) {
        $CI =& get_instance();
        
        $secret_key = $CI->config->item('encryption_key') ?: 'default-secret-key';
        $expires = time() + ($expiration_minutes * 60);
        $data_to_sign = $file_id . '|' . $expires;
        $signature = hash_hmac('sha256', $data_to_sign, $secret_key);
        
        $params = array(
            'id' => $file_id,
            'expires' => $expires,
            'signature' => $signature
        );
        
        return site_url('filemanager/serve?' . http_build_query($params));
    }
}

if (!function_exists('validate_secure_url')) {

    function validate_secure_url($file_id, $expires, $signature) {
        $CI =& get_instance();
        
        if (time() > $expires) {
            return false;
        }
        
        $secret_key = $CI->config->item('encryption_key') ?: 'default-secret-key';
        $data_to_sign = $file_id . '|' . $expires;
        $expected_signature = hash_hmac('sha256', $data_to_sign, $secret_key);
        
        return hash_equals($expected_signature, $signature);
    }
}

if (!function_exists('get_file_preview_url')) {
    /**
     * Get URL untuk preview file dengan Google Docs Viewer
     *
     * @param string $file_path Path file
     * @return string Preview URL
     */
    function get_file_preview_url($file_path) {
        if (empty($file_path)) {
            return '';
        }

        // Generate secure URL untuk file
        $secure_url = secure_file_url($file_path, 120); // 2 jam
        $encoded_url = urlencode($secure_url);

        // Gunakan Google Docs Viewer untuk preview
        return "https://docs.google.com/viewer?url={$encoded_url}&embedded=true";
    }
}

if (!function_exists('validate_file_upload')) {
    /**
     * Validasi file upload
     *
     * @param array $file File dari $_FILES
     * @param array $allowed_types Allowed file types
     * @param int $max_size Max size dalam KB
     * @return array Result dengan status dan message
     */
    function validate_file_upload($file, $allowed_types = [], $max_size = 5120) {
        if (empty($allowed_types)) {
            $allowed_types = ['PDF', 'JPG', 'PNG'];
        }

        // Cek apakah file ada
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['status' => false, 'message' => 'File tidak ditemukan'];
        }

        // Cek error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'Error saat upload file'];
        }

        // Cek ukuran file
        $file_size_kb = $file['size'] / 1024;
        if ($file_size_kb > $max_size) {
            return ['status' => false, 'message' => "Ukuran file terlalu besar. Maksimal {$max_size} KB"];
        }

        // Cek tipe file
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array_map('strtolower', $allowed_types);

        if (!in_array($file_extension, $allowed_extensions)) {
            $allowed_str = implode(', ', $allowed_types);
            return ['status' => false, 'message' => "Tipe file tidak diizinkan. Hanya: {$allowed_str}"];
        }

        // Validasi MIME type dengan metode alternatif
        $mime_type = '';

        // Coba gunakan finfo jika tersedia
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        // Fallback ke mime_content_type jika finfo tidak tersedia
        if (empty($mime_type) && function_exists('mime_content_type')) {
            $mime_type = mime_content_type($file['tmp_name']);
        }

        // Fallback ke getimagesize untuk image files
        if (empty($mime_type) && in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
            $image_info = getimagesize($file['tmp_name']);
            if ($image_info !== false) {
                $mime_type = $image_info['mime'];
            }
        }

        // Fallback berdasarkan extension
        if (empty($mime_type)) {
            $allowed_mimes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png'
            ];

            if (isset($allowed_mimes[$file_extension])) {
                $mime_type = $allowed_mimes[$file_extension];
            }
        }

        // Validasi MIME type
        $allowed_mimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];

        $valid_mime = false;
        foreach ($allowed_extensions as $ext) {
            if (isset($allowed_mimes[$ext]) && $allowed_mimes[$ext] === $mime_type) {
                $valid_mime = true;
                break;
            }
        }

        // Jika MIME type tidak terdeteksi, validasi berdasarkan extension saja
        if (empty($mime_type)) {
            $mime_type = $allowed_mimes[$file_extension] ?? 'application/octet-stream';
            $valid_mime = true; // Allow jika extension valid
        }

        if (!$valid_mime) {
            return ['status' => false, 'message' => 'Tipe file tidak valid'];
        }

        return ['status' => true, 'message' => 'File valid', 'mime_type' => $mime_type];
    }
}

if (!function_exists('generate_unique_filename')) {
    /**
     * Generate unique filename untuk upload
     *
     * @param string $original_name Original filename
     * @param string $prefix Prefix untuk filename
     * @return string Unique filename
     */
    function generate_unique_filename($original_name, $prefix = 'doc') {
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid()), 0, 8);

        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }
}

if (!function_exists('format_file_size')) {
    /**
     * Format file size ke human readable
     *
     * @param int $bytes File size dalam bytes
     * @return string Formatted size
     */
    function format_file_size($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}