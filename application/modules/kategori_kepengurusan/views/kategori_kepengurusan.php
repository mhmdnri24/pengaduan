<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Kategori Kepengurusan</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.kategori_kepengurusan.add')): ?>
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
                            <span class="info-box-icon"><i class="fa fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Kategori</span>
                                <span class="info-box-number"><?= $total_kategori; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Aktif</span>
                                <span class="info-box-number"><?= $kategori_aktif; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Nonaktif</span>
                                <span class="info-box-number"><?= $kategori_nonaktif; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-code"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kode Berikutnya</span>
                                <span class="info-box-number"><?= $next_kode; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
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
                                <th>Kode Kepengurusan</th>
                                <th>Nama Kepengurusan</th>
                                <th>Status</th>
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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Form Kategori Kepengurusan</h4>
            </div>
            <form id="form-kategori">
                <div class="modal-body">
                    <input type="hidden" name="kepengurusan_id" id="kepengurusan_id">
                    
                    <div class="form-group">
                        <label for="kepengurusan_kode">Kode Kepengurusan <span class="text-red">*</span></label>
                        <input type="text" name="kepengurusan_kode" class="form-control" id="kepengurusan_kode" required>
                        <small class="text-muted">Format: KP-001, KP-002, dst.</small>
                    </div>

                    <div class="form-group">
                        <label for="kepengurusan_nama">Nama Kepengurusan <span class="text-red">*</span></label>
                        <input type="text" name="kepengurusan_nama" class="form-control" id="kepengurusan_nama" required>
                        <small class="text-muted">Contoh: Marbot, Takmir, Pengurus Harian, dll.</small>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
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
