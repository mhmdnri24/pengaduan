<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_dokumen_m extends CI_Model
{
    public function dokumen_get_all()
    {
        return $this->db->get('master_dokumen')->result();
    }
    
    public function dokumen_by_id($id)
    {
        return $this->db->where('dokumen_id', $id)->get('master_dokumen')->row();
    }
    
    public function dokumen_by_id_array($id)
    {
        return $this->db->where('dokumen_id', $id)->get('master_dokumen')->result();
    }
    
    public function dokumen_insert_data($post_data)
    {
        return $this->db->insert('master_dokumen', $post_data);
    }
    
    public function dokumen_update_data($post_data, $id)
    {
        return $this->db->where('dokumen_id', $id)->update('master_dokumen', $post_data);
    }
    
    public function dokumen_delete_data($id)
    {
        return $this->db->where('dokumen_id', $id)->delete('master_dokumen');
    }
    
    public function dokumen_by_kelompok($kelompok_id)
    {
        return $this->db->where('kelompok_wisuda_id', $kelompok_id)->get('master_dokumen')->result();
    }
    
    public function dokumen_wajib()
    {
        return $this->db->where('dokumen_wajib', 1)->get('master_dokumen')->result();
    }
    
    public function dokumen_opsional()
    {
        return $this->db->where('dokumen_wajib', 0)->get('master_dokumen')->result();
    }

    public function dokumen_with_kelompok()
    {
        return $this->db->select('md.*, kw.nama_kelompok, kw.kode_kelompok')
                        ->from('master_dokumen md')
                        ->join('kelompok_wisuda kw', 'md.kelompok_wisuda_id = kw.id', 'left')
                        ->order_by('md.dokumen_id', 'asc')
                        ->get()->result();
    }

    public function count_by_kelompok($kelompok_id)
    {
        return $this->db->where('kelompok_wisuda_id', $kelompok_id)->count_all_results('master_dokumen');
    }

    public function count_wajib()
    {
        return $this->db->where('dokumen_wajib', 1)->count_all_results('master_dokumen');
    }

    public function count_opsional()
    {
        return $this->db->where('dokumen_wajib', 0)->count_all_results('master_dokumen');
    }
}
