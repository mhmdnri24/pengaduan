<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fasilitas_umum_m extends CI_Model
{
    private $table = 'fasilitas_umum';

    public function fasilitas_umum_get_all()
    {
        return $this->db->order_by('id', 'desc')->get($this->table)->result();
    }
    
    public function fasilitas_umum_get_active()
    {
        return $this->db->where('status', 1)->order_by('nama_fasilitas', 'asc')->get($this->table)->result();
    }
    
    public function fasilitas_umum_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function count_all_fasilitas()
    {
        return $this->db->count_all($this->table);
    }

    public function count_active_fasilitas()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }

    public function count_inactive_fasilitas()
    {
        return $this->db->where('status', 0)->count_all_results($this->table);
    }

    public function count_fasilitas_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_id', $kategori_id)->count_all_results($this->table);
    }

    public function fasilitas_umum_by_kategori($kategori_id)
    {
        return $this->db->where('kategori_id', $kategori_id)
            ->where('status', 1)
            ->order_by('nama_fasilitas', 'asc')
            ->get($this->table)
            ->result();
    }

    /**
     * Get fasilitas with complete location info for frontend
     */
    public function get_fasilitas_with_location($id)
    {
        $this->db->select('fu.*, mk.nama_kategori, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan');
        $this->db->from('fasilitas_umum fu');
        $this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
        $this->db->join('kota kt', 'fu.id_kota = kt.id_kota', 'left');
        $this->db->join('kecamatan kc', 'fu.id_kecamatan = kc.id_kecamatan', 'left');
        $this->db->join('kelurahan kl', 'fu.id_kelurahan = kl.id_kelurahan', 'left');
        $this->db->where('fu.id', $id);
        $this->db->where('fu.status', 1);
        return $this->db->get()->row();
    }

    /**
     * Get fasilitas with location info by kategori for frontend
     */
    public function get_fasilitas_by_kategori_with_location($kategori_id)
    {
        $this->db->select('fu.*, mk.nama_kategori, kt.nama_kota, kc.nama_kecamatan, kl.nama_kelurahan');
        $this->db->from('fasilitas_umum fu');
        $this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
        $this->db->join('kota kt', 'fu.id_kota = kt.id_kota', 'left');
        $this->db->join('kecamatan kc', 'fu.id_kecamatan = kc.id_kecamatan', 'left');
        $this->db->join('kelurahan kl', 'fu.id_kelurahan = kl.id_kelurahan', 'left');
        $this->db->where('fu.kategori_id', $kategori_id);
        $this->db->where('fu.status', 1);
        $this->db->order_by('fu.nama_fasilitas', 'asc');
        return $this->db->get()->result();
    }

    /**
     * Get photos for a facility
     */
    public function get_fotos_by_fasilitas($fasilitas_id, $status = 1)
    {
        $this->db->where('fasilitas_id', $fasilitas_id);
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('urutan', 'asc');
        $this->db->order_by('created_at', 'asc');
        return $this->db->get('fasilitas_umum_foto')->result();
    }

    /**
     * Get fasilitas statistics by kategori for frontend
     */
    public function get_fasilitas_stats_by_kategori($kategori_id)
    {
        $this->db->select('COUNT(*) as total, 
                           SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as aktif,
                           SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as nonaktif');
        $this->db->where('kategori_id', $kategori_id);
        return $this->db->get($this->table)->row();
    }
}