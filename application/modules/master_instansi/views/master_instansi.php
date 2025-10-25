<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Instansi</small></h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalInstansi">
                        <i class="fa fa-plus"></i> Tambah Instansi
                    </button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table id="dataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:10px;">No</th>
                            <th>Nama Instansi</th>
                            <th>Jenis</th>
                            <th>Kode Cepat</th>
                            <th>ID Instansi</th>
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

<!-- Modal Tambah/Edit Instansi -->
<div class="modal fade" id="modalInstansi">
    <div class="modal-dialog">
        <?= form_open('', ['id' => 'formInstansi']); ?>
            <input type="hidden" name="id" id="id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalInstansiLabel">Form Instansi</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="instansi_nama">Nama Instansi <span class="text-danger">*</span></label>
                        <input type="text" name="instansi_nama" class="form-control" id="instansi_nama" required>
                    </div>
                    <div class="form-group">
                        <label for="jenis">Jenis</label>
                        <input type="text" name="jenis" class="form-control" id="jenis">
                    </div>
                    <div class="form-group">
                        <label for="cepat_kode">Kode Cepat</label>
                        <input type="text" name="cepat_kode" class="form-control" id="cepat_kode">
                    </div>
                    <div class="form-group">
                        <label for="instansi_id">ID Instansi</label>
                        <input type="text" name="instansi_id" class="form-control" id="instansi_id">
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
