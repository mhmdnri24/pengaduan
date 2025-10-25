<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kepengurusan_sosial extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.kepengurusan_sosial.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kepengurusan_sosial.view');

        // Siapkan data statistik untuk view
        $data['header'] = 'Kepengurusan <small>Sosial</small>';
        $data['halaman'] = 'kepengurusan_sosial';
        $data['javascript'] = array(
            'kepengurusan_sosial/js_kepengurusan_sosial' => null
        );

        // Siapkan data statistik
        $data['total_kepengurusan'] = $this->kepengurusan_sosial_m->count_all_kepengurusan();
        $data['kepengurusan_laki'] = $this->kepengurusan_sosial_m->count_by_jenis_kelamin('L');
        $data['kepengurusan_perempuan'] = $this->kepengurusan_sosial_m->count_by_jenis_kelamin('P');
        $data['kepengurusan_menikah'] = $this->kepengurusan_sosial_m->count_by_status_kawin('MENIKAH');

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kepengurusan_sosial.view');

        $dataConfig = array(
            'table' => 'kepengurusan_sosial',
            'select' => 'kepengurusan_sosial.id, kepengurusan_sosial.nama_lengkap, kepengurusan_sosial.nik_ktp, kepengurusan_sosial.no_kk, kepengurusan_sosial.no_wa_hp, kepengurusan_sosial.tempat_lahir, kepengurusan_sosial.tanggal_lahir, kepengurusan_sosial.jenis_kelamin, kepengurusan_sosial.agama, kepengurusan_sosial.status_kawin, kepengurusan_sosial.pendidikan_terakhir',
            'column_order' => array(null, 'nama_lengkap', 'nik_ktp', 'no_kk', 'no_wa_hp', 'tempat_lahir', 'jenis_kelamin', 'agama', 'status_kawin', 'pendidikan_terakhir', null),
            'column_search' => array('nama_lengkap', 'nik_ktp', 'no_kk', 'no_wa_hp', 'tempat_lahir', 'agama', 'status_kawin', 'pendidikan_terakhir'),
            'join' => array(
                array('table' => 'kecamatan', 'on' => 'kepengurusan_sosial.kecamatan=kecamatan.id_kecamatan', 'type' => 'left'),
                array('table' => 'kelurahan', 'on' => 'kepengurusan_sosial.kelurahan=kelurahan.id_kelurahan', 'type' => 'left'),
                array('table' => 'user', 'on' => 'kepengurusan_sosial.created_by=user.id_user', 'type' => 'left')
            ),
            'order' => array('kepengurusan_sosial.id' => 'desc')
        );

        // Filter berdasarkan unit kerja user jika bukan admin (id_level != 1)
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');

        if ($user_level != 1 && !empty($user_id_unitkerja)) {
            // Filter data berdasarkan unit kerja user yang membuat data tersebut
            $conditions['user.id_unitkerja'] = $user_id_unitkerja;
        }

        // Filter berdasarkan jenis kelamin
        $conditions = array();
        $jenis_kelamin = $this->input->post('jenis_kelamin');
        if ($jenis_kelamin !== '' && $jenis_kelamin !== null) {
            $conditions['kepengurusan_sosial.jenis_kelamin'] = $jenis_kelamin;
        }

        // Filter berdasarkan agama
        $agama = $this->input->post('agama');
        if ($agama !== '' && $agama !== null) {
            $conditions['kepengurusan_sosial.agama'] = $agama;
        }

        // Filter berdasarkan status kawin
        $status_kawin = $this->input->post('status_kawin');
        if ($status_kawin !== '' && $status_kawin !== null) {
            $conditions['kepengurusan_sosial.status_kawin'] = $status_kawin;
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
            $row[] = $item->nik_ktp;
            $row[] = $item->no_kk;
            $row[] = $item->no_wa_hp;
            
            // Format tempat tanggal lahir
            $tempat_tgl_lahir = $item->tempat_lahir;
            if (!empty($item->tanggal_lahir)) {
                $tempat_tgl_lahir .= ', ' . date('d-m-Y', strtotime($item->tanggal_lahir));
            }
            $row[] = $tempat_tgl_lahir;
            
            $row[] = $item->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan';
            $row[] = $item->agama;
            $row[] = $item->status_kawin;
            $row[] = $item->pendidikan_terakhir;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.kepengurusan_sosial.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.kepengurusan_sosial.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.kepengurusan_sosial.delete')) {
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
            ce_hak_akses('admin.kepengurusan_sosial.add');
        } else {
            ce_hak_akses('admin.kepengurusan_sosial.update');
        }

        $nama_lengkap = $this->input->post('nama_lengkap');
        $nik_ktp = $this->input->post('nik_ktp');
        $no_kk = $this->input->post('no_kk');
        $no_wa_hp = $this->input->post('no_wa_hp');
        $tempat_lahir = $this->input->post('tempat_lahir');
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $jenis_kelamin = $this->input->post('jenis_kelamin');
        $agama = $this->input->post('agama');
        $alamat = $this->input->post('alamat');
        $kecamatan = $this->input->post('kecamatan');
        $kelurahan = $this->input->post('kelurahan');
        $status_kawin = $this->input->post('status_kawin');
        $pendidikan_terakhir = $this->input->post('pendidikan_terakhir');

        // Validasi basic sesuai pattern fakultas/jurusan
        if (empty($nama_lengkap)) {
            $response = [
                'status' => false,
                'message' => 'Nama lengkap harus diisi'
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($nik_ktp)) {
            $response = [
                'status' => false,
                'message' => 'NIK KTP harus diisi'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'nama_lengkap' => $nama_lengkap,
            'nik_ktp' => $nik_ktp,
            'no_kk' => $no_kk,
            'no_wa_hp' => $no_wa_hp,
            'tempat_lahir' => $tempat_lahir,
            'tanggal_lahir' => $tanggal_lahir,
            'jenis_kelamin' => $jenis_kelamin,
            'agama' => $agama,
            'alamat' => $alamat,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
            'status_kawin' => $status_kawin,
            'pendidikan_terakhir' => $pendidikan_terakhir,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle foto upload
        if (!empty($_FILES['foto_profil']['name'])) {
            $config['upload_path'] = './uploads/kepengurusan_sosial/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048; // 2MB
            $config['file_name'] = 'foto_' . time() . '_' . rand(1000, 9999);

            // Buat direktori jika belum ada
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if ($this->upload->do_upload('foto_profil')) {
                $upload_data = $this->upload->data();
                $data['foto_profil'] = $upload_data['file_name'];

                // Hapus foto lama jika update
                if (!empty($id)) {
                    $old_data = $this->kepengurusan_sosial_m->kepengurusan_sosial_by_id($id);
                    if ($old_data && !empty($old_data->foto_profil)) {
                        $old_foto = './uploads/kepengurusan_sosial/' . $old_data->foto_profil;
                        if (file_exists($old_foto)) {
                            unlink($old_foto);
                        }
                    }
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Error upload foto: ' . $this->upload->display_errors('', '')
                ];
                
                echo json_encode($response);
                return;
            }
        }

        // Validasi data
        $errors = $this->kepengurusan_sosial_m->validate_kepengurusan_data($data, $id);
        if (!empty($errors)) {
            $response = [
                'status' => false,
                'message' => implode('<br>', $errors)
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $this->session->userdata('user_id');
            $result = $this->kepengurusan_sosial_m->kepengurusan_sosial_insert_data($data);
            $message = 'Data kepengurusan sosial berhasil ditambahkan';
        } else {
            $data['updated_by'] = $this->session->userdata('user_id');
            $result = $this->kepengurusan_sosial_m->kepengurusan_sosial_update_data($data, $id);
            $message = 'Data kepengurusan sosial berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kepengurusan sosial'
            ];
        }

        echo json_encode($response);
    }

    public function detail()
    {
        ce_hak_akses('admin.kepengurusan_sosial.view');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $kepengurusan = $this->kepengurusan_sosial_m->get_kepengurusan_with_location($id);
        if (!$kepengurusan) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        $response = [
            'status' => true,
            'data' => $kepengurusan
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.kepengurusan_sosial.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $kepengurusan = $this->kepengurusan_sosial_m->kepengurusan_sosial_by_id($id);
        if (!$kepengurusan) {
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        // Hapus foto profil jika ada
        if (!empty($kepengurusan->foto_profil)) {
            $foto_path = './uploads/kepengurusan_sosial/' . $kepengurusan->foto_profil;
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }

        $result = $this->kepengurusan_sosial_m->kepengurusan_sosial_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kepengurusan sosial berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kepengurusan sosial'
            ];
        }

        echo json_encode($response);
    }

    public function upload_foto()
    {
        ce_hak_akses('admin.kepengurusan_sosial.update');

        $id = $this->input->post('id');
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Konfigurasi upload
        $config['upload_path'] = './uploads/kepengurusan_sosial/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['max_size'] = 2048; // 2MB
        $config['file_name'] = 'foto_' . $id . '_' . time();

        // Buat direktori jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('foto_profil')) {
            $upload_data = $this->upload->data();

            // Hapus foto lama jika ada
            $kepengurusan = $this->kepengurusan_sosial_m->kepengurusan_sosial_by_id($id);
            if ($kepengurusan && !empty($kepengurusan->foto_profil)) {
                $old_foto = './uploads/kepengurusan_sosial/' . $kepengurusan->foto_profil;
                if (file_exists($old_foto)) {
                    unlink($old_foto);
                }
            }

            // Update database
            $data = [
                'foto_profil' => $upload_data['file_name'],
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id')
            ];

            $result = $this->kepengurusan_sosial_m->kepengurusan_sosial_update_data($data, $id);

            if ($result) {
                $response = [
                    'status' => true,
                    'message' => 'Foto profil berhasil diupload',
                    'file_name' => $upload_data['file_name']
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Gagal menyimpan data foto'
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

    public function import_excel()
    {
        ce_hak_akses('admin.kepengurusan_sosial.add');

        if (!isset($_FILES['excel_file']) || empty($_FILES['excel_file']['name'])) {
            $response = [
                'status' => false,
                'message' => 'File Excel tidak ditemukan'
            ];
            echo json_encode($response);
            return;
        }

        // Validasi file
        $file_ext = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($file_ext), ['xlsx', 'xls'])) {
            $response = [
                'status' => false,
                'message' => 'Format file harus Excel (.xlsx atau .xls)'
            ];
            echo json_encode($response);
            return;
        }

        // Panggil method import dari model
        $result = $this->kepengurusan_sosial_m->import_excel_data($_FILES['excel_file']);

        if ($result['success']) {
            $message = "Import berhasil! {$result['imported']} data berhasil diimport";
            if ($result['skipped'] > 0) {
                $message .= ", {$result['skipped']} data dilewatkan (NIK/No KK sudah ada)";
            }

            if (!empty($result['errors'])) {
                $message .= "\n\nDetail error:\n" . implode("\n", $result['errors']);
            }

            $response = [
                'status' => true,
                'message' => $message,
                'imported' => $result['imported'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors']
            ];
        } else {
            $response = [
                'status' => false,
                'message' => $result['message']
            ];
        }

        echo json_encode($response);
    }

    public function download_template()
    {
        ce_hak_akses('admin.kepengurusan_sosial.view');

        // Load PHPSpreadsheet
        require_once APPPATH . '../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = [
            'Nama Lengkap',
            'NIK KTP',
            'No KK',
            'No WA/HP',
            'Tempat Lahir',
            'Tanggal Lahir (DD/MM/YYYY)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Alamat',
            'Kecamatan',
            'Kelurahan',
            'Status Kawin',
            'Pendidikan Terakhir'
        ];

        foreach ($headers as $key => $header) {
            $sheet->setCellValue(chr(65 + $key) . '1', $header);
            $sheet->getStyle(chr(65 + $key) . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension(chr(65 + $key))->setWidth(20);
        }

        // Add sample data
        $sampleData = [
            'Ahmad Yusuf',
            '3171234567890123',
            '3171234567890123',
            '081234567890',
            'Jakarta',
            '15/05/1990',
            'L',
            'ISLAM',
            'Jl. Sudirman No. 123',
            'Jakarta Pusat',
            'Tanah Abang',
            'MENIKAH',
            'SMA'
        ];

        foreach ($sampleData as $key => $value) {
            $sheet->setCellValue(chr(65 + $key) . '2', $value);
        }

        // Set filename
        $filename = 'template_import_kepengurusan_sosial.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}