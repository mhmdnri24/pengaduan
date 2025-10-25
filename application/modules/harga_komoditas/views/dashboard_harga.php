<!-- Custom CSS for Enhanced Dashboard -->
<style>
.commodity-item {
    transition: all 0.3s ease;
}

.commodity-item:hover {
    background-color: #f5f5f5;
    transform: translateX(5px);
}

.commodity-item.active {
    background-color: #3c8dbc;
    color: white;
}

.commodity-item.active .text-muted {
    color: #e0e0e0 !important;
}

.commodity-item.active .badge {
    background-color: #fff !important;
    color: #3c8dbc !important;
}

.price-row:hover {
    background-color: #f9f9f9;
    cursor: pointer;
}

.callout-sm {
    padding: 8px 12px;
    margin-bottom: 8px;
}

.chart-container canvas {
    max-height: 300px;
}

#commodity-list {
    max-height: 500px;
    overflow-y: auto;
}

.list-group-item {
    border-left: 3px solid transparent;
}

.list-group-item.active {
    border-left-color: #fff;
}

.info-box-number {
    font-weight: bold;
}

/* Hierarchical Table Styles */
.hierarchical-table {
    font-size: 14px;
}

.parent-commodity {
    font-weight: bold;
    background-color: #f8f9fa;
    border-left: 4px solid #3c8dbc;
}

.child-commodity td:first-child {
    padding-left: 35px;
    position: relative;
}

.child-commodity td:first-child:before {
    content: "└─";
    position: absolute;
    left: 15px;
    color: #999;
    font-weight: bold;
}


/* Hierarchical table styling */
.parent-commodity {
    background-color: #f8f9fa;
    font-weight: 600;
}

.child-commodity {
    background-color: #ffffff;
}

.child-commodity td:first-child {
    padding-left: 25px;
}

.parent-commodity:hover,
.child-commodity:hover {
    background-color: #e9ecef;
}

/* Price change styling */
.price-change-up {
    color: #28a745;
    font-weight: 600;
}

.price-change-down {
    color: #dc3545;
    font-weight: 600;
}

.price-change-stable {
    color: #6c757d;
}

/* Trend styling */
.trend-up {
    color: #00ff00;
    font-weight: bold;
}

.trend-down {
    color: #ff4444;
    font-weight: bold;
}

.trend-stable {
    color: #ffff00;
    font-weight: bold;
}

.price-cell {
    text-align: right;
    font-weight: 500;
}

.price-change-up {
    color: #d73925;
}

.price-change-down {
    color: #00a65a;
}

.price-change-stable {
    color: #999;
}

.date-header {
    writing-mode: horizontal-tb;
    text-orientation: initial;
    min-width: 100px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
}

.trend-indicator {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
}

.trend-up {
    background-color: #d73925;
    color: white;
}

.trend-down {
    background-color: #00a65a;
    color: white;
}

.trend-stable {
    background-color: #999;
    color: white;
}

@media (max-width: 768px) {
    .col-md-4, .col-md-8 {
        margin-bottom: 20px;
    }

    .date-header {
        writing-mode: horizontal-tb;
        text-orientation: initial;
        min-width: 80px;
        font-size: 11px;
        font-weight: 600;
    }

    .hierarchical-table {
        font-size: 12px;
    }

    .child-commodity td:first-child {
        padding-left: 25px;
    }

    .child-commodity td:first-child:before {
        left: 10px;
    }
}
</style>

<!-- Enhanced Dashboard Header -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-dashboard"></i> Dashboard Harga Komoditas</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-refresh-dashboard">
                        <i class="fa fa-refresh"></i> Refresh Data
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <!-- Quick Stats -->
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-calendar-check-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Data Hari Ini</span>
                                <span class="info-box-number" id="stat-today"><?= $statistik['total_harga_hari_ini'] ?? 0; ?></span>
                                <span class="progress-description">Total input harga</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-shopping-basket"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Komoditas Aktif</span>
                                <span class="info-box-number" id="stat-commodities"><?= $statistik['total_komoditas_dipantau'] ?? 0; ?></span>
                                <span class="progress-description">Jenis komoditas</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-building-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pasar Dipantau</span>
                                <span class="info-box-number" id="stat-markets"><?= $statistik['total_pasar_dipantau'] ?? 0; ?></span>
                                <span class="progress-description">Lokasi pasar</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Volatilitas Tinggi</span>
                                <span class="info-box-number" id="stat-volatile"><?= count($top_volatil ?? []); ?></span>
                                <span class="progress-description">Komoditas volatil</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analysis Section -->
<div class="row">
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i> Analisis Pasar</h3>
            </div>
            <div class="box-body">
                <div id="market-analysis">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Memuat analisis...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-line-chart"></i> Trend Indikator</h3>
            </div>
            <div class="box-body">
                <div id="trend-indicator">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin"></i> Memuat trend...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Price Disparity Chart -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-area-chart"></i> Grafik Disparitas Harga Antar Komoditas
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Filter Pasar untuk Chart</label>
                            <select class="form-control" id="chart-pasar-filter">
                                <option value="all">Semua Pasar</option>
                                <?php if (!empty($pasar_list)): ?>
                                    <?php foreach ($pasar_list as $pasar): ?>
                                        <option value="<?= $pasar->pasar_id; ?>"><?= $pasar->pasar_nama; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Periode Chart</label>
                            <select class="form-control" id="chart-period-filter">
                                <option value="today" selected>Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" id="chart-custom-date-from" style="display: none;">
                        <div class="form-group">
                            <label>Tanggal Dari</label>
                            <input type="date" class="form-control" id="chart-date-from" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2" id="chart-custom-date-to" style="display: none;">
                        <div class="form-group">
                            <label>Tanggal Sampai</label>
                            <input type="date" class="form-control" id="chart-date-to" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-info btn-block" id="btn-update-chart">
                                <i class="fa fa-refresh"></i> Update Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height: 400px;">
                    <canvas id="disparityChart" width="100%" height="400" style="display: none;"></canvas>
                    <div id="chart-placeholder" class="text-center text-muted" style="padding: 50px;">
                        <i class="fa fa-area-chart fa-3x"></i>
                        <p>Klik "Update Chart" untuk menampilkan grafik disparitas harga antar komoditas</p>
                        <small class="text-muted">Grafik menampilkan variasi harga minimum, rata-rata, dan maksimum untuk komoditas yang memiliki data di beberapa pasar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Filter Data Harga Komoditas</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Filter Pasar</label>
                            <select class="form-control" id="filter-pasar">
                                <option value="all">Semua Pasar</option>
                                <?php if (!empty($pasar_list)): ?>
                                    <?php foreach ($pasar_list as $pasar): ?>
                                        <option value="<?= $pasar->pasar_id; ?>"><?= $pasar->pasar_nama; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Filter Tanggal</label>
                            <select class="form-control" id="filter-tanggal">
                                <option value="today">Hari Ini</option>
                                <option value="week" selected>Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2" id="custom-date-from" style="display: none;">
                        <div class="form-group">
                            <label>Tanggal Dari</label>
                            <input type="date" class="form-control" id="date-from" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2" id="custom-date-to" style="display: none;">
                        <div class="form-group">
                            <label>Tanggal Sampai</label>
                            <input type="date" class="form-control" id="date-to" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary btn-block" id="btn-refresh-data">
                                <i class="fa fa-refresh"></i> Refresh Data
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-success btn-block" id="btn-export-excel">
                                <i class="fa fa-download"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Hierarchical Table -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-table"></i> Tabel Harga Komoditas Hierarki
                </h3>
                <div class="box-tools pull-right">
                    <span class="label label-info" id="data-info">Memuat data...</span>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped hierarchical-table" id="hierarchical-price-table">
                        <thead>
                            <tr id="main-headers">
                                <th style="min-width: 200px;">Nama Komoditas</th>
                                <th style="width: 80px;">Satuan</th>
                                <!-- Date headers will be inserted here -->
                                <th style="width: 100px;">Perubahan (%)</th>
                                <th style="width: 80px;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="hierarchical-table-body">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                                    <p>Memuat data harga komoditas...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Harga -->
<div class="modal fade" id="modal-price-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-info-circle"></i> Detail Harga Komoditas</h4>
            </div>
            <div class="modal-body">
                <div id="price-detail-content">
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Memuat detail harga...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- Load Enhanced JavaScript -->
<?php $this->load->view('harga_komoditas/js_dashboard_enhanced'); ?>
