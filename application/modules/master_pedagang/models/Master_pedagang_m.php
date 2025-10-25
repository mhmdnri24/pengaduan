<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pedagang_m extends CI_Model
{
    private $table = 'masyarakat_pedagang';

    public function pedagang_get_all()
    {
        // Temporary: remove joins until database columns are properly set up
        $this->db->select('m.*');
        $this->db->from($this->table . ' m');
        $this->db->order_by('m.created_at', 'desc');
        return $this->db->get()->result();
    }

    public function pedagang_get_active()
    {
        // Temporary: remove joins until database columns are properly set up
        $this->db->select('m.*');
        $this->db->from($this->table . ' m');
        $this->db->where('m.status_aktif', 1);
        $this->db->order_by('m.nama_lengkap', 'asc');
        return $this->db->get()->result();
    }

    public function pedagang_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_pedagang_with_location($id)
    {
        // Temporary: remove joins until database columns are properly set up
        $this->db->select('m.*');
        $this->db->from($this->table . ' m');
        $this->db->where('m.id', $id);
        return $this->db->get()->row();
    }

    public function pedagang_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }

    public function pedagang_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }

    public function pedagang_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }

    public function pedagang_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function pedagang_by_nik($nik)
    {
        return $this->db->where('nik', $nik)->get($this->table)->row();
    }

    public function check_nik_exists($nik, $exclude_id = null)
    {
        $this->db->where('nik', $nik);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function search_pedagang($keyword)
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from($this->table . ' m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.status_aktif', 1);

        if ($keyword) {
            $this->db->group_start();
            $this->db->like('m.nama_lengkap', $keyword);
            $this->db->or_like('m.nik', $keyword);
            $this->db->or_like('m.no_telpon', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('m.nama_lengkap', 'asc');
        $this->db->limit(20); // Limit untuk performance
        return $this->db->get()->result();
    }

    // ========== STATISTIK METHODS ==========

    public function count_all_pedagang()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_by_status($status)
    {
        return $this->db->where('status_aktif', $status)->count_all_results($this->table);
    }

    public function count_by_kecamatan($id_kecamatan)
    {
        return $this->db->where('id_kecamatan', $id_kecamatan)->count_all_results($this->table);
    }

    public function count_by_kelurahan($id_kelurahan)
    {
        return $this->db->where('id_kelurahan', $id_kelurahan)->count_all_results($this->table);
    }

    public function get_pedagang_by_kecamatan()
    {
        $this->db->select('k.nama_kecamatan, COUNT(m.id) as total');
        $this->db->from($this->table . ' m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->where('m.status_aktif', 1);
        $this->db->group_by('m.id_kecamatan');
        $this->db->order_by('total', 'desc');
        return $this->db->get()->result();
    }

    public function get_pedagang_by_kelurahan($id_kecamatan = null)
    {
        $this->db->select('kel.nama_kelurahan, COUNT(m.id) as total');
        $this->db->from($this->table . ' m');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.status_aktif', 1);

        if ($id_kecamatan) {
            $this->db->where('m.id_kecamatan', $id_kecamatan);
        }

        $this->db->group_by('m.id_kelurahan');
        $this->db->order_by('total', 'desc');
        return $this->db->get()->result();
    }

    public function get_recent_pedagang($limit = 10)
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from($this->table . ' m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->order_by('m.created_at', 'desc');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    // ========== UTILITY METHODS ==========

    public function get_kecamatan_list()
    {
        return $this->db->select('id_kecamatan, nama_kecamatan')
                        ->order_by('nama_kecamatan', 'asc')
                        ->get('kecamatan')->result();
    }

    public function get_kelurahan_by_kecamatan($id_kecamatan)
    {
        return $this->db->select('id_kelurahan, nama_kelurahan')
                        ->where('id_kecamatan', $id_kecamatan)
                        ->order_by('nama_kelurahan', 'asc')
                        ->get('kelurahan')->result();
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

    public function get_pedagang_for_export()
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from($this->table . ' m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->order_by('m.nama_lengkap', 'asc');
        return $this->db->get()->result();
    }

    // ========== VALIDATION METHODS ==========

    public function validate_pedagang_data($data, $id = null)
    {
        $errors = [];

        // Validasi nama lengkap
        if (empty($data['nama_lengkap'])) {
            $errors[] = 'Nama lengkap harus diisi';
        }

        // Validasi NIK
        if (empty($data['nik'])) {
            $errors[] = 'NIK harus diisi';
        } elseif (strlen($data['nik']) != 16) {
            $errors[] = 'NIK harus 16 digit';
        } elseif (!is_numeric($data['nik'])) {
            $errors[] = 'NIK harus berupa angka';
        } else {
            // Check NIK uniqueness
            if ($this->check_nik_exists($data['nik'], $id)) {
                $errors[] = 'NIK sudah terdaftar';
            }
        }

        return $errors;
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Count all active pedagang for frontend
     */
    public function count_all_active()
    {
        return $this->db->where('status_aktif', 1)->count_all_results($this->table);
    }

    /**
     * Count public data with filters
     */
    public function count_public_data($pasar_id = '', $search = '')
    {
        $this->db->from($this->table . ' m');
        $this->db->where('m.status_aktif', 1);

        if ($pasar_id) {
            $this->db->where('m.pasar_id', $pasar_id);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama_lengkap', $search);
            $this->db->or_like('m.nik', $search);
            $this->db->or_like('m.jenis_usaha', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Get public data with pagination and filters
     */
    public function get_public_data($limit, $offset, $pasar_id = '', $search = '')
    {
        $this->db->select('m.*, p.pasar_nama, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from($this->table . ' m');
        $this->db->join('master_pasar p', 'm.pasar_id = p.pasar_id', 'left');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.status_aktif', 1);

        if ($pasar_id) {
            $this->db->where('m.pasar_id', $pasar_id);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('m.nama_lengkap', $search);
            $this->db->or_like('m.nik', $search);
            $this->db->or_like('m.jenis_usaha', $search);
            $this->db->group_end();
        }

        $this->db->order_by('m.nama_lengkap', 'asc');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }
}