
<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
                            <select name="id_provinsi" id="id_provinsi" class="form-control optprov select2" required>
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
                            <select name="id_kota" id="id_kota" class="form-control optkot select2" required>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="id_kecamatan" class="col-sm-2 control-label">Kecamatan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="id_kecamatan" id="id_kecamatan" class="form-control optkec select2" required>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_kelurahan" class="col-sm-2 control-label">Nama Kelurahan <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" name="nama_kelurahan" class="form-control" id="nama_kelurahan" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="batas_wilayah" class="col-sm-2 control-label">Batas Wilayah <span class="text-danger">*</span></label>
                         <div class="col-sm-8">
                            <input type="hidden" id="tipe" name="tipe">
                            <input type="hidden" id="latlng" name="latlng">
                            <input type="hidden" id="radius" name="radius">
                            <div id="pikomap" style="width:100%;height:400px;"></div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <?= anchor('kelurahan', '<i class="fa fa-chevron-left"></i> Kembali', 'class="btn btn-default"'); ?>
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>