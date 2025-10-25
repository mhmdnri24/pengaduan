<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fasilitas_umum extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.fasilitas_umum.view');
    }

    public function index()
    {
        ce_hak_akses('admin.fasilitas_umum.view');
        $data['header'] = 'Fasilitas <small>Umum</small>';
        $data['halaman'] = 'fasilitas_umum';
        $data['javascript'] = array(
            'fasilitas_umum/js_fasilitas_umum' => null
        );
        $this->load->view('template', $data);
    }



    public function ajax_data()
    {
        ce_hak_akses('admin.fasilitas_umum.view');

        $dataConfig = [
            'table' => 'fasilitas_umum fu',
            'select' => 'fu.*, mk.nama_kategori, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan',
            'column_order' => [null, 'fu.nama_fasilitas', 'mk.nama_kategori', 'fu.alamat', 'kl.nama_kelurahan', 'kc.nama_kecamatan', 'kt.nama_kota', 'fu.telepon', 'fu.status', null],
            'column_search' => ['fu.nama_fasilitas', 'mk.nama_kategori', 'fu.alamat', 'kl.nama_kelurahan', 'kc.nama_kecamatan', 'kt.nama_kota', 'fu.telepon'],
            'join' => [
                ['master_kategori mk', 'fu.kategori_id=mk.id', 'left'],
                ['kota kt', 'fu.id_kota=kt.id_kota', 'left'],
                ['kecamatan kc', 'fu.id_kecamatan=kc.id_kecamatan', 'left'],
                ['kelurahan kl', 'fu.id_kelurahan=kl.id_kelurahan', 'left']
            ],
            'order' => ['fu.id' => 'desc']
        ];

        // Filter berdasarkan kategori
        $kategori = $this->input->post('kategori');
        if ($kategori !== '' && $kategori !== null) {
            $dataConfig['condition']['fu.kategori_id'] = $kategori;
        }

        // Filter berdasarkan kecamatan
        $kecamatan = $this->input->post('kecamatan');
        if ($kecamatan !== '' && $kecamatan !== null) {
            $dataConfig['condition']['fu.id_kecamatan'] = $kecamatan;
        }

        // Filter berdasarkan kelurahan
        $kelurahan = $this->input->post('kelurahan');
        if ($kelurahan !== '' && $kelurahan !== null) {
            $dataConfig['condition']['fu.id_kelurahan'] = $kelurahan;
        }

        // Filter berdasarkan status
        $status = $this->input->post('status');
        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['fu.status'] = $status;
        }

        // Default filter untuk Kota Palembang
        $dataConfig['condition']['fu.id_kota'] = '16.73';
        
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->nama_fasilitas;
            $row[] = $item->nama_kategori ?: '-';
            $row[] = $item->alamat ? substr($item->alamat, 0, 50) . '...' : '-';
            $row[] = $item->nama_kelurahan ?: '-';
            $row[] = $item->telepon ?: '-';
            $status_label = $item->status == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Nonaktif</span>';
            $row[] = $status_label;
            
            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.fasilitas_umum.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" data-nama="'.$item->nama_fasilitas.'" data-kategori="'.$item->kategori_id.'" data-deskripsi="'.$item->deskripsi.'" data-alamat="'.$item->alamat.'" data-kecamatan="'.$item->id_kecamatan.'" data-kelurahan="'.$item->id_kelurahan.'" data-telepon="'.$item->telepon.'" data-latitude="'.$item->latitude.'" data-longitude="'.$item->longitude.'" data-status="'.$item->status.'"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.fasilitas_umum.delete')) {
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
            ce_hak_akses('admin.fasilitas_umum.add');
        } else {
            ce_hak_akses('admin.fasilitas_umum.update');
        }
        
        $nama_fasilitas = $this->input->post('nama_fasilitas');
        $kategori_id = $this->input->post('kategori_id');
        $deskripsi = $this->input->post('deskripsi');
        $alamat = $this->input->post('alamat');
        $id_kecamatan = $this->input->post('id_kecamatan');
        $id_kelurahan = $this->input->post('id_kelurahan');
        $telepon = $this->input->post('telepon');
        $status = $this->input->post('status');

        if (empty($nama_fasilitas)) {
            $response = [
                'status' => false,
                'message' => 'Nama Fasilitas tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($kategori_id)) {
            $response = [
                'status' => false,
                'message' => 'Kategori harus dipilih'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'nama_fasilitas' => $nama_fasilitas,
            'kategori_id' => $kategori_id,
            'deskripsi' => $deskripsi,
            'alamat' => $alamat,
            'id_kota' => '16.73', // Default Kota Palembang
            'id_kecamatan' => $id_kecamatan,
            'id_kelurahan' => $id_kelurahan,
            'telepon' => $telepon,
            'status' => $status ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Ambil koordinat jika ada
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        
        if (!empty($latitude) && !empty($longitude)) {
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
        }

        if (empty($id)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->fasilitas_umum_m->fasilitas_umum_insert_data($data);
            $message = 'Data fasilitas umum berhasil ditambahkan';
            
            // Get inserted ID for response
            $inserted_id = $this->db->insert_id();
        } else {
            $result = $this->fasilitas_umum_m->fasilitas_umum_update_data($data, $id);
            $message = 'Data fasilitas umum berhasil diperbarui';
            $inserted_id = $id;
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message,
                'fasilitas_id' => $inserted_id
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data fasilitas umum'
            ];
        }

        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.fasilitas_umum.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID fasilitas umum tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        $result = $this->fasilitas_umum_m->fasilitas_umum_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data fasilitas umum berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data fasilitas umum'
            ];
        }

        
        echo json_encode($response);
    }

    // ==== FOTO METHODS ====

    /**
     * Upload multiple photos
     */
    public function upload_foto()
    {
        ce_hak_akses('admin.fasilitas_umum.add');

        $fasilitas_id = $this->input->post('fasilitas_id');

        if (empty($fasilitas_id)) {
            $response = [
                'status' => false,
                'message' => 'ID fasilitas tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Validasi apakah fasilitas ada
        $fasilitas = $this->fasilitas_umum_m->fasilitas_umum_by_id($fasilitas_id);
        if (!$fasilitas) {
            $response = [
                'status' => false,
                'message' => 'Fasilitas tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }

        // Konfigurasi upload
        $upload_path = './uploads/fasilitas_umum/' . $fasilitas_id . '/';

        // Buat direktori jika belum ada
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;
        $config['remove_spaces'] = TRUE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        // Check if file was uploaded
        if (empty($_FILES['foto']['name'])) {
            $response = [
                'status' => false,
                'message' => 'Tidak ada file yang diupload'
            ];
            
            echo json_encode($response);
            return;
        }

        // Dropzone sends files one at a time with 'foto' as the field name
        if ($this->upload->do_upload('foto')) {
            $upload_data = $this->upload->data();

            // Simpan data foto ke database
            $foto_data = [
                'fasilitas_id' => $fasilitas_id,
                'nama_file' => $upload_data['file_name'],
                'path_file' => 'uploads/fasilitas_umum/' . $fasilitas_id . '/' . $upload_data['file_name'],
                'ukuran_file' => $upload_data['file_size'],
                'mime_type' => $upload_data['file_type'],
                'urutan' => $this->fasilitas_umum_m->count_fotos_by_fasilitas($fasilitas_id) + 1,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->fasilitas_umum_m->insert_foto($foto_data)) {
                $photo_id = $this->db->insert_id();

                $response = [
                    'status' => true,
                    'message' => 'Foto berhasil diupload',
                    'photo_id' => $photo_id,
                    'data' => [
                        'name' => $upload_data['orig_name'],
                        'file_name' => $upload_data['file_name'],
                        'size' => $upload_data['file_size'],
                        'url' => base_url($foto_data['path_file'])
                    ]
                ];
            } else {
                // Delete uploaded file if database insert failed
                unlink($upload_data['full_path']);

                $response = [
                    'status' => false,
                    'message' => 'Gagal menyimpan data foto ke database'
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

    /**
     * Get photos for a facility
     */
    public function get_fotos()
    {
        ce_hak_akses('admin.fasilitas_umum.view');
        
        $fasilitas_id = $this->input->post('fasilitas_id');
        
        if (empty($fasilitas_id)) {
            $response = [
                'status' => false,
                'message' => 'ID fasilitas tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }
        
        $fotos = $this->fasilitas_umum_m->get_fotos_by_fasilitas($fasilitas_id);
        
        // Add full URL to each photo
        $fotos_with_url = [];
        foreach ($fotos as $foto) {
            $fotos_with_url[] = [
                'id' => $foto->id,
                'nama_file' => $foto->nama_file,
                'keterangan' => $foto->keterangan,
                'urutan' => $foto->urutan,
                'file_url' => base_url($foto->path_file)
            ];
        }
        
        $response = [
            'status' => true,
            'fotos' => $fotos_with_url
        ];
        
        
        echo json_encode($response);
    }

    /**
     * Delete a photo
     */
    public function delete_foto()
    {
        ce_hak_akses('admin.fasilitas_umum.delete');
        
        $foto_id = $this->input->post('foto_id');
        
        if (empty($foto_id)) {
            $response = [
                'status' => false,
                'message' => 'ID foto tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }
        
        // Get foto data first
        $foto = $this->fasilitas_umum_m->get_foto_by_id($foto_id);
        
        if (!$foto) {
            $response = [
                'status' => false,
                'message' => 'Foto tidak ditemukan'
            ];
            
            echo json_encode($response);
            return;
        }
        
        // Delete file from server
        $file_path = FCPATH . $foto->path_file;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $result = $this->fasilitas_umum_m->delete_foto($foto_id);
        
        $response = [
            'status' => $result,
            'message' => $result ? 'Foto berhasil dihapus' : 'Gagal menghapus foto'
        ];
        
        
        echo json_encode($response);
    }

    /**
     * Update photo caption
     */
    public function update_foto_keterangan()
    {
        ce_hak_akses('admin.fasilitas_umum.update');
        
        $foto_id = $this->input->post('foto_id');
        $keterangan = $this->input->post('keterangan');
        
        if (empty($foto_id)) {
            $response = [
                'status' => false,
                'message' => 'ID foto tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }
        
        $result = $this->fasilitas_umum_m->update_foto_keterangan($foto_id, $keterangan);
        
        $response = [
            'status' => $result,
            'message' => $result ? 'Keterangan foto berhasil diperbarui' : 'Gagal memperbarui keterangan foto'
        ];
        
        
        echo json_encode($response);
    }
}
