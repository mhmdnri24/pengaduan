<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kecamatan_m extends CI_Model
{

    public function kecamatan_get_all()
    {
        $result = $this->db->order_by('nama_kecamatan', 'asc')
            ->get('kecamatan')
            ->result();
        return $result;
    }

    public function kecamatan_by_id($id_kecamatan)
    {
        $result = $this->db->where('id_kecamatan', $id_kecamatan)
            ->get('kecamatan')
            ->row();
        return $result;
    }

    public function kecamatan_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('kecamatan');
    }

    public function kecamatan_update_data($post_data, $id)
    {
        return $this->db->where('id_kecamatan', $id)->update('kecamatan', $post_data);
    }

    public function kecamatan_delete_data($id)
    {
        return $this->db->where('id_kecamatan', $id)->delete('kecamatan');
    }

    public function kecamatan_by_provinsi($id_kota)
    {
        $result = $this->db->where('id_kota', $id_kota)
            ->get('kecamatan')
            ->result();
        return $result;
    }

    public function kecamatan_by_kota($id_kota)
    {
        $result = $this->db->where('id_kota', $id_kota)
            ->order_by('id_kecamatan', 'asc')
            ->get('kecamatan')
            ->result();
        return $result;
    }
}
