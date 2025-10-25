<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ce_model extends CI_Model {
    public function get_opsi($kunci)
    {
        $result = $this->db->where('kunci', $kunci)->get('opsi')->row();
        return $result ? $result->nilai : '';
    }
    
    private function _cek_opsi($kunci)
    {
        $result = $this->db->where('kunci', $kunci)->get('opsi')->num_rows();
        return $result;
    }
    
    public function set_opsi($kunci, $nilai)
    {
        $cek = $this->_cek_opsi($kunci);
        if($cek==0){
            return $this->db->set('kunci', $kunci)
                            ->set('nilai', $nilai)
                            ->insert('opsi');
        }
        else
            return $this->db->where('kunci', $kunci)->update('opsi', array('nilai' => $nilai));
    }
}