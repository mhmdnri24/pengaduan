<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_instansi extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_instansi.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_instansi.view');
        $data['header'] = 'Master <small>Instansi</small>';
        $data['halaman'] = 'master_instansi';
        $data['javascript'] = array(
            'master_instansi/js_master_instansi' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_instansi.view');
        
        $dataConfig = [
            'table' => 'tbInstansi',
            'column_order' => [null, 'instansi_nama', 'jenis', 'cepat_kode', 'instansi_id', null],
            'column_search' => ['instansi_nama', 'jenis', 'cepat_kode', 'instansi_id'],
            'order' => ['id_instansi' => 'asc']
        ];
        
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->instansi_nama;
            $row[] = $item->jenis ? $item->jenis : '-';
            $row[] = $item->cepat_kode ? $item->cepat_kode : '-';
            $row[] = $item->instansi_id ? $item->instansi_id : '-';
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_instansi.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id_instansi.'" data-nama="'.$item->instansi_nama.'" data-jenis="'.$item->jenis.'" data-cepat_kode="'.$item->cepat_kode.'" data-instansi_id="'.$item->instansi_id.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_instansi.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-hapus" data-id="'.$item->id_instansi.'" data-nama="'.$item->instansi_nama.'"><i class="fa fa-trash"></i></button>';
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

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_instansi.add');
        } else {
            ce_hak_akses('admin.master_instansi.update');
        }
        
        $instansi_nama = $this->input->post('instansi_nama');
        $jenis = $this->input->post('jenis');
        $cepat_kode = $this->input->post('cepat_kode');
        $instansi_id = $this->input->post('instansi_id');

        if (empty($instansi_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Instansi tidak boleh kosong'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $data = [
            'instansi_nama' => $instansi_nama,
            'jenis' => $jenis,
            'cepat_kode' => $cepat_kode,
            'instansi_id' => $instansi_id
        ];

        if (empty($id)) {
            $result = $this->master_instansi_m->instansi_insert_data($data);
            $message = 'Instansi berhasil ditambahkan';
        } else {
            $result = $this->master_instansi_m->instansi_update_data($data, $id);
            $message = 'Instansi berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data Instansi'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.master_instansi.delete');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID Instansi tidak valid'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }
        
        $result = $this->master_instansi_m->instansi_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Instansi berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data Instansi'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
