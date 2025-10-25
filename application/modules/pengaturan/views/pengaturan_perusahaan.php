<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">

    <div class="col-md-12">
        <div class="callout callout-info">
            <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
            <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
        </div>
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Silakan isi formulir di bawah ini</h3>
            </div>
            <form method="post" action="">
                <div class="box-body">
                <div class="col-sm-6 no-padding">
                    <div class="form-group">
                        <label for="nama_perusahaan">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_perusahaan" class="form-control" id="nama_perusahaan"
                            placeholder="Nama Toko" value="<?= $perusahaan->nama_perusahaan; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="notelp_perusahaan">No Telp <span class="text-danger">*</span></label>
                        <input type="text" name="notelp_perusahaan" class="form-control" id="notelp_perusahaan"
                            placeholder="Masukan No Telpon" value="<?= $perusahaan->notelp_perusahaan; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat_perusahaan">Alamat <span class="text-danger">*</span></label>
                        <input type="text" name="alamat_perusahaan" class="form-control" id="alamat_perusahaan"
                            placeholder="Alamat" value="<?= $perusahaan->alamat_perusahaan; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="norek_perusahaan">No Rekening <span class="text-danger">*</span></label>
                        <textarea name="norek_perusahaan" class="form-control" id="norek_perusahaan"
                            placeholder="Masukan No Rekening" value=""><?= $perusahaan->norek_perusahaan; ?></textarea>
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