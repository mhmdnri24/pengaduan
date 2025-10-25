<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Status Pelaporan
$config['report_status'] = [
	'LAPOR' => 'Lapor',
	'DITERIMA' => 'Diterima',
	'DIKERJAKAN' => 'Dikerjakan',
	'DIBATALKAN' => 'Dibatalkan',
	'SELESAI' => 'Selesai'
];

// Status Labels untuk UI
$config['report_status_labels'] = [
	'LAPOR' => 'label-warning',
	'DITERIMA' => 'label-info',
	'DIKERJAKAN' => 'label-primary',
	'DIBATALKAN' => 'label-danger',
	'SELESAI' => 'label-success'
];

// Status Icons
$config['report_status_icons'] = [
	'LAPOR' => 'fa-exclamation-circle',
	'DITERIMA' => 'fa-check-circle',
	'DIKERJAKAN' => 'fa-cog',
	'DIBATALKAN' => 'fa-times-circle',
	'SELESAI' => 'fa-check-square'
];

// Status Flow untuk validasi
$config['report_status_flow'] = [
	'LAPOR' => ['DITERIMA', 'BATAL'],
	'DITERIMA' => ['DIKERJAKAN', 'BATAL'],
	'DIKERJAKAN' => ['SELESAI', 'BATAL'],
	'DIBATALKAN' => [],
	'SELESAI' => []
];

// Status Penyewaan Blok
$config['rental_status'] = [
	'MENUNGGU' => 'Menunggu Konfirmasi',
	'AKTIF' => 'Aktif',
	'SELESAI' => 'Selesai',
	'BATAL' => 'Dibatalkan',
	'EXPIRED' => 'Kadaluarsa'
];

// Status Labels untuk UI Penyewaan
$config['rental_status_labels'] = [
	'MENUNGGU' => 'label-warning',
	'AKTIF' => 'label-success',
	'SELESAI' => 'label-info',
	'BATAL' => 'label-danger',
	'EXPIRED' => 'label-default'
];

// Status Icons untuk UI Penyewaan
$config['rental_status_icons'] = [
	'MENUNGGU' => 'fa-clock-o',
	'AKTIF' => 'fa-check-circle',
	'SELESAI' => 'fa-check-square',
	'BATAL' => 'fa-times-circle',
	'EXPIRED' => 'fa-calendar-times-o'
];

// Status Flow untuk validasi Penyewaan
$config['rental_status_flow'] = [
	'MENUNGGU' => ['AKTIF', 'BATAL'],
	'AKTIF' => ['SELESAI', 'BATAL', 'EXPIRED'],
	'SELESAI' => [],
	'BATAL' => [],
	'EXPIRED' => ['AKTIF'] // Bisa diperpanjang
];

// Status Slider
$config['slider_status'] = [
	'AKTIF' => 'Aktif',
	'NON AKTIF' => 'Non Aktif'
];

// Status Labels untuk UI Slider
$config['slider_status_labels'] = [
	'AKTIF' => 'label-success',
	'NON AKTIF' => 'label-danger'
];

// Status Icons untuk UI Slider
$config['slider_status_icons'] = [
	'AKTIF' => 'fa-check-circle',
	'NON AKTIF' => 'fa-times-circle'
];