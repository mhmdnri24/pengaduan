<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu_m extends CI_Model
{
    public function menu_get_all()
    {
        return $this->db->order_by('menu_order', 'asc')->get('master_menu')->result();
    }
    
    public function menu_get_parent()
    {
        return $this->db->where('menu_parent_id', 0)->order_by('menu_order', 'asc')->get('master_menu')->result();
    }
    
    public function menu_get_children($parent_id)
    {
        return $this->db->where('menu_parent_id', $parent_id)->order_by('menu_order', 'asc')->get('master_menu')->result();
    }
    
    public function menu_by_id($id)
    {
        return $this->db->where('menu_id', $id)->get('master_menu')->row();
    }
    
    public function menu_insert_data($post_data)
    {
        return $this->db->insert('master_menu', $post_data);
    }
    
    public function menu_update_data($post_data, $id)
    {
        return $this->db->where('menu_id', $id)->update('master_menu', $post_data);
    }
    
    public function menu_delete_data($id)
    {
        return $this->db->where('menu_id', $id)->delete('master_menu');
    }

    public function get_all_menus_structured()
    {
        $this->db->order_by('menu_parent_id', 'asc');
        $this->db->order_by('menu_order', 'asc');
        $all_menus = $this->db->get('master_menu')->result();
        
        $structured_menu = [];
        $menu_map = [];

        foreach ($all_menus as $menu) {
            $menu->sub_menu = [];
            $menu_map[$menu->menu_id] = $menu;
        }

        foreach ($all_menus as $menu) {
            if ($menu->menu_parent_id != 0 && isset($menu_map[$menu->menu_parent_id])) {
                $menu_map[$menu->menu_parent_id]->sub_menu[] = $menu;
            }
        }

        $final_structure = [];
        foreach ($menu_map as $menu) {
            if ($menu->menu_parent_id == 0) {
                $final_structure[] = $menu;
            }
        }
        
        return $final_structure;
    }
    
    public function get_dynamic_menu()
    {
        $this->db->where('menu_status', 1);
        $this->db->order_by('menu_parent_id', 'asc');
        $this->db->order_by('menu_order', 'asc');
        $all_menus = $this->db->get('master_menu')->result();

        if (empty($all_menus)) {
            return [];
        }

        $menu_map = [];
        foreach ($all_menus as $menu) {
            $menu_map[$menu->menu_id] = (array) $menu;
            $menu_map[$menu->menu_id]['sub_menu'] = [];
        }

        $structured_menu = [];
        foreach ($menu_map as $menu_id => &$menu) {
            if ($menu['menu_parent_id'] != 0 && isset($menu_map[$menu['menu_parent_id']])) {
                $menu_map[$menu['menu_parent_id']]['sub_menu'][] = &$menu;
            } else {
                $structured_menu[] = &$menu;
            }
        }
        
        // Helper function to re-format for ce_nav_menu helper
        function format_menu_for_helper($menus) {
            $result = [];
            foreach ($menus as $menu) {
                $menu_item = [
                    'id' => $menu['menu_id'],
                    'text' => $menu['menu_label'],
                    'url' => $menu['menu_url'],
                    'icon' => $menu['menu_icon'] ?: 'fa fa-circle-o',
                    'hak_akses' => $menu['menu_access_code'],
                ];

                if (!empty($menu['sub_menu'])) {
                    $menu_item['child'] = format_menu_for_helper($menu['sub_menu']);
                }
                
                $result[] = $menu_item;
            }
            return $result;
        }

        return format_menu_for_helper($structured_menu);
    }
}