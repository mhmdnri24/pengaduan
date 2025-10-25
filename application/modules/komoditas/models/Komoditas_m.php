<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Komoditas_m extends CI_Model
{
    private $table = 'master_komoditas';

    public function komoditas_get_all()
    {
        return $this->db->order_by('komoditas_parent_id', 'asc')
                       ->order_by('komoditas_urutan', 'asc')
                       ->get($this->table)->result();
    }
    
    public function komoditas_get_active()
    {
        return $this->db->where('komoditas_status', 1)
                       ->order_by('komoditas_parent_id', 'asc')
                       ->order_by('komoditas_urutan', 'asc')
                       ->get($this->table)->result();
    }
    
    public function komoditas_get_parent()
    {
        return $this->db->where('komoditas_parent_id', 0)
                       ->where('komoditas_status', 1)
                       ->order_by('komoditas_urutan', 'asc')
                       ->get($this->table)->result();
    }
    
    public function komoditas_get_children($parent_id)
    {
        return $this->db->where('komoditas_parent_id', $parent_id)
                       ->order_by('komoditas_urutan', 'asc')
                       ->get($this->table)->result();
    }
    
    public function komoditas_by_id($id)
    {
        return $this->db->where('komoditas_id', $id)->get($this->table)->row();
    }
    
    public function komoditas_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function komoditas_update_data($post_data, $id)
    {
        $this->db->where('komoditas_id', $id);
        return $this->db->update($this->table, $post_data);
    }
    
    public function komoditas_delete_data($id)
    {
        $this->db->where('komoditas_id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Get all komoditas with structured parent-child relationship
     */
    public function get_all_komoditas_structured()
    {
        $this->db->order_by('komoditas_parent_id', 'asc');
        $this->db->order_by('komoditas_urutan', 'asc');
        $all_komoditas = $this->db->get($this->table)->result();
        
        $structured_komoditas = [];
        $komoditas_map = [];

        foreach ($all_komoditas as $komoditas) {
            $komoditas->sub_komoditas = [];
            $komoditas_map[$komoditas->komoditas_id] = $komoditas;
        }

        foreach ($all_komoditas as $komoditas) {
            if ($komoditas->komoditas_parent_id != 0 && isset($komoditas_map[$komoditas->komoditas_parent_id])) {
                $komoditas_map[$komoditas->komoditas_parent_id]->sub_komoditas[] = $komoditas;
            }
        }

        $final_structure = [];
        foreach ($komoditas_map as $komoditas) {
            if ($komoditas->komoditas_parent_id == 0) {
                $final_structure[] = $komoditas;
            }
        }
        
        return $final_structure;
    }

    /**
     * Get komoditas tree structure for select options
     */
    public function get_komoditas_tree()
    {
        $parents = $this->komoditas_get_parent();
        $tree = [];
        
        foreach ($parents as $parent) {
            $parent->children = $this->komoditas_get_children($parent->komoditas_id);
            $tree[] = $parent;
        }
        
        return $tree;
    }

    /**
     * Get komoditas with parent info
     */
    public function komoditas_with_parent($id)
    {
        $this->db->select('mk.*, mkp.komoditas_nama as parent_nama');
        $this->db->from($this->table . ' mk');
        $this->db->join($this->table . ' mkp', 'mk.komoditas_parent_id = mkp.komoditas_id', 'left');
        $this->db->where('mk.komoditas_id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Check if komoditas has children
     */
    public function has_children($id)
    {
        $count = $this->db->where('komoditas_parent_id', $id)->count_all_results($this->table);
        return $count > 0;
    }

    /**
     * Get next order number for parent
     */
    public function get_next_order($parent_id = 0)
    {
        $this->db->select_max('komoditas_urutan');
        $this->db->where('komoditas_parent_id', $parent_id);
        $result = $this->db->get($this->table)->row();
        
        return ($result->komoditas_urutan ?? 0) + 1;
    }

    /**
     * Update order for drag & drop
     */
    public function update_order($id, $new_order, $parent_id = null)
    {
        $data = ['komoditas_urutan' => $new_order];
        
        if ($parent_id !== null) {
            $data['komoditas_parent_id'] = $parent_id;
        }
        
        $this->db->where('komoditas_id', $id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Check for duplicate name in same parent
     */
    public function check_duplicate_name($nama, $parent_id, $exclude_id = null)
    {
        $this->db->where('komoditas_nama', $nama);
        $this->db->where('komoditas_parent_id', $parent_id);
        
        if ($exclude_id) {
            $this->db->where('komoditas_id !=', $exclude_id);
        }
        
        $count = $this->db->count_all_results($this->table);
        return $count > 0;
    }

    /**
     * Get statistics
     */
    public function get_statistics()
    {
        $stats = [];
        
        // Total komoditas
        $stats['total'] = $this->db->count_all_results($this->table);
        
        // Total kategori (parent)
        $stats['kategori'] = $this->db->where('komoditas_parent_id', 0)->count_all_results($this->table);
        
        // Total jenis (child)
        $stats['jenis'] = $this->db->where('komoditas_parent_id >', 0)->count_all_results($this->table);
        
        // Total aktif
        $stats['aktif'] = $this->db->where('komoditas_status', 1)->count_all_results($this->table);
        
        // Total non-aktif
        $stats['nonaktif'] = $this->db->where('komoditas_status', 0)->count_all_results($this->table);
        
        return $stats;
    }

    /**
     * Get komoditas for DataTables with server-side processing
     */
    public function get_datatables($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $select, $join, $where, $order);
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $select, $join, $where, $order);
        $query = $this->db->get();
        return $query->num_rows();
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Count active komoditas for frontend
     */
    public function count_active_komoditas()
    {
        return $this->db->where('komoditas_status', 1)->count_all_results($this->table);
    }

    public function count_all($table, $where = null)
    {
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($table);
    }

    private function _get_datatables_query($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->db->select($select);
        $this->db->from($table);

        if ($join) {
            foreach ($join as $j) {
                $this->db->join($j[0], $j[1], isset($j[2]) ? $j[2] : '');
            }
        }

        if ($where) {
            $this->db->where($where);
        }

        $i = 0;
        foreach ($column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($order)) {
            $order_key = array_keys($order)[0];
            $this->db->order_by($order_key, $order[$order_key]);
        }
    }
}
