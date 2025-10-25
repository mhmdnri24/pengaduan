<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {


    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true
    });

    // Load kecamatan options
    loadKecamatanOptions();
    loadFilterKecamatanOptions();

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('masyarakat/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_kecamatan = $('#filter-kecamatan').val();
                d.filter_kelurahan = $('#filter-kelurahan').val();
                d.filter_status = $('#filter-status').val();
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
            {"data": "4"},
            {"data": "5"},
            {"data": "6"},
            {"data": "7", "orderable": false}
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
    $('#filter-kecamatan, #filter-kelurahan, #filter-status').change(function() {
        table.ajax.reload();
    });

    // Filter kecamatan change
    $('#filter-kecamatan').change(function() {
        var id_kecamatan = $(this).val();
        $('#filter-kelurahan').empty().append('<option value="">Semua Kelurahan</option>');

        if (id_kecamatan) {
            loadFilterKelurahanOptions(id_kecamatan);
        }
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-kecamatan, #filter-kelurahan, #filter-status').val('').trigger('change');
        table.ajax.reload();
    });

    $('#btn-add').click(function() {
        resetForm();
        $('.modal-title').html('<i class="fa fa-plus"></i> Tambah Data Masyarakat');
        $('#modal-form').modal('show');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('masyarakat/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#nama_lengkap').val(data.nama_lengkap);
                    $('#nik').val(data.nik);
                    $('#no_telpon').val(data.no_telpon);
                    $('#status_aktif').val(data.status_aktif).trigger('change');
                    $('#alamat').val(data.alamat);

                    if (data.id_kecamatan) {
                        $('#id_kecamatan').val(data.id_kecamatan).trigger('change');
                        setTimeout(function() {
                            if (data.id_kelurahan) {
                                $('#id_kelurahan').val(data.id_kelurahan).trigger('change');
                            }
                        }, 500);
                    }

                    $('.modal-title').html('<i class="fa fa-edit"></i> Edit Data Masyarakat');
                    $('#modal-form').modal('show');
                } else {
                    showAlert('danger', 'Gagal mengambil data');
                }
            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan sistem');
            }
        });
    });

    // Detail button
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('masyarakat/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#detail-nama_lengkap').text(data.nama_lengkap || '-');
                    $('#detail-nik').text(data.nik || '-');
                    $('#detail-no_telpon').text(data.no_telpon || '-');
                    $('#detail-status_aktif').html(data.status_aktif == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non-Aktif</span>');
                    $('#detail-kecamatan').text(data.nama_kecamatan || '-');
                    $('#detail-kelurahan').text(data.nama_kelurahan || '-');
                    $('#detail-alamat').text(data.alamat || '-');
                    $('#detail-created_at').text(formatDateTime(data.created_at) || '-');
                    
                    $('#modal-detail').modal('show');
                } else {
                    toastr.error('Gagal mengambil data');
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
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('masyarakat/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
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
    });

    // Form submit
    $('#form-masyarakat').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var submitBtn = $('#form-masyarakat button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: '<?= base_url('masyarakat/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status) {
                    showAlert('success', response.message);
                    $('#modal-form').modal('hide');
                    table.ajax.reload(null, false); // Keep pagination
                    resetForm();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('danger', 'Terjadi kesalahan sistem');
            }
        });
    });

    // Modal events
    $('#modal-form').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Kecamatan change event
    $('#id_kecamatan').change(function() {
        var id_kecamatan = $(this).val();
        $('#id_kelurahan').empty().append('<option value="">Pilih Kelurahan</option>');
        
        if (id_kecamatan) {
            $.ajax({
                url: '<?= base_url('masyarakat/get_kelurahan') ?>',
                type: 'POST',
                data: {
                    id_kecamatan: id_kecamatan,
                },
                dataType: 'json',
                success: function(data) {
                    $.each(data, function(index, item) {
                        $('#id_kelurahan').append('<option value="' + item.id_kelurahan + '">' + item.nama_kelurahan + '</option>');
                    });
                }
            });
        }
    });

    // NIK input validation
    $('#nik').on('input', function() {
        var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        $(this).val(value);
        
        if (value.length > 0 && value.length !== 16) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Phone number input validation
    $('#no_telpon').on('input', function() {
        var value = $(this).val().replace(/[^0-9+\-\s]/g, ''); // Allow numbers, +, -, and spaces
        $(this).val(value);
    });
});

// Load kecamatan options for form
function loadKecamatanOptions() {
    $.ajax({
        url: '<?= base_url('masyarakat/get_kecamatan') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#id_kecamatan').empty().append('<option value="">Pilih Kecamatan</option>');
            $.each(data, function(index, item) {
                $('#id_kecamatan').append('<option value="' + item.id_kecamatan + '">' + item.nama_kecamatan + '</option>');
            });
        }
    });
}

// Load kecamatan options for filter
function loadFilterKecamatanOptions() {
    $.ajax({
        url: '<?= base_url('masyarakat/get_kecamatan') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#filter-kecamatan').empty().append('<option value="">Semua Kecamatan</option>');
            $.each(data, function(index, item) {
                $('#filter-kecamatan').append('<option value="' + item.id_kecamatan + '">' + item.nama_kecamatan + '</option>');
            });
        }
    });
}

// Load kelurahan options for filter
function loadFilterKelurahanOptions(id_kecamatan) {
    $.ajax({
        url: '<?= base_url('masyarakat/get_kelurahan') ?>',
        type: 'POST',
        data: {
            id_kecamatan: id_kecamatan
        },
        dataType: 'json',
        success: function(data) {
            $('#filter-kelurahan').empty().append('<option value="">Semua Kelurahan</option>');
            $.each(data, function(index, item) {
                $('#filter-kelurahan').append('<option value="' + item.id_kelurahan + '">' + item.nama_kelurahan + '</option>');
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#form-masyarakat')[0].reset();
    $('#id').val('');
    $('.select2').val('').trigger('change');
    $('#id_kelurahan').empty().append('<option value="">Pilih Kelurahan</option>');
    $('#nik').removeClass('is-invalid');
}

// Format datetime
function formatDateTime(dateString) {
    if (!dateString) return '-';
    var date = new Date(dateString);
    var day = ('0' + date.getDate()).slice(-2);
    var month = ('0' + (date.getMonth() + 1)).slice(-2);
    var year = date.getFullYear();
    var hours = ('0' + date.getHours()).slice(-2);
    var minutes = ('0' + date.getMinutes()).slice(-2);
    return day + '-' + month + '-' + year + ' ' + hours + ':' + minutes;
}

// Show alert function
function showAlert(type, message) {
    var alertClass = 'alert-' + type;
    var iconClass = type === 'success' ? 'fa-check' : 'fa-exclamation-triangle';

    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                    '<i class="fa ' + iconClass + '"></i> ' + message +
                    '</div>';

    $('.box-body').prepend(alertHtml);

    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
