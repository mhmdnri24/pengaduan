<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Kategori <small>Layanan</small></h3>
                <div class="box-tools">
                    <a href="<?= base_url('kategori_layanan/export_csv') ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-download"></i> Export CSV
                    </a>
                    <?php if (ce_hak_akses('admin.kategori_layanan.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalKategori">
                        <i class="fa fa-plus"></i> Tambah Kategori
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Kategori</span>
                                <span class="info-box-number"><?= $this->db->count_all('layanan_kategori') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Aktif</span>
                                <span class="info-box-number"><?= $this->kategori_layanan_m->count_aktif() ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-power-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori Tidak Aktif</span>
                                <span class="info-box-number"><?= $this->kategori_layanan_m->count_tidak_aktif() ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-6">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-default" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Nama Kategori</th>
                                <th>Urutan</th>
                                <th>Status Aktif</th>
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

<!-- Modal Form Kategori -->
<div class="modal fade" id="modalKategori" tabindex="-1" role="dialog" aria-labelledby="modalKategoriLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalKategoriLabel">Form Kategori</h4>
            </div>
            <form id="formKategori">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="kategori_nama">Nama Kategori <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="kategori_nama" name="kategori_nama" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kategori_urutan">Urutan</label>
                                <input type="number" class="form-control" id="kategori_urutan" name="kategori_urutan" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kategori_deskripsi">Deskripsi Kategori</label>
                        <textarea class="form-control" id="kategori_deskripsi" name="kategori_deskripsi" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="kategori_status" name="kategori_status" value="1" checked>
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
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