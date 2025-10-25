<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penyewaan_blok extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.penyewaan_blok.view');
    }

    public function index($pedagang_id = null)
    {
        ce_hak_akses('admin.penyewaan_blok.view');

        $data['header'] = 'Penyewaan <small>Blok Pasar</small>';
        $data['halaman'] = 'penyewaan_blok';
        $data['javascript'] = array(
            'penyewaan_blok/js_penyewaan_blok' => null
        );

        // Jika ada pedagang_id dari URL, tampilkan detail pedagang
        if ($pedagang_id) {
            $data['pedagang'] = $this->master_pedagang_m->pedagang_by_id($pedagang_id);
            $data['penyewaan_list'] = $this->penyewaan_blok_m->get_by_pedagang_id($pedagang_id);
            $data['pedagang_id'] = $pedagang_id;
        } else {
            $data['pedagang'] = null;
            $data['penyewaan_list'] = $this->penyewaan_blok_m->penyewaan_blok_get_all();
            $data['pedagang_id'] = null;
        }

        // Statistik
        $data['stats'] = $this->penyewaan_blok_m->get_statistics();

        $this->load->view('template', $data);
    }

    public function ajax_data($pedagang_id = null)
    {
        // Jika tidak ada pedagang_id dari URL, coba ambil dari POST
        if ($pedagang_id === null) {
            $pedagang_id = $this->input->post('pedagang_id');
        }
        
        ce_hak_akses('admin.penyewaan_blok.view');

        $dataConfig = array(
            'table' => 'penyewaan_blok pb',
            'select' => 'pb.id, pb.tanggal_sewa, pb.tanggal_mulai, pb.tanggal_selesai, pb.harga_sewa, pb.status, m.nama_lengkap, mpb.pasar_blok_nama, mpj.pasar_jenis_nama, mp.pasar_nama, mpu.pasar_unit_nomor',
            'join' => array(
                array('masyarakat_pedagang m', 'pb.pedagang_id = m.id', 'left'),
                array('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left'),
                array('master_pasar_unit mpu', 'pb.pasar_unit_id = mpu.pasar_unit_id', 'left'),
                array('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left'),
                array('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left')
            ),
            'order' => array('pb.id' => 'desc')
        );

        // Konfigurasi kolom berdasarkan apakah ada pedagang_id atau tidak
        if ($pedagang_id) {
            // Untuk halaman pedagang spesifik - tanpa kolom pedagang
            $dataConfig['column_order'] = array(null, 'pb.tanggal_sewa', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_nama', 'pb.tanggal_mulai', 'pb.tanggal_selesai', 'pb.harga_sewa', 'pb.status', null);
            $dataConfig['column_search'] = array('pb.tanggal_sewa', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_nama', 'pb.status');
        } else {
            // Untuk halaman umum - dengan kolom pedagang
            $dataConfig['column_order'] = array(null, 'pb.tanggal_sewa', 'm.nama_lengkap', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_nama', 'pb.tanggal_mulai', 'pb.tanggal_selesai', 'pb.harga_sewa', 'pb.status', null);
            $dataConfig['column_search'] = array('pb.tanggal_sewa', 'm.nama_lengkap', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_nama', 'pb.status');
        }

        // Filter berdasarkan pedagang_id
        $conditions = array();
        if ($pedagang_id) {
            $conditions['pb.pedagang_id'] = $pedagang_id;
        }

        // Filter berdasarkan status
        $filter_status = $this->input->post('filter_status');
        if ($filter_status !== '' && $filter_status !== null) {
            $conditions['pb.status'] = $filter_status;
        }

        if (!empty($conditions)) {
            $dataConfig['condition'] = $conditions;
        }

        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        $no = $this->input->post('start');
        
        // Initialize $data array
        $data = array();

        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = date('d/m/Y', strtotime($item->tanggal_sewa));

            if (!$pedagang_id) {
                $row[] = $item->nama_lengkap ?: '-';
            }

            // Pasar
            $row[] = $item->pasar_nama ?: '-';

            // Jenis Pasar
            $row[] = $item->pasar_jenis_nama ?: '-';

            // Blok/Unit
            if ($item->pasar_unit_nomor) {
                $row[] = $item->pasar_blok_nama . ' - Unit ' . $item->pasar_unit_nomor;
            } else {
                $row[] = $item->pasar_blok_nama ?: '-';
            }

            // Periode
            $periode = date('d/m/Y', strtotime($item->tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($item->tanggal_selesai));
            $row[] = $periode;

            // Harga Sewa
            $row[] = 'Rp ' . number_format($item->harga_sewa, 0, ',', '.');

            // Status
            $status_config = $this->config->item('rental_status');
            $status_labels = $this->config->item('rental_status_labels');
            $status_label = isset($status_labels[$item->status]) ? $status_labels[$item->status] : 'label-default';
            $status_text = isset($status_config[$item->status]) ? $status_config[$item->status] : $item->status;
            $row[] = '<span class="label ' . $status_label . '">' . $status_text . '</span>';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.penyewaan_blok.view')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="'.$item->id.'" title="Detail"><i class="fa fa-eye"></i></button>';
            }
            if (ce_hak_akses('admin.penyewaan_blok.edit')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="'.$item->id.'" title="Edit"><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.penyewaan_blok.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->id.'" title="Hapus"><i class="fa fa-trash"></i></button>';
            }
            $aksi .= '</div>';

            $row[] = $aksi;
            $data[] = $row;
        }

        // Ensure $data is always an array
        if (!isset($data)) {
            $data = array();
        }
        
        $output = array(
            "draw" => $this->input->post('draw') ?: 1,
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data
        );

        // Set proper JSON header
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.penyewaan_blok.add');
        } else {
            ce_hak_akses('admin.penyewaan_blok.edit');
        }

        $data = array(
            'pedagang_id' => $this->input->post('pedagang_id'),
            'pasar_blok_id' => $this->input->post('pasar_blok_id'), // Keep for backward compatibility
            'pasar_unit_id' => $this->input->post('pasar_unit_id'), // New field
            'tanggal_sewa' => $this->input->post('tanggal_sewa'),
            'tanggal_mulai' => $this->input->post('tanggal_mulai'),
            'tanggal_selesai' => $this->input->post('tanggal_selesai'),
            'harga_sewa' => $this->input->post('harga_sewa'),
            'status' => $this->input->post('status') ?: 'MENUNGGU'
        );

        // Validasi data
        $errors = $this->penyewaan_blok_m->validate_penyewaan_data($data, $id);
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
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->penyewaan_blok_m->penyewaan_blok_update_data($data, $id);
            $message = 'Data penyewaan berhasil diperbarui';

            // Jika status diubah menjadi AKTIF, update status unit/blok menjadi TERISI
            if ($data['status'] == 'AKTIF') {
                // Update unit status jika ada unit_id
                if (!empty($data['pasar_unit_id'])) {
                    $this->db->where('pasar_unit_id', $data['pasar_unit_id']);
                    $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERISI']);
                }
                // Update blok status (backward compatibility)
                if (!empty($data['pasar_blok_id'])) {
                    $this->db->where('pasar_blok_id', $data['pasar_blok_id']);
                    $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERISI']);
                }
            }
            // Jika status diubah menjadi SELESAI atau BATAL, update status unit/blok menjadi TERSEDIA
            elseif (in_array($data['status'], ['SELESAI', 'BATAL'])) {
                // Update unit status jika ada unit_id
                if (!empty($data['pasar_unit_id'])) {
                    $this->db->where('pasar_unit_id', $data['pasar_unit_id']);
                    $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERSEDIA']);
                }
                // Update blok status (backward compatibility)
                if (!empty($data['pasar_blok_id'])) {
                    $this->db->where('pasar_blok_id', $data['pasar_blok_id']);
                    $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERSEDIA']);
                }
            }
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->penyewaan_blok_m->penyewaan_blok_insert_data($data);
            
            // Jika penyewaan berhasil dibuat dan status adalah AKTIF, update status unit/blok
            if ($result && $data['status'] == 'AKTIF') {
                // Update unit status jika ada unit_id
                if (!empty($data['pasar_unit_id'])) {
                    $this->db->where('pasar_unit_id', $data['pasar_unit_id']);
                    $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERISI']);
                }
                // Update blok status (backward compatibility)
                if (!empty($data['pasar_blok_id'])) {
                    $this->db->where('pasar_blok_id', $data['pasar_blok_id']);
                    $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERISI']);
                }
            }
            
            $message = 'Data penyewaan berhasil ditambahkan';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data penyewaan'
            ];
        }

        echo json_encode($response);
    }

    public function get_by_id()
    {
        ce_hak_akses('admin.penyewaan_blok.view');

        $id = $this->input->post('id');
        $data = $this->penyewaan_blok_m->penyewaan_blok_by_id($id);

        $response = [
            'status' => true,
            'data' => $data
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.penyewaan_blok.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            echo json_encode($response);
            return;
        }

        // Get penyewaan data untuk update status unit/blok
        $penyewaan = $this->penyewaan_blok_m->penyewaan_blok_by_id($id);
        if ($penyewaan) {
            // Update status unit menjadi TERSEDIA jika ada unit_id
            if (!empty($penyewaan->pasar_unit_id)) {
                $this->db->where('pasar_unit_id', $penyewaan->pasar_unit_id);
                $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERSEDIA']);
            }
            // Update status blok menjadi TERSEDIA (backward compatibility)
            if (!empty($penyewaan->pasar_blok_id)) {
                $this->db->where('pasar_blok_id', $penyewaan->pasar_blok_id);
                $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERSEDIA']);
            }
        }

        $result = $this->penyewaan_blok_m->penyewaan_blok_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data penyewaan berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data penyewaan'
            ];
        }

        echo json_encode($response);
    }

    public function get_available_blocks()
    {
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        $pasar_id = $this->input->post('pasar_id');
        $jenis_id = $this->input->post('jenis_id');

        $blocks = $this->penyewaan_blok_m->get_available_blocks($tanggal_mulai, $tanggal_selesai, $pasar_id, $jenis_id);

        $response = [
            'status' => true,
            'data' => $blocks
        ];

        echo json_encode($response);
    }

    public function get_denah_blocks()
    {
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        $jenis_id = $this->input->post('jenis_id');

        // Get blocks with units
        $blocks = $this->penyewaan_blok_m->get_blocks_with_units($jenis_id, $tanggal_mulai, $tanggal_selesai);

        // Debug: Log the blocks data
        error_log('get_denah_blocks: Retrieved ' . count($blocks) . ' blocks');
        foreach ($blocks as $block) {
            error_log('Block: ' . $block->pasar_blok_nama . ' - ' . $block->pasar_blok_nomor . ' (ID: ' . $block->pasar_blok_id . ')');
        }

        // Get pasar info
        $pasar_info = null;
        if (!empty($blocks)) {
            $pasar_info = (object) [
                'pasar_nama' => $blocks[0]->pasar_nama,
                'pasar_jenis_nama' => $blocks[0]->pasar_jenis_nama
            ];
        }

        $response = [
            'status' => true,
            'data' => $blocks,
            'pasar_info' => $pasar_info
        ];

        echo json_encode($response);
    }

    // New method to get denah units for a specific block
    public function get_denah_units()
    {
        $blok_id = $this->input->post('blok_id');
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');

        if (empty($blok_id)) {
            echo json_encode(['status' => false, 'message' => 'Blok ID harus diisi']);
            return;
        }

        // Load master_pasar_unit model
        $this->load->model('master_pasar_blok/Master_pasar_unit_m');

        // Get block info
        $this->db->select('mpb.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_blok mpb');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('mpb.pasar_blok_id', $blok_id);
        $block = $this->db->get()->row();

        if (!$block) {
            echo json_encode(['status' => false, 'message' => 'Blok tidak ditemukan']);
            return;
        }

        // Get units for this block
        $units = $this->Master_pasar_unit_m->get_units_by_blok_id($blok_id);

        // Debug: Log the number of units retrieved
        error_log('get_denah_units: Retrieved ' . count($units) . ' units for block ' . $blok_id);

        // Check availability for each unit
        foreach ($units as &$unit) {
            $unit->is_available = $this->penyewaan_blok_m->check_unit_available(
                $unit->pasar_unit_id,
                $tanggal_mulai,
                $tanggal_selesai
            );
            
            // Debug: Log each unit's availability
            error_log('Unit ' . $unit->pasar_unit_nomor . ' (ID: ' . $unit->pasar_unit_id . ') is_available: ' . ($unit->is_available ? 'true' : 'false'));
        }

        $response = [
            'status' => true,
            'data' => [
                'block' => $block,
                'units' => $units
            ]
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

    public function get_jenis_by_pasar()
    {
        $pasar_id = $this->input->post('pasar_id');
        $jenis_list = $this->master_jenis_pasar_m->get_by_pasar_id($pasar_id);
        $options = '<option value="">Pilih Jenis Pasar</option>';

        foreach ($jenis_list as $jenis) {
            $options .= '<option value="' . $jenis->pasar_jenis_id . '">' . $jenis->pasar_jenis_nama . '</option>';
        }

        echo $options;
    }

    public function get_pedagang_options()
    {
        $pedagang_list = $this->master_pedagang_m->pedagang_get_active();
        $options = '<option value="">Pilih Pedagang</option>';

        foreach ($pedagang_list as $pedagang) {
            $options .= '<option value="' . $pedagang->id . '">' . $pedagang->nama_lengkap . ' (' . $pedagang->nik . ')</option>';
        }

        echo $options;
    }

    public function update_status()
    {
        ce_hak_akses('admin.penyewaan_blok.edit');

        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (empty($id) || empty($status)) {
            $response = [
                'status' => false,
                'message' => 'ID dan status harus diisi'
            ];
            echo json_encode($response);
            return;
        }

        // Get penyewaan data
        $penyewaan = $this->penyewaan_blok_m->penyewaan_blok_by_id($id);
        if (!$penyewaan) {
            $response = [
                'status' => false,
                'message' => 'Data penyewaan tidak ditemukan'
            ];
            echo json_encode($response);
            return;
        }

        $result = $this->penyewaan_blok_m->update_status($id, $status);

        if ($result) {
            // Update status unit/blok berdasarkan status penyewaan
            if ($status == 'AKTIF') {
                // Update unit status jika ada unit_id
                if (!empty($penyewaan->pasar_unit_id)) {
                    $this->db->where('pasar_unit_id', $penyewaan->pasar_unit_id);
                    $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERISI']);
                }
                // Update blok status (backward compatibility)
                if (!empty($penyewaan->pasar_blok_id)) {
                    $this->db->where('pasar_blok_id', $penyewaan->pasar_blok_id);
                    $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERISI']);
                }
            } elseif (in_array($status, ['SELESAI', 'BATAL'])) {
                // Update unit status jika ada unit_id
                if (!empty($penyewaan->pasar_unit_id)) {
                    $this->db->where('pasar_unit_id', $penyewaan->pasar_unit_id);
                    $this->db->update('master_pasar_unit', ['pasar_unit_status' => 'TERSEDIA']);
                }
                // Update blok status (backward compatibility)
                if (!empty($penyewaan->pasar_blok_id)) {
                    $this->db->where('pasar_blok_id', $penyewaan->pasar_blok_id);
                    $this->db->update('master_pasar_blok', ['pasar_blok_status' => 'TERSEDIA']);
                }
            }

            $response = [
                'status' => true,
                'message' => 'Status penyewaan berhasil diperbarui'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal memperbarui status penyewaan'
            ];
        }

        echo json_encode($response);
    }
}