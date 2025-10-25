<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api_pengaturan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');

        // Load database
        $this->load->database();
        if (!$this->db->conn_id) {
            // If database connection fails, log error
            error_log('Database connection failed in Api_pengaturan');
        }

        $this->start_time = microtime(true);
    }

    protected function log_api_request($status_code, $response_time = null)
    {
        if (!$response_time) {
            $response_time = isset($this->start_time) ? (microtime(true) - $this->start_time) : 0;
        }

        $ip_address = $this->input->ip_address();
        if (empty($ip_address)) {
            $ip_address = '127.0.0.1'; // fallback
        }

        $endpoint = $this->uri->uri_string();
        if (empty($endpoint)) {
            $endpoint = 'unknown';
        }

        $request_data = $this->input->get();
        if (empty($request_data)) {
            $request_data = '[]';
        } else {
            $request_data = json_encode($request_data);
            if (strlen($request_data) > 1000) {
                $request_data = substr($request_data, 0, 1000) . '...';
            }
        }

        $log_data = [
            'ip_address' => $ip_address,
            'domain' => isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null,
            'endpoint' => $endpoint,
            'method' => $this->input->method(),
            'status_code' => $status_code,
            'response_time' => round($response_time * 1000, 2),
            'user_agent' => $this->input->user_agent(),
            'request_data' => $request_data
        ];

        $result = $this->db->insert('api_logs', $log_data);

        // Debug: temporary error reporting
        if (!$result) {
            error_log('API Log Insert FAILED for endpoint: ' . $endpoint);
            error_log('Last Query: ' . $this->db->last_query());
            $db_error = $this->db->error();
            error_log('DB Error: ' . json_encode($db_error));
        } else {
            error_log('API Log Insert SUCCESS for endpoint: ' . $endpoint);
        }
    }

    public function test()
    {
        echo json_encode([
            'status' => 'success',
            'message' => 'API Pengaturan test berhasil',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip' => $this->input->ip_address(),
            'api_key' => $this->input->get_request_header('X-API-Key')
        ]);
    }

    public function index()
    {
        try {
            // Load the ce_model for accessing opsi table
            $this->load->model('Ce_model');
            
            // Get settings from opsi table
            $settings = [
                'nama_situs' => $this->Ce_model->get_opsi('nama_situs') ?: 'Lapor Pak Wali',
                'tagline' => $this->Ce_model->get_opsi('tagline') ?: 'Sistem Pelaporan Masyarakat',
                'logo' => $this->Ce_model->get_opsi('logo') ? base_url($this->Ce_model->get_opsi('logo')) : null,
                'favicon' => $this->Ce_model->get_opsi('favicon') ? base_url($this->Ce_model->get_opsi('favicon')) : null,
                'nomor_kontak' => $this->Ce_model->get_opsi('nomor_kontak') ?: '',
                'email' => $this->Ce_model->get_opsi('email') ?: '',
                'alamat' => $this->Ce_model->get_opsi('alamat') ?: ''
            ];
            
            $this->send_response(200, 'Pengaturan berhasil diambil', $settings);
        } catch (Exception $e) {
            error_log('Error in Api_pengaturan index: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan saat mengambil pengaturan');
        }
    }

    protected function send_response($status_code = 200, $message = '', $data = null)
    {
        // Calculate response time and log request
        if (isset($this->start_time)) {
            $response_time = microtime(true) - $this->start_time;
        } else {
            $response_time = 0;
        }

        $this->log_api_request($status_code, $response_time);

        // Debug: Try direct insert to make sure logging works
        try {
            $direct_data = [
                'ip_address' => 'DEBUG_IP',
                'endpoint' => 'DEBUG_' . $this->uri->uri_string(),
                'method' => $this->input->method(),
                'status_code' => $status_code,
                'response_time' => round($response_time * 1000, 2),
                'user_agent' => 'DEBUG_AGENT',
                'request_data' => '{"debug":"test"}'
            ];
            $this->db->insert('api_logs', $direct_data);
        } catch (Exception $e) {
            // Ignore debug insert errors
        }

        http_response_code($status_code);
        echo json_encode([
            'status' => $status_code >= 200 && $status_code < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}