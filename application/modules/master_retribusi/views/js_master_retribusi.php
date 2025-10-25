<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true
    });

    // Load jenis retribusi options
    loadFilterJenisOptions();

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('master_retribusi/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_jenis = $('#filter-jenis').val();
                d.filter_status = $('#filter-status').val();
            },
            "error": function(xhr, error, code) {
                console.error('Ajax Error:', xhr, error, code);
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
    $('#filter-jenis, #filter-status').change(function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-jenis, #filter-status').val('').trigger('change');
        table.ajax.reload();
    });

    $('#btn-add').click(function() {
        resetForm();
        $('.modal-title').html('<i class="fa fa-plus"></i> Tambah Data Retribusi');
        $('#modal-form').modal('show');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '<?= base_url('master_retribusi/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#nama_retribusi').val(data.nama_retribusi);
                    $('#kode_retribusi').val(data.kode_retribusi);
                    $('#jenis_retribusi').val(data.jenis_retribusi).trigger('change');
                    $('#tarif').val(data.tarif);
                    $('#satuan').val(data.satuan);
                    $('#status_aktif').val(data.status_aktif).trigger('change');
                    $('#deskripsi').val(data.deskripsi);

                    $('.modal-title').html('<i class="fa fa-edit"></i> Edit Data Retribusi');
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
            url: '<?= base_url('master_retribusi/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#detail-nama_retribusi').text(data.nama_retribusi || '-');
                    $('#detail-kode_retribusi').text(data.kode_retribusi || '-');
                    $('#detail-jenis_retribusi').text(data.jenis_retribusi || '-');
                    $('#detail-tarif').text('Rp ' + formatNumber(data.tarif) || '-');
                    $('#detail-satuan').text(data.satuan || '-');
                    $('#detail-status_aktif').html(data.status_aktif == 1 ? '<span class="label label-success">Aktif</span>' : '<span class="label label-danger">Non-Aktif</span>');
                    $('#detail-deskripsi').text(data.deskripsi || '-');
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
                    url: '<?= base_url('master_retribusi/delete') ?>',
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
    $('#form-retribusi').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var submitBtn = $('#form-retribusi button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: '<?= base_url('master_retribusi/save') ?>',
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

    // Tarif input validation
    $('#tarif').on('input', function() {
        var value = $(this).val();
        if (value < 0) {
            $(this).val(0);
        }
    });
});

// Load jenis retribusi options for filter
function loadFilterJenisOptions() {
    $.ajax({
        url: '<?= base_url('master_retribusi/get_jenis_retribusi') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#filter-jenis').empty().append('<option value="">Semua Jenis</option>');
            $.each(data, function(index, item) {
                $('#filter-jenis').append('<option value="' + item.jenis_retribusi + '">' + item.jenis_retribusi + '</option>');
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#form-retribusi')[0].reset();
    $('#id').val('');
    $('.select2').val('').trigger('change');
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

// Format number
function formatNumber(num) {
    if (!num) return '0';
    return parseFloat(num).toLocaleString('id-ID');
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