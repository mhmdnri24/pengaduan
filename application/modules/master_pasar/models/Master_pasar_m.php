<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pasar_m extends CI_Model
{
    private $table = 'master_pasar';
    private $table_jenis = 'master_pasar_jenis';

    public function master_pasar_get_all()
    {
        return $this->db->order_by('pasar_id', 'desc')->get($this->table)->result();
    }
    
    public function master_pasar_get_active()
    {
        return $this->db->where('pasar_status', 1)->order_by('pasar_nama', 'asc')->get($this->table)->result();
    }
    
    public function master_pasar_by_id($id)
    {
        return $this->db->where('pasar_id', $id)->get($this->table)->row();
    }
    
    public function master_pasar_by_id_array($id)
    {
        return $this->db->where('pasar_id', $id)->get($this->table)->result();
    }
    
    public function master_pasar_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function master_pasar_update_data($post_data, $id)
    {
        $this->db->where('pasar_id', $id);
        return $this->db->update($this->table, $post_data);
    }
    
    public function master_pasar_delete_data($id)
    {
        $this->db->where('pasar_id', $id);
        return $this->db->delete($this->table);
    }

    // Method untuk tabel master_pasar_jenis
    public function master_pasar_jenis_by_pasar_id($pasar_id)
    {
        return $this->db->where('pasar_id', $pasar_id)->get($this->table_jenis)->result();
    }

    public function master_pasar_jenis_by_id($id)
    {
        return $this->db->where('pasar_jenis_id', $id)->get($this->table_jenis)->row();
    }

    public function master_pasar_jenis_update_data($post_data, $id)
    {
        $this->db->where('pasar_jenis_id', $id);
        return $this->db->update($this->table_jenis, $post_data);
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
            $order = $order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function check_nama_exists($pasar_nama, $exclude_id = null)
    {
        $this->db->where('pasar_nama', $pasar_nama);
        if ($exclude_id) {
            $this->db->where('pasar_id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Validation method
    public function validate_pasar_data($data, $id = null)
    {
        $errors = [];

        // Validasi nama pasar
        if (empty($data['pasar_nama'])) {
            $errors[] = 'Nama pasar tidak boleh kosong';
        }

        // Check for duplicate nama pasar
        if (!empty($data['pasar_nama'])) {
            if ($this->check_nama_exists($data['pasar_nama'], $id)) {
                $errors[] = 'Nama pasar sudah digunakan';
            }
        }

        return $errors;
    }

    // Method untuk statistik
    public function get_total_pasar()
    {
        return $this->db->count_all($this->table);
    }

    public function get_total_pasar_aktif()
    {
        return $this->db->where('pasar_status', 1)->count_all_results($this->table);
    }

    public function get_total_pasar_nonaktif()
    {
        return $this->db->where('pasar_status', 0)->count_all_results($this->table);
    }

    // Method untuk mendapatkan data pasar dengan jenis
    public function get_pasar_with_jenis($pasar_id)
    {
        $this->db->select('mp.*, mpj.pasar_jenis_nama, mpj.pasar_jenis_jumlah, mpj.pasar_jenis_tersedia, mpj.pasar_jenis_terisi, mpj.pasar_jenis_harga_sewa, mpj.pasar_jenis_status_sewa');
        $this->db->from($this->table . ' mp');
        $this->db->join($this->table_jenis . ' mpj', 'mp.pasar_id = mpj.pasar_id', 'left');
        $this->db->where('mp.pasar_id', $pasar_id);
        return $this->db->get()->result();
    }

    // Method untuk mendapatkan ringkasan jenis pasar
    public function get_jenis_pasar_summary()
    {
        $this->db->select('pasar_jenis_nama, COUNT(*) as total_pasar, SUM(pasar_jenis_jumlah) as total_unit, SUM(pasar_jenis_tersedia) as total_tersedia, SUM(pasar_jenis_terisi) as total_terisi');
        $this->db->from($this->table_jenis);
        $this->db->group_by('pasar_jenis_nama');
        return $this->db->get()->result();
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Get active pasar for frontend
     */
    public function get_active_pasar()
    {
        return $this->master_pasar_get_active();
    }
}
