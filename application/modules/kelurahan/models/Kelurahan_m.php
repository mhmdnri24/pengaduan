<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kelurahan_m extends CI_Model
{

    public function kelurahan_get_all()
    {
        $result = $this->db->order_by('nama_kelurahan', 'asc')
            ->get('kelurahan')
            ->result();
        return $result;
    }

    public function kelurahan_by_id($id_kelurahan)
    {
        $result = $this->db->where('id_kelurahan', $id_kelurahan)
            ->get('kelurahan')
            ->row();
        return $result;
    }

    public function kelurahan_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('kelurahan');
    }

    public function kelurahan_update_data($post_data, $id)
    {
        return $this->db->where('id_kelurahan', $id)->update('kelurahan', $post_data);
    }

    public function kelurahan_delete_data($id)
    {
        return $this->db->where('id_kelurahan', $id)->delete('kelurahan');
    }

    public function kelurahan_by_kecamatan($id_kecamatan)
    {
        $result = $this->db->where('id_kecamatan', $id_kecamatan)
            ->order_by('nama_kelurahan', 'asc')
            ->get('kelurahan')
            ->result();
        return $result;
    }
}
