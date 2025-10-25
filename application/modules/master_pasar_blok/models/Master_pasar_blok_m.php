<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pasar_blok_m extends CI_Model
{
    private $table = 'master_pasar_blok';

    public function master_pasar_blok_get_all()
    {
        return $this->db->order_by('pasar_blok_id', 'desc')->get($this->table)->result();
    }
    
    public function master_pasar_blok_get_active()
    {
        return $this->db->where('pasar_blok_status', 'TERSEDIA')->order_by('pasar_blok_nama', 'asc')->get($this->table)->result();
    }
    
    public function master_pasar_blok_by_id($id)
    {
        return $this->db->where('pasar_blok_id', $id)->get($this->table)->row();
    }
    
    public function master_pasar_blok_by_id_array($id)
    {
        return $this->db->where('pasar_blok_id', $id)->get($this->table)->result();
    }
    
    public function master_pasar_blok_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function master_pasar_blok_update_data($post_data, $id)
    {
        $this->db->where('pasar_blok_id', $id);
        return $this->db->update($this->table, $post_data);
    }
    
    public function master_pasar_blok_delete_data($id)
    {
        $this->db->where('pasar_blok_id', $id);
        return $this->db->delete($this->table);
    }

    // Method untuk mendapatkan blok berdasarkan jenis pasar
    public function master_pasar_blok_by_jenis_id($jenis_id)
    {
        return $this->db->where('pasar_jenis_id', $jenis_id)->get($this->table)->result();
    }

    // Method untuk DataTables dengan join
    public function get_datatables($table, $column_order, $column_search, $order, $join = null, $where = null, $group_by = null, $select = null)
    {
        $this->_get_datatables_query($table, $column_order, $column_search, $order, $join, $where, $group_by, $select);
        $length = isset($_POST['length']) ? $_POST['length'] : -1;
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        
        if ($length != -1) {
            $this->db->limit($length, $start);
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

    public function count_all($table, $join = null, $where = null, $group_by = null)
    {
        if ($join != null) {
            foreach ($join as $value) {
                $this->db->join($value[0], $value[1], $value[2]);
            }
        }
        if ($where != null) {
            $this->db->where($where);
        }
        if ($group_by != null) {
            $this->db->group_by($group_by);
        }
        $this->db->from($table);
        return $this->db->count_all_results();
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

        if ($where != null) {
            $this->db->where($where);
        }

        if ($group_by != null) {
            $this->db->group_by($group_by);
        }

        $i = 0;
        $search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
        
        foreach ($column_search as $item) {
            if ($search_value) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }

                if (count($column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if (isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) {
            $this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($order)) {
            $order_key = array_keys($order);
            $this->db->order_by($order_key[0], $order[$order_key[0]]);
        }
    }

    // Method untuk statistik
    public function get_total_blok()
    {
        return $this->db->count_all($this->table);
    }

    public function get_total_tersedia()
    {
        return $this->db->where('pasar_blok_status', 'TERSEDIA')->count_all_results($this->table);
    }

    public function get_total_terisi()
    {
        return $this->db->where('pasar_blok_status', 'TERISI')->count_all_results($this->table);
    }

    public function get_total_maintenance()
    {
        return $this->db->where('pasar_blok_status', 'MAINTENANCE')->count_all_results($this->table);
    }

    // Method khusus untuk denah
    public function get_denah_data($filter_pasar = null, $filter_jenis = null, $filter_status = null)
    {
        $this->db->select('mpb.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from($this->table . ' mpb');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id=mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left');
        
        if ($filter_pasar && $filter_pasar != '') {
            $this->db->where('mp.pasar_id', $filter_pasar);
        }
        
        if ($filter_jenis && $filter_jenis != '') {
            $this->db->where('mpj.pasar_jenis_nama', $filter_jenis);
        }
        
        if ($filter_status && $filter_status != '') {
            $this->db->where('mpb.pasar_blok_status', $filter_status);
        }
        
        $this->db->order_by('mpb.pasar_blok_posisi_y', 'asc');
        $this->db->order_by('mpb.pasar_blok_posisi_x', 'asc');
        
        return $this->db->get()->result();
    }

    public function update_posisi($id, $x, $y)
    {
        $data = [
            'pasar_blok_posisi_x' => $x,
            'pasar_blok_posisi_y' => $y,
            'pasar_blok_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('pasar_blok_id', $id);
        return $this->db->update($this->table, $data);
    }

    public function get_jenis_pasar_options($pasar_id = null)
    {
        $this->db->select('mpj.pasar_jenis_id, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_jenis mpj');
        $this->db->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left');
        
        if ($pasar_id && $pasar_id != '') {
            $this->db->where('mp.pasar_id', $pasar_id);
        }
        
        $this->db->order_by('mp.pasar_nama', 'asc');
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        
        return $this->db->get()->result();
    }

    // Method untuk bulk operations
    public function bulk_update_status($ids, $status)
    {
        $data = [
            'pasar_blok_status' => $status,
            'pasar_blok_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where_in('pasar_blok_id', $ids);
        return $this->db->update($this->table, $data);
    }

    // Method untuk generate default blok
    public function generate_default_blok($pasar_jenis_id, $jumlah = 10)
    {
        $jenis_data = $this->db->select('mpj.*, mp.pasar_nama')
                               ->from('master_pasar_jenis mpj')
                               ->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left')
                               ->where('mpj.pasar_jenis_id', $pasar_jenis_id)
                               ->get()->row();
        
        if (!$jenis_data) {
            return false;
        }
        
        $prefix = substr($jenis_data->pasar_jenis_nama, 0, 1); // K, L, P, atau L
        $grid_size = ceil(sqrt($jumlah)); // Grid persegi
        
        $blok_data = [];
        $counter = 1;
        
        for ($y = 0; $y < $grid_size; $y++) {
            for ($x = 0; $x < $grid_size; $x++) {
                if ($counter > $jumlah) break;
                
                $blok_data[] = [
                    'pasar_jenis_id' => $pasar_jenis_id,
                    'pasar_blok_nama' => $prefix . '-' . str_pad($counter, 2, '0', STR_PAD_LEFT),
                    'pasar_blok_nomor' => $counter,
                    'pasar_blok_luas' => 0,
                    'pasar_blok_harga_sewa' => $jenis_data->pasar_jenis_harga_sewa,
                    'pasar_blok_status' => 'TERSEDIA',
                    'pasar_blok_posisi_x' => $x * 120 + 50, // Spacing 120px
                    'pasar_blok_posisi_y' => $y * 120 + 50,
                    'pasar_blok_created_at' => date('Y-m-d H:i:s'),
                    'pasar_blok_updated_at' => date('Y-m-d H:i:s')
                ];
                
                $counter++;
            }
        }
        
        return $this->db->insert_batch($this->table, $blok_data);
    }

    // Method untuk validasi nama blok
    public function validate_nama_blok($pasar_jenis_id, $nama_blok, $exclude_id = null)
    {
        $this->db->where('pasar_jenis_id', $pasar_jenis_id);
        $this->db->where('pasar_blok_nama', $nama_blok);
        
        if ($exclude_id) {
            $this->db->where('pasar_blok_id !=', $exclude_id);
        }
        
        $result = $this->db->get($this->table);
        return $result->num_rows() == 0;
    }

    // Method untuk mendapatkan statistik per jenis pasar
    public function get_statistik_per_jenis()
    {
        $this->db->select('mpj.pasar_jenis_nama, mp.pasar_nama, 
                          COUNT(mpb.pasar_blok_id) as total_blok,
                          SUM(CASE WHEN mpb.pasar_blok_status = "TERSEDIA" THEN 1 ELSE 0 END) as tersedia,
                          SUM(CASE WHEN mpb.pasar_blok_status = "TERISI" THEN 1 ELSE 0 END) as terisi,
                          SUM(CASE WHEN mpb.pasar_blok_status = "MAINTENANCE" THEN 1 ELSE 0 END) as maintenance');
        $this->db->from($this->table . ' mpb');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id=mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left');
        $this->db->group_by('mpb.pasar_jenis_id');
        $this->db->order_by('mp.pasar_nama', 'asc');
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        
        return $this->db->get()->result();
    }
}
