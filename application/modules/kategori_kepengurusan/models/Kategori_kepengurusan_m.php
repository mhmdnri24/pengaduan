<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_kepengurusan_m extends CI_Model
{
    private $table = 'kategori_kepengurusan';

    public function kategori_kepengurusan_get_all()
    {
        return $this->db->order_by('kepengurusan_kode', 'asc')->get($this->table)->result();
    }
    
    public function kategori_kepengurusan_get_active()
    {
        return $this->db->where('status', 1)->order_by('kepengurusan_nama', 'asc')->get($this->table)->result();
    }
    
    public function kategori_kepengurusan_by_id($id)
    {
        return $this->db->where('kepengurusan_id', $id)->get($this->table)->row();
    }
    
    public function kategori_kepengurusan_by_id_array($id)
    {
        return $this->db->where('kepengurusan_id', $id)->get($this->table)->result();
    }
    
    public function kategori_kepengurusan_by_kode($kode)
    {
        return $this->db->where('kepengurusan_kode', $kode)->get($this->table)->row();
    }
    
    public function kategori_kepengurusan_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function kategori_kepengurusan_update_data($post_data, $id)
    {
        return $this->db->where('kepengurusan_id', $id)->update($this->table, $post_data);
    }
    
    public function kategori_kepengurusan_delete_data($id)
    {
        return $this->db->where('kepengurusan_id', $id)->delete($this->table);
    }

    public function check_kode_exists($kode, $exclude_id = null)
    {
        $this->db->where('kepengurusan_kode', $kode);
        if ($exclude_id) {
            $this->db->where('kepengurusan_id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function check_nama_exists($nama, $exclude_id = null)
    {
        $this->db->where('kepengurusan_nama', $nama);
        if ($exclude_id) {
            $this->db->where('kepengurusan_id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Count methods for statistics
    public function count_all_kategori()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_active_kategori()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }

    public function count_inactive_kategori()
    {
        return $this->db->where('status', 0)->count_all_results($this->table);
    }

    // Validation method
    public function validate_kategori_data($data, $id = null)
    {
        $errors = [];

        // Validasi kode kepengurusan
        if (empty($data['kepengurusan_kode'])) {
            $errors[] = 'Kode kepengurusan tidak boleh kosong';
        }

        // Validasi nama kepengurusan
        if (empty($data['kepengurusan_nama'])) {
            $errors[] = 'Nama kepengurusan tidak boleh kosong';
        }

        // Check for duplicate kode
        if (!empty($data['kepengurusan_kode'])) {
            if ($this->check_kode_exists($data['kepengurusan_kode'], $id)) {
                $errors[] = 'Kode kepengurusan sudah digunakan';
            }
        }

        // Check for duplicate nama
        if (!empty($data['kepengurusan_nama'])) {
            if ($this->check_nama_exists($data['kepengurusan_nama'], $id)) {
                $errors[] = 'Nama kepengurusan sudah digunakan';
            }
        }

        return $errors;
    }

    public function get_kategori_for_select()
    {
        $this->db->select('kepengurusan_id, kepengurusan_nama');
        $this->db->where('status', 1);
        $this->db->order_by('kepengurusan_nama', 'asc');
        $result = $this->db->get($this->table)->result();
        
        $options = [];
        foreach ($result as $row) {
            $options[$row->kepengurusan_id] = $row->kepengurusan_nama;
        }
        
        return $options;
    }

    // Check usage in other tables
    public function count_usage_in_kepengurusan_detail($kategori_id)
    {
        // Cek penggunaan di tabel kepengurusan_detail
        return $this->db->where('kategori_kepengurusan_id', $kategori_id)->count_all_results('kepengurusan_detail');
    }

    public function get_next_kode()
    {
        $this->db->select('kepengurusan_kode');
        $this->db->like('kepengurusan_kode', 'KP-', 'after');
        $this->db->order_by('kepengurusan_kode', 'desc');
        $this->db->limit(1);
        $result = $this->db->get($this->table)->row();
        
        if ($result) {
            $last_number = (int) substr($result->kepengurusan_kode, 3);
            $next_number = $last_number + 1;
            return 'KP-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
        } else {
            return 'KP-001';
        }
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Get all active kategori for frontend
     */
    public function get_all_active()
    {
        return $this->kategori_kepengurusan_get_active();
    }
}
