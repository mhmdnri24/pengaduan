<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['hak_akses'] = [
    
    //Modul Beranda
    'Beranda' => [
        'admin.beranda.view' => 'Lihat Data',
    ],
    
    //Modul File Manager
    'File Manager' => [
        'admin.filemanager.view' => 'Lihat Data',
        'admin.filemanager.add' => 'Tambah Data',
        'admin.filemanager.update' => 'Edit Data',
        'admin.filemanager.delete' => 'Hapus Data'
    ],

   //Modul Hak Akses
   'Hak Akses' => [
    'admin.hak_akses.view' => 'Lihat Data',
    'admin.hak_akses.add' => 'Tambah Data',
    'admin.hak_akses.update' => 'Edit Data',
    'admin.hak_akses.delete' => 'Hapus Data'
   ],

    //Modul User
    'User' => [
        'admin.user.view' => 'Lihat Data',
        'admin.user.add' => 'Tambah Data',
        'admin.user.update' => 'Edit Data',
        'admin.user.delete' => 'Hapus Data'
    ],


    //Modul Provinsi
    'Provinsi' => array(
        'admin.provinsi.view' => 'Lihat Data',
        'admin.provinsi.add' => 'Tambah Data',
        'admin.provinsi.update' => 'Edit Data',
        'admin.provinsi.delete' => 'Hapus Data'
    ),

    //Modul Kota
    'Kota' => array(
        'admin.kota.view' => 'Lihat Data',
        'admin.kota.add' => 'Tambah Data',
        'admin.kota.update' => 'Edit Data',
        'admin.kota.delete' => 'Hapus Data'
    ),

    //Modul Kecamatan
    'Kecamatan' => array(
        'admin.kecamatan.view' => 'Lihat Data',
        'admin.kecamatan.add' => 'Tambah Data',
        'admin.kecamatan.update' => 'Edit Data',
        'admin.kecamatan.delete' => 'Hapus Data'
    ),

    //Modul Kelurahan
    'Kelurahan' => array(
        'admin.kelurahan.view' => 'Lihat Data',
        'admin.kelurahan.add' => 'Tambah Data',
        'admin.kelurahan.update' => 'Edit Data',
        'admin.kelurahan.delete' => 'Hapus Data'
    ),


    //Modul Identitas
    'Pengaturan' => [
        'admin.pengaturan.aplikasi' => 'Pengaturan Aplikasi',
        'admin.pengaturan.perusahaan' => 'Pengaturan Perusahaan',
        'admin.pengaturan.api' => 'Pengaturan API',
        'admin.pengaturan.api_internal' => 'Pengaturan API Internal',
        'admin.pengaturan.import_excel' => 'Import Excel',
        'admin.pengaturan.bantuan' => 'Pengaturan Bantuan'
    ],
    
    //Modul WhatsApp
    'WhatsApp' => [
        'admin.whatsapp.template' => 'Template WhatsApp',
        'admin.whatsapp.histori' => 'Histori WhatsApp',
        'admin.whatsapp.kirim' => 'Kirim Pesan'
    ],

    //Modul Dokumen
    'Dokumen' => [
        'admin.master_dokumen.view' => 'Lihat Data',
        'admin.master_dokumen.add' => 'Tambah Data',
        'admin.master_dokumen.update' => 'Edit Data',
        'admin.master_dokumen.delete' => 'Hapus Data'
    ],

    //Modul Master Kategori
    'Master Kategori' => [
        'admin.master_kategori.view' => 'Lihat Data',
        'admin.master_kategori.add' => 'Tambah Data',
        'admin.master_kategori.update' => 'Edit Data',
        'admin.master_kategori.delete' => 'Hapus Data'
    ],
  
    //Modul Menu
    'Menu' => [
        'admin.menu.view' => 'Lihat Data',
        'admin.menu.add' => 'Tambah Data',
        'admin.menu.update' => 'Edit Data',
        'admin.menu.delete' => 'Hapus Data'
    ],

    //Modul Retribusi Pasar
    'Retribusi Pasar' => [
        'admin.retribusi_pasar.view' => 'Lihat Data',
        'admin.retribusi_pasar.add' => 'Tambah Pembayaran',
        'admin.retribusi_pasar.update' => 'Edit Pembayaran',
        'admin.retribusi_pasar.delete' => 'Hapus Pembayaran',
        'admin.retribusi_pasar.laporan' => 'Lihat Laporan',
        'admin.retribusi_pasar.export' => 'Export Data',
        'admin.retribusi_pasar.setting_tarif' => 'Setting Tarif',
        'admin.retribusi_pasar.statistik' => 'Lihat Statistik'
    ],

    //Modul Laporan
    'Laporan' => [
        'admin.laporan.view' => 'Lihat Laporan',
        'admin.laporan.detail' => 'Lihat Detail Laporan',
        'admin.laporan.export' => 'Export Laporan',
        'admin.laporan.print' => 'Cetak Laporan'
    ],

    //Modul Fasilitas Umum
    'Fasilitas Umum' => [
        'admin.fasilitas_umum.view' => 'Lihat Data',
        'admin.fasilitas_umum.add' => 'Tambah Data',
        'admin.fasilitas_umum.update' => 'Edit Data',
        'admin.fasilitas_umum.delete' => 'Hapus Data'
    ],

    //Modul Master Pasar
    'Master Pasar' => [
        'admin.master_pasar.view' => 'Lihat Data',
        'admin.master_pasar.add' => 'Tambah Data',
        'admin.master_pasar.update' => 'Edit Data',
        'admin.master_pasar.delete' => 'Hapus Data'
    ],

    //Modul Master Jenis Pasar
    'Master Jenis Pasar' => [
        'admin.master_jenis_pasar.view' => 'Lihat Data',
        'admin.master_jenis_pasar.add' => 'Tambah Data',
        'admin.master_jenis_pasar.update' => 'Edit Data',
        'admin.master_jenis_pasar.delete' => 'Hapus Data'
    ],

    //Modul Master Pasar Blok
    'Master Pasar Blok' => [
        'admin.master_pasar_blok.view' => 'Lihat Data',
        'admin.master_pasar_blok.add' => 'Tambah Data',
        'admin.master_pasar_blok.update' => 'Edit Data',
        'admin.master_pasar_blok.delete' => 'Hapus Data'
    ],

    //Modul Master Pasar Blok
    'Penyewaan Blok' => [
        'admin.penyewaan_blok.view' => 'Lihat Data',
        'admin.penyewaan_blok.add' => 'Tambah Data',
        'admin.penyewaan_blok.update' => 'Edit Data',
        'admin.penyewaan_blok.delete' => 'Hapus Data'
    ],

    //Modul Kepengurusan Sosial
    'Kepengurusan Sosial' => [
        'admin.kepengurusan_sosial.view' => 'Lihat Data',
        'admin.kepengurusan_sosial.add' => 'Tambah Data',
        'admin.kepengurusan_sosial.update' => 'Edit Data',
        'admin.kepengurusan_sosial.delete' => 'Hapus Data'
    ],

    //Modul Kategori Kepengurusan
    'Kategori Kepengurusan' => [
        'admin.kategori_kepengurusan.view' => 'Lihat Data',
        'admin.kategori_kepengurusan.add' => 'Tambah Data',
        'admin.kategori_kepengurusan.update' => 'Edit Data',
        'admin.kategori_kepengurusan.delete' => 'Hapus Data'
    ],

    //Modul Kategori Pelaporan
    'Kategori Pelaporan' => [
        'admin.kategori_pelaporan.view' => 'Lihat Data',
        'admin.kategori_pelaporan.add' => 'Tambah Data',
        'admin.kategori_pelaporan.update' => 'Edit Data',
        'admin.kategori_pelaporan.delete' => 'Hapus Data'
    ],

    //Modul Kepengurusan Detail
    'Kepengurusan Detail' => [
        'admin.kepengurusan_detail.view' => 'Lihat Data',
        'admin.kepengurusan_detail.add' => 'Tambah Data',
        'admin.kepengurusan_detail.update' => 'Edit Data',
        'admin.kepengurusan_detail.delete' => 'Hapus Data'
    ],

    //Modul Masyarakat
    'Masyarakat' => [
        'admin.masyarakat.view' => 'Lihat Data',
        'admin.masyarakat.add' => 'Tambah Data',
        'admin.masyarakat.edit' => 'Edit Data',
        'admin.masyarakat.delete' => 'Hapus Data',
        'admin.masyarakat.detail' => 'Lihat Detail',
        'admin.masyarakat.export' => 'Export Data'
    ],

    //Modul Master Pedagang
    'Master Pedagang' => [
        'admin.master_pedagang.view' => 'Lihat Data',
        'admin.master_pedagang.add' => 'Tambah Data',
        'admin.master_pedagang.update' => 'Edit Data',
        'admin.master_pedagang.delete' => 'Hapus Data'
    ],

    //Modul Master Retribusi
    'Master Retribusi' => [
        'admin.master_retribusi.view' => 'Lihat Data',
        'admin.master_retribusi.add' => 'Tambah Data',
        'admin.master_retribusi.update' => 'Edit Data',
        'admin.master_retribusi.delete' => 'Hapus Data'
    ],

    //Modul Target Retribusi
    'Target Retribusi' => [
        'admin.target_retribusi.view' => 'Lihat Data',
        'admin.target_retribusi.add' => 'Tambah Data',
        'admin.target_retribusi.update' => 'Edit Data',
        'admin.target_retribusi.delete' => 'Hapus Data'
    ],

    //Modul Realisasi Retribusi
    'Realisasi Retribusi' => [
        'admin.realisasi_retribusi.view' => 'Lihat Data',
        'admin.realisasi_retribusi.add' => 'Tambah Data',
        'admin.realisasi_retribusi.update' => 'Edit Data',
        'admin.realisasi_retribusi.delete' => 'Hapus Data'
    ],

    //Modul Laporan Retribusi
    'Laporan Retribusi' => [
        'admin.laporan_retribusi.view' => 'Lihat Laporan',
        'admin.laporan_retribusi.export' => 'Export Data'
    ],

    //Modul Pelaporan
    'Pelaporan' => [
        'admin.pelaporan.view' => 'Lihat Data',
        'admin.pelaporan.add' => 'Tambah Data',
        'admin.pelaporan.update' => 'Edit Data',
        'admin.pelaporan.delete' => 'Hapus Data',
        'admin.pelaporan.detail' => 'Lihat Detail',
        'admin.pelaporan.dashboard' => 'Dashboard Pelaporan',
        'admin.pelaporan.update_status' => 'Update Status Laporan',
        'admin.pelaporan.timeline' => 'Lihat Timeline',
        'admin.pelaporan.map' => 'Lihat Peta Lokasi',
        'admin.pelaporan.comment' => 'Tambah Komentar',
        'admin.pelaporan.export' => 'Export Data',
        'admin.pelaporan.print' => 'Cetak Data'
    ],

    //Modul Jenis Layanan
    'Jenis Layanan' => [
        'admin.jenis_layanan.view' => 'Lihat Data',
        'admin.jenis_layanan.add' => 'Tambah Data',
        'admin.jenis_layanan.update' => 'Edit Data',
        'admin.jenis_layanan.delete' => 'Hapus Data'
    ],

    //Modul Detail Layanan
    'Detail Layanan' => [
        'admin.layanan_detail.view' => 'Lihat Data',
        'admin.layanan_detail.add' => 'Tambah Data',
        'admin.layanan_detail.update' => 'Edit Data',
        'admin.layanan_detail.delete' => 'Hapus Data'
    ],

    //Modul Kategori Layanan
    'Kategori Layanan' => [
        'admin.kategori_layanan.view' => 'Lihat Data',
        'admin.kategori_layanan.add' => 'Tambah Data',
        'admin.kategori_layanan.update' => 'Edit Data',
        'admin.kategori_layanan.delete' => 'Hapus Data'
    ],

    //Modul Komoditas
    'Komoditas' => [
        'admin.komoditas.view' => 'Lihat Data',
        'admin.komoditas.add' => 'Tambah Data',
        'admin.komoditas.update' => 'Edit Data',
        'admin.komoditas.delete' => 'Hapus Data'
    ],

    //Modul Harga Komoditas
    'Harga Komoditas' => [
        'admin.harga_komoditas.view' => 'Lihat Harga Komoditas',
        'admin.harga_komoditas.add' => 'Tambah Harga Komoditas',
        'admin.harga_komoditas.update' => 'Edit Harga Komoditas',
        'admin.harga_komoditas.delete' => 'Hapus Harga Komoditas'
    ],

    //Modul Peta Pelaporan
    'Peta Pelaporan' => [
        'admin.peta_pelaporan.view' => 'Lihat Data',
        'admin.peta_pelaporan.add' => 'Tambah Data',
        'admin.peta_pelaporan.update' => 'Edit Data',
        'admin.peta_pelaporan.delete' => 'Hapus Data',
        'admin.peta_pelaporan.import' => 'Import GeoJSON',
        'admin.peta_pelaporan.export' => 'Export GeoJSON'
    ],

    //Modul Slider
    'Slider' => [
        'admin.slider.view' => 'Lihat Data',
        'admin.slider.add' => 'Tambah Data',
        'admin.slider.update' => 'Edit Data',
        'admin.slider.delete' => 'Hapus Data'
    ]
];