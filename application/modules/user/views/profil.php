<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<form id="form-edit-profil" action="<?= base_url('user/proses_edit_profil') ?>" method="POST" enctype="multipart/form-data" class="form-horizontal">
    <div class="modal-body">
        <input type="hidden" name="id_user" value="<?= $user->id_user ?>">
        
        <div class="form-group">
            <label for="nama" class="col-sm-3 control-label">Nama Lengkap</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="nama" name="nama" value="<?= html_escape($user->nama) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="username" class="col-sm-3 control-label">NIP Baru</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="username" name="username" value="<?= html_escape($user->username) ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="nip_lama" class="col-sm-3 control-label">NIP Lama</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="nip_lama" name="nip_lama" value="<?= html_escape($user->nip_lama) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="glr_depan" class="col-sm-3 control-label">Gelar Depan</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="glr_depan" name="glr_depan" value="<?= html_escape($user->glr_depan) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="glr_belakang" class="col-sm-3 control-label">Gelar Belakang</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="glr_belakang" name="glr_belakang" value="<?= html_escape($user->glr_belakang) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="email" class="col-sm-3 control-label">Email</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" id="email" name="email" value="<?= html_escape($user->email) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="no_telp" class="col-sm-3 control-label">No. Telepon</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= html_escape($user->no_telp) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="instansi" class="col-sm-3 control-label">Instansi</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="instansi" name="instansi" value="<?= html_escape($user->instansi) ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="col-sm-3 control-label">Password Baru</label>
            <div class="col-sm-9">
                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
            </div>
        </div>
        <div class="form-group">
            <label for="foto" class="col-sm-3 control-label">Foto Profil</label>
            <div class="col-sm-6">
                <input type="file" id="foto" name="foto" class="form-control">
                <p class="help-block">Maks. 2MB. Kosongkan jika tidak diubah.</p>
            </div>
            <div class="col-sm-3">
                <img id="foto-preview" src="<?= base_url('user/foto/' . $user->id_user) ?>" class="img-responsive img-thumbnail" alt="Foto Preview">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Perubahan</button>
    </div>
</form>