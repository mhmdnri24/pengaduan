<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| AUTO-LOADER
| -------------------------------------------------------------------
| This file specifies which systems should be loaded by default.
|
| In order to keep the framework as light-weight as possible only the
| absolute minimal resources are loaded by default. For example,
| the database is not connected to automatically since no assumption
| is made regarding whether you intend to use it.  This file lets
| you globally define which systems you would like loaded with every
| request.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| These are the things you can load automatically:
|
| 1. Packages
| 2. Libraries
| 3. Drivers
| 4. Helper files
| 5. Custom config files
| 6. Language files
| 7. Models
|
*/

/*
| -------------------------------------------------------------------
|  Auto-load Packages
| -------------------------------------------------------------------
| Prototype:
|
|  $autoload['packages'] = array(APPPATH.'third_party', '/usr/local/shared');
|
*/
$autoload['packages'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Libraries
| -------------------------------------------------------------------
| These are the classes located in system/libraries/ or your
| application/libraries/ directory, with the addition of the
| 'database' library, which is somewhat of a special case.
|
| Prototype:
|
|	$autoload['libraries'] = array('database', 'email', 'session');
|
| You can also supply an alternative library name to be assigned
| in the controller:
|
|	$autoload['libraries'] = array('user_agent' => 'ua');
*/
$autoload['libraries'] = array(
    'user_agent',
    'database',
    'session',
    'form_validation',
    'upload',
    'curl',
    'security',
    'input',
    'output'
);

/*
| -------------------------------------------------------------------
|  Auto-load Drivers
| -------------------------------------------------------------------
| These classes are located in system/libraries/ or in your
| application/libraries/ directory, but are also placed inside their
| own subdirectory and they extend the CI_Driver_Library class. They
| offer multiple interchangeable driver options.
|
| Prototype:
|
|	$autoload['drivers'] = array('cache');
|
| You can also supply an alternative property name to be assigned in
| the controller:
|
|	$autoload['drivers'] = array('cache' => 'cch');
|
*/
$autoload['drivers'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Helper Files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['helper'] = array('url', 'file');
*/
$autoload['helper'] = array(
    'url',
    'date',
    'text',
    'ce',
    'form',
    'uuid',
    'wa',
    'secure_file',
    'security'
);

/*
| -------------------------------------------------------------------
|  Auto-load Config files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['config'] = array('config1', 'config2');
|
| NOTE: This item is intended for use ONLY if you have created custom
| config files.  Otherwise, leave it blank.
|
*/
$autoload['config'] = array(
    'nav_menu',
    'hak_akses',
    'bulan',
    'status'
);

/*
| -------------------------------------------------------------------
|  Auto-load Language files
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['language'] = array('lang1', 'lang2');
|
| NOTE: Do not include the "_lang" part of your file.  For example
| "codeigniter_lang.php" would be referenced as array('codeigniter');
|
*/
$autoload['language'] = array();

/*
| -------------------------------------------------------------------
|  Auto-load Models
| -------------------------------------------------------------------
| Prototype:
|
|	$autoload['model'] = array('first_model', 'second_model');
|
| You can also supply an alternative model name to be assigned
| in the controller:
|
|	$autoload['model'] = array('first_model' => 'first');
*/
$autoload['model'] = array(
    'ce_model',
    'ajax_data_m',
    'daerah_m',
    'beranda/beranda_m',
    'hak_akses/level_m',
    'pengaturan/pengaturan_m',
    'user/user_m',
    'user/Log_login_m',
    'menu/menu_m',
    'whatsapp/wa_template_m',
    'whatsapp/wa_histori_m',
    'provinsi/provinsi_m',
    'kota/kota_m',
    'kecamatan/kecamatan_m',
    'kelurahan/kelurahan_m',
    
    'master_kategori/master_kategori_m',
    'master_unitkerja/master_unitkerja_m',
    'master_instansi/master_instansi_m',
    'fasilitas_umum/fasilitas_umum_m',
    //kesra
    'kategori_kepengurusan/kategori_kepengurusan_m',
    'kepengurusan_sosial/kepengurusan_sosial_m',
    'kepengurusan_detail/kepengurusan_detail_m',
    //disperindag
    'master_pasar/master_pasar_m',
    'master_jenis_pasar/master_jenis_pasar_m',
    'master_pasar_blok/master_pasar_blok_m',
    'master_pedagang/master_pedagang_m',
    'penyewaan_blok/penyewaan_blok_m',
    'harga_komoditas/harga_komoditas_m',
    'komoditas/komoditas_m',
    //pelaporan
    'masyarakat/masyarakat_m',
    'pelaporan/pelaporan_m',
    'kategori_pelaporan/kategori_pelaporan_m',
    //layanan
    'jenis_layanan/jenis_layanan_m',
    'layanan_detail/layanan_detail_m',
    'kategori_layanan/kategori_layanan_m',

    //retribusi pasar
    'retribusi_pasar/retribusi_pasar_m',
    'retribusi_pasar/retribusi_tarif_m',
    
    //slider
    'slider/slider_m'
);
