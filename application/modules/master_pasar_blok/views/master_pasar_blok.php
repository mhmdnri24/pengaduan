<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Pasar Blok</small></h3>
                <div class="box-tools">
                    <!-- Toggle View Mode -->
                    <div class="btn-group" style="margin-right: 10px;">
                        <button type="button" class="btn btn-sm btn-info" id="btn-denah-view">
                            <i class="fa fa-th-large"></i> Denah View
                        </button>
                        <button type="button" class="btn btn-sm btn-default" id="btn-table-view">
                            <i class="fa fa-table"></i> Table View
                        </button>
                    </div>
                    
                    <?php if (ce_hak_akses('admin.master_pasar_blok.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalPasarBlok">
                        <i class="fa fa-plus"></i> Tambah Blok
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-th-large"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Blok</span>
                                <span class="info-box-number" id="total-blok">0</span>
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
                                <span class="info-box-text">Terisi</span>
                                <span class="info-box-number" id="total-terisi">0</span>
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
                        <select class="form-control select2" id="filter_status" style="width: 100%;">
                            <option value="">-- Semua Status --</option>
                            <option value="TERSEDIA">TERSEDIA</option>
                            <option value="TERISI">TERISI</option>
                            <option value="MAINTENANCE">MAINTENANCE</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <!-- Denah View -->
                <div id="denah-view" class="denah-container">
                    <div class="denah-grid" id="denah-grid">
                        <!-- Blok akan dimuat di sini via AJAX -->
                    </div>
                </div>

                <!-- Table View -->
                <div id="table-view" style="display: none;">
                    <div class="table-responsive">
                        <table id="table-pasar-blok" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Blok</th>
                                    <th>Nomor</th>
                                    <th>Nama Pasar</th>
                                    <th>Jenis</th>
                                    <th>Luas</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Posisi</th>
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
</div>

<!-- Modal Form Pasar Blok -->
<div class="modal fade" id="modalPasarBlok" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formPasarBlok" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modal-title">Tambah Blok Pasar</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_id">Nama Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" name="pasar_id" id="pasar_id" style="width: 100%;" required>
                                    <option value="">Pilih Nama Pasar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_jenis_id">Jenis Pasar <span class="text-red">*</span></label>
                                <select class="form-control select2" name="pasar_jenis_id" id="pasar_jenis_id" style="width: 100%;" required>
                                    <option value="">Pilih Jenis Pasar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_nama">Nama Blok <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="pasar_blok_nama" name="pasar_blok_nama" placeholder="Contoh: A-01, B-15" maxlength="50" required>
                                <small class="help-block">Maksimal 50 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_nomor">Nomor Blok</label>
                                <input type="text" class="form-control" id="pasar_blok_nomor" name="pasar_blok_nomor" placeholder="Nomor urut blok" maxlength="20">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_luas">Luas (m²)</label>
                                <input type="number" class="form-control" id="pasar_blok_luas" name="pasar_blok_luas" placeholder="0.00" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_status">Status</label>
                                <select class="form-control select2" name="pasar_blok_status" id="pasar_blok_status" style="width: 100%;">
                                    <option value="TERSEDIA">TERSEDIA</option>
                                    <option value="TERISI">TERISI</option>
                                    <option value="MAINTENANCE">MAINTENANCE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_posisi_x">Posisi X</label>
                                <input type="number" class="form-control" id="pasar_blok_posisi_x" name="pasar_blok_posisi_x" placeholder="0" min="0">
                                <small class="help-block">Koordinat horizontal untuk denah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pasar_blok_posisi_y">Posisi Y</label>
                                <input type="number" class="form-control" id="pasar_blok_posisi_y" name="pasar_blok_posisi_y" placeholder="0" min="0">
                                <small class="help-block">Koordinat vertikal untuk denah</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="pasar_blok_keterangan">Keterangan</label>
                                <textarea class="form-control" id="pasar_blok_keterangan" name="pasar_blok_keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
                            </div>
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
                <p>Apakah Anda yakin ingin menghapus blok <strong id="nama-hapus"></strong>?</p>
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

<!-- Include Modal Unit -->
<?php $this->load->view('modal_denah_unit'); ?>

<!-- CSS untuk Denah -->
<style>
.denah-container {
    background: #f9f9f9;
    border: 2px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    min-height: 500px;
    position: relative;
    overflow: auto;
}

.denah-grid {
    position: relative;
    width: 100%;
    min-height: 480px;
}

.blok-item {
    position: absolute;
    width: 100px;
    height: 100px;
    border: 2px solid #333;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    transition: all 0.3s ease;
    user-select: none;
}

.blok-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    z-index: 10;
}

.blok-tersedia {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.blok-terisi {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.blok-maintenance {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.blok-item .blok-nama {
    font-size: 14px;
    margin-bottom: 5px;
}

.blok-item .blok-info {
    font-size: 10px;
    opacity: 0.8;
}

.blok-item.dragging {
    opacity: 0.7;
    transform: rotate(5deg);
}

/* Select2 compatibility with Bootstrap modals */
.select2-container--bootstrap .select2-results__option[aria-selected=true] {
    background-color: #f5f5f5;
}

.select2-dropdown {
    z-index: 1051 !important; /* Higher than Bootstrap modal (1050) */
}

.modal .select2-dropdown {
    z-index: 10051 !important; /* Higher than modal backdrop */
}

.select2-container {
    width: 100% !important;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .blok-item {
        width: 80px;
        height: 80px;
        font-size: 10px;
    }

    .blok-item .blok-nama {
        font-size: 12px;
    }

    .blok-item .blok-info {
        font-size: 8px;
    }
}

/* CSS untuk Denah Unit */
.denah-unit-container {
    background: #f9f9f9;
    border: 2px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    min-height: 400px;
    max-height: 600px;
    overflow: auto;
}

.units-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 10px;
}

.unit-item {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.unit-item.tersedia {
    border-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.unit-item.terisi {
    border-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.unit-item.maintenance {
    border-color: #ffc107;
    background: #fff3cd;
    color: #856404;
}

.unit-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    z-index: 10;
}

.unit-nomor {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
}

.unit-ukuran {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.unit-penyewa {
    font-size: 11px;
    color: #333;
    margin-bottom: 3px;
    font-weight: 600;
}

.unit-tanggal {
    font-size: 10px;
    color: #999;
}

.unit-status-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 9px;
    padding: 2px 5px;
    border-radius: 3px;
    background: rgba(0,0,0,0.1);
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .units-grid {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 5px;
    }
    
    .unit-item {
        padding: 5px;
        min-height: 80px;
    }
    
    .unit-nomor {
        font-size: 14px;
    }
    
    .unit-ukuran {
        font-size: 10px;
    }
    
    .unit-penyewa {
        font-size: 9px;
    }
    
    .unit-tanggal {
        font-size: 8px;
    }
}
</style>
