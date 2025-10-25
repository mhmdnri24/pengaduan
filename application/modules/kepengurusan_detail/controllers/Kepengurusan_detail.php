<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kepengurusan_detail extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.kepengurusan_detail.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kepengurusan_detail.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Kepengurusan <small>Detail</small>';
        $data['halaman'] = 'kepengurusan_detail';
        $data['javascript'] = array(
            'kepengurusan_detail/js_kepengurusan_detail' => null
        );

        // Siapkan data statistik
        $stats = $this->kepengurusan_detail_m->get_statistics();
        $data['total_penugasan'] = $stats['total_penugasan'];
        $data['penugasan_aktif'] = $stats['penugasan_aktif'];
        $data['penugasan_selesai'] = $stats['penugasan_selesai'];
        $data['penugasan_nonaktif'] = $stats['penugasan_nonaktif'];

        // Data untuk dropdown
        $data['kepengurusan_sosial_list'] = $this->kepengurusan_sosial_m->get_kepengurusan_for_select();
        $data['kategori_kepengurusan_list'] = $this->kategori_kepengurusan_m->get_kategori_for_select();
        
        // Get fasilitas umum (masjid & mushola saja)
        $data['fasilitas_umum_list'] = $this->get_fasilitas_masjid_mushola();

        $this->load->view('template', $data);
    }

    private function get_fasilitas_masjid_mushola()
    {
        // Ambil kategori masjid dan mushola dari master_kategori
        $this->db->select('fu.id, fu.nama_fasilitas, mk.nama_kategori');
        $this->db->from('fasilitas_umum fu');
        $this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
        $this->db->where('fu.status', 1);
        $this->db->where_in('LOWER(mk.nama_kategori)', ['masjid', 'mushola', 'musholla', 'surau']);
        $this->db->order_by('fu.nama_fasilitas', 'asc');
        $result = $this->db->get()->result();
        
        $options = [];
        foreach ($result as $row) {
            $options[$row->id] = $row->nama_fasilitas . ' (' . $row->nama_kategori . ')';
        }
        
        return $options;
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kepengurusan_detail.view');

        $dataConfig = [
            'table' => 'kepengurusan_detail kd',
            'select' => 'kd.id, ks.nama_lengkap, kk.kepengurusan_nama, fu.nama_fasilitas, kd.tanggal_sk, kd.nomor_sk, kd.masa_jabatan, kd.tanggal_mulai, kd.tanggal_selesai, kd.status',
            'column_order' => [null, 'ks.nama_lengkap', 'kk.kepengurusan_nama', 'fu.nama_fasilitas', 'kd.tanggal_sk', 'kd.nomor_sk', 'kd.masa_jabatan', 'kd.tanggal_mulai', 'kd.tanggal_selesai', 'kd.status', null],
            'column_search' => ['ks.nama_lengkap', 'kk.kepengurusan_nama', 'fu.nama_fasilitas', 'kd.nomor_sk'],
            'join' => [
                ['kepengurusan_sosial ks', 'kd.kepengurusan_sosial_id = ks.id', 'left'],
                ['kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left'],
                ['fasilitas_umum fu', 'kd.id_fasilitas_umum = fu.id', 'left'],
                ['user u', 'kd.created_by = u.id_user', 'left']
            ],
            'order' => ['kd.id' => 'desc']
        ];

        // Filter berdasarkan unit kerja user jika bukan admin (id_level != 1)
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');

        if ($user_level != 1 && !empty($user_id_unitkerja)) {
            // Filter data berdasarkan unit kerja user yang membuat data tersebut
            $dataConfig['condition']['u.id_unitkerja'] = $user_id_unitkerja;
        }

        // Filter berdasarkan status
        $status = $this->input->post('status');
        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['kd.status'] = $status;
        }

        // Filter berdasarkan kategori kepengurusan
        $kategori = $this->input->post('kategori');
        if ($kategori !== '' && $kategori !== null) {
            $dataConfig['condition']['kd.kategori_kepengurusan_id'] = $kategori;
        }

        // Filter berdasarkan fasilitas
        $fasilitas = $this->input->post('fasilitas');
        if ($fasilitas !== '' && $fasilitas !== null) {
            $dataConfig['condition']['kd.id_fasilitas_umum'] = $fasilitas;
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
            $row[] = $item->kepengurusan_nama;
            $row[] = $item->nama_fasilitas;
            $row[] = $item->tanggal_sk ? date('d-m-Y', strtotime($item->tanggal_sk)) : '-';
            $row[] = $item->nomor_sk ?: '-';
            $row[] = $item->masa_jabatan ? $item->masa_jabatan . ' tahun' : '-';
            $row[] = $item->tanggal_mulai ? date('d-m-Y', strtotime($item->tanggal_mulai)) : '-';
            $row[] = $item->tanggal_selesai ? date('d-m-Y', strtotime($item->tanggal_selesai)) : '-';
            
            // Status
            $status_class = '';
            switch ($item->status) {
                case 'AKTIF':
                    $status_class = 'label-success';
                    break;
                case 'SELESAI':
                    $status_class = 'label-info';
                    break;
                case 'NONAKTIF':
                    $status_class = 'label-danger';
                    break;
                default:
                    $status_class = 'label-default';
            }
            $row[] = '<span class="label ' . $status_class . '">' . $item->status . '</span>';
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.kepengurusan_detail.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.kepengurusan_detail.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.kepengurusan_detail.delete')) {
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
            "data" => $data,
            "
            "
        );
        
        
        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.kepengurusan_detail.add');
        } else {
            ce_hak_akses('admin.kepengurusan_detail.update');
        }

        $kepengurusan_sosial_id = $this->input->post('kepengurusan_sosial_id');
        $kategori_kepengurusan_id = $this->input->post('kategori_kepengurusan_id');
        $id_fasilitas_umum = $this->input->post('id_fasilitas_umum');
        $tanggal_sk = $this->input->post('tanggal_sk');
        $nomor_sk = $this->input->post('nomor_sk');
        $masa_jabatan = $this->input->post('masa_jabatan');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        $file_sk = $this->input->post('file_sk_existing'); // For existing file
        $status = $this->input->post('status');

        $data = [
            'kepengurusan_sosial_id' => $kepengurusan_sosial_id,
            'kategori_kepengurusan_id' => $kategori_kepengurusan_id,
            'id_fasilitas_umum' => $id_fasilitas_umum,
            'tanggal_sk' => $tanggal_sk,
            'nomor_sk' => $nomor_sk,
            'masa_jabatan' => $masa_jabatan,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'file_sk' => $file_sk,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle file upload jika ada file baru
        if (!empty($_FILES['file_sk']['name'])) {
            $this->load->helper('secure_file');

            // Validasi file upload
            $validation = validate_file_upload($_FILES['file_sk'], ['pdf', 'jpg', 'jpeg', 'png'], 5120);
            if (!$validation['status']) {
                $response = [
                    'status' => false,
                    'message' => $validation['message'],
                    '
                    '
                ];
                
                echo json_encode($response);
                return;
            }

            // Konfigurasi upload
            $config['upload_path'] = './uploads/kepengurusan_detail/';
            $config['allowed_types'] = 'pdf|jpg|jpeg|png';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = generate_unique_filename($_FILES['file_sk']['name'], 'sk');

            // Buat direktori jika belum ada
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if ($this->upload->do_upload('file_sk')) {
                $upload_data = $this->upload->data();
                $data['file_sk'] = $upload_data['file_name'];

                // Hapus file lama jika update
                if (!empty($id)) {
                    $old_data = $this->kepengurusan_detail_m->kepengurusan_detail_by_id($id);
                    if ($old_data && !empty($old_data->file_sk)) {
                        $old_file = './uploads/kepengurusan_detail/' . $old_data->file_sk;
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Error upload file: ' . $this->upload->display_errors('', ''),
                    '
                    '
                ];
                
                echo json_encode($response);
                return;
            }
        }

        // Validasi data
        $errors = $this->kepengurusan_detail_m->validate_detail_data($data, $id);
        if (!empty($errors)) {
            $response = [
                'status' => false,
                'message' => implode('<br>', $errors),
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Check for duplicate assignment
        if ($this->kepengurusan_detail_m->check_duplicate_assignment($kepengurusan_sosial_id, $kategori_kepengurusan_id, $id_fasilitas_umum, $id)) {
            $response = [
                'status' => false,
                'message' => 'Pengurus sudah memiliki penugasan aktif untuk kategori dan fasilitas yang sama',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $this->session->userdata('user_id');
            $result = $this->kepengurusan_detail_m->kepengurusan_detail_insert_data($data);
            $message = 'Data kepengurusan detail berhasil ditambahkan';
        } else {
            $data['updated_by'] = $this->session->userdata('user_id');
            $result = $this->kepengurusan_detail_m->kepengurusan_detail_update_data($data, $id);
            $message = 'Data kepengurusan detail berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message,
                '
                '
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kepengurusan detail',
                '
                '
            ];
        }

        
        echo json_encode($response);
    }

    public function detail()
    {
        ce_hak_akses('admin.kepengurusan_detail.view');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        $detail = $this->kepengurusan_detail_m->get_detail_with_complete_info($id);
        if (!$detail) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Load helper untuk secure file URL
        $this->load->helper('secure_file');

        // Tambahkan secure URL jika ada file
        if (!empty($detail->file_sk)) {
            $file_path = 'uploads/kepengurusan_detail/' . $detail->file_sk;
            $detail->secure_file_url = secure_file_url($file_path, 120); // 2 jam
        }

        $response = [
            'status' => true,
            'data' => $detail,
            '
            '
        ];

        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.kepengurusan_detail.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        $detail = $this->kepengurusan_detail_m->kepengurusan_detail_by_id($id);
        if (!$detail) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Hapus file SK jika ada
        if (!empty($detail->file_sk)) {
            $file_path = './uploads/kepengurusan_detail/' . $detail->file_sk;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $result = $this->kepengurusan_detail_m->kepengurusan_detail_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kepengurusan detail berhasil dihapus',
                '
                '
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kepengurusan detail',
                '
                '
            ];
        }

        
        echo json_encode($response);
    }

    public function upload_file_sk()
    {
        ce_hak_akses('admin.kepengurusan_detail.update');

        $id = $this->input->post('id');
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Load helper untuk validasi file
        $this->load->helper('secure_file');

        // Validasi file upload menggunakan helper
        if (!empty($_FILES['file_sk']['name'])) {
            $validation = validate_file_upload($_FILES['file_sk'], ['pdf', 'jpg', 'jpeg', 'png'], 5120);
            if (!$validation['status']) {
                $response = [
                    'status' => false,
                    'message' => $validation['message'],
                    '
                    '
                ];
                
                echo json_encode($response);
                return;
            }
        }

        // Konfigurasi upload - hanya PDF dan gambar
        $config['upload_path'] = './uploads/kepengurusan_detail/';
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['file_name'] = generate_unique_filename($_FILES['file_sk']['name'], 'sk');

        // Buat direktori jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('file_sk')) {
            $upload_data = $this->upload->data();

            // Hapus file lama jika ada
            $detail = $this->kepengurusan_detail_m->kepengurusan_detail_by_id($id);
            if ($detail && !empty($detail->file_sk)) {
                $old_file = './uploads/kepengurusan_detail/' . $detail->file_sk;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Update database
            $data = [
                'file_sk' => $upload_data['file_name'],
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id')
            ];

            $result = $this->kepengurusan_detail_m->kepengurusan_detail_update_data($data, $id);

            if ($result) {
                $response = [
                    'status' => true,
                    'message' => 'File SK berhasil diupload',
                    'file_name' => $upload_data['file_name']
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Gagal menyimpan data file'
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }

        
        echo json_encode($response);
    }

    public function get_assignments_by_person()
    {
        ce_hak_akses('admin.kepengurusan_detail.view');

        $kepengurusan_sosial_id = $this->input->post('kepengurusan_sosial_id');

        if (empty($kepengurusan_sosial_id)) {
            $response = [
                'status' => false,
                'message' => 'ID pengurus tidak valid',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        $assignments = $this->kepengurusan_detail_m->get_active_assignments_by_person($kepengurusan_sosial_id);

        $response = [
            'status' => true,
            'data' => $assignments,
            '
            '
        ];

        
        echo json_encode($response);
    }



    public function update_expired_assignments()
    {
        ce_hak_akses('admin.kepengurusan_detail.update');

        $result = $this->kepengurusan_detail_m->update_expired_assignments();

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Status penugasan yang sudah berakhir berhasil diperbarui',
                '
                '
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Tidak ada penugasan yang perlu diperbarui',
                '
                '
            ];
        }

        
        echo json_encode($response);
    }
}
