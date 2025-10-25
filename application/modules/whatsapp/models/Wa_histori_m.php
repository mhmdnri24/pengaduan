<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wa_histori_m extends CI_Model {

    private $table = 'wa_histori';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mendapatkan semua histori WhatsApp
     * 
     * @param int $limit Batas jumlah data
     * @param int $offset Offset data
     * @param array $filter Filter data
     * @return array Daftar histori
     */
    public function get_all($limit = null, $offset = 0, $filter = [])
    {
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $this->db->group_start();
            $this->db->like('nomor_tujuan', $filter['keyword']);
            $this->db->or_like('nama_tujuan', $filter['keyword']);
            $this->db->or_like('pesan', $filter['keyword']);
            $this->db->group_end();
        }

        if (isset($filter['cabang_id']) && !empty($filter['cabang_id'])) {
            $this->db->where('cabang_id', $filter['cabang_id']);
        }

        if (isset($filter['date_from']) && !empty($filter['date_from'])) {
            $this->db->where('waktu_kirim >=', $filter['date_from'] . ' 00:00:00');
        }

        if (isset($filter['date_to']) && !empty($filter['date_to'])) {
            $this->db->where('waktu_kirim <=', $filter['date_to'] . ' 23:59:59');
        }

        if (isset($filter['status']) && !empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }

        $this->db->order_by('waktu_kirim', 'DESC');
        
        if ($limit !== null) {
            return $this->db->get($this->table, $limit, $offset)->result();
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Mendapatkan jumlah total histori WhatsApp
     *
     * @param array $filter Filter data
     * @return int Jumlah total histori
     */
    public function count_all($filter = [])
    {
        if (isset($filter['keyword']) && !empty($filter['keyword'])) {
            $this->db->group_start();
            $this->db->like('nomor_tujuan', $filter['keyword']);
            $this->db->or_like('nama_tujuan', $filter['keyword']);
            $this->db->or_like('pesan', $filter['keyword']);
            $this->db->group_end();
        }

        if (isset($filter['cabang_id']) && !empty($filter['cabang_id'])) {
            $this->db->where('cabang_id', $filter['cabang_id']);
        }

        if (isset($filter['date_from']) && !empty($filter['date_from'])) {
            $this->db->where('waktu_kirim >=', $filter['date_from'] . ' 00:00:00');
        }

        if (isset($filter['date_to']) && !empty($filter['date_to'])) {
            $this->db->where('waktu_kirim <=', $filter['date_to'] . ' 23:59:59');
        }

        if (isset($filter['status']) && !empty($filter['status'])) {
            $this->db->where('status', $filter['status']);
        }

        return $this->db->count_all_results($this->table);
    }

    /**
     * Mendapatkan histori berdasarkan ID
     * 
     * @param int $id ID histori
     * @return object Histori
     */
    public function get_by_id($id)
    {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Menyimpan histori baru
     * 
     * @param array $data Data histori
     * @return int ID histori yang disimpan
     */
    public function save($data)
    {
        if (!isset($data['waktu_kirim'])) {
            $data['waktu_kirim'] = date('Y-m-d H:i:s');
        }
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Mengupdate histori
     * 
     * @param int $id ID histori
     * @param array $data Data histori
     * @return bool Status update
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Menghapus histori
     * 
     * @param int $id ID histori
     * @return bool Status delete
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
} 