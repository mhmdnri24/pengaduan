<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<style>
.dropzone {
   border: 2px dashed #ccc !important;
   border-radius: 10px;
   background: white !important;
   position: relative;
   z-index: 9999 !important;
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

.slider-preview {
    position: relative;
    margin-bottom: 15px;
}

.slider-preview img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.slider-actions {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.7);
    border-radius: 3px;
    padding: 2px;
}

.slider-actions .btn {
    padding: 2px 6px;
    margin: 1px;
    font-size: 10px;
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
                <h3 class="box-title">Slider <small>Manajemen Slider</small></h3>
                <div class="box-tools">
                    <?php if (ce_hak_akses('admin.slider.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalSlider">
                        <i class="fa fa-plus"></i> Tambah Slider
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-image"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Slider</span>
                                <span class="info-box-number" id="total-slider">
                                    <?php
                                    echo $this->slider_m->count_all_slider();
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Slider Aktif</span>
                                <span class="info-box-number" id="slider-aktif">
                                    <?= $this->slider_m->count_active_slider(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Slider Nonaktif</span>
                                <span class="info-box-number" id="slider-nonaktif">
                                    <?= $this->slider_m->count_inactive_slider(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Update Terakhir</span>
                                <span class="info-box-number" id="update-terakhir">
                                    <?php
                                    $this->db->select_max('updated_at');
                                    $max_update = $this->db->get('master_slider')->row();
                                    echo $max_update && $max_update->updated_at ? date('d/m', strtotime($max_update->updated_at)) : '-';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <?php
                            $status_config = $this->config->item('slider_status');
                            foreach ($status_config as $key => $value): ?>
                                <option value="<?= $key ?>"><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                                <th>Judul Slider</th>
                                <th>Deskripsi</th>
                                <th>Gambar</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
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

<!-- Modal Slider -->
<div class="modal fade" id="modalSlider" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalSliderLabel">Tambah Slider</h4>
            </div>
            <form id="formSlider">
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
                            <a href="#tab-image" role="tab" data-toggle="tab">
                                <i class="fa fa-image"></i> Gambar Slider
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" style="margin-top: 15px;">
                        <!-- Tab Informasi Dasar -->
                        <div role="tabpanel" class="tab-pane active" id="tab-info">
                            <div class="form-group">
                                <label for="slider_judul">Judul Slider <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="slider_judul" name="slider_judul" placeholder="Contoh: Promosi Spesial" maxlength="255" required>
                                <small class="help-block">Maksimal 255 karakter</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="slider_deskripsi">Deskripsi</label>
                                <textarea class="form-control" id="slider_deskripsi" name="slider_deskripsi" rows="3" placeholder="Deskripsi slider (opsional)"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="slider_status">Status <span class="text-red">*</span></label>
                                <select class="form-control select2" id="slider_status" name="slider_status" required>
                                    <option value="">Pilih Status</option>
                                    <?php
                                    $status_config = $this->config->item('slider_status');
                                    foreach ($status_config as $key => $value): ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Tab Gambar Slider -->
                        <div role="tabpanel" class="tab-pane" id="tab-image">
                            <div class="form-group">
                                <label>Upload Gambar Slider</label>
                                <div class="dropzone" id="image-dropzone">
                                    <div class="dz-message">
                                        <i class="fa fa-cloud-upload" style="font-size: 48px; color: #ccc;"></i>
                                        <p>Klik atau drag gambar ke sini untuk upload</p>
                                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF, SVG. Maksimal 2MB.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Gambar yang sudah diupload</label>
                                <div id="image-preview" class="row">
                                    <!-- Image will be loaded here -->
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