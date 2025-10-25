<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Komoditas extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        // Load model explicitly for HMVC
        $this->load->model('komoditas/komoditas_m');
        ce_active_menu('admin.komoditas.view');
    }

    public function index()
    {
        ce_hak_akses('admin.komoditas.view');
        
        $data['header'] = 'Master <small>Komoditas</small>';
        $data['halaman'] = 'komoditas';
        $data['javascript'] = array(
            'komoditas/js_komoditas' => null
        );
        
        // Get structured data for tree view
        $data['komoditas_tree'] = $this->komoditas_m->get_all_komoditas_structured();
        $data['parent_komoditas'] = $this->komoditas_m->komoditas_get_parent();
        $data['statistics'] = $this->komoditas_m->get_statistics();
        
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.komoditas.view');
        
        $table = 'master_komoditas mk';
        $select = 'mk.komoditas_id, mk.komoditas_nama, mk.komoditas_parent_id, mk.komoditas_satuan, mk.komoditas_deskripsi, mk.komoditas_urutan, mk.komoditas_status, mkp.komoditas_nama as parent_nama';
        $column_order = [null, 'mk.komoditas_nama', 'mkp.komoditas_nama', 'mk.komoditas_satuan', 'mk.komoditas_urutan', 'mk.komoditas_status', null];
        $column_search = ['mk.komoditas_nama', 'mkp.komoditas_nama', 'mk.komoditas_satuan'];
        $join = [
            ['master_komoditas mkp', 'mk.komoditas_parent_id = mkp.komoditas_id', 'left']
        ];
        $order = ['mk.komoditas_parent_id' => 'asc', 'mk.komoditas_urutan' => 'asc'];

        $list = $this->komoditas_m->get_datatables($table, $column_order, $column_search, $select, $join, null, $order);
        $data = [];
        $no = $_POST['start'];

        foreach ($list as $item) {
            $no++;
            $row = [];

            // Nomor
            $row[] = $no;

            // Nama Komoditas dengan indentasi untuk child
            $nama_display = $item->komoditas_nama;
            if ($item->komoditas_parent_id != 0) {
                $nama_display = '<span class="text-muted">└─</span> ' . $nama_display;
            } else {
                $nama_display = '<strong>' . $nama_display . '</strong>';
            }
            $row[] = $nama_display;

            // Parent/Kategori
            $row[] = $item->parent_nama ?: '<span class="text-muted">Kategori Utama</span>';

            // Satuan
            $row[] = $item->komoditas_satuan ?: '-';

            // Urutan
            $row[] = $item->komoditas_urutan;

            // Status
            $status_label = $item->komoditas_status == 1 ? 'label-success' : 'label-danger';
            $status_text = $item->komoditas_status == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = '<span class="label ' . $status_label . '">' . $status_text . '</span>';

            // Aksi
            $aksi = '';
            if (ce_cek_hak_akses('admin.komoditas.update')) {
                $aksi .= '<button type="button" class="btn btn-warning btn-xs btn-edit" data-id="' . $item->komoditas_id . '" title="Edit">
                            <i class="fa fa-edit"></i>
                          </button> ';
            }
            
            if (ce_cek_hak_akses('admin.komoditas.add')) {
                $aksi .= '<button type="button" class="btn btn-info btn-xs btn-copy" data-id="' . $item->komoditas_id . '" title="Copy">
                            <i class="fa fa-copy"></i>
                          </button> ';
            }
            
            if (ce_cek_hak_akses('admin.komoditas.delete')) {
                $aksi .= '<button type="button" class="btn btn-danger btn-xs btn-delete" data-id="' . $item->komoditas_id . '" title="Hapus">
                            <i class="fa fa-trash"></i>
                          </button>';
            }
            
            $row[] = $aksi;

            $data[] = $row;
        }

        $output = [
            'draw' => $_POST['draw'],
            'recordsTotal' => $this->komoditas_m->count_all($table),
            'recordsFiltered' => $this->komoditas_m->count_filtered($table, $column_order, $column_search, $select, $join),
            'data' => $data,
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash()
        ];

        echo json_encode($output);
    }

    public function get_by_id()
    {
        ce_hak_akses('admin.komoditas.view');
        
        $id = $this->input->post('id');
        $komoditas = $this->komoditas_m->komoditas_by_id($id);
        
        if ($komoditas) {
            $response = [
                'status' => true,
                'data' => $komoditas
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Data komoditas tidak ditemukan'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function save($id = null)
    {
        $hak_akses = empty($id) ? 'admin.komoditas.add' : 'admin.komoditas.update';
        ce_hak_akses($hak_akses);
        
        $this->form_validation->set_rules('komoditas_nama', 'Nama Komoditas', 'required|trim');
        $this->form_validation->set_rules('komoditas_parent_id', 'Kategori', 'required|numeric');
        $this->form_validation->set_rules('komoditas_urutan', 'Urutan', 'required|numeric');
        $this->form_validation->set_rules('komoditas_status', 'Status', 'required|in_list[0,1]');
        
        if ($this->form_validation->run() == FALSE) {
            $response = ['status' => false, 'message' => validation_errors()];
        } else {
            $nama = $this->input->post('komoditas_nama');
            $parent_id = $this->input->post('komoditas_parent_id');
            
            // Check for duplicate name in same parent
            if ($this->komoditas_m->check_duplicate_name($nama, $parent_id, $id)) {
                $response = ['status' => false, 'message' => 'Nama komoditas sudah ada dalam kategori yang sama'];
            } else {
                $data = [
                    'komoditas_nama' => $nama,
                    'komoditas_parent_id' => $parent_id,
                    'komoditas_satuan' => $this->input->post('komoditas_satuan'),
                    'komoditas_deskripsi' => $this->input->post('komoditas_deskripsi'),
                    'komoditas_urutan' => $this->input->post('komoditas_urutan'),
                    'komoditas_status' => $this->input->post('komoditas_status'),
                    'komoditas_updated_at' => date('Y-m-d H:i:s'),
                    'komoditas_updated_by' => $this->session->userdata('id_user')
                ];

                if (empty($id)) {
                    $data['komoditas_created_at'] = date('Y-m-d H:i:s');
                    $data['komoditas_created_by'] = $this->session->userdata('id_user');
                    $result = $this->komoditas_m->komoditas_insert_data($data);
                    $message = 'Komoditas berhasil ditambahkan';
                } else {
                    $result = $this->komoditas_m->komoditas_update_data($data, $id);
                    $message = 'Komoditas berhasil diupdate';
                }

                if ($result) {
                    $response = ['status' => true, 'message' => $message];
                } else {
                    $response = ['status' => false, 'message' => 'Gagal menyimpan data komoditas'];
                }
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function copy()
    {
        ce_hak_akses('admin.komoditas.add');
        
        $id = $this->input->post('id');
        $komoditas = $this->komoditas_m->komoditas_by_id($id);
        
        if (!$komoditas) {
            $response = ['status' => false, 'message' => 'Komoditas tidak ditemukan'];
        } else {
            $data = [
                'komoditas_nama' => $komoditas->komoditas_nama . ' (Copy)',
                'komoditas_parent_id' => $komoditas->komoditas_parent_id,
                'komoditas_satuan' => $komoditas->komoditas_satuan,
                'komoditas_deskripsi' => $komoditas->komoditas_deskripsi,
                'komoditas_urutan' => $this->komoditas_m->get_next_order($komoditas->komoditas_parent_id),
                'komoditas_status' => $komoditas->komoditas_status,
                'komoditas_created_at' => date('Y-m-d H:i:s'),
                'komoditas_created_by' => $this->session->userdata('id_user'),
                'komoditas_updated_at' => date('Y-m-d H:i:s'),
                'komoditas_updated_by' => $this->session->userdata('id_user')
            ];
            
            $result = $this->komoditas_m->komoditas_insert_data($data);
            if ($result) {
                $response = ['status' => true, 'message' => 'Komoditas berhasil dicopy'];
            } else {
                $response = ['status' => false, 'message' => 'Gagal copy komoditas'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.komoditas.delete');
        
        $id = $this->input->post('id');
        
        // Check if has children
        if ($this->komoditas_m->has_children($id)) {
            $response = ['status' => false, 'message' => 'Komoditas ini memiliki sub komoditas. Hapus sub komoditas terlebih dahulu.'];
        } else {
            $result = $this->komoditas_m->komoditas_delete_data($id);
            if ($result) {
                $response = ['status' => true, 'message' => 'Komoditas berhasil dihapus'];
            } else {
                $response = ['status' => false, 'message' => 'Gagal menghapus data komoditas'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function update_order()
    {
        ce_hak_akses('admin.komoditas.update');

        $id = $this->input->post('id');
        $new_order = $this->input->post('order');
        $parent_id = $this->input->post('parent_id');

        $result = $this->komoditas_m->update_order($id, $new_order, $parent_id);

        if ($result) {
            $response = ['status' => true, 'message' => 'Urutan berhasil diupdate'];
        } else {
            $response = ['status' => false, 'message' => 'Gagal update urutan'];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function get_next_order()
    {
        ce_hak_akses('admin.komoditas.view');

        $parent_id = $this->input->post('parent_id') ?: 0;
        $next_order = $this->komoditas_m->get_next_order($parent_id);

        $response = [
            'status' => true,
            'next_order' => $next_order
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }
}
