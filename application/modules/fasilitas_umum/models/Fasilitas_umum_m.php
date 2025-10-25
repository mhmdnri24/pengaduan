<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fasilitas_umum_m extends CI_Model
{
    private $table = 'fasilitas_umum';

    public function fasilitas_umum_get_all()
    {
        return $this->db->order_by('id', 'desc')->get($this->table)->result();
    }
    
    public function fasilitas_umum_get_active()
    {
        return $this->db->where('status', 1)->order_by('nama_fasilitas', 'asc')->get($this->table)->result();
    }
    
    public function fasilitas_umum_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function fasilitas_umum_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }
    
    public function fasilitas_umum_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function fasilitas_umum_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    public function fasilitas_umum_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    // Statistics methods for information boxes
    public function count_all_fasilitas()
    {
        return $this->db->count_all($this->table);
    }

    public function count_active_fasilitas()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }

    public function count_inactive_fasilitas()
    {
        return $this->db->where('status', 0)->count_all_results($this->table);
    }

    public function count_fasilitas_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_id', $kategori_id)->count_all_results($this->table);
    }

    public function count_fasilitas_by_kecamatan($id_kecamatan)
    {
        return $this->db->where('id_kecamatan', $id_kecamatan)->count_all_results($this->table);
    }

    // Helper methods
    public function fasilitas_umum_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_id', $kategori_id)
            ->where('status', 1)
            ->order_by('nama_fasilitas', 'asc')
            ->get($this->table)
            ->result();
    }

    public function fasilitas_umum_by_kota($id_kota)
    {
        return $this->db->where('id_kota', $id_kota)
            ->where('status', 1)
            ->order_by('nama_fasilitas', 'asc')
            ->get($this->table)
            ->result();
    }

    public function get_fasilitas_for_select()
    {
        $this->db->select('id, nama_fasilitas');
        $this->db->where('status', 1);
        $this->db->order_by('nama_fasilitas', 'asc');
        $result = $this->db->get($this->table)->result();
        
        $options = [];
        foreach ($result as $row) {
            $options[$row->id] = $row->nama_fasilitas;
        }
        
        return $options;
    }

    public function search_fasilitas($keyword)
    {
        $this->db->like('nama_fasilitas', $keyword);
        $this->db->where('status', 1);
        $this->db->order_by('nama_fasilitas', 'asc');
        return $this->db->get($this->table)->result();
    }

    public function check_nama_exists($nama_fasilitas, $exclude_id = null)
    {
        $this->db->where('nama_fasilitas', $nama_fasilitas);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Validation method
    public function validate_fasilitas_data($data, $id = null)
    {
        $errors = [];

        // Validasi nama fasilitas
        if (empty($data['nama_fasilitas'])) {
            $errors[] = 'Nama fasilitas tidak boleh kosong';
        }

        // Validasi kategori
        if (empty($data['kategori_id'])) {
            $errors[] = 'Kategori harus dipilih';
        }

        // Check for duplicate nama fasilitas
        if (!empty($data['nama_fasilitas'])) {
            if ($this->check_nama_exists($data['nama_fasilitas'], $id)) {
                $errors[] = 'Nama fasilitas sudah digunakan';
            }
        }

        return $errors;
    }

    // Get fasilitas with complete location info
    public function get_fasilitas_with_location($id)
    {
        $this->db->select('fu.*, mk.nama_kategori, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan');
        $this->db->from('fasilitas_umum fu');
        $this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
        $this->db->join('kota kt', 'fu.id_kota = kt.id_kota', 'left');
        $this->db->join('kecamatan kc', 'fu.id_kecamatan = kc.id_kecamatan', 'left');
        $this->db->join('kelurahan kl', 'fu.id_kelurahan = kl.id_kelurahan', 'left');
        $this->db->where('fu.id', $id);
        return $this->db->get()->row();
    }

    // ==== FOTO METHODS ====

    /**
     * Insert foto data
     */
    public function insert_foto($data)
    {
        return $this->db->insert('fasilitas_umum_foto', $data);
    }

    /**
     * Get all photos for a facility
     */
    public function get_fotos_by_fasilitas($fasilitas_id, $status = 1)
    {
        $this->db->where('fasilitas_id', $fasilitas_id);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('urutan', 'asc');
        $this->db->order_by('created_at', 'asc');
        return $this->db->get('fasilitas_umum_foto')->result();
    }

    /**
     * Get single photo by ID
     */
    public function get_foto_by_id($foto_id)
    {
        return $this->db->where('id', $foto_id)->get('fasilitas_umum_foto')->row();
    }

    /**
     * Delete photo by ID
     */
    public function delete_foto($foto_id)
    {
        return $this->db->where('id', $foto_id)->delete('fasilitas_umum_foto');
    }

    /**
     * Delete all photos for a facility
     */
    public function delete_fotos_by_fasilitas($fasilitas_id)
    {
        return $this->db->where('fasilitas_id', $fasilitas_id)->delete('fasilitas_umum_foto');
    }

    /**
     * Update photo order
     */
    public function update_foto_urutan($foto_id, $urutan)
    {
        return $this->db->where('id', $foto_id)->update('fasilitas_umum_foto', ['urutan' => $urutan]);
    }

    /**
     * Update photo caption
     */
    public function update_foto_keterangan($foto_id, $keterangan)
    {
        return $this->db->where('id', $foto_id)->update('fasilitas_umum_foto', ['keterangan' => $keterangan]);
    }

    /**
     * Count photos for a facility
     */
    public function count_fotos_by_fasilitas($fasilitas_id)
    {
        return $this->db->where('fasilitas_id', $fasilitas_id)
                       ->where('status', 1)
                       ->count_all_results('fasilitas_umum_foto');
    }
}
