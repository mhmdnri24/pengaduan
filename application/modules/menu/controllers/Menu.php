<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Menu extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.menu.view');
    }

    public function index()
    {
        ce_hak_akses('admin.menu.view');
        $data['header'] = 'Pengaturan <small>Menu</small>';
        $data['halaman'] = 'menu/menu';
        $data['javascript'] = array(
            'menu/js_menu' => null
        );
        
        $data['menus'] = $this->menu_m->get_all_menus_structured();
        $data['parent_menus'] = $this->menu_m->menu_get_parent();
        
        $this->load->view('template', $data);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.menu.add');
        } else {
            ce_hak_akses('admin.menu.update');
        }
        
        $this->form_validation->set_rules('menu_label', 'Label Menu', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $response = ['status' => false, 'message' => validation_errors()];
        } else {
            $data = [
                'menu_label' => $this->input->post('menu_label'),
                'menu_icon' => $this->input->post('menu_icon'),
                'menu_url' => $this->input->post('menu_url'),
                'menu_parent_id' => $this->input->post('menu_parent_id'),
                'menu_order' => $this->input->post('menu_order'),
                'menu_access_code' => $this->input->post('menu_access_code'),
                'menu_status' => $this->input->post('menu_status')
            ];

            if (empty($id)) {
                $result = $this->menu_m->menu_insert_data($data);
                $message = 'Menu berhasil ditambahkan';
            } else {
                $result = $this->menu_m->menu_update_data($data, $id);
                $message = 'Menu berhasil diupdate';
            }

            if ($result) {
                $response = ['status' => true, 'message' => $message];
            } else {
                $response = ['status' => false, 'message' => 'Gagal menyimpan data menu'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function copy()
    {
        ce_hak_akses('admin.menu.add');
        $id = $this->input->post('id');
        
        $menu = $this->menu_m->menu_by_id($id);
        if (!$menu) {
            $response = ['status' => false, 'message' => 'Menu tidak ditemukan'];
        } else {
            $data = [
                'menu_label' => $menu->menu_label . ' (Copy)',
                'menu_icon' => $menu->menu_icon,
                'menu_url' => $menu->menu_url,
                'menu_parent_id' => $menu->menu_parent_id,
                'menu_order' => $menu->menu_order + 1,
                'menu_access_code' => $menu->menu_access_code,
                'menu_status' => $menu->menu_status
            ];
            
            $result = $this->menu_m->menu_insert_data($data);
            if ($result) {
                $response = ['status' => true, 'message' => 'Menu berhasil dicopy'];
            } else {
                $response = ['status' => false, 'message' => 'Gagal copy menu'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.menu.delete');
        $id = $this->input->post('id');
        
        if ($this->menu_m->menu_get_children($id)) {
            $response = ['status' => false, 'message' => 'Menu ini memiliki sub menu. Hapus sub menu terlebih dahulu.'];
        } else {
            $result = $this->menu_m->menu_delete_data($id);
            if ($result) {
                $response = ['status' => true, 'message' => 'Menu berhasil dihapus'];
            } else {
                $response = ['status' => false, 'message' => 'Gagal menghapus data menu'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }
}