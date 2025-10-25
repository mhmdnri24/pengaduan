<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_pelaporan_m extends CI_Model
{
    protected $table = 'kategori_pelaporan';
    protected $primary_key = 'pelaporan_id';

    public function __construct()
    {
        parent::__construct();
    }

    public function kategori_pelaporan_insert_data($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function kategori_pelaporan_update_data($data, $id)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->update($this->table, $data);
    }

    public function kategori_pelaporan_delete_data($id)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->delete($this->table);
    }

    public function kategori_pelaporan_by_id($id)
    {
        $this->db->where($this->primary_key, $id);
        return $this->db->get($this->table)->row();
    }

    public function kategori_pelaporan_all()
    {
        return $this->db->order_by($this->primary_key, 'desc')->get($this->table)->result();
    }

    public function kategori_pelaporan_active()
    {
        return $this->db->where('status', 1)->order_by('pelaporan_nama', 'asc')->get($this->table)->result();
    }

    public function count_all_kategori()
    {
        return $this->db->count_all($this->table);
    }

    public function count_active_kategori()
    {
        $this->db->where('status', 1);
        return $this->db->count_all_results($this->table);
    }

    public function count_inactive_kategori()
    {
        $this->db->where('status', 0);
        return $this->db->count_all_results($this->table);
    }

    public function get_next_kode()
    {
        $this->db->select('pelaporan_kode');
        $this->db->like('pelaporan_kode', 'KPL-', 'after');
        $this->db->order_by('pelaporan_kode', 'desc');
        $this->db->limit(1);
        $result = $this->db->get($this->table)->row();
        
        if ($result) {
            $last_number = (int) substr($result->pelaporan_kode, 4);
            $next_number = $last_number + 1;
            return 'KPL-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
        } else {
            return 'KPL-001';
        }
    }

    public function validate_kategori_data($data, $id = null)
    {
        $errors = array();

        // Validasi kode kategori
        if (empty($data['pelaporan_kode'])) {
            $errors[] = 'Kode kategori pelaporan harus diisi';
        } else {
            // Cek duplikasi kode
            $this->db->where('pelaporan_kode', $data['pelaporan_kode']);
            if ($id) {
                $this->db->where($this->primary_key . ' !=', $id);
            }
            $existing = $this->db->get($this->table)->row();
            if ($existing) {
                $errors[] = 'Kode kategori pelaporan sudah digunakan';
            }
        }

        // Validasi nama kategori
        if (empty($data['pelaporan_nama'])) {
            $errors[] = 'Nama kategori pelaporan harus diisi';
        } else {
            // Cek duplikasi nama
            $this->db->where('pelaporan_nama', $data['pelaporan_nama']);
            if ($id) {
                $this->db->where($this->primary_key . ' !=', $id);
            }
            $existing = $this->db->get($this->table)->row();
            if ($existing) {
                $errors[] = 'Nama kategori pelaporan sudah digunakan';
            }
        }

        return $errors;
    }

    public function count_usage_in_pelaporan($kategori_id)
    {
        $this->db->where('kategori_id', $kategori_id);
        return $this->db->count_all_results('pelaporan');
    }

    public function get_kategori_for_dropdown()
    {
        $this->db->select('pelaporan_id, pelaporan_nama');
        $this->db->where('status', 1);
        $this->db->order_by('pelaporan_nama', 'asc');
        $result = $this->db->get($this->table)->result();
        
        $dropdown = array();
        $dropdown[''] = '-- Pilih Kategori --';
        foreach ($result as $row) {
            $dropdown[$row->pelaporan_id] = $row->pelaporan_nama;
        }
        
        return $dropdown;
    }

    public function get_kategori_stats()
    {
        $stats = array();
        
        // Total kategori
        $stats['total'] = $this->count_all_kategori();
        
        // Kategori aktif
        $stats['aktif'] = $this->count_active_kategori();
        
        // Kategori nonaktif
        $stats['nonaktif'] = $this->count_inactive_kategori();
        
        // Kategori paling banyak digunakan
        $this->db->select('k.pelaporan_nama, COUNT(p.pelaporan_id) as jumlah_laporan');
        $this->db->from($this->table . ' k');
        $this->db->join('pelaporan p', 'k.pelaporan_id = p.kategori_id', 'left');
        $this->db->where('k.status', 1);
        $this->db->group_by('k.pelaporan_id');
        $this->db->order_by('jumlah_laporan', 'desc');
        $this->db->limit(1);
        $most_used = $this->db->get()->row();
        
        $stats['most_used'] = $most_used ? $most_used->pelaporan_nama : 'Belum ada data';
        $stats['most_used_count'] = $most_used ? $most_used->jumlah_laporan : 0;
        
        return $stats;
    }

    public function get_kategori_usage_chart()
    {
        $this->db->select('k.pelaporan_nama, COUNT(p.pelaporan_id) as jumlah');
        $this->db->from($this->table . ' k');
        $this->db->join('pelaporan p', 'k.pelaporan_id = p.kategori_id', 'left');
        $this->db->where('k.status', 1);
        $this->db->group_by('k.pelaporan_id');
        $this->db->order_by('jumlah', 'desc');
        $result = $this->db->get()->result();
        
        $chart_data = array();
        foreach ($result as $row) {
            $chart_data[] = array(
                'label' => $row->pelaporan_nama,
                'value' => (int) $row->jumlah
            );
        }
        
        return $chart_data;
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Get all active kategori for frontend
     */
    public function get_all_active()
    {
        return $this->kategori_pelaporan_active();
    }
}
