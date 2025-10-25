<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_kategori_m extends CI_Model
{
    private $table = 'master_kategori';

    public function kategori_get_all()
    {
        return $this->db->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_active()
    {
        return $this->db->where('status', 1)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_parent()
    {
        return $this->db->where('parent_id IS NULL', null, false)->where('status', 1)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_get_children($parent_id)
    {
        return $this->db->where('parent_id', $parent_id)->order_by('nama_kategori', 'asc')->get($this->table)->result();
    }
    
    public function kategori_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function kategori_by_kode($kode_kategori)
    {
        return $this->db->where('kode_kategori', $kode_kategori)->get($this->table)->row();
    }
    
    public function count_all_kategori()
    {
        return $this->db->count_all($this->table);
    }
    
    public function count_active_kategori()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }
    
    /**
     * Get kategori fasilitas umum untuk frontend
     */
    public function get_fasilitas_umum_kategori()
    {
        // Filter kategori yang berhubungan dengan fasilitas umum
        $this->db->where('status', 1);
        $this->db->where('(nama_kategori LIKE "%fasilitas%" OR nama_kategori LIKE "%umum%" OR nama_kategori LIKE "%publik%" OR deskripsi LIKE "%fasilitas%" OR deskripsi LIKE "%umum%" OR deskripsi LIKE "%publik%")', NULL, FALSE);
        $this->db->order_by('nama_kategori', 'asc');
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get kategori dengan ikon untuk frontend
     */
    public function get_kategori_with_icons()
    {
        $kategori = $this->kategori_get_active();
        
        // Tambahkan ikon berdasarkan nama kategori
        foreach ($kategori as $k) {
            $k->icon = $this->get_kategori_icon($k->nama_kategori);
        }
        
        return $kategori;
    }
    
    /**
     * Get ikon berdasarkan nama kategori
     */
    private function get_kategori_icon($nama_kategori)
    {
        $nama_lower = strtolower($nama_kategori);
        
        // Mapping kategori ke ikon
        $icon_map = [
            'kesehatan' => 'heart-pulse',
            'pendidikan' => 'graduation-cap',
            'transportasi' => 'car',
            'pasar' => 'shopping-cart',
            'ibadah' => 'mosque',
            'olahraga' => 'trophy',
            'sosial' => 'users',
            'keamanan' => 'shield',
            'lingkungan' => 'tree-pine',
            'wisata' => 'map-pin',
            'kuliner' => 'utensils',
            'hiburan' => 'music',
            'bank' => 'building',
            'kantor' => 'building-2',
            'fasilitas' => 'home',
            'umum' => 'square-parking'
        ];
        
        // Cek keyword dalam nama kategori
        foreach ($icon_map as $keyword => $icon) {
            if (strpos($nama_lower, $keyword) !== false) {
                return $icon;
            }
        }
        
        // Default icon
        return 'folder';
    }
}