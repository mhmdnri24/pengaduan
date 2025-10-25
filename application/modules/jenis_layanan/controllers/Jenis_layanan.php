<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Jenis_layanan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.jenis_layanan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.jenis_layanan.view');

        // Load additional models
        $this->load->model('user_m');

        $data['header'] = 'Jenis <small>Layanan</small>';
        $data['halaman'] = 'jenis_layanan';
        $data['unitkerja_options'] = $this->user_m->get_unitkerja_options();
        $data['javascript'] = array(
            'jenis_layanan/js_jenis_layanan' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.jenis_layanan.view');

        $dataConfig = [
            'table' => 'layanan_jenis lj',
            'select' => 'lj.*, lk.kategori_nama, uk.unitkerja as unitkerja_nama',
            'join' => [
                ['layanan_kategori lk', 'lj.layanan_kategori = lk.kategori_id', 'left'],
                ['tbUnitKerja uk', 'lj.unitkerja_id = uk.id_unitkerja', 'left']
            ],
            'column_order' => [null, 'lk.kategori_nama', 'lj.layanan_nama', 'lj.layanan_wajib', 'lj.layanan_status', null],
            'column_search' => ['lj.layanan_nama', 'lk.kategori_nama', 'lj.layanan_deskripsi', 'uk.unitkerja'],
            'order' => ['lj.layanan_id' => 'asc']
        ];

        // Filter berdasarkan unit kerja user jika bukan super admin (id_level != 1)
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');

        if ($user_level != 1 && !empty($user_id_unitkerja)) {
            $dataConfig['condition']['lj.unitkerja_id'] = $user_id_unitkerja;
        }

        // Filter berdasarkan kategori dan status
        $kategori = $this->input->post('kategori');
        $status = $this->input->post('status');
        $wajib = $this->input->post('wajib');
        $unitkerja = $this->input->post('unitkerja');

        if (!empty($kategori)) {
            $dataConfig['condition']['lj.layanan_kategori'] = $kategori;
        }

        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['lj.layanan_status'] = $status;
        }

        if ($wajib !== '' && $wajib !== null) {
            $dataConfig['condition']['lj.layanan_wajib'] = $wajib;
        }

        if ($unitkerja !== '' && $unitkerja !== null && $user_level == 1) {
            $dataConfig['condition']['lj.unitkerja_id'] = $unitkerja;
        }

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data = array();
        $no = $this->input->post('start');

        foreach ($list as $item) {
            $no++;
            $row = array();

            // No
            $row[] = $no;
            $row[] = $item->kategori_nama ?? '-';
            $row[] = $item->layanan_nama;
            
            $status_wajib = $item->layanan_wajib == 1 ? '<span class="label label-success">Wajib</span>' : '<span class="label label-warning">Opsional</span>';
            $row[] = $status_wajib;

            // Status Aktif
            $status_aktif = $item->layanan_status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Tidak Aktif</span>';
            $row[] = $status_aktif;
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.layanan_detail.view')) {
                $aksi .= '<a href="'.site_url('layanan_detail/detail/'.$item->layanan_id).'" class="btn btn-sm btn-info" title="Detail"><i class="fa fa-eye"></i>PERSYARATAN</a>';
            }
            if (ce_hak_akses('admin.jenis_layanan.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->layanan_id.'" data-nama="'.$item->layanan_nama.'" data-deskripsi="'.$item->layanan_deskripsi.'" data-durasi="'.$item->layanan_durasi.'" data-kategori="'.$item->layanan_kategori.'" data-wajib="'.$item->layanan_wajib.'" data-contoh="'.$item->layanan_contoh.'" data-urutan="'.$item->layanan_urutan.'" data-status="'.$item->layanan_status.'" data-unitkerja="'.$item->unitkerja_id.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.jenis_layanan.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->layanan_id.'"><i class="fa fa-trash"></i></button>';
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
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.jenis_layanan.add');
        } else {
            ce_hak_akses('admin.jenis_layanan.update');
        }
        
        $layanan_nama = $this->input->post('layanan_nama');
        $layanan_deskripsi = $this->input->post('layanan_deskripsi');
        // $layanan_biaya dihapus
        $layanan_durasi = $this->input->post('layanan_durasi');
        $layanan_kategori = $this->input->post('layanan_kategori');
        $layanan_wajib = $this->input->post('layanan_wajib');
        $layanan_contoh = $this->input->post('layanan_contoh');
        $layanan_urutan = $this->input->post('layanan_urutan');
        $layanan_status = $this->input->post('layanan_status');
        $unitkerja_id = $this->input->post('layanan_unitkerja');

        $user_level = $this->session->userdata('id_level');
        $user_unitkerja = $this->session->userdata('id_unitkerja');

        if (empty($layanan_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Layanan tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        // For non-super admin, force unitkerja_id from session
        if ($user_level != 1) {
            $unitkerja_id = $user_unitkerja;
        } elseif (empty($unitkerja_id)) {
            $response = [
                'status' => false,
                'message' => 'Unit kerja harus dipilih'
            ];
            echo json_encode($response);
            return;
        }

        $existing_layanan = $this->db->where('layanan_nama', $layanan_nama)
                                     ->where('unitkerja_id', $unitkerja_id);
        if (!empty($id)) {
            $existing_layanan->where('layanan_id !=', $id);
        }
        $existing_layanan = $existing_layanan->get('layanan_jenis')->row();

        if ($existing_layanan) {
            $response = [
                'status' => false,
                'message' => 'Nama layanan sudah ada untuk unit kerja ini, silakan gunakan nama lain'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'layanan_nama' => $layanan_nama,
            'layanan_deskripsi' => $layanan_deskripsi,
            // 'layanan_biaya' dihapus,
            'layanan_durasi' => $layanan_durasi,
            'layanan_kategori' => $layanan_kategori,
            'layanan_wajib' => $layanan_wajib ? 1 : 0,
            'layanan_contoh' => $layanan_contoh,
            'layanan_urutan' => !empty($layanan_urutan) ? $layanan_urutan : 0,
            'layanan_status' => $layanan_status ? 1 : 0,
            'unitkerja_id' => $unitkerja_id,
            'layanan_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['layanan_created_at'] = date('Y-m-d H:i:s');
            $result = $this->jenis_layanan_m->layanan_insert_data($data);
            $message = 'Data layanan berhasil ditambahkan';
        } else {
            $result = $this->jenis_layanan_m->layanan_update_data($data, $id);
            $message = 'Data layanan berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data layanan'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.jenis_layanan.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID layanan tidak valid'
            ];

            echo json_encode($response);
            return;
        }

        $details = $this->jenis_layanan_m->detail_by_layanan_id($id);
        if (!empty($details)) {
            $response = [
                'status' => false,
                'message' => 'Tidak dapat menghapus layanan yang masih memiliki detail layanan'
            ];

            echo json_encode($response);
            return;
        }

        $result = $this->jenis_layanan_m->layanan_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data layanan berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data layanan'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }
}

