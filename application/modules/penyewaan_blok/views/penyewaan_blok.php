<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
.select2-container {
    width: 100% !important;
}
.select2-container .select2-selection--single {
    height: 34px !important;
    border: 1px solid #d2d6de !important;
    border-radius: 4px !important;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important;
    padding-left: 12px !important;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 32px !important;
}

/* Denah Styles */
.denah-container {
    border: 1px solid #d2d6de;
    border-radius: 4px;
    padding: 15px;
    background-color: #f9f9f9;
    margin-top: 10px;
    max-width: 100%;
    overflow-x: auto; /* Allow horizontal scroll if needed */
    overflow-y: visible;
}

.denah-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.denah-header h4 {
    margin: 0;
    color: #333;
    font-size: 14px;
}

.denah-legend {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 2px;
    border: 1px solid #ccc;
}

.legend-color.available {
    background-color: #5cb85c;
}

.legend-color.occupied {
    background-color: #d9534f;
}

.legend-color.selected {
    background-color: #f0ad4e;
    border: 2px solid #f39c12;
}

.denah-grid {
    position: relative;
    min-height: 300px;
    max-height: 400px;
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    overflow: visible; /* Changed from auto to visible for better scaling */
    padding: 20px;
    max-width: 100%;
    width: 100%;
    box-sizing: border-box;
    margin: 0 auto; /* Center the grid */
}

/* Responsive denah for modal */
@media (max-width: 1200px) {
    .denah-grid {
        max-height: 350px;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .denah-grid {
        max-height: 300px;
        padding: 10px;
    }

    .modal-dialog.modal-lg {
        width: 95%;
        margin: 10px auto;
    }

    .denah-container {
        padding: 10px;
    }

    .denah-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}

.denah-block {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 4px;
    border: 2px solid #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    z-index: 10;
    background-color: #666; /* Default background */
    min-width: 40px; /* Minimum width for readability */
    min-height: 40px; /* Minimum height for readability */
    text-align: center;
    word-wrap: break-word;
    overflow: hidden;
}

.denah-block.available {
    background-color: #5cb85c;
    border-color: #4cae4c;
}

.denah-block.available:hover {
    background-color: #4cae4c;
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.denah-block.occupied {
    background-color: #d9534f;
    border-color: #d43f3a;
    cursor: not-allowed;
    opacity: 0.7;
}

.denah-block.selected {
    background-color: #f0ad4e;
    border-color: #f39c12;
    border-width: 3px;
    box-shadow: 0 0 0 2px #f39c12;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 2px #f39c12; }
    50% { box-shadow: 0 0 0 6px rgba(243, 156, 18, 0.3); }
    100% { box-shadow: 0 0 0 2px #f39c12; }
}

.denah-block-info {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.denah-block:hover .denah-block-info {
    opacity: 1;
}

.denah-placeholder {
    text-align: center;
    padding: 40px;
    color: #666;
}

/* Modal improvements for denah */
.modal-lg .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.denah-container {
    max-width: 100%;
    overflow-x: auto;
}

/* Responsive modal for denah */
@media (min-width: 1200px) {
    .modal-dialog.modal-lg {
        width: 95%;
        max-width: 1200px;
    }
}

/* Denah scale info styling */
.denah-scale-info {
    position: absolute !important;
    top: 5px !important;
    right: 10px !important;
    background: rgba(0,0,0,0.7) !important;
    color: white !important;
    padding: 3px 8px !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    z-index: 100 !important;
}

/* Unit indicator for blocks */
.unit-indicator {
    position: absolute;
    top: 2px;
    right: 2px;
    background: rgba(255,255,255,0.9);
    color: #333;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
}

/* Unit blocks styling */
.unit-block {
    cursor: pointer;
    transition: all 0.2s ease;
}

.unit-block.available {
    background-color: #5cb85c;
    border-color: #4cae4c;
}

.unit-block.available:hover {
    background-color: #4cae4c;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.unit-block.occupied {
    background-color: #d9534f;
    border-color: #d43f3a;
    cursor: not-allowed;
    opacity: 0.7;
}

/* Modal denah unit adjustments */
#modal-denah-unit .denah-grid {
    min-height: 200px;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: auto;
    width: 100%;
    height: auto;
}

#modal-denah-unit .denah-container {
    max-height: 450px;
    overflow-y: auto;
    overflow-x: auto;
    width: 100%;
}

/* Ensure units are visible in modal */
#modal-denah-unit .modal-body {
    max-height: 500px;
    overflow: auto;
}

/* Unit block positioning fix */
.unit-block {
    position: relative !important;
    display: inline-block !important;
    margin: 5px !important;
    float: left !important;
}

/* Fix z-index for modal denah unit */
#modal-denah-unit {
    z-index: 1050;
}

#modal-denah-unit .modal-dialog {
    z-index: 1051;
}

#modal-denah-unit .modal-content {
    z-index: 1052;
}

/* Ensure backdrop is above modal-form */
#modal-denah-unit + .modal-backdrop {
    z-index: 1049;
}

/* Selection Step Styles */
.selection-step {
    margin-bottom: 20px;
}

.step-header {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
}

.step-header h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 16px;
}

.step-info {
    color: #666;
    font-size: 14px;
    margin-bottom: 0;
}

#btn-back-to-blok {
    margin-bottom: 10px;
}

/* Selected Unit Info */
#selected-unit-info {
    margin-top: 15px;
}

#selected-unit-text {
    font-weight: bold;
}
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                    <?php if (isset($pedagang) && $pedagang): ?>
                        Penyewaan Blok - <?= $pedagang->nama_lengkap; ?>
                    <?php else: ?>
                        Data Penyewaan Blok Pasar
                    <?php endif; ?>
                </h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.penyewaan_blok.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fa fa-plus"></i> Tambah Penyewaan
                    </button>
                    <?php endif; ?>
                    <?php if (!isset($pedagang_id) || !$pedagang_id): ?>
                    <a href="<?= base_url('penyewaan_blok'); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-list"></i> Semua Penyewaan
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <?php if (isset($pedagang) && $pedagang): ?>
                <!-- Info Pedagang -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pedagang: <?= $pedagang->nama_lengkap; ?></span>
                                <span class="info-box-number">NIK: <?= $pedagang->nik; ?> | Telp: <?= $pedagang->no_telpon ?: '-'; ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 0%"></div>
                                </div>
                                <span class="progress-description">
                                    Alamat: <?= $pedagang->alamat ?: '-'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Penyewaan</span>
                                <span class="info-box-number"><?= $stats['total_penyewaan'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Aktif</span>
                                <span class="info-box-number"><?= $stats['status_aktif'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Menunggu</span>
                                <span class="info-box-number"><?= $stats['status_menunggu'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kadaluarsa</span>
                                <span class="info-box-number"><?= $stats['expired_rentals'] ?? 0; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter-status" style="width: 100%;">
                            <option value="">Semua Status</option>
                            <?php
                            $rental_status = $this->config->item('rental_status');
                            if ($rental_status && is_array($rental_status)) {
                                foreach ($rental_status as $key => $value) {
                                    echo '<option value="' . $key . '">' . $value . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default btn-block" id="btn-reset-filter">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Tanggal Sewa</th>
                                <?php if (!isset($pedagang_id) || !$pedagang_id): ?>
                                <th>Pedagang</th>
                                <?php endif; ?>
                                <th>Pasar</th>
                                <th>Jenis</th>
                                <th>Blok</th>
                                <th>Periode</th>
                                <th>Harga Sewa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modal-form" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 95%; width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-building"></i> Form Penyewaan Blok
                </h4>
            </div>
            <form id="form-penyewaan" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="pasar_unit_id" name="pasar_unit_id">
                    <?php if (isset($pedagang_id) && $pedagang_id): ?>
                    <input type="hidden" id="pedagang_id" name="pedagang_id" value="<?= $pedagang_id; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <?php if (!isset($pedagang_id) || !$pedagang_id): ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="pedagang_id">Pedagang <span class="text-red">*</span></label>
                                <select class="form-control select2" id="pedagang_id" name="pedagang_id" style="width: 100%;" required>
                                    <option value="">Pilih Pedagang</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_id">Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" id="pasar_id" name="pasar_id" style="width: 100%;" required>
                                    <option value="">Pilih Pasar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_id">Jenis Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" id="jenis_id" name="jenis_id" style="width: 100%;" required>
                                    <option value="">Pilih Jenis Pasar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="pasar_blok_id">Pilih Unit Pasar <span class="text-red">*</span></label>
                                <input type="hidden" id="pasar_blok_id" name="pasar_blok_id" required>
                                
                                <!-- Step 1: Denah Blok -->
                                <div id="step-blok" class="selection-step step-blok">
                                    <div class="step-header">
                                        <h4><i class="fa fa-map"></i> Langkah 1: Pilih Blok</h4>
                                        <div class="step-info">Silakan pilih blok yang tersedia pada denah di bawah ini</div>
                                    </div>
                                    <div id="denah-container" class="denah-container" style="display: none;">
                                        <div class="denah-header">
                                            <h4 id="denah-title">Denah Pasar</h4>
                                            <div class="denah-legend">
                                                <div class="legend-item">
                                                    <div class="legend-color available"></div>
                                                    <span>Blok Tersedia</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color occupied"></div>
                                                    <span>Blok Tidak Tersedia</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color selected"></div>
                                                    <span>Blok Dipilih</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="denah-grid" class="denah-grid">
                                            <!-- Denah blocks will be loaded here -->
                                        </div>
                                    </div>
                                    <div id="denah-placeholder" class="alert alert-info">
                                        <i class="fa fa-info-circle"></i> Pilih jenis pasar dan isi tanggal mulai/selesai untuk melihat denah blok yang tersedia.
                                    </div>
                                </div>
                                
                                <!-- Step 2: Denah Unit -->
                                <div id="step-unit" class="selection-step step-unit" style="display: none;">
                                    <div class="step-header">
                                        <button type="button" class="btn btn-sm btn-default" id="btn-back-to-blok">
                                            <i class="fa fa-arrow-left"></i> Kembali ke Pilih Blok
                                        </button>
                                        <h4><i class="fa fa-th"></i> Langkah 2: Pilih Unit</h4>
                                        <div class="step-info" id="selected-blok-info">Blok: -</div>
                                    </div>
                                    <div id="denah-unit-container" class="denah-container">
                                        <div class="denah-header">
                                            <h4 id="denah-unit-title">Denah Unit</h4>
                                            <div class="denah-legend">
                                                <div class="legend-item">
                                                    <div class="legend-color available"></div>
                                                    <span>Tersedia</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color occupied"></div>
                                                    <span>Terisi</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color selected"></div>
                                                    <span>Dipilih</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="denah-unit-grid" class="denah-grid">
                                            <!-- Units will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Selected Unit Info -->
                                <div id="selected-unit-info" class="alert alert-success unit-info" style="display: none;">
                                    <i class="fa fa-check-circle"></i>
                                    <strong>Unit Dipilih:</strong> <span id="selected-unit-text">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal_sewa">Tanggal Sewa <span class="text-red">*</span></label>
                                <input type="date" class="form-control" id="tanggal_sewa" name="tanggal_sewa" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="harga_sewa">Harga Sewa <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="harga_sewa" name="harga_sewa" placeholder="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status <span class="text-red">*</span></label>
                                <select class="form-control select2" id="status" name="status" style="width: 100%;" required>
                                    <?php
                                    $rental_status = $this->config->item('rental_status');
                                    if ($rental_status && is_array($rental_status)) {
                                        foreach ($rental_status as $key => $value) {
                                            echo '<option value="' . $key . '">' . $value . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai">Tanggal Mulai <span class="text-red">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_selesai">Tanggal Selesai <span class="text-red">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Denah Unit -->
<div class="modal fade" id="modal-denah-unit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-th"></i> <span id="modal-unit-title">Pilih Unit</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="denah-container">
                    <div class="denah-header">
                        <h4 id="denah-unit-title">Denah Unit</h4>
                        <div class="denah-legend">
                            <div class="legend-item">
                                <div class="legend-color available"></div>
                                <span>Tersedia</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color occupied"></div>
                                <span>Terisi</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color selected"></div>
                                <span>Dipilih</span>
                            </div>
                        </div>
                    </div>
                    <div id="denah-unit-grid" class="denah-grid">
                        <!-- Units will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-eye"></i> Detail Penyewaan Blok
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Pedagang</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-pedagang">-</td>
                            </tr>
                            <tr>
                                <td><strong>NIK</strong></td>
                                <td>:</td>
                                <td id="detail-nik">-</td>
                            </tr>
                            <tr>
                                <td><strong>No. Telepon</strong></td>
                                <td>:</td>
                                <td id="detail-telepon">-</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Sewa</strong></td>
                                <td>:</td>
                                <td id="detail-tanggal-sewa">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Pasar</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-pasar">-</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Pasar</strong></td>
                                <td>:</td>
                                <td id="detail-jenis">-</td>
                            </tr>
                            <tr>
                                <td><strong>Blok</strong></td>
                                <td>:</td>
                                <td id="detail-blok">-</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:</td>
                                <td id="detail-status">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Periode Sewa</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-periode">-</td>
                            </tr>
                            <tr>
                                <td><strong>Harga Sewa</strong></td>
                                <td>:</td>
                                <td id="detail-harga">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Dibuat</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-created">-</td>
                            </tr>
                            <tr>
                                <td><strong>Diupdate</strong></td>
                                <td>:</td>
                                <td id="detail-updated">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>