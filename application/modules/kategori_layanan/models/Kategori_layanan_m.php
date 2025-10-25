<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_layanan_m extends CI_Model
{
    public function kategori_get_all()
    {
        return $this->db->get('layanan_kategori')->result();
    }

    public function kategori_by_id($id)
    {
        return $this->db->where('kategori_id', $id)->get('layanan_kategori')->row();
    }

    public function kategori_by_id_array($id)
    {
        return $this->db->where('kategori_id', $id)->get('layanan_kategori')->result();
    }

    public function kategori_insert_data($post_data)
    {
        return $this->db->insert('layanan_kategori', $post_data);
    }

    public function kategori_update_data($post_data, $id)
    {
        return $this->db->where('kategori_id', $id)->update('layanan_kategori', $post_data);
    }

    public function kategori_delete_data($id)
    {
        return $this->db->where('kategori_id', $id)->delete('layanan_kategori');
    }

    public function kategori_aktif()
    {
        return $this->db->where('kategori_status', 1)->get('layanan_kategori')->result();
    }

    public function kategori_tidak_aktif()
    {
        return $this->db->where('kategori_status', 0)->get('layanan_kategori')->result();
    }

    public function count_layanan_by_kategori($kategori_id)
    {
        return $this->db->where('layanan_kategori', $kategori_id)->count_all_results('layanan_jenis');
    }

    public function count_aktif()
    {
        return $this->db->where('kategori_status', 1)->count_all_results('layanan_kategori');
    }

    public function count_tidak_aktif()
    {
        return $this->db->where('kategori_status', 0)->count_all_results('layanan_kategori');
    }

    public function kategori_with_layanan()
    {
        return $this->db->select('lk.*, COUNT(lj.layanan_id) as total_layanan')
                        ->from('layanan_kategori lk')
                        ->join('layanan_jenis lj', 'lk.kategori_id = lj.layanan_kategori', 'left')
                        ->group_by('lk.kategori_id')
                        ->order_by('lk.kategori_id', 'asc')
                        ->get()->result();
    }
}