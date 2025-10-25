<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Filemanager extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('secure_file');
    }

    public function serve() {
        $file_id = $this->input->get('id');
        $expires = $this->input->get('expires');
        $signature = $this->input->get('signature');

        // Validasi parameter
        if (!$file_id || !$expires || !$signature) {
            show_404();
        }

        // Validasi signature dan expiration
        if (!validate_secure_url($file_id, $expires, $signature)) {
            show_404();
        }

        // Cek apakah file_id adalah path file atau dokumen ID
        if (strpos($file_id, 'uploads/') === 0) {
            // File path langsung
            $file_path = FCPATH . $file_id;
            $mime_type = $this->get_mime_type($file_path);
            $filename = basename($file_path);
        } else {
            // Dokumen ID dari database
            $dokumen = $this->get_dokumen_by_id($file_id);
            if (!$dokumen) {
                show_404();
            }
            
            $file_path = FCPATH . $dokumen->path_file;
            $mime_type = $dokumen->mime_type;
            $filename = $dokumen->nama_file_asli;
        }

        // Cek keberadaan file
        if (!file_exists($file_path)) {
            show_404();
        }

        // Set headers untuk serve file
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=3600, must-revalidate');
        header('Pragma: public');
        header('Accept-Ranges: bytes');

        // Output file
        readfile($file_path);
        exit;
    }

    private function get_dokumen_by_id($dokumen_id) {
        $sql = "SELECT udm.*, md.dokumen_nama 
                FROM upload_dokumen_mahasiswa udm
                LEFT JOIN master_dokumen md ON udm.master_dokumen_id = md.dokumen_id
                WHERE udm.id = ?";
        
        return $this->db->query($sql, [$dokumen_id])->row();
    }

    private function get_mime_type($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        $mime_types = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        return $mime_types[$extension] ?? 'application/octet-stream';
    }
}
