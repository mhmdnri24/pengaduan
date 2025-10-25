<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Master <small>Dokumen</small></h3>
                <div class="box-tools">
                    <a href="<?= base_url('master_dokumen/export_csv') ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-download"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalDokumen">
                        <i class="fa fa-plus"></i> Tambah Dokumen
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-files-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Dokumen</span>
                                <span class="info-box-number"><?= $this->master_dokumen_m->dokumen_get_all() ? count($this->master_dokumen_m->dokumen_get_all()) : 0 ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dokumen Wajib</span>
                                <span class="info-box-number"><?= $this->master_dokumen_m->count_wajib() ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dokumen Opsional</span>
                                <span class="info-box-number"><?= $this->master_dokumen_m->count_opsional() ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kelompok">
                            <option value="">-- Semua Kelompok Wisuda --</option>
                            <?php
                            $kelompok_wisuda = $this->db->where('status', 1)->order_by('urutan', 'asc')->get('kelompok_wisuda')->result();
                            foreach ($kelompok_wisuda as $kw) {
                                echo '<option value="'.$kw->id.'">'.$kw->nama_kelompok.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="1">Wajib</option>
                            <option value="0">Opsional</option>
                        </select>
                    </div>
                    <div class="col-md-3">
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
                                <th>Nama Dokumen</th>
                                <th>Deskripsi</th>
                                <th>Kelompok Wisuda</th>
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

<!-- Modal Form Dokumen -->
<div class="modal fade" id="modalDokumen" tabindex="-1" role="dialog" aria-labelledby="modalDokumenLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalDokumenLabel">Form Dokumen</h4>
            </div>
            <form id="formDokumen" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="form-group">
                        <label for="dokumen_nama">Nama Dokumen <span class="text-red">*</span></label>
                        <input type="text" class="form-control" id="dokumen_nama" name="dokumen_nama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dokumen_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="dokumen_deskripsi" name="dokumen_deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="kelompok_wisuda_id">Kelompok Wisuda</label>
                        <select class="form-control" id="kelompok_wisuda_id" name="kelompok_wisuda_id">
                            <option value="">-- Pilih Kelompok Wisuda --</option>
                            <?php
                            $kelompok_wisuda = $this->db->where('status', 1)->get('kelompok_wisuda')->result();
                            foreach ($kelompok_wisuda as $kw) {
                                echo '<option value="'.$kw->id.'">'.$kw->nama_kelompok.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="dokumen_wajib" name="dokumen_wajib" value="1">
                            Dokumen Wajib
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="contoh_dokumen">Contoh Dokumen</label>
                        <input type="file" class="form-control" id="contoh_dokumen" name="contoh_dokumen" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Format yang diizinkan: PDF, DOC, DOCX, JPG, JPEG, PNG. Maksimal 5MB.</small>
                        <div id="contoh_preview"></div>
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
