<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_preview extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['secure_file', 'file']);
    }

    public function index() {
        echo "File Preview Controller Working";
    }

    /**
     * Serve file dengan validasi secure URL
     */
    public function serve() {
        $file_id = $this->input->get('id');
        $expires = $this->input->get('expires');
        $signature = $this->input->get('signature');

        // Validasi parameter
        if (!$file_id || !$expires || !$signature) {
            show_404();
            return;
        }

        // Validasi secure URL
        if (!validate_secure_url($file_id, $expires, $signature)) {
            show_404();
            return;
        }

        // Decode file path
        $file_path = base64_decode($file_id);
        $full_path = FCPATH . $file_path;

        // Cek apakah file ada
        if (!file_exists($full_path)) {
            show_404();
            return;
        }

        // Set headers untuk file serving
        $mime_type = get_mime_by_extension($full_path);
        $file_size = filesize($full_path);
        $file_name = basename($full_path);

        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="' . $file_name . '"');
        header('Cache-Control: private, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

        // Output file
        readfile($full_path);
    }

    /**
     * Preview contoh dokumen
     */
    public function contoh($dokumen_id) {
        // Cek apakah user sudah login
        if (!$this->session->userdata('mahasiswa_logged_in')) {
            show_404();
            return;
        }

        // Ambil data dokumen
        $dokumen = $this->db->where('dokumen_id', $dokumen_id)
                           ->where('status', 1)
                           ->get('master_dokumen')
                           ->row();

        if (!$dokumen || empty($dokumen->contoh_dokumen)) {
            show_404();
            return;
        }

        // Path file contoh
        $file_path = 'uploads/contoh_dokumen/' . $dokumen->contoh_dokumen;
        $full_path = FCPATH . $file_path;

        // Cek apakah file ada
        if (!file_exists($full_path)) {
            show_404();
            return;
        }

        // Set headers untuk file serving
        $mime_type = get_mime_by_extension($full_path);
        $file_size = filesize($full_path);
        $file_name = basename($full_path);

        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="contoh_' . $file_name . '"');
        header('Cache-Control: private, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

        // Output file
        readfile($full_path);
    }

    /**
     * Preview file upload mahasiswa
     */
    public function upload($upload_id = null) {
        // Debug
        echo "Method upload called with ID: " . $upload_id . "<br>";

        // Validasi upload_id
        if (empty($upload_id)) {
            echo "Upload ID kosong<br>";
            show_404();
            return;
        }

        // Ambil data upload tanpa session check untuk preview
        $upload = $this->db->select('udm.*, rm.nim, rm.nama_lengkap')
                          ->from('upload_dokumen_mahasiswa udm')
                          ->join('registrasi_mahasiswa rm', 'udm.mahasiswa_id = rm.id', 'left')
                          ->where('udm.id', $upload_id)
                          ->get()
                          ->row();

        if (!$upload) {
            show_404();
            return;
        }

        // Path file upload
        $full_path = FCPATH . $upload->path_file;

        // Cek apakah file ada
        if (!file_exists($full_path)) {
            log_message('error', 'File tidak ditemukan: ' . $full_path);
            show_404();
            return;
        }

        // Set headers untuk file serving
        $file_size = filesize($full_path);

        header('Content-Type: ' . $upload->mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="' . $upload->nama_file_asli . '"');
        header('Cache-Control: private, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

        // Output file
        readfile($full_path);
    }

    /**
     * Preview file upload dengan session check
     */
    public function secure_upload($upload_id) {
        // Cek apakah user sudah login
        if (!$this->session->userdata('mahasiswa_logged_in')) {
            show_404();
            return;
        }

        $mahasiswa_id = $this->session->userdata('mahasiswa_id');

        // Ambil data upload dengan validasi ownership
        $upload = $this->db->select('udm.*')
                          ->from('upload_dokumen_mahasiswa udm')
                          ->where('udm.id', $upload_id)
                          ->where('udm.mahasiswa_id', $mahasiswa_id)
                          ->get()
                          ->row();

        if (!$upload) {
            show_404();
            return;
        }

        // Path file upload
        $full_path = FCPATH . $upload->path_file;

        // Cek apakah file ada
        if (!file_exists($full_path)) {
            show_404();
            return;
        }

        // Set headers untuk file serving
        $file_size = filesize($full_path);

        header('Content-Type: ' . $upload->mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="' . $upload->nama_file_asli . '"');
        header('Cache-Control: private, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

        // Output file
        readfile($full_path);
    }
}
