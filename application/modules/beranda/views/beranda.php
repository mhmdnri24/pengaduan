<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Leaflet CSS untuk map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
#map { height: 400px; }
.loading-spinner { text-align: center; padding: 20px; color: #777; }
.fa-spinner { animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
.recent-item { padding: 12px; border-bottom: 1px solid #f4f4f4; }
.recent-item:last-child { border-bottom: none; }
</style>

<!-- Header handled by template content-header -->

<!-- Dashboard Statistics Cards -->
<div class="row">
    <!-- Statistik Masyarakat Terdaftar -->
    <div class="col-md-12">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Masyarakat Terdaftar</span>
                <span class="info-box-number" id="total-masyarakat">
                    <div class="loading-spinner"><i class="fa fa-spinner"></i></div>
                </span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%; background-color: rgba(255,255,255,0.3);"></div>
                </div>
                <span class="progress-description">
                    <span id="masyarakat-aktif">-</span> Aktif, <span id="masyarakat-nonaktif">-</span> Nonaktif
                </span>
            </div>
        </div>
    </div>
</div>

<div class="section-divider"></div>

<!-- Kategori Kepengurusan Cards -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list-alt"></i> Kategori Kepengurusan</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-default" id="refresh-kategori-kepengurusan">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div id="kategori-kepengurusan-cards" class="row">
                    <div class="col-md-12">
                        <div class="loading-spinner">
                            <i class="fa fa-spinner"></i>
                            <p>Memuat data kategori kepengurusan...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fasilitas Umum per Kategori Cards -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-building"></i> Fasilitas Umum per Kategori</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-default" id="refresh-fasilitas-kategori">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">
                    <div class="col-md-4">
                        <select id="filter-kecamatan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kecamatan">
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="filter-kelurahan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kelurahan">
                            <option value="">Semua Kelurahan</option>
                        </select>
                    </div>
                </div>
                <div id="fasilitas-kategori-cards" class="row">
                    <div class="col-md-12">
                        <div class="loading-spinner">
                            <i class="fa fa-spinner"></i>
                            <p>Memuat data fasilitas per kategori...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-divider"></div>

<!-- Statistik Pelaporan -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Statistik Pelaporan</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-default" id="refresh-statistik-pelaporan">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div id="statistik-pelaporan-cards" class="row">
                    <div class="col-md-12">
                        <div class="loading-spinner">
                            <i class="fa fa-spinner"></i>
                            <p>Memuat statistik pelaporan...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-divider"></div>

<!-- Main Content Row -->
<div class="row">
    <!-- Peta Lokasi Pelaporan -->
    <div class="col-md-8">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-map-marker"></i> Peta Lokasi Pelaporan</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-default" id="refresh-pelaporan-map">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">
                    <div class="col-md-4">
                        <select id="pelaporan-map-kecamatan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kecamatan">
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="pelaporan-map-kelurahan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kelurahan">
                            <option value="">Semua Kelurahan</option>
                        </select>
                    </div>
                </div>
                <div id="pelaporan-map-container">
                    <div id="pelaporan-map" style="border:1px solid #ddd; border-radius:4px; height: 400px;"></div>
                    <div id="pelaporan-map-loading" class="loading-spinner">
                        <i class="fa fa-spinner"></i>
                        <p>Memuat peta pelaporan...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peta Lokasi Fasilitas Umum -->
    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-building"></i> Peta Fasilitas Umum</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-sm btn-default" id="refresh-map">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px">
                    <div class="col-md-12">
                        <select id="map-kecamatan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kecamatan">
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                </div>
                <div id="map-container">
                    <div id="map" style="border:1px solid #ddd; border-radius:4px; height: 300px;"></div>
                    <div id="map-loading" class="loading-spinner">
                        <i class="fa fa-spinner"></i>
                        <p>Memuat peta fasilitas umum...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History Login Terbaru -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> History Login Terbaru</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-blue" id="login-count">-</span>
                </div>
            </div>
            <div class="box-body" style="max-height: 300px; overflow-y: auto;">
                <div id="login-history-content">
                    <div class="loading-spinner">
                        <i class="fa fa-spinner"></i>
                        <p>Memuat history login...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Leaflet JS untuk map -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
