<style>
.select2-container {
    width: 100% !important;
}
.select2-container .select2-selection--single {
    height: 34px !important;
    border: 1px solid #d2d6de !important;
    border-radius: 4px !important;
}
.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 32px !important;
    padding-left: 12px !important;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 32px !important;
}
#foto-preview {
    border: 2px dashed #ddd;
    border-radius: 5px;
    transition: border-color 0.3s ease;
}
#foto-preview:hover {
    border-color: #3c8dbc;
}
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Kepengurusan Sosial</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.kepengurusan_sosial.view')): ?>
                    <a href="<?= base_url('kepengurusan_sosial/download_template') ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-download"></i> Template Excel
                    </a>
                    <?php endif; ?>
                    <?php if (ce_hak_akses('admin.kepengurusan_sosial.add')): ?>
                    <button type="button" class="btn btn-success btn-sm" id="btn-import">
                        <i class="fa fa-upload"></i> Import Excel
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fa fa-plus"></i> Tambah Data
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Kepengurusan</span>
                                <span class="info-box-number"><?= $total_kepengurusan; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-male"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Laki-laki</span>
                                <span class="info-box-number"><?= $kepengurusan_laki; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-pink">
                            <span class="info-box-icon"><i class="fa fa-female"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Perempuan</span>
                                <span class="info-box-number"><?= $kepengurusan_perempuan; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-heart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Menikah</span>
                                <span class="info-box-number"><?= $kepengurusan_menikah; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_jenis_kelamin">
                            <option value="">-- Semua Jenis Kelamin --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_agama">
                            <option value="">-- Semua Agama --</option>
                            <option value="ISLAM">Islam</option>
                            <option value="KRISTEN">Kristen</option>
                            <option value="KATOLIK">Katolik</option>
                            <option value="HINDU">Hindu</option>
                            <option value="BUDDHA">Buddha</option>
                            <option value="KONGHUCU">Konghucu</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status_kawin">
                            <option value="">-- Semua Status --</option>
                            <option value="BELUM_MENIKAH">Belum Menikah</option>
                            <option value="MENIKAH">Menikah</option>
                            <option value="CERAI_HIDUP">Cerai Hidup</option>
                            <option value="CERAI_MATI">Cerai Mati</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default" id="btn-reset-filter">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>NIK KTP</th>
                                <th>No KK</th>
                                <th>No WA/HP</th>
                                <th>Tempat, Tanggal Lahir</th>
                                <th>Jenis Kelamin</th>
                                <th>Agama</th>
                                <th>Status Kawin</th>
                                <th>Pendidikan</th>
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

<!-- Modal Form -->
<div class="modal fade" id="modal-form" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Form Kepengurusan Sosial</h4>
            </div>
            <form id="form-kepengurusan" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">

                    <!-- Upload Foto Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><i class="fa fa-camera"></i> Foto Profil</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div id="foto-preview" style="border: 2px dashed #ddd; padding: 20px; text-align: center; min-height: 150px;">
                                            <img id="preview-img" src="" style="max-width: 100%; max-height: 120px; display: none;">
                                            <div id="preview-text">
                                                <i class="fa fa-camera fa-3x text-muted"></i>
                                                <p class="text-muted">Preview Foto</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="file" name="foto_profil" class="form-control" id="foto_profil" accept="image/*">
                                        <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
                                        <div id="current-foto" style="margin-top: 10px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_lengkap"><i class="fa fa-user"></i> Nama Lengkap <span class="text-red">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control" id="nama_lengkap" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nik_ktp"><i class="fa fa-id-card"></i> NIK KTP <span class="text-red">*</span></label>
                                <input type="text" name="nik_ktp" class="form-control" id="nik_ktp" maxlength="16" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_kk"><i class="fa fa-address-card"></i> No KK</label>
                                <input type="text" name="no_kk" class="form-control" id="no_kk" maxlength="16" placeholder="Masukkan No KK (16 digit)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_wa_hp"><i class="fa fa-phone"></i> No WA/HP <span class="text-red">*</span></label>
                                <input type="text" name="no_wa_hp" class="form-control" id="no_wa_hp" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tempat_lahir"><i class="fa fa-map-marker"></i> Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control" id="tempat_lahir">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_lahir"><i class="fa fa-calendar"></i> Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control" id="tanggal_lahir">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_kelamin"><i class="fa fa-venus-mars"></i> Jenis Kelamin</label>
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="agama"><i class="fa fa-book"></i> Agama</label>
                                <select name="agama" id="agama" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Agama</option>
                                    <option value="ISLAM">Islam</option>
                                    <option value="KRISTEN">Kristen</option>
                                    <option value="KATOLIK">Katolik</option>
                                    <option value="HINDU">Hindu</option>
                                    <option value="BUDDHA">Buddha</option>
                                    <option value="KONGHUCU">Konghucu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status_kawin"><i class="fa fa-heart"></i> Status Kawin</label>
                                <select name="status_kawin" id="status_kawin" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Status Kawin</option>
                                    <option value="BELUM_MENIKAH">Belum Menikah</option>
                                    <option value="MENIKAH">Menikah</option>
                                    <option value="CERAI_HIDUP">Cerai Hidup</option>
                                    <option value="CERAI_MATI">Cerai Mati</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alamat"><i class="fa fa-home"></i> Alamat</label>
                        <textarea name="alamat" class="form-control" id="alamat" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kecamatan"><i class="fa fa-map"></i> Kecamatan</label>
                                <select name="kecamatan" id="kecamatan" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Kecamatan</option>
                                    <?php
                                    $kecamatan_list = $this->kecamatan_m->kecamatan_by_kota('16.73'); // Palembang
                                    foreach ($kecamatan_list as $kec) : ?>
                                        <option value="<?= $kec->id_kecamatan; ?>"><?= $kec->nama_kecamatan; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kelurahan"><i class="fa fa-map-marker"></i> Kelurahan</label>
                                <select name="kelurahan" id="kelurahan" class="form-control select2" style="width: 100%;">
                                    <option value="">Pilih Kelurahan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pendidikan_terakhir"><i class="fa fa-graduation-cap"></i> Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-control select2" style="width: 100%;">
                            <option value="">Pilih Pendidikan</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                            <option value="SMK">SMK</option>
                            <option value="D1">D1</option>
                            <option value="D2">D2</option>
                            <option value="D3">D3</option>
                            <option value="D4">D4</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
