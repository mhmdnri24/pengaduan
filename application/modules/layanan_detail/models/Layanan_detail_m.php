<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Layanan_detail_m extends CI_Model
{
    public function layanan_get_all()
    {
        return $this->db->get('layanan_jenis')->result();
    }

    public function layanan_by_id($id)
    {
        return $this->db->where('layanan_id', $id)->get('layanan_jenis')->row();
    }

    public function layanan_by_id_array($id)
    {
        return $this->db->where('layanan_id', $id)->get('layanan_jenis')->result();
    }

    public function layanan_insert_data($post_data)
    {
        return $this->db->insert('layanan_jenis', $post_data);
    }

    public function layanan_update_data($post_data, $id)
    {
        return $this->db->where('layanan_id', $id)->update('layanan_jenis', $post_data);
    }

    public function layanan_delete_data($id)
    {
        return $this->db->where('layanan_id', $id)->delete('layanan_jenis');
    }

    public function get_distinct_kategori()
    {
        return $this->db->select('layanan_kategori')->distinct()->get('layanan_jenis')->result();
    }

    public function layanan_by_kategori($kategori)
    {
        return $this->db->where('layanan_kategori', $kategori)->get('layanan_jenis')->result();
    }

    public function layanan_aktif()
    {
        return $this->db->where('layanan_status', 1)->get('layanan_jenis')->result();
    }

    public function count_layanan_by_kategori($kategori = null)
    {
        if ($kategori) {
            $this->db->where('layanan_kategori', $kategori);
        }
        return $this->db->count_all_results('layanan_jenis');
    }

    public function get_layanan_with_pagination($limit, $offset, $kategori = null, $status = null)
    {
        if ($kategori) {
            $this->db->where('layanan_kategori', $kategori);
        }

        if ($status !== null) {
            $this->db->where('layanan_status', $status);
        }

        $this->db->limit($limit, $offset);
        $this->db->order_by('layanan_urutan', 'ASC');
        $this->db->order_by('layanan_nama', 'ASC');

        return $this->db->get('layanan_jenis')->result();
    }

    public function search_layanan($keyword)
    {
        $this->db->like('layanan_nama', $keyword);
        $this->db->or_like('layanan_deskripsi', $keyword);
        $this->db->or_like('layanan_kategori', $keyword);

        return $this->db->get('layanan_jenis')->result();
    }

    public function get_layanan_statistik()
    {
        $stats = array();

        // Total layanan
        $stats['total'] = $this->db->count_all('layanan_jenis');

        // Layanan aktif
        $stats['aktif'] = $this->db->where('layanan_status', 1)->count_all_results('layanan_jenis');

        // Layanan tidak aktif
        $stats['tidak_aktif'] = $this->db->where('layanan_status', 0)->count_all_results('layanan_jenis');

        // Layanan per kategori
        $this->db->select('layanan_kategori, COUNT(*) as jumlah');
        $this->db->group_by('layanan_kategori');
        $kategori_stats = $this->db->get('layanan_jenis')->result();

        $stats['per_kategori'] = array();
        foreach ($kategori_stats as $item) {
            $stats['per_kategori'][$item->layanan_kategori] = $item->jumlah;
        }

        return $stats;
    }

    public function get_layanan_teratas($limit = 5)
    {
        // Catatan: kolom biaya dihapus. Gunakan urutan tampilan sebagai fallback.
        $this->db->order_by('layanan_urutan', 'ASC');
        $this->db->limit($limit);

        return $this->db->get('layanan_jenis')->result();
    }

    // Backward compatibility: method lama tetap ada namun menyesuaikan
    public function get_layanan_termahal($limit = 5)
    {
        return $this->get_layanan_teratas($limit);
    }


    public function get_layanan_terlama($limit = 5)
    {
        $this->db->order_by('layanan_durasi', 'DESC');
        $this->db->limit($limit);

        return $this->db->get('layanan_jenis')->result();
    }

    public function duplicate_check($nama, $id = null)
    {
        $this->db->where('layanan_nama', $nama);
        if ($id) {
            $this->db->where('layanan_id !=', $id);
        }

        return $this->db->get('layanan_jenis')->num_rows() > 0;
    }

    public function update_status_batch($ids, $status)
    {
        $this->db->where_in('layanan_id', $ids);
        return $this->db->update('layanan_jenis', array('layanan_status' => $status));
    }

    public function delete_batch($ids)
    {
        $this->db->where_in('layanan_id', $ids);
        return $this->db->delete('layanan_jenis');
    }

    public function get_layanan_export()
    {
        $this->db->order_by('layanan_kategori', 'ASC');
        $this->db->order_by('layanan_nama', 'ASC');

        return $this->db->get('layanan_jenis')->result();
    }
}
