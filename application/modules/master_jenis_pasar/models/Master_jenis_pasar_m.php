<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_jenis_pasar_m extends CI_Model
{
    private $table = 'master_pasar_jenis';

    public function master_jenis_pasar_get_all()
    {
        return $this->db->order_by('pasar_jenis_id', 'desc')->get($this->table)->result();
    }

    public function master_jenis_pasar_get_active()
    {
        return $this->db->where('pasar_jenis_status_sewa', 'TERSEDIA')->order_by('pasar_jenis_nama', 'asc')->get($this->table)->result();
    }

    public function master_jenis_pasar_by_id($id)
    {
        return $this->db->where('pasar_jenis_id', $id)->get($this->table)->row();
    }

    public function master_jenis_pasar_by_id_array($id)
    {
        return $this->db->where('pasar_jenis_id', $id)->get($this->table)->result();
    }

    public function master_jenis_pasar_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }

    public function master_jenis_pasar_update_data($post_data, $id)
    {
        $this->db->where('pasar_jenis_id', $id);
        return $this->db->update($this->table, $post_data);
    }

    public function master_jenis_pasar_delete_data($id)
    {
        $this->db->where('pasar_jenis_id', $id);
        return $this->db->delete($this->table);
    }

    // Method untuk mendapatkan jenis pasar berdasarkan pasar_id
    public function master_jenis_pasar_by_pasar_id($pasar_id)
    {
        return $this->db->where('pasar_id', $pasar_id)->get($this->table)->result();
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
            $order_key = array_keys($order)[0];
            $this->db->order_by($order_key, $order[$order_key]);
        }
    }

    // Method untuk mendapatkan statistik
    public function get_statistik()
    {
        $stats = [];
        
        // Total jenis pasar
        $stats['total_jenis'] = $this->db->count_all_results($this->table);
        
        // Total berdasarkan jenis
        $this->db->select('pasar_jenis_nama, COUNT(*) as total');
        $this->db->from($this->table);
        $this->db->group_by('pasar_jenis_nama');
        $jenis_stats = $this->db->get()->result();
        
        foreach ($jenis_stats as $jenis) {
            $stats['jenis_' . strtolower($jenis->pasar_jenis_nama)] = $jenis->total;
        }
        
        // Total berdasarkan status sewa
        $this->db->select('pasar_jenis_status_sewa, COUNT(*) as total');
        $this->db->from($this->table);
        $this->db->group_by('pasar_jenis_status_sewa');
        $status_stats = $this->db->get()->result();
        
        foreach ($status_stats as $status) {
            $stats['status_' . strtolower($status->pasar_jenis_status_sewa)] = $status->total;
        }
        
        return $stats;
    }

    // Method untuk mendapatkan options pasar
    public function get_pasar_options()
    {
        $this->db->select('pasar_id, pasar_nama');
        $this->db->from('master_pasar');
        $this->db->where('pasar_status', 1);
        $this->db->order_by('pasar_nama', 'asc');
        return $this->db->get()->result();
    }

    // Method untuk validasi data
    public function validate_jenis_pasar($pasar_id, $jenis_nama, $exclude_id = null)
    {
        $this->db->where('pasar_id', $pasar_id);
        $this->db->where('pasar_jenis_nama', $jenis_nama);
        
        if ($exclude_id) {
            $this->db->where('pasar_jenis_id !=', $exclude_id);
        }
        
        $result = $this->db->get($this->table);
        return $result->num_rows() == 0;
    }

    // Method untuk mendapatkan jenis pasar berdasarkan pasar_id
    public function get_by_pasar_id($pasar_id)
    {
        $this->db->select('mpj.*, mp.pasar_nama');
        $this->db->from($this->table . ' mpj');
        $this->db->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left');
        $this->db->where('mpj.pasar_id', $pasar_id);
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        return $this->db->get()->result();
    }

    // Method untuk sinkronisasi data dengan master_pasar_blok
    public function sinkronisasi_data_blok($pasar_jenis_id = null)
    {
        // Jika tidak ada ID spesifik, update semua data
        if ($pasar_jenis_id) {
            $where_condition = "WHERE mpj.pasar_jenis_id = " . (int)$pasar_jenis_id;
        } else {
            $where_condition = "";
        }

        $sql = "UPDATE master_pasar_jenis mpj
                SET
                    pasar_jenis_jumlah = (
                        SELECT COUNT(*)
                        FROM master_pasar_blok mpb
                        WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id
                    ),
                    pasar_jenis_tersedia = (
                        SELECT COUNT(*)
                        FROM master_pasar_blok mpb
                        WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id
                        AND mpb.pasar_blok_status = 'TERSEDIA'
                    ),
                    pasar_jenis_terisi = (
                        SELECT COUNT(*)
                        FROM master_pasar_blok mpb
                        WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id
                        AND mpb.pasar_blok_status = 'TERISI'
                    ),
                    pasar_jenis_updated_at = NOW()
                $where_condition";

        return $this->db->query($sql);
    }

    // Method untuk update status sewa otomatis berdasarkan jumlah dan terisi
    public function update_status_otomatis($id)
    {
        // Sinkronisasi data terlebih dahulu
        $this->sinkronisasi_data_blok($id);

        $data = $this->master_jenis_pasar_by_id($id); // Fix: use correct method name
        if ($data) {
            $status_baru = 'TERSEDIA';

            if ($data->pasar_jenis_terisi >= $data->pasar_jenis_jumlah) {
                $status_baru = 'PENUH';
            } elseif ($data->pasar_jenis_tersedia <= 0) {
                $status_baru = 'MAINTENANCE';
            }

            if ($status_baru != $data->pasar_jenis_status_sewa) {
                $this->db->where('pasar_jenis_id', $id);
                $this->db->update($this->table, ['pasar_jenis_status_sewa' => $status_baru]);
            }
        }
    }

    // =============================================
    // STATISTICS METHODS (for get_statistik() controller)
    // =============================================

    /**
     * Get total jenis pasar
     */
    public function get_total_jenis()
    {
        return $this->db->count_all_results($this->table);
    }

    /**
     * Get total jenis pasar dengan status TERSEDIA
     */
    public function get_total_tersedia()
    {
        return $this->db->where('pasar_jenis_status_sewa', 'TERSEDIA')->count_all_results($this->table);
    }

    /**
     * Get total jenis pasar dengan status PENUH
     */
    public function get_total_penuh()
    {
        return $this->db->where('pasar_jenis_status_sewa', 'PENUH')->count_all_results($this->table);
    }

    /**
     * Get total jenis pasar dengan status MAINTENANCE
     */
    public function get_total_maintenance()
    {
        return $this->db->where('pasar_jenis_status_sewa', 'MAINTENANCE')->count_all_results($this->table);
    }
}
