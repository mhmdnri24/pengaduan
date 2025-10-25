<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-info">
            <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
            <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
            <p>Gunakan format <code>{nama_variabel}</code> untuk placeholder yang akan diganti dengan nilai sesuai saat mengirim pesan.</p>
            <p>Contoh: <code>Halo {nama}, tagihan Anda sejumlah Rp {tagihan} akan jatuh tempo pada {tanggal}.</code></p>
            <p>Template mendukung emoji 😊👍 dan format teks seperti <i>baris baru</i>.</p>
        </div>
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $action == 'tambah' ? 'Tambah' : 'Edit'; ?> Template WhatsApp</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('whatsapp/template'); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <?= form_open(); ?>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group <?= form_error('nama') ? 'has-error' : ''; ?>">
                            <label for="nama">Nama Template <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" id="nama" placeholder="Nama Template" 
                                value="<?= set_value('nama', isset($template) ? $template->nama : ''); ?>" required>
                            <?= form_error('nama', '<span class="help-block">', '</span>'); ?>
                        </div>
                        
                        <div class="form-group <?= form_error('kode') ? 'has-error' : ''; ?>">
                            <label for="kode">Kode Template <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control" id="kode" placeholder="Kode Template (tanpa spasi)" 
                                value="<?= set_value('kode', isset($template) ? $template->kode : ''); ?>" required>
                            <?= form_error('kode', '<span class="help-block">', '</span>'); ?>
                            <span class="help-block">Gunakan huruf, angka, underscore dan tanpa spasi (mis: pengingat_tagihan)</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" id="deskripsi" placeholder="Deskripsi Template" 
                                rows="3"><?= set_value('deskripsi', isset($template) ? $template->deskripsi : ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group <?= form_error('isi_template') ? 'has-error' : ''; ?>">
                            <label for="isi_template">Isi Template <span class="text-danger">*</span></label>
                            <textarea name="isi_template" class="form-control" id="isi_template" placeholder="Isi Template" 
                                rows="8" required><?= set_value('isi_template', isset($template) ? $template->isi_template : ''); ?></textarea>
                            <?= form_error('isi_template', '<span class="help-block">', '</span>'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="params">Parameter Preview</label>
                            <textarea name="params" class="form-control" id="params" placeholder="nama=John Doe&#10;tagihan=500.000&#10;tanggal=01/01/2023" 
                                rows="3"></textarea>
                            <span class="help-block">Format: satu baris per parameter, format: nama_parameter=nilai</span>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" id="btn-preview" class="btn btn-info btn-sm">
                                <i class="fa fa-eye"></i> Preview Template
                            </button>
                        </div>
                        
                        <div id="preview-result" class="well" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan
                </button>
                <a href="<?= site_url('whatsapp/template'); ?>" class="btn btn-default">
                    <i class="fa fa-times"></i> Batal
                </a>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Preview template
    $('#btn-preview').on('click', function() {
        var isi_template = $('#isi_template').val();
        var params = $('#params').val();
        
        if (isi_template.trim() === '') {
            alert('Isi template tidak boleh kosong');
            return;
        }
        
        $.ajax({
            url: '<?= site_url('whatsapp/preview_template'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                isi_template: isi_template,
                params: params
            },
            success: function(response) {
                if (response.status) {
                    var previewText = response.preview.replace(/\n/g, '<br>');
                    $('#preview-result').html(previewText).show();
                } else {
                    alert('Gagal memuat preview: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Terjadi kesalahan saat memuat preview. Silakan coba lagi.');
            }
        });
    });
    
    // Format kode template otomatis
    $('#kode').on('input', function() {
        var value = $(this).val();
        value = value.replace(/\s+/g, '_'); // Ganti spasi dengan underscore
        value = value.replace(/[^a-zA-Z0-9_]/g, ''); // Hanya izinkan huruf, angka, dan underscore
        $(this).val(value);
    });
});
</script> 