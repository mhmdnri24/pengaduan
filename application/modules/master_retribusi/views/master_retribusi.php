<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
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
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Retribusi</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.master_retribusi.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fa fa-plus"></i> Tambah Data
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Retribusi</span>
                                <span class="info-box-number"><?= $total_retribusi; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Retribusi Aktif</span>
                                <span class="info-box-number"><?= $retribusi_aktif; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Retribusi Non-Aktif</span>
                                <span class="info-box-number"><?= $retribusi_nonaktif; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <select class="form-control select2" id="filter-jenis" style="width: 100%;">
                            <option value="">Semua Jenis</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control select2" id="filter-status" style="width: 100%;">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-default btn-block" id="btn-reset-filter">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Nama Retribusi</th>
                                <th>Kode</th>
                                <th>Jenis</th>
                                <th>Tarif</th>
                                <th>Satuan</th>
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-money"></i> Form Data Retribusi
                </h4>
            </div>
            <form id="form-retribusi" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_retribusi">Nama Retribusi <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="nama_retribusi" name="nama_retribusi" placeholder="Masukkan nama retribusi" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_retribusi">Kode Retribusi <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="kode_retribusi" name="kode_retribusi" placeholder="Masukkan kode retribusi" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_retribusi">Jenis Retribusi <span class="text-red">*</span></label>
                                <select class="form-control select2" id="jenis_retribusi" name="jenis_retribusi" required>
                                    <option value="">Pilih Jenis Retribusi</option>
                                    <option value="Pasar">Pasar</option>
                                    <option value="Kebersihan">Kebersihan</option>
                                    <option value="Keamanan">Keamanan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status_aktif">Status</label>
                                <select class="form-control select2" id="status_aktif" name="status_aktif">
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tarif">Tarif <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="tarif" name="tarif" placeholder="Masukkan tarif" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="satuan">Satuan <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="satuan" name="satuan" placeholder="Contoh: per hari, per bulan, per meter" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Masukkan deskripsi retribusi"></textarea>
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

<!-- Modal Detail -->
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
                <h4 class="modal-title" style="color: white;">
                    <i class="fa fa-eye"></i> Detail Data Retribusi
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama Retribusi</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-nama_retribusi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Retribusi</strong></td>
                                <td>:</td>
                                <td id="detail-kode_retribusi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Retribusi</strong></td>
                                <td>:</td>
                                <td id="detail-jenis_retribusi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Tarif</strong></td>
                                <td>:</td>
                                <td id="detail-tarif">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Satuan</strong></td>
                                <td width="5%">:</td>
                                <td id="detail-satuan">-</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>:</td>
                                <td id="detail-status_aktif">-</td>
                            </tr>
                            <tr>
                                <td><strong>Deskripsi</strong></td>
                                <td>:</td>
                                <td id="detail-deskripsi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Terdaftar</strong></td>
                                <td>:</td>
                                <td id="detail-created_at">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>