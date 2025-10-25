<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pedagang extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_pedagang.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_pedagang.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Data <small>Pedagang</small>';
        $data['halaman'] = 'master_pedagang/master_pedagang';
        $data['javascript'] = array(
            'master_pedagang/js_master_pedagang' => null
        );

        // Siapkan data statistik
        $data['total_pedagang'] = $this->master_pedagang_m->count_all_pedagang();
        $data['pedagang_aktif'] = $this->master_pedagang_m->count_by_status(1);
        $data['pedagang_nonaktif'] = $this->master_pedagang_m->count_by_status(0);

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_pedagang.view');

        $dataConfig = array(
            'table' => 'masyarakat_pedagang',
            'select' => 'masyarakat_pedagang.id, masyarakat_pedagang.nama_lengkap, masyarakat_pedagang.nik, masyarakat_pedagang.no_telpon, masyarakat_pedagang.alamat, masyarakat_pedagang.status_aktif, masyarakat_pedagang.id_kecamatan, masyarakat_pedagang.id_kelurahan, kecamatan.nama_kecamatan, kelurahan.nama_kelurahan',
            'column_order' => array(null, 'nama_lengkap', 'nik', 'no_telpon', 'nama_kecamatan', 'nama_kelurahan', 'status_aktif', null),
            'column_search' => array('nama_lengkap', 'nik', 'no_telpon', 'kecamatan.nama_kecamatan', 'kelurahan.nama_kelurahan', 'alamat'),
            'join' => array(
                array('kecamatan', 'masyarakat_pedagang.id_kecamatan = kecamatan.id_kecamatan', 'left'),
                array('kelurahan', 'masyarakat_pedagang.id_kelurahan = kelurahan.id_kelurahan', 'left')
            ),
            'order' => array('id' => 'desc')
        );

        // Filter berdasarkan kecamatan
        $conditions = array();
        $filter_kecamatan = $this->input->post('filter_kecamatan');
        if ($filter_kecamatan !== '' && $filter_kecamatan !== null) {
            $conditions['masyarakat_pedagang.id_kecamatan'] = $filter_kecamatan;
        }

        // Filter berdasarkan kelurahan
        $filter_kelurahan = $this->input->post('filter_kelurahan');
        if ($filter_kelurahan !== '' && $filter_kelurahan !== null) {
            $conditions['masyarakat_pedagang.id_kelurahan'] = $filter_kelurahan;
        }

        // Filter berdasarkan status
        $filter_status = $this->input->post('filter_status');
        if ($filter_status !== '' && $filter_status !== null) {
            $conditions['masyarakat_pedagang.status_aktif'] = $filter_status;
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
            $row[] = $item->nama_lengkap;
            $row[] = $item->nik;
            $row[] = $item->no_telpon ?: '-';
            $row[] = $item->nama_kecamatan ?: '-';
            $row[] = $item->nama_kelurahan ?: '-';
            $row[] = $item->status_aktif == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non-Aktif</span>';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_pedagang.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
                $aksi .= '<button type="button" class="btn btn-sm btn-primary btn-sewa-detail" data-id="'.$item->id.'" data-nama="'.$item->nama_lengkap.'" title="Detail Sewa"><i class="fa fa-building"></i></button>';
            }
            if (ce_hak_akses('admin.master_pedagang.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_pedagang.delete')) {
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
            ce_hak_akses('admin.master_pedagang.add');
        } else {
            ce_hak_akses('admin.master_pedagang.update');
        }

        $data = array(
            'nama_lengkap' => $this->input->post('nama_lengkap'),
            'nik' => $this->input->post('nik'),
            'no_telpon' => $this->input->post('no_telpon'),
            'id_kecamatan' => $this->input->post('id_kecamatan') ?: null,
            'id_kelurahan' => $this->input->post('id_kelurahan') ?: null,
            'alamat' => $this->input->post('alamat'),
            'status_aktif' => $this->input->post('status_aktif') ?: 1
        );

        // Validasi data
        $errors = $this->master_pedagang_m->validate_pedagang_data($data, $id);
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
            $result = $this->master_pedagang_m->pedagang_update_data($data, $id);
            $message = 'Data pedagang berhasil diperbarui';
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_pedagang_m->pedagang_insert_data($data);
            $message = 'Data pedagang berhasil ditambahkan';
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
        ce_hak_akses('admin.master_pedagang.view');

        $id = $this->input->post('id');
        $data = $this->master_pedagang_m->pedagang_by_id($id);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_pedagang.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->master_pedagang_m->pedagang_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data pedagang berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data'
            ];
        }

        echo json_encode($response);
    }

    public function get_kecamatan()
    {
        $kecamatan = $this->db->select('id_kecamatan, nama_kecamatan')
                             ->where('id_kota', '16.73')
                             ->order_by('nama_kecamatan', 'asc')
                             ->get('kecamatan')->result();

        echo json_encode($kecamatan);
    }

    public function get_kelurahan()
    {
        $id_kecamatan = $this->input->post('id_kecamatan');
        $kelurahan = $this->db->select('id_kelurahan, nama_kelurahan')
                             ->where('id_kecamatan', $id_kecamatan)
                             ->order_by('nama_kelurahan', 'asc')
                             ->get('kelurahan')->result();

        echo json_encode($kelurahan);
    }

    public function search()
    {
        $keyword = $this->input->post('keyword');
        $data = $this->master_pedagang_m->search_pedagang($keyword);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }
}