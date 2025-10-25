<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">

    <div class="col-md-12">
        <div class="callout callout-info">
            <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
            <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
        </div>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Silakan isi formulir di bawah ini</h3>
            </div>
            <?= form_open(); ?>
            <div class="box-body">
                <div class="col-sm-6 no-padding">
                    <div class="form-group">
                        <label for="level">Nama Grup <span class="text-danger">*</span></label>
                        <input type="text" name="level" class="form-control" id="level" placeholder="Nama Grup"
                            value="<?= $level->level; ?>" required>
                    </div>
                    <?php if ($level->id_level != 1): ?>
                    <div class="form-group">
                        <label>Hak Akses</label>
                        <?php $user_akses = json_decode($level->hak_akses, true);
                            $no = 1;
                            foreach ($hak_akses as $label => $modul):
                                $idCheckbox = strtolower(str_replace(' ', '-', $label));
                                if ($no != 1) echo '<hr>'; ?>
                        <div class="row">
                            <div class="col-md-3">
                                <h4><?= $label; ?></h4>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="<?= $idCheckbox; ?>">
                                        <input type="checkbox" class="minimal"> Pilih semua
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php foreach ($modul as $key => $val):
                                            $checked = (@in_array($key, $user_akses) || $key == 'admin.beranda.view') ? 'checked' : '';
                                            $CheckboxID = $key == 'admin.beranda.view' ? 'readonly' : $idCheckbox; ?>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="hak_akses[]" id="<?= $CheckboxID; ?>"
                                            class="minimal" value="<?= $key; ?>" <?= $checked; ?>> <?= $val; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php $no++;
                            endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-footer">
                <?= anchor('hak-akses', '<i class="fa fa-chevron-left"></i> Kembali', 'class="btn btn-default"'); ?>
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Simpan</button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>