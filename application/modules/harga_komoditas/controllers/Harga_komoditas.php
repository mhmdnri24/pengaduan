<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Harga_komoditas extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Skip authentication for test methods
        $method = $this->router->fetch_method();
        if (!in_array($method, ['test_hierarchical_data', 'debug_ajax_data'])) {
            if (!$this->session->user_login) {
                redirect('user/login');
                exit;
            }
            ce_active_menu('admin.harga_komoditas.view');
        }
        // Load models explicitly for HMVC
    }

    public function index()
    {
        ce_hak_akses('admin.harga_komoditas.view');
        
        $data['header'] = 'Input Harga <small>Komoditas Harian</small>';
        $data['halaman'] = 'harga_komoditas/harga_komoditas';
        $data['javascript'] = array(
            'harga_komoditas/js_harga_komoditas' => null
        );
        
        // Get data untuk dropdown
        $data['komoditas_list'] = $this->komoditas_m->komoditas_get_active();
        $data['pasar_list'] = $this->master_pasar_m->master_pasar_get_active();
        $data['komoditas_tree'] = $this->komoditas_m->get_komoditas_tree();
        
        // Get statistik
        $data['statistik'] = $this->harga_komoditas_m->get_statistik_harga();
        $data['top_volatil'] = $this->harga_komoditas_m->get_top_komoditas_volatil();
        
        $this->load->view('template', $data);
    }

    public function dashboard()
    {
        ce_hak_akses('admin.harga_komoditas.view');
        
        $data['header'] = 'Dashboard <small>Harga Komoditas</small>';
        $data['halaman'] = 'harga_komoditas/dashboard_harga';
        $data['javascript'] = array(
            'harga_komoditas/js_dashboard_harga' => null
        );
        
        // Get data untuk dashboard
        $data['statistik'] = $this->harga_komoditas_m->get_statistik_harga();
        $data['top_volatil'] = $this->harga_komoditas_m->get_top_komoditas_volatil();
        $data['komoditas_list'] = $this->komoditas_m->komoditas_get_active();
        $data['pasar_list'] = $this->master_pasar_m->master_pasar_get_active();
        
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.harga_komoditas.view');
        
        $table = 'view_harga_komoditas_lengkap';
        $column_order = [
            null, 
            'harga_tanggal', 
            'komoditas_nama', 
            'kategori_nama',
            'pasar_nama', 
            'harga_beli', 
            'harga_jual', 
            'harga_rata_rata',
            'stok_tersedia',
            'kualitas',
            'harga_status', 
            null
        ];
        $column_search = [
            'komoditas_nama', 
            'kategori_nama',
            'pasar_nama', 
            'stok_tersedia',
            'kualitas'
        ];
        $order = ['harga_tanggal' => 'desc', 'harga_id' => 'desc'];

        // Filter berdasarkan parameter
        $where = [];
        
        $tanggal_dari = $this->input->post('tanggal_dari');
        $tanggal_sampai = $this->input->post('tanggal_sampai');
        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');
        $status = $this->input->post('status');
        
        if ($tanggal_dari) {
            $where['harga_tanggal >='] = $tanggal_dari;
        }
        
        if ($tanggal_sampai) {
            $where['harga_tanggal <='] = $tanggal_sampai;
        }
        
        if ($komoditas_id) {
            $where['komoditas_id'] = $komoditas_id;
        }
        
        if ($pasar_id) {
            $where['pasar_id'] = $pasar_id;
        }
        
        if ($status !== '' && $status !== null) {
            $where['harga_status'] = $status;
        } else {
            $where['harga_status'] = 1; // Default hanya tampilkan yang aktif
        }

        $list = $this->harga_komoditas_m->get_datatables($table, $column_order, $column_search, '*', null, $where, $order);
        $data = [];
        $no = $_POST['start'];

        foreach ($list as $item) {
            $no++;
            $row = [];

            // Nomor
            $row[] = $no;

            // Tanggal
            $row[] = date('d/m/Y', strtotime($item->harga_tanggal));

            // Komoditas
            $komoditas_display = $item->komoditas_nama;
            if ($item->komoditas_satuan) {
                $komoditas_display .= ' <small class="text-muted">(' . $item->komoditas_satuan . ')</small>';
            }
            $row[] = $komoditas_display;

            // Kategori
            $row[] = $item->kategori_nama ?: '<span class="text-muted">-</span>';

            // Pasar
            $row[] = $item->pasar_nama;

            // Harga Beli
            $harga_beli = $item->harga_beli ? 'Rp ' . number_format($item->harga_beli, 0, ',', '.') : '<span class="text-muted">-</span>';
            $row[] = $harga_beli;

            // Harga Jual
            $harga_jual = 'Rp ' . number_format($item->harga_jual, 0, ',', '.');
            $row[] = $harga_jual;

            // Harga Rata-rata
            $harga_rata = 'Rp ' . number_format($item->harga_rata_rata, 0, ',', '.');
            $row[] = '<strong>' . $harga_rata . '</strong>';

            // Stok
            $stok_class = '';
            switch ($item->stok_tersedia) {
                case 'TERSEDIA':
                    $stok_class = 'label-success';
                    break;
                case 'TERBATAS':
                    $stok_class = 'label-warning';
                    break;
                case 'KOSONG':
                    $stok_class = 'label-danger';
                    break;
            }
            $row[] = '<span class="label ' . $stok_class . '">' . $item->stok_tersedia . '</span>';

            // Kualitas
            $kualitas_class = '';
            switch ($item->kualitas) {
                case 'BAIK':
                    $kualitas_class = 'label-success';
                    break;
                case 'SEDANG':
                    $kualitas_class = 'label-warning';
                    break;
                case 'KURANG':
                    $kualitas_class = 'label-danger';
                    break;
            }
            $row[] = '<span class="label ' . $kualitas_class . '">' . $item->kualitas . '</span>';

            // Status
            $status_label = $item->harga_status == 1 ? 'label-success' : 'label-danger';
            $status_text = $item->harga_status == 1 ? 'Aktif' : 'Non-Aktif';
            $row[] = '<span class="label ' . $status_label . '">' . $status_text . '</span>';

            // Aksi
            $aksi = '<div class="btn-group">';
            if (ce_cek_hak_akses('admin.harga_komoditas.update')) {
                $aksi .= '<button type="button" class="btn btn-warning btn-xs btn-edit" data-id="' . $item->harga_id . '" title="Edit">
                            <i class="fa fa-edit"></i>
                          </button> ';
            }
            
            if (ce_cek_hak_akses('admin.harga_komoditas.add')) {
                $aksi .= '<button type="button" class="btn btn-info btn-xs btn-copy" data-id="' . $item->harga_id . '" title="Copy">
                            <i class="fa fa-copy"></i>
                          </button> ';
            }
            
            if (ce_cek_hak_akses('admin.harga_komoditas.delete')) {
                $aksi .= '<button type="button" class="btn btn-danger btn-xs btn-delete" data-id="' . $item->harga_id . '" title="Hapus">
                            <i class="fa fa-trash"></i>
                          </button>';
            }
            $aksi .= '</div>';
            
            $row[] = $aksi;

            $data[] = $row;
        }

        $output = [
            'draw' => $_POST['draw'],
            'recordsTotal' => $this->harga_komoditas_m->count_all($table),
            'recordsFiltered' => $this->harga_komoditas_m->count_filtered($table, $column_order, $column_search, '*', null, $where, $order),
            'data' => $data
        ];

        echo json_encode($output);
    }

    public function get_by_id()
    {
        ce_hak_akses('admin.harga_komoditas.view');
        
        $id = $this->input->post('id');
        $harga = $this->harga_komoditas_m->harga_komoditas_by_id($id);
        
        if ($harga) {
            $response = [
                'status' => true,
                'data' => $harga
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Data harga komoditas tidak ditemukan'
            ];
        }

        echo json_encode($response);
    }

    public function save($id = null)
    {
        $hak_akses = empty($id) ? 'admin.harga_komoditas.add' : 'admin.harga_komoditas.update';
        ce_hak_akses($hak_akses);
        
        $this->form_validation->set_rules('harga_tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('komoditas_id', 'Komoditas', 'required|numeric');
        $this->form_validation->set_rules('pasar_id', 'Pasar', 'required|numeric');
        $this->form_validation->set_rules('harga_jual', 'Harga Jual', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('harga_beli', 'Harga Beli', 'numeric');
        $this->form_validation->set_rules('stok_tersedia', 'Stok', 'required|in_list[TERSEDIA,TERBATAS,KOSONG]');
        $this->form_validation->set_rules('kualitas', 'Kualitas', 'required|in_list[BAIK,SEDANG,KURANG]');
        $this->form_validation->set_rules('harga_status', 'Status', 'required|in_list[0,1]');
        
        if ($this->form_validation->run() == FALSE) {
            $response = ['status' => false, 'message' => validation_errors()];
        } else {
            $data = [
                'harga_tanggal' => $this->input->post('harga_tanggal'),
                'komoditas_id' => $this->input->post('komoditas_id'),
                'pasar_id' => $this->input->post('pasar_id'),
                'harga_beli' => $this->input->post('harga_beli') ?: null,
                'harga_jual' => $this->input->post('harga_jual'),
                'stok_tersedia' => $this->input->post('stok_tersedia'),
                'kualitas' => $this->input->post('kualitas'),
                'catatan' => $this->input->post('catatan'),
                'harga_status' => $this->input->post('harga_status'),
                'harga_updated_at' => date('Y-m-d H:i:s'),
                'harga_updated_by' => $this->session->userdata('id_user')
            ];

            // Validasi data
            $errors = $this->harga_komoditas_m->validate_harga_data($data, $id);
            if (!empty($errors)) {
                $response = ['status' => false, 'message' => implode('<br>', $errors)];
            } else {
                if (empty($id)) {
                    $data['harga_created_at'] = date('Y-m-d H:i:s');
                    $data['harga_created_by'] = $this->session->userdata('id_user');
                    $result = $this->harga_komoditas_m->harga_komoditas_insert_data($data);
                    $message = 'Data harga komoditas berhasil ditambahkan';
                } else {
                    $result = $this->harga_komoditas_m->harga_komoditas_update_data($data, $id);
                    $message = 'Data harga komoditas berhasil diupdate';
                }

                if ($result) {
                    $response = ['status' => true, 'message' => $message];
                } else {
                    $response = ['status' => false, 'message' => 'Gagal menyimpan data harga komoditas'];
                }
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function copy()
    {
        ce_hak_akses('admin.harga_komoditas.add');
        
        $id = $this->input->post('id');
        $harga = $this->harga_komoditas_m->harga_komoditas_by_id($id);
        
        if (!$harga) {
            $response = ['status' => false, 'message' => 'Data harga tidak ditemukan'];
        } else {
            $data = [
                'harga_tanggal' => date('Y-m-d'), // Set ke hari ini
                'komoditas_id' => $harga->komoditas_id,
                'pasar_id' => $harga->pasar_id,
                'harga_beli' => $harga->harga_beli,
                'harga_jual' => $harga->harga_jual,
                'stok_tersedia' => $harga->stok_tersedia,
                'kualitas' => $harga->kualitas,
                'catatan' => $harga->catatan . ' (Copy)',
                'harga_status' => $harga->harga_status,
                'harga_created_at' => date('Y-m-d H:i:s'),
                'harga_created_by' => $this->session->userdata('id_user'),
                'harga_updated_at' => date('Y-m-d H:i:s'),
                'harga_updated_by' => $this->session->userdata('id_user')
            ];
            
            // Check duplikasi untuk tanggal hari ini
            $errors = $this->harga_komoditas_m->validate_harga_data($data);
            if (!empty($errors)) {
                $response = ['status' => false, 'message' => implode('<br>', $errors)];
            } else {
                $result = $this->harga_komoditas_m->harga_komoditas_insert_data($data);
                if ($result) {
                    $response = ['status' => true, 'message' => 'Data harga berhasil dicopy untuk hari ini'];
                } else {
                    $response = ['status' => false, 'message' => 'Gagal copy data harga'];
                }
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.harga_komoditas.delete');
        
        $id = $this->input->post('id');
        
        $result = $this->harga_komoditas_m->harga_komoditas_delete_data($id);
        if ($result) {
            $response = ['status' => true, 'message' => 'Data harga komoditas berhasil dihapus'];
        } else {
            $response = ['status' => false, 'message' => 'Gagal menghapus data harga komoditas'];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    // =============================================
    // AJAX Methods untuk Analisis dan Chart
    // =============================================

    public function ajax_perbandingan_pasar()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $komoditas_id = $this->input->post('komoditas_id');
        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');

        if (!$komoditas_id) {
            $response = ['status' => false, 'message' => 'Komoditas harus dipilih'];
        } else {
            $data = $this->harga_komoditas_m->get_perbandingan_harga_antar_pasar($komoditas_id, $tanggal);
            $response = [
                'status' => true,
                'data' => $data,
                'tanggal' => $tanggal
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function ajax_trend_harga()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');
        $days = $this->input->post('days') ?: 30;

        if (!$komoditas_id) {
            $response = ['status' => false, 'message' => 'Komoditas harus dipilih'];
        } else {
            $data = $this->harga_komoditas_m->get_trend_harga_komoditas($komoditas_id, $pasar_id, $days);

            // Format data untuk Chart.js
            $labels = [];
            $prices = [];

            foreach ($data as $item) {
                $labels[] = date('d/m', strtotime($item->harga_tanggal));
                $prices[] = (float) $item->harga_rata_rata;
            }

            $response = [
                'status' => true,
                'labels' => $labels,
                'data' => $prices,
                'komoditas_nama' => !empty($data) ? $data[0]->komoditas_nama : '',
                'pasar_nama' => !empty($data) ? $data[0]->pasar_nama : 'Semua Pasar'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function ajax_chart_data()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');
        $days = $this->input->post('days') ?: 30;

        if (!$komoditas_id) {
            $response = ['status' => false, 'message' => 'Komoditas harus dipilih'];
        } else {
            $data = $this->harga_komoditas_m->get_harga_untuk_chart($komoditas_id, $pasar_id, $days);

            $labels = [];
            $prices = [];

            foreach ($data as $item) {
                $labels[] = date('d/m/Y', strtotime($item->tanggal));
                $prices[] = (float) $item->harga_rata;
            }

            $response = [
                'status' => true,
                'labels' => $labels,
                'data' => $prices
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function ajax_analisis_data()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $filters = [
            'tanggal_dari' => $this->input->post('tanggal_dari'),
            'tanggal_sampai' => $this->input->post('tanggal_sampai'),
            'komoditas_id' => $this->input->post('komoditas_id'),
            'pasar_id' => $this->input->post('pasar_id'),
            'trend_status' => $this->input->post('trend_status')
        ];

        $data = $this->harga_komoditas_m->get_analisis_by_filter($filters);

        $response = [
            'status' => true,
            'data' => $data
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    // =============================================
    // Utility Methods
    // =============================================

    public function check_duplicate()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $tanggal = $this->input->post('tanggal');
        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');
        $exclude_id = $this->input->post('exclude_id');

        $is_duplicate = $this->harga_komoditas_m->check_duplicate_harga($tanggal, $komoditas_id, $pasar_id, $exclude_id);

        $response = [
            'status' => true,
            'is_duplicate' => $is_duplicate
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function get_komoditas_by_kategori()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $kategori_id = $this->input->post('kategori_id');

        if ($kategori_id) {
            $komoditas = $this->komoditas_m->komoditas_get_children($kategori_id);
        } else {
            $komoditas = $this->komoditas_m->komoditas_get_active();
        }

        $response = [
            'status' => true,
            'data' => $komoditas
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function execute_analisis()
    {
        ce_hak_akses('admin.harga_komoditas.update');

        $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');

        if (!$komoditas_id || !$pasar_id) {
            $response = ['status' => false, 'message' => 'Komoditas dan Pasar harus dipilih'];
        } else {
            try {
                $result = $this->harga_komoditas_m->execute_analisis_procedure($tanggal, $komoditas_id, $pasar_id);
                if ($result) {
                    $response = ['status' => true, 'message' => 'Analisis harga berhasil dijalankan'];
                } else {
                    $response = ['status' => false, 'message' => 'Gagal menjalankan analisis harga'];
                }
            } catch (Exception $e) {
                $response = ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function ajax_recent_activity()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        try {
            // Get recent activity data (last 10 activities today)
            $recent_data = $this->harga_komoditas_m->get_recent_activity(10);

            $response = [
                'status' => true,
                'data' => $recent_data,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data aktivitas: ' . $e->getMessage(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }

        echo json_encode($response);
    }

    public function ajax_commodity_list()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        try {
            // Get list of commodities that have price data
            $commodities = $this->harga_komoditas_m->get_commodities_with_prices();

            $response = [
                'status' => true,
                'data' => $commodities,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data komoditas: ' . $e->getMessage(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }

        echo json_encode($response);
    }

    public function ajax_price_comparison()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $komoditas_id = $this->input->post('komoditas_id');
        $date_from = $this->input->post('date_from') ?: date('Y-m-d', strtotime('-7 days'));
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');
        $filter_type = $this->input->post('filter_type') ?: 'custom';

        try {
            // Adjust date range based on filter type
            switch ($filter_type) {
                case 'week':
                    $date_from = date('Y-m-d', strtotime('monday this week'));
                    $date_to = date('Y-m-d');
                    break;
                case 'month':
                    $date_from = date('Y-m-01');
                    $date_to = date('Y-m-d');
                    break;
            }

            $price_data = $this->harga_komoditas_m->get_price_comparison_data($komoditas_id, $date_from, $date_to);

            $response = [
                'status' => true,
                'data' => $price_data,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data perbandingan harga: ' . $e->getMessage(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }

        echo json_encode($response);
    }

    public function ajax_price_detail()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $komoditas_id = $this->input->post('komoditas_id');
        $tanggal = $this->input->post('tanggal');

        try {
            $price_detail = $this->harga_komoditas_m->get_price_detail($komoditas_id, $tanggal);

            $response = [
                'status' => true,
                'data' => $price_detail,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil detail harga: ' . $e->getMessage(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }

        echo json_encode($response);
    }

    // =============================================
    // New Dashboard AJAX Endpoints
    // =============================================

    public function ajax_hierarchical_data()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        // Set header untuk JSON
        header('Content-Type: application/json');

        $date_from = $this->input->post('date_from') ?: date('Y-m-d');
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');
        $pasar_id = $this->input->post('pasar_id') ?: 'all';
        $filter_type = $this->input->post('filter_type') ?: 'today';

        try {
            // Test koneksi database
            $db_test = $this->db->query("SELECT COUNT(*) as total FROM harga_komoditas_harian")->row();

            $hierarchical_data = $this->harga_komoditas_m->get_hierarchical_price_data(
                $date_from,
                $date_to,
                $pasar_id,
                $filter_type
            );

            $response = [
                'status' => true,
                'debug_info' => [
                    'params' => [
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'pasar_id' => $pasar_id,
                        'filter_type' => $filter_type
                    ],
                    'total_harga_records' => $db_test->total,
                    'result_summary' => [
                        'total_data' => count($hierarchical_data['data']),
                        'total_commodities' => $hierarchical_data['total_commodities'],
                        'total_with_prices' => $hierarchical_data['total_with_prices'],
                        'dates' => $hierarchical_data['dates']
                    ]
                ],
                'data' => $hierarchical_data
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        echo json_encode($response);
        exit;
    }

    // Method untuk testing tanpa hak akses
    public function test_hierarchical_data()
    {
        // Set header untuk JSON
        header('Content-Type: application/json');

        $date_from = $this->input->post('date_from') ?: date('Y-m-d');
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');
        $pasar_id = $this->input->post('pasar_id') ?: 'all';
        $filter_type = $this->input->post('filter_type') ?: 'today';

        try {
            // Test koneksi database
            $db_test = $this->db->query("SELECT COUNT(*) as total FROM harga_komoditas_harian")->row();

            // Test query komoditas
            $komoditas_test = $this->db->query("SELECT COUNT(*) as total FROM master_komoditas WHERE komoditas_status = 1")->row();

            // Test query pasar
            $pasar_test = $this->db->query("SELECT COUNT(*) as total FROM master_pasar WHERE pasar_status = 1")->row();

            // Test query harga hari ini
            $harga_test = $this->db->query("SELECT COUNT(*) as total FROM harga_komoditas_harian WHERE harga_tanggal = ?", [date('Y-m-d')])->row();

            $hierarchical_data = $this->harga_komoditas_m->get_hierarchical_price_data(
                $date_from,
                $date_to,
                $pasar_id,
                $filter_type
            );

            $response = [
                'status' => true,
                'debug_info' => [
                    'params' => [
                        'date_from' => $date_from,
                        'date_to' => $date_to,
                        'pasar_id' => $pasar_id,
                        'filter_type' => $filter_type
                    ],
                    'database_tests' => [
                        'total_harga_records' => $db_test->total,
                        'total_komoditas' => $komoditas_test->total,
                        'total_pasar' => $pasar_test->total,
                        'harga_hari_ini' => $harga_test->total
                    ],
                    'result_summary' => [
                        'total_data' => count($hierarchical_data['data']),
                        'total_commodities' => $hierarchical_data['total_commodities'],
                        'total_with_prices' => $hierarchical_data['total_with_prices'],
                        'dates' => $hierarchical_data['dates']
                    ]
                ],
                'data' => $hierarchical_data
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    public function ajax_market_analysis()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $date_from = $this->input->post('date_from') ?: date('Y-m-d');
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');
        $pasar_id = $this->input->post('pasar_id') ?: 'all';

        try {
            $analysis = $this->harga_komoditas_m->get_market_analysis($date_from, $date_to, $pasar_id);

            $response = [
                'status' => true,
                'data' => $analysis
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil analisis pasar: ' . $e->getMessage()
            ];
        }

        echo json_encode($response);
    }

    public function ajax_price_disparity()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $date_from = $this->input->post('date_from') ?: date('Y-m-d');
        $date_to = $this->input->post('date_to') ?: date('Y-m-d');
        $pasar_id = $this->input->post('pasar_id') ?: 'all';

        try {
            $disparity_data = $this->harga_komoditas_m->get_price_disparity_data($date_from, $date_to, $pasar_id);

            $response = [
                'status' => true,
                'data' => $disparity_data
            ];

        } catch (Exception $e) {
            $response = [
                'status' => false,
                'message' => 'Gagal mengambil data disparitas: ' . $e->getMessage()
            ];
        }

        echo json_encode($response);
    }

    // =============================================
    // Drill-down Dashboard Methods
    // =============================================

    public function get_commodity_info()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $commodity_id = $this->input->post('commodity_id');

        if (!$commodity_id) {
            $response = ['status' => false, 'message' => 'ID komoditas diperlukan'];
        } else {
            // Get commodity basic info
            $this->db->select('k.*');
            $this->db->from('master_komoditas k');
            $this->db->where('k.komoditas_id', $commodity_id);
            $commodity = $this->db->get()->row();

            if ($commodity) {
                // Get price statistics
                $this->db->select('
                    COUNT(*) as data_count,
                    AVG(harga_rata_rata) as avg_price,
                    MIN(harga_rata_rata) as min_price,
                    MAX(harga_rata_rata) as max_price
                ');
                $this->db->from('harga_komoditas_harian');
                $this->db->where('komoditas_id', $commodity_id);
                $this->db->where('harga_status', 1);
                $this->db->where('harga_tanggal >=', date('Y-m-d', strtotime('-30 days')));
                $stats = $this->db->get()->row();

                $response = [
                    'status' => true,
                    'data' => array_merge((array)$commodity, ['stats' => $stats])
                ];
            } else {
                $response = ['status' => false, 'message' => 'Komoditas tidak ditemukan'];
            }
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function get_drilldown_chart_data()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $commodity_id = $this->input->post('commodity_id');
        $days = $this->input->post('days') ?: 30;

        if (!$commodity_id) {
            $response = ['status' => false, 'message' => 'ID komoditas diperlukan'];
        } else {
            // Get commodity name first
            $this->db->select('komoditas_nama');
            $this->db->from('master_komoditas');
            $this->db->where('komoditas_id', $commodity_id);
            $commodity_name = $this->db->get()->row();

            // Get aggregated price data per date
            $this->db->select('harga_tanggal, AVG(harga_rata_rata) as avg_price');
            $this->db->from('harga_komoditas_harian');
            $this->db->where('komoditas_id', $commodity_id);
            $this->db->where('harga_tanggal >=', date('Y-m-d', strtotime("-{$days} days")));
            $this->db->where('harga_status', 1);
            $this->db->group_by('harga_tanggal');
            $this->db->order_by('harga_tanggal', 'asc');

            $price_data = $this->db->get()->result();

            $labels = [];
            $data = [];

            foreach ($price_data as $row) {
                $labels[] = date('d/m', strtotime($row->harga_tanggal));
                $data[] = floatval($row->avg_price);
            }

            $response = [
                'status' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data,
                    'komoditas_nama' => $commodity_name ? $commodity_name->komoditas_nama : 'Unknown'
                ]
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function get_market_comparison()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $commodity_id = $this->input->post('commodity_id');

        if (!$commodity_id) {
            $response = ['status' => false, 'message' => 'ID komoditas diperlukan'];
        } else {
            // Get latest price data for the last 7 days to show recent market activity
            $this->db->select('h.*, p.pasar_nama, p.pasar_alamat');
            $this->db->from('view_harga_komoditas_lengkap h');
            $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
            $this->db->where('h.komoditas_id', $commodity_id);
            $this->db->where('h.harga_status', 1);
            $this->db->where('h.harga_tanggal >=', date('Y-m-d', strtotime('-7 days')));
            $this->db->order_by('h.harga_tanggal', 'desc');
            $this->db->order_by('h.harga_rata_rata', 'desc');
            $this->db->limit(20); // Show more recent data

            $market_data = $this->db->get()->result();

            $response = [
                'status' => true,
                'data' => $market_data
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();

        echo json_encode($response);
    }

    public function export_drilldown()
    {
        ce_hak_akses('admin.harga_komoditas.view');

        $commodity_id = $this->input->get('commodity_id');
        $commodity_name = $this->input->get('commodity_name');
        $period = $this->input->get('period') ?: 30;

        if (!$commodity_id) {
            show_error('ID komoditas diperlukan');
            return;
        }

        // Get commodity data
        $this->db->select('h.*, p.pasar_nama, k.komoditas_satuan');
        $this->db->from('view_harga_komoditas_lengkap h');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.komoditas_id', $commodity_id);
        $this->db->where('h.harga_tanggal >=', date('Y-m-d', strtotime("-{$period} days")));
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_tanggal', 'desc');

        $data = $this->db->get()->result();

        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="drilldown_' . urlencode($commodity_name) . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, ['Tanggal', 'Pasar', 'Harga Beli', 'Harga Jual', 'Harga Rata-rata', 'Satuan', 'Stok', 'Kualitas']);

        // CSV data
        foreach ($data as $row) {
            fputcsv($output, [
                $row->harga_tanggal,
                $row->pasar_nama,
                $row->harga_beli,
                $row->harga_jual,
                $row->harga_rata_rata,
                $row->komoditas_satuan,
                $row->stok_tersedia,
                $row->kualitas
            ]);
        }

        fclose($output);
        exit;
    }

}
