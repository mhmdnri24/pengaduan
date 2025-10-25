<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_kategori_m extends CI_Model
{
    private $table = 'master_kategori';

    public function kategori_get_all()
    {
        return $this->db->order_by('id', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_active()
    {
        return $this->db->where('status', 1)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_parent()
    {
        return $this->db->where('parent_id IS NULL', null, false)->where('status', 1)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_children($parent_id)
    {
        return $this->db->where('parent_id', $parent_id)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function kategori_by_kode($kode_kategori)
    {
        return $this->db->where('kode_kategori', $kode_kategori)->get($this->table)->row();
    }
    
    public function kategori_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }
    
    public function kategori_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function kategori_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    public function kategori_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }
    
    public function count_all_kategori()
    {
        return $this->db->count_all($this->table);
    }
    
    public function count_active_kategori()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }
    
    public function count_inactive_kategori()
    {
        return $this->db->where('status', 0)->count_all_results($this->table);
    }
    
    public function count_parent_kategori()
    {
        return $this->db->where('parent_id IS NULL', null, false)->count_all_results($this->table);
    }
    
    public function count_child_kategori()
    {
        return $this->db->where('parent_id IS NOT NULL', null, false)->count_all_results($this->table);
    }
    
    public function count_children($parent_id)
    {
        return $this->db->where('parent_id', $parent_id)->count_all_results($this->table);
    }

    /**
     * Get kategori by level (0=root, 1=level1, 2=level2)
     */
    public function get_kategori_by_level($level = 0)
    {
        if ($level == 0) {
            // Root categories (parent_id IS NULL)
            return $this->db->where('parent_id IS NULL', null, false)
                          ->where('status', 1)
                          ->order_by('nama_kategori', 'asc')
                          ->get($this->table)->result();
        } else {
            // Get categories by checking the depth
            $sql = "SELECT * FROM {$this->table} WHERE status = 1";
            if ($level == 1) {
                $sql .= " AND parent_id IS NOT NULL AND parent_id IN (SELECT id FROM {$this->table} WHERE parent_id IS NULL)";
            } elseif ($level == 2) {
                $sql .= " AND parent_id IS NOT NULL AND parent_id IN (SELECT id FROM {$this->table} WHERE parent_id IS NOT NULL AND parent_id IN (SELECT id FROM {$this->table} WHERE parent_id IS NULL))";
            }
            $sql .= " ORDER BY nama_kategori ASC";
            return $this->db->query($sql)->result();
        }
    }

    /**
     * Get kategori level (0=root, 1=level1, 2=level2, etc)
     */
    public function get_kategori_level($id)
    {
        $kategori = $this->kategori_by_id($id);
        if (!$kategori) return -1;
        
        if ($kategori->parent_id === null) {
            return 0; // Root level
        }
        
        $parent = $this->kategori_by_id($kategori->parent_id);
        if (!$parent) return 1;
        
        if ($parent->parent_id === null) {
            return 1; // Level 1 (child of root)
        } else {
            return 2; // Level 2 (grandchild)
        }
    }

    /**
     * Get full kategori tree with 3 levels
     */
    public function get_full_kategori_tree()
    {
        $tree = [];
        
        // Get root categories
        $roots = $this->kategori_get_parent();
        
        foreach ($roots as $root) {
            $root->level = 0;
            $root->children = [];
            
            // Get level 1 children
            $level1_children = $this->kategori_get_children($root->id);
            foreach ($level1_children as $level1) {
                $level1->level = 1;
                $level1->children = [];
                
                // Get level 2 children (grandchildren)
                $level2_children = $this->kategori_get_children($level1->id);
                foreach ($level2_children as $level2) {
                    $level2->level = 2;
                    $level1->children[] = $level2;
                }
                
                $root->children[] = $level1;
            }
            
            $tree[] = $root;
        }
        
        return $tree;
    }

    /**
     * Get kategori tree structure
     */
    public function get_kategori_tree()
    {
        $parents = $this->kategori_get_parent();
        $tree = [];
        
        foreach ($parents as $parent) {
            $parent->children = $this->kategori_get_children($parent->id);
            $tree[] = $parent;
        }
        
        return $tree;
    }

    /**
     * Get kategori with parent info
     */
    public function kategori_with_parent($id)
    {
        $this->db->select('mk.*, mkp.nama_kategori as parent_nama');
        $this->db->from($this->table . ' mk');
        $this->db->join($this->table . ' mkp', 'mk.parent_id = mkp.id', 'left');
        $this->db->where('mk.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Get statistics
     */
    public function get_statistics()
    {
        $stats = [];
        
        // Total kategori
        $stats['total'] = $this->count_all_kategori();
        
        // Kategori aktif
        $stats['aktif'] = $this->count_active_kategori();
        
        // Kategori nonaktif
        $stats['nonaktif'] = $this->count_inactive_kategori();
        
        // Parent kategori
        $stats['parent'] = $this->count_parent_kategori();
        
        // Child kategori
        $stats['child'] = $this->count_child_kategori();
        
        return $stats;
    }

    /**
     * Check if kategori can be deleted (no children)
     */
    public function can_delete($id)
    {
        $children = $this->kategori_get_children($id);
        return empty($children);
    }

    /**
     * Get kategori hierarchy path
     */
    public function get_kategori_path($id)
    {
        $path = [];
        $kategori = $this->kategori_by_id($id);
        
        while ($kategori) {
            array_unshift($path, $kategori);
            if ($kategori->parent_id) {
                $kategori = $this->kategori_by_id($kategori->parent_id);
            } else {
                break;
            }
        }
        
        return $path;
    }

    /**
     * Get all descendants of a kategori
     */
    public function get_descendants($parent_id)
    {
        $descendants = [];
        $children = $this->kategori_get_children($parent_id);
        
        foreach ($children as $child) {
            $descendants[] = $child;
            $grandchildren = $this->get_descendants($child->id);
            $descendants = array_merge($descendants, $grandchildren);
        }
        
        return $descendants;
    }
}
