
<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">

    <div class="col-md-12">
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Silakan isi formulir di bawah ini</h3>
            </div>
            <form method="post" action="" class="form-horizontal">
                <div class="box-body">
                    <div class="form-group">
                        <label for="id_provinsi" class="col-sm-2 control-label">Provinsi <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="id_provinsi" id="id_provinsi" class="form-control optprov select2">
                            <option value="">- Pilih Provinsi -</option>
                            <?php 
                            foreach($provinsi as $sel){
                                echo '<option value="'.$sel->id_provinsi.'">'.$sel->nama_provinsi.'</option>';
                            }
                            ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="id_kota" class="col-sm-2 control-label">Kota <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="id_kota" id="id_kota" class="form-control optkot select2">
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_kecamatan" class="col-sm-2 control-label">Kode Kecamatan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="id_kecamatan" class="form-control" id="id_kecamatan">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_kecamatan" class="col-sm-2 control-label">Kecamatan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="nama_kecamatan" class="form-control" id="nama_kecamatan">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= anchor('kecamatan', '<i class="fa fa-chevron-left"></i> Kembali', 'class="btn btn-default"'); ?>
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>