<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_pelaporan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.kategori_pelaporan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kategori_pelaporan.view');
        
        $data['header'] = 'Kategori <small>Pelaporan</small>';
        $data['halaman'] = 'kategori_pelaporan';
        $data['javascript'] = array(
            'kategori_pelaporan/js_kategori_pelaporan' => null
        );

        // Siapkan data statistik
        $data['total_kategori'] = $this->kategori_pelaporan_m->count_all_kategori();
        $data['kategori_aktif'] = $this->kategori_pelaporan_m->count_active_kategori();
        $data['kategori_nonaktif'] = $this->kategori_pelaporan_m->count_inactive_kategori();
        $data['next_kode'] = $this->kategori_pelaporan_m->get_next_kode();

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kategori_pelaporan.view');

        $dataConfig = array(
            'table' => 'kategori_pelaporan',
            'select' => 'pelaporan_id, pelaporan_kode, pelaporan_nama, status',
            'column_order' => array(null, 'pelaporan_kode', 'pelaporan_nama', 'status', null),
            'column_search' => array('pelaporan_kode', 'pelaporan_nama'),
            'order' => array('pelaporan_id' => 'asc')
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
            $row[] = $item->pelaporan_kode;
            $row[] = $item->pelaporan_nama;
            $status_label = $item->status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Nonaktif</span>';
            $row[] = $status_label;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.kategori_pelaporan.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->pelaporan_id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.kategori_pelaporan.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->pelaporan_id.'" title="Hapus"><i class="fa fa-trash"></i></button>';
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
        $id = $this->input->post('pelaporan_id');
        if (empty($id)) {
            ce_hak_akses('admin.kategori_pelaporan.add');
        } else {
            ce_hak_akses('admin.kategori_pelaporan.update');
        }

        $pelaporan_kode = $this->input->post('pelaporan_kode');
        $pelaporan_nama = $this->input->post('pelaporan_nama');
        $status = $this->input->post('status');

        if (empty($pelaporan_kode)) {
            $response = [
                'status' => false,
                'message' => 'Kode kategori tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        if (empty($pelaporan_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama kategori tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        $data = [
            'pelaporan_kode' => $pelaporan_kode,
            'pelaporan_nama' => $pelaporan_nama,
            'status' => $status ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->kategori_pelaporan_m->kategori_pelaporan_insert_data($data);
            $message = 'Data kategori pelaporan berhasil ditambahkan';
        } else {
            $result = $this->kategori_pelaporan_m->kategori_pelaporan_update_data($data, $id);
            $message = 'Data kategori pelaporan berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kategori pelaporan'
            ];
        }

        echo json_encode($response);
    }

    public function detail()
    {
        ce_hak_akses('admin.kategori_pelaporan.view');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $kategori = $this->kategori_pelaporan_m->kategori_pelaporan_by_id($id);
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
        ce_hak_akses('admin.kategori_pelaporan.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID kategori pelaporan tidak valid'
            ];

            echo json_encode($response);
            return;
        }

        $result = $this->kategori_pelaporan_m->kategori_pelaporan_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kategori pelaporan berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kategori pelaporan'
            ];
        }

        echo json_encode($response);
    }

    public function get_next_kode()
    {
        ce_hak_akses('admin.kategori_pelaporan.add');
        
        $next_kode = $this->kategori_pelaporan_m->get_next_kode();
        
        $response = [
            'status' => true,
            'kode' => $next_kode
        ];

        echo json_encode($response);
    }
}
