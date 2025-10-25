<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="row">

    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Silakan isi formulir di bawah ini</h3>
            </div>
            <form method="post" action="" enctype="multipart/form-data" class="form-horizontal">
                <div class="box-body">
                    <fieldset>
                        <legend>Google Gemini AI API</legend>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Pengaturan API untuk Google Gemini AI yang digunakan untuk fitur AI agent pada modul Realisasi.
                        </div>
                        
                        <div class="form-group">
                            <label for="gemini_api_key" class="col-sm-2 control-label">API Key <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="gemini_api_key" class="form-control" id="gemini_api_key" value="<?= ce_opsi('gemini_api_key') ?>" placeholder="Masukkan API Key Gemini" required>
                                <small class="text-muted">
                                    <i class="fa fa-info-circle"></i> API key untuk mengakses Gemini API. 
                                    Dapatkan API key di <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a>.
                                    <br>Pastikan API key memiliki akses ke model yang dipilih.
                                </small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gemini_model" class="col-sm-2 control-label">Model <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select name="gemini_model" class="form-control select2" id="gemini_model" required>
                                    <optgroup label="Gemini 2.5 Models (Terbaru)">
                                        <option value="gemini-2.5-pro" <?= ce_opsi('gemini_model') == 'gemini-2.5-pro' ? 'selected' : '' ?>>Gemini 2.5 Pro (Paling Canggih)</option>
                                        <option value="gemini-2.5-flash" <?= ce_opsi('gemini_model') == 'gemini-2.5-flash' ? 'selected' : '' ?>>Gemini 2.5 Flash (Seimbang)</option>
                                        <option value="gemini-2.5-flash-lite-preview-06-17" <?= ce_opsi('gemini_model') == 'gemini-2.5-flash-lite-preview-06-17' ? 'selected' : '' ?>>Gemini 2.5 Flash-Lite (Paling Efisien)</option>
                                    </optgroup>
                                    <optgroup label="Gemini 2.0 Models">
                                        <option value="gemini-2.0-pro" <?= ce_opsi('gemini_model') == 'gemini-2.0-pro' ? 'selected' : '' ?>>Gemini 2.0 Pro</option>
                                        <option value="gemini-2.0-flash" <?= ce_opsi('gemini_model') == 'gemini-2.0-flash' ? 'selected' : '' ?>>Gemini 2.0 Flash</option>
                                        <option value="gemini-2.0-flash-lite" <?= ce_opsi('gemini_model') == 'gemini-2.0-flash-lite' ? 'selected' : '' ?>>Gemini 2.0 Flash-Lite</option>
                                    </optgroup>
                                    <optgroup label="Gemini 1.5 Models">
                                        <option value="gemini-1.5-pro" <?= ce_opsi('gemini_model') == 'gemini-1.5-pro' ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
                                        <option value="gemini-1.5-flash" <?= ce_opsi('gemini_model') == 'gemini-1.5-flash' ? 'selected' : '' ?>>Gemini 1.5 Flash</option>
                                        <option value="gemini-1.5-flash-8b" <?= ce_opsi('gemini_model') == 'gemini-1.5-flash-8b' ? 'selected' : '' ?>>Gemini 1.5 Flash-8B</option>
                                    </optgroup>
                                </select>
                                <small class="text-muted">Model Gemini AI yang digunakan. Disarankan menggunakan model terbaru.</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gemini_temperature" class="col-sm-2 control-label">Temperature <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="number" name="gemini_temperature" class="form-control" id="gemini_temperature" value="<?= ce_opsi('gemini_temperature', '0.7'); ?>" min="0" max="1" step="0.1" required>
                                <small class="text-muted">Nilai temperature untuk kreativitas output (0.0 - 1.0)</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gemini_max_tokens" class="col-sm-2 control-label">Max Tokens <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="number" name="gemini_max_tokens" class="form-control" id="gemini_max_tokens" value="<?= ce_opsi('gemini_max_tokens', '2048'); ?>" min="1" required>
                                <small class="text-muted">Jumlah maksimum token yang dihasilkan</small>
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset>
                        <legend>Whatsapp API</legend>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Pengaturan ini adalah pengaturan default untuk WhatsApp API. 
                            Untuk mengatur Sender ID khusus per cabang, silakan atur di menu <strong>Master &gt; Cabang</strong>.
                        </div>
                        
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Panduan Penggunaan WhatsApp API dengan Multiple Sender</h4>
                            </div>
                            <div class="panel-body">
                                <ol>
                                    <li>Isi pengaturan WhatsApp API default di bawah ini (URL, API Key, dan Default Sender ID)</li>
                                    <li>Untuk menggunakan nomor WhatsApp yang berbeda per cabang:
                                        <ul>
                                            <li>Buka menu <strong>Master &gt; Cabang</strong></li>
                                            <li>Edit cabang yang ingin diatur</li>
                                            <li>Isi field <strong>WhatsApp Sender ID</strong> dengan ID sender khusus untuk cabang tersebut</li>
                                            <li>Simpan perubahan</li>
                                        </ul>
                                    </li>
                                    <li>Sistem akan otomatis mengirim notifikasi menggunakan:
                                        <ul>
                                            <li>Sender ID khusus cabang (jika diisi)</li>
                                            <li>Default Sender ID (jika cabang tidak memiliki Sender ID khusus)</li>
                                        </ul>
                                    </li>
                                </ol>
                                <p><strong>Catatan:</strong> Pastikan semua nomor WhatsApp sudah di-scan di WA Gateway di wsender.ridped.com</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="waapi_url" class="col-sm-2 control-label">URL <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="waapi_url" class="form-control" id="waapi_url" value="<?= ce_opsi('waapi_url'); ?>" required>
                                <small class="text-muted">URL API WhatsApp (contoh: https://wsender.ridped.com/api/send-message)</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="waapi_key" class="col-sm-2 control-label">Api Key <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="waapi_key" class="form-control" id="waapi_key" value="<?= ce_opsi('waapi_key'); ?>" required>
                                <small class="text-muted">API Key untuk mengakses layanan WhatsApp API</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="waapi_sender" class="col-sm-2 control-label">Default Sender ID <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="waapi_sender" class="form-control" id="waapi_sender" value="<?= ce_opsi('waapi_sender'); ?>" required>
                                <small class="text-muted">Sender ID default yang akan digunakan jika tidak ada pengaturan khusus per cabang</small>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>API Kepegawaian</legend>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Pengaturan API untuk mengambil data pegawai berdasarkan NIP.
                        </div>
                        <div class="form-group">
                            <label for="pegawai_api_url" class="col-sm-2 control-label">URL Endpoint</label>
                            <div class="col-sm-8">
                                <input type="text" name="pegawai_api_url" class="form-control" id="pegawai_api_url" value="<?= ce_opsi('pegawai_api_url') ?>" placeholder="Contoh: https://sinanan.bkpsdm.lubuklinggaukota.go.id/api/pegawai/nip/">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pegawai_api_header" class="col-sm-2 control-label">Header Key</label>
                            <div class="col-sm-8">
                                <input type="text" name="pegawai_api_header" class="form-control" id="pegawai_api_header" value="<?= ce_opsi('pegawai_api_header', 'apikey') ?>" placeholder="Contoh: apikey">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pegawai_api_key" class="col-sm-2 control-label">API Key</label>
                            <div class="col-sm-8">
                                <input type="text" name="pegawai_api_key" class="form-control" id="pegawai_api_key" value="<?= ce_opsi('pegawai_api_key') ?>" placeholder="Masukkan API Key">
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>