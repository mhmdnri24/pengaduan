<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Helper untuk menambahkan template pengingat pembayaran dengan emoji
 * File ini perlu dijalankan menggunakan CLI atau diakses melalui URL
 */

// Menginisialisasi instance CI
$CI = &get_instance();

// Memastikan model dimuat
$CI->load->model('whatsapp/wa_template_m');

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
$CI->db->query("SET NAMES utf8mb4");
$CI->db->query("SET CHARACTER SET utf8mb4");

// Cek apakah template dengan kode tersebut sudah ada
$existing = $CI->db->get_where('wa_template', ['kode' => $data['kode']])->row();

if ($existing) {
    // Update template yang ada
    $CI->db->where('id', $existing->id);
    $result = $CI->db->update('wa_template', [
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
    
    $result = $CI->db->insert('wa_template', $data);
    
    if ($result) {
        echo "Template berhasil ditambahkan";
    } else {
        echo "Gagal menambahkan template";
    }
} 