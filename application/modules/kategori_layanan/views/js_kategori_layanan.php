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
            "url": "<?= base_url('kategori_layanan/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.status = $('#filter_status').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": false }],
        "order": [[1, 'asc']]
    });

    // Filter change events
    $('#filter_status').change(function() {
        table.draw();
    });

    // Reset filter
    $('#btn-reset').click(function() {
        $('#filter_status').val('').trigger('change');
        table.draw();
    });

    // Form submit
    $('#formKategori').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('kategori_layanan/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#formKategori button[type="submit"]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalKategori').modal('hide');
                    table.draw();
                    toastr.success(response.message);
                    $('#formKategori')[0].reset();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#formKategori button[type="submit"]').prop('disabled', false).text('Simpan');
            }
        });
    });

    // Reset form when modal is closed
    $('#modalKategori').on('hidden.bs.modal', function() {
        $('#formKategori')[0].reset();
        $('#id').val('');
        $('#modalKategoriLabel').text('Form Kategori');
        $('#kategori_status').prop('checked', true);
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var deskripsi = $(this).data('deskripsi');
        var urutan = $(this).data('urutan');
        var status = $(this).data('status');

        $('#id').val(id);
        $('#kategori_nama').val(nama);
        $('#kategori_deskripsi').val(deskripsi);
        $('#kategori_urutan').val(urutan);
        $('#kategori_status').prop('checked', status == 1);
        $('#modalKategoriLabel').text('Edit Kategori');

        $('#modalKategori').modal('show');
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('kategori_layanan/delete') ?>',
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