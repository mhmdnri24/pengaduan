<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!-- Detail Layanan Utama -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Layanan</h3>
                <div class="box-tools">
                    <a href="<?= site_url('layanan_detail') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <?php if (ce_cek_hak_akses('admin.layanan_detail.update')): ?>
                    <button type="button" class="btn btn-warning btn-sm" onclick="edit_data(<?= $layanan->layanan_id ?>)">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="20%">Nama Layanan</th>
                        <td width="30%"><?= $layanan->layanan_nama ?></td>
                        <th width="20%">Kategori</th>
                        <td width="30%"><span class="label label-info"><?= $layanan->layanan_kategori ?></span></td>
                    </tr>
                    <tr>
                        <th>Durasi</th>
                        <td><?= $layanan->layanan_durasi ?> hari</td>
                        <th>Status</th>
                        <td>
                            <?php if ($layanan->layanan_status == 1): ?>
                                <span class="label label-success">Aktif</span>
                            <?php else: ?>
                                <span class="label label-danger">Tidak Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Deskripsi</th>
                        <td colspan="3"><?= nl2br($layanan->layanan_deskripsi) ?></td>
                    </tr>
                    <?php if ($layanan->layanan_contoh): ?>
                    <tr>
                        <th>Contoh</th>
                        <td colspan="3"><?= nl2br($layanan->layanan_contoh) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isset($layanan->layanan_created_at) || isset($layanan->layanan_updated_at)): ?>
                    <tr>
                        <?php if (isset($layanan->layanan_created_at)): ?>
                        <th>Dibuat</th>
                        <td><?= date('d/m/Y H:i', strtotime($layanan->layanan_created_at)) ?></td>
                        <?php endif; ?>
                        <?php if (isset($layanan->layanan_updated_at)): ?>
                        <th>Diperbarui</th>
                        <td><?= date('d/m/Y H:i', strtotime($layanan->layanan_updated_at)) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Persyaratan Dokumen (Lebar Penuh) -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Persyaratan Dokumen</h3>
                <div class="box-tools">
                    <?php if (ce_cek_hak_akses('admin.layanan_detail.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="tambah_persyaratan()">
                        <i class="fa fa-plus"></i> Tambah Persyaratan
                    </button>
                    <?php endif; ?>
                    <a href="<?= site_url('jenis_layanan') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Jenis Layanan
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table id="tablePersyaratan" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="20%">Nama Dokumen</th>
                                <th width="25%">Deskripsi</th>
                                <th width="15%">Contoh File</th>
                                <th width="8%">Wajib</th>
                                <th width="8%">Urutan</th>
                                <th width="8%">Status</th>
                                <?php if (ce_cek_hak_akses('admin.layanan_detail.update') || ce_cek_hak_akses('admin.layanan_detail.delete')): ?>
                                <th width="15%">Aksi</th>
                                <?php endif; ?>
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

<!-- Layanan Terkait (Sidebar) -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-solid box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Layanan Terkait</h3>
            </div>
            <div class="box-body">
                <?php
                $layanan_terkait = $this->db->where('layanan_kategori', $layanan->layanan_kategori)
                                           ->where('layanan_id !=', $layanan->layanan_id)
                                           ->where('layanan_status', 1)
                                           ->limit(5)
                                           ->get('layanan_jenis')->result();

                if ($layanan_terkait):
                    echo '<div class="row">';
                    $counter = 0;
                    foreach ($layanan_terkait as $terkait):
                        if ($counter % 3 == 0 && $counter > 0) echo '</div><div class="row">';
                ?>
                <div class="col-md-4">
                    <div class="media">
                        <div class="media-left">
                            <i class="fa fa-cog media-object" style="font-size: 20px; color: #3c8dbc;"></i>
                        </div>
                        <div class="media-body">
                            <h5 class="media-heading">
                                <a href="<?= site_url('layanan_detail/detail/'.$terkait->layanan_id) ?>">
                                    <?= $terkait->layanan_nama ?>
                                </a>
                            </h5>
                        </div>
                    </div>
                </div>
                <?php
                        $counter++;
                    endforeach;
                    echo '</div>';
                else:
                ?>
                <p class="text-muted text-center">Tidak ada layanan terkait</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Persyaratan -->
<div class="modal fade" id="modalPersyaratan" tabindex="-1" role="dialog" aria-labelledby="modalPersyaratanLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalPersyaratanLabel">Form Persyaratan</h4>
            </div>
            <form id="formPersyaratan">
                <div class="modal-body">
                    <input type="hidden" id="layanan_detail_id" name="layanan_detail_id">
                    <input type="hidden" id="layanan_id_form" name="layanan_id" value="<?= $layanan->layanan_id ?>">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="layanan_detail_nama">Nama Persyaratan <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="layanan_detail_nama" name="layanan_detail_nama" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="layanan_detail_urutan">Urutan</label>
                                <input type="number" class="form-control" id="layanan_detail_urutan" name="layanan_detail_urutan" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="layanan_detail_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="layanan_detail_deskripsi" name="layanan_detail_deskripsi" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_detail_tipe_file">Tipe File yang Diterima</label>
                                <input type="text" class="form-control" id="layanan_detail_tipe_file" name="layanan_detail_tipe_file" value="PDF,JPG,PNG" placeholder="PDF,JPG,PNG">
                                <small class="text-muted">Pisahkan dengan koma (,)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_detail_ukuran_max">Ukuran Maksimal (KB)</label>
                                <input type="number" class="form-control" id="layanan_detail_ukuran_max" name="layanan_detail_ukuran_max" min="1" value="5120">
                                <small class="text-muted">Default: 5120 KB (5 MB)</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contoh_file">Upload Contoh Dokumen (Opsional)</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="contoh_file" name="contoh_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-info" id="btnPreviewFile" style="display:none;" onclick="previewFile()">
                                    <i class="fa fa-eye"></i> Preview
                                </button>
                                <button type="button" class="btn btn-danger" id="btnRemoveFile" style="display:none;" onclick="removeFile()">
                                    <i class="fa fa-trash"></i> Hapus
                                </button>
                            </span>
                        </div>
                        <small class="text-muted">File yang diizinkan: PDF, DOC, DOCX, JPG, PNG. Maksimal 5MB</small>
                        <div id="currentFileInfo" style="display:none; margin-top:5px;">
                            <small class="text-info">
                                <i class="fa fa-file"></i> File saat ini: <span id="currentFileName"></span>
                                <button type="button" class="btn btn-xs btn-info" onclick="previewCurrentFile()">Preview</button>
                            </small>
                        </div>
                        <div id="uploadProgress" style="display:none; margin-top:5px;">
                            <div class="progress progress-xs">
                                <div class="progress-bar progress-bar-primary" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted">Mengupload...</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="layanan_detail_wajib" name="layanan_detail_wajib" value="1" checked>
                                        Persyaratan Wajib
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="layanan_detail_status" name="layanan_detail_status" value="1" checked>
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

<!-- Modal Preview File -->
<div class="modal fade" id="modalPreviewFile" tabindex="-1" role="dialog" aria-labelledby="modalPreviewFileLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalPreviewFileLabel">Preview Dokumen</h4>
            </div>
            <div class="modal-body">
                <div id="filePreviewContainer" style="text-align: center; min-height: 400px;">
                    <!-- Preview content akan dimuat di sini -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <a id="downloadFileBtn" href="#" class="btn btn-primary" target="_blank">
                    <i class="fa fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Layanan (akan dimuat dari JavaScript) -->
<div class="modal fade" id="modalLayanan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Konten akan dimuat via AJAX -->
        </div>
    </div>
</div>

<style>
/* Enhanced table styling for wider layout */
#tablePersyaratan {
    margin-bottom: 0;
}

#tablePersyaratan th {
    background-color: #f4f4f4;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}

/* Enhanced detail table styling */
.table > tbody > tr > th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-right: 2px solid #dee2e6;
}

/* Enhanced media styling for layanan terkait */
.media {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background-color: #fff;
    transition: all 0.3s ease;
}

.media:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: #3c8dbc;
}

.media .media-left {
    padding-right: 15px;
}

.media .media-left i {
    padding: 8px;
    border-radius: 50%;
    background-color: #f8f9fa;
}

.media .media-heading a {
    color: #3c8dbc;
    text-decoration: none;
    font-weight: 600;
}

.media .media-heading a:hover {
    color: #2980b9;
    text-decoration: underline;
}

/* File preview styles */
#filePreviewContainer img {
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#uploadProgress .progress {
    margin-bottom: 5px;
}

.file-preview-btn {
    margin-right: 5px;
}

#currentFileInfo {
    background-color: #f9f9f9;
    padding: 8px;
    border-radius: 4px;
    border-left: 3px solid #3c8dbc;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .media {
        padding: 10px;
    }

    .table-responsive {
        border: none;
    }

    .box-header .box-tools {
        margin-top: 5px;
    }

    .box-header .box-tools .btn {
        margin-bottom: 5px;
    }
}
</style>
