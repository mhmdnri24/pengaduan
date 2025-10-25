<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Unit Kerja</small></h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalUnitKerja">
                        <i class="fa fa-plus"></i> Tambah Unit Kerja
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_instansi">Filter Instansi</label>
                            <select id="filter_instansi" name="filter_instansi" class="form-control select2" style="width: 100%;">
                                <option value="">Semua Instansi</option>
                                <?php foreach($instansi as $inst): ?>
                                    <option value="<?= $inst->instansi_nama ?>"><?= $inst->instansi_nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filter_unitkerja">Filter Unit Kerja</label>
                            <select id="filter_unitkerja" name="filter_unitkerja" class="form-control select2" style="width: 100%;">
                                <option value="">Semua Unit Kerja</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Nama Unit Kerja</th>
                                <th>Unit Induk</th>
                                <th>Instansi</th>
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

<!-- Modal Tambah/Edit Unit Kerja -->
<div class="modal fade" id="modalUnitKerja">
    <div class="modal-dialog">
        <?= form_open('', ['id' => 'formUnitKerja']); ?>
            <input type="hidden" name="id" id="id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalUnitKerjaLabel">Form Unit Kerja</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="unitkerja">Nama Unit Kerja <span class="text-danger">*</span></label>
                        <input type="text" name="unitkerja" class="form-control" id="unitkerja" required>
                    </div>
                    <div class="form-group">
                        <label for="id_tb_instansi">Instansi <span class="text-danger">*</span></label>
                        <select name="id_tb_instansi" id="id_tb_instansi" class="form-control select2" style="width: 100%;" required>
                            <option value="">Pilih Instansi</option>
                            <?php foreach($instansi as $inst): ?>
                                <option value="<?= $inst->id_instansi ?>"><?= $inst->instansi_nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="induk_unit">Unit Induk</label>
                        <select name="induk_unit" id="induk_unit" class="form-control select2" style="width: 100%;">
                            <option value="">Tidak Ada (Unit Utama)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </div>
        <?= form_close(); ?>
    </div>
</div>
