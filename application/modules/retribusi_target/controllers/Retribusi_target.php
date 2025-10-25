<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retribusi_target extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.retribusi_target.view');
    }

    public function index()
    {
        ce_hak_akses('admin.retribusi_target.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Data <small>Target Retribusi</small>';
        $data['halaman'] = 'retribusi_target/retribusi_target';
        $data['javascript'] = array(
            'retribusi_target/js_retribusi_target' => null
        );

        // Siapkan data statistik
        $data['total_target'] = $this->retribusi_target_m->count_all_target();
        $data['target_tahunan'] = $this->retribusi_target_m->count_target_tahunan();
        $data['target_bulanan'] = $this->retribusi_target_m->count_target_bulanan();

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.retribusi_target.view');

        $dataConfig = array(
            'table' => 'retribusi_target',
            'select' => 'retribusi_target.id, retribusi_target.id_retribusi, retribusi_target.tahun, retribusi_target.bulan, retribusi_target.target, retribusi_target.capaian, retribusi_target.persentase, retribusi_target.keterangan, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi, retribusi_target.created_at',
            'column_order' => array(null, 'nama_retribusi', 'tahun', 'bulan', 'target', 'capaian', 'persentase', 'keterangan', null),
            'column_search' => array('nama_retribusi', 'kode_retribusi', 'jenis_retribusi', 'tahun', 'bulan', 'keterangan'),
            'order' => array('tahun' => 'desc', 'bulan' => 'asc', 'nama_retribusi' => 'asc')
        );

        // Join dengan master_retribusi
        $dataConfig['join'] = array(
            array('table' => 'master_retribusi', 'condition' => 'master_retribusi.id = retribusi_target.id_retribusi', 'type' => 'left')
        );

        // Filter berdasarkan tahun
        $conditions = array();
        $filter_tahun = $this->input->post('filter_tahun');
        if ($filter_tahun !== '' && $filter_tahun !== null) {
            $conditions['retribusi_target.tahun'] = $filter_tahun;
        }

        // Filter berdasarkan jenis target
        $filter_jenis = $this->input->post('filter_jenis');
        if ($filter_jenis !== '' && $filter_jenis !== null) {
            if ($filter_jenis == 'tahunan') {
                $conditions['retribusi_target.bulan IS NULL'] = null;
            } elseif ($filter_jenis == 'bulanan') {
                $conditions['retribusi_target.bulan IS NOT NULL'] = null;
            }
        }

        // Filter berdasarkan retribusi
        $filter_retribusi = $this->input->post('filter_retribusi');
        if ($filter_retribusi !== '' && $filter_retribusi !== null) {
            $conditions['retribusi_target.id_retribusi'] = $filter_retribusi;
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
            $row[] = $item->nama_retribusi . '<br><small class="text-muted">' . $item->kode_retribusi . '</small>';
            $row[] = $item->tahun;
            $row[] = $item->bulan ? $item->bulan : '<span class="label label-primary">Tahunan</span>';
            $row[] = 'Rp ' . number_format($item->target, 0, ',', '.');
            $row[] = 'Rp ' . number_format($item->capaian, 0, ',', '.');
            
            // Persentase dengan warna
            $persentase_class = '';
            if ($item->persentase >= 100) {
                $persentase_class = 'label-success';
            } elseif ($item->persentase >= 80) {
                $persentase_class = 'label-warning';
            } else {
                $persentase_class = 'label-danger';
            }
            $row[] = '<span class="label ' . $persentase_class . '">' . number_format($item->persentase, 2, ',', '.') . '%</span>';
            
            $row[] = $item->keterangan ? $item->keterangan : '-';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.retribusi_target.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.retribusi_target.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.retribusi_target.delete')) {
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
            ce_hak_akses('admin.retribusi_target.add');
        } else {
            ce_hak_akses('admin.retribusi_target.update');
        }

        $data = array(
            'id_retribusi' => $this->input->post('id_retribusi'),
            'tahun' => $this->input->post('tahun'),
            'bulan' => $this->input->post('jenis_target') == 'bulanan' ? $this->input->post('bulan') : null,
            'target' => $this->input->post('target') ?: 0,
            'capaian' => $this->input->post('capaian') ?: 0,
            'keterangan' => $this->input->post('keterangan')
        );

        // Validasi data
        $errors = $this->retribusi_target_m->validate_target_data($data, $id);
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
            $result = $this->retribusi_target_m->target_update_data($data, $id);
            $message = 'Data target retribusi berhasil diperbarui';
        } else {
            // Insert
            $result = $this->retribusi_target_m->target_insert_data($data);
            $message = 'Data target retribusi berhasil ditambahkan';
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
        ce_hak_akses('admin.retribusi_target.view');

        $id = $this->input->post('id');
        $data = $this->retribusi_target_m->target_by_id($id);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.retribusi_target.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->retribusi_target_m->target_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data target retribusi berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data'
            ];
        }

        echo json_encode($response);
    }

    public function get_retribusi_list()
    {
        $retribusi_list = $this->retribusi_target_m->get_retribusi_list();
        echo json_encode($retribusi_list);
    }

    public function get_tahun_list()
    {
        $tahun_list = $this->retribusi_target_m->get_tahun_list();
        echo json_encode($tahun_list);
    }

    public function get_bulan_list()
    {
        $bulan_list = $this->retribusi_target_m->get_bulan_list();
        echo json_encode($bulan_list);
    }

    public function search()
    {
        $keyword = $this->input->post('keyword');
        $data = $this->retribusi_target_m->search_target($keyword);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }

    public function dashboard()
    {
        ce_hak_akses('admin.retribusi_target.view');

        $tahun = $this->input->get('tahun') ?: date('Y');
        
        $data['header'] = 'Dashboard <small>Target Retribusi ' . $tahun . '</small>';
        $data['halaman'] = 'retribusi_target/dashboard_target';
        $data['javascript'] = array(
            'retribusi_target/js_dashboard_target' => null
        );
        
        $data['tahun_selected'] = $tahun;
        $data['tahun_list'] = $this->retribusi_target_m->get_tahun_list();
        $data['summary_tahunan'] = $this->retribusi_target_m->get_summary_by_tahun($tahun);
        $data['target_tahunan'] = $this->retribusi_target_m->target_tahunan_by_tahun($tahun);
        $data['target_bulanan'] = $this->retribusi_target_m->target_bulanan_by_tahun($tahun);

        $this->load->view('template', $data);
    }

    public function update_capaian()
    {
        ce_hak_akses('admin.retribusi_target.update');

        $id = $this->input->post('id');
        $capaian = $this->input->post('capaian');

        if (empty($id) || !is_numeric($capaian) || $capaian < 0) {
            $response = [
                'status' => false,
                'message' => 'Data tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $data = array(
            'capaian' => $capaian
        );

        $result = $this->retribusi_target_m->target_update_data($data, $id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Capaian berhasil diperbarui'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal memperbarui capaian'
            ];
        }

        echo json_encode($response);
    }
}