<div class="row">
     <div class="col-md-12">
         <?= ce_msg('success'); ?>
         <?= ce_msg('danger'); ?>
         <div class="box box-solid box-primary">
             <div class="box-header with-border">
                 <h3 class="box-title">Kategori <small>Pelaporan</small></h3>
                 <div class="box-tools">
                     <?php if (ce_hak_akses('admin.kategori_pelaporan.add')): ?>
                     <button type="button" class="btn btn-primary btn-sm" id="btn-tambah">
                         <i class="fa fa-plus"></i> Tambah Kategori
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
                                <span class="info-box-number" id="total-kategori">
                                    <?php
                                    $this->load->model('kategori_pelaporan_m');
                                    echo $this->kategori_pelaporan_m->count_all_kategori();
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Aktif</span>
                                <span class="info-box-number" id="kategori-aktif">
                                    <?= $this->kategori_pelaporan_m->count_active_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Nonaktif</span>
                                <span class="info-box-number" id="kategori-nonaktif">
                                    <?= $this->kategori_pelaporan_m->count_inactive_kategori(); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-code"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kode Berikutnya</span>
                                <span class="info-box-number">KPL-001</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter-status">
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
                    <table id="tabel-kategori" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Kode</th>
                                <th>Nama Kategori</th>
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
                <h4 class="modal-title" id="modal-title">Tambah Kategori Pelaporan</h4>
            </div>
            <form id="form-kategori">
                <div class="modal-body">
                    <input type="hidden" id="pelaporan_id" name="pelaporan_id">
                    
                    <div class="form-group">
                        <label for="pelaporan_kode">Kode Kategori <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="pelaporan_kode" name="pelaporan_kode" placeholder="Masukkan kode kategori" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="pelaporan_nama">Nama Kategori <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="pelaporan_nama" name="pelaporan_nama" placeholder="Masukkan nama kategori" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="status" name="status" value="1" checked> Aktif
                            </label>
                        </div>
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
<div class="modal fade" id="modal-hapus" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Konfirmasi Hapus</h4>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus kategori pelaporan ini?</p>
                <input type="hidden" id="hapus_id">
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
