<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api.php';

class Simple_api extends api
{
    public function __construct()
    {
        // Skip parent validation for testing
        CI_Controller::__construct();

        header('Content-Type: application/json');
    }

    public function test()
    {
        // Simple test without validation
        $this->send_response(200, 'API test berhasil', [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip' => $this->input->ip_address()
        ]);
    }
}