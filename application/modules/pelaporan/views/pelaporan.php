<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- Leaflet Geocoder CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<!-- Select2 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />

<style>
.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--single {
    height: 34px;
    border: 1px solid #d2d6de;
    border-radius: 0;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 32px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 32px;
}

.leaflet-container {
    z-index: 1;
}

.modal {
    z-index: 1050;
}

.modal-xl {
    width: 95%;
    max-width: 1200px;
}

.tab-content {
    min-height: 400px;
}

.nav-tabs > li > a {
    cursor: pointer;
}

.nav-tabs > li.active > a,
.nav-tabs > li.active > a:hover,
.nav-tabs > li.active > a:focus {
    background-color: #fff;
    border-bottom-color: transparent;
}

/* Leaflet geocoder styling */
.leaflet-control-geocoder {
    background: white;
    border-radius: 6px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    border: 1px solid #ddd;
}

.leaflet-control-geocoder-form {
    padding: 5px;
}

.leaflet-control-geocoder-form input {
    width: 250px !important;
    padding: 8px 12px !important;
    border: 1px solid #ccc !important;
    border-radius: 4px !important;
    font-size: 14px !important;
    outline: none !important;
}

.leaflet-control-geocoder-form input:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
}

.leaflet-control-geocoder-alternatives {
    background: white;
    border-radius: 0 0 6px 6px;
    max-height: 250px;
    overflow-y: auto;
    border-top: 1px solid #eee;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.leaflet-control-geocoder-alternative {
    padding: 10px 12px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    font-size: 13px;
    line-height: 1.4;
}

.leaflet-control-geocoder-alternative:hover {
    background-color: #f8f9fa;
}

.leaflet-control-geocoder-alternative:last-child {
    border-bottom: none;
}

.leaflet-control-geocoder-icon {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktc2VhcmNoIiB2aWV3Qm94PSIwIDAgMTYgMTYiPjxwYXRoIGQ9Im0xMS4zNDIgMTMuNzU4LTMuMjc1LTMuMjc1YTUuNzUgNS43NSAwIDEgMC0xLjQxNiAxLjQxNmwzLjI3NSAzLjI3NWExIDEgMCAwIDAgMS40MTYtMS40MTZ6TTEyIDYuNUE1LjUgNS41IDAgMSAxIDEgNi41YTUuNSA1LjUgMCAwIDEgMTEgMHoiLz48L3N2Zz4=');
}

.location-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    margin-top: 10px;
}

.location-info small {
    color: #6c757d;
}

/* Styling untuk kolom kode laporan */
.kode-laporan-cell {
    line-height: 0.5;
}
.kode-laporan-cell strong {
    display: block;
    font-size: 14px;
    color: #2c3e50;
}
.kode-laporan-cell small {
    color: #6c757d;
    font-size: 12px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-file-text-o"></i> Daftar Laporan Masyarakat</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.pelaporan.dashboard')): ?>
                    <a href="<?= base_url('pelaporan/dashboard') ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                    <?php endif; ?>
                    <?php if (ce_hak_akses('admin.pelaporan.add')): ?>
                    <button type="button" class="btn btn-success btn-sm btn-add">
                        <i class="fa fa-plus"></i> Tambah Laporan
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Filter Section -->
                 <div class="row" style="margin-bottom: 15px;">
                     <div class="col-md-2">
                         <select class="form-control select2" id="filter-status" name="filter_status">
                             <option value="">Semua Status</option>
                             <option value="LAPOR">Lapor</option>
                             <option value="DITERIMA">Diterima</option>
                             <option value="DIKERJAKAN">Dikerjakan</option>
                             <option value="SELESAI">Selesai</option>
                         </select>
                     </div>
                     <div class="col-md-2">
                         <select class="form-control select2" id="filter-kategori" name="filter_kategori">
                             <option value="">Semua Kategori</option>
                         </select>
                     </div>
                     <div class="col-md-2">
                         <select class="form-control select2" id="filter-prioritas" name="filter_prioritas">
                             <option value="">Semua Prioritas</option>
                             <option value="URGENT">Urgent</option>
                             <option value="TINGGI">Tinggi</option>
                             <option value="SEDANG">Sedang</option>
                             <option value="RENDAH">Rendah</option>
                         </select>
                     </div>
                     <?php if ($this->session->userdata('id_level') == 1): ?>
                     <div class="col-md-4">
                         <select class="form-control select2" id="filter-unitkerja" name="filter_unitkerja">
                             <option value="">Semua Unit Kerja</option>
                             <?php if (!empty($unitkerja_options)): ?>
                                 <?php foreach ($unitkerja_options as $unit): ?>
                                     <option value="<?= $unit->id_unitkerja ?>"><?= $unit->unitkerja ?></option>
                                 <?php endforeach; ?>
                             <?php endif; ?>
                         </select>
                     </div>
                     <div class="col-md-2">
                         <!-- <button type="button" class="btn btn-default btn-sm" id="btn-reset-filter">
                             <i class="fa fa-refresh"></i> Reset Filter
                         </button> -->
                         <?php if (ce_hak_akses('admin.pelaporan.export')): ?>
                         <button type="button" class="btn btn-warning btn-sm" id="btn-export">
                             <i class="fa fa-download"></i> Export
                         </button>
                         <?php endif; ?>
                     </div>
                     <?php else: ?>
                     <div class="col-md-2">
                         <!-- <button type="button" class="btn btn-default btn-sm" id="btn-reset-filter">
                             <i class="fa fa-refresh"></i> Reset Filter
                         </button> -->
                         <?php if (ce_hak_akses('admin.pelaporan.export')): ?>
                         <button type="button" class="btn btn-warning btn-sm" id="btn-export">
                             <i class="fa fa-download"></i> Export
                         </button>
                         <?php endif; ?>
                     </div>
                     <?php endif; ?>
                 </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-pelaporan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="18%">Kode Laporan</th>
                                <th width="20%">Judul</th>
                                <th width="15%">Kategori</th>
                                <th width="10%">Status</th>
                                <th width="13%">Tanggal</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Laporan -->
<div class="modal fade" id="modal-laporan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-title"><i class="fa fa-plus"></i> Tambah Laporan</h4>
            </div>
            <form id="form-laporan" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id" id="laporan-id">
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab-info" role="tab" data-toggle="tab">
                                <i class="fa fa-info-circle"></i> Informasi Laporan
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab-pelapor" role="tab" data-toggle="tab">
                                <i class="fa fa-user"></i> Data Pelapor
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab-location" role="tab" data-toggle="tab">
                                <i class="fa fa-map-marker"></i> Lokasi & Peta
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" style="margin-top: 15px;">
                        <!-- Tab Informasi Laporan -->
                        <div role="tabpanel" class="tab-pane active" id="tab-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="judul">Judul Laporan <span class="text-red">*</span></label>
                                        <input type="text" class="form-control" id="judul" name="judul" placeholder="Contoh: Jalan Rusak di Jl. Sudirman" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kategori">Kategori <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="kategori" name="kategori" required>
                                            <option value="">Pilih Kategori</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="deskripsi">Deskripsi Laporan <span class="text-red">*</span></label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan detail masalah yang dilaporkan..." required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prioritas">Prioritas</label>
                                        <select class="form-control select2" id="prioritas" name="prioritas">
                                            <option value="RENDAH">🟢 Rendah</option>
                                            <option value="SEDANG" selected>🟡 Sedang</option>
                                            <option value="TINGGI">🟠 Tinggi</option>
                                            <option value="URGENT">🔴 Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="alamat">Alamat Kejadian <span class="text-red">*</span></label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap lokasi kejadian" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Data Pelapor -->
                        <div role="tabpanel" class="tab-pane" id="tab-pelapor">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="masyarakat_id">Pilih Masyarakat</label>
                                        <select class="form-control select2" id="masyarakat_id" name="masyarakat_id">
                                            <option value="">Pilih dari data masyarakat</option>
                                        </select>
                                        <small class="help-block">Pilih dari data masyarakat yang sudah terdaftar atau isi manual di bawah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pelapor_nama">Nama Pelapor <span class="text-red">*</span></label>
                                        <input type="text" class="form-control" id="pelapor_nama" name="pelapor_nama" placeholder="Nama lengkap pelapor" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pelapor_nik">NIK</label>
                                        <input type="text" class="form-control" id="pelapor_nik" name="pelapor_nik" placeholder="16 digit NIK" maxlength="16">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pelapor_telepon">Telepon</label>
                                        <input type="text" class="form-control" id="pelapor_telepon" name="pelapor_telepon" placeholder="Nomor telepon/HP">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="pelapor_alamat">Alamat Pelapor</label>
                                        <textarea class="form-control" id="pelapor_alamat" name="pelapor_alamat" rows="3" placeholder="Alamat lengkap pelapor"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab Lokasi & Peta -->
                        <div role="tabpanel" class="tab-pane" id="tab-location">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lokasi_lat">Latitude</label>
                                        <input type="text" class="form-control" id="lokasi_lat" name="lokasi_lat" placeholder="Contoh: -2.9760285" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lokasi_lng">Longitude</label>
                                        <input type="text" class="form-control" id="lokasi_lng" name="lokasi_lng" placeholder="Contoh: 104.7754794" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Pilih Lokasi pada Peta <small class="text-muted">(Klik pada peta untuk menentukan lokasi atau gunakan pencarian)</small></label>
                                <div id="map" style="height: 400px; border: 1px solid #ddd; border-radius: 4px;"></div>
                                <div class="location-info" id="location-info" style="display: none;">
                                    <strong>Lokasi Terpilih:</strong>
                                    <div id="selected-location"></div>
                                    <small>Koordinat: <span id="selected-coordinates"></span></small>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-info btn-sm" id="btn-detect-location">
                                    <i class="fa fa-crosshairs"></i> Deteksi Lokasi Saya
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" id="btn-clear-location">
                                    <i class="fa fa-times"></i> Hapus Lokasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modal-status" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-refresh"></i> Update Status Laporan</h4>
            </div>
            <form id="form-status" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id" id="status-id">
                    
                    <div class="form-group">
                        <label for="status">Status Baru <span class="text-red">*</span></label>
                        <select class="form-control select2" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan perubahan status..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-refresh"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export -->
<div class="modal fade" id="modal-export" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-download"></i> Export Data Laporan</h4>
            </div>
            <form id="form-export" method="get" action="<?= base_url('pelaporan/export') ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="export_format">Format Export</label>
                        <select class="form-control select2" id="export_format" name="format">
                            <option value="excel">📊 Excel (.xlsx)</option>
                            <option value="pdf">📄 PDF</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="export_status">Filter Status</label>
                        <select class="form-control select2" id="export_status" name="status">
                            <option value="">Semua Status</option>
                            <option value="LAPOR">Lapor</option>
                            <option value="DITERIMA">Diterima</option>
                            <option value="DIKERJAKAN">Dikerjakan</option>
                            <option value="SELESAI">Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="export_kategori">Filter Kategori</label>
                        <select class="form-control select2" id="export_kategori" name="kategori">
                            <option value="">Semua Kategori</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Leaflet Geocoder JS -->
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>