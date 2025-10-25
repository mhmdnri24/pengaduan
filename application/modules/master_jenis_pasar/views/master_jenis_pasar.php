<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Jenis Pasar</small></h3>
                <div class="box-tools">
                    <?php if (ce_hak_akses('admin.master_jenis_pasar.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalJenisPasar">
                        <i class="fa fa-plus"></i> Tambah Jenis Pasar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Jenis</span>
                                <span class="info-box-number" id="total-jenis">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tersedia</span>
                                <span class="info-box-number" id="total-tersedia">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Penuh</span>
                                <span class="info-box-number" id="total-penuh">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-wrench"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Maintenance</span>
                                <span class="info-box-number" id="total-maintenance">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_pasar" style="width: 100%;">
                            <option value="">-- Semua Pasar --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_jenis" style="width: 100%;">
                            <option value="">-- Semua Jenis --</option>
                            <option value="KIOS">KIOS</option>
                            <option value="LOS">LOS</option>
                            <option value="PELATARAN">PELATARAN</option>
                            <option value="LAPAK">LAPAK</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status_sewa" style="width: 100%;">
                            <option value="">-- Semua Status --</option>
                            <option value="TERSEDIA">TERSEDIA</option>
                            <option value="PENUH">PENUH</option>
                            <option value="MAINTENANCE">MAINTENANCE</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="table-jenis-pasar" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Pasar</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Tersedia</th>
                                <th>Terisi</th>
                                <th>Harga Sewa</th>
                                <th>Status</th>
                                <th>Jumlah Blok</th>
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

<!-- Modal Form Jenis Pasar -->
<div class="modal fade" id="modalJenisPasar" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formJenisPasar" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modal-title">Tambah Jenis Pasar</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_id">Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" name="pasar_id" id="pasar_id" style="width: 100%;" required>
                                    <option value="">Pilih Pasar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_jenis_nama">Jenis Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" name="pasar_jenis_nama" id="pasar_jenis_nama" style="width: 100%;" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="KIOS">KIOS</option>
                                    <option value="LOS">LOS</option>
                                    <option value="PELATARAN">PELATARAN</option>
                                    <option value="LAPAK">LAPAK</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pasar_jenis_jumlah">Jumlah <span class="text-red">*</span></label>
                                <input type="number" class="form-control" name="pasar_jenis_jumlah" id="pasar_jenis_jumlah" min="0" required>
                                <small class="text-muted">Total kapasitas jenis pasar ini</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pasar_jenis_tersedia">Tersedia <span class="text-red">*</span></label>
                                <input type="number" class="form-control" name="pasar_jenis_tersedia" id="pasar_jenis_tersedia" min="0" required>
                                <small class="text-muted">Jumlah yang masih tersedia</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pasar_jenis_terisi">Terisi <span class="text-red">*</span></label>
                                <input type="number" class="form-control" name="pasar_jenis_terisi" id="pasar_jenis_terisi" min="0" required>
                                <small class="text-muted">Jumlah yang sudah terisi</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_jenis_harga_sewa">Harga Sewa <span class="text-red">*</span></label>
                                <input type="number" class="form-control" name="pasar_jenis_harga_sewa" id="pasar_jenis_harga_sewa" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_jenis_status_sewa">Status Sewa <span class="text-red">*</span></label>
                                <select class="form-control select2" name="pasar_jenis_status_sewa" id="pasar_jenis_status_sewa" style="width: 100%;" required>
                                    <option value="">Pilih Status</option>
                                    <option value="TERSEDIA">TERSEDIA</option>
                                    <option value="PENUH">PENUH</option>
                                    <option value="MAINTENANCE">MAINTENANCE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pasar_jenis_keterangan">Keterangan</label>
                        <textarea class="form-control" name="pasar_jenis_keterangan" id="pasar_jenis_keterangan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Konfirmasi Hapus</h4>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data <strong id="nama-hapus"></strong>?</p>
                <input type="hidden" id="id-hapus">
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
