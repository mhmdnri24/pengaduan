<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retribusi_target_m extends CI_Model
{
    private $table = 'retribusi_target';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('master_retribusi/Master_retribusi_m');
    }

    public function target_get_all()
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');
        $this->db->order_by('retribusi_target.tahun', 'desc');
        $this->db->order_by('retribusi_target.bulan', 'asc');
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function target_by_id($id)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');
        $this->db->where('retribusi_target.id', $id);
        return $this->db->get()->row();
    }

    public function target_by_retribusi_tahun($id_retribusi, $tahun, $bulan = null)
    {
        $this->db->where('id_retribusi', $id_retribusi);
        $this->db->where('tahun', $tahun);
        
        if ($bulan !== null) {
            $this->db->where('bulan', $bulan);
        } else {
            $this->db->where('bulan IS NULL');
        }
        
        return $this->db->get($this->table)->row();
    }

    public function target_by_tahun($tahun)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');
        $this->db->where('retribusi_target.tahun', $tahun);
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function target_tahunan_by_tahun($tahun)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');
        $this->db->where('retribusi_target.tahun', $tahun);
        $this->db->where('retribusi_target.bulan IS NULL');
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function target_bulanan_by_tahun($tahun)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');
        $this->db->where('retribusi_target.tahun', $tahun);
        $this->db->where('retribusi_target.bulan IS NOT NULL');
        $this->db->order_by('retribusi_target.bulan', 'asc');
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');
        return $this->db->get()->result();
    }

    public function target_insert_data($post_data)
    {
        // Hitung persentase jika capaian dan target diisi
        if (isset($post_data['target']) && isset($post_data['capaian']) && $post_data['target'] > 0) {
            $post_data['persentase'] = ($post_data['capaian'] / $post_data['target']) * 100;
        }

        // Set created_by dari session user
        if ($this->session->user_login) {
            $post_data['created_by'] = $this->session->user_login['id'];
        }

        return $this->db->insert($this->table, $post_data);
    }

    public function target_update_data($post_data, $id)
    {
        // Hitung persentase jika capaian dan target diisi
        if (isset($post_data['target']) && isset($post_data['capaian']) && $post_data['target'] > 0) {
            $post_data['persentase'] = ($post_data['capaian'] / $post_data['target']) * 100;
        }

        // Set updated_by dari session user
        if ($this->session->user_login) {
            $post_data['updated_by'] = $this->session->user_login['id'];
        }

        return $this->db->where('id', $id)->update($this->table, $post_data);
    }

    public function target_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function get_retribusi_list()
    {
        return $this->Master_retribusi_m->retribusi_get_active();
    }

    public function get_tahun_list()
    {
        $this->db->select('DISTINCT(tahun) as tahun');
        $this->db->from($this->table);
        $this->db->order_by('tahun', 'desc');
        return $this->db->get()->result();
    }

    public function get_bulan_list()
    {
        return [
            (object)['bulan' => 'Januari'],
            (object)['bulan' => 'Februari'],
            (object)['bulan' => 'Maret'],
            (object)['bulan' => 'April'],
            (object)['bulan' => 'Mei'],
            (object)['bulan' => 'Juni'],
            (object)['bulan' => 'Juli'],
            (object)['bulan' => 'Agustus'],
            (object)['bulan' => 'September'],
            (object)['bulan' => 'Oktober'],
            (object)['bulan' => 'November'],
            (object)['bulan' => 'Desember']
        ];
    }

    public function search_target($keyword)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');

        if ($keyword) {
            $this->db->group_start();
            $this->db->like('master_retribusi.nama_retribusi', $keyword);
            $this->db->or_like('master_retribusi.kode_retribusi', $keyword);
            $this->db->or_like('master_retribusi.jenis_retribusi', $keyword);
            $this->db->or_like('retribusi_target.tahun', $keyword);
            $this->db->or_like('retribusi_target.bulan', $keyword);
            $this->db->or_like('retribusi_target.keterangan', $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('retribusi_target.tahun', 'desc');
        $this->db->order_by('retribusi_target.bulan', 'asc');
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');
        $this->db->limit(20);
        return $this->db->get()->result();
    }

    // ========== STATISTIK METHODS ==========

    public function count_all_target()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_by_tahun($tahun)
    {
        return $this->db->where('tahun', $tahun)->count_all_results($this->table);
    }

    public function count_target_tahunan()
    {
        return $this->db->where('bulan IS NULL')->count_all_results($this->table);
    }

    public function count_target_bulanan()
    {
        return $this->db->where('bulan IS NOT NULL')->count_all_results($this->table);
    }

    public function get_summary_by_tahun($tahun)
    {
        $this->db->select('
            SUM(target) as total_target,
            SUM(capaian) as total_capaian,
            AVG(persentase) as avg_persentase,
            COUNT(id) as total_records
        ');
        $this->db->from($this->table);
        $this->db->where('tahun', $tahun);
        return $this->db->get()->row();
    }

    public function get_summary_by_retribusi($id_retribusi, $tahun)
    {
        $this->db->select('
            SUM(target) as total_target,
            SUM(capaian) as total_capaian,
            AVG(persentase) as avg_persentase,
            COUNT(id) as total_records
        ');
        $this->db->from($this->table);
        $this->db->where('id_retribusi', $id_retribusi);
        $this->db->where('tahun', $tahun);
        return $this->db->get()->row();
    }

    // ========== VALIDATION METHODS ==========

    public function validate_target_data($data, $id = null)
    {
        $errors = [];

        // Validasi id_retribusi
        if (empty($data['id_retribusi'])) {
            $errors[] = 'Jenis retribusi harus dipilih';
        } else {
            // Check if retribusi exists
            $retribusi = $this->Master_retribusi_m->retribusi_by_id($data['id_retribusi']);
            if (!$retribusi) {
                $errors[] = 'Jenis retribusi tidak valid';
            }
        }

        // Validasi tahun
        if (empty($data['tahun'])) {
            $errors[] = 'Tahun harus diisi';
        } elseif (!is_numeric($data['tahun']) || $data['tahun'] < 2000 || $data['tahun'] > 2100) {
            $errors[] = 'Tahun tidak valid';
        }

        // Validasi target
        if (!is_numeric($data['target']) || $data['target'] < 0) {
            $errors[] = 'Target harus berupa angka dan tidak boleh negatif';
        }

        // Validasi capaian
        if (!is_numeric($data['capaian']) || $data['capaian'] < 0) {
            $errors[] = 'Capaian harus berupa angka dan tidak boleh negatif';
        }

        // Check duplicate data
        if (empty($id)) {
            // For insert
            $existing = $this->target_by_retribusi_tahun($data['id_retribusi'], $data['tahun'], $data['bulan'] ?? null);
            if ($existing) {
                $type = empty($data['bulan']) ? 'tahunan' : 'bulanan (' . $data['bulan'] . ')';
                $errors[] = 'Target ' . $type . ' untuk retribusi ini sudah ada';
            }
        } else {
            // For update
            $existing = $this->db->where('id_retribusi', $data['id_retribusi'])
                                ->where('tahun', $data['tahun'])
                                ->where('bulan', $data['bulan'] ?? null)
                                ->where('id !=', $id)
                                ->get($this->table)->row();
            if ($existing) {
                $type = empty($data['bulan']) ? 'tahunan' : 'bulanan (' . $data['bulan'] . ')';
                $errors[] = 'Target ' . $type . ' untuk retribusi ini sudah ada';
            }
        }

        return $errors;
    }

    // ========== EXPORT METHODS ==========

    public function get_target_for_export($tahun = null, $id_retribusi = null)
    {
        $this->db->select('retribusi_target.*, master_retribusi.nama_retribusi, master_retribusi.kode_retribusi, master_retribusi.jenis_retribusi');
        $this->db->from($this->table);
        $this->db->join('master_retribusi', 'master_retribusi.id = retribusi_target.id_retribusi', 'left');

        if ($tahun) {
            $this->db->where('retribusi_target.tahun', $tahun);
        }

        if ($id_retribusi) {
            $this->db->where('retribusi_target.id_retribusi', $id_retribusi);
        }

        $this->db->order_by('retribusi_target.tahun', 'desc');
        $this->db->order_by('retribusi_target.bulan', 'asc');
        $this->db->order_by('master_retribusi.nama_retribusi', 'asc');

        return $this->db->get()->result();
    }
}