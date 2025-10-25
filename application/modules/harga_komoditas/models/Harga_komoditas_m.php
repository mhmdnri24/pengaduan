<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Harga_komoditas_m extends CI_Model
{
    private $table = 'harga_komoditas_harian';
    private $table_analisis = 'harga_komoditas_analisis';
    private $view_lengkap = 'view_harga_komoditas_lengkap';
    private $view_analisis = 'view_analisis_harga_lengkap';

    public function harga_komoditas_get_all()
    {
        return $this->db->order_by('harga_tanggal', 'desc')
                       ->order_by('harga_id', 'desc')
                       ->get($this->table)->result();
    }
    
    public function harga_komoditas_get_active()
    {
        return $this->db->where('harga_status', 1)
                       ->order_by('harga_tanggal', 'desc')
                       ->order_by('harga_id', 'desc')
                       ->get($this->table)->result();
    }
    
    public function harga_komoditas_by_id($id)
    {
        return $this->db->where('harga_id', $id)->get($this->table)->row();
    }
    
    public function harga_komoditas_by_tanggal_komoditas_pasar($tanggal, $komoditas_id, $pasar_id)
    {
        return $this->db->where('harga_tanggal', $tanggal)
                       ->where('komoditas_id', $komoditas_id)
                       ->where('pasar_id', $pasar_id)
                       ->get($this->table)->row();
    }
    
    public function harga_komoditas_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function harga_komoditas_update_data($post_data, $id)
    {
        $this->db->where('harga_id', $id);
        return $this->db->update($this->table, $post_data);
    }
    
    public function harga_komoditas_delete_data($id)
    {
        $this->db->where('harga_id', $id);
        return $this->db->delete($this->table);
    }

    public function get_harga_lengkap_by_filter($filters = [])
    {
        $this->db->from($this->view_lengkap);
        
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('harga_tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('harga_tanggal <=', $filters['tanggal_sampai']);
        }
        
        if (!empty($filters['komoditas_id'])) {
            $this->db->where('komoditas_id', $filters['komoditas_id']);
        }
        
        if (!empty($filters['pasar_id'])) {
            $this->db->where('pasar_id', $filters['pasar_id']);
        }
        
        if (!empty($filters['kategori_id'])) {
            $this->db->join('master_komoditas mk', 'mk.komoditas_id = ' . $this->view_lengkap . '.komoditas_id');
            $this->db->where('mk.komoditas_parent_id', $filters['kategori_id']);
        }
        
        $this->db->where('harga_status', 1);
        $this->db->order_by('harga_tanggal', 'desc');
        $this->db->order_by('komoditas_nama', 'asc');
        $this->db->order_by('pasar_nama', 'asc');
        
        return $this->db->get()->result();
    }

    public function check_duplicate_harga($tanggal, $komoditas_id, $pasar_id, $exclude_id = null)
    {
        $this->db->where('harga_tanggal', $tanggal);
        $this->db->where('komoditas_id', $komoditas_id);
        $this->db->where('pasar_id', $pasar_id);
        
        if ($exclude_id) {
            $this->db->where('harga_id !=', $exclude_id);
        }
        
        $count = $this->db->count_all_results($this->table);
        return $count > 0;
    }

    public function validate_harga_data($data, $id = null)
    {
        $errors = [];

        // Validasi tanggal
        if (empty($data['harga_tanggal'])) {
            $errors[] = 'Tanggal harga tidak boleh kosong';
        }

        // Validasi komoditas
        if (empty($data['komoditas_id'])) {
            $errors[] = 'Komoditas harus dipilih';
        }

        // Validasi pasar
        if (empty($data['pasar_id'])) {
            $errors[] = 'Pasar harus dipilih';
        }

        // Validasi harga jual
        if (empty($data['harga_jual']) || $data['harga_jual'] <= 0) {
            $errors[] = 'Harga jual harus diisi dan lebih dari 0';
        }

        // Validasi harga beli jika ada
        if (!empty($data['harga_beli']) && $data['harga_beli'] <= 0) {
            $errors[] = 'Harga beli harus lebih dari 0 jika diisi';
        }

        // Validasi logika harga beli vs jual
        if (!empty($data['harga_beli']) && !empty($data['harga_jual'])) {
            if ($data['harga_beli'] >= $data['harga_jual']) {
                $errors[] = 'Harga beli harus lebih kecil dari harga jual';
            }
        }

        // Check for duplicate
        if (!empty($data['harga_tanggal']) && !empty($data['komoditas_id']) && !empty($data['pasar_id'])) {
            if ($this->check_duplicate_harga($data['harga_tanggal'], $data['komoditas_id'], $data['pasar_id'], $id)) {
                $errors[] = 'Data harga untuk tanggal, komoditas, dan pasar ini sudah ada';
            }
        }

        return $errors;
    }

    // =============================================
    // Analisis Harga
    // =============================================

    public function get_analisis_by_filter($filters = [])
    {
        $this->db->from($this->view_analisis);
        
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('analisis_tanggal >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('analisis_tanggal <=', $filters['tanggal_sampai']);
        }
        
        if (!empty($filters['komoditas_id'])) {
            $this->db->where('komoditas_id', $filters['komoditas_id']);
        }
        
        if (!empty($filters['pasar_id'])) {
            $this->db->where('pasar_id', $filters['pasar_id']);
        }
        
        if (!empty($filters['trend_status'])) {
            $this->db->where('trend_status', $filters['trend_status']);
        }
        
        $this->db->order_by('analisis_tanggal', 'desc');
        $this->db->order_by('komoditas_nama', 'asc');
        
        return $this->db->get()->result();
    }

    public function get_trend_harga_komoditas($komoditas_id, $pasar_id = null, $days = 30)
    {
        $this->db->select('harga_tanggal, harga_rata_rata, komoditas_nama, pasar_nama');
        $this->db->from($this->view_lengkap);
        $this->db->where('komoditas_id', $komoditas_id);
        
        if ($pasar_id) {
            $this->db->where('pasar_id', $pasar_id);
        }
        
        $this->db->where('harga_tanggal >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->where('harga_status', 1);
        $this->db->order_by('harga_tanggal', 'asc');
        
        return $this->db->get()->result();
    }

    public function get_perbandingan_harga_antar_pasar($komoditas_id, $tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $this->db->select('pasar_nama, harga_beli, harga_jual, harga_rata_rata, stok_tersedia, kualitas');
        $this->db->from($this->view_lengkap);
        $this->db->where('komoditas_id', $komoditas_id);
        $this->db->where('harga_tanggal', $tanggal);
        $this->db->where('harga_status', 1);
        $this->db->order_by('harga_rata_rata', 'asc');
        
        return $this->db->get()->result();
    }

    // =============================================
    // Statistik dan Dashboard
    // =============================================

    public function get_statistik_harga($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $stats = [];
        
        // Total data harga hari ini
        $stats['total_harga_hari_ini'] = $this->db->where('harga_tanggal', $tanggal)
                                                 ->where('harga_status', 1)
                                                 ->count_all_results($this->table);
        
        // Total komoditas yang dipantau
        $stats['total_komoditas_dipantau'] = $this->db->select('DISTINCT komoditas_id')
                                                     ->where('harga_tanggal', $tanggal)
                                                     ->where('harga_status', 1)
                                                     ->count_all_results($this->table);
        
        // Total pasar yang dipantau
        $stats['total_pasar_dipantau'] = $this->db->select('DISTINCT pasar_id')
                                                 ->where('harga_tanggal', $tanggal)
                                                 ->where('harga_status', 1)
                                                 ->count_all_results($this->table);
        
        // Komoditas dengan harga tertinggi
        $this->db->select('komoditas_nama, pasar_nama, harga_rata_rata');
        $this->db->from($this->view_lengkap);
        $this->db->where('harga_tanggal', $tanggal);
        $this->db->where('harga_status', 1);
        $this->db->order_by('harga_rata_rata', 'desc');
        $this->db->limit(1);
        $stats['harga_tertinggi'] = $this->db->get()->row();
        
        // Komoditas dengan harga terendah
        $this->db->select('komoditas_nama, pasar_nama, harga_rata_rata');
        $this->db->from($this->view_lengkap);
        $this->db->where('harga_tanggal', $tanggal);
        $this->db->where('harga_status', 1);
        $this->db->order_by('harga_rata_rata', 'asc');
        $this->db->limit(1);
        $stats['harga_terendah'] = $this->db->get()->row();
        
        return $stats;
    }

    public function get_top_komoditas_volatil($limit = 5)
    {
        $this->db->select('komoditas_nama, COUNT(*) as jumlah_volatil, AVG(ABS(persentase_harian)) as rata_volatilitas');
        $this->db->from($this->view_analisis);
        $this->db->where('volatilitas', 'TINGGI');
        $this->db->where('analisis_tanggal >=', date('Y-m-d', strtotime('-30 days')));
        $this->db->group_by('komoditas_id, komoditas_nama');
        $this->db->order_by('rata_volatilitas', 'desc');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }

    // =============================================
    // DataTables Server-Side Processing
    // =============================================

    public function get_datatables($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $select, $join, $where, $order);
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $select, $join, $where, $order);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($table, $where = null)
    {
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($table);
    }

    private function _get_datatables_query($table, $column_order, $column_search, $select = '*', $join = null, $where = null, $order = null)
    {
        $this->db->select($select);
        $this->db->from($table);

        if ($join) {
            foreach ($join as $j) {
                $this->db->join($j[0], $j[1], isset($j[2]) ? $j[2] : '');
            }
        }

        if ($where) {
            $this->db->where($where);
        }

        $i = 0;
        foreach ($column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($order)) {
            $order_key = array_keys($order)[0];
            $this->db->order_by($order_key, $order[$order_key]);
        }
    }

    // =============================================
    // Utility Functions
    // =============================================

    public function execute_analisis_procedure($tanggal, $komoditas_id, $pasar_id)
    {
        $sql = "CALL sp_hitung_analisis_harga(?, ?, ?)";
        return $this->db->query($sql, [$tanggal, $komoditas_id, $pasar_id]);
    }

    public function get_harga_untuk_chart($komoditas_id, $pasar_id = null, $days = 30)
    {
        $this->db->select('DATE(harga_tanggal) as tanggal, AVG(harga_rata_rata) as harga_rata');
        $this->db->from($this->table);
        $this->db->where('komoditas_id', $komoditas_id);
        
        if ($pasar_id) {
            $this->db->where('pasar_id', $pasar_id);
        }
        
        $this->db->where('harga_tanggal >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->where('harga_status', 1);
        $this->db->group_by('DATE(harga_tanggal)');
        $this->db->order_by('harga_tanggal', 'asc');
        
        return $this->db->get()->result();
    }

    // =============================================
    // Dashboard Specific Methods
    // =============================================

    public function get_recent_activity($limit = 10)
    {
        $this->db->select('h.harga_id, h.harga_tanggal, h.harga_rata_rata, k.komoditas_nama, p.pasar_nama, h.harga_created_at');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('DATE(h.harga_created_at)', date('Y-m-d'));
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_created_at', 'desc');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    public function get_commodities_with_prices()
    {
        $this->db->select('k.komoditas_id, k.komoditas_nama, k.komoditas_satuan, COUNT(h.harga_id) as total_records');
        $this->db->from('master_komoditas k');
        $this->db->join($this->table . ' h', 'k.komoditas_id = h.komoditas_id', 'inner');
        $this->db->where('k.komoditas_status', 1);
        $this->db->where('h.harga_status', 1);
        $this->db->group_by('k.komoditas_id, k.komoditas_nama, k.komoditas_satuan');
        $this->db->having('total_records >', 0);
        $this->db->order_by('k.komoditas_nama', 'asc');

        return $this->db->get()->result();
    }

    public function get_price_comparison_data($komoditas_id, $date_from, $date_to)
    {
        $this->db->select('h.harga_tanggal, h.harga_rata_rata, h.harga_beli, h.harga_jual, h.stok_tersedia, h.kualitas, p.pasar_nama');
        $this->db->from($this->view_lengkap . ' h');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.komoditas_id', $komoditas_id);
        $this->db->where('h.harga_tanggal >=', $date_from);
        $this->db->where('h.harga_tanggal <=', $date_to);
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_tanggal', 'desc');

        $result = $this->db->get()->result();

        return $result;
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Get latest prices for frontend
     */
    public function get_latest_prices($limit = 10)
    {
        $this->db->select('h.*, k.komoditas_nama, k.komoditas_satuan, p.pasar_nama');
        $this->db->from($this->table . ' h');
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
     * Get prices by date and market for frontend
     */
    public function get_prices_by_date_and_market($tanggal, $pasar_id = 'all')
    {
        $this->db->select('h.*, k.komoditas_nama, k.komoditas_satuan, k.komoditas_parent_id, p.pasar_nama, kp.komoditas_nama as parent_nama');
        $this->db->from($this->table . ' h');
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

        return $this->db->get()->result();

        // Calculate price changes
        foreach ($result as $key => $row) {
            if ($key > 0 && isset($result[$key - 1])) {
                $prev_price = $result[$key - 1]->harga_rata_rata;
                $current_price = $row->harga_rata_rata;

                if ($prev_price > 0) {
                    $row->perubahan_harga = $current_price - $prev_price;
                    $row->persentase_perubahan = (($current_price - $prev_price) / $prev_price) * 100;
                } else {
                    $row->perubahan_harga = 0;
                    $row->persentase_perubahan = 0;
                }
            } else {
                $row->perubahan_harga = 0;
                $row->persentase_perubahan = 0;
            }
        }

        return $result;
    }

    public function get_price_detail($komoditas_id, $tanggal)
    {
        $this->db->select('h.*, k.komoditas_nama, k.komoditas_satuan, p.pasar_nama, p.pasar_alamat');
        $this->db->from($this->view_lengkap . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.komoditas_id', $komoditas_id);
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);

        return $this->db->get()->result();
    }

    public function get_chart_data($komoditas_id, $date_from, $date_to)
    {
        // Get commodity name
        $komoditas = $this->db->select('komoditas_nama')->where('komoditas_id', $komoditas_id)->get('master_komoditas')->row();

        // Get price data for chart
        $this->db->select('harga_tanggal, AVG(harga_rata_rata) as avg_price');
        $this->db->from($this->table);
        $this->db->where('komoditas_id', $komoditas_id);
        $this->db->where('harga_tanggal >=', $date_from);
        $this->db->where('harga_tanggal <=', $date_to);
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

        return [
            'labels' => $labels,
            'data' => $data,
            'komoditas_nama' => $komoditas ? $komoditas->komoditas_nama : 'Unknown'
        ];
    }

    // =============================================
    // New Dashboard Specific Methods
    // =============================================

    /**
     * Get hierarchical commodity data with prices for specific date range and market
     */
    public function get_hierarchical_price_data($date_from, $date_to, $pasar_id = null, $filter_type = 'custom')
    {
        // Adjust date range based on filter type
        switch ($filter_type) {
            case 'today':
                $date_from = $date_to = date('Y-m-d');
                break;
            case 'week':
                $date_from = date('Y-m-d', strtotime('monday this week'));
                $date_to = date('Y-m-d');
                break;
            case 'month':
                $date_from = date('Y-m-01');
                $date_to = date('Y-m-d');
                break;
        }

        // Get all dates in range
        $dates = $this->get_date_range($date_from, $date_to);

        // Get hierarchical commodity structure
        $sql = "
            SELECT
                k.komoditas_id,
                k.komoditas_nama,
                k.komoditas_satuan,
                k.komoditas_parent_id,
                kp.komoditas_nama as parent_nama,
                k.komoditas_urutan
            FROM master_komoditas k
            LEFT JOIN master_komoditas kp ON k.komoditas_parent_id = kp.komoditas_id
            WHERE k.komoditas_status = 1
            ORDER BY
                CASE WHEN k.komoditas_parent_id = 0 THEN k.komoditas_urutan ELSE kp.komoditas_urutan END,
                k.komoditas_parent_id,
                k.komoditas_urutan
        ";

        $commodities = $this->db->query($sql)->result();

        // Get price data for all commodities and dates
        $price_data = $this->get_price_matrix($commodities, $dates, $pasar_id);

        // Structure the data hierarchically
        $structured_data = [];
        $parent_map = [];

        foreach ($commodities as $commodity) {
            $commodity->dates = [];
            $commodity->price_changes = [];

            // Add price data for each date
            foreach ($dates as $date) {
                $price_key = $commodity->komoditas_id . '_' . $date;
                $commodity->dates[$date] = isset($price_data[$price_key]) ? $price_data[$price_key] : null;
            }

            // Calculate price changes
            $commodity->price_changes = $this->calculate_price_changes($commodity->dates);

            if ($commodity->komoditas_parent_id == 0) {
                // Parent commodity
                $commodity->children = [];
                $parent_map[$commodity->komoditas_id] = $commodity;
                $structured_data[] = $commodity;
            }
        }

        // Add children to parents
        foreach ($commodities as $commodity) {
            if ($commodity->komoditas_parent_id != 0 && isset($parent_map[$commodity->komoditas_parent_id])) {
                $parent_map[$commodity->komoditas_parent_id]->children[] = $commodity;
            }
        }

        return [
            'data' => $structured_data,
            'dates' => $dates,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'filter_type' => $filter_type,
            'total_commodities' => count($commodities),
            'total_with_prices' => count(array_filter($commodities, function($c) {
                return !empty(array_filter($c->dates));
            }))
        ];
    }

    /**
     * Get date range array
     */
    private function get_date_range($date_from, $date_to)
    {
        $dates = [];
        $current = strtotime($date_from);
        $end = strtotime($date_to);

        while ($current <= $end) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        return $dates;
    }

    /**
     * Get price matrix for commodities and dates
     */
    private function get_price_matrix($commodities, $dates, $pasar_id = null)
    {
        if (empty($commodities) || empty($dates)) {
            return [];
        }

        $commodity_ids = array_column($commodities, 'komoditas_id');

        $this->db->select('komoditas_id, harga_tanggal, AVG(harga_rata_rata) as avg_price, COUNT(*) as market_count');
        $this->db->from($this->table);
        $this->db->where_in('komoditas_id', $commodity_ids);
        $this->db->where_in('harga_tanggal', $dates);
        $this->db->where('harga_status', 1);

        if ($pasar_id && $pasar_id != 'all') {
            $this->db->where('pasar_id', $pasar_id);
        }

        $this->db->group_by(['komoditas_id', 'harga_tanggal']);

        $results = $this->db->get()->result();

        $price_matrix = [];
        foreach ($results as $row) {
            $key = $row->komoditas_id . '_' . $row->harga_tanggal;
            $price_matrix[$key] = [
                'price' => floatval($row->avg_price),
                'market_count' => intval($row->market_count)
            ];
        }

        return $price_matrix;
    }

    /**
     * Calculate price changes for a commodity
     */
    private function calculate_price_changes($dates_data)
    {
        $changes = [];
        $prev_price = null;

        foreach ($dates_data as $date => $data) {
            if ($data && $data['price'] > 0) {
                if ($prev_price !== null && $prev_price > 0) {
                    $change = (($data['price'] - $prev_price) / $prev_price) * 100;
                    $changes[$date] = [
                        'percentage' => round($change, 2),
                        'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable'),
                        'amount' => $data['price'] - $prev_price
                    ];
                } else {
                    $changes[$date] = [
                        'percentage' => 0,
                        'direction' => 'stable',
                        'amount' => 0
                    ];
                }
                $prev_price = $data['price'];
            } else {
                $changes[$date] = null;
            }
        }

        return $changes;
    }

    /**
     * Get overall market analysis
     */
    public function get_market_analysis($date_from, $date_to, $pasar_id = null)
    {
        $this->db->select('
            COUNT(DISTINCT komoditas_id) as total_commodities,
            AVG(harga_rata_rata) as avg_price,
            MIN(harga_rata_rata) as min_price,
            MAX(harga_rata_rata) as max_price,
            COUNT(*) as total_records
        ');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $date_from);
        $this->db->where('harga_tanggal <=', $date_to);
        $this->db->where('harga_status', 1);

        if ($pasar_id && $pasar_id != 'all') {
            $this->db->where('pasar_id', $pasar_id);
        }

        $analysis = $this->db->get()->row();

        // Get trend analysis
        $trend_analysis = $this->get_trend_analysis($date_from, $date_to, $pasar_id);

        return [
            'summary' => $analysis,
            'trend' => $trend_analysis
        ];
    }

    /**
     * Get trend analysis
     */
    private function get_trend_analysis($date_from, $date_to, $pasar_id = null)
    {
        // Get price trends
        $this->db->select('
            harga_tanggal,
            AVG(harga_rata_rata) as daily_avg,
            COUNT(DISTINCT komoditas_id) as commodity_count
        ');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $date_from);
        $this->db->where('harga_tanggal <=', $date_to);
        $this->db->where('harga_status', 1);

        if ($pasar_id && $pasar_id != 'all') {
            $this->db->where('pasar_id', $pasar_id);
        }

        $this->db->group_by('harga_tanggal');
        $this->db->order_by('harga_tanggal', 'asc');

        $daily_trends = $this->db->get()->result();

        // Calculate overall trend
        $trend_direction = 'stable';
        $trend_percentage = 0;

        if (count($daily_trends) >= 2) {
            $first_price = $daily_trends[0]->daily_avg;
            $last_price = end($daily_trends)->daily_avg;

            if ($first_price > 0) {
                $trend_percentage = (($last_price - $first_price) / $first_price) * 100;
                $trend_direction = $trend_percentage > 1 ? 'up' : ($trend_percentage < -1 ? 'down' : 'stable');
            }
        }

        return [
            'daily_data' => $daily_trends,
            'overall_direction' => $trend_direction,
            'overall_percentage' => round($trend_percentage, 2)
        ];
    }

    /**
     * Get price disparity data for chart
     */
    public function get_price_disparity_data($date_from, $date_to, $pasar_id = null)
    {
        // First, try to get commodities with multiple markets (original logic)
        $this->db->select('
            k.komoditas_nama,
            k.komoditas_satuan,
            AVG(h.harga_rata_rata) as avg_price,
            MIN(h.harga_rata_rata) as min_price,
            MAX(h.harga_rata_rata) as max_price,
            (MAX(h.harga_rata_rata) - MIN(h.harga_rata_rata)) as price_range,
            COUNT(DISTINCT h.pasar_id) as market_count,
            COUNT(h.harga_id) as data_count
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.harga_tanggal >=', $date_from);
        $this->db->where('h.harga_tanggal <=', $date_to);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if ($pasar_id && $pasar_id != 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->group_by('h.komoditas_id');
        $this->db->having('market_count >', 1); // Only show commodities with multiple markets
        $this->db->order_by('price_range', 'desc');
        $this->db->limit(10);

        $multi_market_data = $this->db->get()->result();

        // If we have enough multi-market data, return it
        if (count($multi_market_data) >= 3) {
            return $multi_market_data;
        }

        // If not enough multi-market data, get commodities with at least some data
        // Reset query
        $this->db->reset_query();

        $this->db->select('
            k.komoditas_nama,
            k.komoditas_satuan,
            AVG(h.harga_rata_rata) as avg_price,
            MIN(h.harga_rata_rata) as min_price,
            MAX(h.harga_rata_rata) as max_price,
            (MAX(h.harga_rata_rata) - MIN(h.harga_rata_rata)) as price_range,
            COUNT(DISTINCT h.pasar_id) as market_count,
            COUNT(h.harga_id) as data_count
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.harga_tanggal >=', $date_from);
        $this->db->where('h.harga_tanggal <=', $date_to);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if ($pasar_id && $pasar_id != 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->group_by('h.komoditas_id');
        $this->db->having('data_count >=', 1); // At least one data point
        $this->db->order_by('data_count', 'desc'); // Order by most data points
        $this->db->limit(10);

        $fallback_data = $this->db->get()->result();

        // If we still have no data, return empty array (will show placeholder)
        return $fallback_data ?: [];
    }

    // ========== NEW FRONTEND METHODS FOR CHART AND TABLE ==========

    /**
     * Get price chart data for specific commodity
     */
    public function get_price_chart_data($komoditas_id, $pasar_id = 'all', $days = 7)
    {
        $this->db->select('h.harga_tanggal, AVG(h.harga_rata_rata) as avg_price, k.komoditas_nama, k.komoditas_satuan');
        $this->db->from($this->table . ' h');
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
     * Get structured prices for table display
     */
    public function get_structured_prices($tanggal, $pasar_id = 'all')
    {
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama
        ');
        $this->db->from($this->table . ' h');
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
        $this->db->select('komoditas_id, pasar_id, harga_rata_rata');
        $this->db->from($this->table);
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

    // ========== NEW DATE RANGE METHODS ==========

    /**
     * Get price chart data with date range support
     */
    public function get_price_chart_data_range($komoditas_id, $pasar_id = 'all', $start_date, $end_date, $date_range_type = 'custom')
    {
        $this->db->select('h.harga_tanggal, AVG(h.harga_rata_rata) as avg_price, k.komoditas_nama, k.komoditas_satuan');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.komoditas_id', $komoditas_id);
        $this->db->where('h.harga_tanggal >=', $start_date);
        $this->db->where('h.harga_tanggal <=', $end_date);
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
            // Format label berdasarkan range type
            if ($date_range_type === 'today') {
                $labels[] = date('H:i', strtotime($row->harga_tanggal));
            } elseif ($date_range_type === 'week') {
                $labels[] = date('d/m', strtotime($row->harga_tanggal));
            } elseif ($date_range_type === 'month') {
                $labels[] = date('d/m', strtotime($row->harga_tanggal));
            } else {
                $labels[] = date('d/m/Y', strtotime($row->harga_tanggal));
            }

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
     * Get structured prices with date range support
     */
    public function get_structured_prices_range($start_date, $end_date, $pasar_id = 'all', $date_range_type = 'today')
    {
        // For today, use the latest date in range
        if ($date_range_type === 'today') {
            $target_date = $end_date;

            $this->db->select('
                h.*,
                k.komoditas_nama,
                k.komoditas_satuan,
                p.pasar_nama,
                pk.komoditas_nama as parent_nama
            ');
            $this->db->from($this->table . ' h');
            $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
            $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
            $this->db->join('master_komoditas pk', 'k.komoditas_parent_id = pk.komoditas_id', 'left');
            $this->db->where('h.harga_tanggal', $target_date);
            $this->db->where('h.harga_status', 1);

            if ($pasar_id !== 'all') {
                $this->db->where('h.pasar_id', $pasar_id);
            }

            $this->db->order_by('pk.komoditas_nama', 'asc');
            $this->db->order_by('k.komoditas_nama', 'asc');
            $this->db->order_by('p.pasar_nama', 'asc');

            $results = $this->db->get()->result();
        } else {
            // For range periods, get average prices
            $this->db->select('
                AVG(h.harga_rata_rata) as harga_rata_rata,
                k.komoditas_id,
                k.komoditas_nama,
                k.komoditas_satuan,
                p.pasar_id,
                p.pasar_nama,
                pk.komoditas_nama as parent_nama,
                MAX(h.harga_tanggal) as harga_tanggal
            ');
            $this->db->from($this->table . ' h');
            $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
            $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
            $this->db->join('master_komoditas pk', 'k.komoditas_parent_id = pk.komoditas_id', 'left');
            $this->db->where('h.harga_tanggal >=', $start_date);
            $this->db->where('h.harga_tanggal <=', $end_date);
            $this->db->where('h.harga_status', 1);

            if ($pasar_id !== 'all') {
                $this->db->where('h.pasar_id', $pasar_id);
            }

            $this->db->group_by(['h.komoditas_id', 'h.pasar_id']);
            $this->db->order_by('pk.komoditas_nama', 'asc');
            $this->db->order_by('k.komoditas_nama', 'asc');
            $this->db->order_by('p.pasar_nama', 'asc');

            $results = $this->db->get()->result();
        }

        // Get previous prices for comparison
        $prev_prices = $this->get_previous_prices_range($start_date, $end_date, $date_range_type);

        // Structure the data
        $structured_data = [];

        foreach ($results as $row) {
            $parent_name = $row->parent_nama ?: 'LAINNYA';

            if (!isset($structured_data[$parent_name])) {
                $structured_data[$parent_name] = [];
            }

            // Calculate percentage change
            $key = $row->komoditas_id . '_' . $row->pasar_id;
            $current_price = floatval($row->harga_rata_rata);
            $prev_price = isset($prev_prices[$key]) ? floatval($prev_prices[$key]) : $current_price;

            $percentage_change = 0;
            $status_perubahan = 'stabil';
            $keterangan_perubahan = 'Stabil';

            if ($prev_price > 0 && $current_price != $prev_price) {
                $percentage_change = (($current_price - $prev_price) / $prev_price) * 100;

                if ($percentage_change > 0) {
                    $status_perubahan = 'naik';
                    $keterangan_perubahan = 'Naik ' . number_format(abs($percentage_change), 1) . '%';
                } else {
                    $status_perubahan = 'turun';
                    $keterangan_perubahan = 'Turun ' . number_format(abs($percentage_change), 1) . '%';
                }
            }

            $structured_data[$parent_name][] = [
                'komoditas_id' => $row->komoditas_id,
                'komoditas_nama' => $row->komoditas_nama,
                'komoditas_satuan' => $row->komoditas_satuan,
                'pasar_nama' => $row->pasar_nama,
                'harga_rata_rata' => $current_price,
                'harga_tanggal' => $row->harga_tanggal,
                'persentase_perubahan' => $percentage_change,
                'status_perubahan' => $status_perubahan,
                'keterangan_perubahan' => $keterangan_perubahan
            ];
        }

        return $structured_data;
    }

    /**
     * Get previous prices for range comparison
     */
    private function get_previous_prices_range($start_date, $end_date, $date_range_type)
    {
        // Calculate comparison period based on range type
        $start_obj = new DateTime($start_date);
        $end_obj = new DateTime($end_date);
        $diff_days = $start_obj->diff($end_obj)->days + 1;

        // Get previous period with same duration
        $prev_end = clone $start_obj;
        $prev_end->sub(new DateInterval('P1D'));
        $prev_start = clone $prev_end;
        $prev_start->sub(new DateInterval('P' . ($diff_days - 1) . 'D'));

        $this->db->select('
            AVG(h.harga_rata_rata) as harga_rata_rata,
            h.komoditas_id,
            h.pasar_id
        ');
        $this->db->from($this->table . ' h');
        $this->db->where('h.harga_tanggal >=', $prev_start->format('Y-m-d'));
        $this->db->where('h.harga_tanggal <=', $prev_end->format('Y-m-d'));
        $this->db->where('h.harga_status', 1);
        $this->db->group_by(['h.komoditas_id', 'h.pasar_id']);

        $results = $this->db->get()->result();

        $prev_prices = [];
        foreach ($results as $row) {
            $key = $row->komoditas_id . '_' . $row->pasar_id;
            $prev_prices[$key] = $row->harga_rata_rata;
        }

        return $prev_prices;
    }

    /**
     * Get chart data for all commodities (top 5 most traded)
     */
    public function get_all_commodities_chart_data($pasar_id = 'all', $start_date, $end_date, $date_range_type = 'custom')
    {
        // Get top 5 most traded commodities in the date range
        $this->db->select('k.komoditas_id, k.komoditas_nama, k.komoditas_satuan, COUNT(*) as trade_count');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->where('h.harga_tanggal >=', $start_date);
        $this->db->where('h.harga_tanggal <=', $end_date);

        if ($pasar_id !== 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->group_by('k.komoditas_id, k.komoditas_nama, k.komoditas_satuan');
        $this->db->order_by('trade_count', 'DESC');
        $this->db->limit(5);

        $top_commodities = $this->db->get()->result();

        if (empty($top_commodities)) {
            return [
                'labels' => [],
                'datasets' => [],
                'title' => 'Tidak ada data komoditas'
            ];
        }

        // Get price data for each top commodity
        $datasets = [];
        $labels = [];
        $colors = [
            'rgb(59, 130, 246)',   // Blue
            'rgb(16, 185, 129)',   // Green
            'rgb(245, 158, 11)',   // Yellow
            'rgb(239, 68, 68)',    // Red
            'rgb(139, 92, 246)'    // Purple
        ];

        // Generate date labels
        $current_date = new DateTime($start_date);
        $end_date_obj = new DateTime($end_date);

        while ($current_date <= $end_date_obj) {
            $labels[] = $current_date->format('Y-m-d');
            $current_date->add(new DateInterval('P1D'));
        }

        // Get data for each commodity
        foreach ($top_commodities as $index => $commodity) {
            $this->db->select('h.harga_tanggal, AVG(h.harga_rata_rata) as avg_price');
            $this->db->from($this->table . ' h');
            $this->db->where('h.komoditas_id', $commodity->komoditas_id);
            $this->db->where('h.harga_tanggal >=', $start_date);
            $this->db->where('h.harga_tanggal <=', $end_date);

            if ($pasar_id !== 'all') {
                $this->db->where('h.pasar_id', $pasar_id);
            }

            $this->db->group_by('h.harga_tanggal');
            $this->db->order_by('h.harga_tanggal', 'ASC');

            $price_data = $this->db->get()->result();

            // Create data array matching labels
            $data = [];
            $price_map = [];

            foreach ($price_data as $price) {
                $price_map[$price->harga_tanggal] = (float)$price->avg_price;
            }

            foreach ($labels as $date) {
                $data[] = isset($price_map[$date]) ? $price_map[$date] : null;
            }

            $datasets[] = [
                'label' => $commodity->komoditas_nama . ' (Rp/' . $commodity->komoditas_satuan . ')',
                'data' => $data,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => str_replace('rgb', 'rgba', str_replace(')', ', 0.1)', $colors[$index % count($colors)])),
                'borderWidth' => 2,
                'fill' => false,
                'tension' => 0.4
            ];
        }

        // Format labels for display
        $formatted_labels = [];
        foreach ($labels as $date) {
            $formatted_labels[] = date('d/m', strtotime($date));
        }

        return [
            'labels' => $formatted_labels,
            'datasets' => $datasets,
            'title' => 'Trend Harga Komoditas Utama'
        ];
    }

}
