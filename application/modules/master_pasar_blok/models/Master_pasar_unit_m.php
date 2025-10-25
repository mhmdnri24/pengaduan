<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pasar_unit_m extends CI_Model
{
    private $table = 'master_pasar_unit';

    // Get all units by blok_id
    public function get_units_by_blok_id($blok_id)
    {
        // Debug: Log the query
        $this->db->where('pasar_blok_id', $blok_id);
        $this->db->order_by('pasar_unit_nomor', 'asc');
        $query = $this->db->get($this->table);
        
        // Debug: Log the SQL query and number of results
        error_log('get_units_by_blok_id SQL: ' . $this->db->last_query());
        error_log('get_units_by_blok_id Results: ' . $query->num_rows());
        
        return $query->result();
    }

    // Get unit by id
    public function get_unit_by_id($id)
    {
        return $this->db->where('pasar_unit_id', $id)
                        ->get($this->table)
                        ->row();
    }

    // Insert new unit
    public function insert_unit($data)
    {
        $data['pasar_unit_created_at'] = date('Y-m-d H:i:s');
        $data['pasar_unit_updated_at'] = date('Y-m-d H:i:s');
        
        // Calculate luas if not provided
        if (!isset($data['pasar_unit_luas']) || $data['pasar_unit_luas'] == 0) {
            $data['pasar_unit_luas'] = $data['pasar_unit_lebar'] * $data['pasar_unit_panjang'];
        }
        
        return $this->db->insert($this->table, $data);
    }

    // Update unit
    public function update_unit($data, $id)
    {
        $data['pasar_unit_updated_at'] = date('Y-m-d H:i:s');
        
        // Calculate luas if not provided
        if (!isset($data['pasar_unit_luas']) || $data['pasar_unit_luas'] == 0) {
            $data['pasar_unit_luas'] = $data['pasar_unit_lebar'] * $data['pasar_unit_panjang'];
        }
        
        $this->db->where('pasar_unit_id', $id);
        return $this->db->update($this->table, $data);
    }

    // Delete unit
    public function delete_unit($id)
    {
        $this->db->where('pasar_unit_id', $id);
        return $this->db->delete($this->table);
    }

    // Check if unit number exists in blok
    public function check_unit_nomor_exists($blok_id, $nomor, $exclude_id = null)
    {
        $this->db->where('pasar_blok_id', $blok_id);
        $this->db->where('pasar_unit_nomor', $nomor);
        
        if ($exclude_id) {
            $this->db->where('pasar_unit_id !=', $exclude_id);
        }
        
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Get unit statistics
    public function get_unit_stats($blok_id = null)
    {
        if ($blok_id) {
            $this->db->where('pasar_blok_id', $blok_id);
        }
        
        $result = $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN pasar_unit_status = "TERSEDIA" THEN 1 ELSE 0 END) as tersedia,
            SUM(CASE WHEN pasar_unit_status = "TERISI" THEN 1 ELSE 0 END) as terisi,
            SUM(CASE WHEN pasar_unit_status = "MAINTENANCE" THEN 1 ELSE 0 END) as maintenance
        ')->get($this->table)->row();
        
        return $result;
    }

    // Get units with expiry notification (within 30 days)
    public function get_units_expiring_soon($days = 30)
    {
        $expiry_date = date('Y-m-d', strtotime("+$days days"));
        
        return $this->db->select('mpu.*, mpb.pasar_blok_nama, mpj.pasar_jenis_nama, mp.pasar_nama')
                        ->from($this->table . ' mpu')
                        ->join('master_pasar_blok mpb', 'mpu.pasar_blok_id = mpb.pasar_blok_id')
                        ->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id')
                        ->join('master_pasar mp', 'mpj.pasar_id = mp.pasar_id')
                        ->where('mpu.pasar_unit_status', 'TERISI')
                        ->where('mpu.pasar_unit_tanggal_jatuh_tempo <=', $expiry_date)
                        ->where('mpu.pasar_unit_tanggal_jatuh_tempo >=', date('Y-m-d'))
                        ->order_by('mpu.pasar_unit_tanggal_jatuh_tempo', 'asc')
                        ->get()
                        ->result();
    }

    // Bulk update status
    public function bulk_update_status($ids, $status)
    {
        $data = [
            'pasar_unit_status' => $status,
            'pasar_unit_updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Clear penyewa and dates if status is not TERISI
        if ($status !== 'TERISI') {
            $data['pasar_unit_penyewa'] = null;
            $data['pasar_unit_tanggal_sewa'] = null;
            $data['pasar_unit_tanggal_jatuh_tempo'] = null;
        }
        
        $this->db->where_in('pasar_unit_id', $ids);
        return $this->db->update($this->table, $data);
    }

    // Generate multiple units
    public function generate_units($blok_id, $start_nomor, $end_nomor, $lebar = 0, $panjang = 0, $harga = 0)
    {
        $units = [];
        for ($i = $start_nomor; $i <= $end_nomor; $i++) {
            // Check if unit already exists
            if (!$this->check_unit_nomor_exists($blok_id, $i)) {
                $units[] = [
                    'pasar_blok_id' => $blok_id,
                    'pasar_unit_nomor' => $i,
                    'pasar_unit_lebar' => $lebar,
                    'pasar_unit_panjang' => $panjang,
                    'pasar_unit_luas' => $lebar * $panjang,
                    'pasar_unit_harga_sewa' => $harga,
                    'pasar_unit_status' => 'TERSEDIA',
                    'pasar_unit_created_at' => date('Y-m-d H:i:s'),
                    'pasar_unit_updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        if (!empty($units)) {
            return $this->db->insert_batch($this->table, $units);
        }
        
        return true;
    }
}