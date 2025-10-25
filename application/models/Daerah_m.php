<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Daerah_m extends CI_Model
{
    public function get_provinsi_lists()
    {
        return $this->db->get('provinsi')->result();
    }

    public function get_kota_lists($prov_id)
    {
        return $this->db->get_where('kota', ['prov_id' => $prov_id])->result();
    }

    public function get_kecamatan_lists($kota_id)
    {
        return $this->db->get_where('kecamatan', ['id_kota' => $kota_id])->result();
    }

    public function get_kelurahan_lists($kec_id)
    {
        return $this->db->get_where('kelurahan', ['id_kecamatan' => $kec_id])->result();
    }

    // Method untuk JSON endpoint - get kecamatan by kota dengan field yang konsisten
    public function get_kecamatan_by_kota($id_kota)
    {
        $this->db->select('id_kecamatan, nama_kecamatan');
        $this->db->from('kecamatan');
        $this->db->where('id_kota', $id_kota);
        $this->db->order_by('nama_kecamatan', 'asc');
        return $this->db->get()->result();
    }

    // Method untuk JSON endpoint - get kelurahan by kecamatan dengan field yang konsisten
    public function get_kelurahan_by_kecamatan($id_kecamatan)
    {
        $this->db->select('id_kelurahan, nama_kelurahan');
        $this->db->from('kelurahan');
        $this->db->where('id_kecamatan', $id_kecamatan);
        $this->db->order_by('nama_kelurahan', 'asc');
        return $this->db->get()->result();
    }
}