<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * API Controller untuk Modul User
 * Endpoint: /api/v1/user
 */
class Api_user extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        // $this->load->model('User_m');
    }

    public function test()
    {
        $this->send_response(200, 'API User test berhasil', [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $this->input->method(),
            'ip' => $this->input->ip_address(),
            'api_key' => $this->input->get_request_header('X-API-Key')
        ]);
    }

    protected function send_response($status_code = 200, $message = '', $data = null)
    {
        http_response_code($status_code);
        echo json_encode([
            'status' => $status_code >= 200 && $status_code < 300 ? 'success' : 'error',
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    /**
     * GET /api/v1/user
     * Mengambil list user dengan pagination dan filter
     */
    public function index()
    {
        // Validate JWT token for sensitive data
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            // Get query parameters
            $page = (int)$this->input->get('page', true) ?: 1;
            $limit = (int)$this->input->get('limit', true) ?: 20;
            $search = $this->input->get('search', true);
            $status = $this->input->get('status', true);

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Build query
            $this->db->select('id_user, nama, username, email, level, status, last_login, created_at')
                     ->from('user');

            if (!empty($search)) {
                $this->db->group_start()
                         ->like('nama', $search)
                         ->or_like('username', $search)
                         ->or_like('email', $search)
                         ->group_end();
            }

            if (!empty($status)) {
                $this->db->where('status', $status);
            }

            // Get total count for pagination
            $total_records = $this->db->count_all_results('', false);

            // Apply pagination
            $this->db->limit($limit, $offset);
            $this->db->order_by('created_at', 'DESC');

            $users = $this->db->get()->result();

            // Remove sensitive data
            foreach ($users as &$user) {
                unset($user->password); // Just in case
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

            $this->send_response(200, 'Data user berhasil diambil', [
                'users' => $users,
                'pagination' => $pagination
            ]);

        } catch (Exception $e) {
            log_message('error', 'API User Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/user/{id}
     * Mengambil detail user berdasarkan ID
     */
    public function show($id = null)
    {
        if (!$id) {
            $this->send_response(400, 'ID user diperlukan');
        }

        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            $user = $this->db->select('id_user, nama, username, email, level, status, last_login, created_at, updated_at')
                             ->where('id_user', $id)
                             ->get('user')
                             ->row();

            if (!$user) {
                $this->send_response(404, 'User tidak ditemukan');
            }

            // Remove sensitive data
            unset($user->password);

            $this->send_response(200, 'Detail user berhasil diambil', $user);

        } catch (Exception $e) {
            log_message('error', 'API User Detail Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * GET /api/v1/user/profile
     * Mengambil profile user yang sedang login (berdasarkan JWT)
     */
    public function profile()
    {
        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            // In real implementation, get user_id from JWT payload
            $user_id = $jwt_payload->user_id ?? null;

            if (!$user_id) {
                $this->send_response(401, 'Invalid token payload');
            }

            $user = $this->db->select('id_user, nama, username, email, level, status, last_login, created_at, updated_at')
                             ->where('id_user', $user_id)
                             ->get('user')
                             ->row();

            if (!$user) {
                $this->send_response(404, 'User tidak ditemukan');
            }

            // Remove sensitive data
            unset($user->password);

            $this->send_response(200, 'Profile user berhasil diambil', $user);

        } catch (Exception $e) {
            log_message('error', 'API User Profile Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * POST /api/v1/user/auth
     * Autentikasi user dan generate JWT token
     */
    public function auth()
    {
        $username = $this->input->post('username');
        $password = $this->input->post('password');

        if (empty($username) || empty($password)) {
            $this->send_response(400, 'Username dan password diperlukan');
        }

        try {
            // Check user credentials
            $user = $this->db->where('username', $username)
                             ->where('status', 'aktif')
                             ->get('user')
                             ->row();

            if (!$user || !password_verify($password, $user->password)) {
                $this->send_response(401, 'Username atau password salah');
            }

            // Update last login
            $this->db->where('id_user', $user->id_user)
                     ->update('user', ['last_login' => date('Y-m-d H:i:s')]);

            // Create JWT payload
            $payload = [
                'user_id' => $user->id_user,
                'username' => $user->username,
                'nama' => $user->nama,
                'level' => $user->level,
                'iat' => time(),
                'exp' => time() + (24 * 60 * 60) // 24 hours
            ];

            // Generate JWT token
            $token = $this->generate_jwt($payload);

            if (!$token) {
                $this->send_response(500, 'Gagal membuat token autentikasi');
            }

            $this->send_response(200, 'Autentikasi berhasil', [
                'token' => $token,
                'user' => [
                    'id_user' => $user->id_user,
                    'nama' => $user->nama,
                    'username' => $user->username,
                    'email' => $user->email,
                    'level' => $user->level
                ],
                'expires_in' => 86400 // 24 hours in seconds
            ]);

        } catch (Exception $e) {
            log_message('error', 'API User Auth Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }

    /**
     * PUT /api/v1/user/profile
     * Update profile user
     */
    public function update_profile()
    {
        // Validate JWT token
        $jwt_payload = $this->validate_jwt();
        if (!$jwt_payload) {
            $this->send_response(401, 'Authentication required');
        }

        try {
            $user_id = $jwt_payload->user_id ?? null;

            if (!$user_id) {
                $this->send_response(401, 'Invalid token payload');
            }

            // Get input data
            $update_data = [];
            $allowed_fields = ['nama', 'email'];

            foreach ($allowed_fields as $field) {
                $value = $this->input->input_stream($field);
                if ($value !== null) {
                    $update_data[$field] = $value;
                }
            }

            if (empty($update_data)) {
                $this->send_response(400, 'Tidak ada data yang akan diupdate');
            }

            // Add updated_at
            $update_data['updated_at'] = date('Y-m-d H:i:s');

            // Update user
            $this->db->where('id_user', $user_id)->update('user', $update_data);

            if ($this->db->affected_rows() > 0) {
                $this->send_response(200, 'Profile berhasil diupdate');
            } else {
                $this->send_response(400, 'Gagal mengupdate profile');
            }

        } catch (Exception $e) {
            log_message('error', 'API User Update Profile Error: ' . $e->getMessage());
            $this->send_response(500, 'Terjadi kesalahan internal server');
        }
    }
}