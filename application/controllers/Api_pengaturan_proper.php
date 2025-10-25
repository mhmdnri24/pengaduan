<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api.php';

/**
 * API Controller untuk Modul Pengaturan
 * Endpoint: /api/v1/pengaturan
 *
 * Features:
 * - JWT Authentication
 * - Get logo, favicon, nama_situs, tagline dari tabel opsi
 * - Rate limiting & domain whitelist
 */
class Api_pengaturan_proper extends api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pengaturan_m');
    }

    /**
     * GET /api/v1/pengaturan
     * Mengambil data pengaturan aplikasi dengan JWT validation
     */
    public function index()
    {
        try {
            // Ambil data dari tabel opsi
            $settings = [
                'nama_situs' => ce_opsi('nama_situs', ''),
                'tagline' => ce_opsi('tagline', ''),
                'logo' => ce_opsi('logo') ? base_url(ce_opsi('logo')) : null,
                'favicon' => ce_opsi('favicon') ? base_url(ce_opsi('favicon')) : null,
            ];

            // Tambahkan informasi request
            $settings['requested_by'] = [
                'ip_address' => $this->input->ip_address(),
                'domain' => $this->get_referer_domain(),
            ];

            // Tambahkan timestamp request
            $settings['timestamp'] = date('Y-m-d H:i:s');
            $settings['api_version'] = 'v1';

            $this->send_response(200, 'Data pengaturan berhasil diambil', $settings);

        } catch (Exception $e) {
            log_message('error', 'API Pengaturan Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }


    /**
     * GET /api/v1/pengaturan/test
     * Endpoint test tanpa JWT requirement
     */
    public function test()
    {
        // Skip domain validation for test endpoint
        $this->skip_domain_validation = true;

        // Test endpoint tanpa JWT untuk debugging
        $data = [
            'status' => 'API Pengaturan Test OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip_address' => $this->input->ip_address(),
            'api_key_provided' => !empty($this->input->get_request_header('X-API-Key')),
            'domain' => $this->get_referer_domain(),
            'base_url' => $this->api_settings->base_url ?? '/api/v1/',
            'rate_limit_status' => 'OK'
        ];

        $this->send_response(200, 'API test berhasil', $data);
    }

    /**
     * GET /api/v1/pengaturan/config
     * Mengambil konfigurasi API (untuk debugging)
     */
    public function config()
    {
        try {
            $config = [
                'api_enabled' => $this->api_settings->api_enabled ?? false,
                'base_url' => $this->api_settings->base_url ?? '/api/v1/',
                'rate_limit_per_minute' => $this->api_settings->rate_limit_per_minute ?? 60,
                'rate_limit_per_hour' => $this->api_settings->rate_limit_per_hour ?? 1000,
                'domains_allowed' => array_map(function($domain) {
                    return [
                        'domain' => $domain->domain,
                        'description' => $domain->description,
                        'active' => (bool)$domain->is_active
                    ];
                }, $this->Pengaturan_m->get_api_domains()),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            $this->send_response(200, 'Konfigurasi API berhasil diambil', $config);

        } catch (Exception $e) {
            log_message('error', 'API Pengaturan Config Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }
}