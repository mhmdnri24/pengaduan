<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retribusi_pasar_m extends CI_Model
{
    private $table = 'retribusi_pasar_transaksi';
    private $view_lengkap = 'view_retribusi_lengkap';
    private $view_statistik = 'view_retribusi_statistik_pedagang';

    public function retribusi_get_all()
    {
        return $this->db->order_by('retribusi_tanggal', 'desc')
                       ->order_by('retribusi_id', 'desc')
                       ->get($this->table)->result();
    }
    
    public function retribusi_get_active()
    {
        return $this->db->where('retribusi_status !=', 'BATAL')
                       ->order_by('retribusi_tanggal', 'desc')
                       ->order_by('retribusi_id', 'desc')
                       ->get($this->table)->result();
    }
    
    public function retribusi_by_id($id)
    {
        return $this->db->where('retribusi_id', $id)->get($this->table)->row();
    }
    
    public function retribusi_by_id_array($id)
    {
        return $this->db->where('retribusi_id', $id)->get($this->table)->result();
    }
    
    public function retribusi_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function retribusi_update_data($post_data, $id)
    {
        return $this->db->where('retribusi_id', $id)->update($this->table, $post_data);
    }
    
    public function retribusi_delete_data($id)
    {
        return $this->db->where('retribusi_id', $id)->delete($this->table);
    }

    // Method untuk mendapatkan data lengkap dengan join
    public function retribusi_lengkap_get_all()
    {
        return $this->db->order_by('retribusi_tanggal', 'desc')
                       ->order_by('retribusi_id', 'desc')
                       ->get($this->view_lengkap)->result();
    }

    public function retribusi_lengkap_by_id($id)
    {
        return $this->db->where('retribusi_id', $id)->get($this->view_lengkap)->row();
    }

    // Method untuk mendapatkan retribusi berdasarkan pedagang
    public function get_by_pedagang_id($pedagang_id, $limit = null, $offset = null)
    {
        $this->db->where('pedagang_id', $pedagang_id);
        $this->db->order_by('retribusi_tanggal', 'desc');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->view_lengkap)->result();
    }

    // Method untuk mendapatkan retribusi berdasarkan blok
    public function get_by_blok_id($blok_id, $limit = null, $offset = null)
    {
        $this->db->where('pasar_blok_id', $blok_id);
        $this->db->order_by('retribusi_tanggal', 'desc');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get($this->view_lengkap)->result();
    }

    // Method untuk mendapatkan retribusi berdasarkan periode
    public function get_by_periode($bulan, $tahun, $status = null)
    {
        $this->db->where('retribusi_periode_bulan', $bulan);
        $this->db->where('retribusi_periode_tahun', $tahun);
        
        if ($status) {
            $this->db->where('retribusi_status', $status);
        }
        
        $this->db->order_by('retribusi_tanggal', 'desc');
        return $this->db->get($this->view_lengkap)->result();
    }

    // Method untuk mendapatkan pedagang yang belum bayar
    public function get_pedagang_belum_bayar($bulan = null, $tahun = null)
    {
        if (!$bulan) $bulan = date('n');
        if (!$tahun) $tahun = date('Y');

        $this->db->select('mp.*, mpb.pasar_blok_nama, mpj.pasar_jenis_nama, mps.pasar_nama');
        $this->db->from('masyarakat_pedagang mp');
        $this->db->join('penyewaan_blok pb', 'mp.id = pb.pedagang_id AND pb.status = "AKTIF"', 'inner');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');
        
        // Left join untuk cek apakah sudah ada pembayaran
        $this->db->join($this->table . ' rpt', 
            'mp.id = rpt.pedagang_id AND rpt.retribusi_periode_bulan = ' . $bulan . ' AND rpt.retribusi_periode_tahun = ' . $tahun, 
            'left');
        
        $this->db->where('mp.status_aktif', 1);
        $this->db->where('rpt.retribusi_id IS NULL'); // Belum ada pembayaran
        $this->db->order_by('mp.nama_lengkap', 'asc');
        
        return $this->db->get()->result();
    }

    // Method untuk statistik
    public function get_statistik_pembayaran($bulan = null, $tahun = null)
    {
        if (!$bulan) $bulan = date('n');
        if (!$tahun) $tahun = date('Y');

        $this->db->select('
            COUNT(*) as total_transaksi,
            SUM(CASE WHEN retribusi_status = "LUNAS" THEN 1 ELSE 0 END) as lunas,
            SUM(CASE WHEN retribusi_status = "BELUM_BAYAR" THEN 1 ELSE 0 END) as belum_bayar,
            SUM(CASE WHEN retribusi_status = "TERLAMBAT" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN retribusi_status = "SEBAGIAN" THEN 1 ELSE 0 END) as sebagian,
            SUM(retribusi_total) as total_pendapatan,
            SUM(retribusi_denda) as total_denda,
            AVG(retribusi_total) as rata_rata_pembayaran
        ');
        $this->db->where('retribusi_periode_bulan', $bulan);
        $this->db->where('retribusi_periode_tahun', $tahun);
        
        return $this->db->get($this->table)->row();
    }

    // Method untuk mendapatkan statistik per pedagang
    public function get_statistik_pedagang()
    {
        return $this->db->order_by('total_pembayaran', 'desc')
                       ->get($this->view_statistik)->result();
    }

    // Method untuk validasi data
    public function validate_retribusi_data($data, $id = null)
    {
        $errors = [];

        // Validasi pedagang_id
        if (empty($data['pedagang_id'])) {
            $errors[] = 'Pedagang harus dipilih';
        }

        // Validasi pasar_blok_id
        if (empty($data['pasar_blok_id'])) {
            $errors[] = 'Blok pasar harus dipilih';
        }

        // Validasi periode
        if (empty($data['retribusi_periode_bulan']) || empty($data['retribusi_periode_tahun'])) {
            $errors[] = 'Periode pembayaran harus diisi';
        }

        // Validasi nominal
        if (empty($data['retribusi_nominal']) || $data['retribusi_nominal'] <= 0) {
            $errors[] = 'Nominal retribusi harus lebih dari 0';
        }

        // Cek duplikasi pembayaran untuk periode yang sama
        if (!empty($data['pedagang_id']) && !empty($data['retribusi_periode_bulan']) && !empty($data['retribusi_periode_tahun'])) {
            $this->db->where('pedagang_id', $data['pedagang_id']);
            $this->db->where('retribusi_periode_bulan', $data['retribusi_periode_bulan']);
            $this->db->where('retribusi_periode_tahun', $data['retribusi_periode_tahun']);
            
            if ($id) {
                $this->db->where('retribusi_id !=', $id);
            }
            
            $existing = $this->db->get($this->table)->row();
            if ($existing) {
                $errors[] = 'Pembayaran untuk periode ini sudah ada';
            }
        }

        return $errors;
    }

    // Method untuk kalkulasi denda
    public function hitung_denda($nominal, $tanggal_jatuh_tempo, $tanggal_bayar, $persen_denda = 2.5)
    {
        $jatuh_tempo = new DateTime($tanggal_jatuh_tempo);
        $bayar = new DateTime($tanggal_bayar);
        
        if ($bayar <= $jatuh_tempo) {
            return 0; // Tidak ada denda jika bayar tepat waktu
        }
        
        $selisih_hari = $jatuh_tempo->diff($bayar)->days;
        $denda = ($nominal * $persen_denda / 100) * $selisih_hari;
        
        return round($denda, 2);
    }

    // Method untuk cek pembayaran harian
    public function cek_pembayaran_harian($pedagang_id, $tanggal)
    {
        $this->db->where('pedagang_id', $pedagang_id);
        $this->db->where('retribusi_tanggal', $tanggal);
        return $this->db->get($this->table)->row();
    }

    // Method untuk mendapatkan riwayat pembayaran pedagang
    public function get_riwayat_pembayaran($pedagang_id, $limit = 10, $offset = 0)
    {
        $this->db->where('pedagang_id', $pedagang_id);
        $this->db->order_by('retribusi_tanggal', 'desc');
        $this->db->limit($limit, $offset);
        return $this->db->get($this->view_lengkap)->result();
    }

    // Method untuk mendapatkan total pembayaran hari ini
    public function get_pembayaran_hari_ini($tanggal = null)
    {
        if (!$tanggal) $tanggal = date('Y-m-d');

        $this->db->select('
            COUNT(*) as total_transaksi,
            SUM(retribusi_total) as total_pendapatan,
            SUM(retribusi_denda) as total_denda
        ');
        $this->db->where('retribusi_tanggal', $tanggal);
        $this->db->where('retribusi_status', 'LUNAS');

        return $this->db->get($this->table)->row();
    }

    // Method untuk mendapatkan pedagang yang belum bayar hari ini
    public function get_pedagang_belum_bayar_hari_ini($tanggal = null)
    {
        if (!$tanggal) $tanggal = date('Y-m-d');

        $this->db->select('mp.*, mpb.pasar_blok_nama, mpj.pasar_jenis_nama, mps.pasar_nama, pb.id as penyewaan_blok_id');
        $this->db->from('masyarakat_pedagang mp');
        $this->db->join('penyewaan_blok pb', 'mp.id = pb.pedagang_id AND pb.status = "AKTIF"', 'inner');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');

        // Left join untuk cek apakah sudah ada pembayaran hari ini
        $this->db->join($this->table . ' rpt',
            'mp.id = rpt.pedagang_id AND rpt.retribusi_tanggal = "' . $tanggal . '"',
            'left');

        $this->db->where('mp.status_aktif', 1);
        $this->db->where('rpt.retribusi_id IS NULL'); // Belum ada pembayaran hari ini
        $this->db->order_by('mp.nama_lengkap', 'asc');

        return $this->db->get()->result();
    }

    // Method untuk laporan pembayaran per periode
    public function get_laporan_periode($tanggal_mulai, $tanggal_selesai, $status = null)
    {
        $this->db->where('retribusi_tanggal >=', $tanggal_mulai);
        $this->db->where('retribusi_tanggal <=', $tanggal_selesai);

        if ($status) {
            $this->db->where('retribusi_status', $status);
        }

        $this->db->order_by('retribusi_tanggal', 'desc');
        $this->db->order_by('pedagang_nama', 'asc');

        return $this->db->get($this->view_lengkap)->result();
    }

    // Method untuk DataTables dengan join
    public function get_datatables($table, $column_order, $column_search, $order, $join = null, $where = null, $group_by = null, $select = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $order, $join, $where, $group_by, $select);
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered($table, $column_order, $column_search, $order, $join = null, $where = null, $group_by = null, $select = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $order, $join, $where, $group_by, $select);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($table, $where = null)
    {
        if ($where != null) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($table);
    }

    private function _get_datatables_query($table, $column_order, $column_search, $order, $join = null, $where = null, $group_by = null, $select = null)
    {
        if ($select != null) {
            $this->db->select($select);
        }

        $this->db->from($table);

        if ($join != null) {
            foreach ($join as $value) {
                $this->db->join($value[0], $value[1], $value[2]);
            }
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

        if ($where != null) {
            $this->db->where($where);
        }

        if ($group_by != null) {
            $this->db->group_by($group_by);
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($order)) {
            $order_key = key($order);
            $this->db->order_by($order_key, $order[$order_key]);
        }
    }
}
