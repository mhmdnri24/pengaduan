<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_instansi_m extends CI_Model
{
    public function instansi_get_all()
    {
        return $this->db->get('tbInstansi')->result();
    }
    
    public function instansi_by_id($id)
    {
        return $this->db->where('id_instansi', $id)->get('tbInstansi')->row();
    }
    
    public function instansi_by_id_array($id)
    {
        return $this->db->where('id_instansi', $id)->get('tbInstansi')->result();
    }
    
    public function instansi_insert_data($post_data)
    {
        return $this->db->insert('tbInstansi', $post_data);
    }
    
    public function instansi_update_data($post_data, $id)
    {
        return $this->db->where('id_instansi', $id)->update('tbInstansi', $post_data);
    }
    
    public function instansi_delete_data($id)
    {
        return $this->db->where('id_instansi', $id)->delete('tbInstansi');
    }
}
