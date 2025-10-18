<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'frontend';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

// Frontend public routes
$route['frontend'] = 'frontend';
$route['frontend/detail-(:any)/(:num)'] = 'frontend/detail/$2';
$route['frontend/fasilitas'] = 'frontend/fasilitas';
$route['frontend/fasilitas/(:any)/(:num)'] = 'frontend/fasilitas/$1/$2';
$route['frontend/harga_komoditas'] = 'frontend/harga_komoditas';
$route['frontend/pelaporan'] = 'frontend/pelaporan';
$route['frontend/detail-pelaporan/(:num)'] = 'frontend/detail_pelaporan/$1';
$route['frontend/test'] = 'frontend/test';
$route['frontend/(:any)'] = 'frontend/$1';


// $route['harga-komoditas'] = 'frontend/harga_komoditas';
// $route['kepengurusan'] = 'frontend/kepengurusan';
// $route['umkm'] = 'frontend/umkm';
// $route['file/(.+)'] = 'frontend/serve_secure_file/$1';

// // AJAX routes
// $route['ajax-komoditas-table'] = 'frontend/ajax_komoditas_table';
// $route['ajax-chart-data'] = 'frontend/ajax_chart_data';

// // Admin routes
// $route['admin'] = 'beranda';
// $route['admin/(.*)'] = '$1';

// API routes
$route['api/v1/pengaturan'] = 'api_pengaturan_proper/index';
$route['api/v1/pengaturan/(:any)'] = 'api_pengaturan_proper/$1';

$route['api/v1/pelaporan'] = 'api_pelaporan_proper/index';
$route['api/v1/pelaporan/create'] = 'api_pelaporan_proper/create';
$route['api/v1/pelaporan/kategori'] = 'api_pelaporan_proper/kategori';
$route['api/v1/pelaporan/statistics'] = 'api_pelaporan_proper/statistics';
$route['api/v1/pelaporan/test'] = 'api_pelaporan_proper/test';
$route['api/v1/pelaporan/pelaporan_history/(:num)'] = 'Api_pelaporan_proper/pelaporan_history/$1';
$route['api/v1/pelaporan/(:num)/create_comment'] = 'api_pelaporan_proper/create_comment/$1';
$route['api/v1/pelaporan/(:num)'] = 'api_pelaporan_proper/show/$1';

$route['api/v1/masyarakat/profile'] = 'api_masyarakat/profile';
$route['api/v1/masyarakat/kecamatan'] = 'api_masyarakat/get_kecamatan';
$route['api/v1/masyarakat/kelurahan/(:any)'] = 'api_masyarakat/get_kelurahan/$1';
$route['api/v1/masyarakat/register'] = 'api_masyarakat/register';
$route['api/v1/masyarakat/login'] = 'api_masyarakat/login';
$route['api/v1/masyarakat/verify-otp'] = 'api_masyarakat/verify_otp';
$route['api/v1/masyarakat/test'] = 'api_masyarakat/test';
$route['api/v1/masyarakat/search'] = 'api_masyarakat/get_by_nik';
$route['api/v1/masyarakat/([0-9]{16})'] = 'api_masyarakat/get_by_nik/$1';

$route['api/v1/device'] = 'api_device/index';
$route['api/v1/device/create'] = 'api_device/create';
$route['api/v1/device/insert_or_update'] = 'api_device/insert_or_update';
$route['api/v1/device/update'] = 'api_device/update';
$route['api/v1/device/send_test'] = 'api_device/send_test';
$route['api/v1/device/delete/(:any)'] = 'api_device/delete/$1';

$route['api/v1/(:any)'] = 'api_$1/index';
$route['api/v1/(:any)/(:any)'] = 'api_$1/$2';
