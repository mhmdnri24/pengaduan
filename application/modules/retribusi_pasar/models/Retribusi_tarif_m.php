<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retribusi_tarif_m extends CI_Model
{
    private $table = 'retribusi_pasar_tarif';

    public function tarif_get_all()
    {
        $this->db->select('rpt.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from($this->table . ' rpt');
        $this->db->join('master_pasar_jenis mpj', 'rpt.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->order_by('rpt.tarif_berlaku_dari', 'desc');
        $this->db->order_by('rpt.tarif_id', 'desc');
        return $this->db->get()->result();
    }
    
    public function tarif_get_active()
    {
        $this->db->select('rpt.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from($this->table . ' rpt');
        $this->db->join('master_pasar_jenis mpj', 'rpt.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('rpt.tarif_status', 1);
        $this->db->where('rpt.tarif_berlaku_dari <=', date('Y-m-d'));
        $this->db->group_start();
        $this->db->where('rpt.tarif_berlaku_sampai IS NULL');
        $this->db->or_where('rpt.tarif_berlaku_sampai >=', date('Y-m-d'));
        $this->db->group_end();
        $this->db->order_by('rpt.tarif_berlaku_dari', 'desc');
        return $this->db->get()->result();
    }
    
    public function tarif_by_id($id)
    {
        $this->db->select('rpt.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from($this->table . ' rpt');
        $this->db->join('master_pasar_jenis mpj', 'rpt.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('rpt.tarif_id', $id);
        return $this->db->get()->row();
    }
    
    public function tarif_by_id_array($id)
    {
        return $this->db->where('tarif_id', $id)->get($this->table)->result();
    }
    
    public function tarif_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function tarif_update_data($post_data, $id)
    {
        return $this->db->where('tarif_id', $id)->update($this->table, $post_data);
    }
    
    public function tarif_delete_data($id)
    {
        return $this->db->where('tarif_id', $id)->delete($this->table);
    }

    // Method untuk mendapatkan tarif berdasarkan jenis pasar
    public function get_by_jenis_pasar($jenis_pasar_id, $tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $this->db->where('pasar_jenis_id', $jenis_pasar_id);
        $this->db->where('tarif_status', 1);
        $this->db->where('tarif_berlaku_dari <=', $tanggal);
        $this->db->group_start();
        $this->db->where('tarif_berlaku_sampai IS NULL');
        $this->db->or_where('tarif_berlaku_sampai >=', $tanggal);
        $this->db->group_end();
        $this->db->order_by('tarif_berlaku_dari', 'desc');
        $this->db->limit(1);

        return $this->db->get($this->table)->row();
    }

    // Alias method untuk konsistensi
    public function get_tarif_aktif_by_jenis($jenis_pasar_id, $tanggal = null)
    {
        return $this->get_by_jenis_pasar($jenis_pasar_id, $tanggal);
    }

    // Method untuk mendapatkan tarif berdasarkan blok pasar
    public function get_by_blok_pasar($blok_id, $tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $this->db->select('rpt.*');
        $this->db->from($this->table . ' rpt');
        $this->db->join('master_pasar_jenis mpj', 'rpt.pasar_jenis_id = mpj.pasar_jenis_id', 'inner');
        $this->db->join('master_pasar_blok mpb', 'mpj.pasar_jenis_id = mpb.pasar_jenis_id', 'inner');
        $this->db->where('mpb.pasar_blok_id', $blok_id);
        $this->db->where('rpt.tarif_status', 1);
        $this->db->where('rpt.tarif_berlaku_dari <=', $tanggal);
        $this->db->group_start();
        $this->db->where('rpt.tarif_berlaku_sampai IS NULL');
        $this->db->or_where('rpt.tarif_berlaku_sampai >=', $tanggal);
        $this->db->group_end();
        $this->db->order_by('rpt.tarif_berlaku_dari', 'desc');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    // Method untuk mendapatkan semua jenis pasar untuk dropdown
    public function get_jenis_pasar_options()
    {
        $this->db->select('mpj.pasar_jenis_id, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_jenis mpj');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('mp.pasar_status', 1);
        $this->db->order_by('mp.pasar_nama', 'asc');
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        
        return $this->db->get()->result();
    }

    // Method untuk validasi data tarif
    public function validate_tarif_data($data, $id = null)
    {
        $errors = [];

        // Validasi pasar_jenis_id
        if (empty($data['pasar_jenis_id'])) {
            $errors[] = 'Jenis pasar harus dipilih';
        }

        // Validasi nama tarif
        if (empty($data['tarif_nama'])) {
            $errors[] = 'Nama tarif tidak boleh kosong';
        }

        // Validasi tarif harian
        if (empty($data['tarif_harian']) || $data['tarif_harian'] < 0) {
            $errors[] = 'Tarif harian harus diisi dan tidak boleh negatif';
        }

        // Validasi tarif bulanan
        if (empty($data['tarif_bulanan']) || $data['tarif_bulanan'] < 0) {
            $errors[] = 'Tarif bulanan harus diisi dan tidak boleh negatif';
        }

        // Validasi persentase denda
        if (!isset($data['tarif_denda_persen']) || $data['tarif_denda_persen'] < 0 || $data['tarif_denda_persen'] > 100) {
            $errors[] = 'Persentase denda harus antara 0-100%';
        }

        // Validasi tanggal berlaku
        if (empty($data['tarif_berlaku_dari'])) {
            $errors[] = 'Tanggal mulai berlaku harus diisi';
        }

        // Validasi tanggal berlaku sampai (jika diisi)
        if (!empty($data['tarif_berlaku_dari']) && !empty($data['tarif_berlaku_sampai'])) {
            if (strtotime($data['tarif_berlaku_sampai']) <= strtotime($data['tarif_berlaku_dari'])) {
                $errors[] = 'Tanggal berakhir harus lebih besar dari tanggal mulai';
            }
        }

        // Cek overlap periode untuk jenis pasar yang sama
        if (!empty($data['pasar_jenis_id']) && !empty($data['tarif_berlaku_dari'])) {
            $this->db->where('pasar_jenis_id', $data['pasar_jenis_id']);
            $this->db->where('tarif_status', 1);
            
            if ($id) {
                $this->db->where('tarif_id !=', $id);
            }

            // Cek overlap periode
            $this->db->group_start();
            $this->db->where('tarif_berlaku_dari <=', $data['tarif_berlaku_dari']);
            $this->db->group_start();
            $this->db->where('tarif_berlaku_sampai IS NULL');
            $this->db->or_where('tarif_berlaku_sampai >=', $data['tarif_berlaku_dari']);
            $this->db->group_end();
            $this->db->group_end();

            if (!empty($data['tarif_berlaku_sampai'])) {
                $this->db->or_group_start();
                $this->db->where('tarif_berlaku_dari <=', $data['tarif_berlaku_sampai']);
                $this->db->group_start();
                $this->db->where('tarif_berlaku_sampai IS NULL');
                $this->db->or_where('tarif_berlaku_sampai >=', $data['tarif_berlaku_sampai']);
                $this->db->group_end();
                $this->db->group_end();
            }

            $existing = $this->db->get($this->table)->row();
            if ($existing) {
                $errors[] = 'Periode tarif bertumpang tindih dengan tarif yang sudah ada';
            }
        }

        return $errors;
    }

    // Method untuk mendapatkan statistik tarif
    public function get_statistik_tarif()
    {
        $this->db->select('
            COUNT(*) as total_tarif,
            SUM(CASE WHEN tarif_status = 1 THEN 1 ELSE 0 END) as tarif_aktif,
            SUM(CASE WHEN tarif_status = 0 THEN 1 ELSE 0 END) as tarif_nonaktif,
            AVG(tarif_harian) as rata_rata_harian,
            AVG(tarif_bulanan) as rata_rata_bulanan,
            AVG(tarif_denda_persen) as rata_rata_denda
        ');
        
        return $this->db->get($this->table)->row();
    }

    // Method untuk mendapatkan riwayat perubahan tarif
    public function get_riwayat_tarif($jenis_pasar_id)
    {
        $this->db->select('rpt.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from($this->table . ' rpt');
        $this->db->join('master_pasar_jenis mpj', 'rpt.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('rpt.pasar_jenis_id', $jenis_pasar_id);
        $this->db->order_by('rpt.tarif_berlaku_dari', 'desc');
        
        return $this->db->get()->result();
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
