<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp/wa_template_m');
    }

    /**
     * Menambahkan template pengingat SPP dengan emoji
     */
    public function add_pengingat_template()
    {
        // Isi template dengan emoji
        $template_content = <<<EOT
Dear {nama_ortu}

Mohon maaf kami hanya ingin mengingatkan untuk monthly fee ananda {nama_siswa} bulan {bulan} ini blm ya mama, mungkin kelupaan 😊🙏
Mengingat hari ini sudah lewat tanggal 15

Biaya private/monthly fee dapat dilakukan melalui rek :
Mandiri, Inda Ariestiya, 9000015204010
Atau
BCA, Ali Wardana, 0570974505

Mohon konfirmasi dengan mengirimkan bukti transfer jika sudah membayar
Terima kasih banyak atas pengertiannya. 
{nama_perusahaan} 🙏
EOT;

        // Data template
        $data = [
            'nama' => 'Pengingat Pembayaran SPP (dengan Emoji)',
            'kode' => 'pengingat_spp_emoji',
            'isi_template' => $template_content,
            'deskripsi' => 'Template untuk pengingat pembayaran bulanan dengan emoji',
            'created_by' => 1 // admin
        ];

        // Set koneksi database untuk mendukung emoji
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET CHARACTER SET utf8mb4");

        // Cek apakah template dengan kode tersebut sudah ada
        $existing = $this->db->get_where('wa_template', ['kode' => $data['kode']])->row();

        if ($existing) {
            // Update template yang ada
            $this->db->where('id', $existing->id);
            $result = $this->db->update('wa_template', [
                'nama' => $data['nama'],
                'isi_template' => $data['isi_template'],
                'deskripsi' => $data['deskripsi'],
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $data['created_by']
            ]);
            
            if ($result) {
                echo "Template berhasil diperbarui";
            } else {
                echo "Gagal memperbarui template";
            }
        } else {
            // Simpan template baru
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $result = $this->db->insert('wa_template', $data);
            
            if ($result) {
                echo "Template berhasil ditambahkan";
            } else {
                echo "Gagal menambahkan template";
            }
        }
    }
    
    /**
     * Contoh penggunaan template untuk pembayaran
     */
    public function sample_pengingat()
    {
        // Load model yang dibutuhkan
        $this->load->model('whatsapp/wa_template_m');
        $this->load->model('pengaturan/pengaturan_m');
        
        // Dapatkan data perusahaan
        $id_perusahaan = $this->session->userdata('id_perusahaan');
        $perusahaan = $this->pengaturan_m->perusahaan_by_id($id_perusahaan);
        
        // Parameter contoh
        $params = [
            'nama_ortu' => 'Bpk/Ibu Ahmad',
            'nama_siswa' => 'Budi',
            'bulan' => 'Juni 2024',
            'nama_perusahaan' => $perusahaan ? $perusahaan->nama_perusahaan : 'Little Star Learning Centre'
        ];
        
        // Dapatkan template dan proses
        $pesan = $this->wa_template_m->proses_template('pengingat_spp_emoji', $params);
        
        if ($pesan) {
            // Tampilkan contoh pesan
            echo "<h3>Contoh Pesan Pengingat Pembayaran</h3>";
            echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 15px; font-family: Arial;'>";
            echo $pesan;
            echo "</div>";
        } else {
            echo "Template tidak ditemukan. Silakan tambahkan template terlebih dahulu.";
        }
    }

    /**
     * Menambahkan template pembayaran gedung
     */
    public function add_payment_templates()
    {
        // Load helper untuk menambahkan template
        $this->load->helper('whatsapp/add_payment_templates');
    }

    /**
     * Contoh penggunaan template pembayaran
     */
    public function sample_payment_notification()
    {
        // Load model yang dibutuhkan
        $this->load->model('whatsapp/wa_template_m');
        $this->load->model('pengaturan/pengaturan_m');

        // Dapatkan data perusahaan
        $id_perusahaan = $this->session->userdata('id_perusahaan');
        $perusahaan = $this->pengaturan_m->perusahaan_by_id($id_perusahaan);

        // Parameter contoh untuk DP
        $params_dp = [
            'nama_pic' => 'Bpk/Ibu Ahmad',
            'nama_acara' => 'Rapat Koordinasi Tahunan',
            'gedung_nama' => 'Aula Utama',
            'tanggal_acara' => '25 Juli 2025',
            'jam_acara' => '08:00 - 12:00',
            'kode_pembayaran' => 'DP-20250713-001',
            'nominal_pembayaran' => '1.000.000',
            'tanggal_pembayaran' => '13 Juli 2025',
            'sisa_tagihan' => '1.500.000',
            'nama_perusahaan' => $perusahaan ? $perusahaan->nama_perusahaan : 'Booking System'
        ];

        // Proses template DP
        $pesan_dp = $this->wa_template_m->proses_template('konfirmasi_dp_gedung', $params_dp);

        echo "<h3>Contoh Template Konfirmasi DP:</h3>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 15px; font-family: Arial;'>";
        echo $pesan_dp;
        echo "</div>";

        // Parameter contoh untuk pelunasan
        $params_pelunasan = [
            'nama_pic' => 'Bpk/Ibu Ahmad',
            'nama_acara' => 'Rapat Koordinasi Tahunan',
            'gedung_nama' => 'Aula Utama',
            'tanggal_acara' => '25 Juli 2025',
            'jam_acara' => '08:00 - 12:00',
            'kode_pembayaran' => 'PLN-20250713-001',
            'nominal_pembayaran' => '1.500.000',
            'tanggal_pembayaran' => '20 Juli 2025',
            'nama_perusahaan' => $perusahaan ? $perusahaan->nama_perusahaan : 'Booking System'
        ];

        // Proses template pelunasan
        $pesan_pelunasan = $this->wa_template_m->proses_template('konfirmasi_pelunasan_gedung', $params_pelunasan);

        echo "<h3>Contoh Template Konfirmasi Pelunasan:</h3>";
        echo "<div style='white-space: pre-wrap; border: 1px solid #ccc; padding: 15px; font-family: Arial;'>";
        echo $pesan_pelunasan;
        echo "</div>";
    }
}