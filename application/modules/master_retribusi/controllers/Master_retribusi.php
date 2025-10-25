<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_retribusi extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_retribusi.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_retribusi.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Data <small>Retribusi</small>';
        $data['halaman'] = 'master_retribusi/master_retribusi';
        $data['javascript'] = array(
            'master_retribusi/js_master_retribusi' => null
        );

        // Siapkan data statistik
        $data['total_retribusi'] = $this->master_retribusi_m->count_all_retribusi();
        $data['retribusi_aktif'] = $this->master_retribusi_m->count_by_status(1);
        $data['retribusi_nonaktif'] = $this->master_retribusi_m->count_by_status(0);

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_retribusi.view');

        $dataConfig = array(
            'table' => 'master_retribusi',
            'select' => 'master_retribusi.id, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi, master_retribusi.tarif, master_retribusi.satuan, master_retribusi.status_aktif, master_retribusi.deskripsi, master_retribusi.created_at',
            'column_order' => array(null, 'nama_retribusi', 'kode_retribusi', 'jenis_retribusi', 'tarif', 'satuan', 'status_aktif', null),
            'column_search' => array('nama_retribusi', 'kode_retribusi', 'jenis_retribusi', 'deskripsi'),
            'order' => array('id' => 'desc')
        );

        // Filter berdasarkan jenis retribusi
        $conditions = array();
        $filter_jenis = $this->input->post('filter_jenis');
        if ($filter_jenis !== '' && $filter_jenis !== null) {
            $conditions['master_retribusi.jenis_retribusi'] = $filter_jenis;
        }

        // Filter berdasarkan status
        $filter_status = $this->input->post('filter_status');
        if ($filter_status !== '' && $filter_status !== null) {
            $conditions['master_retribusi.status_aktif'] = $filter_status;
        }

        if (!empty($conditions)) {
            $dataConfig['condition'] = $conditions;
        }

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $no = $this->input->post('start');
        $data = array();

        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->nama_retribusi;
            $row[] = $item->kode_retribusi;
            $row[] = $item->jenis_retribusi;
            $row[] = 'Rp ' . number_format($item->tarif, 0, ',', '.');
            $row[] = $item->satuan;
            $row[] = $item->status_aktif == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non-Aktif</span>';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_retribusi.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.master_retribusi.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_retribusi.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->id.'" title="Hapus"><i class="fa fa-trash"></i></button>';
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
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_retribusi.add');
        } else {
            ce_hak_akses('admin.master_retribusi.update');
        }

        $data = array(
            'nama_retribusi' => $this->input->post('nama_retribusi'),
            'kode_retribusi' => $this->input->post('kode_retribusi'),
            'jenis_retribusi' => $this->input->post('jenis_retribusi'),
            'tarif' => $this->input->post('tarif') ?: 0,
            'satuan' => $this->input->post('satuan'),
            'deskripsi' => $this->input->post('deskripsi'),
            'status_aktif' => $this->input->post('status_aktif') ?: 1
        );

        // Validasi data
        $errors = $this->master_retribusi_m->validate_retribusi_data($data, $id);
        if (!empty($errors)) {
            $response = [
                'status' => false,
                'message' => implode('<br>', $errors)
            ];
            echo json_encode($response);
            return;
        }

        if ($id) {
            // Update
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->master_retribusi_m->retribusi_update_data($data, $id);
            $message = 'Data retribusi berhasil diperbarui';
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_retribusi_m->retribusi_insert_data($data);
            $message = 'Data retribusi berhasil ditambahkan';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data'
            ];
        }

        echo json_encode($response);
    }

    public function get_by_id()
    {
        ce_hak_akses('admin.master_retribusi.view');

        $id = $this->input->post('id');
        $data = $this->master_retribusi_m->retribusi_by_id($id);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_retribusi.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->master_retribusi_m->retribusi_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data retribusi berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data'
            ];
        }

        echo json_encode($response);
    }

    public function get_jenis_retribusi()
    {
        $jenis_retribusi = $this->master_retribusi_m->get_jenis_retribusi_list();
        echo json_encode($jenis_retribusi);
    }

    public function search()
    {
        $keyword = $this->input->post('keyword');
        $data = $this->master_retribusi_m->search_retribusi($keyword);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }
}