<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper untuk menambahkan template WhatsApp pembayaran gedung
 */

// Menginisialisasi instance CI
$CI = &get_instance();

// Memastikan model dimuat
$CI->load->model('whatsapp/wa_template_m');

// Set koneksi database untuk mendukung emoji
$CI->db->query("SET NAMES utf8mb4");
$CI->db->query("SET CHARACTER SET utf8mb4");

// Template 1: Konfirmasi Pembayaran DP
$template_dp = <<<EOT
🎉 *KONFIRMASI PEMBAYARAN DP* 🎉

Halo {nama_pic},

Terima kasih! Pembayaran DP untuk booking gedung telah kami terima dengan detail sebagai berikut:

📋 *Detail Booking:*
• Nama Acara: {nama_acara}
• Gedung: {gedung_nama}
• Tanggal: {tanggal_acara}
• Waktu: {jam_acara}

💰 *Detail Pembayaran:*
• Kode Pembayaran: {kode_pembayaran}
• Jenis: Down Payment (DP)
• Nominal: Rp {nominal_pembayaran}
• Tanggal Bayar: {tanggal_pembayaran}

⚠️ *Sisa Tagihan: Rp {sisa_tagihan}*

Silakan lakukan pelunasan sebelum hari H acara. Terima kasih atas kepercayaan Anda!

Salam,
{nama_perusahaan} 🏢
EOT;

$data_dp = [
    'nama' => 'Konfirmasi Pembayaran DP Gedung',
    'kode' => 'konfirmasi_dp_gedung',
    'isi_template' => $template_dp,
    'deskripsi' => 'Template konfirmasi pembayaran DP untuk booking gedung',
    'created_by' => 1
];

// Template 2: Konfirmasi Pelunasan
$template_pelunasan = <<<EOT
✅ *KONFIRMASI PELUNASAN* ✅

Halo {nama_pic},

Alhamdulillah! Pembayaran pelunasan untuk booking gedung telah kami terima dengan detail sebagai berikut:

📋 *Detail Booking:*
• Nama Acara: {nama_acara}
• Gedung: {gedung_nama}
• Tanggal: {tanggal_acara}
• Waktu: {jam_acara}

💰 *Detail Pembayaran:*
• Kode Pembayaran: {kode_pembayaran}
• Jenis: Pelunasan
• Nominal: Rp {nominal_pembayaran}
• Tanggal Bayar: {tanggal_pembayaran}

🎊 *STATUS: LUNAS* 🎊

Booking Anda telah dikonfirmasi dan siap digunakan. Silakan datang sesuai jadwal yang telah ditentukan.

Terima kasih atas kepercayaan Anda!

Salam,
{nama_perusahaan} 🏢
EOT;

$data_pelunasan = [
    'nama' => 'Konfirmasi Pelunasan Gedung',
    'kode' => 'konfirmasi_pelunasan_gedung',
    'isi_template' => $template_pelunasan,
    'deskripsi' => 'Template konfirmasi pelunasan untuk booking gedung',
    'created_by' => 1
];

// Template 3: Konfirmasi Pembayaran Lunas (Bayar Sekaligus)
$template_lunas = <<<EOT
🎉 *KONFIRMASI PEMBAYARAN LUNAS* 🎉

Halo {nama_pic},

Terima kasih! Pembayaran untuk booking gedung telah kami terima dengan detail sebagai berikut:

📋 *Detail Booking:*
• Nama Acara: {nama_acara}
• Gedung: {gedung_nama}
• Tanggal: {tanggal_acara}
• Waktu: {jam_acara}

💰 *Detail Pembayaran:*
• Kode Pembayaran: {kode_pembayaran}
• Jenis: Pembayaran Lunas
• Nominal: Rp {nominal_pembayaran}
• Tanggal Bayar: {tanggal_pembayaran}

✅ *STATUS: LUNAS* ✅

Booking Anda telah dikonfirmasi dan siap digunakan. Silakan datang sesuai jadwal yang telah ditentukan.

Terima kasih atas kepercayaan Anda!

Salam,
{nama_perusahaan} 🏢
EOT;

$data_lunas = [
    'nama' => 'Konfirmasi Pembayaran Lunas Gedung',
    'kode' => 'konfirmasi_pembayaran_gedung',
    'isi_template' => $template_lunas,
    'deskripsi' => 'Template konfirmasi pembayaran lunas untuk booking gedung',
    'created_by' => 1
];

// Template 4: Pengingat Pelunasan
$template_pengingat = <<<EOT
⏰ *PENGINGAT PELUNASAN* ⏰

Halo {nama_pic},

Kami ingin mengingatkan bahwa masih ada sisa pembayaran untuk booking gedung Anda:

📋 *Detail Booking:*
• Nama Acara: {nama_acara}
• Gedung: {gedung_nama}
• Tanggal: {tanggal_acara}
• Waktu: {jam_acara}

💰 *Status Pembayaran:*
• Total Tagihan: Rp {total_tagihan}
• Sudah Dibayar: Rp {sudah_dibayar}
• Sisa Tagihan: Rp {sisa_tagihan}

⚠️ Mohon segera lakukan pelunasan sebelum hari H acara.

Untuk pembayaran dapat melalui:
• Transfer Bank
• Cash di tempat

Terima kasih atas perhatiannya!

Salam,
{nama_perusahaan} 🏢
EOT;

$data_pengingat = [
    'nama' => 'Pengingat Pelunasan Gedung',
    'kode' => 'pengingat_pelunasan_gedung',
    'isi_template' => $template_pengingat,
    'deskripsi' => 'Template pengingat pelunasan untuk booking gedung',
    'created_by' => 1
];

// Array semua template
$templates = [$data_dp, $data_pelunasan, $data_lunas, $data_pengingat];

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

echo "\nSemua template pembayaran gedung telah diproses!\n";
