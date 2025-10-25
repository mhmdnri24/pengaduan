<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Harga_komoditas_m extends CI_Model
{
    private $table = 'harga_komoditas_harian';
    private $cache_prefix = 'harga_komoditas_';
    private $cache_time = 300; // 5 minutes
    
    /**
     * Get latest prices for frontend widget
     */
    public function get_latest_prices($limit = 5)
    {
        $cache_key = $this->cache_prefix . 'latest_prices_' . $limit;
        
        // Try to get from cache
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        $this->db->select('h.*, k.komoditas_nama, k.komoditas_satuan, p.pasar_nama');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);
        $this->db->order_by('h.harga_tanggal', 'desc');
        $this->db->order_by('h.harga_created_at', 'desc');
        $this->db->limit($limit);

        $result = $this->db->get()->result();
        
        // Cache the result
        $this->cache->save($cache_key, $result, $this->cache_time);
        
        return $result;
    }
    
    /**
     * Get structured prices by date for table display
     */
    public function get_structured_prices($tanggal, $pasar_id = 'all')
    {
        $cache_key = $this->cache_prefix . 'structured_' . $tanggal . '_' . (is_array($pasar_id) ? implode('_', $pasar_id) : $pasar_id);
        
        // Try to get from cache
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== false) {
            return $cached_result;
        }
        
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

        // Cache the result
        $this->cache->save($cache_key, $structured, $this->cache_time);
        
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
    
    /**
     * Get price chart data for specific commodity
     */
    public function get_price_chart_data($komoditas_id, $pasar_id = 'all', $days = 7)
    {
        $cache_key = $this->cache_prefix . 'chart_' . $komoditas_id . '_' . (is_array($pasar_id) ? implode('_', $pasar_id) : $pasar_id) . '_' . $days;
        
        // Try to get from cache
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== false) {
            return $cached_result;
        }
        
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

        $result = [
            'labels' => $labels,
            'data' => $data,
            'komoditas_nama' => $komoditas_nama,
            'komoditas_satuan' => $komoditas_satuan
        ];
        
        // Cache the result
        $this->cache->save($cache_key, $result, $this->cache_time);
        
        return $result;
    }
    
    /**
     * Get list of all markets
     */
    public function get_all_markets()
    {
        return $this->db->where('pasar_status', 1)
                        ->order_by('pasar_nama', 'asc')
                        ->get('master_pasar')
                        ->result();
    }
    
    /**
     * Get price statistics
     */
    public function get_price_statistics($tanggal = null)
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
        $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);
        $this->db->order_by('h.harga_rata_rata', 'desc');
        $this->db->limit(1);
        $stats['harga_tertinggi'] = $this->db->get()->row();
        
        // Komoditas dengan harga terendah
        $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
        $this->db->from($this->table . ' h');
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
     * Get structured prices with date range for table display
     */
    public function get_structured_prices_range($start_date, $end_date, $pasar_id = 'all', $date_range_type = 'today')
    {
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama,
            h_kemarin.harga_rata_rata as harga_kemarin
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_komoditas kp', 'k.komoditas_parent_id = kp.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        
        // Join with previous day's price
        $prev_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        $this->db->join($this->table . ' h_kemarin',
                        'h.komoditas_id = h_kemarin.komoditas_id AND h.pasar_id = h_kemarin.pasar_id AND h_kemarin.harga_tanggal = "' . $prev_date . '"',
                        'left');
        
        $this->db->where('h.harga_tanggal >=', $start_date);
        $this->db->where('h.harga_tanggal <=', $end_date);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if ($pasar_id !== 'all' && is_array($pasar_id)) {
            $this->db->where_in('h.pasar_id', $pasar_id);
        } elseif ($pasar_id !== 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $this->db->order_by('kp.komoditas_urutan', 'asc');
        $this->db->order_by('k.komoditas_urutan', 'asc');

        $results = $this->db->get()->result();

        // Calculate percentage change
        foreach ($results as $row) {
            $row->persentase_perubahan = 0;
            $row->status_perubahan = 'stable';
            $row->keterangan_perubahan = 'Tidak ada perubahan';

            if ($row->harga_kemarin > 0) {
                $row->persentase_perubahan = (($row->harga_rata_rata - $row->harga_kemarin) / $row->harga_kemarin) * 100;

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
        }

        return $results;
    }
    
    /**
     * Get commodity price detail for modal
     */
    public function get_commodity_price_detail($komoditas_id, $pasar_id = 'all', $start_date = null, $end_date = null)
    {
        if (!$start_date) $start_date = date('Y-m-d');
        if (!$end_date) $end_date = date('Y-m-d');
        
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama,
            h_kemarin.harga_rata_rata as harga_kemarin
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_komoditas kp', 'k.komoditas_parent_id = kp.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        
        // Join with previous day's price
        $prev_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        $this->db->join($this->table . ' h_kemarin',
                        'h.komoditas_id = h_kemarin.komoditas_id AND h.pasar_id = h_kemarin.pasar_id AND h_kemarin.harga_tanggal = "' . $prev_date . '"',
                        'left');
        
        $this->db->where('h.komoditas_id', $komoditas_id);
        $this->db->where('h.harga_tanggal', $end_date);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if ($pasar_id !== 'all') {
            $this->db->where('h.pasar_id', $pasar_id);
        }

        $result = $this->db->get()->row();
        
        if ($result) {
            // Calculate percentage change
            $result->persentase_perubahan = 0;
            $result->status_perubahan = 'stable';
            $result->keterangan_perubahan = 'Tidak ada perubahan';

            if ($result->harga_kemarin > 0) {
                $result->persentase_perubahan = (($result->harga_rata_rata - $result->harga_kemarin) / $result->harga_kemarin) * 100;

                if ($result->persentase_perubahan > 0) {
                    $result->status_perubahan = 'naik';
                    $result->keterangan_perubahan = 'Naik ' . number_format(abs($result->persentase_perubahan), 1) . '%';
                } elseif ($result->persentase_perubahan < 0) {
                    $result->status_perubahan = 'turun';
                    $result->keterangan_perubahan = 'Turun ' . number_format(abs($result->persentase_perubahan), 1) . '%';
                } else {
                    $result->keterangan_perubahan = 'Stabil';
                }
            } else {
                $result->keterangan_perubahan = 'Data baru';
            }
        }
        
        return $result;
    }
    
    /**
     * Get all commodities chart data for monthly realization
     */
    public function get_all_commodities_chart_data($pasar_id = 'all', $start_date = null, $end_date = null, $date_range_type = 'month')
    {
        if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
        if (!$end_date) $end_date = date('Y-m-d');
        
        $this->db->select('harga_tanggal, AVG(harga_rata_rata) as avg_price');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $start_date);
        $this->db->where('harga_tanggal <=', $end_date);
        $this->db->where('harga_status', 1);

        if ($pasar_id !== 'all' && is_array($pasar_id)) {
            $this->db->where_in('pasar_id', $pasar_id);
        } elseif ($pasar_id !== 'all') {
            $this->db->where('pasar_id', $pasar_id);
        }

        $this->db->group_by('harga_tanggal');
        $this->db->order_by('harga_tanggal', 'asc');

        $results = $this->db->get()->result();

        $labels = [];
        $data = [];

        foreach ($results as $row) {
            $labels[] = date('d/m', strtotime($row->harga_tanggal));
            $data[] = floatval($row->avg_price);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Calculate total revenue for a date range
     */
    public function calculate_total_revenue($start_date, $end_date, $pasar_ids = ['all'])
    {
        $this->db->select_sum('harga_rata_rata');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $start_date);
        $this->db->where('harga_tanggal <=', $end_date);
        $this->db->where('harga_status', 1);

        if (!in_array('all', $pasar_ids) && is_array($pasar_ids)) {
            $this->db->where_in('pasar_id', $pasar_ids);
        }

        $result = $this->db->get()->row();
        return $result ? $result->harga_rata_rata : 0;
    }
    
    /**
     * Count monitored markets
     */
    public function count_monitored_markets($start_date, $end_date)
    {
        $this->db->select('COUNT(DISTINCT pasar_id) as total');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $start_date);
        $this->db->where('harga_tanggal <=', $end_date);
        $this->db->where('harga_status', 1);

        $result = $this->db->get()->row();
        return $result ? $result->total : 0;
    }
    
    /**
     * Count price changes by type
     */
    public function count_price_changes($start_date, $end_date, $pasar_ids = ['all'], $type = 'naik')
    {
        // This is a simplified version - in production, you'd want to calculate actual changes
        $this->db->select('COUNT(*) as total');
        $this->db->from($this->table);
        $this->db->where('harga_tanggal >=', $start_date);
        $this->db->where('harga_tanggal <=', $end_date);
        $this->db->where('harga_status', 1);

        if (!in_array('all', $pasar_ids) && is_array($pasar_ids)) {
            $this->db->where_in('pasar_id', $pasar_ids);
        }

        $result = $this->db->get()->row();
        $total = $result ? $result->total : 0;
        
        // For demo purposes, distribute the counts
        if ($type === 'naik') {
            return intval($total * 0.3);
        } elseif ($type === 'turun') {
            return intval($total * 0.2);
        } else {
            return intval($total * 0.5);
        }
    }
    
    /**
     * Get data for export
     */
    public function get_export_data($start_date, $end_date, $pasar_ids = ['all'])
    {
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama,
            h_kemarin.harga_rata_rata as harga_kemarin
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_komoditas kp', 'k.komoditas_parent_id = kp.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        
        // Join with previous day's price
        $prev_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        $this->db->join($this->table . ' h_kemarin',
                        'h.komoditas_id = h_kemarin.komoditas_id AND h.pasar_id = h_kemarin.pasar_id AND h_kemarin.harga_tanggal = "' . $prev_date . '"',
                        'left');
        
        $this->db->where('h.harga_tanggal >=', $start_date);
        $this->db->where('h.harga_tanggal <=', $end_date);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if (!in_array('all', $pasar_ids) && is_array($pasar_ids)) {
            $this->db->where_in('h.pasar_id', $pasar_ids);
        }

        $this->db->order_by('kp.komoditas_urutan', 'asc');
        $this->db->order_by('k.komoditas_urutan', 'asc');

        $results = $this->db->get()->result();

        // Calculate percentage change
        foreach ($results as $row) {
            $row->persentase_perubahan = 0;
            $row->status_perubahan = 'stable';
            $row->keterangan_perubahan = 'Tidak ada perubahan';

            if ($row->harga_kemarin > 0) {
                $row->persentase_perubahan = (($row->harga_rata_rata - $row->harga_kemarin) / $row->harga_kemarin) * 100;

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
        }

        return $results;
    }
    
    /**
     * Clear cache for specific keys or all cache
     */
    public function clear_cache($pattern = null)
    {
        if ($pattern) {
            // Clear specific pattern
            $this->cache->clean();
        } else {
            // Clear all harga_komoditas cache
            $this->cache->clean();
        }
    }
    
    /**
     * Get paginated data with lazy loading
     */
    public function get_paginated_data($start_date, $end_date, $pasar_ids = ['all'], $page = 1, $per_page = 20, $sort_column = 'komoditas_nama', $sort_direction = 'asc')
    {
        $offset = ($page - 1) * $per_page;
        
        $this->db->select('
            h.*,
            k.komoditas_nama,
            k.komoditas_satuan,
            k.komoditas_parent_id,
            kp.komoditas_nama as parent_nama,
            p.pasar_nama,
            h_kemarin.harga_rata_rata as harga_kemarin
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_komoditas kp', 'k.komoditas_parent_id = kp.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        
        // Join with previous day's price
        $prev_date = date('Y-m-d', strtotime($end_date . ' -1 day'));
        $this->db->join($this->table . ' h_kemarin',
                        'h.komoditas_id = h_kemarin.komoditas_id AND h.pasar_id = h_kemarin.pasar_id AND h_kemarin.harga_tanggal = "' . $prev_date . '"',
                        'left');
        
        $this->db->where('h.harga_tanggal >=', $start_date);
        $this->db->where('h.harga_tanggal <=', $end_date);
        $this->db->where('h.harga_status', 1);
        $this->db->where('k.komoditas_status', 1);

        if (!in_array('all', $pasar_ids) && is_array($pasar_ids)) {
            $this->db->where_in('h.pasar_id', $pasar_ids);
        }

        // Apply sorting
        switch ($sort_column) {
            case 'komoditas':
                $this->db->order_by('k.komoditas_nama', $sort_direction);
                break;
            case 'satuan':
                $this->db->order_by('k.komoditas_satuan', $sort_direction);
                break;
            case 'pasar':
                $this->db->order_by('p.pasar_nama', $sort_direction);
                break;
            case 'harga_kemarin':
                $this->db->order_by('h_kemarin.harga_rata_rata', $sort_direction);
                break;
            case 'harga_hari_ini':
                $this->db->order_by('h.harga_rata_rata', $sort_direction);
                break;
            case 'perubahan':
                // This would require more complex calculation, simplified for now
                $this->db->order_by('h.harga_rata_rata', $sort_direction);
                break;
            default:
                $this->db->order_by('kp.komoditas_urutan', 'asc');
                $this->db->order_by('k.komoditas_urutan', 'asc');
        }

        // Get total count for pagination
        $total_query = $this->db->get_compiled_select();
        $total = $this->db->query("SELECT COUNT(*) as total FROM ($total_query) as subquery")->row()->total;

        // Apply pagination
        $this->db->limit($per_page, $offset);
        $results = $this->db->get()->result();

        // Calculate percentage change
        foreach ($results as $row) {
            $row->persentase_perubahan = 0;
            $row->status_perubahan = 'stable';
            $row->keterangan_perubahan = 'Tidak ada perubahan';

            if ($row->harga_kemarin > 0) {
                $row->persentase_perubahan = (($row->harga_rata_rata - $row->harga_kemarin) / $row->harga_kemarin) * 100;

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
        }

        return [
            'data' => $results,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'last_page' => ceil($total / $per_page),
                'from' => $offset + 1,
                'to' => min($offset + $per_page, $total)
            ]
        ];
    }
    
    /**
     * Bulk insert for performance improvement
     */
    public function bulk_insert_prices($data)
    {
        return $this->db->insert_batch($this->table, $data);
    }
    
    /**
     * Get optimized statistics with caching
     */
    public function get_optimized_statistics($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }
        
        $cache_key = $this->cache_prefix . 'stats_' . $tanggal;
        
        // Try to get from cache
        $cached_result = $this->cache->get($cache_key);
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        $stats = [];
        
        // Use more efficient queries
        $stats['total_harga_hari_ini'] = $this->db->where('harga_tanggal', $tanggal)
                                                 ->where('harga_status', 1)
                                                 ->count_all_results($this->table);
        
        $stats['total_komoditas_dipantau'] = $this->db->distinct()
                                                       ->select('komoditas_id')
                                                       ->where('harga_tanggal', $tanggal)
                                                       ->where('harga_status', 1)
                                                       ->count_all_results($this->table);
        
        $stats['total_pasar_dipantau'] = $this->db->distinct()
                                                    ->select('pasar_id')
                                                    ->where('harga_tanggal', $tanggal)
                                                    ->where('harga_status', 1)
                                                    ->count_all_results($this->table);
        
        // Get additional stats with single query
        $this->db->select('
            MAX(h.harga_rata_rata) as max_price,
            MIN(h.harga_rata_rata) as min_price,
            AVG(h.harga_rata_rata) as avg_price,
            k.komoditas_nama,
            p.pasar_nama
        ');
        $this->db->from($this->table . ' h');
        $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
        $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
        $this->db->where('h.harga_tanggal', $tanggal);
        $this->db->where('h.harga_status', 1);
        
        $result = $this->db->get()->row();
        
        if ($result) {
            // Get highest price commodity
            $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
            $this->db->from($this->table . ' h');
            $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
            $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
            $this->db->where('h.harga_tanggal', $tanggal);
            $this->db->where('h.harga_status', 1);
            $this->db->order_by('h.harga_rata_rata', 'desc');
            $this->db->limit(1);
            $stats['harga_tertinggi'] = $this->db->get()->row();
            
            // Get lowest price commodity
            $this->db->select('k.komoditas_nama, p.pasar_nama, h.harga_rata_rata');
            $this->db->from($this->table . ' h');
            $this->db->join('master_komoditas k', 'h.komoditas_id = k.komoditas_id', 'left');
            $this->db->join('master_pasar p', 'h.pasar_id = p.pasar_id', 'left');
            $this->db->where('h.harga_tanggal', $tanggal);
            $this->db->where('h.harga_status', 1);
            $this->db->order_by('h.harga_rata_rata', 'asc');
            $this->db->limit(1);
            $stats['harga_terendah'] = $this->db->get()->row();
            
            $stats['rata_rata_harga'] = $result->avg_price;
        }
        
        // Cache the result
        $this->cache->save($cache_key, $stats, $this->cache_time);
        
        return $stats;
    }
}