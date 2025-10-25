<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="callout callout-info">
    <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
    <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
</div>

<?= ce_msg('success'); ?>
<?= ce_msg('danger'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Silakan isi formulir di bawah ini</h3>
            </div>
            <form method="post" enctype="multipart/form-data">
            <div class="box-body">
                <div class="col-sm-6 no-padding">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" id="nama" placeholder="Nama Lengkap"
                            value="<?= $user->nama; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" id="username" placeholder="Username"
                            value="<?= $user->username; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control" id="password"
                            placeholder="Password">
                        <i class="help-block">Kosongkan jika tidak ingin mengganti password</i>
                    </div>
                    <div class="form-group">
                        <label for="foto">Foto</label>
                        <p><img src="<?= base_url('assets/img/user/' . $user->foto); ?>" class="img-thumbnail"
                                width="100"></p>
                        <input type="file" name="foto" id="foto">
                        <i class="help-block">File sebelumnya (jika ada) akan diganti</i>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
            </div>
            </form>
        </div>
    </div>
</div>