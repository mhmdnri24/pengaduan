<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    /* Level 0 - Root categories */
    .table-hover .parent-row {
        background-color: #f9f9f9;
        font-weight: bold;
    }
    .parent-row td {
        border-top: 2px solid #3c8dbc;
        padding-top: 12px;
        padding-bottom: 12px;
        font-weight: bold;
    }
    .parent-row:hover {
        background-color: #e9e9e9;
    }
    .parent-row .fa-sitemap {
        font-size: 16px;
        margin-right: 5px;
        color: #3c8dbc;
    }
    .table > tbody > tr.parent-row > td:first-child {
        border-left: 4px solid #3c8dbc;
    }
    
    /* Level 1 - Child categories */
    .child-row {
        background-color: #fafafa;
    }
    .child-row:hover {
        background-color: #f0f0f0;
    }
    .table > tbody > tr.child-row > td:first-child {
        border-left: 4px solid #f39c12;
        padding-left: 25px;
    }
    .child-row td:nth-child(2) {
        padding-left: 60px !important;
    }
    .child-row td:nth-child(3) {
        padding-left: 60px !important;
    }
    .child-row td:nth-child(4) {
        padding-left: 25px;
    }
    .child-row td:nth-child(5),
    .child-row td:nth-child(6) {
        padding-left: 8px;
    }
    
    /* Level 2 - Grandchild categories */
    .grandchild-row {
        background-color: #f5f5f5;
    }
    .grandchild-row:hover {
        background-color: #eeeeee;
    }
    .table > tbody > tr.grandchild-row > td:first-child {
        border-left: 4px solid #e74c3c;
        padding-left: 35px;
    }
    .grandchild-row td:nth-child(2) {
        padding-left: 100px !important;
    }
    .grandchild-row td:nth-child(3) {
        padding-left: 100px !important;
    }
    .grandchild-row td:nth-child(4) {
        padding-left: 35px;
    }
    .grandchild-row td:nth-child(5),
    .grandchild-row td:nth-child(6) {
        padding-left: 8px;
    }
    
    /* Icon styling untuk berbagai level */
    .child-row .fa-long-arrow-right {
        margin-right: 8px;
        color: #f39c12;
        font-size: 14px;
    }
    
    .grandchild-row .fa-angle-double-right {
        margin-right: 8px;
        color: #e74c3c;
        font-size: 14px;
    }
    
    /* Button styling */
    .child-row .btn-group,
    .grandchild-row .btn-group {
        opacity: 0.8;
    }
    .child-row:hover .btn-group,
    .grandchild-row:hover .btn-group {
        opacity: 1;
    }
    
    /* Typography untuk berbagai level */
    .parent-row td:nth-child(3) {
        font-size: 15px;
        color: #2c3e50;
        font-weight: bold;
    }
    .child-row td:nth-child(3) {
        font-size: 14px;
        color: #555;
        font-weight: 500;
    }
    .grandchild-row td:nth-child(3) {
        font-size: 13px;
        color: #666;
        font-weight: normal;
        font-style: italic;
    }
    
    /* Visual connectors untuk menunjukkan hierarki */
    .child-row td:nth-child(3)::before {
        content: '';
        position: absolute;
        left: 45px;
        top: 50%;
        width: 10px;
        height: 1px;
        background-color: #f39c12;
    }
    
    .grandchild-row td:nth-child(3)::before {
        content: '';
        position: absolute;
        left: 85px;
        top: 50%;
        width: 10px;
        height: 1px;
        background-color: #e74c3c;
    }
    
    /* Additional spacing dan borders */
    .level-0 {
        border-top: 3px solid #3c8dbc !important;
    }
    .level-1 {
        border-top: 1px solid #f39c12;
    }
    .level-2 {
        border-top: 1px solid #e74c3c;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <!-- CSRF Token for AJAX requests -->
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Kategori</small></h3>
                <p class="box-subtitle" style="margin: 5px 0 0 0; color: #666; font-size: 12px;">
                    <i class="fa fa-info-circle"></i> Data ditampilkan berdasarkan hierarki 3 Level: Root → Sub Level 1 → Sub Level 2
                </p>
                <div class="box-tools">
                    <?php if (ce_hak_akses('admin.master_kategori.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalKategori">
                        <i class="fa fa-plus"></i> Tambah Kategori
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-2">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-gift"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Kategori</span>
                                <span class="info-box-number" id="total-kategori">
                                    <?php
                                    echo $this->master_kategori_m->count_all_kategori();
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Aktif</span>
                                <span class="info-box-number" id="kategori-aktif">
                                    <?= $this->master_kategori_m->count_active_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Nonaktif</span>
                                <span class="info-box-number" id="kategori-nonaktif">
                                    <?= $this->master_kategori_m->count_inactive_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-sitemap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Parent</span>
                                <span class="info-box-number">
                                    <?= $this->master_kategori_m->count_parent_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-code-fork"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Sub Kategori</span>
                                <span class="info-box-number">
                                    <?= $this->master_kategori_m->count_child_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_parent">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($kategori_parent_list as $kategori): ?>
                                <option value="<?= $kategori->id ?>"><?= $kategori->nama_kategori ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                                <th style="width:100px;">Kode</th>
                                <th>Parent / Kategori</th>
                                <th style="width:80px;">Info</th>
                                <th style="width:80px;">Status</th>
                                <th style="width:120px;">Aksi</th>
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

<!-- Modal Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalKategoriLabel">Tambah Kategori</h4>
            </div>
            <form id="formKategori">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parent_id">Parent Kategori</label>
                                <select class="form-control select2" id="parent_id" name="parent_id">
                                    <option value="">-- Root Kategori (Level 0) --</option>
                                    <?php 
                                    $tree = $this->master_kategori_m->get_full_kategori_tree();
                                    foreach ($tree as $root): ?>
                                        <option value="<?= $root->id ?>"><?= $root->nama_kategori ?> (Root)</option>
                                        <?php foreach ($root->children as $level1): ?>
                                            <option value="<?= $level1->id ?>">--- <?= $level1->nama_kategori ?> (Sub dari <?= $root->nama_kategori ?>)</option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="help-block">
                                    <strong>Level 0:</strong> Root kategori (tidak ada parent)<br>
                                    <strong>Level 1:</strong> Sub kategori dari Root<br>
                                    <strong>Level 2:</strong> Sub kategori dari Level 1 (maksimal 3 level)
                                </small>
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_kategori">Kode Kategori <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" placeholder="Contoh: TEKNOLOGI" maxlength="10" required>
                                <small class="help-block">Maksimal 10 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_kategori">Nama Kategori <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Teknologi & Digital" maxlength="100" required>
                                <small class="help-block">Maksimal 100 karakter</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi kategori (opsional)"></textarea>
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
