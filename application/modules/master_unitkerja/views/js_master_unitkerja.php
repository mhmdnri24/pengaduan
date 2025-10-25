<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function get_post_data(data) {
        data[csrf_token_name] = csrf_hash;
        return data;
    }

    function update_csrf(new_csrf_hash) {
        csrf_hash = new_csrf_hash;
        $('input[name="' + csrf_token_name + '"]').val(csrf_hash);
    }

    function showNotification(type, message) {
        toastr[type](message);
    }

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true
    });

    // Initialize table variable
    var table;

    // Function to load table data
    function loadTableData() {
        var filter_instansi = $('#filter_instansi').val();
        var filter_unitkerja = $('#filter_unitkerja').val();

        $.ajax({
            url: '<?= base_url('master_unitkerja/get_hierarchy_data') ?>',
            type: 'POST',
            data: get_post_data({
                filter_instansi: filter_instansi,
                filter_unitkerja: filter_unitkerja
            }),
            dataType: 'json',
            success: function(response) {
                update_csrf(response.csrf_hash);

                if (response.status) {
                    if ($.fn.DataTable.isDataTable('#dataTable')) {
                        table.clear().rows.add(response.data).draw();
                    } else {
                        table = $('#dataTable').DataTable({
                            data: response.data,
                            columns: [
                                { data: '0', name: '0', orderable: false },
                                { data: '1', name: 'unitkerja', orderable: false },
                                { data: '2', name: 'parent_unit', orderable: false },
                                { data: '3', name: 'instansi_nama' },
                                { data: '4', name: '4', orderable: false, searchable: false }
                            ],
                            order: [],
                            ordering: false,
                            searching: true,
                            paging: true,
                            pageLength: 10
                        });
                    }
                } else {
                    showNotification('error', 'Gagal memuat data unit kerja');
                }
            },
            error: function() {
                showNotification('error', 'Terjadi kesalahan saat memuat data');
            }
        });
    }

    // Function to load unit kerja options based on instansi
    function loadUnitKerjaOptions() {
        var instansi_filter = $('#filter_instansi').val();

        $('#filter_unitkerja').empty().append('<option value="">Semua Unit Kerja</option>');

        if (instansi_filter) {
            $.ajax({
                url: '<?= base_url('master_unitkerja/get_unitkerja_options') ?>',
                type: 'GET',
                data: { instansi_filter: instansi_filter },
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.data) {
                        $.each(response.data, function(index, item) {
                            $('#filter_unitkerja').append('<option value="' + item.id_unitkerja + '">' + item.unitkerja + '</option>');
                        });
                    }
                }
            });
        } else {
            // Load all unit kerja if no instansi filter
            $.ajax({
                url: '<?= base_url('master_unitkerja/get_unitkerja_options') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.data) {
                        $.each(response.data, function(index, item) {
                            $('#filter_unitkerja').append('<option value="' + item.id_unitkerja + '">' + item.unitkerja + '</option>');
                        });
                    }
                }
            });
        }
    }

    // Initial load unit kerja options
    loadUnitKerjaOptions();

    // Initial load
    loadTableData();

    // Event handlers for filters
    $('#filter_instansi').on('change', function() {
        // Reset unit kerja filter when instansi changes
        $('#filter_unitkerja').val('').trigger('change');
        // Reload unit kerja options
        loadUnitKerjaOptions();
        // Reload table data
        loadTableData();
    });

    $('#filter_unitkerja').on('change', function() {
        // Reload table data when unit kerja filter changes
        loadTableData();
    });


    $('#modalUnitKerja').on('hidden.bs.modal', function() {
        $('#formUnitKerja')[0].reset();
        $('#id').val('');
        $('#modalUnitKerjaLabel').text('Form Unit Kerja');
        $('#id_tb_instansi').val('').trigger('change');
        $('#induk_unit').empty().append('<option value="">Tidak Ada (Unit Utama)</option>').val('').trigger('change');
    });

    // Load parent options when instansi changes
    $('#id_tb_instansi').on('change', function() {
        var instansi_id = $(this).val();
        var current_id = $('#id').val();
        
        $('#induk_unit').empty().append('<option value="">Tidak Ada (Unit Utama)</option>');
        
        if (instansi_id) {
            $.ajax({
                url: '<?= base_url('master_unitkerja/get_parent_options') ?>',
                type: 'GET',
                data: { 
                    instansi_id: instansi_id,
                    current_id: current_id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status && response.data) {
                        $.each(response.data, function(index, item) {
                            $('#induk_unit').append('<option value="' + item.id_unitkerja + '">' + item.unitkerja + '</option>');
                        });
                    }
                }
            });
        }
    });

    $('#formUnitKerja').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append(csrf_token_name, csrf_hash);
        
        var submitBtn = $('#btnSimpan');
        var originalText = submitBtn.html();
        
        $.ajax({
            url: '<?= base_url('master_unitkerja/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                update_csrf(response.csrf_hash);
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status) {
                    $('#modalUnitKerja').modal('hide');
                    showNotification('success', response.message);
                    loadTableData();
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html(originalText);
                showNotification('error', 'Terjadi kesalahan saat menyimpan data.');
            }
        });
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var unitkerja = $(this).data('unitkerja');
        var instansi = $(this).data('instansi');
        var induk = $(this).data('induk');
        
        $('#id').val(id);
        $('#unitkerja').val(unitkerja);
        $('#id_tb_instansi').val(instansi).trigger('change');
        
        // Set induk unit after instansi is loaded
        setTimeout(function() {
            if (induk && induk != '0') {
                $('#induk_unit').val(induk).trigger('change');
            }
        }, 500);
        
        $('#modalUnitKerjaLabel').text('Edit Unit Kerja');
        $('#modalUnitKerja').modal('show');
    });

    $(document).on('click', '.btn-hapus', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        
        Swal.fire({
            title: 'Anda Yakin?',
            text: 'Data unit kerja "' + nama + '" akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('master_unitkerja/hapus/') ?>' + id,
                    type: 'POST',
                    data: { [csrf_token_name]: csrf_hash },
                    dataType: 'json',
                    success: function(response) {
                        update_csrf(response.csrf_hash);
                        if (response.status) {
                            showNotification('success', response.message);
                            loadTableData();
                        } else {
                            showNotification('error', response.message);
                        }
                    },
                    error: function() {
                        showNotification('error', 'Tidak dapat memproses permintaan.');
                    }
                });
            }
        });
    });
});
</script>
