<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pelaporan_m extends CI_Model
{
    private $table = 'pelaporan';
    private $history_table = 'pelaporan_history';
    private $kategori_table = 'kategori_pelaporan';
    private $files_table = 'pelaporan_files';
    private $comments_table = 'pelaporan_comments';

    // ========== MAIN PELAPORAN METHODS ==========

    public function get_all()
    {
        return $this->db->order_by('created_at', 'desc')->get($this->table)->result();
    }
    
    public function get_active()
    {
        return $this->db->where('status !=', 'SELESAI')->order_by('created_at', 'desc')->get($this->table)->result();
    }
    
    public function get_by_id($id)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori, u.nama as operator_nama, m.nama_lengkap as masyarakat_nama, m.nik as masyarakat_nik, m.no_telpon as masyarakat_telpon, k.nama_kecamatan, kel.nama_kelurahan, uk.unitkerja as nama_unitkerja');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->join('user u', 'p.operator_id = u.id_user', 'left');
        $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->join('tbUnitKerja uk', 'p.unitkerja_id = uk.id_unitkerja', 'left');
        $this->db->where('p.id', $id);
        return $this->db->get()->row();
    }
    
    public function get_by_kode($kode_laporan)
    {
        return $this->db->where('kode_laporan', $kode_laporan)->get($this->table)->row();
    }
    
    public function insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    public function delete_data($id)
    {
        // Delete related data first
        $this->db->where('pelaporan_id', $id)->delete($this->history_table);
        $this->db->where('pelaporan_id', $id)->delete($this->files_table);
        $this->db->where('pelaporan_id', $id)->delete($this->comments_table);
        
        // Delete main record
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function generate_kode_laporan()
    {
        $prefix = 'LP';
        $date = date('Ymd');
        
        // Get last number for today
        $this->db->select('kode_laporan');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $this->db->like('kode_laporan', $prefix . $date, 'after');
        $this->db->order_by('kode_laporan', 'desc');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row();
        
        if ($last) {
            $last_number = (int) substr($last->kode_laporan, -4);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }
        
        return $prefix . $date . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    public function validate_data($data, $exclude_id = null)
    {
        $errors = [];

        // Required fields validation
        $required_fields = [
            'judul' => 'Judul laporan harus diisi',
            'deskripsi' => 'Deskripsi laporan harus diisi',
            'kategori' => 'Kategori laporan harus dipilih',
            'alamat' => 'Alamat laporan harus diisi',
            'pelapor_nama' => 'Nama pelapor harus diisi'
        ];

        foreach ($required_fields as $field => $message) {
            if (empty($data[$field])) {
                $errors[] = $message;
            }
        }

        // Email validation
        if (!empty($data['pelapor_email']) && !filter_var($data['pelapor_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        // Phone validation
        if (!empty($data['pelapor_telepon']) && !preg_match('/^[0-9+\-\s()]+$/', $data['pelapor_telepon'])) {
            $errors[] = 'Format nomor telepon tidak valid';
        }

        // NIK validation
        if (!empty($data['pelapor_nik']) && !preg_match('/^[0-9]{16}$/', $data['pelapor_nik'])) {
            $errors[] = 'NIK harus 16 digit angka';
        }

        return $errors;
    }

    // ========== STATUS UPDATE METHODS ==========

    public function update_status($id, $status_baru, $keterangan = null, $tanggal_selesai = null, $user_id = null)
    {
        // Get current status
        $current = $this->get_by_id($id);
        if (!$current) {
            return false;
        }

        $update_data = [
            'status' => $status_baru,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $user_id
        ];

        if ($status_baru == 'SELESAI') {
            $update_data['tanggal_selesai'] = $tanggal_selesai ?: date('Y-m-d H:i:s');
        }

        if ($status_baru == 'DITERIMA' && $user_id) {
            $update_data['operator_id'] = $user_id;
        }

        // Update main record
        $result = $this->db->where('id', $id)->update($this->table, $update_data);

        if ($result) {
            // Insert history
            $this->insert_history($id, $current->status, $status_baru, $keterangan, $user_id);
        }

        return $result;
    }

    // ========== HISTORY METHODS ==========

    public function get_history($pelaporan_id)
    {
        $this->db->select('ph.*, u.nama as updated_by_name');
        $this->db->from($this->history_table . ' ph');
        $this->db->join('user u', 'ph.created_by = u.id_user', 'left');
        $this->db->where('ph.pelaporan_id', $pelaporan_id);
        $this->db->order_by('ph.created_at', 'asc');
        return $this->db->get()->result();
    }

    public function insert_history($pelaporan_id, $status_dari, $status_ke, $keterangan = null, $user_id = null)
    {
        $data = [
            'pelaporan_id' => $pelaporan_id,
            'status_dari' => $status_dari,
            'status_ke' => $status_ke,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user_id
        ];

        return $this->db->insert($this->history_table, $data);
    }

    // ========== FILES METHODS ==========

    public function get_files($pelaporan_id)
    {
        $this->db->select('pf.*, u.nama as uploaded_by_name');
        $this->db->from($this->files_table . ' pf');
        $this->db->join('user u', 'pf.uploaded_by = u.id_user', 'left');
        $this->db->where('pf.pelaporan_id', $pelaporan_id);
        $this->db->order_by('pf.uploaded_at', 'desc');
        return $this->db->get()->result();
    }

    public function insert_file($data)
    {
        return $this->db->insert($this->files_table, $data);
    }

    public function delete_file($id)
    {
        return $this->db->where('id', $id)->delete('pelaporan_files');
    }

    // ========== COMMENTS METHODS ==========

    public function get_comments($pelaporan_id)
    {
        $this->db->select('pc.*, u.nama as created_by_name');
        $this->db->from($this->comments_table . ' pc');
        $this->db->join('user u', 'pc.created_by = u.id_user', 'left');
        $this->db->where('pc.pelaporan_id', $pelaporan_id);
        $this->db->order_by('pc.created_at', 'desc');
        return $this->db->get()->result();
    }

    public function insert_comment($data)
    {
        return $this->db->insert($this->comments_table, $data);
    }

    // ========== KATEGORI METHODS ==========

    public function get_all_kategori()
    {
        return $this->db->order_by('pelaporan_nama', 'asc')->get($this->kategori_table)->result();
    }

    public function get_active_kategori()
    {
        return $this->db->where('status', 1)->order_by('pelaporan_nama', 'asc')->get($this->kategori_table)->result();
    }

    public function get_kategori_for_select()
    {
        $result = $this->get_active_kategori();
        $options = [];
        foreach ($result as $row) {
            $options[$row->pelaporan_nama] = $row->pelaporan_nama;
        }
        return $options;
    }

    // ========== MASYARAKAT METHODS ==========

    public function get_active_masyarakat()
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from('masyarakat m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.status_aktif', 1);
        $this->db->order_by('m.nama_lengkap', 'asc');
        return $this->db->get()->result();
    }

    public function get_masyarakat_by_id($id)
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from('masyarakat m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.id', $id);
        return $this->db->get()->row();
    }

    public function search_masyarakat($keyword)
    {
        $this->db->select('m.*, k.nama_kecamatan, kel.nama_kelurahan');
        $this->db->from('masyarakat m');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->join('kelurahan kel', 'm.id_kelurahan = kel.id_kelurahan', 'left');
        $this->db->where('m.status_aktif', 1);
        
        if ($keyword) {
            $this->db->group_start();
            $this->db->like('m.nama_lengkap', $keyword);
            $this->db->or_like('m.nik', $keyword);
            $this->db->or_like('m.no_telpon', $keyword);
            $this->db->group_end();
        }
        
        $this->db->order_by('m.nama_lengkap', 'asc');
        return $this->db->get()->result();
    }

    // ========== DASHBOARD & STATISTICS METHODS ==========

    public function get_dashboard_stats($unitkerja_id = null)
    {
        $stats = [];

        // Base query with unit kerja filter
        $base_query = $this->db;
        if ($unitkerja_id) {
            $base_query = $base_query->where('unitkerja_id', $unitkerja_id);
        }

        // Total laporan
        $stats['total'] = $base_query->count_all_results($this->table);

        // By status
        $stats['lapor'] = $base_query->where('status', 'LAPOR')->count_all_results($this->table);
        $stats['diterima'] = $base_query->where('status', 'DITERIMA')->count_all_results($this->table);
        $stats['dikerjakan'] = $base_query->where('status', 'DIKERJAKAN')->count_all_results($this->table);
        $stats['selesai'] = $base_query->where('status', 'SELESAI')->count_all_results($this->table);

        // By priority
        $stats['urgent'] = $base_query->where('prioritas', 'URGENT')->count_all_results($this->table);
        $stats['tinggi'] = $base_query->where('prioritas', 'TINGGI')->count_all_results($this->table);
        $stats['sedang'] = $base_query->where('prioritas', 'SEDANG')->count_all_results($this->table);
        $stats['rendah'] = $base_query->where('prioritas', 'RENDAH')->count_all_results($this->table);

        // This month
        $stats['bulan_ini'] = $base_query->where('MONTH(created_at)', date('m'))
                                      ->where('YEAR(created_at)', date('Y'))
                                      ->count_all_results($this->table);

        // Today
        $stats['hari_ini'] = $base_query->where('DATE(created_at)', date('Y-m-d'))
                                     ->count_all_results($this->table);

        return $stats;
    }

    public function get_recent_reports($limit = 10, $unitkerja_id = null)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        if ($unitkerja_id) {
            $this->db->where('p.unitkerja_id', $unitkerja_id);
        }
        $this->db->order_by('p.created_at', 'desc');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_reports_by_status($unitkerja_id = null)
    {
        // Enum values untuk kolom status
        $all_statuses = ['LAPOR', 'DITERIMA', 'DIKERJAKAN', 'DIBATALKAN', 'SELESAI'];

        $this->db->select('status, COUNT(*) as count');
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        $this->db->group_by('status');
        $result = $this->db->get($this->table)->result();

        // Convert to associative array
        $counts = [];
        foreach ($result as $row) {
            $counts[$row->status] = $row->count;
        }

        // Build data with all enum values, default count 0
        $data = [];
        foreach ($all_statuses as $status) {
            $data[] = (object) [
                'status' => $status,
                'count' => isset($counts[$status]) ? (int)$counts[$status] : 0
            ];
        }

        return $data;
    }

    public function get_reports_by_prioritas($unitkerja_id = null)
    {
        // Enum values untuk kolom prioritas
        $all_prioritas = ['URGENT', 'TINGGI', 'SEDANG', 'RENDAH'];

        $this->db->select('prioritas, COUNT(*) as count');
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        $this->db->group_by('prioritas');
        $result = $this->db->get($this->table)->result();

        // Convert to associative array
        $counts = [];
        foreach ($result as $row) {
            $counts[$row->prioritas] = $row->count;
        }

        // Build data with all enum values, default count 0
        $data = [];
        foreach ($all_prioritas as $prioritas) {
            $data[] = (object) [
                'prioritas' => $prioritas,
                'count' => isset($counts[$prioritas]) ? (int)$counts[$prioritas] : 0
            ];
        }

        return $data;
    }

    public function get_reports_by_kategori($unitkerja_id = null)
    {
        $this->db->select('p.kategori, kp.pelaporan_nama as nama_kategori, COUNT(*) as count');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        if ($unitkerja_id) {
            $this->db->where('p.unitkerja_id', $unitkerja_id);
        }
        $this->db->group_by('p.kategori');
        return $this->db->get()->result();
    }

    public function get_reports_map_data($unitkerja_id = null)
    {
        $this->db->select('id, kode_laporan, judul, alamat, lokasi_lat, lokasi_lng, status, prioritas, kategori');
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        $this->db->where('lokasi_lat IS NOT NULL');
        $this->db->where('lokasi_lng IS NOT NULL');
        $this->db->where('lokasi_lat !=', '');
        $this->db->where('lokasi_lng !=', '');
        return $this->db->get($this->table)->result();
    }

    // Map data with optional lokasi filters via masyarakat linkage
     public function get_reports_map_data_filtered($id_kota = '16.73', $id_kecamatan = null, $id_kelurahan = null, $unitkerja_id = null)
     {
         $this->db->select('p.id, p.kode_laporan, p.judul, p.alamat, p.lokasi_lat, p.lokasi_lng, p.status, p.prioritas, p.kategori');
         $this->db->from($this->table . ' p');
         $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
         // Join kecamatan agar bisa filter berdasarkan kota (m tidak punya kolom id_kota)
         $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
         if ($unitkerja_id) {
             $this->db->where('p.unitkerja_id', $unitkerja_id);
         }
         $this->db->where('p.lokasi_lat IS NOT NULL');
         $this->db->where('p.lokasi_lng IS NOT NULL');
         $this->db->where('p.lokasi_lat !=', '');
         $this->db->where('p.lokasi_lng !=', '');
         if (!empty($id_kota)) {
             $this->db->group_start();
             $this->db->where('k.id_kota', $id_kota);
             // Tetap tampilkan jika tidak ada relasi masyarakat/kecamatan
             $this->db->or_where('m.id_kecamatan IS NULL', null, false);
             $this->db->group_end();
         }
         if (!empty($id_kecamatan)) {
             $this->db->where('m.id_kecamatan', $id_kecamatan);
         }
         if (!empty($id_kelurahan)) {
             $this->db->where('m.id_kelurahan', $id_kelurahan);
         }
         $this->db->order_by('p.created_at', 'desc');
         return $this->db->get()->result();
     }

    public function get_monthly_reports($year = null, $unitkerja_id = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $this->db->select('MONTH(created_at) as bulan, COUNT(*) as total');
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        $this->db->where('YEAR(created_at)', $year);
        $this->db->group_by('MONTH(created_at)');
        $this->db->order_by('MONTH(created_at)', 'asc');
        return $this->db->get($this->table)->result();
    }

    // ========== SEARCH & FILTER METHODS ==========

    public function search_reports($keyword, $status = null, $kategori = null, $unitkerja_id = null)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        if ($unitkerja_id) {
            $this->db->where('p.unitkerja_id', $unitkerja_id);
        }
        
        if ($keyword) {
            $this->db->group_start();
            $this->db->like('p.kode_laporan', $keyword);
            $this->db->or_like('p.judul', $keyword);
            $this->db->or_like('p.deskripsi', $keyword);
            $this->db->or_like('p.alamat', $keyword);
            $this->db->or_like('p.pelapor_nama', $keyword);
            $this->db->group_end();
        }
        
        if ($status) {
            $this->db->where('p.status', $status);
        }
        
        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }
        
        $this->db->order_by('p.created_at', 'desc');
        return $this->db->get()->result();
    }

    public function get_export_data($status = null, $kategori = null, $start_date = null, $end_date = null, $unitkerja_id = null)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori, u.nama as operator_nama, uk.unitkerja as nama_unitkerja');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->join('user u', 'p.operator_id = u.id_user', 'left');
        $this->db->join('tbUnitKerja uk', 'p.unitkerja_id = uk.id_unitkerja', 'left');
        if ($unitkerja_id) {
            $this->db->where('p.unitkerja_id', $unitkerja_id);
        }
        
        if ($status) {
            $this->db->where('p.status', $status);
        }
        
        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }
        
        if ($start_date) {
            $this->db->where('DATE(p.created_at) >=', $start_date);
        }
        
        if ($end_date) {
            $this->db->where('DATE(p.created_at) <=', $end_date);
        }
        
        $this->db->order_by('p.created_at', 'desc');
        return $this->db->get()->result();
    }

    // ========== LEGACY METHODS (for backward compatibility) ==========

    public function get_all_reports()
    {
        return $this->get_all();
    }

    public function get_report_by_id($id)
    {
        return $this->get_by_id($id);
    }

    public function get_report_history($report_id)
    {
        return $this->get_history($report_id);
    }

    public function update_report_status($report_id, $status, $keterangan, $user_id)
    {
        return $this->update_status($report_id, $status, $keterangan, null, $user_id);
    }

    public function count_total_reports($unitkerja_id = null)
    {
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->count_all_results($this->table);
    }

    public function count_verified_reports($unitkerja_id = null)
    {
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->where('status', 'SELESAI')->count_all_results($this->table);
    }

    public function count_pending_reports($unitkerja_id = null)
    {
        if ($unitkerja_id) {
            $this->db->where('unitkerja_id', $unitkerja_id);
        }
        return $this->db->where('status', 'LAPOR')->count_all_results($this->table);
    }

    public function insert_report($data)
    {
        return $this->insert_data($data);
    }

    // ========== DETAIL PAGE METHODS ==========

    public function get_laporan_timeline($id)
    {
        $this->db->select('ph.*, u.nama as operator_nama');
        $this->db->from('pelaporan_history ph');
        $this->db->join('user u', 'ph.created_by = u.id_user', 'left');
        $this->db->where('ph.pelaporan_id', $id);
        $this->db->order_by('ph.created_at', 'asc');
        return $this->db->get()->result();
    }

    public function get_laporan_files($id)
    {
        $this->db->select('pf.*, u.nama as uploaded_by_name');
        $this->db->from('pelaporan_files pf');
        $this->db->join('user u', 'pf.uploaded_by = u.id_user', 'left');
        $this->db->where('pf.pelaporan_id', $id);
        $this->db->order_by('pf.uploaded_at', 'desc');
        return $this->db->get()->result();
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Count all reports for frontend
     */
    public function count_all_reports()
    {
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get latest reports for frontend
     */
    public function get_latest_reports($limit = 10)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->where('p.status !=', 'DRAFT');
        $this->db->order_by('p.created_at', 'desc');
        $this->db->limit($limit);

        return $this->db->get()->result();
    }

    /**
     * Count public reports with filters
     */
    public function count_public_reports($status = '', $kategori = '', $search = '')
    {
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->where('p.status !=', 'DRAFT');

        if ($status) {
            $this->db->where('p.status', $status);
        }

        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.kode_laporan', $search);
            $this->db->or_like('p.judul', $search);
            $this->db->or_like('p.deskripsi', $search);
            $this->db->or_like('p.alamat', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Get public reports with pagination and filters
     */
    public function get_public_reports($limit, $offset, $status = '', $kategori = '', $search = '')
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->where('p.status !=', 'DRAFT');

        if ($status) {
            $this->db->where('p.status', $status);
        }

        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.kode_laporan', $search);
            $this->db->or_like('p.judul', $search);
            $this->db->or_like('p.deskripsi', $search);
            $this->db->or_like('p.alamat', $search);
            $this->db->group_end();
        }

        $this->db->order_by('p.created_at', 'desc');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get public report detail
     */
    public function get_public_report_detail($id)
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->where('p.id', $id);
        $this->db->where('p.status !=', 'DRAFT');

        return $this->db->get()->row();
    }

    /**
     * Get report timeline for public
     */
    public function get_report_timeline($id)
    {
        $this->db->select('ph.status_ke as status, ph.keterangan, ph.created_at');
        $this->db->from('pelaporan_history ph');
        $this->db->where('ph.pelaporan_id', $id);
        $this->db->order_by('ph.created_at', 'asc');
        return $this->db->get()->result();
    }

    /**
     * Get report files for public
     */
    public function get_report_files($id)
    {
        $this->db->select('pf.file_name, pf.file_path, pf.file_type, pf.uploaded_at');
        $this->db->from('pelaporan_files pf');
        $this->db->where('pf.pelaporan_id', $id);
        $this->db->order_by('pf.uploaded_at', 'desc');
        return $this->db->get()->result();
    }

    /**
     * Search public reports
     */
    public function search_public_reports($search, $status = '', $kategori = '')
    {
        $this->db->select('p.*, kp.pelaporan_nama as nama_kategori');
        $this->db->from($this->table . ' p');
        $this->db->join($this->kategori_table . ' kp', 'p.kategori = kp.pelaporan_nama', 'left');
        $this->db->where('p.status !=', 'DRAFT');

        if ($status) {
            $this->db->where('p.status', $status);
        }

        if ($kategori) {
            $this->db->where('p.kategori', $kategori);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('p.kode_laporan', $search);
            $this->db->or_like('p.judul', $search);
            $this->db->or_like('p.deskripsi', $search);
            $this->db->or_like('p.alamat', $search);
            $this->db->group_end();
        }

        $this->db->order_by('p.created_at', 'desc');
        $this->db->limit(20);

        return $this->db->get()->result();
    }

    public function get_laporan_comments($id)
    {
        $this->db->select('pc.*, u.nama as created_by_name');
        $this->db->from('pelaporan_comments pc');
        $this->db->join('user u', 'pc.created_by = u.id_user', 'left');
        $this->db->where('pc.pelaporan_id', $id);
        $this->db->order_by('pc.created_at', 'desc');
        return $this->db->get()->result();
    }

    public function save_comment($data)
    {
        return $this->db->insert('pelaporan_comments', $data);
    }

    public function save_file($data)
    {
        $this->db->insert('pelaporan_files', $data);
        return $this->db->insert_id();
    }

    public function get_progress_photos($pelaporan_id)
    {
        $this->db->select('pf.*, u.nama as uploaded_by_name');
        $this->db->from('pelaporan_files pf');
        $this->db->join('user u', 'pf.uploaded_by = u.id_user', 'left');
        $this->db->where('pf.pelaporan_id', $pelaporan_id);
        $this->db->where('pf.file_type', 'progress_photo');
        $this->db->order_by('pf.uploaded_at', 'desc');
        return $this->db->get()->result();
    }

    public function get_file_by_id($id)
    {
        return $this->db->where('id', $id)->get('pelaporan_files')->row();
    }

    // ========== PERFORMANCE ANALYSIS METHODS ==========

    /**
     * Get average processing time by status transition
     */
    public function get_average_processing_times()
    {
        $this->db->select('
            ph.status_dari,
            ph.status_ke,
            AVG(TIMESTAMPDIFF(SECOND,
                LAG(ph.created_at) OVER (PARTITION BY ph.pelaporan_id ORDER BY ph.created_at),
                ph.created_at
            )) as avg_duration_seconds,
            COUNT(*) as transition_count
        ');
        $this->db->from('pelaporan_history ph');
        $this->db->where('ph.status_dari IS NOT NULL');
        $this->db->group_by(['ph.status_dari', 'ph.status_ke']);
        $this->db->order_by('ph.status_dari, ph.status_ke');

        return $this->db->get()->result();
    }

    /**
     * Get processing time statistics for a specific report
     */
    public function get_report_processing_stats($pelaporan_id)
    {
        // Get timeline for the specific report
        $timeline = $this->get_laporan_timeline($pelaporan_id);

        if (empty($timeline)) {
            return null;
        }

        $stats = [
            'total_stages' => count($timeline),
            'completed_stages' => 0,
            'total_duration' => 0,
            'stage_durations' => [],
            'efficiency_score' => 0,
            'bottleneck_stage' => null
        ];

        // Calculate stage durations
        $reversed_timeline = array_reverse($timeline);
        $previous_time = strtotime($reversed_timeline[0]->created_at);
        $max_duration = 0;
        $bottleneck_stage = null;

        foreach ($reversed_timeline as $index => $item) {
            if ($index === 0) continue;

            $current_time = strtotime($item->created_at);
            $duration = max(0, $current_time - $previous_time);

            $stage_key = $item->status_dari . '_to_' . $item->status_ke;
            $stats['stage_durations'][$stage_key] = [
                'duration' => $duration,
                'from_status' => $item->status_dari,
                'to_status' => $item->status_ke,
                'operator' => $item->operator_nama
            ];

            if ($duration > $max_duration) {
                $max_duration = $duration;
                $bottleneck_stage = $stage_key;
            }

            $stats['completed_stages']++;
            $previous_time = $current_time;
        }

        $stats['total_duration'] = strtotime(end($timeline)->created_at) - strtotime($reversed_timeline[0]->created_at);
        $stats['bottleneck_stage'] = $bottleneck_stage;

        // Calculate efficiency score (0-100)
        $expected_duration = 3 * 24 * 3600; // 3 days in seconds
        $actual_duration = $stats['total_duration'];
        $stats['efficiency_score'] = max(0, min(100, (($expected_duration - $actual_duration) / $expected_duration) * 100));

        return $stats;
    }

    /**
     * Get performance comparison with similar reports
     */
    public function get_performance_comparison($pelaporan_id)
    {
        $report = $this->get_by_id($pelaporan_id);
        if (!$report) return null;

        // Get similar reports (same category and priority) with completion time from history
        $this->db->select('
            p.id,
            p.kode_laporan,
            p.created_at,
            ph.created_at as completion_time,
            TIMESTAMPDIFF(SECOND, p.created_at, ph.created_at) as total_duration
        ');
        $this->db->from($this->table . ' p');
        $this->db->join('pelaporan_history ph', 'p.id = ph.pelaporan_id AND ph.status_ke = "SELESAI"', 'inner');
        $this->db->where('p.kategori', $report->kategori);
        $this->db->where('p.prioritas', $report->prioritas);
        $this->db->where('p.unitkerja_id', $report->unitkerja_id); // Same unit kerja
        $this->db->where('p.id !=', $pelaporan_id);
        $this->db->where('p.status', 'SELESAI');
        $this->db->limit(10);
        $this->db->order_by('p.created_at', 'desc');

        $similar_reports = $this->db->get()->result();

        if (empty($similar_reports)) return null;

        $durations = array_column($similar_reports, 'total_duration');
        $avg_duration = array_sum($durations) / count($durations);
        $min_duration = min($durations);
        $max_duration = max($durations);

        // Get actual completion time from history
        $completion_time = null;
        if ($report->status == 'SELESAI') {
            $this->db->select('created_at');
            $this->db->from('pelaporan_history');
            $this->db->where('pelaporan_id', $pelaporan_id);
            $this->db->where('status_ke', 'SELESAI');
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(1);
            $completion_record = $this->db->get()->row();

            if ($completion_record) {
                $completion_time = strtotime($completion_record->created_at);
            }
        }

        $current_duration = $completion_time ?
            $completion_time - strtotime($report->created_at) :
            time() - strtotime($report->created_at);

        return [
            'current_duration' => $current_duration,
            'average_duration' => $avg_duration,
            'min_duration' => $min_duration,
            'max_duration' => $max_duration,
            'performance_percentile' => $this->calculate_percentile($current_duration, $durations),
            'similar_reports_count' => count($similar_reports)
        ];
    }

    /**
     * Calculate percentile ranking
     */
    private function calculate_percentile($value, $array)
    {
        sort($array);
        $count = count($array);
        $rank = 0;

        foreach ($array as $item) {
            if ($item <= $value) {
                $rank++;
            }
        }

        return round(($rank / $count) * 100, 1);
    }

    /**
     * Get SLA compliance status
     */
    public function get_sla_compliance($pelaporan_id)
    {
        $report = $this->get_by_id($pelaporan_id);
        if (!$report) return null;

        // Define SLA targets based on priority (in hours)
        $sla_targets = [
            'URGENT' => 4,   // 4 hours
            'TINGGI' => 24,  // 1 day
            'SEDANG' => 72,  // 3 days
            'RENDAH' => 168  // 7 days
        ];

        $target_hours = $sla_targets[$report->prioritas] ?? 72;
        $target_seconds = $target_hours * 3600;

        // Get actual completion time from history for SLA calculation
        $completion_time = null;
        if ($report->status == 'SELESAI') {
            $this->db->select('created_at');
            $this->db->from('pelaporan_history');
            $this->db->where('pelaporan_id', $pelaporan_id);
            $this->db->where('status_ke', 'SELESAI');
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(1);
            $completion_record = $this->db->get()->row();

            if ($completion_record) {
                $completion_time = strtotime($completion_record->created_at);
            }
        }

        $actual_duration = $completion_time ?
            $completion_time - strtotime($report->created_at) :
            time() - strtotime($report->created_at);

        $compliance_percentage = min(100, ($target_seconds / $actual_duration) * 100);
        $is_compliant = $actual_duration <= $target_seconds;
        $remaining_time = $target_seconds - $actual_duration;

        return [
            'target_hours' => $target_hours,
            'actual_duration' => $actual_duration,
            'compliance_percentage' => round($compliance_percentage, 1),
            'is_compliant' => $is_compliant,
            'remaining_time' => $remaining_time,
            'status' => $is_compliant ? 'COMPLIANT' : ($remaining_time > 0 ? 'AT_RISK' : 'OVERDUE')
        ];
    }
}
