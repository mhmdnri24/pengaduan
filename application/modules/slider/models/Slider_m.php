<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Master Slider
 * Mengelola operasi database untuk tabel master_slider
 */
class Slider_m extends CI_Model
{
    private $table = 'master_slider';

    /**
     * Mendapatkan semua data slider
     * @return object
     */
    public function slider_get_all()
    {
        return $this->db->order_by('id', 'desc')->get($this->table)->result();
    }
    
    /**
     * Mendapatkan data slider yang aktif saja
     * @return object
     */
    public function slider_get_active()
    {
        return $this->db->where('slider_status', 'AKTIF')
                        ->order_by('created_at', 'asc')
                        ->get($this->table)
                        ->result();
    }
    
    /**
     * Mendapatkan data slider berdasarkan ID
     * @param int $id
     * @return object
     */
    public function slider_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    /**
     * Mendapatkan data slider berdasarkan ID dalam bentuk array
     * @param int $id
     * @return array
     */
    public function slider_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }
    
    /**
     * Menyimpan data slider baru
     * @param array $post_data
     * @return bool
     */
    public function slider_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    /**
     * Update data slider
     * @param array $post_data
     * @param int $id
     * @return bool
     */
    public function slider_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    /**
     * Hapus data slider
     * @param int $id
     * @return bool
     */
    public function slider_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Menghitung total semua slider
     * @return int
     */
    public function count_all_slider()
    {
        return $this->db->count_all($this->table);
    }

    /**
     * Menghitung slider yang aktif
     * @return int
     */
    public function count_active_slider()
    {
        return $this->db->where('slider_status', 'AKTIF')
                        ->count_all_results($this->table);
    }

    /**
     * Menghitung slider yang tidak aktif
     * @return int
     */
    public function count_inactive_slider()
    {
        return $this->db->where('slider_status', 'NON AKTIF')
                        ->count_all_results($this->table);
    }

    /**
     * Mendapatkan slider untuk dropdown/select
     * @return array
     */
    public function get_slider_for_select()
    {
        $this->db->select('id, slider_judul');
        $this->db->where('slider_status', 'AKTIF');
        $this->db->order_by('slider_judul', 'asc');
        $result = $this->db->get($this->table)->result();
        
        $options = [];
        foreach ($result as $row) {
            $options[$row->id] = $row->slider_judul;
        }
        
        return $options;
    }

    /**
     * Mencari slider berdasarkan keyword
     * @param string $keyword
     * @return object
     */
    public function search_slider($keyword)
    {
        $this->db->like('slider_judul', $keyword);
        $this->db->or_like('slider_deskripsi', $keyword);
        $this->db->order_by('slider_judul', 'asc');
        return $this->db->get($this->table)->result();
    }

    /**
     * Memeriksa apakah judul slider sudah ada
     * @param string $slider_judul
     * @param int $exclude_id
     * @return bool
     */
    public function check_judul_exists($slider_judul, $exclude_id = null)
    {
        $this->db->where('slider_judul', $slider_judul);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    /**
     * Validasi data slider
     * @param array $data
     * @param int $id
     * @return array
     */
    public function validate_slider_data($data, $id = null)
    {
        $errors = [];

        // Validasi judul slider
        if (empty($data['slider_judul'])) {
            $errors[] = 'Judul slider tidak boleh kosong';
        }

        // Validasi status
        if (!empty($data['slider_status']) && 
            !in_array($data['slider_status'], ['AKTIF', 'NON AKTIF'])) {
            $errors[] = 'Status slider tidak valid';
        }

        // Check for duplicate judul slider
        if (!empty($data['slider_judul'])) {
            if ($this->check_judul_exists($data['slider_judul'], $id)) {
                $errors[] = 'Judul slider sudah digunakan';
            }
        }

        return $errors;
    }

    /**
     * Mendapatkan slider dengan format lengkap untuk frontend
     * @param int $limit
     * @return object
     */
    public function get_slider_for_frontend($limit = null)
    {
        $this->db->where('slider_status', 'AKTIF');
        $this->db->order_by('created_at', 'asc');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Update status slider (Aktif/Non Aktif)
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function update_slider_status($id, $status)
    {
        if (!in_array($status, ['AKTIF', 'NON AKTIF'])) {
            return false;
        }
        
        return $this->db->where('id', $id)
                       ->update($this->table, ['slider_status' => $status]);
    }

    /**
     * Menghapus file slider dari database saat update
     * @param int $id
     * @return bool
     */
    public function clear_slider_file($id)
    {
        return $this->db->where('id', $id)
                       ->update($this->table, ['slider_file' => null]);
    }
}