<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_retribusi_m extends CI_Model
{
    private $table = 'master_retribusi';

    public function retribusi_get_all()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('created_at', 'desc');
        return $this->db->get()->result();
    }

    public function retribusi_get_active()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('status_aktif', 1);
        $this->db->order_by('nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function retribusi_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function retribusi_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }

    public function retribusi_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }

    public function retribusi_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }

    public function retribusi_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function retribusi_by_kode($kode_retribusi)
    {
        return $this->db->where('kode_retribusi', $kode_retribusi)->get($this->table)->row();
    }

    public function check_kode_exists($kode_retribusi, $exclude_id = null)
    {
        $this->db->where('kode_retribusi', $kode_retribusi);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function search_retribusi($keyword)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('status_aktif', 1);

        if ($keyword) {
            $this->db->group_start();
            $this->db->like('nama_retribusi', $keyword);
            $this->db->or_like('kode_retribusi', $keyword);
            $this->db->or_like('jenis_retribusi', $keyword);
            $this->db->or_like('deskripsi', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('nama_retribusi', 'asc');
        $this->db->limit(20); // Limit untuk performance
        return $this->db->get()->result();
    }

    // ========== STATISTIK METHODS ==========

    public function count_all_retribusi()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_by_status($status)
    {
        return $this->db->where('status_aktif', $status)->count_all_results($this->table);
    }

    public function count_by_jenis($jenis_retribusi)
    {
        return $this->db->where('jenis_retribusi', $jenis_retribusi)->count_all_results($this->table);
    }

    public function get_retribusi_by_jenis()
    {
        $this->db->select('jenis_retribusi, COUNT(id) as total, SUM(tarif) as total_tarif');
        $this->db->from($this->table);
        $this->db->where('status_aktif', 1);
        $this->db->group_by('jenis_retribusi');
        $this->db->order_by('total', 'desc');
        return $this->db->get()->result();
    }

    public function get_recent_retribusi($limit = 10)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('created_at', 'desc');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    // ========== UTILITY METHODS ==========

    public function get_jenis_retribusi_list()
    {
        $this->db->select('DISTINCT(jenis_retribusi) as jenis_retribusi');
        $this->db->from($this->table);
        $this->db->where('jenis_retribusi IS NOT NULL');
        $this->db->where('jenis_retribusi !=', '');
        $this->db->order_by('jenis_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function update_status($id, $status)
    {
        $data = [
            'status_aktif' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    // ========== EXPORT METHODS ==========

    public function get_retribusi_for_export()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->order_by('nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    // ========== VALIDATION METHODS ==========

    public function validate_retribusi_data($data, $id = null)
    {
        $errors = [];

        // Validasi nama retribusi
        if (empty($data['nama_retribusi'])) {
            $errors[] = 'Nama retribusi harus diisi';
        }

        // Validasi kode retribusi
        if (empty($data['kode_retribusi'])) {
            $errors[] = 'Kode retribusi harus diisi';
        } else {
            // Check kode uniqueness
            if ($this->check_kode_exists($data['kode_retribusi'], $id)) {
                $errors[] = 'Kode retribusi sudah terdaftar';
            }
        }

        // Validasi jenis retribusi
        if (empty($data['jenis_retribusi'])) {
            $errors[] = 'Jenis retribusi harus diisi';
        }

        // Validasi tarif
        if (!is_numeric($data['tarif']) || $data['tarif'] < 0) {
            $errors[] = 'Tarif harus berupa angka dan tidak boleh negatif';
        }

        // Validasi satuan
        if (empty($data['satuan'])) {
            $errors[] = 'Satuan harus diisi';
        }

        return $errors;
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Count all active retribusi for frontend
     */
    public function count_all_active()
    {
        return $this->db->where('status_aktif', 1)->count_all_results($this->table);
    }

    /**
     * Count public data with filters
     */
    public function count_public_data($jenis_retribusi = '', $search = '')
    {
        $this->db->from($this->table);
        $this->db->where('status_aktif', 1);

        if ($jenis_retribusi) {
            $this->db->where('jenis_retribusi', $jenis_retribusi);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('nama_retribusi', $search);
            $this->db->or_like('kode_retribusi', $search);
            $this->db->or_like('jenis_retribusi', $search);
            $this->db->or_like('deskripsi', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Get public data with pagination and filters
     */
    public function get_public_data($limit, $offset, $jenis_retribusi = '', $search = '')
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('status_aktif', 1);

        if ($jenis_retribusi) {
            $this->db->where('jenis_retribusi', $jenis_retribusi);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('nama_retribusi', $search);
            $this->db->or_like('kode_retribusi', $search);
            $this->db->or_like('jenis_retribusi', $search);
            $this->db->or_like('deskripsi', $search);
            $this->db->group_end();
        }

        $this->db->order_by('nama_retribusi', 'asc');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }
}