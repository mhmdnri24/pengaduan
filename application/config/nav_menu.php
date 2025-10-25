<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['nav_menu'] = array(
    // Menu minimal untuk fallback jika database error
    array(
        'id' => 'dashboard',
        'text' => 'Dashboard',
        'icon' => 'fa fa-dashboard',
        'url' => 'beranda',
        'hak_akses' => 'admin.dashboard.view',
    ),
    array(
        'id' => 'menu_admin',
        'text' => 'Menu Admin',
        'url' => 'menu',
        'icon' => 'fa fa-list',
        'hak_akses' => 'admin.menu.view',
    ),
);

// Menu dinamis dari database tidak perlu di-merge ke nav_menu
// Karena sistem sudah menggunakan prioritas: database first, config fallback
// Kode ini di-comment untuk optimasi performa

/*
// Tambahkan menu dinamis dari database (jika ada)
// Gunakan try-catch untuk menghindari error jika fungsi belum tersedia
try {
    if (function_exists('ce_get_dynamic_menu')) {
        $dynamic_menu = ce_get_dynamic_menu();
        if (!empty($dynamic_menu)) {
            $config['nav_menu'] = array_merge($config['nav_menu'], $dynamic_menu);
        }
    }
} catch (Exception $e) {
    // Log error jika perlu
    if (function_exists('log_message')) {
        log_message('error', 'Error saat memuat menu dinamis: ' . $e->getMessage());
    }
}
*/

$config['active_menu'] = null;