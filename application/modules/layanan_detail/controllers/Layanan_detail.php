<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Layanan_detail extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.layanan_detail.view');
    }

    public function index()
    {
        ce_hak_akses('admin.layanan_detail.view');
        $data['header'] = 'Detail <small>Layanan</small>';
        $data['halaman'] = 'layanan_detail';
        $data['javascript'] = array(
            'layanan_detail/js_layanan_detail' => null
        );
        $this->load->view('template', $data);
    }

    public function detail($id = null)
    {
        ce_hak_akses('admin.layanan_detail.view');

        if (!$id) {
            show_404();
        }

        $layanan = $this->layanan_detail_m->layanan_by_id($id);
        if (!$layanan) {
            show_404();
        }

        $data['header'] = 'Detail Layanan - ' . $layanan->layanan_nama;
        $data['layanan'] = $layanan;
        // Persyaratan dokumen untuk layanan ini
        $data['persyaratan'] = $this->jenis_layanan_m->detail_by_layanan_id($id);
        $data['halaman'] = 'detail_layanan';
        $data['javascript'] = array(
            'layanan_detail/js_layanan_detail' => null
        );

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.layanan_detail.view');

        $dataConfig = [
            'table' => 'layanan_jenis lj',
            'select' => 'lj.*',
            'column_order' => [null, 'layanan_nama', 'layanan_kategori', 'layanan_durasi', 'layanan_status', null],
            'column_search' => ['layanan_nama', 'layanan_kategori', 'layanan_deskripsi'],
            'order' => ['layanan_id' => 'asc']
        ];

        // Filter berdasarkan kategori dan status
        $kategori = $this->input->post('kategori');
        $status = $this->input->post('status');

        if (!empty($kategori)) {
            $dataConfig['condition']['lj.layanan_kategori'] = $kategori;
        }

        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['lj.layanan_status'] = $status;
        }

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data_result = array();
        $no = $this->input->post('start');

        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->layanan_nama;
            $row[] = $item->layanan_kategori;
            // kolom biaya dihapus
            $row[] = $item->layanan_durasi . ' hari';
            $row[] = $item->layanan_status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Tidak Aktif</span>';

            $action = '';
            if (ce_cek_hak_akses('admin.layanan_detail.view')) {
                $action .= '<a href="'.site_url('layanan_detail/detail/'.$item->layanan_id).'" class="btn btn-info btn-xs" title="Detail"><i class="fa fa-eye"></i></a> ';
            }
            if (ce_cek_hak_akses('admin.layanan_detail.update')) {
                $action .= '<button type="button" class="btn btn-warning btn-xs" onclick="edit_data('.$item->layanan_id.')" title="Edit"><i class="fa fa-edit"></i></button> ';
            }
            if (ce_cek_hak_akses('admin.layanan_detail.delete')) {
                $action .= '<button type="button" class="btn btn-danger btn-xs" onclick="delete_data('.$item->layanan_id.')" title="Hapus"><i class="fa fa-trash"></i></button>';
            }

            $row[] = $action;
            $data_result[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data_result
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.layanan_detail.add');
        } else {
            ce_hak_akses('admin.layanan_detail.update');
        }

        $layanan_nama = $this->input->post('layanan_nama');
        $layanan_deskripsi = $this->input->post('layanan_deskripsi');
        // $layanan_biaya dihapus
        $layanan_durasi = $this->input->post('layanan_durasi');
        $layanan_kategori = $this->input->post('layanan_kategori');
        // $layanan_persyaratan dihapus (persyaratan dikelola di tabel layanan_detail)
        $layanan_status = $this->input->post('layanan_status');

        if (empty($layanan_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Layanan tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        // Validasi biaya dihapus

        // Validasi duplikasi nama layanan
        $existing_layanan = $this->db->where('layanan_nama', $layanan_nama);
        if (!empty($id)) {
            $existing_layanan->where('layanan_id !=', $id);
        }
        $existing_layanan = $existing_layanan->get('layanan_jenis')->row();

        if ($existing_layanan) {
            $response = [
                'status' => false,
                'message' => 'Nama layanan sudah ada, silakan gunakan nama lain'
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
            // 'layanan_persyaratan' dihapus,
            'layanan_status' => $layanan_status ? 1 : 0,
            'layanan_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['layanan_created_at'] = date('Y-m-d H:i:s');
            $result = $this->layanan_detail_m->layanan_insert_data($data);
            $message = 'Data layanan berhasil ditambahkan';
        } else {
            $result = $this->layanan_detail_m->layanan_update_data($data, $id);
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



        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.layanan_detail.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID layanan tidak valid'
            ];

            echo json_encode($response);
            return;
        }

        $result = $this->layanan_detail_m->layanan_delete_data($id);

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



        echo json_encode($response);
    }

    // CRUD Methods untuk Persyaratan Dokumen
    public function ajax_persyaratan($layanan_id = null)
    {
        ce_hak_akses('admin.layanan_detail.view');

        if (!$layanan_id) {
            $layanan_id = $this->input->post('layanan_id');
        }

        $dataConfig = [
            'table' => 'layanan_detail ld',
            'select' => 'ld.*',
            'column_order' => [null, 'layanan_detail_nama', 'layanan_detail_deskripsi', 'layanan_detail_contoh_file', 'layanan_detail_wajib', 'layanan_detail_urutan', 'layanan_detail_status', null],
            'column_search' => ['layanan_detail_nama', 'layanan_detail_deskripsi'],
            'order' => ['layanan_detail_urutan' => 'asc', 'layanan_detail_nama' => 'asc'],
            'condition' => ['ld.layanan_id' => $layanan_id]
        ];

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data_result = array();
        $no = $this->input->post('start');

        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->layanan_detail_nama;
            $row[] = $item->layanan_detail_deskripsi ?: '-';

            // Kolom Contoh File
            if ($item->layanan_detail_contoh_file) {
                $file_ext = strtolower(pathinfo($item->layanan_detail_contoh_file, PATHINFO_EXTENSION));
                $file_name = basename($item->layanan_detail_contoh_file);
                $preview_btn = '<button type="button" class="btn btn-xs btn-info" onclick="previewFileFromTable(\''.$item->layanan_detail_contoh_file.'\', \''.$file_name.'\')" title="Preview"><i class="fa fa-eye"></i></button>';
                $row[] = $preview_btn . ' <small>' . substr($file_name, 0, 15) . (strlen($file_name) > 15 ? '...' : '') . '</small>';
            } else {
                $row[] = '<span class="text-muted">-</span>';
            }

            $row[] = $item->layanan_detail_wajib ? '<span class="label label-success">Wajib</span>' : '<span class="label label-warning">Opsional</span>';
            $row[] = (int)$item->layanan_detail_urutan;
            $row[] = $item->layanan_detail_status ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Tidak Aktif</span>';

            $action = '';
            if (ce_cek_hak_akses('admin.layanan_detail.update')) {
                $action .= '<button type="button" class="btn btn-warning btn-xs" onclick="edit_persyaratan('.$item->layanan_detail_id.')" title="Edit"><i class="fa fa-edit"></i></button> ';
            }
            if (ce_cek_hak_akses('admin.layanan_detail.delete')) {
                $action .= '<button type="button" class="btn btn-danger btn-xs" onclick="delete_persyaratan('.$item->layanan_detail_id.')" title="Hapus"><i class="fa fa-trash"></i></button>';
            }

            $row[] = $action;
            $data_result[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data_result,
        );

        echo json_encode($output);
    }

    public function save_persyaratan()
    {
        $id = $this->input->post('layanan_detail_id');
        if (empty($id)) {
            ce_hak_akses('admin.layanan_detail.add');
        } else {
            ce_hak_akses('admin.layanan_detail.update');
        }

        $layanan_id = $this->input->post('layanan_id');
        $nama = $this->input->post('layanan_detail_nama');
        $deskripsi = $this->input->post('layanan_detail_deskripsi');
        $tipe_file = $this->input->post('layanan_detail_tipe_file');
        $ukuran_max = $this->input->post('layanan_detail_ukuran_max');
        $wajib = $this->input->post('layanan_detail_wajib');
        $urutan = $this->input->post('layanan_detail_urutan');
        $status = $this->input->post('layanan_detail_status');

        // Validasi
        if (empty($layanan_id)) {
            $response = [
                'status' => false,
                'message' => 'ID Layanan tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        if (empty($nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama persyaratan tidak boleh kosong'
            ];
            echo json_encode($response);
            return;
        }

        if (empty($ukuran_max) || !is_numeric($ukuran_max)) {
            $ukuran_max = 5120; // Default 5MB
        }

        if (empty($urutan) || !is_numeric($urutan)) {
            $urutan = 0;
        }

        $contoh_file = $this->input->post('layanan_detail_contoh_file');

        $data = [
            'layanan_id' => $layanan_id,
            'layanan_detail_nama' => $nama,
            'layanan_detail_deskripsi' => $deskripsi,
            'layanan_detail_tipe_file' => $tipe_file ?: 'PDF,JPG,PNG',
            'layanan_detail_ukuran_max' => $ukuran_max,
            'layanan_detail_contoh_file' => $contoh_file,
            'layanan_detail_wajib' => $wajib ? 1 : 0,
            'layanan_detail_urutan' => $urutan,
            'layanan_detail_status' => $status ? 1 : 0,
            'layanan_detail_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['layanan_detail_created_at'] = date('Y-m-d H:i:s');
            $result = $this->jenis_layanan_m->detail_insert_data($data);
            $message = 'Persyaratan berhasil ditambahkan';
        } else {
            $result = $this->jenis_layanan_m->detail_update_data($data, $id);
            $message = 'Persyaratan berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan persyaratan'
            ];
        }



        echo json_encode($response);
    }

    public function get_persyaratan($id)
    {
        ce_hak_akses('admin.layanan_detail.view');

        $persyaratan = $this->jenis_layanan_m->detail_by_id($id);

        if ($persyaratan) {
            $response = [
                'status' => true,
                'data' => $persyaratan
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Data persyaratan tidak ditemukan'
            ];
        }

        echo json_encode($response);
    }

    public function delete_persyaratan()
    {
        ce_hak_akses('admin.layanan_detail.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID persyaratan tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->jenis_layanan_m->detail_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Persyaratan berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus persyaratan'
            ];
        }



        echo json_encode($response);
    }

    public function upload_contoh_file()
    {
        ce_hak_akses('admin.layanan_detail.add');

        $response = ['status' => false, 'message' => '', 'file_path' => ''];

        if (!isset($_FILES['contoh_file']) || $_FILES['contoh_file']['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'Tidak ada file yang diupload atau terjadi error';
            echo json_encode($response);
            return;
        }

        $file = $_FILES['contoh_file'];
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validasi tipe file
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            $response['message'] = 'Tipe file tidak diizinkan. Gunakan: ' . implode(', ', $allowed_types);
            echo json_encode($response);
            return;
        }

        // Validasi ukuran file
        if ($file['size'] > $max_size) {
            $response['message'] = 'Ukuran file terlalu besar. Maksimal 5MB';
            echo json_encode($response);
            return;
        }

        // Generate nama file unik
        $new_filename = 'contoh_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = FCPATH . 'uploads/contoh_dokumen/';

        // Pastikan direktori ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $full_path = $upload_path . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $full_path)) {
            $response = [
                'status' => true,
                'message' => 'File berhasil diupload',
                'file_path' => 'uploads/contoh_dokumen/' . $new_filename,
                'file_name' => $file['name']
            ];
        } else {
            $response['message'] = 'Gagal mengupload file';
        }

        echo json_encode($response);
    }

    public function delete_contoh_file()
    {
        ce_hak_akses('admin.layanan_detail.update');

        $file_path = $this->input->post('file_path');
        $response = ['status' => false, 'message' => ''];

        if (empty($file_path)) {
            $response['message'] = 'Path file tidak valid';
            echo json_encode($response);
            return;
        }

        $full_path = FCPATH . $file_path;

        if (file_exists($full_path)) {
            if (unlink($full_path)) {
                $response = [
                    'status' => true,
                    'message' => 'File berhasil dihapus'
                ];
            } else {
                $response['message'] = 'Gagal menghapus file';
            }
        } else {
            $response = [
                'status' => true,
                'message' => 'File tidak ditemukan (mungkin sudah dihapus)'
            ];
        }



        echo json_encode($response);
    }

    // Method untuk mendapatkan data layanan by ID (untuk edit)
    public function get_layanan_by_id()
    {
        ce_hak_akses('admin.layanan_detail.view');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID layanan tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        $layanan = $this->layanan_detail_m->layanan_by_id($id);

        if ($layanan) {
            $response = [
                'status' => true,
                'data' => $layanan
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Data layanan tidak ditemukan'
            ];
        }

        echo json_encode($response);
    }

    // Method untuk load form edit layanan
    public function load_edit_form()
    {
        ce_hak_akses('admin.layanan_detail.update');

        $id = $this->input->post('id');

        if (empty($id)) {
            echo 'ID layanan tidak valid';
            return;
        }

        $layanan = $this->layanan_detail_m->layanan_by_id($id);

        if (!$layanan) {
            echo 'Data layanan tidak ditemukan';
            return;
        }

        // Load view form edit
        $data['layanan'] = $layanan;
        $this->load->view('layanan_detail/layanan_detail/form_edit', $data);
    }
}
