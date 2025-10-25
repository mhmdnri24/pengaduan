<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="row">

    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Pengaturan API Internal</h3>
                <p class="help-block">Konfigurasi API internal untuk mengakses data aplikasi dari luar.</p>
            </div>
            <form method="post" action="" enctype="multipart/form-data" class="form-horizontal">
                <div class="box-body">
                    <fieldset>
                        <legend>Pengaturan Utama</legend>

                        <div class="form-group">
                            <label for="api_enabled" class="col-sm-3 control-label">Status API <span class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <select name="api_enabled" class="form-control" id="api_enabled" required>
                                    <option value="0" <?= (ce_opsi('api_enabled', '0') == '0') ? 'selected' : '' ?>>Nonaktif</option>
                                    <option value="1" <?= (ce_opsi('api_enabled', '0') == '1') ? 'selected' : '' ?>>Aktif</option>
                                </select>
                                <small class="text-muted">Aktifkan/nonaktifkan API internal</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="base_url" class="col-sm-3 control-label">Base URL <span class="text-danger">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" name="base_url" class="form-control" id="base_url" value="<?= ce_opsi('api_base_url', '/api/v1/') ?>" placeholder="/api/v1/" required>
                                <small class="text-muted">URL dasar untuk endpoint API (contoh: /api/v1/)</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="api_key" class="col-sm-3 control-label">API Key Global</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="password" name="api_key" class="form-control" id="api_key" value="<?= ce_opsi('api_key') ?>" placeholder="Masukkan API Key">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" onclick="generateApiKey()">
                                            <i class="fa fa-refresh"></i> Generate
                                        </button>
                                    </span>
                                </div>
                                <small class="text-muted">API Key global yang digunakan untuk autentikasi. Biarkan kosong untuk disable API Key.</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="jwt_secret" class="col-sm-3 control-label">JWT Secret</label>
                            <div class="col-sm-6">
                                <div class="input-group">
                                    <input type="password" name="jwt_secret" class="form-control" id="jwt_secret" value="<?= ce_opsi('jwt_secret') ?>" placeholder="Masukkan JWT Secret">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" onclick="generateJwtSecret()">
                                            <i class="fa fa-refresh"></i> Generate
                                        </button>
                                    </span>
                                </div>
                                <small class="text-muted">Secret key untuk generate dan validasi JWT token</small>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Rate Limiting</legend>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Batasi jumlah request per IP untuk mencegah abuse.
                        </div>

                        <div class="form-group">
                            <label for="rate_limit_per_minute" class="col-sm-3 control-label">Request per Menit <span class="text-danger">*</span></label>
                            <div class="col-sm-3">
                                <input type="number" name="rate_limit_per_minute" class="form-control" id="rate_limit_per_minute" value="<?= ce_opsi('api_rate_limit_per_minute', '60') ?>" min="1" required>
                                <small class="text-muted">Maksimal request per menit per IP</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rate_limit_per_hour" class="col-sm-3 control-label">Request per Jam <span class="text-danger">*</span></label>
                            <div class="col-sm-3">
                                <input type="number" name="rate_limit_per_hour" class="form-control" id="rate_limit_per_hour" value="<?= ce_opsi('api_rate_limit_per_hour', '1000') ?>" min="1" required>
                                <small class="text-muted">Maksimal request per jam per IP</small>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Domain Whitelist</legend>
                        <div class="alert alert-warning">
                            <i class="fa fa-warning"></i> API Key hanya bisa digunakan dari domain yang terdaftar di whitelist ini.
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addDomainModal">
                                    <i class="fa fa-plus"></i> Tambah Domain
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <table class="table table-bordered table-striped" id="domainTable">
                                    <thead>
                                        <tr>
                                            <th>Domain</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Domain list will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>IP Whitelist</legend>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Jika ada IP yang terdaftar, maka hanya IP tersebut yang bisa mengakses API.
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addIpModal">
                                    <i class="fa fa-plus"></i> Tambah IP
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <table class="table table-bordered table-striped" id="ipTable">
                                    <thead>
                                        <tr>
                                            <th>IP Address</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- IP list will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Modal Tambah Domain -->
<div class="modal fade" id="addDomainModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tambah Domain</h4>
            </div>
            <div class="modal-body">
                <form id="domainForm">
                    <div class="form-group">
                        <label>Domain</label>
                        <input type="text" class="form-control" name="domain" placeholder="example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" name="description" placeholder="Deskripsi domain">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveDomain()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah IP -->
<div class="modal fade" id="addIpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tambah IP Address</h4>
            </div>
            <div class="modal-body">
                <form id="ipForm">
                    <div class="form-group">
                        <label>IP Address</label>
                        <input type="text" class="form-control" name="ip_address" placeholder="192.168.1.1" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <input type="text" class="form-control" name="description" placeholder="Deskripsi IP">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveIp()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
function generateApiKey() {
    const randomKey = 'API_' + Math.random().toString(36).substr(2, 9).toUpperCase() + Date.now().toString(36);
    document.getElementById('api_key').value = randomKey;
}

function generateJwtSecret() {
    const randomSecret = Math.random().toString(36).substr(2, 15) + Math.random().toString(36).substr(2, 15);
    document.getElementById('jwt_secret').value = randomSecret;
}

function saveDomain() {
    // AJAX implementation for saving domain
    alert('Fitur save domain akan diimplementasikan');
}

function saveIp() {
    // AJAX implementation for saving IP
    alert('Fitur save IP akan diimplementasikan');
}
</script>