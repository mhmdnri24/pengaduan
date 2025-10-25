<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #ddd;
    }

    .bulk-actions {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 10px 15px;
        margin-bottom: 15px;
        display: none;
        border-radius: 3px;
    }

    .checkbox-header {
        width: 20px;
        text-align: center;
    }

    .checkbox-cell {
        width: 20px;
        text-align: center;
    }
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-users"></i> Daftar User
                </h3>
                <div class="box-tools pull-right">
                    <?= ce_anchor('admin.user.add', 'user/tambah', '<i class="fa fa-plus"></i> Tambah User', 'class="btn btn-primary btn-sm"');?>
                </div>
            <div class="box-body">
                <!-- Alert Messages -->
                <div class="row">
                    <div class="col-md-12">
                        <?= ce_msg('success');?>
                        <?= ce_msg('danger');?>
                    </div>
                </div>

                <!-- Bulk Actions (Hidden by default) -->
                <div class="bulk-actions" id="bulkActions">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-0"><i class="fa fa-check-square"></i> <span id="selectedCount">0</span> user dipilih</h5>
                        </div>
                        <div class="col-md-4 text-right">
                                    <button type="button" class="btn btn-danger btn-xs" onclick="bulkDelete()">
                                        <i class="fa fa-trash"></i> Hapus Terpilih
                                    </button>
                                    <button type="button" class="btn btn-default btn-xs" onclick="cancelBulk()">
                                        <i class="fa fa-times"></i> Batal
                                    </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box filter-box">
                            <div class="box-header with-border">
                                <h4 class="box-title">
                                    <i class="fa fa-filter"></i> Filter Data
                                </h4>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status User</label>
                                            <select id="filter_status" class="form-control select2" style="width: 100%;">
                                                <option value="">Semua Status</option>
                                                <option value="0">Aktif</option>
                                                <option value="1">Diblokir</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Hak Akses</label>
                                            <select id="filter_level" class="form-control select2" style="width: 100%;">
                                                <option value="">Semua Level</option>
                                                <?php foreach ($level as $lev) {
                                                    if ($lev->id_level != 1 || ($lev->id_level == 1 && $this->session->userdata('id_level') == 1))
                                                        echo '<option value="' . $lev->id_level . '">' . $lev->level . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Instansi</label>
                                            <select id="filter_instansi" class="form-control select2" style="width: 100%;">
                                                <option value="">Semua Instansi</option>
                                                <?php foreach ($instansi as $inst) {
                                                    echo '<option value="' . $inst->id_instansi . '">' . $inst->instansi_nama . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Unit Kerja</label>
                                            <select id="filter_unitkerja" class="form-control select2" style="width: 100%;">
                                                <option value="">Semua Unit Kerja</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="checkbox-header">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th style="width: 10px;">#</th>
                                        <th style="width: 80px;">Foto</th>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Instansi</th>
                                        <th>Unit Kerja</th>
                                        <th>Hak Akses</th>
                                        <th style="width: 100px;">Status</th>
                                        <th style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 dengan konfigurasi minimal untuk menghindari konflik
    $('.select2').select2({
        placeholder: 'Pilih...',
        width: '100%'
    });
});

function delete_confirm() {
    return confirm('Apakah Anda yakin ingin menghapus data ini?');
}
</script>