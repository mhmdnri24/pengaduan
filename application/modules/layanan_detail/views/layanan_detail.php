<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detail <small>Layanan</small></h3>
                <div class="box-tools">
                    <?php if (ce_cek_hak_akses('admin.layanan_detail.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalLayanan">
                        <i class="fa fa-plus"></i> Tambah Layanan
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
                                <span class="info-box-text">Total Layanan</span>
                                <span class="info-box-number"><?= $this->db->count_all('layanan_jenis') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Layanan Aktif</span>
                                <span class="info-box-number"><?= $this->db->where('layanan_status', 1)->count_all_results('layanan_jenis') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Layanan Tidak Aktif</span>
                                <span class="info-box-number"><?= $this->db->where('layanan_status', 0)->count_all_results('layanan_jenis') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-purple">
                            <span class="info-box-icon"><i class="fa fa-tags"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kategori</span>
                                <span class="info-box-number"><?= $this->db->select('layanan_kategori')->distinct()->get('layanan_jenis')->num_rows() ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <select class="form-control" id="filter_kategori">
                            <option value="">Semua Kategori</option>
                            <?php
                            $kategori_list = $this->db->select('layanan_kategori')->distinct()->get('layanan_jenis')->result();
                            foreach ($kategori_list as $kat) {
                                if (!empty($kat->layanan_kategori)) {
                                    echo '<option value="'.$kat->layanan_kategori.'">'.$kat->layanan_kategori.'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filter_status">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-info" id="btn_filter">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-default" id="btn_reset">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table_layanan">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Layanan</th>
                                <th>Kategori</th>
                                <!-- Kolom Biaya dihapus -->
                                <th>Durasi</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
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

<!-- Modal Tambah/Edit Layanan -->
<div class="modal fade" id="modalLayanan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalTitle">Tambah Layanan</h4>
            </div>
            <form id="formLayanan">
                <div class="modal-body">
                    <input type="hidden" id="layanan_id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_nama">Nama Layanan <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="layanan_nama" name="layanan_nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_kategori">Kategori <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="layanan_kategori" name="layanan_kategori" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="layanan_deskripsi">Deskripsi <span class="text-red">*</span></label>
                        <textarea class="form-control" id="layanan_deskripsi" name="layanan_deskripsi" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_durasi">Durasi (hari) <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="layanan_durasi" name="layanan_durasi" min="1" required>
                            </div>
                        </div>
                    </div>
                    

                    <div class="form-group">
                        <label for="layanan_status">Status</label>
                        <select class="form-control" id="layanan_status" name="layanan_status">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
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
