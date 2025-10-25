<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penyewaan_blok_m extends CI_Model
{
    private $table = 'penyewaan_blok';

    public function penyewaan_blok_get_all()
    {
        return $this->db->order_by('created_at', 'desc')->get($this->table)->result();
    }

    public function penyewaan_blok_get_active()
    {
        return $this->db->where('status', 'AKTIF')->order_by('created_at', 'desc')->get($this->table)->result();
    }

    public function penyewaan_blok_by_id($id)
    {
        $this->db->select('pb.*,
                          mp.nama_lengkap, mp.nik, mp.no_telpon, mp.alamat,
                          mpb.pasar_blok_nama, mpb.pasar_blok_nomor, mpb.pasar_blok_luas, mpb.pasar_blok_harga_sewa,
                          mpu.pasar_unit_nomor, mpu.pasar_unit_luas, mpu.pasar_unit_harga_sewa,
                          mpj.pasar_jenis_nama,
                          mps.pasar_nama');
        $this->db->from($this->table . ' pb');
        $this->db->join('masyarakat_pedagang mp', 'pb.pedagang_id = mp.id', 'left');
        
        // Join ke master_pasar_blok (always untuk backward compatibility)
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        
        // Join ke master_pasar_unit (jika ada unit_id)
        $this->db->join('master_pasar_unit mpu', 'pb.pasar_unit_id = mpu.pasar_unit_id', 'left');
        
        // Join ke master_pasar_jenis melalui blok (unit tidak memiliki direct link ke jenis)
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');
        $this->db->where('pb.id', $id);
        return $this->db->get()->row();
    }

    public function penyewaan_blok_by_id_array($id)
    {
        $this->db->select('pb.*,
                          mp.nama_lengkap, mp.nik, mp.no_telpon, mp.alamat,
                          mpb.pasar_blok_nama, mpb.pasar_blok_nomor, mpb.pasar_blok_luas, mpb.pasar_blok_harga_sewa,
                          mpu.pasar_unit_nomor, mpu.pasar_unit_luas, mpu.pasar_unit_harga_sewa,
                          mpj.pasar_jenis_nama,
                          mps.pasar_nama');
        $this->db->from($this->table . ' pb');
        $this->db->join('masyarakat_pedagang mp', 'pb.pedagang_id = mp.id', 'left');
        
        // Join ke master_pasar_blok (always untuk backward compatibility)
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        
        // Join ke master_pasar_unit (jika ada unit_id)
        $this->db->join('master_pasar_unit mpu', 'pb.pasar_unit_id = mpu.pasar_unit_id', 'left');
        
        // Join ke master_pasar_jenis melalui blok (unit tidak memiliki direct link ke jenis)
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');
        $this->db->where('pb.id', $id);
        return $this->db->get()->result();
    }

    public function penyewaan_blok_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }

    public function penyewaan_blok_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }

    public function penyewaan_blok_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    // Get penyewaan by pedagang_id
    public function get_by_pedagang_id($pedagang_id)
    {
        $this->db->select('pb.*,
                          mpb.pasar_blok_nama, mpb.pasar_blok_nomor,
                          mpu.pasar_unit_nomor,
                          mpj.pasar_jenis_nama, mp.pasar_nama,
                          COALESCE(mpu.pasar_unit_harga_sewa, mpb.pasar_blok_harga_sewa) as harga_sewa_display');
        $this->db->from($this->table . ' pb');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_unit mpu', 'pb.pasar_unit_id = mpu.pasar_unit_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('pb.pedagang_id', $pedagang_id);
        $this->db->order_by('pb.created_at', 'desc');
        return $this->db->get()->result();
    }

    // Get penyewaan by blok_id
    public function get_by_blok_id($blok_id)
    {
        $this->db->select('pb.*, m.nama_lengkap, m.nik, m.no_telpon');
        $this->db->from($this->table . ' pb');
        $this->db->join('masyarakat_pedagang m', 'pb.pedagang_id = m.id', 'left');
        $this->db->where('pb.pasar_blok_id', $blok_id);
        $this->db->where('pb.status', 'AKTIF');
        $this->db->order_by('pb.created_at', 'desc');
        return $this->db->get()->row();
    }

    // Check if blok is available for rental (backward compatibility)
    public function check_blok_available($blok_id, $tanggal_mulai, $tanggal_selesai, $exclude_id = null)
    {
        $this->db->where('pasar_blok_id', $blok_id);
        $this->db->where('status', 'AKTIF');

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        // Check for overlapping dates
        $this->db->group_start();
        $this->db->where('tanggal_mulai <=', $tanggal_selesai);
        $this->db->where('tanggal_selesai >=', $tanggal_mulai);
        $this->db->group_end();

        $result = $this->db->get($this->table);
        return $result->num_rows() == 0;
    }

    // Check if unit is available for rental
    public function check_unit_available($unit_id, $tanggal_mulai, $tanggal_selesai, $exclude_id = null)
    {
        $this->db->where('pasar_unit_id', $unit_id);
        $this->db->where('status', 'AKTIF');

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        // Check for overlapping dates
        $this->db->group_start();
        $this->db->where('tanggal_mulai <=', $tanggal_selesai);
        $this->db->where('tanggal_selesai >=', $tanggal_mulai);
        $this->db->group_end();

        $result = $this->db->get($this->table);
        return $result->num_rows() == 0;
    }

    // Update status
    public function update_status($id, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    // Get expired rentals
    public function get_expired_rentals()
    {
        $this->db->where('status', 'AKTIF');
        $this->db->where('tanggal_selesai <', date('Y-m-d'));
        return $this->db->get($this->table)->result();
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

    // Validation method
    public function validate_penyewaan_data($data, $id = null)
    {
        $errors = [];

        // Validasi pedagang_id
        if (empty($data['pedagang_id'])) {
            $errors[] = 'Pedagang harus dipilih';
        }

        // Validasi: harus ada salah satu (pasar_blok_id untuk backward compatibility atau pasar_unit_id untuk baru)
        if (empty($data['pasar_blok_id']) && empty($data['pasar_unit_id'])) {
            $errors[] = 'Blok atau Unit harus dipilih';
        }

        // Untuk penyewaan baru, unit_id harus ada
        if (empty($id) && empty($data['pasar_unit_id'])) {
            $errors[] = 'Unit harus dipilih';
        }

        // Validasi tanggal
        if (empty($data['tanggal_mulai'])) {
            $errors[] = 'Tanggal mulai harus diisi';
        }

        if (empty($data['tanggal_selesai'])) {
            $errors[] = 'Tanggal selesai harus diisi';
        }

        if (!empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
            if (strtotime($data['tanggal_selesai']) <= strtotime($data['tanggal_mulai'])) {
                $errors[] = 'Tanggal selesai harus lebih besar dari tanggal mulai';
            }
        }

        // Check unit availability (prioritas utama)
        if (!empty($data['pasar_unit_id']) && !empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
            if (!$this->check_unit_available($data['pasar_unit_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], $id)) {
                $errors[] = 'Unit sudah disewa pada periode tanggal tersebut';
            }
        }
        // Check blok availability (backward compatibility)
        elseif (!empty($data['pasar_blok_id']) && !empty($data['tanggal_mulai']) && !empty($data['tanggal_selesai'])) {
            if (!$this->check_blok_available($data['pasar_blok_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], $id)) {
                $errors[] = 'Blok sudah disewa pada periode tanggal tersebut';
            }
        }

        return $errors;
    }

    // Get available blocks for rental (backward compatibility)
    public function get_available_blocks($tanggal_mulai, $tanggal_selesai, $pasar_id = null, $jenis_id = null)
    {
        // First get all rented block IDs for the date range
        $this->db->select('pasar_blok_id');
        $this->db->from('penyewaan_blok');
        $this->db->where('status', 'AKTIF');
        $this->db->where('tanggal_mulai <=', $tanggal_selesai);
        $this->db->where('tanggal_selesai >=', $tanggal_mulai);
        $rented_blocks = $this->db->get()->result();

        // Extract block IDs
        $rented_block_ids = array_column($rented_blocks, 'pasar_blok_id');

        // Now get available blocks
        $this->db->select('mpb.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_blok mpb');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('mpb.pasar_blok_status', 'TERSEDIA');

        // Exclude rented blocks
        if (!empty($rented_block_ids)) {
            $this->db->where_not_in('mpb.pasar_blok_id', $rented_block_ids);
        }

        if ($pasar_id) {
            $this->db->where('mp.pasar_id', $pasar_id);
        }

        if ($jenis_id) {
            $this->db->where('mpj.pasar_jenis_id', $jenis_id);
        }

        $this->db->order_by('mp.pasar_nama', 'asc');
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        $this->db->order_by('mpb.pasar_blok_nama', 'asc');

        return $this->db->get()->result();
    }

    // Get available units for rental
    public function get_available_units($tanggal_mulai, $tanggal_selesai, $pasar_id = null, $jenis_id = null, $blok_id = null)
    {
        // First get all rented unit IDs for the date range
        $this->db->select('pasar_unit_id');
        $this->db->from('penyewaan_blok');
        $this->db->where('status', 'AKTIF');
        $this->db->where('tanggal_mulai <=', $tanggal_selesai);
        $this->db->where('tanggal_selesai >=', $tanggal_mulai);
        $rented_units = $this->db->get()->result();

        // Extract unit IDs
        $rented_unit_ids = array_column($rented_units, 'pasar_unit_id');

        // Now get available units
        $this->db->select('mpu.*, mpb.pasar_blok_nama, mpb.pasar_blok_nomor, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_unit mpu');
        $this->db->join('master_pasar_blok mpb', 'mpu.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('mpu.pasar_unit_status', 'TERSEDIA');

        // Exclude rented units
        if (!empty($rented_unit_ids)) {
            $this->db->where_not_in('mpu.pasar_unit_id', $rented_unit_ids);
        }

        if ($pasar_id) {
            $this->db->where('mp.pasar_id', $pasar_id);
        }

        if ($jenis_id) {
            $this->db->where('mpj.pasar_jenis_id', $jenis_id);
        }

        if ($blok_id) {
            $this->db->where('mpb.pasar_blok_id', $blok_id);
        }

        $this->db->order_by('mp.pasar_nama', 'asc');
        $this->db->order_by('mpj.pasar_jenis_nama', 'asc');
        $this->db->order_by('mpb.pasar_blok_nama', 'asc');
        $this->db->order_by('mpu.pasar_unit_nomor', 'asc');

        return $this->db->get()->result();
    }

    // Get blocks with units for denah display
    public function get_blocks_with_units($jenis_id, $tanggal_mulai, $tanggal_selesai)
    {
        // Get all blocks for this jenis
        $this->db->select('mpb.*, mpj.pasar_jenis_nama, mp.pasar_nama');
        $this->db->from('master_pasar_blok mpb');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id', 'left');
        $this->db->where('mpb.pasar_jenis_id', $jenis_id);
        $this->db->order_by('mpb.pasar_blok_posisi_y', 'asc');
        $this->db->order_by('mpb.pasar_blok_posisi_x', 'asc');
        
        $query = $this->db->get();
        
        // Debug: Log the SQL query and number of results
        error_log('get_blocks_with_units SQL: ' . $this->db->last_query());
        error_log('get_blocks_with_units Results: ' . $query->num_rows());
        
        $blocks = $query->result();
        
        // Load the master_pasar_unit model
        $this->load->model('master_pasar_blok/Master_pasar_unit_m');
        
        // Get units for each block and check availability
        foreach ($blocks as &$block) {
            $block->units = $this->Master_pasar_unit_m->get_units_by_blok_id($block->pasar_blok_id);
            
            // Check availability for each unit
            foreach ($block->units as &$unit) {
                $unit->is_available = $this->check_unit_available(
                    $unit->pasar_unit_id,
                    $tanggal_mulai,
                    $tanggal_selesai
                );
            }
            
            // Check if block has any available units
            $block->has_available_units = !empty(array_filter($block->units, function($unit) {
                return $unit->is_available;
            }));
        }
        
        return $blocks;
    }

    // Get statistics
    public function get_statistics()
    {
        $stats = [];

        // Total penyewaan
        $stats['total_penyewaan'] = $this->db->count_all($this->table);

        // By status
        $this->db->select('status, COUNT(*) as count');
        $this->db->group_by('status');
        $status_result = $this->db->get($this->table)->result();

        foreach ($status_result as $row) {
            $stats['status_' . strtolower($row->status)] = $row->count;
        }

        // Active rentals
        $stats['active_rentals'] = $this->db->where('status', 'AKTIF')->count_all_results($this->table);

        // Expired rentals
        $stats['expired_rentals'] = $this->db->where('status', 'AKTIF')->where('tanggal_selesai <', date('Y-m-d'))->count_all_results($this->table);

        return $stats;
    }
}