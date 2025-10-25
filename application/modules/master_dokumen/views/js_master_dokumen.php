<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    // Initialize Select2
    $('.select2').select2({
        placeholder: function() {
            return $(this).find('option:first').text();
        },
        allowClear: true
    });

    // DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('master_dokumen/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.kelompok = $('#filter_kelompok').val();
                d.status = $('#filter_status').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": false }],
        "order": [[1, 'asc']]
    });

    // Filter events
    $('#filter_kelompok, #filter_status').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset').click(function() {
        $('#filter_kelompok, #filter_status').val(null).trigger('change');
    });

    // Reset modal saat ditutup
    $('#modalDokumen').on('hidden.bs.modal', function() {
        $('#formDokumen')[0].reset();
        $('#id').val('');
        $('#modalDokumenLabel').text('Form Dokumen');
        $('#contoh_preview').html('');
    });

    // Submit form
    $('#formDokumen').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('master_dokumen/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#modalDokumen').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var deskripsi = $(this).data('deskripsi');
        var kelompok = $(this).data('kelompok');
        var wajib = $(this).data('wajib');
        var contoh = $(this).data('contoh');
        
        $('#id').val(id);
        $('#dokumen_nama').val(nama);
        $('#dokumen_deskripsi').val(deskripsi);
        $('#kelompok_wisuda_id').val(kelompok);
        $('#dokumen_wajib').prop('checked', wajib == 1);
        $('#modalDokumenLabel').text('Edit Dokumen');
        
        if (contoh) {
            var fileExt = contoh.split('.').pop().toLowerCase();
            var fileName = contoh.split('/').pop();
            var previewHtml = '';

            if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                previewHtml = '<div class="mt-2"><strong>File saat ini:</strong><br><img src="<?= base_url() ?>' + contoh + '" class="img-thumbnail" style="max-width: 200px; margin-top: 5px;"></div>';
            } else {
                previewHtml = '<div class="mt-2"><strong>File saat ini:</strong><br><a href="<?= base_url() ?>' + contoh + '" target="_blank" class="btn btn-sm btn-info" style="margin-top: 5px;"><i class="fa fa-download"></i> ' + fileName + '</a></div>';
            }

            $('#contoh_preview').html(previewHtml);
        }
        
        $('#modalDokumen').modal('show');
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('master_dokumen/delete') ?>',
                type: 'POST',
                data: get_post_data({ id: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan sistem');
                }
            });
        }
    });

    // Preview file saat dipilih
    $('#contoh_dokumen').on('change', function() {
        var file = this.files[0];
        if (file) {
            var fileExt = file.name.split('.').pop().toLowerCase();
            var previewHtml = '';
            
            if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#contoh_preview').html('<div class="mt-2"><img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px;"></div>');
                };
                reader.readAsDataURL(file);
            } else {
                $('#contoh_preview').html('<div class="mt-2"><span class="label label-info">' + file.name + '</span></div>');
            }
        } else {
            $('#contoh_preview').html('');
        }
    });
});
</script>
