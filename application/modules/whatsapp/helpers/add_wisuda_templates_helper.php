<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper untuk menambahkan template WhatsApp untuk registrasi wisuda
 */

// Menginisialisasi instance CI
$CI = &get_instance();

// Memastikan model dimuat
$CI->load->model('whatsapp/wa_template_m');

// Set koneksi database untuk mendukung emoji
$CI->db->query("SET NAMES utf8mb4");
$CI->db->query("SET CHARACTER SET utf8mb4");

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

// Template 3: Pengingat Deadline Registrasi Wisuda
$template_reminder = <<<EOT
⏰ *PENGINGAT DEADLINE REGISTRASI WISUDA*

Halo {nama_lengkap},

⚠️ Registrasi wisuda untuk periode {nama_periode} akan segera berakhir!

📅 *Informasi Deadline:*
• Tanggal Tutup: {tanggal_tutup}
• Sisa Waktu: {sisa_hari} hari
• Tanggal Wisuda: {tanggal_wisuda}

📋 *Status Registrasi Anda:*
{status_registrasi}

🚀 *Segera Lakukan:*
• Login ke sistem registrasi wisuda
• Lengkapi semua dokumen persyaratan
• Submit untuk verifikasi admin
• Pastikan semua data sudah benar

🔗 *Link Sistem:* {link_sistem}

Jangan sampai terlewat! Daftarkan diri Anda sekarang juga.

Salam,
{nama_institusi} ⏰
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

// Data template untuk pengingat deadline
$data_reminder = [
    'nama' => 'Pengingat Deadline Registrasi Wisuda',
    'kode' => 'wisuda_reminder',
    'isi_template' => $template_reminder,
    'deskripsi' => 'Template pengingat deadline registrasi wisuda',
    'created_by' => 1
];

// Array semua template
$templates = [$data_approved, $data_rejected, $data_reminder];

// Proses setiap template
foreach ($templates as $template_data) {
    // Cek apakah template dengan kode tersebut sudah ada
    $existing = $CI->db->get_where('wa_template', ['kode' => $template_data['kode']])->row();
    
    if ($existing) {
        // Update template yang ada
        $CI->db->where('id', $existing->id);
        $result = $CI->db->update('wa_template', [
            'nama' => $template_data['nama'],
            'isi_template' => $template_data['isi_template'],
            'deskripsi' => $template_data['deskripsi'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $template_data['created_by']
        ]);
        
        if ($result) {
            echo "Template '{$template_data['nama']}' berhasil diperbarui\n";
        } else {
            echo "Gagal memperbarui template '{$template_data['nama']}'\n";
        }
    } else {
        // Simpan template baru
        $template_data['created_at'] = date('Y-m-d H:i:s');
        $template_data['updated_at'] = date('Y-m-d H:i:s');
        
        $result = $CI->db->insert('wa_template', $template_data);
        
        if ($result) {
            echo "Template '{$template_data['nama']}' berhasil ditambahkan\n";
        } else {
            echo "Gagal menambahkan template '{$template_data['nama']}'\n";
        }
    }
}

echo "\n=== SELESAI ===\n";
echo "Template WhatsApp untuk registrasi wisuda telah berhasil ditambahkan/diperbarui.\n\n";

echo "Template yang tersedia:\n";
echo "1. wisuda_approved - Notifikasi registrasi wisuda disetujui\n";
echo "2. wisuda_rejected - Notifikasi registrasi wisuda ditolak\n";
echo "3. wisuda_reminder - Pengingat deadline registrasi wisuda\n\n";

echo "Parameter yang dapat digunakan:\n";
echo "- {nama_lengkap} - Nama lengkap mahasiswa\n";
echo "- {nim} - NIM mahasiswa\n";
echo "- {nama_periode} - Nama periode wisuda\n";
echo "- {tanggal_wisuda} - Tanggal pelaksanaan wisuda\n";
echo "- {tempat_wisuda} - Tempat pelaksanaan wisuda\n";
echo "- {biaya_wisuda} - Biaya wisuda\n";
echo "- {catatan_penolakan} - Alasan penolakan (untuk template rejected)\n";
echo "- {link_sistem} - Link ke sistem registrasi\n";
echo "- {nama_institusi} - Nama institusi/universitas\n";
echo "- {tanggal_tutup} - Tanggal tutup registrasi (untuk reminder)\n";
echo "- {sisa_hari} - Sisa hari deadline (untuk reminder)\n";
echo "- {status_registrasi} - Status registrasi saat ini (untuk reminder)\n";
