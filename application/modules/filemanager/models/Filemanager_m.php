<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Filemanager_m extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_files_by_directory($directory_name)
    {
        $this->db->select('fm.*, u.nama as user_nama, a.atlet_nama');
        $this->db->from('filemanager fm');
        $this->db->join('user u', 'fm.uploaded_by = u.id_user', 'left');
        $this->db->join('atlet a', 'fm.atlet_id = a.atlet_id', 'left');
        $this->db->where('fm.directory_name', $directory_name);
        $this->db->order_by('fm.uploaded_at', 'DESC');
        return $this->db->get()->result();
    }

    public function insert_file($data)
    {
        return $this->db->insert('filemanager', $data);
    }

    public function get_file_by_id($id)
    {
        return $this->db->get_where('filemanager', ['id' => $id])->row();
    }

    public function delete_file($id)
    {
        return $this->db->delete('filemanager', ['id' => $id]);
    }
}