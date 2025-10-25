<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_kepengurusan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.kategori_kepengurusan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kategori_kepengurusan.view');
        
        $data['header'] = 'Kategori <small>Kepengurusan</small>';
        $data['halaman'] = 'kategori_kepengurusan';
        $data['javascript'] = array(
            'kategori_kepengurusan/js_kategori_kepengurusan' => null
        );

        // Siapkan data statistik
        $data['total_kategori'] = $this->kategori_kepengurusan_m->count_all_kategori();
        $data['kategori_aktif'] = $this->kategori_kepengurusan_m->count_active_kategori();
        $data['kategori_nonaktif'] = $this->kategori_kepengurusan_m->count_inactive_kategori();
        $data['next_kode'] = $this->kategori_kepengurusan_m->get_next_kode();

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kategori_kepengurusan.view');

        $dataConfig = array(
            'table' => 'kategori_kepengurusan',
            'column_order' => array(null, 'kepengurusan_kode', 'kepengurusan_nama', 'status', null),
            'column_search' => array('kepengurusan_kode', 'kepengurusan_nama'),
            'order' => array('kepengurusan_id' => 'asc')
        );

        // Filter berdasarkan status
        $conditions = array();
        $status = $this->input->post('status');
        if ($status !== '' && $status !== null) {
            $conditions['status'] = $status;
        }
        
        if (!empty($conditions)) {
            $dataConfig['condition'] = $conditions;
        }
        
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->kepengurusan_kode;
            $row[] = $item->kepengurusan_nama;
            $status_label = $item->status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Nonaktif</span>';
            $row[] = $status_label;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.kategori_kepengurusan.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->kepengurusan_id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.kategori_kepengurusan.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->kepengurusan_id.'" title="Hapus"><i class="fa fa-trash"></i></button>';
            }
            $aksi .= '</div>';
            $row[] = $aksi;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data
        );
        
        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('kepengurusan_id');
        if (empty($id)) {
            ce_hak_akses('admin.kategori_kepengurusan.add');
        } else {
            ce_hak_akses('admin.kategori_kepengurusan.update');
        }
        
        $kepengurusan_kode = $this->input->post('kepengurusan_kode');
        $kepengurusan_nama = $this->input->post('kepengurusan_nama');
        $status = $this->input->post('status');

        $data = [
            'kepengurusan_kode' => $kepengurusan_kode,
            'kepengurusan_nama' => $kepengurusan_nama,
            'status' => $status ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validasi data
        $errors = $this->kategori_kepengurusan_m->validate_kategori_data($data, $id);
        if (!empty($errors)) {
            $response = [
                'status' => false,
                'message' => implode('<br>', $errors)
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $this->session->userdata('user_id');
            $result = $this->kategori_kepengurusan_m->kategori_kepengurusan_insert_data($data);
            $message = 'Data kategori kepengurusan berhasil ditambahkan';
        } else {
            $data['updated_by'] = $this->session->userdata('user_id');
            $result = $this->kategori_kepengurusan_m->kategori_kepengurusan_update_data($data, $id);
            $message = 'Data kategori kepengurusan berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kategori kepengurusan'
            ];
        }

        echo json_encode($response);
    }

    public function detail()
    {
        ce_hak_akses('admin.kategori_kepengurusan.view');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $kategori = $this->kategori_kepengurusan_m->kategori_kepengurusan_by_id($id);
        if (!$kategori) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        $response = [
            'status' => true,
            'data' => $kategori
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.kategori_kepengurusan.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $kategori = $this->kategori_kepengurusan_m->kategori_kepengurusan_by_id($id);
        if (!$kategori) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        // Cek apakah kategori masih digunakan
        $usage_count = $this->kategori_kepengurusan_m->count_usage_in_kepengurusan_detail($id);
        if ($usage_count > 0) {
            $response = [
                'status' => false,
                'message' => 'Kategori kepengurusan tidak dapat dihapus karena masih digunakan di ' . $usage_count . ' data kepengurusan detail'
            ];
            
            echo json_encode($response);
            return;
        }

        $result = $this->kategori_kepengurusan_m->kategori_kepengurusan_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kategori kepengurusan berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kategori kepengurusan'
            ];
        }

        echo json_encode($response);
    }

    public function get_next_kode()
    {
        ce_hak_akses('admin.kategori_kepengurusan.add');
        
        $next_kode = $this->kategori_kepengurusan_m->get_next_kode();
        
        $response = [
            'status' => true,
            'kode' => $next_kode
        ];

        echo json_encode($response);
    }
}
