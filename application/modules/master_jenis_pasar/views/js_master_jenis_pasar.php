<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Load initial data
    loadPasarOptions();
    loadStatistik();

    // DataTable
    var table = $('#table-jenis-pasar').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('master_jenis_pasar/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_pasar = $('#filter_pasar').val();
                d.filter_jenis = $('#filter_jenis').val();
                d.filter_status_sewa = $('#filter_status_sewa').val();
            }
        },
        "columns": [
            { "data": 0, "orderable": false },
            { "data": 1 },
            { "data": 2, "orderable": false },
            { "data": 3 },
            { "data": 4 },
            { "data": 5 },
            { "data": 6 },
            { "data": 7, "orderable": false },
            { "data": 8, "orderable": false },
            { "data": 9, "orderable": false }
        ],
        "order": [[1, "asc"]],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
        }
    });

    // Filter events
    $('#filter_pasar, #filter_jenis, #filter_status_sewa').on('change', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#btn-reset').click(function() {
        $('#filter_pasar, #filter_jenis, #filter_status_sewa').val('').trigger('change');
        table.ajax.reload();
    });

    // Modal events - reset form when modal is shown
    $('#modalJenisPasar').on('show.bs.modal', function() {
        resetForm();
        $('#modal-title').text('Tambah Jenis Pasar');
    });

    // Edit data
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $('#modal-title').text('Edit Jenis Pasar');
        $('#id').val(id);

        // Load data lengkap via AJAX
        $.ajax({
            url: '<?= base_url('master_jenis_pasar/get_by_id/') ?>' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var data = response.data;

                    // Set input values
                    $('#pasar_jenis_jumlah').val(data.jumlah_blok);
                    $('#pasar_jenis_tersedia').val(data.blok_tersedia);
                    $('#pasar_jenis_terisi').val(data.blok_terisi);
                    $('#pasar_jenis_harga_sewa').val(data.pasar_jenis_harga_sewa);
                    $('#pasar_jenis_keterangan').val(data.pasar_jenis_keterangan);

                    // Load pasar options dan set values
                    $.ajax({
                        url: '<?= base_url('master_pasar/get_pasar_options') ?>',
                        type: 'GET',
                        success: function(optionsData) {
                            $('#pasar_id').html(optionsData);

                            // Set Select2 values setelah options di-reload
                            setTimeout(function() {
                                $('#pasar_id').val(data.pasar_id).trigger('change');
                                $('#pasar_jenis_nama').val(data.pasar_jenis_nama).trigger('change');
                                $('#pasar_jenis_status_sewa').val(data.pasar_jenis_status_sewa).trigger('change');
                            }, 100);
                        }
                    });
                } else {
                    toastr.error('Gagal memuat data: ' + response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat memuat data');
            }
        });

        $('#modalJenisPasar').modal('show');
    });

    // Delete data
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        $('#id-hapus').val(id);
        $('#nama-hapus').text(nama);
        $('#modalHapus').modal('show');
    });

    // Form submit
    $('#formJenisPasar').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $('#btn-simpan').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: '<?= base_url('master_jenis_pasar/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#modalJenisPasar').modal('hide');
                    table.ajax.reload();
                    loadStatistik();
                    
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                

            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-simpan').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Konfirmasi hapus
    $('#btn-konfirm-hapus').click(function() {
        var id = $('#id-hapus').val();
        
        $('#btn-konfirm-hapus').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menghapus...');
        
        $.ajax({
            url: '<?= base_url('master_jenis_pasar/delete') ?>',
            type: 'POST',
            data: {
                id: id,
                <?= $this->security->get_csrf_token_name(); ?>: $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#modalHapus').modal('hide');
                    table.ajax.reload();
                    loadStatistik();
                    
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
                

            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-konfirm-hapus').prop('disabled', false).html('<i class="fa fa-trash"></i> Hapus');
            }
        });
    });

    // Modal events
    $('#modalJenisPasar').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Validasi input angka
    $('#pasar_jenis_jumlah, #pasar_jenis_tersedia, #pasar_jenis_terisi').on('input', function() {
        validateNumbers();
    });

    // Functions
    function resetForm() {
        $('#formJenisPasar')[0].reset();
        $('#modal-title').text('Tambah Jenis Pasar');
        $('#id').val('');
        $('#pasar_id, #pasar_jenis_nama, #pasar_jenis_status_sewa').val(null).trigger('change');
    }

    function loadPasarOptions() {
        $.ajax({
            url: '<?= base_url('master_pasar/get_pasar_options') ?>',
            type: 'GET',
            success: function(data) {
                $('#pasar_id').html(data);

                // Load untuk filter juga
                var filterOptions = '<option value="">-- Semua Pasar --</option>' + data.replace('<option value="">Pilih Pasar</option>', '');
                $('#filter_pasar').html(filterOptions);
            },
            error: function() {
                console.log('Error loading pasar options');
            }
        });
    }

    function loadStatistik() {
        $.ajax({
            url: '<?= base_url('master_jenis_pasar/get_statistik') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#total-jenis').text(data.total_jenis);
                $('#total-tersedia').text(data.total_tersedia);
                $('#total-penuh').text(data.total_penuh);
                $('#total-maintenance').text(data.total_maintenance);
            },
            error: function() {
                console.log('Error loading statistik');
            }
        });
    }

    function validateNumbers() {
        var jumlah = parseInt($('#pasar_jenis_jumlah').val()) || 0;
        var tersedia = parseInt($('#pasar_jenis_tersedia').val()) || 0;
        var terisi = parseInt($('#pasar_jenis_terisi').val()) || 0;
        
        // Validasi: tersedia + terisi tidak boleh lebih dari jumlah
        if ((tersedia + terisi) > jumlah) {
            toastr.warning('Jumlah tersedia + terisi tidak boleh melebihi total jumlah');
        }
        
        // Auto update status berdasarkan kondisi
        if (terisi >= jumlah) {
            $('#pasar_jenis_status_sewa').val('PENUH').trigger('change');
        } else if (tersedia <= 0) {
            $('#pasar_jenis_status_sewa').val('MAINTENANCE').trigger('change');
        } else {
            $('#pasar_jenis_status_sewa').val('TERSEDIA').trigger('change');
        }
    }
});
</script>
