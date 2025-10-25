<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true
    });

    // Load filter options
    loadFilterOptions();

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('retribusi_target/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_tahun = $('#filter-tahun').val();
                d.filter_jenis = $('#filter-jenis').val();
                d.filter_retribusi = $('#filter-retribusi').val();
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
            {"data": "7"},
            {"data": "8", "orderable": false}
        ],
        "order": [[2, 'desc'], [3, 'asc'], [1, 'asc']],
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
    $('#filter-tahun, #filter-jenis, #filter-retribusi').change(function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-tahun, #filter-jenis, #filter-retribusi').val('').trigger('change');
        table.ajax.reload();
    });

    $('#btn-add').click(function() {
        resetForm();
        $('.modal-title').html('<i class="fa fa-plus"></i> Tambah Target Retribusi');
        $('#modal-form').modal('show');
    });

    // Jenis target change event
    $('#jenis_target').change(function() {
        if ($(this).val() === 'bulanan') {
            $('#bulan-container').show();
            $('#bulan').prop('required', true);
        } else {
            $('#bulan-container').hide();
            $('#bulan').prop('required', false).val('').trigger('change');
        }
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '<?= base_url('retribusi_target/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#id_retribusi').val(data.id_retribusi).trigger('change');
                    $('#tahun').val(data.tahun);
                    
                    // Set jenis target
                    var jenisTarget = data.bulan ? 'bulanan' : 'tahunan';
                    $('#jenis_target').val(jenisTarget).trigger('change');
                    
                    if (data.bulan) {
                        $('#bulan').val(data.bulan).trigger('change');
                    }
                    
                    $('#target').val(data.target);
                    $('#capaian').val(data.capaian);
                    $('#keterangan').val(data.keterangan);

                    $('.modal-title').html('<i class="fa fa-edit"></i> Edit Target Retribusi');
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
            url: '<?= base_url('retribusi_target/get_by_id') ?>',
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
                    $('#detail-tahun').text(data.tahun || '-');
                    $('#detail-periode').html(data.bulan ? data.bulan : '<span class="label label-primary">Tahunan</span>');
                    $('#detail-target').text('Rp ' + formatNumber(data.target) || '-');
                    $('#detail-capaian').text('Rp ' + formatNumber(data.capaian) || '-');
                    
                    // Persentase dengan warna
                    var persentaseClass = '';
                    if (data.persentase >= 100) {
                        persentaseClass = 'label-success';
                    } else if (data.persentase >= 80) {
                        persentaseClass = 'label-warning';
                    } else {
                        persentaseClass = 'label-danger';
                    }
                    $('#detail-persentase').html('<span class="label ' + persentaseClass + '">' + formatNumber(data.persentase) + '%</span>');
                    
                    $('#detail-keterangan').text(data.keterangan || '-');

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

    // Input Capaian button
    $(document).on('click', '.btn-capaian', function() {
        var id = $(this).data('id');
        var retribusi = $(this).data('retribusi');
        var periode = $(this).data('periode');
        var capaian = $(this).data('capaian');
        var keterangan = $(this).data('keterangan');

        $('#capaian-id').val(id);
        $('#capaian-retribusi').text(retribusi);
        $('#capaian-periode').text(periode);
        $('#capaian-value').val(capaian);
        $('#capaian-keterangan').val(keterangan);

        $('#modal-capaian').modal('show');
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
                    url: '<?= base_url('retribusi_target/delete') ?>',
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
    $('#form-target').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var submitBtn = $('#form-target button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: '<?= base_url('retribusi_target/save') ?>',
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
                    table.ajax.reload(null, false);
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

    // Form capaian submit
    $('#form-capaian').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var submitBtn = $('#form-capaian button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: '<?= base_url('retribusi_target/update_capaian') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memperbarui...');
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status) {
                    showAlert('success', response.message);
                    $('#modal-capaian').modal('hide');
                    table.ajax.reload(null, false);
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

    // Target and capaian input validation
    $('#target, #capaian, #capaian-value').on('input', function() {
        var value = $(this).val();
        if (value < 0) {
            $(this).val(0);
        }
    });

    // Auto calculate percentage when target or capaian changes
    $('#target, #capaian').on('input', function() {
        calculatePercentage();
    });

    // Set default tahun to current year
    $('#tahun').val(new Date().getFullYear());
});

// Load filter options
function loadFilterOptions() {
    // Load tahun options
    $.ajax({
        url: '<?= base_url('retribusi_target/get_tahun_list') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#filter-tahun').empty().append('<option value="">Semua Tahun</option>');
            $.each(data, function(index, item) {
                $('#filter-tahun').append('<option value="' + item.tahun + '">' + item.tahun + '</option>');
            });
        }
    });

    // Load retribusi options
    $.ajax({
        url: '<?= base_url('retribusi_target/get_retribusi_list') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#filter-retribusi').empty().append('<option value="">Semua Retribusi</option>');
            $('#id_retribusi').empty().append('<option value="">Pilih Jenis Retribusi</option>');
            
            $.each(data, function(index, item) {
                var option = '<option value="' + item.id + '">' + item.nama_retribusi + ' (' + item.kode_retribusi + ')</option>';
                $('#filter-retribusi').append(option);
                $('#id_retribusi').append(option);
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#form-target')[0].reset();
    $('#id').val('');
    $('.select2').val('').trigger('change');
    $('#bulan-container').hide();
    $('#bulan').prop('required', false);
    
    // Set default tahun to current year
    $('#tahun').val(new Date().getFullYear());
}

// Calculate percentage
function calculatePercentage() {
    var target = parseFloat($('#target').val()) || 0;
    var capaian = parseFloat($('#capaian').val()) || 0;
    
    if (target > 0) {
        var persentase = (capaian / target) * 100;
        // You can display this somewhere if needed
        console.log('Persentase: ' + persentase.toFixed(2) + '%');
    }
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