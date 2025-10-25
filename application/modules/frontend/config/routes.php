<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Frontend module routes - Specific routes first
$route['frontend/detail-pelaporan/(:num)'] = 'frontend/detail_pelaporan/$1';
$route['frontend/pelaporan'] = 'frontend/pelaporan';
$route['frontend/fasilitas'] = 'frontend/fasilitas';
$route['frontend/fasilitas/(:any)/(:num)'] = 'frontend/fasilitas/$1/$2';
$route['frontend/harga_komoditas'] = 'frontend/harga_komoditas';
$route['frontend/detail-(:any)/(:num)'] = 'frontend/detail/$2';

// Default route
$route['frontend'] = 'frontend/index';
$route['frontend/(:any)'] = 'frontend/$1';