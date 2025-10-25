<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_unitkerja_m extends CI_Model
{
    public function unitkerja_get_all()
    {
        // Query untuk mendapatkan data dengan urutan hierarchy
        $this->db->select('
            tbUnitKerja.*,
            tbInstansi.instansi_nama,
            COALESCE(parent.unitkerja, "") as parent_name
        ');
        $this->db->from('tbUnitKerja');
        $this->db->join('tbInstansi', 'tbUnitKerja.id_tb_instansi = tbInstansi.id_instansi', 'left');
        $this->db->join('tbUnitKerja as parent', 'tbUnitKerja.induk_unit = parent.id_unitkerja', 'left');
        $this->db->order_by('tbUnitKerja.id_tb_instansi', 'ASC');
        $this->db->order_by('tbUnitKerja.induk_unit', 'ASC');
        $this->db->order_by('parent_name', 'ASC');
        $this->db->order_by('tbUnitKerja.unitkerja', 'ASC');

        $result = $this->db->get()->result();

        // Urutkan ulang berdasarkan hierarchy yang benar
        return $this->_sort_hierarchy($result);
    }

    private function _sort_hierarchy($data)
    {
        $sorted = [];
        $processed = [];
        $parent_map = [];

        // Buat mapping parent ke children
        foreach ($data as $item) {
            if (!isset($parent_map[$item->induk_unit])) {
                $parent_map[$item->induk_unit] = [];
            }
            $parent_map[$item->induk_unit][] = $item;
        }

        // Fungsi rekursif untuk mendapatkan hierarchy
        $get_hierarchy = function($parent_id = 0) use (&$parent_map, &$processed, &$get_hierarchy) {
            $result = [];

            if (isset($parent_map[$parent_id])) {
                // Urutkan children berdasarkan nama unit kerja
                usort($parent_map[$parent_id], function($a, $b) {
                    return strcmp($a->unitkerja, $b->unitkerja);
                });

                foreach ($parent_map[$parent_id] as $item) {
                    if (!in_array($item->id_unitkerja, $processed)) {
                        $processed[] = $item->id_unitkerja;
                        $result[] = $item;
                        // Rekursif untuk children
                        $children = $get_hierarchy($item->id_unitkerja);
                        $result = array_merge($result, $children);
                    }
                }
            }

            return $result;
        };

        // Kelompokkan berdasarkan instansi dan urutkan
        $by_instansi = [];
        foreach ($data as $item) {
            $by_instansi[$item->id_tb_instansi][] = $item;
        }

        // Urutkan instansi berdasarkan nama
        ksort($by_instansi);

        // Untuk setiap instansi, buat hierarchy
        foreach ($by_instansi as $instansi_data) {
            $instansi_sorted = $get_hierarchy();
            // Filter hanya untuk instansi ini
            $instansi_filtered = array_filter($instansi_sorted, function($item) use ($instansi_data) {
                foreach ($instansi_data as $inst_item) {
                    if ($item->id_unitkerja == $inst_item->id_unitkerja) {
                        return true;
                    }
                }
                return false;
            });

            $sorted = array_merge($sorted, $instansi_filtered);
        }

        return $sorted;
    }
    
    public function unitkerja_by_id($id)
    {
        $this->db->select('tbUnitKerja.*, tbInstansi.instansi_nama');
        $this->db->from('tbUnitKerja');
        $this->db->join('tbInstansi', 'tbUnitKerja.id_tb_instansi = tbInstansi.id_instansi', 'left');
        $this->db->where('tbUnitKerja.id_unitkerja', $id);
        return $this->db->get()->row();
    }
    
    public function unitkerja_by_id_array($id)
    {
        return $this->db->where('id_unitkerja', $id)->get('tbUnitKerja')->result();
    }
    
    public function unitkerja_insert_data($post_data)
    {
        return $this->db->insert('tbUnitKerja', $post_data);
    }
    
    public function unitkerja_update_data($post_data, $id)
    {
        return $this->db->where('id_unitkerja', $id)->update('tbUnitKerja', $post_data);
    }
    
    public function unitkerja_delete_data($id)
    {
        return $this->db->where('id_unitkerja', $id)->delete('tbUnitKerja');
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
    
    public function get_parent_options($instansi_id = null)
    {
        $this->db->select('id_unitkerja, unitkerja');
        $this->db->from('tbUnitKerja');
        if ($instansi_id) {
            $this->db->where('id_tb_instansi', $instansi_id);
        }
        $this->db->order_by('unitkerja', 'ASC');
        return $this->db->get()->result();
    }
    
    public function check_has_children($id)
    {
        $this->db->where('induk_unit', $id);
        $count = $this->db->count_all_results('tbUnitKerja');
        return $count > 0;
    }
}
