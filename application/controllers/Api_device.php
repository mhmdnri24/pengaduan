<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api.php';

/**
 * API Controller untuk Modul Device
 * Endpoint: /api/v1/device
 *
 * Features:
 * - GET device dengan pagination & filter
 * - POST buat/update device
 */
class Api_device extends api
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /api/v1/device
     * Mengambil list device dengan pagination dan filter status
     */
    public function index()
    {
        // Only allow GET method for this endpoint
        if ($this->input->method() !== 'get') {
            $this->send_response(405, 'Method not allowed. Use GET for listing devices');
        }

        try {
            // Get query parameters
            $page = (int)$this->input->get('page', true) ?: 1;
            $limit = (int)$this->input->get('limit', true) ?: 20;
            $status = $this->input->get('status', true);

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Build query - adjust to match existing table structure
            $this->db->select('*')
                     ->from('api_device');

            // Filter by masyarakat_id if provided
            if (!empty($status)) {
                $this->db->where('masyarakat_id', $status); // status parameter used for masyarakat_id filter
            }

            // Get total count for pagination
            $total_records = $this->db->count_all_results('', false);

            // Apply pagination and ordering
            $this->db->limit($limit, $offset);
            $this->db->order_by('created_at', 'DESC');

            $devices = $this->db->get()->result();

            // Check if data is empty
            if (empty($devices)) {
                $this->send_response(404, 'Tidak ada data device ditemukan', [
                    'devices' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $limit,
                        'total_records' => 0,
                        'total_pages' => 0,
                        'has_next' => false,
                        'has_prev' => false
                    ]
                ]);
            }

            // Format response data
            foreach ($devices as &$item) {
                $item->created_at_formatted = date('d/m/Y H:i', strtotime($item->created_at));
            }

            // Calculate pagination info
            $total_pages = ceil($total_records / $limit);

            $pagination = [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total_records,
                'total_pages' => $total_pages,
                'has_next' => $page < $total_pages,
                'has_prev' => $page > 1
            ];

            $this->send_response(200, 'Data device berhasil diambil', [
                'devices' => $devices,
                'pagination' => $pagination,
                'filters_applied' => [
                    'status' => $status
                ]
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Device Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }


    public function insert_or_update()
    {
        try {
            $device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
            $masyarakat_id = isset($_POST['masyarakat_id']) ? trim($_POST['masyarakat_id']) : null;
            $fcm_token = isset($_POST['fcm_token']) ? trim($_POST['fcm_token']) : null;

            if (empty($device_id)) {
                $this->send_response(400, 'Field device_id wajib diisi');
            }

            $data = [
                'device_id' => $device_id,
                'masyarakat_id' => $masyarakat_id,
                'fcm_token' => $fcm_token, 
            ];

            // Cek apakah sudah ada data dengan device_id + masyarakat_id
            $this->db->where('device_id', $device_id);
            $this->db->where('masyarakat_id', $masyarakat_id);
            $exists = $this->db->get('api_device')->row();

            if ($exists) {
                // Jika sudah ada, lakukan update
                $this->db->where('device_id', $device_id);
                $this->db->where('masyarakat_id', $masyarakat_id);
                $this->db->update('api_device', $data);

                if ($this->db->affected_rows() >= 0) {
                    $this->send_response(200, 'Device berhasil diperbarui', [
                        'device_id' => $device_id,
                        'masyarakat_id' => $masyarakat_id,
                        'fcm_token' => $fcm_token, 
                    ]);
                } else {
                    $this->send_response(500, 'Gagal memperbarui device');
                }

            } else { 
                $this->db->insert('api_device', $data);

                if ($this->db->affected_rows() > 0) {
                    $this->send_response(201, 'Device berhasil dibuat', [
                        'device_id' => $device_id,
                        'masyarakat_id' => $masyarakat_id,
                        'fcm_token' => $fcm_token, 
                    ]);
                } else {
                    $this->send_response(500, 'Gagal membuat device');
                }
            }

        } catch (Exception $e) {
            log_message('error', 'API Device Create Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server: ' . $e->getMessage());
        }
    }


    /**
     * POST /api/v1/device/create
     * Membuat device baru.
     */
    public function create()
    {
        try {
            // Get POST data using $_POST for reliability
            $device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
            $masyarakat_id = isset($_POST['masyarakat_id']) ? trim($_POST['masyarakat_id']) : null;
            $fcm_token = isset($_POST['fcm_token']) ? trim($_POST['fcm_token']) : null;

            // Validate required fields
            if (empty($device_id)) {
                $this->send_response(400, 'Field device_id wajib diisi');
            }

            // Prepare data
            $data = [
                'device_id' => $device_id,
                'masyarakat_id' => $masyarakat_id,
                'fcm_token' => $fcm_token,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert device
            $this->db->insert('api_device', $data);

            if ($this->db->affected_rows() > 0) {
                $this->send_response(201, 'Device berhasil dibuat', [
                    'device_id' => $device_id,
                    'masyarakat_id' => $masyarakat_id,
                    'fcm_token' => $fcm_token,
                    'created_at' => $data['created_at']
                ]);
            } else {
                $this->send_response(500, 'Gagal membuat device');
            }

        } catch (Exception $e) {
            log_message('error', 'API Device Create Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server: ' . $e->getMessage());
        }
    }

    /**
     * PUT /api/v1/device/update
     * Update device berdasarkan device_id.
     */
    public function update()
    {
        try {
            // Handle PUT data
            if ($this->input->method() === 'put') {
                $put_data = file_get_contents('php://input');
                parse_str($put_data, $_PUT);
                $device_id = isset($_PUT['device_id']) ? trim($_PUT['device_id']) : '';
                $masyarakat_id = isset($_PUT['masyarakat_id']) ? trim($_PUT['masyarakat_id']) : null;
                $fcm_token = isset($_PUT['fcm_token']) ? trim($_PUT['fcm_token']) : null;
            } else {
                // Fallback to POST data for compatibility
                $device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
                $masyarakat_id = isset($_POST['masyarakat_id']) ? trim($_POST['masyarakat_id']) : null;
                $fcm_token = isset($_POST['fcm_token']) ? trim($_POST['fcm_token']) : null;
            }

            // Validate required fields
            if (empty($device_id)) {
                $this->send_response(400, 'Field device_id wajib diisi');
            }

            // Check if device exists
            $existing_device = $this->db->where('device_id', $device_id)->get('api_device')->row();
            if (!$existing_device) {
                $this->send_response(404, 'Device tidak ditemukan');
            }

            // Prepare update data
            $data = [
                'masyarakat_id' => $masyarakat_id,
                'fcm_token' => $fcm_token
            ];

            // UPDATE operation
            $this->db->where('device_id', $device_id);
            $this->db->update('api_device', $data);

            if ($this->db->affected_rows() >= 0) {
                $this->send_response(200, 'Device berhasil diupdate', [
                    'device_id' => $device_id,
                    'masyarakat_id' => $masyarakat_id,
                    'fcm_token' => $fcm_token
                ]);
            } else {
                $this->send_response(500, 'Gagal mengupdate device');
            }

        } catch (Exception $e) {
            log_message('error', 'API Device Update Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server: ' . $e->getMessage());
        }
    }

    /**
     * DELETE /api/v1/device/delete/{id}
     * Hapus device berdasarkan ID.
     */
    public function delete($id = null)
    {
        try {
            // Validate ID parameter
            if (empty($id)) {
                $this->send_response(400, 'ID device wajib disertakan dalam URL');
            }

            // Check if device exists
            $existing_device = $this->db->where('device_id', $id)->get('api_device')->row();
            if (!$existing_device) {
                $this->send_response(404, 'Device tidak ditemukan');
            }

            // DELETE operation
            $this->db->where('device_id', $id);
            $this->db->delete('api_device');

            if ($this->db->affected_rows() > 0) {
                $this->send_response(200, 'Device berhasil dihapus', [
                    'id' => $id,
                    'device_id' => $existing_device->device_id,
                    'masyarakat_id' => $existing_device->masyarakat_id
                ]);
            } else {
                $this->send_response(500, 'Gagal menghapus device');
            }

        } catch (Exception $e) {
            log_message('error', 'API Device Delete Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server: ' . $e->getMessage());
        }
    }


    public function send_test()
    {

        $this->load->helper('fcm_helper');
        $tokens = [
            'fqWq_meMTbqxLFBs0UnZEh:APA91bGyvhaB-Y_9N6o5qEah1jrtUX5PwgPeqZpzbWditx-9Rsc-ci5Zeq25m6-DjJ1gppOLDq_He0yhPr4WweWq6LY6K9gw-gCmErfHL1hxiwJ1_1YNyNE'
        ];
        
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
        
        echo json_encode($results);
    }

    
}