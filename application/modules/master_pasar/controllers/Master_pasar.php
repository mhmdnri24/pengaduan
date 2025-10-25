<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pasar extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_pasar.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_pasar.view');
        $data['header'] = 'Master <small>Pasar</small>';
        $data['halaman'] = 'master_pasar';
        $data['javascript'] = array(
            'master_pasar/js_master_pasar' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_pasar.view');

        $dataConfig = [
            'table' => 'master_pasar mp',
            'select' => 'mp.pasar_id, mp.pasar_nama, mp.pasar_alamat, mp.pasar_telepon, mp.pasar_status, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan',
            'column_order' => [null, 'mp.pasar_nama', 'mp.pasar_alamat', 'kl.nama_kelurahan', 'kc.nama_kecamatan', 'kt.nama_kota', 'mp.pasar_telepon', 'mp.pasar_status', null],
            'column_search' => ['mp.pasar_nama', 'mp.pasar_alamat', 'kl.nama_kelurahan', 'kc.nama_kecamatan', 'kt.nama_kota', 'mp.pasar_telepon'],
            'join' => [
                ['kota kt', 'mp.pasar_id_kota=kt.id_kota', 'left'],
                ['kecamatan kc', 'mp.pasar_id_kecamatan=kc.id_kecamatan', 'left'],
                ['kelurahan kl', 'mp.pasar_id_kelurahan=kl.id_kelurahan', 'left']
            ],
            'order' => ['mp.pasar_id' => 'desc']
        ];

        // Filter berdasarkan parameter
        $where = [];
        
        if ($this->input->post('filter_kecamatan') && $this->input->post('filter_kecamatan') != '') {
            $where['mp.pasar_id_kecamatan'] = $this->input->post('filter_kecamatan');
        }
        
        if ($this->input->post('filter_kelurahan') && $this->input->post('filter_kelurahan') != '') {
            $where['mp.pasar_id_kelurahan'] = $this->input->post('filter_kelurahan');
        }
        
        if ($this->input->post('filter_status') !== '' && $this->input->post('filter_status') !== null) {
            $where['mp.pasar_status'] = $this->input->post('filter_status');
        }

        if (!empty($where)) {
            $dataConfig['where'] = $where;
        }

        $list = $this->master_pasar_m->get_datatables(
            $dataConfig['table'],
            $dataConfig['column_order'],
            $dataConfig['column_search'],
            $dataConfig['order'],
            $dataConfig['join'],
            isset($dataConfig['where']) ? $dataConfig['where'] : null,
            null,
            $dataConfig['select']
        );

        $data = array();
        $no = $_POST['start'];
        foreach ($list as $item) {
            $no++;
            $row = array();
            
            // Nomor
            $row[] = $no;
            
            // Nama Pasar
            $row[] = $item->pasar_nama;
            
            // Alamat
            $row[] = $item->pasar_alamat ?: '-';
            
            // Kelurahan
            $row[] = $item->nama_kelurahan ?: '-';
            
            // Kecamatan
            $row[] = $item->nama_kecamatan ?: '-';
            
            // Kota
            $row[] = $item->nama_kota ?: '-';
            
            // Telepon
            $row[] = $item->pasar_telepon ?: '-';
            
            // Status
            if ($item->pasar_status == 1) {
                $status = '<span class="label label-success">Aktif</span>';
            } else {
                $status = '<span class="label label-danger">Nonaktif</span>';
            }
            $row[] = $status;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_pasar.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->pasar_id.'" data-nama="'.$item->pasar_nama.'" data-alamat="'.$item->pasar_alamat.'" data-deskripsi="'.$item->pasar_deskripsi.'" data-kecamatan="'.$item->pasar_id_kecamatan.'" data-kelurahan="'.$item->pasar_id_kelurahan.'" data-telepon="'.$item->pasar_telepon.'" data-latitude="'.$item->pasar_latitude.'" data-longitude="'.$item->pasar_longitude.'" data-status="'.$item->pasar_status.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_pasar.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->pasar_id.'" data-nama="'.$item->pasar_nama.'"><i class="fa fa-trash"></i></button>';
            }
            $aksi .= '</div>';
            
            $row[] = $aksi;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->master_pasar_m->count_all(
                $dataConfig['table'],
                $dataConfig['join']
            ),
            "recordsFiltered" => $this->master_pasar_m->count_filtered(
                $dataConfig['table'],
                $dataConfig['column_order'],
                $dataConfig['column_search'],
                $dataConfig['order'],
                $dataConfig['join'],
                isset($dataConfig['where']) ? $dataConfig['where'] : null,
                null,
                $dataConfig['select']
            ),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_pasar.add');
        } else {
            ce_hak_akses('admin.master_pasar.update');
        }
        
        $pasar_nama = $this->input->post('pasar_nama');
        $pasar_alamat = $this->input->post('pasar_alamat');
        $pasar_deskripsi = $this->input->post('pasar_deskripsi');
        $pasar_id_kecamatan = $this->input->post('pasar_id_kecamatan');
        $pasar_id_kelurahan = $this->input->post('pasar_id_kelurahan');
        $pasar_telepon = $this->input->post('pasar_telepon');
        $pasar_status = $this->input->post('pasar_status');

        if (empty($pasar_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Pasar tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'pasar_nama' => $pasar_nama,
            'pasar_alamat' => $pasar_alamat,
            'pasar_deskripsi' => $pasar_deskripsi,
            'pasar_id_kota' => '16.73', // Default Kota Palembang
            'pasar_id_kecamatan' => $pasar_id_kecamatan,
            'pasar_id_kelurahan' => $pasar_id_kelurahan,
            'pasar_telepon' => $pasar_telepon,
            'pasar_status' => $pasar_status ? 1 : 0,
            'pasar_updated_at' => date('Y-m-d H:i:s')
        ];

        // Ambil koordinat jika ada
        $pasar_latitude = $this->input->post('pasar_latitude');
        $pasar_longitude = $this->input->post('pasar_longitude');
        
        if (!empty($pasar_latitude) && !empty($pasar_longitude)) {
            $data['pasar_latitude'] = $pasar_latitude;
            $data['pasar_longitude'] = $pasar_longitude;
        }

        if (empty($id)) {
            $data['pasar_created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_pasar_m->master_pasar_insert_data($data);
            $message = 'Data pasar berhasil ditambahkan';
            
            // Get inserted ID for response
            $inserted_id = $this->db->insert_id();
        } else {
            $result = $this->master_pasar_m->master_pasar_update_data($data, $id);
            $message = 'Data pasar berhasil diperbarui';
            $inserted_id = $id;
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message,
                'pasar_id' => $inserted_id
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data pasar'
            ];
        }

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_pasar.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Cek apakah data ada
        $pasar = $this->master_pasar_m->master_pasar_by_id($id);
        if (!$pasar) {
            $response = [
                'status' => false,
                'message' => 'Data pasar tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        $result = $this->master_pasar_m->master_pasar_delete_data($id);
        
        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data pasar berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data pasar'
            ];
        }
        
        echo json_encode($response);
    }

    // Method untuk mendapatkan data jenis pasar berdasarkan pasar_id
    public function get_jenis_pasar()
    {
        ce_hak_akses('admin.master_pasar.view');
        
        $pasar_id = $this->input->post('pasar_id');
        
        if (empty($pasar_id)) {
            $response = [
                'status' => false,
                'message' => 'ID Pasar tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $jenis_pasar = $this->master_pasar_m->master_pasar_jenis_by_pasar_id($pasar_id);
        
        $response = [
            'status' => true,
            'data' => $jenis_pasar
        ];
        
        echo json_encode($response);
    }

    public function get_pasar_options()
    {
        $pasar_list = $this->master_pasar_m->master_pasar_get_active();
        $options = '<option value="">Pilih Pasar</option>';

        foreach ($pasar_list as $pasar) {
            $options .= '<option value="' . $pasar->pasar_id . '">' . $pasar->pasar_nama . '</option>';
        }

        echo $options;
    }
}
