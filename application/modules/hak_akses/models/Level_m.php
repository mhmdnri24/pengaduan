<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Level_m extends CI_Model {
    
    public function level_get_all()
    {
        $result = $this->db->get('level')
                            ->result();
        return $result;
    }
    
    public function level_by_id($id_level)
    {
        $result = $this->db->where('id_level', $id_level)
                            ->get('level')
                            ->row();
        return $result;
    }
    
    public function level_insert_data($post_data)
    {
        return $this->db->set($post_data)->insert('level');
    }
    
    public function level_update_data($post_data, $id)
    {
        return $this->db->where('id_level', $id)->update('level', $post_data);
    }
    
    public function level_delete_data($id)
    {
        return $this->db->where('id_level', $id)->delete('level');
    }
    
}