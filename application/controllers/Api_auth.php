<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    public function index() {
        $action = $this->input->get('action');
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Log for debugging
        error_log("API Auth: action=$action, method=$method");
        
        try {
            switch ($action) {
                case 'settings':
                    $this->handleSettings();
                    break;
                case 'login':
                    $this->handleLogin($method);
                    break;
                case 'verify-otp':
                    $this->handleVerifyOTP($method);
                    break;
                default:
                    $this->sendResponse(404, 'Endpoint not found');
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse(500, 'Internal server error: ' . $e->getMessage());
        }
    }

    private function handleSettings() {
        $settings = $this->getSiteSettings();
        $this->sendResponse(200, 'Settings retrieved successfully', $settings);
    }

    private function handleLogin($method) {
        error_log("handleLogin: method=$method");
        
        if ($method !== 'POST') {
            $this->sendResponse(405, 'Method not allowed');
            return;
        }
        
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        $nik = $input['nik'] ?? '';
        $phone = $input['phone'] ?? '';
        
        if (empty($nik) && empty($phone)) {
            $this->sendResponse(400, 'NIK or phone number is required');
            return;
        }
        
        $user = null;
        
        if (!empty($nik)) {
            // Login with NIK
            if (!preg_match('/^[0-9]{16}$/', $nik)) {
                $this->sendResponse(400, 'NIK must be 16 digits');
                return;
            }
            $user = $this->getUserByNIK($nik);
        } else {
            // Login with phone
            if (!preg_match('/^08[0-9]{8,11}$/', $phone)) {
                $this->sendResponse(400, 'Phone number must start with 08 and be 10-13 digits');
                return;
            }
            $user = $this->getUserByPhone($phone);
        }
        
        if (!$user) {
            $this->sendResponse(404, 'User not found or account is not active');
            return;
        }
        
        // Generate OTP
        $otp = sprintf('%06d', mt_rand(0, 999999));
        
        // Update OTP in database
        if (!$this->updateOTP($user['id'], $otp)) {
            $this->sendResponse(500, 'Failed to update OTP');
            return;
        }
        
        // Send OTP via WhatsApp
        $otpSent = $this->sendWhatsAppOTP($user['no_telpon'], $user['nama_lengkap'], $otp);
        
        // Return response (don't expose if OTP was actually sent for security)
        $this->sendResponse(200, 'OTP sent successfully', [
            'nik' => $user['nik'],
            'nama_lengkap' => $user['nama_lengkap'],
            'no_telpon' => $user['no_telpon'],
            'otp_sent' => true
        ]);
    }

    private function handleVerifyOTP($method) {
        if ($method !== 'POST') {
            $this->sendResponse(405, 'Method not allowed');
            return;
        }
        
        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            // Fallback to POST data
            $input = $_POST;
        }
        
        $nik = $input['nik'] ?? '';
        $phone = $input['phone'] ?? '';
        $otp = $input['otp'] ?? '';
        
        if (empty($otp)) {
            $this->sendResponse(400, 'OTP is required');
            return;
        }
        
        if (empty($nik) && empty($phone)) {
            $this->sendResponse(400, 'NIK or phone number is required');
            return;
        }
        
        $user = null;
        
        if (!empty($nik)) {
            $user = $this->getUserByNIK($nik);
        } else {
            $user = $this->getUserByPhone($phone);
        }
        
        if (!$user) {
            $this->sendResponse(404, 'User not found');
            return;
        }
        
        // Verify OTP
        $verifiedUser = $this->verifyOTP($user['id'], $otp);
        
        if (!$verifiedUser) {
            $this->sendResponse(401, 'Invalid OTP');
            return;
        }
        
        // Return user data (excluding sensitive information)
        $this->sendResponse(200, 'Login successful', [
            'id' => $verifiedUser['id'],
            'nama_lengkap' => $verifiedUser['nama_lengkap'],
            'nik' => $verifiedUser['nik'],
            'no_telpon' => $verifiedUser['no_telpon'],
            'token' => $verifiedUser['token'],
            'foto_profil_url' => $verifiedUser['foto_profil'] ? '../../' . $verifiedUser['foto_profil'] : null
        ]);
    }

    private function getSiteSettings() {
        $settings = [];
        
        // Get nama_situs
        $result = $this->db->query("SELECT nilai FROM opsi WHERE kunci = 'nama_situs' LIMIT 1");
        if ($result && $row = $result->row_array()) {
            $settings['nama_situs'] = $row['nilai'];
        } else {
            $settings['nama_situs'] = 'Lapor Pak Wali';
        }
        
        // Get tagline
        $result = $this->db->query("SELECT nilai FROM opsi WHERE kunci = 'tagline' LIMIT 1");
        if ($result && $row = $result->row_array()) {
            $settings['tagline'] = $row['nilai'];
        } else {
            $settings['tagline'] = 'Sistem Pelaporan Masyarakat';
        }
        
        return $settings;
    }

    private function getUserByNIK($nik) {
        $result = $this->db->query("SELECT * FROM masyarakat WHERE nik = ? AND status_aktif = 1 LIMIT 1", array($nik));
        if ($result && $row = $result->row_array()) {
            return $row;
        }
        return null;
    }

    private function getUserByPhone($phone) {
        $result = $this->db->query("SELECT * FROM masyarakat WHERE no_telpon = ? AND status_aktif = 1 LIMIT 1", array($phone));
        if ($result && $row = $result->row_array()) {
            return $row;
        }
        return null;
    }

    private function updateOTP($userId, $otp) {
        $this->db->query("UPDATE masyarakat SET otp = ?, updated_at = NOW() WHERE id = ?", array($otp, $userId));
        return $this->db->affected_rows() > 0;
    }

    private function verifyOTP($userId, $otp) {
        $result = $this->db->query("SELECT * FROM masyarakat WHERE id = ? AND otp = ? AND status_aktif = 1 LIMIT 1", array($userId, $otp));
        if ($result && $row = $result->row_array()) {
            // Clear OTP after successful verification
            $token = md5($row['id'] . $row['nik'] . time());
            $this->db->query("UPDATE masyarakat SET otp = NULL, token = ? WHERE id = ?", array($token, $row['id']));
            
            $row['token'] = $token;
            return $row;
        }
        return null;
    }

    private function sendWhatsAppOTP($phoneNumber, $nama, $otp) {
        // Load the wa_helper
        $this->load->helper('wa');
        
        // Send OTP using template
        $result = kirim_template($phoneNumber, 'masyarakat_otp', [
            'nama' => $nama,
            'otp' => $otp
        ], null, [
            'nama_tujuan' => $nama,
            'created_by' => 1 // System admin
        ]);
        
        return $result;
    }

    private function sendResponse($statusCode, $message, $data = null) {
        http_response_code($statusCode);
        
        $response = [
            'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
}