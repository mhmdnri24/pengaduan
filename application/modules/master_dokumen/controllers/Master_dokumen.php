<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_dokumen extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_dokumen.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_dokumen.view');
        $data['header'] = 'Master <small>Dokumen</small>';
        $data['halaman'] = 'master_dokumen';
        $data['javascript'] = array(
            'master_dokumen/js_master_dokumen' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_dokumen.view');

        $dataConfig = [
            'table' => 'master_dokumen md',
            'select' => 'md.*, kw.nama_kelompok, kw.kode_kelompok',
            'column_order' => [null, 'dokumen_nama', 'dokumen_deskripsi', 'nama_kelompok', 'dokumen_wajib', null],
            'column_search' => ['dokumen_nama', 'dokumen_deskripsi', 'nama_kelompok'],
            'order' => ['dokumen_id' => 'asc'],
            'join' => [
                ['kelompok_wisuda kw', 'md.kelompok_wisuda_id = kw.id', 'left']
            ]
        ];

        // Filter berdasarkan kelompok wisuda dan status
        $kelompok = $this->input->post('kelompok');
        $status = $this->input->post('status');

        if (!empty($kelompok)) {
            $dataConfig['condition']['md.kelompok_wisuda_id'] = $kelompok;
        }

        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['md.dokumen_wajib'] = $status;
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
            
            // Nama Dokumen
            $row[] = $item->dokumen_nama;
            
            // Deskripsi
            $row[] = $item->dokumen_deskripsi ? substr($item->dokumen_deskripsi, 0, 100) . '...' : '-';
            
            // Kelompok Wisuda
            $row[] = $item->nama_kelompok ? $item->nama_kelompok : '-';
            
            // Status Wajib
            $status_wajib = $item->dokumen_wajib == 1 ? '<span class="label label-success">Wajib</span>' : '<span class="label label-warning">Opsional</span>';
            $row[] = $status_wajib;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_dokumen.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->dokumen_id.'" data-nama="'.$item->dokumen_nama.'" data-deskripsi="'.$item->dokumen_deskripsi.'" data-kelompok="'.$item->kelompok_wisuda_id.'" data-wajib="'.$item->dokumen_wajib.'" data-contoh="'.$item->contoh_dokumen.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_dokumen.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->dokumen_id.'"><i class="fa fa-trash"></i></button>';
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
            ce_hak_akses('admin.master_dokumen.add');
        } else {
            ce_hak_akses('admin.master_dokumen.update');
        }
        
        $dokumen_nama = $this->input->post('dokumen_nama');
        $dokumen_deskripsi = $this->input->post('dokumen_deskripsi');
        $kelompok_wisuda_id = $this->input->post('kelompok_wisuda_id');
        $dokumen_wajib = $this->input->post('dokumen_wajib');

        if (empty($dokumen_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Dokumen tidak boleh kosong',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Validasi duplikasi nama dokumen
        $existing_dokumen = $this->db->where('dokumen_nama', $dokumen_nama);
        if (!empty($id)) {
            $existing_dokumen->where('dokumen_id !=', $id);
        }
        $existing_dokumen = $existing_dokumen->get('master_dokumen')->row();

        if ($existing_dokumen) {
            $response = [
                'status' => false,
                'message' => 'Nama dokumen sudah ada, silakan gunakan nama lain',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Upload contoh dokumen jika ada
        $upload_path = './uploads/dokumen/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 755, true);
        }

        $contoh_dokumen = '';
        if (!empty($_FILES['contoh_dokumen']['name'])) {
            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'pdf|doc|docx|jpg|jpeg|png';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = 'contoh_' . time() . '_' . $_FILES['contoh_dokumen']['name'];

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if ($this->upload->do_upload('contoh_dokumen')) {
                $upload_data = $this->upload->data();
                $contoh_dokumen = 'uploads/dokumen/' . $upload_data['file_name'];
                
                // Hapus file lama jika update
                if (!empty($id)) {
                    $old_data = $this->master_dokumen_m->dokumen_by_id($id);
                    if ($old_data && !empty($old_data->contoh_dokumen) && file_exists('./' . $old_data->contoh_dokumen)) {
                        unlink('./' . $old_data->contoh_dokumen);
                    }
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Error upload file: ' . $this->upload->display_errors(),
                    '
                    '
                ];
                
                echo json_encode($response);
                return;
            }
        }

        $data = [
            'dokumen_nama' => $dokumen_nama,
            'dokumen_deskripsi' => $dokumen_deskripsi,
            'kelompok_wisuda_id' => !empty($kelompok_wisuda_id) ? $kelompok_wisuda_id : null,
            'dokumen_wajib' => $dokumen_wajib ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($contoh_dokumen)) {
            $data['contoh_dokumen'] = $contoh_dokumen;
        }

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_dokumen_m->dokumen_insert_data($data);
            $message = 'Data dokumen berhasil ditambahkan';
        } else {
            $result = $this->master_dokumen_m->dokumen_update_data($data, $id);
            $message = 'Data dokumen berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data dokumen'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['

        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_dokumen.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID dokumen tidak valid',
                '
                '
            ];
            
            echo json_encode($response);
            return;
        }

        // Hapus file contoh dokumen jika ada
        $dokumen = $this->master_dokumen_m->dokumen_by_id($id);
        if ($dokumen && !empty($dokumen->contoh_dokumen) && file_exists('./' . $dokumen->contoh_dokumen)) {
            unlink('./' . $dokumen->contoh_dokumen);
        }

        $result = $this->master_dokumen_m->dokumen_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data dokumen berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data dokumen'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['

        
        echo json_encode($response);
    }

    public function export_csv()
    {
        ce_hak_akses('admin.master_dokumen.view');

        $data = $this->master_dokumen_m->dokumen_with_kelompok();

        $filename = 'master_dokumen_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header CSV
        fputcsv($output, [
            'No',
            'Nama Dokumen',
            'Deskripsi',
            'Kelompok Wisuda',
            'Status Wajib',
            'Contoh Dokumen',
            'Dibuat',
            'Diperbarui'
        ]);

        // Data
        $no = 1;
        foreach ($data as $item) {
            fputcsv($output, [
                $no++,
                $item->dokumen_nama,
                $item->dokumen_deskripsi,
                $item->nama_kelompok ? $item->nama_kelompok : '-',
                $item->dokumen_wajib == 1 ? 'Wajib' : 'Opsional',
                $item->contoh_dokumen ? base_url($item->contoh_dokumen) : '-',
                $item->created_at,
                $item->updated_at
            ]);
        }

        fclose($output);
    }
}
