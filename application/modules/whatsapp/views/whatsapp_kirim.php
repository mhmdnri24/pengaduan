<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-info">
            <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
            <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
            <p>Format nomor HP: 08xxxxxxxx atau 628xxxxxxxx</p>
        </div>
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Kirim Pesan WhatsApp</h3>
            </div>
            <?= form_open(); ?>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group <?= form_error('nomor') ? 'has-error' : ''; ?>">
                            <label for="nomor">Nomor HP <span class="text-danger">*</span></label>
                            <input type="text" name="nomor" class="form-control" id="nomor" placeholder="Contoh: 08123456789" 
                                value="<?= set_value('nomor'); ?>" required>
                            <?= form_error('nomor', '<span class="help-block">', '</span>'); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="nama_tujuan">Nama Penerima</label>
                            <input type="text" name="nama_tujuan" class="form-control" id="nama_tujuan" placeholder="Nama Penerima" 
                                value="<?= set_value('nama_tujuan'); ?>">
                        </div>
                        
                        <?php if (!empty($cabang)): ?>
                        <div class="form-group">
                            <label for="cabang_id">Kirim dari Cabang</label>
                            <select name="cabang_id" class="form-control" id="cabang_id">
                                <option value="">-- Default --</option>
                                <?php foreach ($cabang as $c): ?>
                                <option value="<?= $c->id_cabang; ?>" <?= set_select('cabang_id', $c->id_cabang); ?>>
                                    <?= $c->nama_cabang; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-block">Opsional. Pilih cabang jika ingin menggunakan sender ID khusus.</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="template_id">Gunakan Template</label>
                            <select name="template_id" class="form-control" id="template_id">
                                <option value="">-- Pilih Template --</option>
                                <?php foreach ($templates as $template): ?>
                                <option value="<?= $template->id; ?>" <?= set_select('template_id', $template->id); ?>>
                                    <?= $template->nama; ?> [<?= $template->kode; ?>]
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group <?= form_error('pesan') ? 'has-error' : ''; ?>">
                            <label for="pesan">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea name="pesan" class="form-control" id="pesan" placeholder="Isi pesan" rows="12" required><?= set_value('pesan'); ?></textarea>
                            <?= form_error('pesan', '<span class="help-block">', '</span>'); ?>
                        </div>
                        
                        <div id="template-params" style="display: none;">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Parameter Template</h3>
                                </div>
                                <div class="panel-body" id="param-inputs">
                                    <!-- Parameter inputs will be populated here -->
                                </div>
                                <div class="panel-footer">
                                    <button type="button" id="btn-apply-params" class="btn btn-sm btn-primary">
                                        <i class="fa fa-check"></i> Terapkan Parameter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-paper-plane"></i> Kirim Pesan
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fungsi untuk ekstrak parameter dari template
    function extractParams(template) {
        var matches = template.match(/\{([^}]+)\}/g) || [];
        var params = [];
        
        for (var i = 0; i < matches.length; i++) {
            var param = matches[i].replace(/[\{\}]/g, '');
            if (params.indexOf(param) === -1) {
                params.push(param);
            }
        }
        
        return params;
    }
    
    // Fungsi untuk menerapkan parameter ke template
    function applyParams(template, params) {
        var result = template;
        
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                var regex = new RegExp('\\{' + key + '\\}', 'g');
                result = result.replace(regex, params[key]);
            }
        }
        
        return result;
    }
    
    // Handler ketika template dipilih
    $('#template_id').change(function() {
        var templateId = $(this).val();
        
        if (!templateId) {
            $('#template-params').hide();
            return;
        }
        
        $.ajax({
            url: '<?= site_url('whatsapp/get_template'); ?>',
            type: 'POST',
            data: { template_id: templateId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var template = response.data;
                    $('#pesan').val(template.isi_template);
                    
                    // Ekstrak parameter dari template
                    var params = extractParams(template.isi_template);
                    
                    if (params.length > 0) {
                        var paramHtml = '';
                        for (var i = 0; i < params.length; i++) {
                            paramHtml += '<div class="form-group">';
                            paramHtml += '<label for="param_' + params[i] + '">' + params[i] + ':</label>';
                            paramHtml += '<input type="text" class="form-control param-input" id="param_' + params[i] + '" data-name="' + params[i] + '">';
                            paramHtml += '</div>';
                        }
                        
                        $('#param-inputs').html(paramHtml);
                        $('#template-params').show();
                    } else {
                        $('#template-params').hide();
                    }
                }
            },
            error: function() {
                alert('Gagal memuat template');
            }
        });
    });
    
    // Handler untuk tombol terapkan parameter
    $('#btn-apply-params').click(function() {
        var template = $('#pesan').val();
        var params = {};
        
        $('.param-input').each(function() {
            var name = $(this).data('name');
            var value = $(this).val();
            params[name] = value;
        });
        
        var result = applyParams(template, params);
        $('#pesan').val(result);
    });
});
</script> 