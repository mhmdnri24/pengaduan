<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    // Initialize Select2 dengan pencarian
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        allowClear: true,
        placeholder: function() {
            return $(this).data('placeholder') || 'Pilih...';
        }
    });

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('kategori_kepengurusan/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.status = $('#filter_status').val();
            },
            "error": function(xhr, error, code) {
                console.log('Ajax Error:', xhr, error, code);
                alert('Terjadi kesalahan saat memuat data. Silakan periksa koneksi atau hubungi administrator.');
            }
        },
        "columns": [
            {"data": "0", "orderable": false},
            {"data": "1"},
            {"data": "2"},
            {"data": "3"},
            {"data": "4", "orderable": false}
        ],
        "order": [[1, 'asc']],
        "language": {
            "processing": "Memproses...",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "infoFiltered": "(disaring dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            },
            "loadingRecords": "Memuat data...",
            "emptyTable": "Tidak ada data yang tersedia"
        }
    });

    // Filter events
    $('#filter_status').on('change', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#btn-reset-filter').click(function() {
        $('#filter_status').val('').trigger('change');
        table.ajax.reload();
    });

    // Add button
    $('#btn-add').click(function() {
        $('#form-kategori')[0].reset();
        $('#kepengurusan_id').val('');
        $('#modal-form .modal-title').text('Tambah Kategori Kepengurusan');
        $('.select2').val('').trigger('change');

        // Get next kode
        $.ajax({
            url: '<?= base_url('kategori_kepengurusan/get_next_kode') ?>',
            type: 'POST',
            data: get_post_data({}),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#kepengurusan_kode').val(response.kode);
                }
            },
            error: function() {
                // Jika gagal get kode, biarkan kosong
                $('#kepengurusan_kode').val('');
            }
        });

        $('#modal-form').modal('show');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('kategori_kepengurusan/detail') ?>',
            type: 'POST',
            data: get_post_data({
                id: id
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#kepengurusan_id').val(data.kepengurusan_id);
                    $('#kepengurusan_kode').val(data.kepengurusan_kode);
                    $('#kepengurusan_nama').val(data.kepengurusan_nama);
                    $('#status').val(data.status);

                    $('#modal-form .modal-title').text('Edit Kategori Kepengurusan');
                    $('#modal-form').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('kategori_kepengurusan/delete') ?>',
                type: 'POST',
                data: get_post_data({
                    id: id
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        table.ajax.reload();
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

    // Form submit
    $('#form-kategori').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('kategori_kepengurusan/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#form-kategori button[type="submit"]').prop('disabled', true).html('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#modal-form').modal('hide');
                    table.ajax.reload();

                    // Reset form sesuai pattern fakultas/jurusan
                    $('#form-kategori')[0].reset();
                    $('#kepengurusan_id').val('');
                    $('#modal-form .modal-title').text('Tambah Kategori Kepengurusan');
                    $('.select2').val('').trigger('change');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#form-kategori button[type="submit"]').prop('disabled', false).html('Simpan');
            }
        });
    });

    // Reset form when modal is closed
    $('#modal-form').on('hidden.bs.modal', function() {
        $('#form-kategori')[0].reset();
        $('#kepengurusan_id').val('');
        $('#modal-form .modal-title').text('Tambah Kategori Kepengurusan');
        $('.select2').val('').trigger('change');
    });

    // Kode validation
    $('#kepengurusan_kode').on('input', function() {
        var value = $(this).val().toUpperCase();
        $(this).val(value);
    });
});
</script>
