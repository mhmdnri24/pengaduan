<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_unitkerja extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        $this->load->model('master_unitkerja/master_unitkerja_m');
        ce_active_menu('admin.master_unitkerja.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_unitkerja.view');
        $data['header'] = 'Master <small>Unit Kerja</small>';
        $data['halaman'] = 'master_unitkerja';
        $data['javascript'] = array(
            'master_unitkerja/js_master_unitkerja' => null
        );
        
        $data['instansi'] = $this->master_unitkerja_m->get_instansi_options();
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_unitkerja.view');

        $dataConfig = [
            'table' => 'tbUnitKerja',
            'select' => 'tbUnitKerja.id_unitkerja, tbUnitKerja.unitkerja, tbUnitKerja.induk_unit, tbUnitKerja.id_tb_instansi, tbInstansi.instansi_nama',
            'column_order' => [null, 'tbUnitKerja.unitkerja', 'tbInstansi.instansi_nama', null, null],
            'column_search' => ['tbUnitKerja.unitkerja', 'tbInstansi.instansi_nama'],
            'join' => [
                ['table' => 'tbInstansi', 'on' => 'tbUnitKerja.id_tb_instansi = tbInstansi.id_instansi', 'type' => 'left'],
                ['table' => 'tbUnitKerja as parent', 'on' => 'tbUnitKerja.induk_unit = parent.id_unitkerja', 'type' => 'left']
            ],
            'order' => [
                'tbUnitKerja.id_tb_instansi' => 'asc',
                'tbUnitKerja.induk_unit' => 'asc',
                'tbUnitKerja.unitkerja' => 'asc'
            ]
        ];

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;

            // Tampilkan hierarki dengan indentasi
            $level = $this->_get_unit_level($item->id_unitkerja);
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            if ($level > 0) {
                $prefix .= '└─ ';
            }
            $row[] = $prefix . $item->unitkerja;

            // Get parent unit name manually
            $parent_name = '-';
            if ($item->induk_unit && $item->induk_unit != 0) {
                $parent = $this->db->where('id_unitkerja', $item->induk_unit)->get('tbUnitKerja')->row();
                if ($parent) {
                    $parent_name = $parent->unitkerja;
                }
            }
            $row[] = $parent_name;

            $row[] = $item->instansi_nama ? $item->instansi_nama : '-';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_unitkerja.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id_unitkerja.'" data-unitkerja="'.$item->unitkerja.'" data-instansi="'.$item->id_tb_instansi.'" data-induk="'.$item->induk_unit.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_unitkerja.delete')) {
                $has_children = $this->master_unitkerja_m->check_has_children($item->id_unitkerja);
                if (!$has_children) {
                    $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-hapus" data-id="'.$item->id_unitkerja.'" data-nama="'.$item->unitkerja.'"><i class="fa fa-trash"></i></button>';
                } else {
                    $aksi .= '<button type="button" class="btn btn-sm btn-danger" disabled title="Tidak dapat dihapus karena memiliki sub unit"><i class="fa fa-trash"></i></button>';
                }
            }
            $aksi .= '</div>';
            $row[] = $aksi;

            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data,
            "csrf_hash" => $this->security->get_csrf_hash()
        );

        header('Content-Type: application/json');
        echo json_encode($output);
    }

    public function get_hierarchy_data()
    {
        ce_hak_akses('admin.master_unitkerja.view');

        // Get filter parameters
        $filter_instansi = $this->input->post('filter_instansi');
        $filter_unitkerja = $this->input->post('filter_unitkerja');

        // Get data from model with proper hierarchy sorting
        $all_items = $this->master_unitkerja_m->unitkerja_get_all();

        // Apply filters if provided
        if ($filter_instansi || $filter_unitkerja) {
            $filtered_items = [];

            if ($filter_unitkerja) {
                // Jika filter unit kerja dipilih, ambil unit kerja tersebut dan semua child-nya
                $selected_unit_ids = $this->_get_unit_and_children($filter_unitkerja);

                foreach ($all_items as $item) {
                    if (in_array($item->id_unitkerja, $selected_unit_ids)) {
                        // Filter by instansi jika juga dipilih
                        if (!$filter_instansi || $item->instansi_nama == $filter_instansi) {
                            $filtered_items[] = $item;
                        }
                    }
                }
            } else {
                // Jika hanya filter instansi
                foreach ($all_items as $item) {
                    if ($item->instansi_nama == $filter_instansi) {
                        $filtered_items[] = $item;
                    }
                }
            }

            $items = $filtered_items;
        } else {
            $items = $all_items;
        }

        $data = array();
        $no = 0;
        foreach ($items as $item) {
            $no++;
            $row = array();
            $row[] = $no;

            // Tampilkan hierarki dengan indentasi
            $level = $this->_get_unit_level($item->id_unitkerja);
            $prefix = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            if ($level > 0) {
                $prefix .= '└─ ';
            }
            $row[] = $prefix . $item->unitkerja;

            // Get parent unit name
            $parent_name = '-';
            if ($item->induk_unit && $item->induk_unit != 0) {
                $parent = $this->db->where('id_unitkerja', $item->induk_unit)->get('tbUnitKerja')->row();
                if ($parent) {
                    $parent_name = $parent->unitkerja;
                }
            }
            $row[] = $parent_name;

            $row[] = $item->instansi_nama ? $item->instansi_nama : '-';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_unitkerja.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id_unitkerja.'" data-unitkerja="'.$item->unitkerja.'" data-instansi="'.$item->id_tb_instansi.'" data-induk="'.$item->induk_unit.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_unitkerja.delete')) {
                $has_children = $this->master_unitkerja_m->check_has_children($item->id_unitkerja);
                if (!$has_children) {
                    $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-hapus" data-id="'.$item->id_unitkerja.'" data-nama="'.$item->unitkerja.'"><i class="fa fa-trash"></i></button>';
                } else {
                    $aksi .= '<button type="button" class="btn btn-sm btn-danger" disabled title="Tidak dapat dihapus karena memiliki sub unit"><i class="fa fa-trash"></i></button>';
                }
            }
            $aksi .= '</div>';
            $row[] = $aksi;

            $data[] = $row;
        }

        $response = [
            'status' => true,
            'data' => $data,
            'csrf_hash' => $this->security->get_csrf_hash()
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function _get_unit_level($unit_id, $level = 0)
    {
        $unit = $this->db->where('id_unitkerja', $unit_id)->get('tbUnitKerja')->row();
        if ($unit && $unit->induk_unit != 0) {
            return $this->_get_unit_level($unit->induk_unit, $level + 1);
        }
        return $level;
    }

    public function get_parent_options()
    {
        $instansi_id = $this->input->get('instansi_id');
        $current_id = $this->input->get('current_id');

        $options = $this->master_unitkerja_m->get_parent_options($instansi_id);

        // Filter out current unit and its children to prevent circular reference
        if ($current_id) {
            $options = array_filter($options, function($option) use ($current_id) {
                return $option->id_unitkerja != $current_id && !$this->_is_child_of($option->id_unitkerja, $current_id);
            });
        }

        $response = [
            'status' => true,
            'data' => $options
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_unitkerja_options()
    {
        $instansi_filter = $this->input->get('instansi_filter');

        $this->db->select('id_unitkerja, unitkerja');
        $this->db->from('tbUnitKerja');

        if ($instansi_filter) {
            // Filter unit kerja berdasarkan nama instansi
            $this->db->join('tbInstansi', 'tbUnitKerja.id_tb_instansi = tbInstansi.id_instansi', 'left');
            $this->db->where('tbInstansi.instansi_nama', $instansi_filter);
        }

        $this->db->order_by('unitkerja', 'ASC');
        $options = $this->db->get()->result();

        $response = [
            'status' => true,
            'data' => $options
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function _is_child_of($potential_child, $parent_id)
    {
        $unit = $this->db->where('id_unitkerja', $potential_child)->get('tbUnitKerja')->row();
        if ($unit && $unit->induk_unit != 0) {
            if ($unit->induk_unit == $parent_id) {
                return true;
            }
            return $this->_is_child_of($unit->induk_unit, $parent_id);
        }
        return false;
    }

    private function _get_unit_and_children($unit_id)
    {
        $unit_ids = [$unit_id];

        // Function to get all children recursively
        $get_children = function($parent_id) use (&$get_children, &$unit_ids) {
            $this->db->where('induk_unit', $parent_id);
            $children = $this->db->get('tbUnitKerja')->result();

            foreach ($children as $child) {
                $unit_ids[] = $child->id_unitkerja;
                $get_children($child->id_unitkerja); // Recursive call
            }
        };

        $get_children($unit_id);
        return $unit_ids;
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_unitkerja.add');
        } else {
            ce_hak_akses('admin.master_unitkerja.update');
        }

        $unitkerja = $this->input->post('unitkerja');
        $id_tb_instansi = $this->input->post('id_tb_instansi');
        $induk_unit = $this->input->post('induk_unit');

        if (empty($unitkerja)) {
            $response = [
                'status' => false,
                'message' => 'Nama Unit Kerja tidak boleh kosong'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        if (empty($id_tb_instansi)) {
            $response = [
                'status' => false,
                'message' => 'Instansi harus dipilih'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Validasi circular reference untuk edit
        if (!empty($id) && !empty($induk_unit) && $induk_unit == $id) {
            $response = [
                'status' => false,
                'message' => 'Unit kerja tidak boleh menjadi induk dari dirinya sendiri'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $data = [
            'unitkerja' => $unitkerja,
            'id_tb_instansi' => $id_tb_instansi,
            'induk_unit' => empty($induk_unit) ? 0 : $induk_unit
        ];

        if (empty($id)) {
            $result = $this->master_unitkerja_m->unitkerja_insert_data($data);
            $message = 'Unit Kerja berhasil ditambahkan';
        } else {
            $result = $this->master_unitkerja_m->unitkerja_update_data($data, $id);
            $message = 'Unit Kerja berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data Unit Kerja'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.master_unitkerja.delete');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID Unit Kerja tidak valid'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Cek apakah unit kerja memiliki sub unit
        $has_children = $this->master_unitkerja_m->check_has_children($id);
        if ($has_children) {
            $response = [
                'status' => false,
                'message' => 'Unit Kerja tidak dapat dihapus karena memiliki sub unit'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $result = $this->master_unitkerja_m->unitkerja_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Unit Kerja berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data Unit Kerja'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
