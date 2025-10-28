<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api.php';

/**
 * API Controller untuk Modul Masyarakat
 * Endpoint: /api/v1/masyarakat
 *
 * Features:
 * - Registrasi masyarakat baru
 * - Login dengan OTP via WhatsApp
 * - Verifikasi OTP
 * - Domain whitelist & rate limiting
 */
class Api_masyarakat extends api
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
        $this->load->helper('string');
        $this->load->helper('wa_helper');
    }

    /**
     * POST /api/v1/masyarakat/register
     * Registrasi masyarakat baru
     */
    public function register()
    {
        try {
            // Get POST data
            $data = [
                'nama_lengkap' => $this->input->post('nama_lengkap'),
                'nik' => $this->input->post('nik'),
                'no_telpon' => $this->input->post('no_telpon'),
                'id_kecamatan' => $this->input->post('id_kecamatan'),
                'id_kelurahan' => $this->input->post('id_kelurahan'),
                'alamat' => $this->input->post('alamat'),
                'status_aktif' => 0, // Akun baru belum aktif, perlu verifikasi admin
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Validate required fields
            $required_fields = ['nama_lengkap', 'nik', 'no_telpon'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    $this->send_response(400, "Field {$field} wajib diisi");
                }
            }

            // Validate NIK format (16 digits)
            if (!preg_match('/^[0-9]{16}$/', $data['nik'])) {
                $this->send_response(400, 'NIK harus 16 digit angka');
            }

            // Validate phone format (start with 08, 10-13 digits)
            if (!preg_match('/^08[0-9]{8,11}$/', $data['no_telpon'])) {
                $this->send_response(400, 'Nomor telepon harus dimulai dengan 08 dan 10-13 digit');
            }

            // Check if NIK already exists
            $existing_nik = $this->db->where('nik', $data['nik'])->get('masyarakat')->row();
            if ($existing_nik) {
                $this->send_response(409, 'NIK sudah terdaftar');
            }

            // Check if phone number already exists
            $existing_phone = $this->db->where('no_telpon', $data['no_telpon'])->get('masyarakat')->row();
            if ($existing_phone) {
                $this->send_response(409, 'Nomor telepon sudah terdaftar');
            }

            // Handle photo uploads
            $uploaded_files = [];

            // Upload foto_profil
            if (!empty($_FILES['foto_profil']['name'])) {
                $profil_upload = $this->upload_file('foto_profil', 'profil', $data['nik']);
                if ($profil_upload['status']) {
                    $data['foto_profil'] = $profil_upload['file_path'];
                    $uploaded_files[] = $profil_upload['file_path'];
                } else {
                    $this->send_response(400, 'Gagal upload foto profil: ' . $profil_upload['error']);
                }
            }

            // Upload foto_ktp
            if (!empty($_FILES['foto_ktp']['name'])) {
                $ktp_upload = $this->upload_file('foto_ktp', 'ktp', $data['nik']);
                if ($ktp_upload['status']) {
                    $data['foto_ktp'] = $ktp_upload['file_path'];
                    $uploaded_files[] = $ktp_upload['file_path'];
                } else {
                    // Clean up previously uploaded files if KTP upload fails
                    foreach ($uploaded_files as $file) {
                        if (file_exists('./' . $file)) {
                            unlink('./' . $file);
                        }
                    }
                    $this->send_response(400, 'Gagal upload foto KTP: ' . $ktp_upload['error']);
                }
            }

            // Insert masyarakat data
            $this->db->insert('masyarakat', $data);
            $insert_id = $this->db->insert_id();

            if ($insert_id) {
                // Send WhatsApp notification about successful registration but pending verification
                kirim_template($data['no_telpon'], 'masyarakat_registrasi', [
                    'nama' => $data['nama_lengkap']
                ], null, [
                    'nama_tujuan' => $data['nama_lengkap'],
                    'created_by' => 1 // System admin
                ]);

                $this->send_response(201, 'Registrasi berhasil, akun Anda sedang dalam proses verifikasi. Kami akan mengirim notifikasi melalui WhatsApp setelah diverifikasi.', [
                    'id' => $insert_id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'nik' => $data['nik'],
                    'no_telpon' => $data['no_telpon'],
                    'status_aktif' => 0,
                    'status_message' => 'Menunggu verifikasi admin'
                ]);
            } else {
                // Clean up uploaded files if database insert fails
                foreach ($uploaded_files as $file) {
                    if (file_exists('./' . $file)) {
                        unlink('./' . $file);
                    }
                }
                $this->send_response(500, 'Gagal menyimpan data registrasi');
            }

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Register Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * POST /api/v1/masyarakat/login
     * Login masyarakat dengan NIK dan kirim OTP
     */
    public function login()
    {
        try {
            // Try to get data from JSON body first, then fallback to form data
            $json_input = json_decode($this->input->raw_input_stream, true);
            if ($json_input && isset($json_input['nik'])) {
                $nik = $json_input['nik'];
            } else {
                $nik = $this->input->post('nik');
            }

            // Validate NIK format first
            if (empty($nik)) {
                $this->send_response(400, 'NIK wajib diisi');
            }

            if (!preg_match('/^[0-9]{16}$/', $nik)) {
                $this->send_response(400, 'NIK harus 16 digit angka');
            }

            // Find masyarakat by NIK (without status_aktif filter first)
            $masyarakat = $this->db->where('nik', $nik)
                                   ->get('masyarakat')
                                   ->row();

            if (!$masyarakat) {
                $this->send_response(404, 'NIK tidak ditemukan dalam sistem kami');
            }

            // Check if account is active
            if ($masyarakat->status_aktif != 1) {
                $this->send_response(403, 'Mohon maaf, akun Anda sedang dalam proses verifikasi oleh admin. Kami akan mengirim notifikasi melalui WhatsApp setelah akun Anda diverifikasi dan diaktifkan.');
            }

            // Generate new OTP
            $otp = random_string('numeric', 6);

            // Update OTP in database
            $this->db->where('id', $masyarakat->id)
                    ->update('masyarakat', [
                        'otp' => $otp,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

            // Send OTP via WhatsApp using template
            kirim_template($masyarakat->no_telpon, 'masyarakat_otp', [
                'nama' => $masyarakat->nama_lengkap,
                'otp' => $otp
            ], null, [
                'nama_tujuan' => $masyarakat->nama_lengkap,
                'created_by' => 1 // System admin
            ]);

            $this->send_response(200, 'OTP telah dikirim ke WhatsApp', [
                'nik' => $nik,
                'nama_lengkap' => $masyarakat->nama_lengkap,
                'otp_sent' => true,
                'otp_expires_in' => '5 menit'
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Login Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * POST /api/v1/masyarakat/verify-otp
     * Verifikasi OTP untuk login/registrasi
     */
    public function verify_otp()
    {
        try {
            $nik = $this->input->post('nik');
            $otp = $this->input->post('otp');

            // Validate inputs
            if (empty($nik) || empty($otp)) {
                $this->send_response(400, 'NIK dan OTP wajib diisi');
            }

            // Find masyarakat by NIK
            $masyarakat = $this->db->where('nik', $nik)
                                  ->where('status_aktif', 1)
                                  ->get('masyarakat')
                                  ->row();

            if (!$masyarakat) {
                $this->send_response(404, 'NIK tidak ditemukan atau akun tidak aktif');
            }

            // Check OTP
            if ($masyarakat->otp !== $otp) {
                $this->send_response(401, 'OTP tidak valid');
            }

            // Generate token for session (simple approach - can be enhanced with JWT)
            $token = md5($masyarakat->id . $nik . time());

            // Clear OTP and update token after successful verification
            $this->db->where('id', $masyarakat->id)
                    ->update('masyarakat', [
                        'otp' => null,
                        'token' => $token,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

            $this->send_response(200, 'Login berhasil', [
                'id' => $masyarakat->id,
                'nama_lengkap' => $masyarakat->nama_lengkap,
                'nik' => $masyarakat->nik,
                'no_telpon' => $masyarakat->no_telpon,
                'token' => $token,
                'foto_profil_url' => $masyarakat->foto_profil ? base_url($masyarakat->foto_profil) : null
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Verify OTP Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET/PUT /api/v1/masyarakat/profile
     * GET: Ambil data profile masyarakat
     * PUT: Update profile masyarakat (nama, foto_profil, no_telpon, tempat_lahir, tanggal_lahir)
     * NIK dan foto_ktp tidak dapat diupdate
     */
    public function profile()
    {
        try {
            // Validate token
            $token = $this->input->get_request_header('Authorization');
            if (!$token) {
                $token = $this->input->post('token');
            }

            if (empty($token)) {
                $this->send_response(401, 'Token autentikasi wajib diisi');
            }

            // Find masyarakat by token with kecamatan and kelurahan names
            $masyarakat = $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan')
                                   ->from('masyarakat m')
                                   ->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left')
                                   ->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left')
                                   ->where('m.token', $token)
                                   ->where('m.status_aktif', 1)
                                   ->get()
                                   ->row();

            if (!$masyarakat) {
                $this->send_response(401, 'Token tidak valid atau akun tidak aktif');
            }

            $method = $this->input->method();

            if ($method === 'get') {
                // GET: Return profile data
                $this->send_response(200, 'Profile berhasil diambil', [
                    'id' => $masyarakat->id,
                    'nama_lengkap' => $masyarakat->nama_lengkap,
                    'nik' => $masyarakat->nik,
                    'no_telpon' => $masyarakat->no_telpon,
                    'tempat_lahir' => $masyarakat->tempat_lahir ?? null,
                    'tanggal_lahir' => $masyarakat->tanggal_lahir ?? null,
                    'alamat' => $masyarakat->alamat ?? null,
                    'kecamatan' => [
                        'id_kecamatan' => $masyarakat->id_kecamatan ?? null,
                        'nama_kecamatan' => $masyarakat->nama_kecamatan ?? null
                    ],
                    'kelurahan' => [
                        'id_kelurahan' => $masyarakat->id_kelurahan ?? null,
                        'nama_kelurahan' => $masyarakat->nama_kelurahan ?? null
                    ],
                    'foto_profil_url' => $masyarakat->foto_profil ? base_url($masyarakat->foto_profil) : null,
                    'foto_ktp_url' => $masyarakat->foto_ktp ? base_url($masyarakat->foto_ktp) : null,
                    'status_aktif' => $masyarakat->status_aktif,
                    'created_at' => $masyarakat->created_at,
                    'updated_at' => $masyarakat->updated_at
                ]);
            } elseif ($method === 'put') {
                // PUT: Update profile data
                $update_data = [];

                // Try to get data from JSON body first, then fallback to form data
                $json_input = json_decode($this->input->raw_input_stream, true);

                if ($json_input && is_array($json_input)) {
                    // JSON input
                    $input_data = $json_input;
                } else {
                    // Form data fallback
                    $input_data = $this->input->post();
                }

                $allowed_fields = ['nama_lengkap', 'no_telpon', 'tempat_lahir', 'tanggal_lahir', 'id_kecamatan', 'id_kelurahan','alamat'];

                foreach ($allowed_fields as $field) {
                    $value = $input_data[$field] ?? null;
                    if ($value !== null && $value !== '') {
                        $update_data[$field] = $value;
                    }
                }

                // Validate phone format if being updated
                if (isset($update_data['no_telpon'])) {
                    if (!preg_match('/^08[0-9]{8,11}$/', $update_data['no_telpon'])) {
                        $this->send_response(400, 'Nomor telepon harus dimulai dengan 08 dan 10-13 digit');
                    }

                    // Check if new phone number is already used by another user
                    $existing_phone = $this->db->where('no_telpon', $update_data['no_telpon'])
                                               ->where('id !=', $masyarakat->id)
                                               ->get('masyarakat')
                                               ->row();
                    if ($existing_phone) {
                        $this->send_response(409, 'Nomor telepon sudah digunakan oleh akun lain');
                    }
                }

                // Validate kecamatan if being updated
                if (isset($update_data['id_kecamatan'])) {
                    $kecamatan_check = $this->db->where('id_kecamatan', $update_data['id_kecamatan'])
                                               ->where('id_kota', '16.73')
                                               ->get('kecamatan')
                                               ->row();
                    if (!$kecamatan_check) {
                        $this->send_response(400, 'Kecamatan tidak valid atau bukan bagian dari Kota Lubuk Linggau');
                    }
                }

                // Validate kelurahan if being updated
                if (isset($update_data['id_kelurahan'])) {
                    // If kecamatan is also being updated, use the new one, otherwise use existing
                    $kecamatan_id = $update_data['id_kecamatan'] ?? $masyarakat->id_kecamatan;

                    if ($kecamatan_id) {
                        $kelurahan_check = $this->db->where('id_kelurahan', $update_data['id_kelurahan'])
                                                   ->where('id_kecamatan', $kecamatan_id)
                                                   ->get('kelurahan')
                                                   ->row();
                        if (!$kelurahan_check) {
                            $this->send_response(400, 'Kelurahan tidak valid atau bukan bagian dari kecamatan yang dipilih');
                        }
                    } else {
                        $this->send_response(400, 'Kecamatan harus dipilih terlebih dahulu sebelum memilih kelurahan');
                    }
                }

                // Handle foto_profil upload if provided
                if (!empty($_FILES['foto_profil']['name'])) {
                    $profil_upload = $this->upload_file('foto_profil', 'profil', $masyarakat->nik);
                    if ($profil_upload['status']) {
                        $update_data['foto_profil'] = $profil_upload['file_path'];

                        // Delete old foto_profil if exists
                        if (!empty($masyarakat->foto_profil) && file_exists('./' . $masyarakat->foto_profil)) {
                            unlink('./' . $masyarakat->foto_profil);
                        }
                    } else {
                        $this->send_response(400, 'Gagal upload foto profil: ' . $profil_upload['error']);
                    }
                }

                if (empty($update_data)) {
                    $this->send_response(400, 'Tidak ada data yang diupdate');
                }

                // Add updated_at timestamp
                $update_data['updated_at'] = date('Y-m-d H:i:s');

                // Update database
                $this->db->where('id', $masyarakat->id)->update('masyarakat', $update_data);

                if ($this->db->affected_rows() > 0) {
                    // Get updated data
                    $updated_masyarakat = $this->db->where('id', $masyarakat->id)->get('masyarakat')->row();

                    $this->send_response(200, 'Profile berhasil diupdate', [
                        'id' => $updated_masyarakat->id,
                        'nama_lengkap' => $updated_masyarakat->nama_lengkap,
                        'nik' => $updated_masyarakat->nik,
                        'no_telpon' => $updated_masyarakat->no_telpon,
                        'tempat_lahir' => $updated_masyarakat->tempat_lahir ?? null,
                        'tanggal_lahir' => $updated_masyarakat->tanggal_lahir ?? null,
                        'foto_profil_url' => $updated_masyarakat->foto_profil ? base_url($updated_masyarakat->foto_profil) : null,
                        'updated_at' => $updated_masyarakat->updated_at
                    ]);
                } else {
                    $this->send_response(500, 'Gagal update profile');
                }
            } else {
                $this->send_response(405, 'Method not allowed');
            }

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Profile Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }


    /**
     * Helper function to upload files
     */
    private function upload_file($field_name, $type, $nik)
    {
        $upload_path = './uploads/masyarakat' . $nik . '/';

        // Create directory if not exists
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048, // 2MB
            'file_name' => 'masyarakat_' . $type . '_' . time() . '_' . rand(1000, 9999)
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return [
                'status' => true,
                'file_path' => 'uploads/masyarakat' . $nik . '/' . $upload_data['file_name'],
                'file_name' => $upload_data['file_name']
            ];
        } else {
            return [
                'status' => false,
                'error' => $this->upload->display_errors('', '')
            ];
        }
    }


    /**
     * GET /api/v1/masyarakat/kecamatan
     * Ambil list kecamatan di Kota Lubuk Linggau
     */
    public function get_kecamatan()
    {
        try {
            // Ambil kecamatan untuk Kota Lubuk Linggau (id_kota = '16.73')
            $kecamatan = $this->db->select('id_kecamatan, nama_kecamatan')
                                  ->from('kecamatan')
                                  ->where('id_kota', '16.73')
                                  ->order_by('nama_kecamatan', 'ASC')
                                  ->get()
                                  ->result();

            $this->send_response(200, 'Data kecamatan berhasil diambil', [
                'kecamatan' => $kecamatan,
                'total' => count($kecamatan)
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Get Kecamatan Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/masyarakat/kelurahan/{id_kecamatan}
     * Ambil list kelurahan berdasarkan id_kecamatan
     */
    public function get_kelurahan($id_kecamatan = null)
    {
        try {
            if (!$id_kecamatan) {
                $this->send_response(400, 'ID kecamatan wajib diisi');
            }

            // Validasi kecamatan exists dan termasuk dalam Kota Lubuk Linggau
            $kecamatan_check = $this->db->where('id_kecamatan', $id_kecamatan)
                                       ->where('id_kota', '16.73')
                                       ->get('kecamatan')
                                       ->row();

            if (!$kecamatan_check) {
                $this->send_response(404, 'Kecamatan tidak ditemukan atau bukan bagian dari Kota Lubuk Linggau');
            }

            // Ambil kelurahan berdasarkan id_kecamatan
            $kelurahan = $this->db->select('id_kelurahan, nama_kelurahan')
                                  ->from('kelurahan')
                                  ->where('id_kecamatan', $id_kecamatan)
                                  ->order_by('nama_kelurahan', 'ASC')
                                  ->get()
                                  ->result();

            $this->send_response(200, 'Data kelurahan berhasil diambil', [
                'kecamatan' => [
                    'id_kecamatan' => $kecamatan_check->id_kecamatan,
                    'nama_kecamatan' => $kecamatan_check->nama_kecamatan
                ],
                'kelurahan' => $kelurahan,
                'total' => count($kelurahan)
            ]);

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Get Kelurahan Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/masyarakat/{nik}
     * Ambil data masyarakat berdasarkan NIK
     */
    public function get_by_nik($nik = null)
    {
        try {
            log_message('debug', 'get_by_nik called, URL nik: ' . $nik);

            // Try to get NIK from query parameter if not in URL
            if (!$nik) {
                $nik = $this->input->get('nik');
                log_message('debug', 'NIK from query param: ' . $nik);
            }

            // Validate NIK parameter
            if (!$nik) {
                log_message('error', 'get_by_nik: NIK not provided');
                $this->send_response(400, 'NIK wajib diisi');
            }

            // Validate NIK format (16 digits)
            if (!preg_match('/^[0-9]{16}$/', $nik)) {
                log_message('error', 'get_by_nik: Invalid NIK format: ' . $nik);
                $this->send_response(400, 'NIK harus 16 digit angka');
            }

            log_message('debug', 'get_by_nik: Valid NIK: ' . $nik);

            // Find masyarakat by NIK with kecamatan and kelurahan names
            $masyarakat = $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan')
                                   ->from('masyarakat m')
                                   ->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left')
                                   ->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left')
                                   ->where('m.nik', $nik)
                                   ->get()
                                   ->row();

            log_message('debug', 'get_by_nik: Query executed, result: ' . ($masyarakat ? 'found' : 'not found'));

            if (!$masyarakat) {
                log_message('error', 'get_by_nik: Masyarakat not found for NIK: ' . $nik);
                $this->send_response(200, 'NIK tidak terdaftar dalam sistem', [
                    'nik' => $nik,
                    'found' => false,
                    'status_message' => 'NIK belum terdaftar'
                ]);
            }

            // Format response data
            $response_data = [
                'id' => $masyarakat->id,
                'nama_lengkap' => $masyarakat->nama_lengkap,
                'nik' => $masyarakat->nik,
                'no_telpon' => $masyarakat->no_telpon,
                'tempat_lahir' => $masyarakat->tempat_lahir ?? null,
                'tanggal_lahir' => $masyarakat->tanggal_lahir ?? null,
                'alamat' => $masyarakat->alamat ?? null,
                'kecamatan' => [
                    'id_kecamatan' => $masyarakat->id_kecamatan ?? null,
                    'nama_kecamatan' => $masyarakat->nama_kecamatan ?? null
                ],
                'kelurahan' => [
                    'id_kelurahan' => $masyarakat->id_kelurahan ?? null,
                    'nama_kelurahan' => $masyarakat->nama_kelurahan ?? null
                ],
                'foto_profil_url' => $masyarakat->foto_profil ? base_url($masyarakat->foto_profil) : null,
                'foto_ktp_url' => $masyarakat->foto_ktp ? base_url($masyarakat->foto_ktp) : null,
                'status_aktif' => $masyarakat->status_aktif,
                'created_at' => $masyarakat->created_at,
                'updated_at' => $masyarakat->updated_at
            ];

            $this->send_response(200, 'Data masyarakat berhasil diambil', $response_data);

        } catch (Exception $e) {
            log_message('error', 'API Masyarakat Get By NIK Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/masyarakat/test
     * Endpoint test untuk masyarakat
     */
    public function test()
    {
        // Skip domain validation for test endpoint
        $this->skip_domain_validation = true;

        // Test endpoint
        $data = [
            'status' => 'API Masyarakat Test OK',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip_address' => $this->input->ip_address(),
            'api_key_provided' => !empty($this->input->get_request_header('X-API-Key')),
            'domain' => $this->get_referer_domain(),
            'endpoints_available' => [
                'POST /api/v1/masyarakat/register - Registrasi masyarakat',
                'POST /api/v1/masyarakat/login - Login dengan NIK',
                'POST /api/v1/masyarakat/verify-otp - Verifikasi OTP',
                'GET /api/v1/masyarakat/profile - Ambil data profile',
                'PUT /api/v1/masyarakat/profile - Update profile masyarakat (TESTED ✅)',
                'GET /api/v1/masyarakat/{nik} - Ambil data masyarakat berdasarkan NIK',
                'GET /api/v1/masyarakat/kecamatan - Ambil list kecamatan',
                'GET /api/v1/masyarakat/kelurahan/{id_kecamatan} - Ambil list kelurahan'
            ]
        ];

        $this->send_response(200, 'API Masyarakat test berhasil', $data);
    }
}