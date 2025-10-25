<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jenis_layanan_m extends CI_Model
{
    public function layanan_get_all($unitkerja_id = null)
    {
        $this->db->select('lj.*, uk.unitkerja as unitkerja_nama');
        $this->db->from('layanan_jenis lj');
        $this->db->join('tbUnitKerja uk', 'lj.unitkerja_id = uk.id_unitkerja', 'left');
        if ($unitkerja_id) {
            $this->db->where('lj.unitkerja_id', $unitkerja_id);
        }
        return $this->db->get()->result();
    }
    
    public function layanan_by_id($id)
    {
        return $this->db->where('layanan_id', $id)->get('layanan_jenis')->row();
    }
    
    public function layanan_by_id_array($id)
    {
        return $this->db->where('layanan_id', $id)->get('layanan_jenis')->result();
    }
    
    public function layanan_insert_data($post_data)
    {
        return $this->db->insert('layanan_jenis', $post_data);
    }
    
    public function layanan_update_data($post_data, $id)
    {
        return $this->db->where('layanan_id', $id)->update('layanan_jenis', $post_data);
    }
    
    public function layanan_delete_data($id)
    {
        return $this->db->where('layanan_id', $id)->delete('layanan_jenis');
    }
    
    public function layanan_by_kategori($kategori)
    {
        return $this->db->where('layanan_kategori', $kategori)->get('layanan_jenis')->result();
    }
    
    public function layanan_wajib()
    {
        return $this->db->where('layanan_wajib', 1)->get('layanan_jenis')->result();
    }
    
    public function layanan_opsional()
    {
        return $this->db->where('layanan_wajib', 0)->get('layanan_jenis')->result();
    }

    public function layanan_with_detail($unitkerja_id = null)
    {
        $this->db->select('lj.*, uk.unitkerja as unitkerja_nama, COUNT(ld.layanan_detail_id) as total_detail');
        $this->db->from('layanan_jenis lj');
        $this->db->join('tbUnitKerja uk', 'lj.unitkerja_id = uk.id_unitkerja', 'left');
        $this->db->join('layanan_detail ld', 'lj.layanan_id = ld.layanan_id', 'left');
        if ($unitkerja_id) {
            $this->db->where('lj.unitkerja_id', $unitkerja_id);
        }
        $this->db->group_by('lj.layanan_id');
        $this->db->order_by('lj.layanan_id', 'asc');
        return $this->db->get()->result();
    }

    public function count_by_kategori($kategori, $unitkerja_id = null)
    {
        $this->db->where('layanan_kategori', $kategori);
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->count_all_results('layanan_jenis');
    }

    public function count_wajib($unitkerja_id = null)
    {
        $this->db->where('layanan_wajib', 1);
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->count_all_results('layanan_jenis');
    }

    public function count_opsional($unitkerja_id = null)
    {
        $this->db->where('layanan_wajib', 0);
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->count_all_results('layanan_jenis');
    }

    // Methods untuk layanan_detail
    public function detail_get_all($unitkerja_id = null)
    {
        $this->db->join('layanan_jenis lj', 'layanan_detail.layanan_id = lj.layanan_id', 'left');
        if ($unitkerja_id) {
            $this->db->where('lj.unitkerja_id', $unitkerja_id);
        }
        return $this->db->get('layanan_detail')->result();
    }
    
    public function detail_by_id($id)
    {
        return $this->db->where('layanan_detail_id', $id)->get('layanan_detail')->row();
    }
    
    public function detail_by_layanan_id($layanan_id)
    {
        return $this->db->where('layanan_id', $layanan_id)->get('layanan_detail')->result();
    }
    
    public function detail_insert_data($post_data)
    {
        return $this->db->insert('layanan_detail', $post_data);
    }
    
    public function detail_update_data($post_data, $id)
    {
        return $this->db->where('layanan_detail_id', $id)->update('layanan_detail', $post_data);
    }
    
    public function detail_delete_data($id)
    {
        return $this->db->where('layanan_detail_id', $id)->delete('layanan_detail');
    }

    public function detail_wajib_by_layanan($layanan_id)
    {
        return $this->db->where('layanan_id', $layanan_id)
                        ->where('layanan_detail_wajib', 1)
                        ->get('layanan_detail')->result();
    }

    public function detail_opsional_by_layanan($layanan_id)
    {
        return $this->db->where('layanan_id', $layanan_id)
                        ->where('layanan_detail_wajib', 0)
                        ->get('layanan_detail')->result();
    }
}
