<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- Leaflet Geocoder CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<!-- Dropzone CSS -->
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

<style>
.dropzone {
   border: 2px dashed #ccc !important;
   border-radius: 10px;
   background: white !important;
   position: relative; /* Required for z-index */
   z-index: 9999 !important; /* Force it to be on top */
   text-align: center;
   padding: 20px;
   transition: border-color 0.3s ease;
   min-height: 150px;
   cursor: pointer !important;
}

.dropzone:hover {
    border-color: #007bff !important;
}

.dropzone.dz-drag-hover {
    border-color: #007bff !important;
    background-color: #f8f9fa !important;
}

.dropzone .dz-message {
    margin: 2em 0;
    pointer-events: none;
    font-weight: normal;
}

.dropzone .dz-message i {
    display: block;
    margin-bottom: 10px;
}

/* Dropzone preview styling */
.dropzone .dz-preview {
    margin: 10px;
}

.dropzone .dz-preview .dz-image {
    border-radius: 5px;
}

.dropzone .dz-preview .dz-remove {
    text-decoration: none;
    color: #dc3545;
    font-weight: bold;
}

.dropzone .dz-preview .dz-remove:hover {
    color: #c82333;
}

.photo-item {
    position: relative;
    margin-bottom: 15px;
}

.photo-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.photo-actions {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.7);
    border-radius: 3px;
    padding: 2px;
}

.photo-actions .btn {
    padding: 2px 6px;
    margin: 1px;
    font-size: 10px;
}

.map-container {
    z-index: 1;
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

/* Tab styling improvements */
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

/* Geocoder icon styling */
.leaflet-control-geocoder-icon {
    background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktc2VhcmNoIiB2aWV3Qm94PSIwIDAgMTYgMTYiPjxwYXRoIGQ9Im0xMS4zNDIgMTMuNzU4LTMuMjc1LTMuMjc1YTUuNzUgNS43NSAwIDEgMC0xLjQxNiAxLjQxNmwzLjI3NSAzLjI3NWExIDEgMCAwIDAgMS40MTYtMS40MTZ6TTEyIDYuNUE1LjUgNS41IDAgMSAxIDEgNi41YTUuNSA1LjUgMCAwIDEgMTEgMHoiLz48L3N2Zz4=');
}
.select2-container {
    width: 100% !important;
}

/* Fix Select2 z-index in modal */
.select2-container--open {
    z-index: 999999 !important;
}

.select2-dropdown {
    z-index: 999999 !important;
}

/* Fix Select2 in modal */
.modal .select2-container {
    width: 100% !important;
}

.modal .select2-selection {
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 34px;
}

.modal .select2-selection__rendered {
    padding: 6px 12px;
}

.modal .select2-selection__arrow {
    height: 32px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Fasilitas <small>Umum</small></h3>
                <div class="box-tools">
                    <?php if (ce_hak_akses('admin.fasilitas_umum.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalFasilitas">
                        <i class="fa fa-plus"></i> Tambah Fasilitas
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Fasilitas</span>
                                <span class="info-box-number" id="total-fasilitas">
                                    <?php
                                    $this->load->model('fasilitas_umum_m');
                                    echo $this->fasilitas_umum_m->count_all_fasilitas();
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fasilitas Aktif</span>
                                <span class="info-box-number" id="fasilitas-aktif">
                                    <?= $this->fasilitas_umum_m->count_active_fasilitas(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Fasilitas Nonaktif</span>
                                <span class="info-box-number" id="fasilitas-nonaktif">
                                    <?= $this->fasilitas_umum_m->count_inactive_fasilitas(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-map-marker"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kota Palembang</span>
                                <span class="info-box-number">16.73</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kategori">
                            <option value="">-- Semua Kategori --</option>
                            <?php
                            $this->load->model('master_kategori_m');
                            $kategori = $this->master_kategori_m->kategori_get_active();
                            // Tampilkan kategori parent terlebih dahulu
                            $parent_categories = array_filter($kategori, function($k) { return $k->parent_id == null; });
                            foreach ($parent_categories as $parent) : ?>
                                <optgroup label="<?= $parent->nama_kategori; ?>">
                                    <option value="<?= $parent->id; ?>"><?= $parent->nama_kategori; ?></option>
                                    <?php
                                    // Tampilkan kategori child
                                    $child_categories = array_filter($kategori, function($k) use ($parent) { return $k->parent_id == $parent->id; });
                                    foreach ($child_categories as $child) : ?>
                                        <option value="<?= $child->id; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?= $child->nama_kategori; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kecamatan">
                            <option value="">-- Semua Kecamatan --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kelurahan">
                            <option value="">-- Semua Kelurahan --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Nama Fasilitas</th>
                                <th>Kategori</th>
                                <th>Alamat</th>
                                <th>Kelurahan</th>
                                <th>Telepon</th>
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

<!-- Modal Fasilitas -->
<div class="modal fade" id="modalFasilitas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalFasilitasLabel">Tambah Fasilitas</h4>
            </div>
            <form id="formFasilitas">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab-info" role="tab" data-toggle="tab">
                                <i class="fa fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab-location" role="tab" data-toggle="tab">
                                <i class="fa fa-map-marker"></i> Lokasi & Map
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab-photos" role="tab" data-toggle="tab">
                                <i class="fa fa-camera"></i> Foto
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" style="margin-top: 15px;">
                        <!-- Tab Informasi Dasar -->
                        <div role="tabpanel" class="tab-pane active" id="tab-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_fasilitas">Nama Fasilitas <span class="text-red">*</span></label>
                                        <input type="text" class="form-control" id="nama_fasilitas" name="nama_fasilitas" placeholder="Contoh: Rumah Sakit Umum" maxlength="100" required>
                                        <small class="help-block">Maksimal 100 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kategori_id">Kategori <span class="text-red">*</span></label>
                                        <select class="form-control select2" id="kategori_id" name="kategori_id" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php
                                            $this->load->model('master_kategori_m');
                                            $kategori = $this->master_kategori_m->kategori_get_active();
                                            // Tampilkan kategori parent terlebih dahulu
                                            $parent_categories = array_filter($kategori, function($k) { return $k->parent_id == null; });
                                            foreach ($parent_categories as $parent) : ?>
                                                <optgroup label="<?= $parent->nama_kategori; ?>">
                                                    <option value="<?= $parent->id; ?>"><?= $parent->nama_kategori; ?></option>
                                                    <?php
                                                    // Tampilkan kategori child
                                                    $child_categories = array_filter($kategori, function($k) use ($parent) { return $k->parent_id == $parent->id; });
                                                    foreach ($child_categories as $child) : ?>
                                                        <option value="<?= $child->id; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?= $child->nama_kategori; ?></option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi fasilitas (opsional)"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telepon">Telepon</label>
                                        <input type="text" class="form-control" id="telepon" name="telepon" placeholder="Nomor telepon">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" id="status" name="status" value="1" checked> Status Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab Lokasi & Map -->
                        <div role="tabpanel" class="tab-pane" id="tab-location">
                            <div class="form-group">
                                <label for="alamat">Alamat Lengkap</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap fasilitas"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_kecamatan">Kecamatan</label>
                                        <select class="form-control select2" id="id_kecamatan" name="id_kecamatan">
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_kelurahan">Kelurahan</label>
                                        <select class="form-control select2" id="id_kelurahan" name="id_kelurahan">
                                            <option value="">Pilih Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="latitude">Latitude</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Contoh: -2.9760285" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="longitude">Longitude</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Contoh: 104.7754794" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Pilih Lokasi pada Map <small class="text-muted">(Klik pada map untuk menentukan lokasi)</small></label>
                                <div id="map" style="height: 400px; border: 1px solid #ddd;"></div>
                            </div>
                        </div>
                        
                        <!-- Tab Foto -->
                        <div role="tabpanel" class="tab-pane" id="tab-photos">
                            <div class="form-group">
                                <label>Upload Foto Fasilitas</label>
                               <div class="dropzone" id="foto-dropzone">
                                   <div class="dz-message">
                                       <i class="fa fa-cloud-upload" style="font-size: 48px; color: #ccc;"></i>
                                       <p>Klik atau drag foto ke sini untuk upload</p>
                                       <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 5MB per file. Multiple files allowed.</small>
                                   </div>
                               </div>
                            </div>

                            <div class="form-group">
                                <label>Foto yang sudah diupload</label>
                                <div id="photo-gallery" class="row">
                                    <!-- Photos will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan
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
