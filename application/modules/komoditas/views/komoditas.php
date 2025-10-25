<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<style>
.tree-view {
    margin: 0;
    padding: 0;
    list-style: none;
}

.tree-view li {
    margin: 5px 0;
    padding: 0;
}

.tree-view .parent-item {
    font-weight: bold;
    color: #333;
    background: #f8f9fa;
    padding: 8px 12px;
    border-left: 4px solid #007bff;
    margin-bottom: 5px;
}

.tree-view .child-item {
    margin-left: 20px;
    padding: 5px 12px;
    border-left: 2px solid #dee2e6;
    color: #666;
}

.tree-view .child-item:hover {
    background: #f8f9fa;
}

.komoditas-actions {
    float: right;
}

.komoditas-actions .btn {
    margin-left: 3px;
}

.statistics-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 10px;
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 0.9em;
    opacity: 0.9;
}
</style>

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <?= ce_msg('info'); ?>
        
        <!-- Statistics -->
        <div class="statistics-box">
            <div class="row">
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number"><?= $statistics['total'] ?></span>
                        <span class="stat-label">Total Komoditas</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number"><?= $statistics['kategori'] ?></span>
                        <span class="stat-label">Kategori</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number"><?= $statistics['jenis'] ?></span>
                        <span class="stat-label">Jenis</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number"><?= $statistics['aktif'] ?></span>
                        <span class="stat-label">Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <span class="stat-number"><?= $statistics['nonaktif'] ?></span>
                        <span class="stat-label">Non-Aktif</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stat-item">
                        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalKomoditas">
                            <i class="fa fa-plus"></i> Tambah Komoditas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Tree View -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-sitemap"></i> Struktur Komoditas</h3>
                        <div class="box-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalKomoditas">
                                <i class="fa fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <ul class="tree-view" id="komoditasTree">
                            <?php if (empty($komoditas_tree)): ?>
                                <li class="text-center text-muted">
                                    <i class="fa fa-info-circle"></i> Belum ada data komoditas
                                </li>
                            <?php else: ?>
                                <?php foreach ($komoditas_tree as $parent): ?>
                                    <li>
                                        <div class="parent-item" data-id="<?= $parent->komoditas_id ?>">
                                            <i class="fa fa-folder-open"></i> <?= $parent->komoditas_nama ?>
                                            <span class="badge badge-info"><?= count($parent->sub_komoditas) ?> item</span>
                                            <div class="komoditas-actions">
                                                <?php if (ce_cek_hak_akses('admin.komoditas.update')): ?>
                                                    <button type="button" class="btn btn-warning btn-xs btn-edit" data-id="<?= $parent->komoditas_id ?>" title="Edit">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (ce_cek_hak_akses('admin.komoditas.add')): ?>
                                                    <button type="button" class="btn btn-info btn-xs btn-copy" data-id="<?= $parent->komoditas_id ?>" title="Copy">
                                                        <i class="fa fa-copy"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (ce_cek_hak_akses('admin.komoditas.delete')): ?>
                                                    <button type="button" class="btn btn-danger btn-xs btn-delete" data-id="<?= $parent->komoditas_id ?>" title="Hapus">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($parent->sub_komoditas)): ?>
                                            <ul class="tree-view">
                                                <?php foreach ($parent->sub_komoditas as $child): ?>
                                                    <li>
                                                        <div class="child-item" data-id="<?= $child->komoditas_id ?>">
                                                            <i class="fa fa-leaf"></i> <?= $child->komoditas_nama ?>
                                                            <?php if ($child->komoditas_satuan): ?>
                                                                <small class="text-muted">(<?= $child->komoditas_satuan ?>)</small>
                                                            <?php endif; ?>
                                                            <div class="komoditas-actions">
                                                                <?php if (ce_cek_hak_akses('admin.komoditas.update')): ?>
                                                                    <button type="button" class="btn btn-warning btn-xs btn-edit" data-id="<?= $child->komoditas_id ?>" title="Edit">
                                                                        <i class="fa fa-edit"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if (ce_cek_hak_akses('admin.komoditas.add')): ?>
                                                                    <button type="button" class="btn btn-info btn-xs btn-copy" data-id="<?= $child->komoditas_id ?>" title="Copy">
                                                                        <i class="fa fa-copy"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                                <?php if (ce_cek_hak_akses('admin.komoditas.delete')): ?>
                                                                    <button type="button" class="btn btn-danger btn-xs btn-delete" data-id="<?= $child->komoditas_id ?>" title="Hapus">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- DataTable View -->
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-table"></i> Daftar Komoditas</h3>
                        <div class="box-tools">
                            <button type="button" class="btn btn-default btn-sm" onclick="$('#dataTable').DataTable().ajax.reload();" title="Refresh">
                                <i class="fa fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table id="dataTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Komoditas</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th width="8%">Urutan</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Aksi</th>
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

<!-- Modal Tambah/Edit Komoditas -->
<div class="modal fade" id="modalKomoditas">
    <div class="modal-dialog">
        <?= form_open('', ['id' => 'formKomoditas']); ?>
            <input type="hidden" name="id" id="id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalKomoditasLabel">Form Komoditas</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="komoditas_nama">Nama Komoditas <span class="text-danger">*</span></label>
                        <input type="text" name="komoditas_nama" class="form-control" id="komoditas_nama" required>
                    </div>
                    <div class="form-group">
                        <label for="komoditas_parent_id">Kategori <span class="text-danger">*</span></label>
                        <select name="komoditas_parent_id" id="komoditas_parent_id" class="form-control" required>
                            <option value="0">-- Kategori Utama --</option>
                            <?php foreach ($parent_komoditas as $parent): ?>
                                <option value="<?= $parent->komoditas_id ?>"><?= $parent->komoditas_nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="komoditas_satuan">Satuan</label>
                        <input type="text" name="komoditas_satuan" class="form-control" id="komoditas_satuan" placeholder="kg, liter, gram, dll">
                        <small class="text-muted">Kosongkan jika kategori utama</small>
                    </div>
                    <div class="form-group">
                        <label for="komoditas_deskripsi">Deskripsi</label>
                        <textarea name="komoditas_deskripsi" class="form-control" id="komoditas_deskripsi" rows="3" placeholder="Deskripsi komoditas (opsional)"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="komoditas_urutan">Urutan <span class="text-danger">*</span></label>
                                <input type="number" name="komoditas_urutan" class="form-control" id="komoditas_urutan" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="komoditas_status">Status <span class="text-danger">*</span></label>
                                <select name="komoditas_status" id="komoditas_status" class="form-control" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        <?= form_close(); ?>
    </div>
</div>
