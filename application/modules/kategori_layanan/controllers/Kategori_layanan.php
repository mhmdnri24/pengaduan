<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kategori_layanan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.kategori_layanan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kategori_layanan.view');
        $data['header'] = 'Kategori <small>Layanan</small>';
        $data['halaman'] = 'kategori_layanan';
        $data['javascript'] = array(
            'kategori_layanan/js_kategori_layanan' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kategori_layanan.view');

        $dataConfig = [
            'table' => 'layanan_kategori lk',
            'select' => 'lk.*',
            'column_order' => [null, 'kategori_nama', 'kategori_urutan', 'kategori_status', null],
            'column_search' => ['kategori_nama', 'kategori_deskripsi'],
            'order' => ['kategori_id' => 'asc']
        ];

        // Filter berdasarkan status
        $status = $this->input->post('status');

        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['lk.kategori_status'] = $status;
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

            // Nama Kategori
            $row[] = $item->kategori_nama;

            // Urutan
            $row[] = $item->kategori_urutan;

            // Status Aktif
            $status_aktif = $item->kategori_status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Tidak Aktif</span>';
            $row[] = $status_aktif;

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.kategori_layanan.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->kategori_id.'" data-nama="'.$item->kategori_nama.'" data-deskripsi="'.$item->kategori_deskripsi.'" data-urutan="'.$item->kategori_urutan.'" data-status="'.$item->kategori_status.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.kategori_layanan.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->kategori_id.'"><i class="fa fa-trash"></i></button>';
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
            ce_hak_akses('admin.kategori_layanan.add');
        } else {
            ce_hak_akses('admin.kategori_layanan.update');
        }

        $kategori_nama = $this->input->post('kategori_nama');
        $kategori_deskripsi = $this->input->post('kategori_deskripsi');
        $kategori_urutan = $this->input->post('kategori_urutan');
        $kategori_status = $this->input->post('kategori_status');

        if (empty($kategori_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Kategori tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        // Validasi duplikasi nama kategori
        $existing_kategori = $this->db->where('kategori_nama', $kategori_nama);
        if (!empty($id)) {
            $existing_kategori->where('kategori_id !=', $id);
        }
        $existing_kategori = $existing_kategori->get('layanan_kategori')->row();

        if ($existing_kategori) {
            $response = [
                'status' => false,
                'message' => 'Nama kategori sudah ada, silakan gunakan nama lain'
            ];

            echo json_encode($response);
            return;
        }

        $data = [
            'kategori_nama' => $kategori_nama,
            'kategori_deskripsi' => $kategori_deskripsi,
            'kategori_urutan' => !empty($kategori_urutan) ? $kategori_urutan : 0,
            'kategori_status' => $kategori_status ? 1 : 0,
            'kategori_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['kategori_created_at'] = date('Y-m-d H:i:s');
            $result = $this->kategori_layanan_m->kategori_insert_data($data);
            $message = 'Data kategori berhasil ditambahkan';
        } else {
            $result = $this->kategori_layanan_m->kategori_update_data($data, $id);
            $message = 'Data kategori berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kategori'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.kategori_layanan.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID kategori tidak valid'
            ];

            echo json_encode($response);
            return;
        }

        // Check if kategori has jenis_layanan records
        $layanan_count = $this->kategori_layanan_m->count_layanan_by_kategori($id);
        if ($layanan_count > 0) {
            $response = [
                'status' => false,
                'message' => 'Tidak dapat menghapus kategori yang masih memiliki jenis layanan'
            ];

            echo json_encode($response);
            return;
        }

        $result = $this->kategori_layanan_m->kategori_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kategori berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kategori'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function export_csv()
    {
        ce_hak_akses('admin.kategori_layanan.view');

        $data = $this->kategori_layanan_m->kategori_get_all();

        $filename = 'kategori_layanan_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header CSV
        fputcsv($output, [
            'No',
            'Nama Kategori',
            'Deskripsi',
            'Urutan',
            'Status Aktif',
            'Dibuat',
            'Diperbarui'
        ]);

        // Data CSV
        $no = 1;
        foreach ($data as $item) {
            fputcsv($output, [
                $no++,
                $item->kategori_nama,
                $item->kategori_deskripsi,
                $item->kategori_urutan,
                $item->kategori_status == 1 ? 'Aktif' : 'Tidak Aktif',
                $item->kategori_created_at,
                $item->kategori_updated_at
            ]);
        }

        fclose($output);
    }
}