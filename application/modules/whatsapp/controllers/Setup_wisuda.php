<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup_wisuda extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsapp/wa_template_m');
    }

    /**
     * Setup template WhatsApp untuk registrasi wisuda
     */
    public function install_templates()
    {
        // Set koneksi database untuk mendukung emoji
        $this->db->query("SET NAMES utf8mb4");
        $this->db->query("SET CHARACTER SET utf8mb4");

        // Template 1: Registrasi Wisuda Disetujui
        $template_approved = <<<EOT
🎉 *SELAMAT {nama_lengkap}!* 🎉

✅ Registrasi wisuda Anda telah *DISETUJUI* dan diverifikasi oleh admin.

📋 *Detail Registrasi Wisuda:*
• NIM: {nim}
• Nama: {nama_lengkap}
• Periode: {nama_periode}
• Tanggal Wisuda: {tanggal_wisuda}
• Tempat: {tempat_wisuda}
• Biaya: Rp {biaya_wisuda}

🎓 *Status: APPROVED* ✅

📝 *Langkah Selanjutnya:*
• Login ke sistem untuk mencetak kartu pendaftaran
• Siapkan dokumen yang diperlukan saat hari wisuda
• Pantau informasi lebih lanjut melalui sistem

🔗 *Link Sistem:* {link_sistem}

Selamat! Anda telah berhasil terdaftar untuk wisuda. 

Terima kasih atas kesabaran Anda.

Salam,
{nama_institusi} 🎓
EOT;

        // Template 2: Registrasi Wisuda Ditolak
        $template_rejected = <<<EOT
❌ *PEMBERITAHUAN REGISTRASI WISUDA*

Mohon maaf {nama_lengkap},

❌ Registrasi wisuda Anda dengan NIM {nim} *DITOLAK* karena:

📝 *Alasan Penolakan:*
{catatan_penolakan}

📋 *Detail Registrasi:*
• NIM: {nim}
• Nama: {nama_lengkap}
• Periode: {nama_periode}
• Tanggal Wisuda: {tanggal_wisuda}

🔄 *Langkah Perbaikan:*
• Login ke sistem untuk melihat detail penolakan
• Perbaiki dokumen/data yang diminta
• Submit ulang untuk verifikasi
• Pantau status melalui sistem

🔗 *Link Sistem:* {link_sistem}

💡 *Tips:*
• Pastikan semua dokumen sesuai persyaratan
• Periksa kembali data akademik Anda
• Hubungi admin jika ada pertanyaan

Silakan perbaiki dan daftar ulang sesuai petunjuk di atas.

Terima kasih atas pengertian Anda.

Salam,
{nama_institusi} 📚
EOT;

        // Data template untuk registrasi wisuda disetujui
        $data_approved = [
            'nama' => 'Registrasi Wisuda Disetujui',
            'kode' => 'wisuda_approved',
            'isi_template' => $template_approved,
            'deskripsi' => 'Template notifikasi untuk registrasi wisuda yang disetujui',
            'created_by' => 1
        ];

        // Data template untuk registrasi wisuda ditolak
        $data_rejected = [
            'nama' => 'Registrasi Wisuda Ditolak',
            'kode' => 'wisuda_rejected',
            'isi_template' => $template_rejected,
            'deskripsi' => 'Template notifikasi untuk registrasi wisuda yang ditolak',
            'created_by' => 1
        ];

        // Array semua template
        $templates = [$data_approved, $data_rejected];
        $results = [];

        // Proses setiap template
        foreach ($templates as $template_data) {
            // Cek apakah template dengan kode tersebut sudah ada
            $existing = $this->db->get_where('wa_template', ['kode' => $template_data['kode']])->row();
            
            if ($existing) {
                // Update template yang ada
                $this->db->where('id', $existing->id);
                $result = $this->db->update('wa_template', [
                    'nama' => $template_data['nama'],
                    'isi_template' => $template_data['isi_template'],
                    'deskripsi' => $template_data['deskripsi'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $template_data['created_by']
                ]);
                
                if ($result) {
                    $results[] = "✅ Template '{$template_data['nama']}' berhasil diperbarui";
                } else {
                    $results[] = "❌ Gagal memperbarui template '{$template_data['nama']}'";
                }
            } else {
                // Simpan template baru
                $template_data['created_at'] = date('Y-m-d H:i:s');
                $template_data['updated_at'] = date('Y-m-d H:i:s');
                
                $result = $this->db->insert('wa_template', $template_data);
                
                if ($result) {
                    $results[] = "✅ Template '{$template_data['nama']}' berhasil ditambahkan";
                } else {
                    $results[] = "❌ Gagal menambahkan template '{$template_data['nama']}'";
                }
            }
        }

        // Output hasil
        echo "<h2>Setup Template WhatsApp Registrasi Wisuda</h2>";
        echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 5px;'>";
        
        foreach ($results as $result) {
            echo $result . "<br>";
        }
        
        echo "<br><strong>=== SELESAI ===</strong><br>";
        echo "Template WhatsApp untuk registrasi wisuda telah berhasil ditambahkan/diperbarui.<br><br>";

        echo "<strong>Template yang tersedia:</strong><br>";
        echo "1. <code>wisuda_approved</code> - Notifikasi registrasi wisuda disetujui<br>";
        echo "2. <code>wisuda_rejected</code> - Notifikasi registrasi wisuda ditolak<br><br>";

        echo "<strong>Parameter yang dapat digunakan:</strong><br>";
        echo "• <code>{nama_lengkap}</code> - Nama lengkap mahasiswa<br>";
        echo "• <code>{nim}</code> - NIM mahasiswa<br>";
        echo "• <code>{nama_periode}</code> - Nama periode wisuda<br>";
        echo "• <code>{tanggal_wisuda}</code> - Tanggal pelaksanaan wisuda<br>";
        echo "• <code>{tempat_wisuda}</code> - Tempat pelaksanaan wisuda<br>";
        echo "• <code>{biaya_wisuda}</code> - Biaya wisuda<br>";
        echo "• <code>{catatan_penolakan}</code> - Alasan penolakan (untuk template rejected)<br>";
        echo "• <code>{link_sistem}</code> - Link ke sistem registrasi<br>";
        echo "• <code>{nama_institusi}</code> - Nama institusi/universitas<br>";
        
        echo "</div>";
    }
}
