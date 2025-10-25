<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Beranda_m extends CI_Model
{
    /**
     * Hitung total pendaftar wisuda (semua status)
     */
    public function count_total_pendaftar()
    {
        return $this->db->count_all('registrasi_wisuda_mahasiswa');
    }

    /**
     * Hitung pendaftar berdasarkan status APPROVED
     */
    public function count_pendaftar_approved()
    {
        $this->db->where('status', 'APPROVED');
        return $this->db->count_all_results('registrasi_wisuda_mahasiswa');
    }

    /**
     * Hitung pendaftar berdasarkan status SUBMITTED
     */
    public function count_pendaftar_submitted()
    {
        $this->db->where('status', 'SUBMITTED');
        return $this->db->count_all_results('registrasi_wisuda_mahasiswa');
    }

    /**
     * Hitung pendaftar berdasarkan status DRAFT
     */
    public function count_pendaftar_draft()
    {
        $this->db->where('status', 'DRAFT');
        return $this->db->count_all_results('registrasi_wisuda_mahasiswa');
    }

    /**
     * Hitung pendaftar berdasarkan status REJECTED
     */
    public function count_pendaftar_rejected()
    {
        $this->db->where('status', 'REJECTED');
        return $this->db->count_all_results('registrasi_wisuda_mahasiswa');
    }

    /**
     * Hitung pendaftar berdasarkan status CANCELLED
     */
    public function count_pendaftar_cancelled()
    {
        $this->db->where('status', 'CANCELLED');
        return $this->db->count_all_results('registrasi_wisuda_mahasiswa');
    }

    /**
     * Ambil statistik pendaftar per bulan untuk grafik
     */
    public function get_pendaftar_per_bulan()
    {
        $tahun_ini = date('Y');

        $this->db->select('MONTH(created_at) as bulan, COUNT(id) as jumlah_pendaftar');
        $this->db->from('registrasi_wisuda_mahasiswa');
        $this->db->where('YEAR(created_at)', $tahun_ini);
        $this->db->group_by('MONTH(created_at)');
        $this->db->order_by('MONTH(created_at)', 'ASC');

        $result = $this->db->get()->result();

        // Hitung rata-rata
        $total_pendaftar = 0;
        $jumlah_bulan = count($result);

        foreach ($result as $row) {
            $total_pendaftar += $row->jumlah_pendaftar;
        }

        $rata_rata = ($jumlah_bulan > 0) ? ($total_pendaftar / $jumlah_bulan) : 0;

        return [
            'data_per_bulan' => $result,
            'rata_rata' => $rata_rata
        ];
    }

    /**
     * Ambil jadwal wisuda terbaru
     */
    public function get_jadwal_wisuda_terbaru($limit = 10)
    {
        // Gunakan query manual untuk mengatasi collation mismatch
        $sql = "SELECT jw.*, kw.nama_kelompok, kw.kode_kelompok
                FROM jadwal_wisuda jw
                LEFT JOIN kelompok_wisuda kw ON CAST(jw.kelompok_wisuda_id AS CHAR) = CAST(kw.id AS CHAR)
                WHERE jw.status = 1
                ORDER BY jw.tanggal_wisuda ASC
                LIMIT ?";

        return $this->db->query($sql, [$limit])->result();
    }

    /**
     * Ambil pendaftar wisuda terbaru
     */
    public function get_pendaftar_terbaru($limit = 10)
    {
        // Gunakan query manual untuk mengatasi collation mismatch
        $sql = "SELECT rwm.*, rm.nim, rm.nama_lengkap, rm.no_whatsapp, jw.nama_periode, kw.nama_kelompok
                FROM registrasi_wisuda_mahasiswa rwm
                LEFT JOIN registrasi_mahasiswa rm ON CAST(rwm.mahasiswa_id AS CHAR) = CAST(rm.id AS CHAR)
                LEFT JOIN jadwal_wisuda jw ON CAST(rwm.jadwal_wisuda_id AS CHAR) = CAST(jw.id AS CHAR)
                LEFT JOIN kelompok_wisuda kw ON CAST(jw.kelompok_wisuda_id AS CHAR) = CAST(kw.id AS CHAR)
                ORDER BY rwm.created_at DESC
                LIMIT ?";

        return $this->db->query($sql, [$limit])->result();
    }

    /**
     * Ambil statistik pendaftar berdasarkan kelompok wisuda
     */
    public function get_statistik_per_kelompok()
    {
        // Gunakan query manual untuk mengatasi collation mismatch
        $sql = "SELECT
                    kw.nama_kelompok,
                    kw.kode_kelompok,
                    COUNT(rwm.id) as total_pendaftar,
                    SUM(CASE WHEN rwm.status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN rwm.status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
                    SUM(CASE WHEN rwm.status = 'DRAFT' THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN rwm.status = 'REJECTED' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN rwm.status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled
                FROM kelompok_wisuda kw
                LEFT JOIN jadwal_wisuda jw ON CAST(kw.id AS CHAR) = CAST(jw.kelompok_wisuda_id AS CHAR)
                LEFT JOIN registrasi_wisuda_mahasiswa rwm ON CAST(jw.id AS CHAR) = CAST(rwm.jadwal_wisuda_id AS CHAR)
                WHERE kw.status = 1
                GROUP BY kw.id, kw.nama_kelompok, kw.kode_kelompok
                ORDER BY total_pendaftar DESC";

        return $this->db->query($sql)->result();
    }

    /**
     * Ambil login terakhir untuk dashboard
     */
    public function get_recent_logins($limit = 5)
    {
        // Gunakan query manual untuk mengatasi kemungkinan collation mismatch
        $sql = "SELECT ll.*, u.nama, u.username
                FROM log_login ll
                LEFT JOIN user u ON CAST(ll.id_user AS CHAR) = CAST(u.id_user AS CHAR)
                WHERE ll.status = 'success'
                ORDER BY ll.attempt_time DESC
                LIMIT ?";

        return $this->db->query($sql, [$limit])->result();
    }

    /**
     * Ambil jadwal wisuda mendatang untuk kalender
     */
    public function get_jadwal_wisuda_calendar($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?: date('m');
        $tahun = $tahun ?: date('Y');

        // Gunakan query manual untuk mengatasi collation mismatch
        $sql = "SELECT jw.*, kw.nama_kelompok, kw.kode_kelompok
                FROM jadwal_wisuda jw
                LEFT JOIN kelompok_wisuda kw ON CAST(jw.kelompok_wisuda_id AS CHAR) = CAST(kw.id AS CHAR)
                WHERE jw.status = 1
                AND MONTH(jw.tanggal_wisuda) = ?
                AND YEAR(jw.tanggal_wisuda) = ?
                ORDER BY jw.tanggal_wisuda ASC";

        $jadwal_list = $this->db->query($sql, [$bulan, $tahun])->result();

        // Group by date
        $jadwal_by_date = [];
        foreach ($jadwal_list as $jadwal) {
            $date_key = date('Y-m-d', strtotime($jadwal->tanggal_wisuda));
            if (!isset($jadwal_by_date[$date_key])) {
                $jadwal_by_date[$date_key] = [];
            }
            $jadwal_by_date[$date_key][] = $jadwal;
        }

        return $jadwal_by_date;
    }

    /**
     * Ambil ringkasan jadwal wisuda untuk dashboard
     */
    public function get_jadwal_wisuda_summary()
    {
        $today = date('Y-m-d');

        // Jadwal yang akan datang
        $this->db->select('COUNT(*) as total_mendatang');
        $this->db->from('jadwal_wisuda');
        $this->db->where('status', 1);
        $this->db->where('tanggal_wisuda >=', $today);
        $mendatang = $this->db->get()->row()->total_mendatang;

        // Jadwal yang sedang buka pendaftaran
        $this->db->select('COUNT(*) as total_buka');
        $this->db->from('jadwal_wisuda');
        $this->db->where('status', 1);
        $this->db->where('tanggal_buka_daftar <=', $today);
        $this->db->where('tanggal_tutup_daftar >=', $today);
        $buka_pendaftaran = $this->db->get()->row()->total_buka;

        return [
            'total_mendatang' => $mendatang,
            'buka_pendaftaran' => $buka_pendaftaran
        ];
    }

    /**
     * Ambil data pelaporan untuk peta
     */
    public function get_pelaporan_for_maps($id_kota = '16.73', $id_kecamatan = null, $id_kelurahan = null)
    {
        $this->db->select('p.id, p.kode_laporan, p.judul, p.alamat, p.lokasi_lat, p.lokasi_lng, p.status, p.prioritas, p.kategori, p.created_at, m.nama_lengkap as nama_pelapor');
        $this->db->from('pelaporan p');
        $this->db->join('masyarakat m', 'p.masyarakat_id = m.id', 'left');
        $this->db->join('kecamatan k', 'm.id_kecamatan = k.id_kecamatan', 'left');
        $this->db->where('p.lokasi_lat IS NOT NULL');
        $this->db->where('p.lokasi_lng IS NOT NULL');
        $this->db->where('p.lokasi_lat !=', '');
        $this->db->where('p.lokasi_lng !=', '');

        // Filter berdasarkan lokasi
        if (!empty($id_kota) && $id_kota !== 'all') {
            $this->db->where('(k.id_kota = ' . $this->db->escape($id_kota) . ' OR m.id_kecamatan IS NULL)', null, false);
        }
        if (!empty($id_kecamatan)) {
            $this->db->where('m.id_kecamatan', $id_kecamatan);
        }
        if (!empty($id_kelurahan)) {
            $this->db->where('m.id_kelurahan', $id_kelurahan);
        }

        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit(100); // Batasi untuk performa

        return $this->db->get()->result();
    }

    /**
     * Ambil statistik pelaporan untuk dashboard
     */
    public function get_statistik_pelaporan()
    {
        // Total pelaporan
        $total_pelaporan = $this->db->count_all('pelaporan');

        // Berdasarkan status
        $this->db->where('status', 'BARU');
        $status_baru = $this->db->count_all_results('pelaporan');

        $this->db->where('status', 'PROSES');
        $status_proses = $this->db->count_all_results('pelaporan');

        $this->db->where('status', 'SELESAI');
        $status_selesai = $this->db->count_all_results('pelaporan');

        // Berdasarkan prioritas
        $this->db->where('prioritas', 'TINGGI');
        $prioritas_tinggi = $this->db->count_all_results('pelaporan');

        $this->db->where('prioritas', 'SEDANG');
        $prioritas_sedang = $this->db->count_all_results('pelaporan');

        $this->db->where('prioritas', 'RENDAH');
        $prioritas_rendah = $this->db->count_all_results('pelaporan');

        // Pelaporan bulan ini
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $bulan_ini = $this->db->count_all_results('pelaporan');

        return [
            'total_pelaporan' => $total_pelaporan,
            'status_baru' => $status_baru,
            'status_proses' => $status_proses,
            'status_selesai' => $status_selesai,
            'prioritas_tinggi' => $prioritas_tinggi,
            'prioritas_sedang' => $prioritas_sedang,
            'prioritas_rendah' => $prioritas_rendah,
            'bulan_ini' => $bulan_ini
        ];
    }
}