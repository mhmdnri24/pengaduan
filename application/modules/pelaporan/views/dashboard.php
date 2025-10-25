<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
    </div>
</div>

<!-- Dashboard Statistics Cards Berdasarkan Status -->
<div class="row">
    <!-- Total Laporan -->
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= $stats['total'] ?></h3>
                <p>Total Laporan</p>
            </div>
            <div class="icon">
                <i class="fa fa-file-text"></i>
            </div>
            <a href="<?= base_url('pelaporan') ?>" class="small-box-footer">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <?php
    // Mapping warna dan ikon berdasarkan status
    $status_config = [
        'LAPOR' => ['bg' => 'bg-red', 'icon' => 'fa fa-exclamation-circle', 'label' => 'Laporan Baru'],
        'DITERIMA' => ['bg' => 'bg-blue', 'icon' => 'fa fa-info-circle', 'label' => 'Diterima'],
        'DIKERJAKAN' => ['bg' => 'bg-yellow', 'icon' => 'fa fa-cog', 'label' => 'Sedang Dikerjakan'],
        'DIBATALKAN' => ['bg' => 'bg-gray', 'icon' => 'fa fa-times-circle', 'label' => 'Batal'],
        'SELESAI' => ['bg' => 'bg-green', 'icon' => 'fa fa-check-circle', 'label' => 'Selesai']
    ];

    foreach ($reports_by_status as $status_data):
        $status = $status_data->status;
        $count = $status_data->count;
        $config = isset($status_config[$status]) ? $status_config[$status] : ['bg' => 'bg-gray', 'icon' => 'fa fa-question', 'label' => $status];
    ?>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box <?= $config['bg'] ?>">
            <div class="inner">
                <h3><?= $count ?></h3>
                <p><?= $config['label'] ?></p>
            </div>
            <div class="icon">
                <i class="<?= $config['icon'] ?>"></i>
            </div>
            <a href="<?= base_url('pelaporan?status=' . $status) ?>" class="small-box-footer">
                Lihat Detail <i class="fa fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Secondary Stats -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Bulan Ini</span>
                <span class="info-box-number"><?= $stats['bulan_ini'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Hari Ini</span>
                <span class="info-box-number"><?= $stats['hari_ini'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Prioritas Tinggi</span>
                <span class="info-box-number"><?= $stats['urgent'] + $stats['tinggi'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Menunggu</span>
                <span class="info-box-number"><?= $stats['diterima'] ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Map Visualization -->
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-map-marker"></i> Peta Lokasi Laporan</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row" style="margin-bottom:10px;" id="map-controls">
                    <div class="col-md-4">
                        <select id="map-kecamatan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kecamatan">
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="map-kelurahan" class="form-control select2" style="width:100%" data-placeholder="Pilih Kelurahan">
                            <option value="">Semua Kelurahan</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-default" id="map-fullscreen"><i class="fa fa-arrows-alt"></i> Full Screen</button>
                        <button class="btn btn-default" id="map-refresh"><i class="fa fa-refresh"></i> Refresh</button>
                    </div>
                </div>
                <div id="reports-map" style="height: 420px; position: relative;">
                    <!-- Fullscreen controls overlay -->
                    <div id="fullscreen-controls" style="display: none; position: absolute; top: 10px; left: 10px; z-index: 1000; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                        <div class="row">
                            <div class="col-xs-4">
                                <select id="fullscreen-kecamatan" class="form-control" style="width:100%">
                                    <option value="">Semua Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-xs-4">
                                <select id="fullscreen-kelurahan" class="form-control" style="width:100%">
                                    <option value="">Semua Kelurahan</option>
                                </select>
                            </div>
                            <div class="col-xs-4">
                                <button class="btn btn-default btn-sm" id="fullscreen-refresh"><i class="fa fa-refresh"></i> Refresh</button>
                                <button class="btn btn-default btn-sm" id="fullscreen-exit"><i class="fa fa-compress"></i> Exit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-12">
                        <div class="legend">
                            <span class="legend-item">
                                <i class="fa fa-circle text-red"></i> Lapor
                            </span>
                            <span class="legend-item">
                                <i class="fa fa-circle text-blue"></i> Diterima
                            </span>
                            <span class="legend-item">
                                <i class="fa fa-circle text-yellow"></i> Dikerjakan
                            </span>
                            <span class="legend-item">
                                <i class="fa fa-circle text-gray"></i> Batal
                            </span>
                            <span class="legend-item">
                                <i class="fa fa-circle text-green"></i> Selesai
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
</div>

<div class="row">
    <!-- Category Distribution -->
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Laporan per Kategori</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div style="position: relative; height: 150px; width: 100%;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Distribution -->
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Distribusi Prioritas</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php
                // Mapping prioritas ke warna dan label
                $prioritas_config = [
                    'URGENT' => ['bg' => 'progress-bar-danger', 'label' => 'Urgent'],
                    'TINGGI' => ['bg' => 'progress-bar-warning', 'label' => 'Tinggi'],
                    'SEDANG' => ['bg' => 'progress-bar-info', 'label' => 'Sedang'],
                    'RENDAH' => ['bg' => 'progress-bar-success', 'label' => 'Rendah']
                ];
    
                foreach ($reports_by_prioritas as $prioritas_data):
                    $prioritas = $prioritas_data->prioritas;
                    $count = $prioritas_data->count;
                    $config = isset($prioritas_config[$prioritas]) ? $prioritas_config[$prioritas] : ['bg' => 'progress-bar-default', 'label' => $prioritas];
                ?>
                <div class="progress-group">
                    <span class="progress-text"><?= $config['label'] ?></span>
                    <span class="float-right"><b><?= $count ?></b>/<?= $stats['total'] ?></span>
                    <div class="progress progress-sm">
                        <div class="progress-bar <?= $config['bg'] ?>" style="width: <?= $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Status Distribution -->
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-pie-chart"></i> Distribusi Status</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-legend" style="margin-top: 10px;">
                    <?php foreach ($reports_by_status as $status): ?>
                    <div class="legend-item">
                        <span class="legend-color" style="background-color: 
                            <?= $status->status == 'LAPOR' ? '#f39c12' : 
                                ($status->status == 'DITERIMA' ? '#3c8dbc' : 
                                ($status->status == 'DIKERJAKAN' ? '#f56954' : '#00a65a')) ?>"></span>
                        <?= $status->status ?>: <?= $status->count ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Recent Reports -->
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o"></i> Laporan Terbaru</h3>
                <div class="box-tools pull-right">
                    <a href="<?= base_url('pelaporan') ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-eye"></i> Lihat Semua
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="12%">Kode Laporan</th>
                                <th width="25%">Judul</th>
                                <th width="15%">Pelapor</th>
                                <th width="12%">Kategori</th>
                                <th width="10%">Status</th>
                                <th width="10%">Prioritas</th>
                                <th width="12%">Tanggal</th>
                                <th width="4%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_reports)): ?>
                                <?php foreach ($recent_reports as $report): ?>
                                <tr>
                                    <td><strong><?= $report->kode_laporan ?></strong></td>
                                    <td><?= substr($report->judul, 0, 50) . (strlen($report->judul) > 50 ? '...' : '') ?></td>
                                    <td><?= $report->pelapor_nama ?></td>
                                    <td>
                                        <?php if (isset($report->kategori_icon) && isset($report->kategori_warna)): ?>
                                        <span class="label" style="background-color: <?= $report->kategori_warna ?>">
                                            <i class="<?= $report->kategori_icon ?>"></i> <?= $report->nama_kategori ?>
                                        </span>
                                        <?php else: ?>
                                        <?= $report->kategori ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_config = $this->config->item('report_status_labels');
                                        $status_label = isset($status_config[$report->status]) ? $status_config[$report->status] : 'label-default';
                                        ?>
                                        <span class="label <?= $status_label ?>"><?= $report->status ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $prioritas_class = '';
                                        switch($report->prioritas) {
                                            case 'URGENT': $prioritas_class = 'label-danger'; break;
                                            case 'TINGGI': $prioritas_class = 'label-warning'; break;
                                            case 'SEDANG': $prioritas_class = 'label-info'; break;
                                            case 'RENDAH': $prioritas_class = 'label-default'; break;
                                        }
                                        ?>
                                        <span class="label <?= $prioritas_class ?>"><?= $report->prioritas ?></span>
                                    </td>
                                    <td><?= date('d-m-Y H:i', strtotime($report->created_at)) ?></td>
                                    <td>
                                        <a href="<?= base_url('pelaporan/detail/' . $report->id) ?>" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Detail">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada laporan</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.legend {
    text-align: center;
}
.legend-item {
    display: inline-block;
    margin: 0 10px;
    font-size: 12px;
}
.legend-color {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 5px;
}
.chart-legend .legend-item {
    display: block;
    margin: 5px 0;
}
.progress-group {
    margin-bottom: 10px;
}
.float-right {
    float: right;
}
/* Fullscreen map support */
#reports-map.fullscreen {
    position: fixed !important;
    top: 0; left: 0; right: 0; bottom: 0;
    height: auto !important;
    z-index: 1050;
}

/* Hide normal controls when fullscreen */
.fullscreen-active #map-controls {
    display: none;
}

/* Fullscreen controls styling */
#fullscreen-controls {
    min-width: 600px;
}

#fullscreen-controls .form-control {
    font-size: 12px;
    height: 30px;
}

#fullscreen-controls .btn {
    height: 30px;
    padding: 5px 10px;
    font-size: 12px;
}

/* Tooltip styling for GeoJSON overlays */
.kecamatan-tooltip {
    background-color: rgba(255, 140, 0, 0.9);
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    font-size: 12px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

.kelurahan-tooltip {
    background-color: rgba(50, 205, 50, 0.9);
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    font-size: 11px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

/* Custom marker icon styling - same as beranda module */
.custom-marker {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
}

.custom-marker div {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    border: 2px solid white;
}

.custom-marker i {
    font-size: 12px;
    line-height: 1;
    color: white;
}

/* Operator marker styling */
.operator-marker {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
}

.operator-marker div {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 8px rgba(0,0,0,0.4);
    border: 3px solid white;
    animation: pulse 2s infinite;
}

.operator-marker i {
    font-size: 16px;
    line-height: 1;
    color: white;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Routing control styling - Enhanced for cross-device compatibility */
.routing-control {
    background-color: white !important;
    border-radius: 4px !important;
    box-shadow: 0 1px 5px rgba(0,0,0,0.4) !important;
    min-width: 90px !important;
    z-index: 1000 !important;
    position: relative !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure routing control is visible on all devices */
.leaflet-control-container .routing-control {
    display: block !important;
    visibility: visible !important;
}

/* Force visibility on desktop */
@media (min-width: 768px) {
    .routing-control {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 1000 !important;
    }
}

.routing-control button {
    border: 1px solid #ccc !important;
    border-radius: 3px !important;
    background-color: #f8f9fa !important;
    color: #333 !important;
    font-size: 11px !important;
    padding: 3px 6px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    width: 100% !important;
    margin-bottom: 2px !important;
}

.routing-control button:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
}

.routing-control button.btn-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

.routing-control button.btn-success:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
}

.routing-control button:last-child {
    margin-bottom: 0 !important;
}

#gps-status {
    font-size: 10px !important;
    color: #666 !important;
    text-align: center !important;
    margin-top: 2px !important;
    line-height: 1.2 !important;
    word-wrap: break-word !important;
}

/* Leaflet routing machine customization */
.leaflet-routing-container {
    background-color: rgba(255, 255, 255, 0.95) !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
}

.leaflet-routing-container h3 {
    background-color: #007bff !important;
    color: white !important;
    margin: 0 !important;
    padding: 10px !important;
    border-radius: 4px 4px 0 0 !important;
}

.leaflet-routing-alternatives-container {
    max-height: 200px !important;
    overflow-y: auto !important;
}

/* Additional fixes for routing control visibility */
.leaflet-control-container {
    pointer-events: none;
}

.leaflet-control-container .leaflet-control {
    pointer-events: auto;
}

.leaflet-top.leaflet-left {
    z-index: 1000 !important;
    position: relative !important;
}

.leaflet-control {
    z-index: 1000 !important;
    position: relative !important;
}

/* Ensure routing control is always visible */
#routing-control-container {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 1001 !important;
    position: relative !important;
}

/* Desktop specific fixes */
@media (min-width: 992px) {
    .routing-control {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 1001 !important;
    }

    .leaflet-control-container .routing-control {
        display: block !important;
    }

    #routing-control-container {
        display: block !important;
        visibility: visible !important;
    }
}

/* Fallback routing control styling */
#fallback-routing-control {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    z-index: 1002 !important;
    position: absolute !important;
}

#fallback-routing-control button {
    border: 1px solid #ccc !important;
    border-radius: 3px !important;
    background-color: #f8f9fa !important;
    color: #333 !important;
    font-size: 11px !important;
    padding: 3px 6px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    width: 100% !important;
    margin-bottom: 2px !important;
}

#fallback-routing-control button:hover {
    background-color: #e9ecef !important;
    border-color: #adb5bd !important;
}

#fallback-routing-control button.btn-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

#fallback-routing-control button.btn-success:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
}
</style>

<script>
// Load GeoJSON data for overlays
var geojsonData = <?php echo file_get_contents('application/modules/pelaporan/views/geojson.json'); ?>;
var geojsonKecamatanData = <?php echo file_get_contents('application/modules/pelaporan/views/geojson_kecamatan.json'); ?>;
var geojsonKelurahanData = <?php echo file_get_contents('application/modules/pelaporan/views/geosjon_kelurahan.json'); ?>;

$(function() {
    // Inisialisasi tooltip (konten peta & chart ditangani oleh js_dashboard.php)
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
