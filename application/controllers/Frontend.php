<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Frontend extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load models yang diperlukan untuk frontend publik
        $this->load->model('harga_komoditas/harga_komoditas_m');
        $this->load->model('komoditas/komoditas_m');
        $this->load->model('pelaporan/pelaporan_m');
        $this->load->model('kategori_pelaporan/kategori_pelaporan_m');
        $this->load->model('kepengurusan_sosial/kepengurusan_sosial_m');
        $this->load->model('kategori_kepengurusan/kategori_kepengurusan_m');
        $this->load->model('master_pedagang/master_pedagang_m');
        $this->load->model('master_pasar/master_pasar_m');
        $this->load->model('masyarakat/masyarakat_m');
    }

    public function index()
    {
        // Page configuration
        $data['page_title'] = 'Dashboard Masyarakat';
        $data['page_description'] = 'Dashboard Masyarakat - Sistem Informasi Publik untuk akses data transparan harga komoditas, pelaporan masyarakat, kepengurusan, dan UMKM';
        $data['page_keywords'] = 'dashboard masyarakat, sistem informasi publik, transparansi data, harga komoditas, pelaporan, kepengurusan, UMKM';
        $data['current_page'] = 'beranda';

        // Page-specific assets
        $data['page_js'] = ['beranda.js'];
        
        // Data statistik untuk beranda
        $data['stats'] = [
            'total_komoditas' => $this->komoditas_m->count_active_komoditas(),
            'total_pelaporan' => $this->pelaporan_m->count_all_reports(),
            'total_kepengurusan' => $this->kepengurusan_sosial_m->count_all(),
            'total_umkm' => $this->master_pedagang_m->count_all_active()
        ];
        
        // Data harga komoditas terbaru (5 teratas)
        $data['harga_terbaru'] = $this->harga_komoditas_m->get_latest_prices(5);
        
        // Data pelaporan terbaru (5 teratas)
        $data['pelaporan_terbaru'] = $this->pelaporan_m->get_latest_reports(5);
        
        $this->load->view('frontend/template', $data);
    }

    public function harga_komoditas()
    {
        // Page configuration
        $data['page_title'] = 'Monitoring Retribusi';
        $data['page_description'] = 'Pantau target dan capaian retribusi pasar. Informasi real-time tentang target retribusi, realisasi pendapatan, dan persentase pencapaian.';
        $data['page_keywords'] = 'monitoring retribusi, target retribusi, capaian retribusi, pasar tradisional, pendapatan pasar, persentase pencapaian';
        $data['current_page'] = 'harga-komoditas';

        // Get filter parameters
        $pasar_id = $this->input->get('pasar') ?: 'all';
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d'); // Default hari ini

        // Data untuk filter
        $data['pasar_list'] = $this->master_pasar_m->get_active_pasar();

        // Page-specific assets
        $data['page_js'] = ['harga-komoditas.js'];
        $data['inline_js'] = '
            window.baseUrl = "' . base_url() . '";
            window.pasarList = ' . json_encode($data['pasar_list']) . ';
            window.selectedPasar = "' . $pasar_id . '";
            window.selectedTanggal = "' . $tanggal . '";
        ';
        $data['selected_pasar'] = $pasar_id;
        $data['selected_tanggal'] = $tanggal;

        // Initialize statistics for the dashboard
        $data['statistics'] = $this->harga_komoditas_m->get_price_statistics($tanggal);

        $this->load->view('frontend/template', $data);
    }

    public function pelaporan()
    {
        // Page configuration
        $data['page_title'] = 'Pelaporan Masyarakat';
        $data['page_description'] = 'Sistem pelaporan dan pengaduan masyarakat online. Laporkan keluhan, saran, dan aspirasi Anda dengan mudah dan transparan.';
        $data['page_keywords'] = 'pelaporan masyarakat, pengaduan online, aspirasi rakyat, keluhan publik, transparansi pemerintah, layanan masyarakat';
        $data['current_page'] = 'pelaporan';

        // Page-specific assets
        $data['page_js'] = ['pelaporan.js'];
        
        // Get filter parameters
        $status = $this->input->get('status') ?: '';
        $kategori = $this->input->get('kategori') ?: '';
        $search = $this->input->get('search') ?: '';
        
        // Data untuk filter
        $data['kategori_list'] = $this->kategori_pelaporan_m->get_all_active();
        $data['status_list'] = ['BARU', 'PROSES', 'SELESAI', 'DITOLAK'];
        
        // Data pelaporan dengan pagination
        $config['base_url'] = base_url('pelaporan');
        $config['total_rows'] = $this->pelaporan_m->count_public_reports($status, $kategori, $search);
        $config['per_page'] = 12;
        $config['uri_segment'] = 2;
        $config['reuse_query_string'] = TRUE;
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $offset = $this->uri->segment(2) ?: 0;
        $data['pelaporan_list'] = $this->pelaporan_m->get_public_reports($config['per_page'], $offset, $status, $kategori, $search);
        $data['pagination'] = $this->pagination->create_links();
        
        $data['filters'] = [
            'status' => $status,
            'kategori' => $kategori,
            'search' => $search
        ];
        
        $this->load->view('frontend/template', $data);
    }

    public function pelaporan_detail($id)
    {
        $pelaporan = $this->pelaporan_m->get_public_report_detail($id);

        if (!$pelaporan) {
            show_404();
            return;
        }

        // Page configuration
        $data['page_title'] = 'Detail Pelaporan - ' . $pelaporan->kode_laporan;
        $data['page_description'] = 'Detail pelaporan: ' . substr($pelaporan->judul, 0, 150) . '... - Status: ' . $pelaporan->status_ke;
        $data['page_keywords'] = 'detail pelaporan, ' . $pelaporan->kode_laporan . ', pengaduan masyarakat, status laporan, transparansi';
        $data['current_page'] = 'pelaporan';

        // Page-specific assets
        $data['page_js'] = ['pelaporan-detail.js'];
        
        $data['pelaporan'] = $pelaporan;
        $data['timeline'] = $this->pelaporan_m->get_report_timeline($id);
        $data['files'] = $this->pelaporan_m->get_report_files($id);
        
        $this->load->view('frontend/template', $data);
    }

    public function kepengurusan()
    {
        // Page configuration
        $data['page_title'] = 'Data Kepengurusan';
        $data['page_description'] = 'Informasi lengkap kepengurusan sosial dan organisasi masyarakat. Data struktur organisasi, program kerja, dan kegiatan sosial kemasyarakatan.';
        $data['page_keywords'] = 'kepengurusan sosial, organisasi masyarakat, struktur organisasi, program kerja, kegiatan sosial, pemberdayaan masyarakat';
        $data['current_page'] = 'kepengurusan';

        // Page-specific assets
        $data['page_js'] = ['kepengurusan.js'];
        
        // Get filter parameters
        $kategori_id = $this->input->get('kategori') ?: '';
        $search = $this->input->get('search') ?: '';
        
        // Data untuk filter
        $data['kategori_list'] = $this->kategori_kepengurusan_m->get_all_active();
        
        // Data kepengurusan dengan pagination
        $config['base_url'] = base_url('kepengurusan');
        $config['total_rows'] = $this->kepengurusan_sosial_m->count_public_data($kategori_id, $search);
        $config['per_page'] = 12;
        $config['uri_segment'] = 2;
        $config['reuse_query_string'] = TRUE;
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $offset = $this->uri->segment(2) ?: 0;
        $data['kepengurusan_list'] = $this->kepengurusan_sosial_m->get_public_data($config['per_page'], $offset, $kategori_id, $search);
        $data['pagination'] = $this->pagination->create_links();
        
        $data['filters'] = [
            'kategori' => $kategori_id,
            'search' => $search
        ];
        
        $this->load->view('frontend/template', $data);
    }

    public function umkm()
    {
        // Page configuration
        $data['page_title'] = 'Data UMKM';
        $data['page_description'] = 'Direktori lengkap Usaha Mikro Kecil dan Menengah (UMKM) di berbagai pasar tradisional. Temukan informasi pedagang, produk, dan lokasi usaha.';
        $data['page_keywords'] = 'UMKM, usaha mikro kecil menengah, pedagang pasar, direktori usaha, ekonomi rakyat, pasar tradisional, wirausaha';
        $data['current_page'] = 'umkm';

        // Page-specific assets
        $data['page_js'] = ['umkm.js'];
        
        // Get filter parameters
        $pasar_id = $this->input->get('pasar') ?: '';
        $search = $this->input->get('search') ?: '';
        
        // Data untuk filter
        $data['pasar_list'] = $this->master_pasar_m->get_active_pasar();
        
        // Data UMKM dengan pagination
        $config['base_url'] = base_url('umkm');
        $config['total_rows'] = $this->master_pedagang_m->count_public_data($pasar_id, $search);
        $config['per_page'] = 12;
        $config['uri_segment'] = 2;
        $config['reuse_query_string'] = TRUE;
        
        $this->load->library('pagination');
        $this->pagination->initialize($config);
        
        $offset = $this->uri->segment(2) ?: 0;
        $data['umkm_list'] = $this->master_pedagang_m->get_public_data($config['per_page'], $offset, $pasar_id, $search);
        $data['pagination'] = $this->pagination->create_links();
        
        $data['filters'] = [
            'pasar' => $pasar_id,
            'search' => $search
        ];
        
        $this->load->view('frontend/template', $data);
    }

    /**
     * Secure file serving - hides actual file URLs
     */
    public function serve_secure_file($encrypted_path = null)
    {
        if (!$encrypted_path) {
            show_404();
            return;
        }

        try {
            // Load encryption library
            $this->load->library('encryption');

            // Decrypt the file path
            $file_path = $this->encryption->decrypt(base64_decode(urldecode($encrypted_path)));

            if (!$file_path) {
                show_404();
                return;
            }

            // Security check - ensure file is within allowed directories
            $allowed_dirs = ['uploads/', 'assets/uploads/', 'files/', 'documents/'];
            $is_allowed = false;

            foreach ($allowed_dirs as $dir) {
                if (strpos($file_path, $dir) === 0) {
                    $is_allowed = true;
                    break;
                }
            }

            if (!$is_allowed) {
                show_404();
                return;
            }

            // Full file path
            $full_path = FCPATH . $file_path;

            if (!file_exists($full_path)) {
                show_404();
                return;
            }

            // Get file info
            $file_info = pathinfo($full_path);
            $file_size = filesize($full_path);
            $mime_type = $this->get_mime_type($full_path);

            // Set headers for secure serving
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . $file_size);
            header('Content-Disposition: inline; filename="' . $file_info['basename'] . '"');
            header('Cache-Control: private, max-age=3600');
            header('Pragma: private');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');

            // Output file
            readfile($full_path);
            exit;

        } catch (Exception $e) {
            log_message('error', 'Secure file serving error: ' . $e->getMessage());
            show_404();
            return;
        }
    }

    /**
     * Get MIME type of file
     */
    private function get_mime_type($file_path)
    {
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed'
        ];

        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        if (isset($mime_types[$extension])) {
            return $mime_types[$extension];
        }

        // Fallback to PHP's mime_content_type if available
        if (function_exists('mime_content_type')) {
            return mime_content_type($file_path);
        }

        return 'application/octet-stream';
    }

    // AJAX Methods untuk loading data dinamis
    
    public function ajax_harga_komoditas()
    {
        $action = $this->input->post('action') ?: 'load_data';
        
        switch ($action) {
            case 'revenue_cards':
                $this->ajax_revenue_cards();
                break;
            case 'chart_data':
                $this->ajax_chart_data();
                break;
            case 'table_data':
                $this->ajax_komoditas_table();
                break;
            case 'price_detail':
                $this->ajax_price_detail();
                break;
            default:
                $tanggal = $this->input->post('tanggal') ?: date('Y-m-d');
                $pasar_id = $this->input->post('pasar_id') ?: 'all';

                $data = $this->harga_komoditas_m->get_prices_by_date_and_market($tanggal, $pasar_id);

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode([
                        'status' => true,
                        'data' => $data
                    ]));
        }
    }
    
    /**
     * AJAX handler for revenue cards data
     */
    public function ajax_revenue_cards()
    {
        $dateRange = $this->input->post('dateRange') ?: 'today';
        $startDate = $this->input->post('startDate') ?: date('Y-m-d');
        $endDate = $this->input->post('endDate') ?: date('Y-m-d');
        $pasarSelect = $this->input->post('pasarSelect') ?: ['all'];
        
        // Calculate date range
        $dateFilter = $this->calculateDateRange($dateRange, $startDate, $endDate);
        
        // Get revenue data (mock data for now, can be replaced with actual revenue calculations)
        $data = [
            'target_revenue' => 1000000000, // Rp 1 Miliar
            'realized_revenue' => $this->calculateRealizedRevenue($dateFilter, $pasarSelect),
            'active_commodities' => $this->komoditas_m->count_active_komoditas(),
            'monitored_markets' => $this->countMonitoredMarkets($dateFilter, $pasarSelect),
            'price_increase' => $this->countPriceChanges($dateFilter, $pasarSelect, 'naik'),
            'price_decrease' => $this->countPriceChanges($dateFilter, $pasarSelect, 'turun'),
            'price_stable' => $this->countPriceChanges($dateFilter, $pasarSelect, 'stable')
        ];
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $data
            ]));
    }
    
    /**
     * AJAX handler for price detail modal
     */
    public function ajax_price_detail()
    {
        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id');
        $dateRange = $this->input->post('dateRange') ?: 'today';
        $startDate = $this->input->post('startDate') ?: date('Y-m-d');
        $endDate = $this->input->post('endDate') ?: date('Y-m-d');
        
        if (!$komoditas_id) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Komoditas ID is required'
                ]));
            return;
        }
        
        // Get detailed price information
        $data = $this->harga_komoditas_m->get_commodity_price_detail($komoditas_id, $pasar_id, $startDate, $endDate);
        
        if (!$data) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]));
            return;
        }
        
        // Get chart data for the last 7 days
        $chartData = $this->harga_komoditas_m->get_price_chart_data($komoditas_id, $pasar_id, 7);
        $data['chart_data'] = $chartData;
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $data
            ]));
    }
    
    /**
     * Export harga komoditas data
     */
    public function export_harga_komoditas()
    {
        $dateRange = $this->input->get('dateRange') ?: 'today';
        $startDate = $this->input->get('startDate') ?: date('Y-m-d');
        $endDate = $this->input->get('endDate') ?: date('Y-m-d');
        $pasarSelect = $this->input->get('pasarSelect') ?: ['all'];
        
        // Calculate date range
        $dateFilter = $this->calculateDateRange($dateRange, $startDate, $endDate);
        
        // Get data for export
        $data = $this->harga_komoditas_m->get_export_data($dateFilter['start'], $dateFilter['end'], $pasarSelect);
        
        // Generate CSV
        $filename = 'harga_komoditas_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, ['Komoditas', 'Satuan', 'Pasar', 'Harga Kemarin', 'Harga Hari Ini', 'Perubahan', 'Stok']);
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, [
                $row->komoditas_nama,
                $row->komoditas_satuan,
                $row->pasar_nama,
                $row->harga_kemarin,
                $row->harga_hari_ini,
                $row->keterangan_perubahan,
                $row->stok_tersedia
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Helper functions
     */
    private function calculateDateRange($dateRange, $startDate, $endDate)
    {
        $today = date('Y-m-d');
        
        switch ($dateRange) {
            case 'today':
                return ['start' => $today, 'end' => $today];
            case 'yesterday':
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                return ['start' => $yesterday, 'end' => $yesterday];
            case 'last7days':
                $start = date('Y-m-d', strtotime('-6 days'));
                return ['start' => $start, 'end' => $today];
            case 'last30days':
                $start = date('Y-m-d', strtotime('-29 days'));
                return ['start' => $start, 'end' => $today];
            case 'custom':
                return ['start' => $startDate, 'end' => $endDate];
            default:
                return ['start' => $today, 'end' => $today];
        }
    }
    
    private function calculateRealizedRevenue($dateFilter, $pasarSelect)
    {
        // This is a mock calculation - replace with actual revenue logic
        // For now, calculate based on total price data
        $totalRevenue = $this->harga_komoditas_m->calculate_total_revenue($dateFilter['start'], $dateFilter['end'], $pasarSelect);
        return $totalRevenue ?: 750000000; // Default: Rp 750 Juta
    }
    
    private function countMonitoredMarkets($dateFilter, $pasarSelect)
    {
        if (in_array('all', $pasarSelect)) {
            return $this->harga_komoditas_m->count_monitored_markets($dateFilter['start'], $dateFilter['end']);
        } else {
            return count($pasarSelect);
        }
    }
    
    private function countPriceChanges($dateFilter, $pasarSelect, $type)
    {
        return $this->harga_komoditas_m->count_price_changes($dateFilter['start'], $dateFilter['end'], $pasarSelect, $type);
    }

    public function ajax_chart_data()
    {
        $pasar_id = $this->input->post('pasar_id') ?: 'all';
        $start_date = $this->input->post('start_date') ?: date('Y-m-d', strtotime('-6 days'));
        $end_date = $this->input->post('end_date') ?: date('Y-m-d');
        $date_range_type = $this->input->post('date_range_type') ?: 'week';

        // Get chart data for all commodities (top 5 most traded)
        $chart_data = $this->harga_komoditas_m->get_all_commodities_chart_data($pasar_id, $start_date, $end_date, $date_range_type);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $chart_data
            ]));
    }

    public function ajax_komoditas_table()
    {
        $start_date = $this->input->post('startDate') ?: date('Y-m-d');
        $end_date = $this->input->post('endDate') ?: date('Y-m-d');
        $date_range_type = $this->input->post('dateRange') ?: 'today';
        $pasar_ids = $this->input->post('pasarSelect') ?: ['all'];
        $page = $this->input->post('page') ?: 1;
        $sort_column = $this->input->post('sort_column') ?: 'komoditas';
        $sort_direction = $this->input->post('sort_direction') ?: 'asc';
        
        // Calculate date range
        $dateFilter = $this->calculateDateRange($date_range_type, $start_date, $end_date);
        
        // Get paginated data for table
        $result = $this->harga_komoditas_m->get_paginated_data(
            $dateFilter['start'],
            $dateFilter['end'],
            $pasar_ids,
            $page,
            20,
            $sort_column,
            $sort_direction
        );

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]));
    }

    public function ajax_search_pelaporan()
    {
        $search = $this->input->post('search');
        $status = $this->input->post('status');
        $kategori = $this->input->post('kategori');
        
        $data = $this->pelaporan_m->search_public_reports($search, $status, $kategori);
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => true,
                'data' => $data
            ]));
    }

    /**
     * Generate secure file URL
     */
    public function generate_secure_url($file_path)
    {
        try {
            $this->load->library('encryption');
            $encrypted_path = base64_encode($this->encryption->encrypt($file_path));
            return base_url('file/' . urlencode($encrypted_path));
        } catch (Exception $e) {
            log_message('error', 'Secure URL generation error: ' . $e->getMessage());
            return '#';
        }
    }
}
