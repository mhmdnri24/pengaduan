<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Provinsi_m extends CI_Model
{

    public function provinsi_get_all()
    {
        $result = $this->db->order_by('nama_provinsi', 'asc')
            ->get('provinsi')
            ->result();
        return $result;
    }

    public function provinsi_by_id($id_provinsi)
    {
        $result = $this->db->where('id_provinsi', $id_provinsi)
            ->get('provinsi')
            ->row();
        return $result;
    }

    public function provinsi_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('provinsi');
    }

    public function provinsi_update_data($post_data, $id)
    {
        return $this->db->where('id_provinsi', $id)->update('provinsi', $post_data);
    }

    public function provinsi_delete_data($id)
    {
        return $this->db->where('id_provinsi', $id)->delete('provinsi');
    }
}
