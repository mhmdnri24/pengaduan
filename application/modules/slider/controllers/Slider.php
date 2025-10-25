<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Slider extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.slider.view');    
    }

    public function index()
    {
        ce_hak_akses('admin.slider.view');
        $data['header'] = 'Slider <small>Manajemen Slider</small>';
        $data['halaman'] = 'slider/slider';
        $data['javascript'] = array(
            'slider/js_slider' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.slider.view');

        $dataConfig = [
            'table' => 'master_slider',
            'select' => 'id, slider_judul, slider_deskripsi, slider_file, slider_status, created_at, updated_at',
            'column_order' => [null, 'slider_judul', 'slider_deskripsi', 'slider_file', 'slider_status', 'created_at', null],
            'column_search' => ['slider_judul', 'slider_deskripsi'],
            'order' => ['id' => 'desc']
        ];

        // Filter berdasarkan status
        $status = $this->input->post('status');
        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['slider_status'] = $status;
        }
        
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->slider_judul;
            $row[] = $item->slider_deskripsi ? substr($item->slider_deskripsi, 0, 50) . '...' : '-';
            
            // File gambar
            if ($item->slider_file) {
                $file_path = base_url('uploads/slider/' . $item->slider_file);
                $row[] = '<img src="' . $file_path . '" alt="' . $item->slider_judul . '" style="width: 100px; height: 60px; object-fit: cover; border-radius: 5px;" onerror="this.src=\'uploads/slider/placeholder.jpg\'">';
            } else {
                $row[] = '<img src="uploads/slider/placeholder.jpg" alt="No Image" style="width: 100px; height: 60px; object-fit: cover; border-radius: 5px;">';
            }
            
            // Status
            $status_config = $this->config->item('slider_status');
            $status_labels = $this->config->item('slider_status_labels');
            $status_icons = $this->config->item('slider_status_icons');
            
            $status_label = isset($status_labels[$item->slider_status]) ? $status_labels[$item->slider_status] : 'label-default';
            $status_icon = isset($status_icons[$item->slider_status]) ? $status_icons[$item->slider_status] : 'fa-question';
            $status_text = isset($status_config[$item->slider_status]) ? $status_config[$item->slider_status] : $item->slider_status;
            
            $row[] = '<span class="label ' . $status_label . '"><i class="fa ' . $status_icon . '"></i> ' . $status_text . '</span>';
            
            // Tanggal dibuat
            $row[] = date('d/m/Y H:i', strtotime($item->created_at));
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.slider.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" data-judul="'.$item->slider_judul.'" data-deskripsi="'.$item->slider_deskripsi.'" data-file="'.$item->slider_file.'" data-status="'.$item->slider_status.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.slider.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->id.'"><i class="fa fa-trash"></i></button>';
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
            ce_hak_akses('admin.slider.add');
        } else {
            ce_hak_akses('admin.slider.update');
        }
        
        $slider_judul = $this->input->post('slider_judul');
        $slider_deskripsi = $this->input->post('slider_deskripsi');
        $slider_status = $this->input->post('slider_status');

        // Validasi input
        if (empty($slider_judul)) {
            $response = [
                'status' => false,
                'message' => 'Judul slider tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($slider_status)) {
            $response = [
                'status' => false,
                'message' => 'Status harus dipilih'
            ];
            
            echo json_encode($response);
            return;
        }

        // Validasi status
        $status_config = $this->config->item('slider_status');
        if (!array_key_exists($slider_status, $status_config)) {
            $response = [
                'status' => false,
                'message' => 'Status tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'slider_judul' => $slider_judul,
            'slider_deskripsi' => $slider_deskripsi,
            'slider_status' => $slider_status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->slider_m->slider_insert_data($data);
            $message = 'Data slider berhasil ditambahkan';
            
            // Get inserted ID for response
            $inserted_id = $this->db->insert_id();
        } else {
            $result = $this->slider_m->slider_update_data($data, $id);
            $message = 'Data slider berhasil diperbarui';
            $inserted_id = $id;
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message,
                'slider_id' => $inserted_id
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data slider'
            ];
        }

        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.slider.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID slider tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Ambil data slider untuk hapus file
        $slider = $this->slider_m->slider_by_id($id);
        if ($slider && $slider->slider_file) {
            // Hapus file dari server
            $file_path = FCPATH . 'uploads/slider/' . $slider->slider_file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $result = $this->slider_m->slider_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data slider berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data slider'
            ];
        }

        
        echo json_encode($response);
    }

    public function upload_file()
    {
        ce_hak_akses('admin.slider.add');

        $slider_id = $this->input->post('slider_id');

        if (empty($slider_id)) {
            $response = [
                'status' => false,
                'message' => 'ID slider tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Validasi apakah slider ada
        $slider = $this->slider_m->slider_by_id($slider_id);
        if (!$slider) {
            $response = [
                'status' => false,
                'message' => 'Slider tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        // Konfigurasi upload
        $upload_path = './uploads/slider/';

        // Buat direktori jika belum ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|svg';
        $config['max_size'] = 2048; // 2MB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        // Check if file was uploaded
        if (empty($_FILES['slider_file']['name'])) {
            $response = [
                'status' => false,
                'message' => 'Tidak ada file yang diupload'
            ];
            
            echo json_encode($response);
            return;
        }

        // Hapus file lama jika ada
        if ($slider->slider_file) {
            $old_file_path = FCPATH . 'uploads/slider/' . $slider->slider_file;
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }

        if ($this->upload->do_upload('slider_file')) {
            $upload_data = $this->upload->data();

            // Update data slider dengan nama file baru
            $update_data = [
                'slider_file' => $upload_data['file_name'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->slider_m->slider_update_data($update_data, $slider_id)) {
                $response = [
                    'status' => true,
                    'message' => 'File berhasil diupload',
                    'data' => [
                        'file_name' => $upload_data['file_name'],
                        'file_path' => 'uploads/slider/' . $upload_data['file_name'],
                        'full_path' => base_url('uploads/slider/' . $upload_data['file_name']),
                        'size' => $upload_data['file_size']
                    ]
                ];
            } else {
                // Delete uploaded file if database update failed
                unlink($upload_data['full_path']);

                $response = [
                    'status' => false,
                    'message' => 'Gagal menyimpan data file ke database'
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => 'Upload gagal: ' . $this->upload->display_errors('', '')
            ];
        }

        
        echo json_encode($response);
    }

    public function delete_file()
    {
        ce_hak_akses('admin.slider.update');
        
        $slider_id = $this->input->post('slider_id');
        
        if (empty($slider_id)) {
            $response = [
                'status' => false,
                'message' => 'ID slider tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }
        
        // Get slider data first
        $slider = $this->slider_m->slider_by_id($slider_id);
        
        if (!$slider) {
            $response = [
                'status' => false,
                'message' => 'Slider tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }
        
        // Delete file from server if exists
        if ($slider->slider_file) {
            $file_path = FCPATH . 'uploads/slider/' . $slider->slider_file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Clear file from database
        $result = $this->slider_m->clear_slider_file($slider_id);
        
        $response = [
            'status' => $result,
            'message' => $result ? 'File berhasil dihapus' : 'Gagal menghapus file'
        ];
        
        
        echo json_encode($response);
    }

    public function toggle_status()
    {
        ce_hak_akses('admin.slider.update');
        
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        
        if (empty($id) || empty($status)) {
            $response = [
                'status' => false,
                'message' => 'Parameter tidak lengkap'
            ];
            
            echo json_encode($response);
            return;
        }
        
        // Validasi status
        if (!in_array($status, ['AKTIF', 'NON AKTIF'])) {
            $response = [
                'status' => false,
                'message' => 'Status tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }
        
        $result = $this->slider_m->update_slider_status($id, $status);
        $status_text = $status == 'AKTIF' ? 'diaktifkan' : 'dinonaktifkan';
        
        $response = [
            'status' => $result,
            'message' => $result ? "Slider berhasil {$status_text}" : 'Gagal mengubah status slider'
        ];
        
        echo json_encode($response);
    }

    /**
     * Get statistics for dashboard
     */
    public function get_statistics()
    {
        ce_hak_akses('admin.slider.view');
        
        $statistics = [
            'total' => $this->slider_m->count_all_slider(),
            'active' => $this->slider_m->count_active_slider(),
            'inactive' => $this->slider_m->count_inactive_slider()
        ];
        
        // Get last update
        $this->db->select_max('updated_at');
        $last_update = $this->db->get('master_slider')->row();
        $statistics['last_update'] = $last_update && $last_update->updated_at ? date('d/m', strtotime($last_update->updated_at)) : '-';
        
        $response = [
            'status' => true,
            'data' => $statistics
        ];
        
        echo json_encode($response);
    }
}