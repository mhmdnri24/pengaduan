<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kepengurusan_detail_m extends CI_Model
{
    private $table = 'kepengurusan_detail';

    public function kepengurusan_detail_get_all()
    {
        return $this->db->order_by('created_at', 'desc')->get($this->table)->result();
    }
    
    public function kepengurusan_detail_get_active()
    {
        return $this->db->where('status', 'AKTIF')->order_by('tanggal_mulai', 'desc')->get($this->table)->result();
    }
    
    public function kepengurusan_detail_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function kepengurusan_detail_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }
    
    public function kepengurusan_detail_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function kepengurusan_detail_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    public function kepengurusan_detail_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function kepengurusan_detail_by_kepengurusan_sosial($kepengurusan_sosial_id)
    {
        return $this->db->where('kepengurusan_sosial_id', $kepengurusan_sosial_id)
                        ->order_by('tanggal_mulai', 'desc')
                        ->get($this->table)->result();
    }

    public function kepengurusan_detail_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_kepengurusan_id', $kategori_id)
                        ->order_by('tanggal_mulai', 'desc')
                        ->get($this->table)->result();
    }

    public function kepengurusan_detail_by_fasilitas($fasilitas_id)
    {
        return $this->db->where('id_fasilitas_umum', $fasilitas_id)
                        ->order_by('tanggal_mulai', 'desc')
                        ->get($this->table)->result();
    }

    public function kepengurusan_detail_by_status($status)
    {
        return $this->db->where('status', $status)
                        ->order_by('tanggal_mulai', 'desc')
                        ->get($this->table)->result();
    }

    // Count methods for statistics
    public function count_all_detail()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_by_status($status)
    {
        return $this->db->where('status', $status)->count_all_results($this->table);
    }

    public function count_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_kepengurusan_id', $kategori_id)->count_all_results($this->table);
    }

    public function count_by_fasilitas($fasilitas_id)
    {
        return $this->db->where('id_fasilitas_umum', $fasilitas_id)->count_all_results($this->table);
    }

    // Validation method
    public function validate_detail_data($data, $id = null)
    {
        $errors = [];

        // Validasi kepengurusan sosial
        if (empty($data['kepengurusan_sosial_id'])) {
            $errors[] = 'Pengurus harus dipilih';
        }

        // Validasi kategori kepengurusan
        if (empty($data['kategori_kepengurusan_id'])) {
            $errors[] = 'Kategori kepengurusan harus dipilih';
        }

        // Validasi fasilitas umum
        if (empty($data['id_fasilitas_umum'])) {
            $errors[] = 'Fasilitas umum harus dipilih';
        }

        // Validasi tanggal
        if (!empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
            if (strtotime($data['tanggal_mulai']) > strtotime($data['tanggal_selesai'])) {
                $errors[] = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai';
            }
        }

        return $errors;
    }

    // Get detail with complete info
    public function get_detail_with_complete_info($id)
    {
        $this->db->select('kd.*, ks.nama_lengkap, ks.nik_ktp, ks.no_wa_hp, kk.kepengurusan_nama, fu.nama_fasilitas');
        $this->db->from($this->table . ' kd');
        $this->db->join('kepengurusan_sosial ks', 'kd.kepengurusan_sosial_id = ks.id', 'left');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->join('fasilitas_umum fu', 'kd.id_fasilitas_umum = fu.id', 'left');
        $this->db->where('kd.id', $id);
        return $this->db->get()->row();
    }

    public function get_detail_list_with_info()
    {
        $this->db->select('kd.*, ks.nama_lengkap, ks.nik_ktp, ks.no_wa_hp, kk.kepengurusan_nama, fu.nama_fasilitas');
        $this->db->from($this->table . ' kd');
        $this->db->join('kepengurusan_sosial ks', 'kd.kepengurusan_sosial_id = ks.id', 'left');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->join('fasilitas_umum fu', 'kd.id_fasilitas_umum = fu.id', 'left');
        $this->db->order_by('kd.created_at', 'desc');
        return $this->db->get()->result();
    }

    // Check if person already has active assignment in same facility and category
    public function check_duplicate_assignment($kepengurusan_sosial_id, $kategori_kepengurusan_id, $id_fasilitas_umum, $exclude_id = null)
    {
        $this->db->where('kepengurusan_sosial_id', $kepengurusan_sosial_id);
        $this->db->where('kategori_kepengurusan_id', $kategori_kepengurusan_id);
        $this->db->where('id_fasilitas_umum', $id_fasilitas_umum);
        $this->db->where('status', 'AKTIF');
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Get active assignments by person
    public function get_active_assignments_by_person($kepengurusan_sosial_id)
    {
        $this->db->select('kd.*, kk.kepengurusan_nama, fu.nama_fasilitas');
        $this->db->from($this->table . ' kd');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->join('fasilitas_umum fu', 'kd.id_fasilitas_umum = fu.id', 'left');
        $this->db->where('kd.kepengurusan_sosial_id', $kepengurusan_sosial_id);
        $this->db->where('kd.status', 'AKTIF');
        $this->db->order_by('kd.tanggal_mulai', 'desc');
        return $this->db->get()->result();
    }

    // Get assignments by facility
    public function get_assignments_by_facility($id_fasilitas_umum)
    {
        $this->db->select('kd.*, ks.nama_lengkap, kk.kepengurusan_nama');
        $this->db->from($this->table . ' kd');
        $this->db->join('kepengurusan_sosial ks', 'kd.kepengurusan_sosial_id = ks.id', 'left');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->where('kd.id_fasilitas_umum', $id_fasilitas_umum);
        $this->db->order_by('kd.tanggal_mulai', 'desc');
        return $this->db->get()->result();
    }

    // Update status to expired for assignments that have passed end date
    public function update_expired_assignments()
    {
        $this->db->where('tanggal_selesai <', date('Y-m-d'));
        $this->db->where('status', 'AKTIF');
        return $this->db->update($this->table, ['status' => 'SELESAI', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // Get statistics for dashboard
    public function get_statistics()
    {
        $stats = [];
        
        $stats['total_penugasan'] = $this->count_all_detail();
        $stats['penugasan_aktif'] = $this->count_by_status('AKTIF');
        $stats['penugasan_selesai'] = $this->count_by_status('SELESAI');
        $stats['penugasan_nonaktif'] = $this->count_by_status('NONAKTIF');
        
        return $stats;
    }
}
