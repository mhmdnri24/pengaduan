<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
$(document).ready(function() {

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    // DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('jenis_layanan/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.kategori = $('#filter_kategori').val();
                d.wajib = $('#filter_wajib').val();
                d.status = $('#filter_status').val();
                d.unitkerja = $('#filter_unitkerja').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": false }],
        "order": [[1, 'asc']]
    });

    // Filter change events
    $('#filter_kategori, #filter_wajib, #filter_status').change(function() {
        table.draw();
    });

    // Unit kerja filter (only for super admin)
    $('#filter_unitkerja').change(function() {
        table.draw();
    });

    // Initialize filter unitkerja Select2 (similar to other filters)
    $('#filter_unitkerja').select2({
        placeholder: 'Semua Unit Kerja',
        width: '100%',
        allowClear: true
    });

    // Reset filter
    $('#btn-reset').click(function() {
        $('#filter_kategori').val('').trigger('change');
        $('#filter_wajib').val('').trigger('change');
        $('#filter_status').val('').trigger('change');
        $('#filter_unitkerja').val('').trigger('change');
        table.draw();
    });

    // Form submit
    $('#formLayanan').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('jenis_layanan/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#formLayanan button[type="submit"]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalLayanan').modal('hide');
                    table.draw();
                    toastr.success(response.message);
                    $('#formLayanan')[0].reset();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#formLayanan button[type="submit"]').prop('disabled', false).text('Simpan');
            }
        });
    });

    // Reset form when modal is closed
    $('#modalLayanan').on('hidden.bs.modal', function() {
        $('#formLayanan')[0].reset();
        $('#id').val('');
        $('#modalLayananLabel').text('Form Layanan');
        $('#layanan_kategori').val('').trigger('change');
        $('#layanan_status').prop('checked', true);
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var deskripsi = $(this).data('deskripsi');
        // biaya dihapus
        var durasi = $(this).data('durasi');
        var kategori = $(this).data('kategori');
        var wajib = $(this).data('wajib');
        var contoh = $(this).data('contoh');
        var urutan = $(this).data('urutan');
        var status = $(this).data('status');
        var unitkerja = $(this).data('unitkerja');

        $('#id').val(id);
        $('#layanan_nama').val(nama);
        $('#layanan_deskripsi').val(deskripsi);
        // $('#layanan_biaya').val(biaya); // dihapus
        $('#layanan_durasi').val(durasi);
        $('#layanan_kategori').val(kategori).trigger('change');
        $('#layanan_wajib').prop('checked', wajib == 1);
        $('#layanan_contoh').val(contoh);
        $('#layanan_urutan').val(urutan);
        $('#layanan_status').prop('checked', status == 1);
        $('#layanan_unitkerja').val(unitkerja).trigger('change');
        $('#layanan_unitkerja_user').val(unitkerja).trigger('change');
        $('#modalLayananLabel').text('Edit Layanan');

        $('#modalLayanan').modal('show');
    });

    // Initialize Select2 when modal is shown
    $('#modalLayanan').on('shown.bs.modal', function() {
        // Initialize kategori Select2
        if ($('#layanan_kategori').hasClass('select2-hidden-accessible')) {
            $('#layanan_kategori').select2('destroy');
        }
        $('#layanan_kategori').select2({
            placeholder: 'Pilih Kategori Layanan',
            width: '100%',
            dropdownParent: $('#modalLayanan .modal-body'),  // Fix dropdown positioning in modal
            minimumResultsForSearch: 0,  // Always show search box
            minimumInputLength: 0,  // Allow search from 0 characters
            allowClear: true
        });

        // Initialize unitkerja Select2 (similar to kategori)
        if ($('#layanan_unitkerja').hasClass('select2-hidden-accessible')) {
            $('#layanan_unitkerja').select2('destroy');
        }
        if ($('#layanan_unitkerja_user').hasClass('select2-hidden-accessible')) {
            $('#layanan_unitkerja_user').select2('destroy');
        }
        if ($('#layanan_unitkerja').length > 0) {
            $('#layanan_unitkerja').select2({
                placeholder: 'Pilih Unit Kerja',
                width: '100%',
                dropdownParent: $('#modalLayanan .modal-body'),  // Fix dropdown positioning in modal
                minimumResultsForSearch: 0,  // Always show search box
                minimumInputLength: 0,  // Allow search from 0 characters
                allowClear: true
            });
        }

        // Initialize user unitkerja Select2 (disabled for non-super admin)
        if ($('#layanan_unitkerja_user').hasClass('select2-hidden-accessible')) {
            $('#layanan_unitkerja_user').select2('destroy');
        }
        if ($('#layanan_unitkerja_user').length > 0) {
            $('#layanan_unitkerja_user').select2({
                placeholder: 'Pilih Unit Kerja',
                width: '100%',
                dropdownParent: $('#modalLayanan .modal-body'),
                minimumResultsForSearch: 0,
                minimumInputLength: 0,
                allowClear: false,
                disabled: true
            });
        }

        // Focus on search field when dropdown opens for kategori
        $('#layanan_kategori').on('select2:open', function () {
            setTimeout(function() {
                $('.select2-search--dropdown .select2-search__field').focus();
            }, 10);
        });

        // Focus on search field when dropdown opens for unitkerja
        $('#layanan_unitkerja').on('select2:open', function () {
            setTimeout(function() {
                $('.select2-search--dropdown .select2-search__field').focus();
            }, 10);
        });
    });

    // Destroy Select2 when modal is hidden to prevent conflicts
    $('#modalLayanan').on('hidden.bs.modal', function() {
        if ($('#layanan_kategori').hasClass('select2-hidden-accessible')) {
            $('#layanan_kategori').select2('destroy');
        }
        if ($('#layanan_unitkerja').hasClass('select2-hidden-accessible')) {
            $('#layanan_unitkerja').select2('destroy');
        }
        $('#layanan_kategori').val('').trigger('change');
        $('#layanan_unitkerja').val('').trigger('change');
        $('#layanan_unitkerja_user').val('<?= $this->session->userdata('id_unitkerja') ?>').trigger('change');
        $('#layanan_unitkerja_user').val('').trigger('change');
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('jenis_layanan/delete') ?>',
                type: 'POST',
                data: {
                    id: id,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        table.draw();
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

});
</script>
