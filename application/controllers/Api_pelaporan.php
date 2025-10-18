<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API Controller untuk Modul Pelaporan
 * Endpoint: /api/v1/pelaporan
 */
class Api_pelaporan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->database();
        $this->load->model('Pengaturan_m');
        // $this->load->model('Pelaporan_m');
    }

    protected function log_api_request($status_code, $response_time = null)
    {
        if (!$response_time) {
            $response_time = 0; // For demo purposes
        }

        $log_data = [
            'ip_address' => $this->input->ip_address(),
            'domain' => isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : null,
            'endpoint' => $this->uri->uri_string(),
            'method' => $this->input->method(),
            'status_code' => $status_code,
            'response_time' => round($response_time * 1000, 2),
            'user_agent' => $this->input->user_agent(),
            'request_data' => json_encode($this->input->get())
        ];

        $this->db->insert('api_logs', $log_data);
    }

    public function test()
    {
        $this->send_response(200, 'API Pelaporan test berhasil', [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip' => $this->input->ip_address(),
            'api_key' => $this->input->get_request_header('X-API-Key')
        ]);
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

        http_response_code($status_code);
        echo json_encode([
            'status' => $status_code >= 200 && $status_code < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * GET /api/v1/pelaporan
     * Mengambil list pelaporan dengan pagination dan filter
     */
    public function index()
    {
        // For test purposes, skip authentication
        // $jwt_payload = $this->validate_jwt();
        // if (!$jwt_payload) {
        //     $this->send_response(401, 'Authentication required');
        // }

        $start_time = microtime(true);

        try {
            // Simple test response for demo - return empty array since no data
            $this->send_response(200, 'Data pelaporan berhasil diambil', [
                'pelaporan' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 20,
                    'total_records' => 0,
                    'total_pages' => 0,
                    'has_next' => false,
                    'has_prev' => false
                ]
            ]);

        } catch (Exception $e) {
            $response_time = microtime(true) - $start_time;
            $this->log_api_request(500, $response_time);
            log_message('error', 'API Pelaporan Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/pelaporan/{id}
     * Mengambil detail pelaporan berdasarkan ID
     */
    public function show($id = null)
    {
        if (!$id) {
            $this->send_response(400, 'ID pelaporan diperlukan');
        }

        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            $pelaporan = $this->db->select('p.*, k.nama_kategori, m.nama as nama_pelapor, m.no_hp, m.email')
                                  ->from('pelaporan p')
                                  ->join('kategori_pelaporan k', 'p.id_kategori = k.id_kategori', 'left')
                                  ->join('masyarakat m', 'p.id_masyarakat = m.id_masyarakat', 'left')
                                  ->where('p.id_pelaporan', $id)
                                  ->get()
                                  ->row();

            if (!$pelaporan) {
                $this->send_response(404, 'Pelaporan tidak ditemukan');
            }

            // Format response
            $pelaporan->foto_url = $pelaporan->foto ? base_url('uploads/pelaporan/' . $pelaporan->foto) : null;
            $pelaporan->created_at_formatted = date('d/m/Y H:i', strtotime($pelaporan->created_at));
            $pelaporan->updated_at_formatted = $pelaporan->updated_at ? date('d/m/Y H:i', strtotime($pelaporan->updated_at)) : null;

            // Get komentar/timeline if exists
            $komentar = $this->db->where('id_pelaporan', $id)
                                ->order_by('created_at', 'ASC')
                                ->get('komentar_pelaporan')
                                ->result();

            $pelaporan->timeline = $komentar;

            $this->send_response(200, 'Detail pelaporan berhasil diambil', $pelaporan);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Detail Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * POST /api/v1/pelaporan
     * Membuat pelaporan baru
     */
    public function create()
    {
        // Validate JWT token (user must be logged in)
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            // Get POST data
            $data = [
                'judul' => $this->input->post('judul'),
                'deskripsi' => $this->input->post('deskripsi'),
                'alamat' => $this->input->post('alamat'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'id_kategori' => $this->input->post('id_kategori'),
                'id_masyarakat' => $jwt_payload->user_id ?? null, // Assuming user is masyarakat
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Validate required fields
            $required_fields = ['judul', 'deskripsi', 'alamat', 'id_kategori'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    $this->send_response(400, "Field {$field} wajib diisi");
                }
            }

            // Handle file upload if provided
            if (!empty($_FILES['foto']['name'])) {
                $upload_config = [
                    'upload_path' => './uploads/pelaporan/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size' => 2048, // 2MB
                    'file_name' => 'pelaporan_' . time() . '_' . rand(1000, 9999)
                ];

                $this->load->library('upload', $upload_config);

                if ($this->upload->do_upload('foto')) {
                    $upload_data = $this->upload->data();
                    $data['foto'] = $upload_data['file_name'];
                } else {
                    $this->send_response(400, 'Gagal upload foto: ' . $this->upload->display_errors('', ''));
                }
            }

            // Insert pelaporan
            $this->db->insert('pelaporan', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                $results = $this->send_test();
                $this->send_response(201, 'Pelaporan berhasil dibuat', [
                    'id_pelaporan' => $insert_id,
                    'status' => 'pending',
                    'results' => $results
                ]);

              
            } else {
                $this->send_response(500, 'Gagal membuat pelaporan');
            }

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Create Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * PUT /api/v1/pelaporan/{id}/status
     * Update status pelaporan (untuk admin/petugas)
     */
    public function update_status($id = null)
    {
        if (!$id) {
            $this->send_response(400, 'ID pelaporan diperlukan');
        }

        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            $status = $this->input->input_stream('status');
            $komentar = $this->input->input_stream('komentar');

            if (empty($status)) {
                $this->send_response(400, 'Status wajib diisi');
            }

            // Validasi status yang diizinkan
            $allowed_status = ['pending', 'proses', 'selesai', 'ditolak'];
            if (!in_array($status, $allowed_status)) {
                $this->send_response(400, 'Status tidak valid');
            }

            // Update status pelaporan
            $update_data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->where('id_pelaporan', $id)->update('pelaporan', $update_data);

            if ($this->db->affected_rows() > 0) {
                // Tambah komentar jika ada
                if (!empty($komentar)) {
                    $komentar_data = [
                        'id_pelaporan' => $id,
                        'komentar' => $komentar,
                        'id_user' => $jwt_payload->user_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('komentar_pelaporan', $komentar_data);
                }

                $this->send_response(200, 'Status pelaporan berhasil diupdate');
            } else {
                $this->send_response(404, 'Pelaporan tidak ditemukan atau tidak ada perubahan');
            }

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Update Status Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/pelaporan/kategori
     * Mengambil list kategori pelaporan
     */
    public function kategori()
    {
        try {
            $kategori = $this->db->where('status', 'aktif')
                                ->order_by('nama_kategori', 'ASC')
                                ->get('kategori_pelaporan')
                                ->result();

            $this->send_response(200, 'Data kategori berhasil diambil', $kategori);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Kategori Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/pelaporan/statistics
     * Mengambil statistik pelaporan
     */
    public function statistics()
    {
        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            // Total pelaporan
            $total = $this->db->count_all('pelaporan');

            // By status
            $status_stats = $this->db->select('status, COUNT(*) as jumlah')
                                    ->group_by('status')
                                    ->get('pelaporan')
                                    ->result();

            // By kategori
            $kategori_stats = $this->db->select('k.nama_kategori, COUNT(p.id_pelaporan) as jumlah')
                                      ->from('pelaporan p')
                                      ->join('kategori_pelaporan k', 'p.id_kategori = k.id_kategori', 'left')
                                      ->group_by('p.id_kategori')
                                      ->order_by('jumlah', 'DESC')
                                      ->get()
                                      ->result();

            // Monthly trend (last 12 months)
            $monthly_trend = $this->db->select("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as jumlah")
                                     ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-12 months')))
                                     ->group_by("DATE_FORMAT(created_at, '%Y-%m')")
                                     ->order_by("DATE_FORMAT(created_at, '%Y-%m')", 'ASC')
                                     ->get('pelaporan')
                                     ->result();

            $this->send_response(200, 'Statistik pelaporan berhasil diambil', [
                'total_pelaporan' => $total,
                'by_status' => $status_stats,
                'by_kategori' => $kategori_stats,
                'monthly_trend' => $monthly_trend
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Statistics Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }


    public function send_test()
    {

        $this->load->helper('fcm_helper');
        $tokens = [
            // 'fqWq_meMTbqxLFBs0UnZEh:APA91bGyvhaB-Y_9N6o5qEah1jrtUX5PwgPeqZpzbWditx-9Rsc-ci5Zeq25m6-DjJ1gppOLDq_He0yhPr4WweWq6LY6K9gw-gCmErfHL1hxiwJ1_1YNyNE'
        ];
        
        $devices = $this->db->select('fcm_token')->from('api_device')->get()->result();
        foreach ($devices as $device) {
            $tokens[] = $device->fcm_token;
        }

        $messageDataOnly = [
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'status' => 'done',
                'screen' => 'overlay_test',
                'timestamp' => date('c'),
                'count' => '4',
                'title' => '🚨 Pengaduan Baru',
                'body' => 'Ada pengaduan masuk dari ERHA - Test Background (data-only)',
            ],
            'android' => [
                'priority' => 'high'
            ]
        ];
        $results = send_fcm_data_only($tokens, $messageDataOnly);
        
        return ($results);
    }
}