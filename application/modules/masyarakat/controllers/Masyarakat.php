<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Masyarakat extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.masyarakat.view');
    }

    public function index()
    {
        ce_hak_akses('admin.masyarakat.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Data <small>Masyarakat</small>';
        $data['halaman'] = 'masyarakat/masyarakat';
        $data['javascript'] = array(
            'masyarakat/js_masyarakat' => null
        );

        // Siapkan data statistik
        $data['total_masyarakat'] = $this->masyarakat_m->count_all_masyarakat();
        $data['masyarakat_aktif'] = $this->masyarakat_m->count_by_status(1);
        $data['masyarakat_nonaktif'] = $this->masyarakat_m->count_by_status(0);

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.masyarakat.view');

        $dataConfig = array(
            'table' => 'masyarakat',
            'select' => 'masyarakat.id, masyarakat.nama_lengkap, masyarakat.nik, masyarakat.no_telpon, masyarakat.alamat, masyarakat.status_aktif, masyarakat.id_kecamatan, masyarakat.id_kelurahan, kecamatan.nama_kecamatan, kelurahan.nama_kelurahan',
            'column_order' => array(null, 'nama_lengkap', 'nik', 'no_telpon', 'nama_kecamatan', 'nama_kelurahan', 'status_aktif', null),
            'column_search' => array('nama_lengkap', 'nik', 'no_telpon', 'kecamatan.nama_kecamatan', 'kelurahan.nama_kelurahan', 'alamat'),
            'join' => array(
                array('kecamatan', 'masyarakat.id_kecamatan = kecamatan.id_kecamatan', 'left'),
                array('kelurahan', 'masyarakat.id_kelurahan = kelurahan.id_kelurahan', 'left')
            ),
            'order' => array('id' => 'desc')
        );

        // Filter berdasarkan kecamatan
        $conditions = array();
        $filter_kecamatan = $this->input->post('filter_kecamatan');
        if ($filter_kecamatan !== '' && $filter_kecamatan !== null) {
            $conditions['masyarakat.id_kecamatan'] = $filter_kecamatan;
        }

        // Filter berdasarkan kelurahan
        $filter_kelurahan = $this->input->post('filter_kelurahan');
        if ($filter_kelurahan !== '' && $filter_kelurahan !== null) {
            $conditions['masyarakat.id_kelurahan'] = $filter_kelurahan;
        }

        // Filter berdasarkan status
        $filter_status = $this->input->post('filter_status');
        if ($filter_status !== '' && $filter_status !== null) {
            $conditions['masyarakat.status_aktif'] = $filter_status;
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
            $row[] = $item->nama_lengkap;
            $row[] = $item->nik;
            $row[] = $item->no_telpon ?: '-';
            $row[] = $item->nama_kecamatan ?: '-';
            $row[] = $item->nama_kelurahan ?: '-';
            $row[] = $item->status_aktif == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non-Aktif</span>';
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.masyarakat.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.masyarakat.edit')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.masyarakat.delete')) {
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
            ce_hak_akses('admin.masyarakat.add');
        } else {
            ce_hak_akses('admin.masyarakat.update');
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
        $errors = $this->masyarakat_m->validate_masyarakat_data($data, $id);
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
            $result = $this->masyarakat_m->masyarakat_update_data($data, $id);
            $message = 'Data masyarakat berhasil diperbarui';
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->masyarakat_m->masyarakat_insert_data($data);
            $message = 'Data masyarakat berhasil ditambahkan';
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
        ce_hak_akses('admin.masyarakat.view');

        $id = $this->input->post('id');
        $data = $this->masyarakat_m->masyarakat_by_id($id);

        $response = [
            'status' => true,
            'data' => $data
        ];
        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.masyarakat.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->masyarakat_m->masyarakat_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data masyarakat berhasil dihapus'
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
        $data = $this->masyarakat_m->search_masyarakat($keyword);
        
        $response = [
            'status' => true,
            'data' => $data
        ];
        
        echo json_encode($response);
    }
}
