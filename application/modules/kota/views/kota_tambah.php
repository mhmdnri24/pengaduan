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
                            <select name="id_provinsi" id="id_provinsi" class="form-control select2" required>
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
                        <label for="nama_kota" class="col-sm-2 control-label">Kode Kota/Kab <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="id_kota" class="form-control" id="id_kota" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_kota" class="col-sm-2 control-label">Kota <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="nama_kota" class="form-control" id="nama_kota" required>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= anchor('kota', '<i class="fa fa-chevron-left"></i> Kembali', 'class="btn btn-default"'); ?>
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>