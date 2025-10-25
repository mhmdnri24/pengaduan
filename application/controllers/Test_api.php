<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_api extends CI_Controller
{
    public function index()
    {
        echo json_encode([
            'status' => 'success',
            'message' => 'API test berhasil',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function test()
    {
        echo json_encode([
            'status' => 'success',
            'message' => 'Test endpoint works',
            'method' => $this->input->method(),
            'api_key' => $this->input->get_request_header('X-API-Key')
        ]);
    }
}