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
.input-group-addon {
    background-color: #f4f4f4;
}
.target-card {
    transition: all 0.3s ease;
    border-left: 4px solid #3c8dbc;
}
.target-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.target-card.success {
    border-left-color: #00a65a;
}
.target-card.warning {
    border-left-color: #f39c12;
}
.target-card.danger {
    border-left-color: #dd4b39;
}
.percentage-badge {
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 10px;
}
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Target Retribusi</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.retribusi_target.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fa fa-plus"></i> Tambah Data
                    </button>
                    <?php endif; ?>
                    <a href="<?= base_url('retribusi_target/dashboard') ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-bullseye"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Target</span>
                                <span class="info-box-number"><?= $total_target; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Target Tahunan</span>
                                <span class="info-box-number"><?= $target_tahunan; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-calendar-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Target Bulanan</span>
                                <span class="info-box-number"><?= $target_bulanan; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter-tahun" style="width: 100%;">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter-jenis" style="width: 100%;">
                            <option value="">Semua Jenis</option>
                            <option value="tahunan">Tahunan</option>
                            <option value="bulanan">Bulanan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter-retribusi" style="width: 100%;">
                            <option value="">Semua Retribusi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default btn-block" id="btn-reset-filter">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Quick Input Cards -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Input Cepat:</strong> 
                            Klik tombol "Input Capaian" pada setiap baris untuk memperbarui capaian tanpa membuka form edit.
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Retribusi</th>
                                <th>Tahun</th>
                                <th>Periode</th>
                                <th>Target</th>
                                <th>Capaian</th>
                                <th>Persentase</th>
                                <th>Keterangan</th>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-bullseye"></i> Form Target Retribusi
                </h4>
            </div>
            <form id="form-target" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_retribusi">Jenis Retribusi <span class="text-red">*</span></label>
                                <select class="form-control select2" id="id_retribusi" name="id_retribusi" required>
                                    <option value="">Pilih Jenis Retribusi</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tahun">Tahun <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="tahun" name="tahun" placeholder="Contoh: 2025" min="2000" max="2100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_target">Jenis Target <span class="text-red">*</span></label>
                                <select class="form-control select2" id="jenis_target" name="jenis_target" required>
                                    <option value="">Pilih Jenis Target</option>
                                    <option value="tahunan">Tahunan</option>
                                    <option value="bulanan">Bulanan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="bulan-container" style="display: none;">
                            <div class="form-group">
                                <label for="bulan">Bulan <span class="text-red">*</span></label>
                                <select class="form-control select2" id="bulan" name="bulan">
                                    <option value="">Pilih Bulan</option>
                                    <option value="Januari">Januari</option>
                                    <option value="Februari">Februari</option>
                                    <option value="Maret">Maret</option>
                                    <option value="April">April</option>
                                    <option value="Mei">Mei</option>
                                    <option value="Juni">Juni</option>
                                    <option value="Juli">Juli</option>
                                    <option value="Agustus">Agustus</option>
                                    <option value="September">September</option>
                                    <option value="Oktober">Oktober</option>
                                    <option value="November">November</option>
                                    <option value="Desember">Desember</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target">Target <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" class="form-control" id="target" name="target" placeholder="Masukkan target" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capaian">Capaian</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" class="form-control" id="capaian" name="capaian" placeholder="Masukkan capaian" min="0" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Contoh: SURPLUS, DEFISIT, dll">
                    </div>

                    <!-- Auto Calculate Info -->
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <strong>Info:</strong> 
                        Persentase akan dihitung otomatis berdasarkan rumus: (Capaian / Target) × 100%
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

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-eye"></i> Detail Target Retribusi
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Jenis Retribusi</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-nama_retribusi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Retribusi</strong></td>
                                <td>:</td>
                                <td id="detail-kode_retribusi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Tahun</strong></td>
                                <td>:</td>
                                <td id="detail-tahun">-</td>
                            </tr>
                            <tr>
                                <td><strong>Periode</strong></td>
                                <td>:</td>
                                <td id="detail-periode">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Target</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-target">-</td>
                            </tr>
                            <tr>
                                <td><strong>Capaian</strong></td>
                                <td>:</td>
                                <td id="detail-capaian">-</td>
                            </tr>
                            <tr>
                                <td><strong>Persentase</strong></td>
                                <td>:</td>
                                <td id="detail-persentase">-</td>
                            </tr>
                            <tr>
                                <td><strong>Keterangan</strong></td>
                                <td>:</td>
                                <td id="detail-keterangan">-</td>
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

<!-- Modal Input Capaian -->
<div class="modal fade" id="modal-capaian" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-line-chart"></i> Input Capaian
                </h4>
            </div>
            <form id="form-capaian">
                <div class="modal-body">
                    <input type="hidden" id="capaian-id" name="id">
                    
                    <div class="form-group">
                        <label>Retribusi</label>
                        <p class="form-control-static" id="capaian-retribusi">-</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Periode</label>
                        <p class="form-control-static" id="capaian-periode">-</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="capaian-value">Capaian <span class="text-red">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="number" class="form-control" id="capaian-value" name="capaian" placeholder="Masukkan capaian" min="0" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="capaian-keterangan">Keterangan</label>
                        <input type="text" class="form-control" id="capaian-keterangan" name="keterangan" placeholder="Contoh: SURPLUS, DEFISIT, dll">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Update Capaian</button>
                </div>
            </form>
        </div>
    </div>
</div>