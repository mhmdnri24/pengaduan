<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api.php';

/**
 * API Controller untuk Modul Pelaporan
 * Endpoint: /api/v1/pelaporan
 *
 * Features:
 * - JWT Authentication untuk endpoint sensitif
 * - GET pelaporan dengan pagination & filter
 * - POST buat pelaporan baru
 * - GET kategori pelaporan
 * - Domain whitelist & rate limiting
 */
class Api_pelaporan_proper extends api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pelaporan/Pelaporan_m');
        $this->load->model('Pengaturan_m');
    }

    /**
     * Helper function to get user name from either masyarakat or user table
     */
    private function get_user_name($user_id)
    {
        if (!$user_id) {
            return 'System';
        }

         // If not found in user table, check masyarakat table
         $masyarakat = $this->db->select('nama_lengkap')
         ->where('id', $user_id)
         ->get('masyarakat')
         ->row();

        if ($masyarakat) {
        return $masyarakat->nama_lengkap;
        }



        // First check user table
        $user = $this->db->select('nama')
                        ->where('id_user', $user_id)
                        ->get('user')
                        ->row();

        if ($user) {
            return $user->nama;
        }

       
        return 'System';
    }

    /**
     * GET /api/v1/pelaporan
     * Mengambil list pelaporan dengan pagination dan filter
     */
    public function index()
    {
        try {
            // Get query parameters
            $page = (int)$this->input->get('page', true) ?: 1;
            $limit = (int)$this->input->get('limit', true) ?: 20;
            $status = $this->input->get('status', true);
            $kategori = $this->input->get('kategori', true);
            $search = $this->input->get('search', true);

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Build query - get all columns from pelaporan table
            $this->db->select('p.*, k.pelaporan_nama as nama_kategori')
                     ->from('pelaporan p')
                     ->join('kategori_pelaporan k', 'p.kategori = k.pelaporan_nama', 'left')
                     ->where('k.status', 1); // Only active categories

            if (!empty($status)) {
                $this->db->where('p.status', $status);
            }

            if (!empty($kategori)) {
                 $this->db->where('p.kategori', $kategori);
             }

            if (!empty($search)) {
                $this->db->group_start()
                         ->like('p.judul', $search)
                         ->or_like('p.deskripsi', $search)
                         ->or_like('p.alamat', $search)
                         ->or_like('m.nama', $search)
                         ->group_end();
            }

            // Get total count for pagination
            $total_records = $this->db->count_all_results('', false);

            // Apply pagination and ordering
            $this->db->limit($limit, $offset);
            $this->db->order_by('p.created_at', 'DESC');

            $pelaporan = $this->db->get()->result();

            // Format response data
            foreach ($pelaporan as &$item) {
                $item->foto_url = $item->foto ? base_url('uploads/pelaporan/progress/' . $item->foto) : null;
                $item->created_at_formatted = date('d/m/Y H:i', strtotime($item->created_at));
                $item->updated_at_formatted = $item->updated_at ? date('d/m/Y H:i', strtotime($item->updated_at)) : null;

                // Get files for this pelaporan
                $files = $this->db->where('pelaporan_id', $item->id)
                                 ->order_by('uploaded_at', 'ASC')
                                 ->get('pelaporan_files')
                                 ->result();

                // Format files with URLs
                foreach ($files as &$file) {
                    $file->file_url = base_url($file->file_path);
                }

                $item->files = $files;
                $item->files_count = count($files);

                // Remove sensitive data
                unset($item->password); // If any
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

            $this->send_response(200, 'Data pelaporan berhasil diambil', [
                'pelaporan' => $pelaporan,
                'pagination' => $pagination,
                'filters_applied' => [
                    'status' => $status,
                    'kategori' => $kategori,
                    'search' => $search
                ]
            ]);

        } catch (Exception $e) {
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

        try {
            // Get full pelaporan data with all columns
            $pelaporan = $this->db->select('p.*, k.pelaporan_nama as nama_kategori')
                                  ->from('pelaporan p')
                                  ->join('kategori_pelaporan k', 'p.kategori = k.pelaporan_nama', 'left')
                                  ->where('p.id', $id)
                                  ->get()
                                  ->row();

            if (!$pelaporan) {
                $this->send_response(404, 'Pelaporan tidak ditemukan');
            }

            // Format response
            $pelaporan->foto_url = $pelaporan->foto ? base_url('uploads/pelaporan/' . $pelaporan->foto) : null;
            $pelaporan->created_at_formatted = date('d/m/Y H:i', strtotime($pelaporan->created_at));
            $pelaporan->updated_at_formatted = $pelaporan->updated_at ? date('d/m/Y H:i', strtotime($pelaporan->updated_at)) : null;

            // Add created_by and updated_by names
            $pelaporan->created_by_name = $this->get_user_name($pelaporan->created_by);
            $pelaporan->updated_by_name = $this->get_user_name($pelaporan->updated_by);

            // Get related files from pelaporan_files table
            $files = $this->db->where('pelaporan_id', $pelaporan->id)
                             ->order_by('uploaded_at', 'ASC')
                             ->get('pelaporan_files')
                             ->result();

            // Format files with full URLs
            foreach ($files as &$file) {
                $file->file_url = base_url($file->file_path);
                $file->uploaded_at_formatted = date('d/m/Y H:i', strtotime($file->uploaded_at));

                // Get uploader info using helper function
                $file->uploaded_by_name = $this->get_user_name($file->uploaded_by);
            }

            // Get pelaporan history
            $history = $this->db->where('pelaporan_id', $pelaporan->id)
                               ->order_by('created_at', 'ASC')
                               ->get('pelaporan_history')
                               ->result();

            // Format history with user info and URLs
            foreach ($history as &$item) {
                $item->created_at_formatted = date('d/m/Y H:i', strtotime($item->created_at));
                $item->foto_progress_url = $item->foto_progress ? base_url($item->foto_progress) : null;

                // Get user who made the change using helper function
                $item->created_by_name = $this->get_user_name($item->created_by);
            }

            // Get pelaporan comments
            $comments = $this->db->where('pelaporan_id', $pelaporan->id)
                                ->order_by('created_at', 'ASC')
                                ->get('pelaporan_comments')
                                ->result();

            // Format comments with user info
            foreach ($comments as &$comment) {
                $comment->created_at_formatted = date('d/m/Y H:i', strtotime($comment->created_at));
                $comment->is_internal_formatted = $comment->is_internal ? 'Internal' : 'Public';

                // Get user who made the comment using helper function
                $comment->created_by_name = $this->get_user_name($comment->created_by) ?: 'Anonim';
            }

            $pelaporan->files = $files;
            $pelaporan->history = $history;
            $pelaporan->comments = $comments;

            $this->send_response(200, 'Detail pelaporan berhasil diambil', $pelaporan);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Detail Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }
    /**
     * GET /api/v1/pelaporan/pelaporan_history/{id}
     * Mengambil history pelaporan berdasarkan ID pelaporan
     */
    public function pelaporan_history($id_pelaporan = null)
    {
        log_message('debug', 'API pelaporan_history START - called with id: ' . $id_pelaporan);

        // Validasi parameter
        if (!$id_pelaporan) {
            log_message('debug', 'API pelaporan_history: No id provided');
            $this->send_response(400, 'ID pelaporan diperlukan');
        }

        try {
            // Periksa apakah pelaporan ada
            $pelaporan = $this->db->where('id', $id_pelaporan)->get('pelaporan')->row();
            log_message('debug', 'API pelaporan_history: Pelaporan query result: ' . json_encode($pelaporan));
            if (!$pelaporan) {
                log_message('debug', 'API pelaporan_history: Pelaporan not found for id: ' . $id_pelaporan);
                $this->send_response(404, 'Maaf, pelaporan tersebut tidak ditemukan atau belum ada');
            }

            // Ambil history menggunakan model
            log_message('debug', 'API pelaporan_history: Calling Pelaporan_m->get_history for id: ' . $id_pelaporan);
            $history = $this->Pelaporan_m->get_history($id_pelaporan);
            log_message('debug', 'API pelaporan_history: History result count: ' . count($history));

            if (empty($history)) {
                log_message('debug', 'API pelaporan_history: No history found for id: ' . $id_pelaporan);
                $this->send_response(404, 'History pelaporan tidak ditemukan');
            }

            // Urutkan descending berdasarkan updated_at (created_at)
            $history = array_reverse($history);
            log_message('debug', 'API pelaporan_history: After reverse, history count: ' . count($history));

            // Format response data
            foreach ($history as &$item) {
                $item->status_lama = $item->status_dari;
                $item->status_baru = $item->status_ke;
                $item->updated_by = $item->updated_by_name;
                $item->updated_at = $item->created_at;
                $item->updated_at_formatted = date('d/m/Y H:i', strtotime($item->created_at));

                // Hapus field yang tidak diperlukan
                unset($item->status_dari);
                unset($item->status_ke);
                unset($item->updated_by_name);
                unset($item->created_at);
                unset($item->created_by);
            }

            log_message('debug', 'API pelaporan_history: Sending success response for id: ' . $id_pelaporan);
            $this->send_response(200, 'History pelaporan berhasil diambil', [
                'pelaporan_id' => $id_pelaporan,
                'history' => $history,
                'total_history' => count($history)
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan History Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/pelaporan/kategori

    /**
     * POST /api/v1/pelaporan
     * Membuat pelaporan baru
     */
    public function create()
    {
        try {
            // Get POST data
            $data = [
                'judul' => $this->input->post('judul'),
                'deskripsi' => $this->input->post('deskripsi'),
                'alamat' => $this->input->post('alamat'),
                'lokasi_lat' => $this->input->post('latitude'),
                'lokasi_lng' => $this->input->post('longitude'),
                'kategori' => $this->input->post('kategori'),
                'masyarakat_id' => $this->input->post('masyarakat_id') ?? null,
                'status' => 'LAPOR',
                'prioritas' => $this->input->post('prioritas') ?: 'SEDANG',
                'pelapor_nama' => $this->input->post('pelapor_nama'),
                'pelapor_telepon' => $this->input->post('pelapor_telepon'),
                'pelapor_nik' => $this->input->post('pelapor_nik'),
                'pelapor_alamat' => $this->input->post('pelapor_alamat'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Validate required fields
            $required_fields = ['judul', 'deskripsi', 'alamat', 'kategori', 'pelapor_nama', 'pelapor_telepon', 'pelapor_nik', 'pelapor_alamat'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    $this->send_response(400, "Field {$field} wajib diisi");
                }
            }

            // Validate kategori exists and active
            $kategori = $this->db->where('pelaporan_nama', $data['kategori'])
                                ->where('status', 1)
                                ->get('kategori_pelaporan')
                                ->row();
            if (!$kategori) {
                $this->send_response(400, 'Kategori pelaporan tidak valid');
            }

            // Validate prioritas
            $valid_priorities = ['RENDAH', 'SEDANG', 'TINGGI', 'URGENT'];
            if (!in_array($data['prioritas'], $valid_priorities)) {
                $data['prioritas'] = 'SEDANG'; // Default fallback
            }

            // Handle file upload if provided - save to progress folder and pelaporan_files table
            $uploaded_files = [];
            if (!empty($_FILES['foto']['name'])) {
                $config = [
                    'upload_path' => './uploads/pelaporan/progress/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size' => 2048, // 2MB
                    'file_name' => 'pelaporan_' . time() . '_' . rand(1000, 9999)
                ];

                // Create directory if not exists
                if (!is_dir($config['upload_path'])) {
                    mkdir($config['upload_path'], 0755, true);
                }

                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if ($this->upload->do_upload('foto')) {
                    $upload_data = $this->upload->data();

                    // Save file info to pelaporan_files table (will be linked after pelaporan is created)
                    $uploaded_files[] = [
                        'file_name' => $upload_data['file_name'],
                        'file_path' => 'uploads/pelaporan/progress/' . $upload_data['file_name'],
                        'file_type' => $upload_data['file_type'],
                        'file_size' => $upload_data['file_size'],
                        'file_category' => 'progress'
                    ];
                } else {
                    $this->send_response(400, 'Gagal upload foto: ' . $this->upload->display_errors('', ''));
                }
            }

            // Generate kode_laporan
            $date_prefix = date('Ymd');
            $last_code = $this->db->select('kode_laporan')
                                 ->like('kode_laporan', 'LP' . $date_prefix, 'after')
                                 ->order_by('id', 'DESC')
                                 ->limit(1)
                                 ->get('pelaporan')
                                 ->row();

            if ($last_code) {
                $last_number = (int)substr($last_code->kode_laporan, -4);
                $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $new_number = '0001';
            }

            $data['kode_laporan'] = 'LP' . $date_prefix . $new_number;

            // Insert pelaporan
            $this->db->insert('pelaporan', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                // Save uploaded files to pelaporan_files table
                if (!empty($uploaded_files)) {
                    foreach ($uploaded_files as $file_info) {
                        $file_data = [
                            'pelaporan_id' => $insert_id,
                            'file_name' => $file_info['file_name'],
                            'file_path' => $file_info['file_path'],
                            'file_type' => $file_info['file_type'],
                            'file_size' => $file_info['file_size'],
                            'uploaded_by' => 1, // Default admin user
                            'uploaded_at' => date('Y-m-d H:i:s')
                        ];

                        $this->db->insert('pelaporan_files', $file_data);
                    }
                }

                // Insert initial history record for pelaporan creation
                $history_data = [
                    'pelaporan_id' => $insert_id,
                    'status_dari' => 'LAPOR', // No previous status for new pelaporan
                    'status_ke' => '', // Initial status
                    'created_by' => $data['masyarakat_id'] ?: 1, // Use masyarakat_id if provided, otherwise system admin
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->db->insert('pelaporan_history', $history_data);

                $this->send_test();
                
                $this->send_response(201, 'Pelaporan berhasil dibuat', [
                    'id' => $insert_id,
                    'kode_laporan' => $data['kode_laporan'],
                    'status' => 'LAPOR',
                    'kategori' => $kategori->pelaporan_nama,
                    'files_uploaded' => count($uploaded_files),
                    'created_at' => date('Y-m-d H:i:s')
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
     * GET /api/v1/pelaporan/kategori
     * Mengambil list kategori pelaporan
     */
    public function kategori()
    {
        try {
            $kategori = $this->db->where('status', 1)
                                 ->order_by('pelaporan_nama', 'ASC')
                                 ->get('kategori_pelaporan')
                                 ->result();

            // Format response
            foreach ($kategori as &$item) {
                $item->created_at_formatted = date('d/m/Y H:i', strtotime($item->created_at));
                $item->updated_at_formatted = $item->updated_at ? date('d/m/Y H:i', strtotime($item->updated_at)) : null;
            }

            $this->send_response(200, 'Data kategori berhasil diambil', [
                'kategori' => $kategori,
                'total' => count($kategori)
            ]);

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
        try {
            // Total pelaporan
            $total = $this->db->count_all('pelaporan');

            // By status
            $status_stats = $this->db->select('status, COUNT(*) as jumlah')
                                    ->group_by('status')
                                    ->get('pelaporan')
                                    ->result();

            // By kategori (active categories only)
            $kategori_stats = $this->db->select('k.pelaporan_nama as nama_kategori, COUNT(p.id_pelaporan) as jumlah')
                                      ->from('pelaporan p')
                                      ->join('kategori_pelaporan k', 'p.id_kategori = k.id_kategori', 'left')
                                      ->where('k.status', 1)
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

            // Format monthly trend
            foreach ($monthly_trend as &$item) {
                $item->bulan_formatted = date('M Y', strtotime($item->bulan . '-01'));
            }

            $this->send_response(200, 'Statistik pelaporan berhasil diambil', [
                'total_pelaporan' => $total,
                'by_status' => $status_stats,
                'by_kategori' => $kategori_stats,
                'monthly_trend' => $monthly_trend,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Statistics Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * POST /api/v1/pelaporan/{id}/create_comment
     * Membuat komentar baru untuk pelaporan
     */
    public function create_comment($id = null)
    {
        if (!$id) {
            $this->send_response(400, 'ID pelaporan diperlukan');
        }

        try {
            // Verify pelaporan exists
            $pelaporan = $this->db->where('id', $id)->get('pelaporan')->row();
            if (!$pelaporan) {
                $this->send_response(404, 'Pelaporan tidak ditemukan');
            }

            // Get POST data
            $data = [
                'pelaporan_id' => $id,
                'comment' => $this->input->post('comment'),
                'rating' => $this->input->post('rating') ?: null,
                'is_internal' => $this->input->post('is_internal') ?: 0,
                'created_by' => $this->input->post('created_by') ?: null, // Could be masyarakat_id or user_id
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Validate required fields
            if (empty($data['comment'])) {
                $this->send_response(400, 'Komentar wajib diisi');
            }

            // Validate rating if provided (1-5)
            if ($data['rating'] !== null) {
                $rating = (int)$data['rating'];
                if ($rating < 1 || $rating > 5) {
                    $this->send_response(400, 'Rating harus antara 1 sampai 5');
                }
                $data['rating'] = $rating;
            }

            // Validate is_internal (should be 0 or 1)
            $data['is_internal'] = (int)$data['is_internal'];
            if ($data['is_internal'] !== 0 && $data['is_internal'] !== 1) {
                $data['is_internal'] = 0; // Default to public
            }

            // Insert comment
            $this->db->insert('pelaporan_comments', $data);

            if ($this->db->insert_id()) {
                // Get the inserted comment with user info
                $comment_id = $this->db->insert_id();
                $comment = $this->db->select('pc.*, u.nama as user_name, m.nama_lengkap as masyarakat_name')
                                   ->from('pelaporan_comments pc')
                                   ->join('user u', 'pc.created_by = u.id_user', 'left')
                                   ->join('masyarakat m', 'pc.created_by = m.id', 'left')
                                   ->where('pc.id', $comment_id)
                                   ->get()
                                   ->row();

                // Format response
                $response_data = [
                    'id' => $comment->id,
                    'pelaporan_id' => $comment->pelaporan_id,
                    'comment' => $comment->comment,
                    'rating' => $comment->rating,
                    'is_internal' => (bool)$comment->is_internal,
                    'is_internal_formatted' => $comment->is_internal ? 'Internal' : 'Public',
                    'created_by' => $comment->created_by,
                    'created_by_name' => $comment->user_name ?: $comment->masyarakat_name ?: 'Anonim',
                    'created_at' => $comment->created_at,
                    'created_at_formatted' => date('d/m/Y H:i', strtotime($comment->created_at))
                ];

                $this->send_response(201, 'Komentar berhasil dibuat', $response_data);
            } else {
                $this->send_response(500, 'Gagal membuat komentar');
            }

        } catch (Exception $e) {
            log_message('error', 'API Pelaporan Create Comment Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/pelaporan/test
     * Endpoint test untuk pelaporan
     */
    public function test()
    {
        // Skip domain validation for test endpoint
        $this->skip_domain_validation = true;

        // Test endpoint
        $data = [
            'status' => 'API Pelaporan Test OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip_address' => $this->input->ip_address(),
            'api_key_provided' => !empty($this->input->get_request_header('X-API-Key')),
            'domain' => $this->get_referer_domain(),
            'endpoints_available' => [
                'GET /api/v1/pelaporan - List pelaporan',
                'GET /api/v1/pelaporan/{id} - Detail pelaporan',
                'POST /api/v1/pelaporan - Buat pelaporan baru',
                'POST /api/v1/pelaporan/{id}/create_comment - Buat komentar',
                'GET /api/v1/pelaporan/kategori - List kategori',
                'GET /api/v1/pelaporan/statistics - Statistik'
            ]
        ];

        $this->send_response(200, 'API Pelaporan test berhasil', $data);
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
                'screen' => 'Notification',
                'timestamp' => date('c'),
                'count' => '4',
                'title' => '🚨 Pengaduan Baru',
                'body' => '27',
            ],
            'android' => [
                'priority' => 'high'
            ]
        ];
        $results = send_fcm_data_only($tokens, $messageDataOnly);
        
        return ($results);
    }
}