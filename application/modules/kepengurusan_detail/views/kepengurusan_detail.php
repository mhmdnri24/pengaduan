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
.panel {
    margin-bottom: 20px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.panel-heading {
    border-radius: 6px 6px 0 0;
}
.widget-user-2 .widget-user-header {
    padding: 20px;
    border-radius: 5px 5px 0 0;
}
.widget-user-image img {
    width: 45px;
    height: 45px;
    border: 3px solid #fff;
}
.modal-xl {
    width: 95%;
    max-width: 1200px;
}
.bg-primary {
    background-color: #3c8dbc !important;
}
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Data Kepengurusan Detail</h3>
                <div class="box-tools pull-right">
                    <?php if (ce_hak_akses('admin.kepengurusan_detail.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add">
                        <i class="fa fa-plus"></i> Tambah Data
                    </button>
                    <?php endif; ?>
                    <?php if (ce_hak_akses('admin.kepengurusan_detail.update')): ?>
                    <button type="button" class="btn btn-warning btn-sm" id="btn-update-expired">
                        <i class="fa fa-refresh"></i> Update Status Berakhir
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-tasks"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Penugasan</span>
                                <span class="info-box-number"><?= $total_penugasan; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Penugasan Aktif</span>
                                <span class="info-box-number"><?= $penugasan_aktif; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="fa fa-flag-checkered"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Penugasan Selesai</span>
                                <span class="info-box-number"><?= $penugasan_selesai; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Penugasan Nonaktif</span>
                                <span class="info-box-number"><?= $penugasan_nonaktif; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status --</option>
                            <option value="AKTIF">Aktif</option>
                            <option value="SELESAI">Selesai</option>
                            <option value="NONAKTIF">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kategori">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($kategori_kepengurusan_list as $id => $nama): ?>
                                <option value="<?= $id; ?>"><?= $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_fasilitas">
                            <option value="">-- Semua Fasilitas --</option>
                            <?php foreach ($fasilitas_umum_list as $id => $nama): ?>
                                <option value="<?= $id; ?>"><?= $nama; ?></option>
                            <?php endforeach; ?>
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
                                <th>Nama Pengurus</th>
                                <th>Kategori</th>
                                <th>Fasilitas</th>
                                <th>Tanggal SK</th>
                                <th>Nomor SK</th>
                                <th>Masa Jabatan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title text-white"><i class="fa fa-tasks"></i> Form Kepengurusan Detail</h4>
            </div>
            <form id="form-detail" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="file_sk_existing" id="file_sk_existing">

                    <!-- Section 1: Pilih Pengurus -->
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h4 class="panel-title"><i class="fa fa-user"></i> Pilih Pengurus</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kepengurusan_sosial_id"><i class="fa fa-users"></i> Nama Pengurus <span class="text-red">*</span></label>
                                        <select name="kepengurusan_sosial_id" id="kepengurusan_sosial_id" class="form-control select2" style="width: 100%;" data-placeholder="Pilih Pengurus" required>
                                            <option value="">Pilih Pengurus</option>
                                            <?php foreach ($kepengurusan_sosial_list as $id => $nama): ?>
                                                <option value="<?= $id; ?>"><?= $nama; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Profile Preview akan muncul di sini -->
                                    <div id="profile-preview" style="display: none;">
                                        <div class="box box-widget widget-user-2">
                                            <div class="widget-user-header bg-blue">
                                                <div class="widget-user-image">
                                                    <img class="img-circle" id="preview-foto" src="" alt="Foto Profil">
                                                </div>
                                                <h3 class="widget-user-username" id="preview-nama">-</h3>
                                                <h5 class="widget-user-desc" id="preview-nik">-</h5>
                                            </div>
                                            <div class="box-footer no-padding">
                                                <ul class="nav nav-stacked">
                                                    <li><a href="#"><i class="fa fa-phone"></i> <span id="preview-hp">-</span></a></li>
                                                    <li><a href="#"><i class="fa fa-venus-mars"></i> <span id="preview-gender">-</span></a></li>
                                                    <li><a href="#"><i class="fa fa-book"></i> <span id="preview-agama">-</span></a></li>
                                                    <li><a href="#"><i class="fa fa-graduation-cap"></i> <span id="preview-pendidikan">-</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Detail Penugasan -->
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h4 class="panel-title"><i class="fa fa-clipboard"></i> Detail Penugasan</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kategori_kepengurusan_id"><i class="fa fa-list"></i> Kategori Kepengurusan <span class="text-red">*</span></label>
                                        <select name="kategori_kepengurusan_id" id="kategori_kepengurusan_id" class="form-control select2" style="width: 100%;" data-placeholder="Pilih Kategori" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($kategori_kepengurusan_list as $id => $nama): ?>
                                                <option value="<?= $id; ?>"><?= $nama; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_fasilitas_umum"><i class="fa fa-building"></i> Fasilitas Umum (Masjid/Mushola) <span class="text-red">*</span></label>
                                        <select name="id_fasilitas_umum" id="id_fasilitas_umum" class="form-control select2" style="width: 100%;" data-placeholder="Pilih Fasilitas" required>
                                            <option value="">Pilih Fasilitas</option>
                                            <?php foreach ($fasilitas_umum_list as $id => $nama): ?>
                                                <option value="<?= $id; ?>"><?= $nama; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Informasi SK -->
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4 class="panel-title"><i class="fa fa-file-text"></i> Informasi Surat Keputusan</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_sk"><i class="fa fa-calendar"></i> Tanggal SK</label>
                                        <input type="date" name="tanggal_sk" class="form-control" id="tanggal_sk">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nomor_sk"><i class="fa fa-hashtag"></i> Nomor SK</label>
                                        <input type="text" name="nomor_sk" class="form-control" id="nomor_sk" placeholder="Contoh: SK/001/2024">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="file_sk"><i class="fa fa-upload"></i> File SK (PDF/DOC/IMG)</label>
                                        <div class="input-group">
                                            <input type="file" name="file_sk" class="form-control" id="file_sk" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            <span class="input-group-addon"><i class="fa fa-file"></i></span>
                                        </div>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Format: PDF, DOC, DOCX, JPG, JPEG, PNG. Maksimal 5MB</small>
                                        <div id="current-file" style="margin-top: 5px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Periode Jabatan -->
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h4 class="panel-title"><i class="fa fa-clock-o"></i> Periode Jabatan</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="masa_jabatan"><i class="fa fa-hourglass-half"></i> Masa Jabatan (tahun)</label>
                                        <select name="masa_jabatan" class="form-control select2" id="masa_jabatan" style="width: 100%;">
                                            <option value="">Pilih Masa Jabatan</option>
                                            <option value="1">1 Tahun</option>
                                            <option value="2">2 Tahun</option>
                                            <option value="3">3 Tahun</option>
                                            <option value="4">4 Tahun</option>
                                            <option value="5">5 Tahun</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tanggal_mulai"><i class="fa fa-play"></i> Tanggal Mulai</label>
                                        <input type="date" name="tanggal_mulai" class="form-control" id="tanggal_mulai">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tanggal_selesai"><i class="fa fa-stop"></i> Tanggal Selesai</label>
                                        <input type="date" name="tanggal_selesai" class="form-control" id="tanggal_selesai" readonly>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Otomatis terisi berdasarkan masa jabatan</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="status"><i class="fa fa-flag"></i> Status Penugasan</label>
                                        <select name="status" id="status" class="form-control select2" style="width: 100%;">
                                            <option value="AKTIF">🟢 Aktif</option>
                                            <option value="SELESAI">🔵 Selesai</option>
                                            <option value="NONAKTIF">🔴 Nonaktif</option>
                                        </select>
                                    </div>
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
