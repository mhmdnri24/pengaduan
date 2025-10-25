<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Frontend extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Master_kategori_m');
        $this->load->model('Fasilitas_umum_m');
        $this->load->model('Harga_komoditas_m');
        $this->load->model('slider/Slider_m');
    }
    
    private function get_setting($key)
    {
        $result = $this->db->where('kunci', $key)->get('opsi')->row();
        return $result ? $result->nilai : null;
    }
    
 
    private function get_contact_data($type = 'bantuan_kontak')
    {
        $kontak_data = $this->get_setting($type);
        if ($kontak_data) {
            $kontak_array = json_decode($kontak_data, true);
            return $kontak_array ? $kontak_array : [];
        }
        return [];
    }
    
    /**
     * Get emergency contact data from kontak_darurat setting
     */
    private function get_emergency_contact_data()
    {
        $data = $this->get_contact_data('kontak_darurat');
        return $data;
    }

    public function index()
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['tagline'] = $this->get_setting('tagline') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Page configuration
        $data['page_title'] = 'Beranda';
        $data['page_description'] = $data['site_name'] . ' - Sistem Informasi Publik untuk akses data transparan fasilitas umum';
        $data['page_keywords'] = 'dashboard masyarakat, fasilitas umum, sistem informasi publik, transparansi data';
        $data['current_page'] = 'beranda';

        // Data kategori fasilitas umum (hanya parent/root kategori)
        $kategori_list = $this->master_kategori_m->kategori_get_parent();
        
        // Tambahkan ikon untuk setiap kategori
        foreach ($kategori_list as $kategori) {
            $kategori->icon = $this->get_kategori_icon($kategori->nama_kategori);
        }
        
        $data['kategori_list'] = $kategori_list;
        
        // Data harga komoditas terbaru untuk widget
        $data['latest_prices'] = $this->get_latest_prices_widget(5);
        
        // Get emergency contact data for homepage
        $data['emergency_contacts'] = $this->get_emergency_contact_data();
        
        // Get statistics for homepage
        $data['stats'] = $this->get_homepage_statistics();
        
        // Get latest prices for homepage
        $data['harga_terbaru'] = $this->get_latest_prices_widget(3);
        
        // Get latest reports for homepage
        $data['pelaporan_terbaru'] = $this->get_latest_reports_widget(3);
        
        // Data slider dari database
        $data['slider_data'] = $this->slider_m->get_slider_for_frontend();
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    public function detail($id)
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Get data kategori berdasarkan ID
        $kategori = $this->master_kategori_m->kategori_by_id($id);
        
        if (!$kategori) {
            show_404();
            return;
        }
        
        // Get children kategori
        $children = $this->master_kategori_m->kategori_get_children($id);
        
        // Tambahkan ikon untuk setiap kategori anak
        foreach ($children as $child) {
            $child->icon = $this->get_kategori_icon($child->nama_kategori);
        }
        
        // Page configuration
        $data['page_title'] = $kategori->nama_kategori;
        $data['page_description'] = 'Detail kategori ' . $kategori->nama_kategori . ' - ' . (!empty($kategori->deskripsi) ? character_limiter($kategori->deskripsi, 100) : 'Informasi lengkap mengenai kategori ini');
        $data['page_keywords'] = $kategori->nama_kategori . ', fasilitas umum, kategori, ' . (!empty($kategori->deskripsi) ? implode(' ', array_slice(explode(' ', $kategori->deskripsi), 0, 5)) : '');
        $data['current_page'] = 'detail-kategori';
        
        // Tambahkan ikon untuk kategori utama
        $kategori->icon = $this->get_kategori_icon($kategori->nama_kategori);
        
        // Get statistik fasilitas untuk setiap child kategori
        foreach ($children as $child) {
            $total = $this->fasilitas_umum_m->count_fasilitas_by_kategori($child->id);
            $child->total_fasilitas = $total;
            $child->fasilitas_aktif = $total; // Semua fasilitas aktif karena sudah difilter
            $child->fasilitas_nonaktif = 0;
            // Tambahkan warna untuk setiap child kategori
            $child->color = $this->get_category_color($child->nama_kategori);
        }
        
        // Tambahkan warna untuk kategori utama
        $kategori->color = $this->get_category_color($kategori->nama_kategori);
        
        // Data untuk view
        $data['kategori'] = $kategori;
        $data['children'] = $children;
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    public function fasilitas($nama_kategori = null, $id = null)
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Jika tidak ada parameter, tampilkan list kategori
        if ($nama_kategori === null && $id === null) {
            // Page configuration
            $data['page_title'] = 'Fasilitas Umum';
            $data['page_description'] = 'Daftar kategori fasilitas umum yang tersedia';
            $data['page_keywords'] = 'fasilitas umum, kategori, layanan publik';
            $data['current_page'] = 'fasilitas';
            
            // Get kategori parent (parent_id = null)
            $kategori_list = $this->master_kategori_m->kategori_get_parent();
            
            // Tambahkan ikon dan hitung jumlah fasilitas untuk setiap kategori
            foreach ($kategori_list as $kategori) {
                $kategori->icon = $this->get_kategori_icon($kategori->nama_kategori);
                
                // Hitung jumlah fasilitas untuk kategori ini
                $this->db->where('kategori_id', $kategori->id);
                $this->db->where('status', 1);
                $kategori->jumlah_fasilitas = $this->db->count_all_results('fasilitas_umum');
            }
            
            $data['kategori_list'] = $kategori_list;
            
            // Load view dengan template frontend
            $this->load->view('frontend/template', $data);
            return;
        }
        
        // Jika ada parameter, tampilkan detail fasilitas per kategori
        if ($nama_kategori !== null && $id !== null) {
            // Get data kategori berdasarkan ID
            $kategori = $this->master_kategori_m->kategori_by_id($id);
            
            if (!$kategori) {
                show_404();
                return;
            }
            
            // Get fasilitas berdasarkan kategori - gunakan query langsung dulu
            $this->db->select('fu.*, mk.nama_kategori, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan');
            $this->db->from('fasilitas_umum fu');
            $this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
            $this->db->join('kota kt', 'fu.id_kota = kt.id_kota', 'left');
            $this->db->join('kecamatan kc', 'fu.id_kecamatan = kc.id_kecamatan', 'left');
            $this->db->join('kelurahan kl', 'fu.id_kelurahan = kl.id_kelurahan', 'left');
            $this->db->where('fu.kategori_id', $id);
            $this->db->where('fu.status', 1);
            $this->db->order_by('fu.nama_fasilitas', 'asc');
            $fasilitas_list = $this->db->get()->result();
            
            // Page configuration
            $data['page_title'] = 'Fasilitas ' . $kategori->nama_kategori;
            $data['page_description'] = 'Daftar fasilitas ' . $kategori->nama_kategori . ' - ' . (!empty($kategori->deskripsi) ? character_limiter($kategori->deskripsi, 100) : 'Informasi lengkap mengenai fasilitas dalam kategori ini');
            $data['page_keywords'] = $kategori->nama_kategori . ', fasilitas umum, lokasi, map, ' . (!empty($kategori->deskripsi) ? implode(' ', array_slice(explode(' ', $kategori->deskripsi), 0, 5)) : '');
            $data['current_page'] = 'fasilitas-list';
            
            // Data untuk view
            $data['kategori'] = $kategori;
            $data['fasilitas_list'] = $fasilitas_list;
            
            // Load view dengan template frontend
            $this->load->view('frontend/template', $data);
        }
    }
    
    /**
     * Halaman daftar harga komoditas
     */
    public function harga_komoditas()
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Page configuration
        $data['page_title'] = 'Harga Komoditas';
        $data['page_description'] = 'Informasi harga komoditas terkini di berbagai pasar';
        $data['page_keywords'] = 'harga komoditas, pasar, harga terkini';
        $data['current_page'] = 'harga_komoditas';
        
        // Get parameters
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
        $pasar_id = $this->input->get('pasar_id') ?: 'all';
        
        // Get data using direct database queries instead of model
        $data['tanggal'] = $tanggal;
        $data['pasar_id'] = $pasar_id;
        
        // Get list of markets
        $data['pasar_list'] = $this->db->where('pasar_status', 1)
                                       ->order_by('pasar_nama', 'asc')
                                       ->get('master_pasar')
                                       ->result();
        
        // Get structured prices
        $data['structured_prices'] = $this->get_structured_prices($tanggal, $pasar_id);
        
        // Get statistics
        $data['statistics'] = $this->get_price_statistics($tanggal);
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    /**
     * Get structured prices by date for table display
     */
    private function get_structured_prices($tanggal, $pasar_id = 'all')
    {
        $table = 'harga_komoditas_harian';
        
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama
        ');
        $this->db->from($table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_komoditas kp', 'k.komoditas_parent_id = kp.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if ($pasar_id !== 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->order_by('kp.komoditas_urutan', 'asc');
        $this->db->order_by('k.komoditas_urutan', 'asc');

        $results = $this->db->get()->result();

        // Get previous day prices for comparison
        $prev_tanggal = date('Y-m-d', strtotime($tanggal . ' -1 day'));
        $prev_prices = $this->get_previous_prices($prev_tanggal, $pasar_id);

        // Structure data by parent category
        $structured = [];
        foreach ($results as $row) {
            $parent_name = $row->parent_nama ?: 'Lainnya';

            if (!isset($structured[$parent_name])) {
                $structured[$parent_name] = [];
            }

            // Calculate percentage change
            $prev_key = $row->komoditas_id . '_' . $row->pasar_id;
            $prev_price = isset($prev_prices[$prev_key]) ? $prev_prices[$prev_key] : 0;

            $row->persentase_perubahan = 0;
            $row->status_perubahan = 'stable';
            $row->keterangan_perubahan = 'Tidak ada perubahan';

            if ($prev_price > 0) {
                $row->persentase_perubahan = (($row->harga_rata_rata - $prev_price) / $prev_price) * 100;

                if ($row->persentase_perubahan > 0) {
                    $row->status_perubahan = 'naik';
                    $row->keterangan_perubahan = 'Naik ' . number_format(abs($row->persentase_perubahan), 1) . '%';
                } elseif ($row->persentase_perubahan < 0) {
                    $row->status_perubahan = 'turun';
                    $row->keterangan_perubahan = 'Turun ' . number_format(abs($row->persentase_perubahan), 1) . '%';
                } else {
                    $row->keterangan_perubahan = 'Stabil';
                }
            } else {
                $row->keterangan_perubahan = 'Data baru';
            }

            $structured[$parent_name][] = $row;
        }

        return $structured;
    }
    
    /**
     * Get previous day prices for comparison
     */
    private function get_previous_prices($tanggal, $pasar_id = 'all')
    {
        $table = 'harga_komoditas_harian';
        
        $this->db->select('komoditas_id, pasar_id, harga_rata_rata');
        $this->db->from($table);
        $this->db->where('harga_tanggal', $tanggal);
        $this->db->where('harga_status', 1);

        if ($pasar_id !== 'all') {
            $this->db->where('pasar_id', $pasar_id);
        }

        $results = $this->db->get()->result();

        $prev_prices = [];
        foreach ($results as $row) {
            $key = $row->komoditas_id . '_' . $row->pasar_id;
            $prev_prices[$key] = $row->harga_rata_rata;
        }

        return $prev_prices;
    }
    
    /**
     * Get price statistics
     */
    private function get_price_statistics($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $table = 'harga_komoditas_harian';
        $stats = [];
        
        // Total data harga hari ini
        $stats['total_harga_hari_ini'] = $this->db->where('harga_tanggal', $tanggal)
                                                 ->where('harga_status', 1)
                                                 ->count_all_results($table);
        
        // Total komoditas yang dipantau
        $stats['total_komoditas_dipantau'] = $this->db->select('DISTINCT komoditas_id')
                                                      ->where('harga_tanggal', $tanggal)
                                                      ->where('harga_status', 1)
                                                      ->count_all_results($table);
        
        // Total pasar yang dipantau
        $stats['total_pasar_dipantau'] = $this->db->select('DISTINCT pasar_id')
                                                   ->where('harga_tanggal', $tanggal)
                                                   ->where('harga_status', 1)
                                                   ->count_all_results($table);
        
        // Komoditas dengan harga tertinggi
        $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
        $this->db->from($table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_rata_rata', 'desc');
        $this->db->limit(1);
        $stats['harga_tertinggi'] = $this->db->get()->row();
        
        // Komoditas dengan harga terendah
        $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
        $this->db->from($table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_rata_rata', 'asc');
        $this->db->limit(1);
        $stats['harga_terendah'] = $this->db->get()->row();
        
        return $stats;
    }
    
    /**
     * AJAX endpoint untuk chart data harga komoditas
     */
    public function ajax_harga_chart()
    {
        $komoditas_id = $this->input->post('komoditas_id');
        $pasar_id = $this->input->post('pasar_id') ?: 'all';
        
        if (!$komoditas_id) {
            $response = ['status' => false, 'message' => 'ID komoditas diperlukan'];
        } else {
            $chart_data = $this->get_price_chart_data($komoditas_id, $pasar_id);
            $response = ['status' => true, 'data' => $chart_data];
        }
        
        echo json_encode($response);
    }
    
    /**
     * Get price chart data for specific commodity
     */
    private function get_price_chart_data($komoditas_id, $pasar_id = 'all', $days = 7)
    {
        $table = 'harga_komoditas_harian';
        
        $this->db->select('h.harga_tanggal, AVG(h.harga_rata_rata) as avg_price, k.komoditas_nama, k.komoditas_satuan');
        $this->db->from($table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.komoditas_id', $komoditas_id);
        $this->db->where('h.harga_tanggal >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->where('h.harga_status', 1);

        if ($pasar_id !== 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->group_by('h.harga_tanggal');
        $this->db->order_by('h.harga_tanggal', 'asc');

        $results = $this->db->get()->result();

        $labels = [];
        $data = [];
        $komoditas_nama = '';
        $komoditas_satuan = '';

        foreach ($results as $row) {
            $labels[] = date('d/m', strtotime($row->harga_tanggal));
            $data[] = floatval($row->avg_price);
            if (empty($komoditas_nama)) {
                $komoditas_nama = $row->komoditas_nama;
                $komoditas_satuan = $row->komoditas_satuan;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'komoditas_nama' => $komoditas_nama,
            'komoditas_satuan' => $komoditas_satuan
        ];
    }
    
    /**
     * Get ikon berdasarkan nama kategori
     */
    private function get_kategori_icon($nama_kategori)
    {
        $nama_lower = strtolower($nama_kategori);
        
        // Mapping kategori ke ikon
        $icon_map = [
            'kesehatan' => 'heart-pulse',
            'pendidikan' => 'graduation-cap',
            'transportasi' => 'car',
            'pasar' => 'shopping-cart',
            'ibadah' => 'mosque',
            'olahraga' => 'trophy',
            'sosial' => 'users',
            'keamanan' => 'shield',
            'lingkungan' => 'tree-pine',
            'wisata' => 'map-pin',
            'kuliner' => 'utensils',
            'hiburan' => 'music',
            'bank' => 'building',
            'kantor' => 'building-2',
            'fasilitas' => 'home',
            'umum' => 'square-parking'
        ];
        
        // Cek keyword dalam nama kategori
        foreach ($icon_map as $keyword => $icon) {
            if (strpos($nama_lower, $keyword) !== false) {
                return $icon;
            }
        }
        
        // Default icon
        return 'folder';
    }
    
    /**
     * Get warna berdasarkan nama kategori
     */
    public function get_category_color($nama_kategori)
    {
        $nama_lower = strtolower($nama_kategori);
        
        // Mapping kategori ke warna
        $color_map = [
            'kesehatan' => 'green',
            'pendidikan' => 'blue',
            'transportasi' => 'yellow',
            'pasar' => 'orange',
            'ibadah' => 'purple',
            'olahraga' => 'red',
            'sosial' => 'pink',
            'keamanan' => 'indigo',
            'lingkungan' => 'emerald',
            'wisata' => 'cyan',
            'kuliner' => 'amber',
            'hiburan' => 'rose',
            'bank' => 'slate',
            'kantor' => 'gray',
            'fasilitas' => 'teal',
            'umum' => 'zinc'
        ];
        
        // Cek keyword dalam nama kategori
        foreach ($color_map as $keyword => $color) {
            if (strpos($nama_lower, $keyword) !== false) {
                return $color;
            }
        }
        
        // Default color
        return 'blue';
    }
    
    /**
     * Get latest prices for frontend widget
     */
    private function get_latest_prices_widget($limit = 5)
    {
        $table = 'harga_komoditas_harian';
        
        $this->db->select('h.*, k.komoditas_nama, k.komoditas_satuan, p.pasar_nama');
        $this->db->from($table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);
        $this->db->order_by('h.harga_tanggal', 'desc');
        $this->db->order_by('h.harga_created_at', 'desc');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }
    
    /**
     * Halaman daftar pelaporan insiden publik
     */
    public function pelaporan()
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Page configuration
        $data['page_title'] = 'Pelaporan Insiden Publik';
        $data['page_description'] = 'Daftar pelaporan insiden publik yang telah diajukan oleh masyarakat';
        $data['page_keywords'] = 'pelaporan, insiden, publik, laporan masyarakat';
        $data['current_page'] = 'pelaporan';
        
        // Get parameters
        $search = $this->input->get('search');
        $kategori = $this->input->get('kategori');
        $status = $this->input->get('status');
        $sort = $this->input->get('sort') ?: 'terbaru';
        $page = $this->input->get('page') ?: 1;
        $per_page = 12;
        
        // Set pagination
        $offset = ($page - 1) * $per_page;
        
        // Get data using direct database queries
        $data['search'] = $search;
        $data['kategori'] = $kategori;
        $data['status'] = $status;
        $data['sort'] = $sort;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        
        // Get list of categories
        $data['kategori_list'] = $this->db->where('status', 1)
                                         ->order_by('nama_kategori', 'asc')
                                         ->get('pelaporan_kategori')
                                         ->result();
        
        // Get status options
        $data['status_list'] = [
            'LAPOR' => 'Menunggu Proses',
            'DITERIMA' => 'Diterima',
            'DIKERJAKAN' => 'Sedang Diproses',
            'DIBATALKAN' => 'Dibatalkan',
            'SELESAI' => 'Selesai'
        ];
        
        // Get reports
        $reports = $this->get_reports_list($search, $kategori, $status, $sort, $per_page, $offset);
        $data['reports'] = $reports;
        
        // Get total count for pagination
        $total_reports = $this->count_reports($search, $kategori, $status);
        $data['total_reports'] = $total_reports;
        $data['total_pages'] = ceil($total_reports / $per_page);
        
        // Get statistics
        $data['statistics'] = $this->get_pelaporan_statistics();
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    /**
     * Halaman detail pelaporan
     */
    public function detail_pelaporan($id = null)
    {
        // Get ID from URI segment if not provided as parameter
        if ($id === null) {
            $id = $this->uri->segment(3);
        }
        
        // Debug: Log the incoming ID
        error_log("detail_pelaporan called with ID: " . $id);
        
        // Validate ID
        if (!$id || !is_numeric($id)) {
            error_log("Invalid ID provided: " . $id);
            show_404();
            return;
        }
        
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Get report details
        $report = $this->get_report_detail($id);
        
        if (!$report) {
            error_log("Report not found for ID: " . $id);
            show_404();
            return;
        }
        
        // Page configuration
        $data['page_title'] = $report->judul;
        $data['page_description'] = character_limiter($report->deskripsi, 100);
        $data['page_keywords'] = $report->kategori . ', pelaporan, ' . $report->judul;
        $data['current_page'] = 'pelaporan_detail'; // Set to a unique value for detail page
        
        // Get related data
        $data['report'] = $report;
        $data['files'] = $this->get_report_files($id);
        $data['history'] = $this->get_report_history($id);
        $data['comments'] = $this->get_report_comments($id);
        $data['average_rating'] = $this->get_report_average_rating($id);
        
        // Debug: Log that we're about to load the view
        error_log("Loading template for detail_pelaporan with ID: " . $id);
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    /**
     * Get reports list with filters
     */
    private function get_reports_list($search = null, $kategori = null, $status = null, $sort = 'terbaru', $per_page = 12, $offset = 0)
    {
        $this->db->select('
            p.*,
            m.nama_lengkap,
            pk.nama_kategori,
            pk.icon as kategori_icon,
            pk.warna as kategori_warna,
            (SELECT REPLACE(file_path, \'/frontend/\', \'/\') FROM pelaporan_files WHERE pelaporan_id = p.id LIMIT 1) as thumbnail
        ');
        $this->db->from('pelaporan p');
        $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
        $this->db->join('pelaporan_kategori pk', 'p.kategori = pk.nama_kategori', 'left');
        
        // Apply filters
        if ($search) {
            $this->db->group_start();
            $this->db->like('p.judul', $search);
            $this->db->or_like('p.deskripsi', $search);
            $this->db->group_end();
        }
        
        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }
        
        if ($status) {
            $this->db->where('p.status', $status);
        }
        
        // Apply sorting
        switch ($sort) {
            case 'terlama':
                $this->db->order_by('p.created_at', 'asc');
                break;
            case 'prioritas':
                $this->db->order_by('p.prioritas', 'desc');
                $this->db->order_by('p.created_at', 'desc');
                break;
            default: // terbaru
                $this->db->order_by('p.created_at', 'desc');
                break;
        }
        
        $this->db->limit($per_page, $offset);
        
        $reports = $this->db->get()->result();
        
        // Fix thumbnail path
        foreach ($reports as $report) {
            if ($report->thumbnail) {
                $report->thumbnail = str_replace('/frontend/', '/', $report->thumbnail);
            }
        }
        
        return $reports;
    }
    
    /**
     * Count total reports with filters
     */
    private function count_reports($search = null, $kategori = null, $status = null)
    {
        $this->db->from('pelaporan p');
        
        // Apply filters
        if ($search) {
            $this->db->group_start();
            $this->db->like('p.judul', $search);
            $this->db->or_like('p.deskripsi', $search);
            $this->db->group_end();
        }
        
        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }
        
        if ($status) {
            $this->db->where('p.status', $status);
        }
        
        return $this->db->count_all_results();
    }
    
    /**
     * Get pelaporan statistics
     */
    private function get_pelaporan_statistics()
    {
        $stats = [];
        
        // Total reports
        $stats['total'] = $this->db->count_all_results('pelaporan');
        
        // Reports by status
        $status_counts = $this->db->select('status, COUNT(*) as count')
                                  ->from('pelaporan')
                                  ->group_by('status')
                                  ->get()
                                  ->result();
        
        $stats['by_status'] = [];
        foreach ($status_counts as $status) {
            $stats['by_status'][$status->status] = $status->count;
        }
        
        // Ensure all statuses are present
        $all_statuses = ['LAPOR', 'DITERIMA', 'DIKERJAKAN', 'DIBATALKAN', 'SELESAI'];
        foreach ($all_statuses as $status) {
            if (!isset($stats['by_status'][$status])) {
                $stats['by_status'][$status] = 0;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get report detail by ID
     */
    private function get_report_detail($id)
    {
        $this->db->select('
            p.*,
            m.nama_lengkap,
            m.no_telpon as pelapor_telepon,
            pk.nama_kategori,
            pk.icon as kategori_icon,
            pk.warna as kategori_warna,
            p.lokasi_lat as latitude,
            p.lokasi_lng as longitude
        ');
        $this->db->from('pelaporan p');
        $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
        $this->db->join('pelaporan_kategori pk', 'p.kategori = pk.nama_kategori', 'left');
        $this->db->where('p.id', $id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get report files
     */
    private function get_report_files($pelaporan_id)
    {
        $files = $this->db->where('pelaporan_id', $pelaporan_id)
                         ->order_by('uploaded_at', 'asc')
                         ->get('pelaporan_files')
                         ->result();
        
        // Debug: Log files data
        error_log("Original files from database for pelaporan ID $pelaporan_id: " . json_encode($files));
        
        // Remove /frontend/ from file_path if it exists
        foreach ($files as $file) {
            if ($file->file_path) {
                // Check for /frontend/ in the path
                if (strpos($file->file_path, '/frontend/') !== false) {
                    $file->file_path = str_replace('/frontend/', '/', $file->file_path);
                    error_log("Modified file_path (removed /frontend/): " . $file->file_path);
                }
                // Also check if path starts with /frontend/
                if (strpos($file->file_path, '/frontend') === 0) {
                    $file->file_path = substr($file->file_path, 9); // Remove '/frontend'
                    if (empty($file->file_path)) {
                        $file->file_path = '/';
                    }
                    error_log("Modified file_path (removed leading /frontend): " . $file->file_path);
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Get report history
     */
    private function get_report_history($pelaporan_id)
    {
        $this->db->select('ph.*, u.nama as updated_by_name');
        $this->db->from('pelaporan_history ph');
        $this->db->join('user u', 'ph.created_by = u.id_user', 'left');
        $this->db->where('ph.pelaporan_id', $pelaporan_id);
        $this->db->order_by('ph.created_at', 'desc');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get report comments
     */
    private function get_report_comments($pelaporan_id)
    {
        $this->db->select('pc.*, m.nama_lengkap');
        $this->db->from('pelaporan_comments pc');
        $this->db->join('masyarakat m', 'pc.created_by = m.id', 'left');
        $this->db->where('pc.pelaporan_id', $pelaporan_id);
        $this->db->where('pc.is_internal', 0);
        $this->db->order_by('pc.created_at', 'desc');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get report average rating
     */
    private function get_report_average_rating($pelaporan_id)
    {
        $result = $this->db->select('AVG(rating) as avg_rating, COUNT(*) as total_ratings')
                          ->where('pelaporan_id', $pelaporan_id)
                          ->where('rating IS NOT NULL')
                          ->get('pelaporan_comments')
                          ->row();
        
        return $result ? (object)[
            'average' => round($result->avg_rating, 1),
            'total' => $result->total_ratings
        ] : (object)[
            'average' => 0,
            'total' => 0
        ];
    }
    
    /**
     * Get status color class
     */
    public function get_status_color($status)
    {
        $colors = [
            'LAPOR' => 'text-yellow-600',
            'DITERIMA' => 'text-blue-600',
            'DIKERJAKAN' => 'text-indigo-600',
            'DIBATALKAN' => 'text-red-600',
            'SELESAI' => 'text-green-600'
        ];
        
        return isset($colors[$status]) ? $colors[$status] : 'text-gray-600';
    }
    
    /**
     * Get status badge color class
     */
    public function get_status_badge_color($status)
    {
        $colors = [
            'LAPOR' => 'bg-yellow-500',
            'DITERIMA' => 'bg-blue-500',
            'DIKERJAKAN' => 'bg-indigo-500',
            'DIBATALKAN' => 'bg-red-500',
            'SELESAI' => 'bg-green-500'
        ];
        
        return isset($colors[$status]) ? $colors[$status] : 'bg-gray-500';
    }
    
    /**
     * Get priority color class
     */
    public function get_priority_color($priority)
    {
        $colors = [
            'RENDAH' => 'bg-green-100 text-green-800',
            'SEDANG' => 'bg-yellow-100 text-yellow-800',
            'TINGGI' => 'bg-orange-100 text-orange-800',
            'URGENT' => 'bg-red-100 text-red-800'
        ];
        
        return isset($colors[$priority]) ? $colors[$priority] : 'bg-gray-100 text-gray-800';
    }
    
    /**
     * AJAX endpoint for getting pelaporan detail
     */
    public function ajax_get_pelaporan_detail()
    {
        $pelaporan_id = $this->input->post('pelaporan_id');
        
        if (!$pelaporan_id || !is_numeric($pelaporan_id)) {
            $response = ['status' => false, 'message' => 'ID pelaporan tidak valid'];
            echo json_encode($response);
            return;
        }
        
        // Get report details
        $report = $this->get_report_detail($pelaporan_id);
        
        if (!$report) {
            $response = ['status' => false, 'message' => 'Pelaporan tidak ditemukan'];
            echo json_encode($response);
            return;
        }
        
        // Get related data
        $files = $this->get_report_files($pelaporan_id);
        $history = $this->get_report_history($pelaporan_id);
        $comments = $this->get_report_comments($pelaporan_id);
        $average_rating = $this->get_report_average_rating($pelaporan_id);
        
        // Debug: Log files data
        error_log("Files for pelaporan ID $pelaporan_id: " . json_encode($files));
        
        // Prepare response
        $response = [
            'status' => true,
            'data' => [
                'report' => $report,
                'files' => $files,
                'history' => $history,
                'comments' => $comments,
                'average_rating' => $average_rating
            ]
        ];
        
        echo json_encode($response);
    }
    
     
    /**
     * Halaman kontak darurat
     */
    public function kontak_darurat()
    {
        // Get settings from database
        $data['site_name'] = $this->get_setting('nama_situs') ?: 'Dashboard Masyarakat';
        $data['site_favicon'] = $this->get_setting('favicon') ?: 'assets/images/favicon.ico';
        
        // Get contact data
        $data['contact_data'] = $this->get_contact_data();
        
        // Get emergency contact data
        $data['emergency_contacts'] = $this->get_emergency_contact_data();
        
        // Page configuration
        $data['page_title'] = 'Kontak Darurat';
        $data['page_description'] = 'Daftar kontak darurat yang dapat dihubungi untuk keadaan darurat';
        $data['page_keywords'] = 'kontak darurat, emergency, bantuan, darurat';
        $data['current_page'] = 'kontak_darurat';
        
        // Load view dengan template frontend
        $this->load->view('frontend/template', $data);
    }
    
    /**
     * Get homepage statistics
     */
    private function get_homepage_statistics()
    {
        $stats = [];
        
        // Total komoditas
        $stats['total_komoditas'] = $this->db->where('komoditas_status', 1)
                                            ->count_all_results('master_komoditas');
        
        // Total pelaporan
        $stats['total_pelaporan'] = $this->db->count_all_results('pelaporan');
        
        // // Total kepengurusan
        // $stats['total_kepengurusan'] = $this->db->count_all_results('kategori_kepengurusan');
        
        // // Total UMKM
        // $stats['total_umkm'] = $this->db->where('status', 1)
        //                               ->count_all_results('umkm');
        
        return $stats;
    }
    
   
    private function get_latest_reports_widget($limit = 3)
    {
        $this->db->select('
            p.*,
            m.nama_lengkap,
            pk.nama_kategori
        ');
        $this->db->from('pelaporan p');
        $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
        $this->db->join('pelaporan_kategori pk', 'p.kategori = pk.nama_kategori', 'left');
        $this->db->order_by('p.created_at', 'desc');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
}