<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wa_template_m extends CI_Model {

    private $table = 'wa_template';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mendapatkan semua template WhatsApp
     * 
     * @return array Daftar template
     */
    public function get_all_templates()
    {
        $this->db->order_by('nama', 'ASC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Mendapatkan template berdasarkan ID
     * 
     * @param int $id ID template
     * @return object Template
     */
    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Mendapatkan template berdasarkan kode
     * 
     * @param string $kode Kode template
     * @return object Template
     */
    public function get_by_kode($kode)
    {
        return $this->db->get_where($this->table, ['kode' => $kode])->row();
    }

    /**
     * Menyimpan template baru
     * 
     * @param array $data Data template
     * @return int ID template yang disimpan
     */
    public function save($data)
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
    
    /**
     * Menyimpan template baru dengan dukungan emoji
     * 
     * @param array $data Data template
     * @return int ID template yang disimpan
     */
    public function save_with_emoji($data)
    {
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        
        // Set koneksi ke utf8mb4 untuk mendukung emoji
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET CHARACTER SET utf8mb4");
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Mengupdate template
     * 
     * @param int $id ID template
     * @param array $data Data template
     * @return bool Status update
     */
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Mengupdate template dengan dukungan emoji
     * 
     * @param int $id ID template
     * @param array $data Data template
     * @return bool Status update
     */
    public function update_with_emoji($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Set koneksi ke utf8mb4 untuk mendukung emoji
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET CHARACTER SET utf8mb4");
        
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Menghapus template
     * 
     * @param int $id ID template
     * @return bool Status delete
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Memproses template dengan parameter yang diberikan
     * 
     * @param string $kode Kode template atau ID template
     * @param array $params Parameter untuk mengganti placeholder di template
     * @return string|bool Isi template yang sudah diproses atau false jika template tidak ditemukan
     */
    public function proses_template($kode, $params = [])
    {
        if (is_numeric($kode)) {
            $template = $this->get_by_id($kode);
        } else {
            $template = $this->get_by_kode($kode);
        }

        if (!$template) {
            return false;
        }

        $pesan = $template->isi_template;

        // Ganti semua placeholder dengan nilai parameter
        foreach ($params as $key => $value) {
            $pesan = str_replace('{'.$key.'}', $value, $pesan);
        }

        return $pesan;
    }
} 