<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kota_m extends CI_Model
{

    public function kota_get_all()
    {
        $result = $this->db->order_by('nama_kota', 'asc')
            ->get('kota')
            ->result();
        return $result;
    }

    public function kota_by_id($id_kota)
    {
        $result = $this->db->where('id_kota', $id_kota)
            ->get('kota')
            ->row();
        return $result;
    }

    public function kota_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('kota');
    }

    public function kota_update_data($post_data, $id)
    {
        return $this->db->where('id_kota', $id)->update('kota', $post_data);
    }

    public function kota_delete_data($id)
    {
        return $this->db->where('id_kota', $id)->delete('kota');
    }

    public function kota_by_provinsi($id_provinsi)
    {
        $result = $this->db->where('id_provinsi', $id_provinsi)
            ->order_by('nama_kota', 'asc')
            ->get('kota')
            ->result();
        return $result;
    }
}
