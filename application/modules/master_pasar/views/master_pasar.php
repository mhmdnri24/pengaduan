<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Pasar</small></h3>
                <div class="box-tools">
                    <?php if (ce_hak_akses('admin.master_pasar.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPasar">
                        <i class="fa fa-plus"></i> Tambah Pasar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Pasar</span>
                                <span class="info-box-number" id="total-pasar">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pasar Aktif</span>
                                <span class="info-box-number" id="pasar-aktif">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pasar Nonaktif</span>
                                <span class="info-box-number" id="pasar-nonaktif">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jenis Pasar</span>
                                <span class="info-box-number">4</span>
                                <span class="progress-description">KIOS, LOS, PELATARAN, LAPAK</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kecamatan" style="width: 100%;">
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kelurahan" style="width: 100%;">
                            <option value="">Semua Kelurahan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status" style="width: 100%;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default btn-sm" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="table-responsive">
                    <table id="table-pasar" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Pasar</th>
                                <th>Alamat</th>
                                <th>Kelurahan</th>
                                <th>Kecamatan</th>
                                <th>Kota</th>
                                <th>Telepon</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Pasar -->
<div class="modal fade" id="modalPasar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formPasar" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modal-title">Tambah Pasar</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#tab-info" aria-controls="tab-info" role="tab" data-toggle="tab">
                                <i class="fa fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab-lokasi" aria-controls="tab-lokasi" role="tab" data-toggle="tab">
                                <i class="fa fa-map-marker"></i> Lokasi & Koordinat
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" style="margin-top: 15px;">
                        <!-- Tab Informasi Dasar -->
                        <div role="tabpanel" class="tab-pane active" id="tab-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_nama">Nama Pasar <span class="text-red">*</span></label>
                                        <input type="text" class="form-control" id="pasar_nama" name="pasar_nama" placeholder="Contoh: Pasar Tradisional Kuto" maxlength="100" required>
                                        <small class="help-block">Maksimal 100 karakter</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_telepon">Telepon</label>
                                        <input type="text" class="form-control" id="pasar_telepon" name="pasar_telepon" placeholder="Contoh: 0711-123456" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pasar_alamat">Alamat</label>
                                <textarea class="form-control" id="pasar_alamat" name="pasar_alamat" rows="3" placeholder="Masukkan alamat lengkap pasar"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="pasar_deskripsi">Deskripsi</label>
                                <textarea class="form-control" id="pasar_deskripsi" name="pasar_deskripsi" rows="4" placeholder="Masukkan deskripsi pasar (opsional)"></textarea>
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="pasar_status" name="pasar_status" value="1" checked>
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab Lokasi & Koordinat -->
                        <div role="tabpanel" class="tab-pane" id="tab-lokasi">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_id_kecamatan">Kecamatan</label>
                                        <select class="form-control select2" id="pasar_id_kecamatan" name="pasar_id_kecamatan" style="width: 100%;">
                                            <option value="">Pilih Kecamatan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_id_kelurahan">Kelurahan</label>
                                        <select class="form-control select2" id="pasar_id_kelurahan" name="pasar_id_kelurahan" style="width: 100%;">
                                            <option value="">Pilih Kelurahan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_latitude">Latitude</label>
                                        <input type="text" class="form-control" id="pasar_latitude" name="pasar_latitude" placeholder="Contoh: -2.9760285" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pasar_longitude">Longitude</label>
                                        <input type="text" class="form-control" id="pasar_longitude" name="pasar_longitude" placeholder="Contoh: 104.7754794" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Pilih Lokasi pada Map <small class="text-muted">(Klik pada map untuk menentukan lokasi)</small></label>
                                <div id="map" style="height: 400px; border: 1px solid #ddd;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Konfirmasi Hapus</h4>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pasar <strong id="nama-pasar-hapus"></strong>?</p>
                <p class="text-danger"><small><i class="fa fa-warning"></i> Data yang dihapus tidak dapat dikembalikan!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-konfirm-hapus">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</div>
