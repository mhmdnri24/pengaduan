<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_m extends CI_Model
{

    public function user_get_all()
    {
        $result = $this->db->join('level', 'level.id_level=user.id_level', 'left')
            ->join('tbInstansi', 'tbInstansi.id_instansi=user.id_instansi', 'left')
            ->join('tbUnitKerja', 'tbUnitKerja.id_unitkerja=user.id_unitkerja', 'left')
            ->get('user')
            ->result();
        return $result;
    }

    public function user_cek_login($username)
    {
        $result = $this->db->where('username', $username)
            // ->where('password', $password)
            ->get('user')
            ->row();
        return $result;
    }

    public function user_by_id($id_user)
    {
        $result = $this->db->join('level', 'level.id_level=user.id_level', 'left')
            ->join('tbInstansi', 'tbInstansi.id_instansi=user.id_instansi', 'left')
            ->join('tbUnitKerja', 'tbUnitKerja.id_unitkerja=user.id_unitkerja', 'left')
            ->where('user.id_user', $id_user)
            ->get('user')
            ->row();
        return $result;
    }

    public function user_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('user');
    }

    public function user_update_data($post_data, $id)
    {
        return $this->db->where('id_user', $id)->update('user', $post_data);
    }

    public function user_delete_data($id)
    {
        return $this->db->where('id_user', $id)->delete('user');
    }
    public function get_user_by_username($username)
    {
        $this->db->where('username', $username);
        return $this->db->get('user')->row();
    }

    public function get_instansi_options()
    {
        $this->db->select('id_instansi, instansi_nama');
        $this->db->from('tbInstansi');
        $this->db->where('instansi_nama IS NOT NULL');
        $this->db->where('instansi_nama !=', '');
        $this->db->order_by('instansi_nama', 'ASC');
        return $this->db->get()->result();
    }

    public function get_unitkerja_options($instansi_id = null)
    {
        $this->db->select('id_unitkerja, unitkerja');
        $this->db->from('tbUnitKerja');
        if ($instansi_id) {
            $this->db->where('id_tb_instansi', $instansi_id);
        }
        $this->db->order_by('unitkerja', 'ASC');
        return $this->db->get()->result();
    }
}
