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

    // Format date function
    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear();
        return day + '-' + month + '-' + year;
    }

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('kepengurusan_sosial/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.jenis_kelamin = $('#filter_jenis_kelamin').val();
                d.agama = $('#filter_agama').val();
                d.status_kawin = $('#filter_status_kawin').val();
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
            {"data": "7"},
            {"data": "8"},
            {"data": "9"},
            {"data": "10", "orderable": false}
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
    $('#filter_jenis_kelamin, #filter_agama, #filter_status_kawin').on('change', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#btn-reset-filter').click(function() {
        $('#filter_jenis_kelamin, #filter_agama, #filter_status_kawin').val('').trigger('change');
        table.ajax.reload();
    });

    // Kecamatan change event
    $('#kecamatan').change(function() {
        var kecamatan_id = $(this).val();
        $('#kelurahan').empty().append('<option value="">Pilih Kelurahan</option>');
        
        if (kecamatan_id) {
            // Gunakan endpoint yang benar sesuai pattern modules lain
            $.get('<?= base_url('kelurahan/get-option/') ?>' + kecamatan_id, function(result) {
                $('#kelurahan').html(result);
                $('#kelurahan').select2();
            });
        }
    });

    // Add button
    $('#btn-add').click(function() {
        $('#form-kepengurusan')[0].reset();
        $('#modal-form .modal-title').text('Tambah Kepengurusan Sosial');
        $('#modal-form').modal('show');
        $('.select2').trigger('change');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('kepengurusan_sosial/detail') ?>',
            type: 'POST',
            data: get_post_data({
                id: id
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#nama_lengkap').val(data.nama_lengkap);
                    $('#nik_ktp').val(data.nik_ktp);
                    $('#no_kk').val(data.no_kk);
                    $('#no_wa_hp').val(data.no_wa_hp);
                    $('#tempat_lahir').val(data.tempat_lahir);
                    $('#tanggal_lahir').val(data.tanggal_lahir);
                    $('#jenis_kelamin').val(data.jenis_kelamin).trigger('change');
                    $('#agama').val(data.agama).trigger('change');
                    $('#alamat').val(data.alamat);
                    $('#kecamatan').val(data.kecamatan).trigger('change');
                    $('#status_kawin').val(data.status_kawin).trigger('change');
                    $('#pendidikan_terakhir').val(data.pendidikan_terakhir).trigger('change');

                    // Show current foto if exists
                    if (data.foto_profil) {
                        $('#preview-img').attr('src', '<?= base_url('uploads/kepengurusan_sosial/') ?>' + data.foto_profil).show();
                        $('#preview-text').hide();
                        $('#current-foto').html('<small class="text-info"><i class="fa fa-image"></i> Foto saat ini: ' + data.foto_profil + '</small>');
                    } else {
                        $('#preview-img').hide();
                        $('#preview-text').show();
                        $('#current-foto').html('');
                    }

                    // Load kelurahan after kecamatan is set
                    if (data.kecamatan) {
                        setTimeout(function() {
                            $.get('<?= base_url('kelurahan/get-option/') ?>' + data.kecamatan, function(result) {
                                $('#kelurahan').html(result);
                                $('#kelurahan').val(data.kelurahan).trigger('change');
                                $('#kelurahan').select2();
                            });
                        }, 500);
                    }

                    $('#modal-form .modal-title').text('Edit Kepengurusan Sosial');
                    $('#modal-form').modal('show');
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Detail button
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('kepengurusan_sosial/detail') ?>',
            type: 'POST',
            data: get_post_data({
                id: id
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var jenis_kelamin = data.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan';
                    var tanggal_lahir = data.tanggal_lahir ? formatDate(data.tanggal_lahir) : '-';
                    
                    var html = '<div class="row">';

                    // Foto profil section
                    if (data.foto_profil) {
                        html += '<div class="col-md-12 text-center" style="margin-bottom: 20px;">';
                        html += '<img src="<?= base_url('uploads/kepengurusan_sosial/') ?>' + data.foto_profil + '" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">';
                        html += '</div>';
                    }

                    html += '<div class="col-md-6">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><td><strong><i class="fa fa-user"></i> Nama Lengkap</strong></td><td>' + (data.nama_lengkap || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-id-card"></i> NIK KTP</strong></td><td>' + (data.nik_ktp || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-address-card"></i> No KK</strong></td><td>' + (data.no_kk || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-phone"></i> No WA/HP</strong></td><td>' + (data.no_wa_hp || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-map-marker"></i> Tempat Lahir</strong></td><td>' + (data.tempat_lahir || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-calendar"></i> Tanggal Lahir</strong></td><td>' + tanggal_lahir + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-venus-mars"></i> Jenis Kelamin</strong></td><td>' + jenis_kelamin + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><td><strong><i class="fa fa-book"></i> Agama</strong></td><td>' + (data.agama || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-heart"></i> Status Kawin</strong></td><td>' + (data.status_kawin || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-graduation-cap"></i> Pendidikan</strong></td><td>' + (data.pendidikan_terakhir || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-map"></i> Kecamatan</strong></td><td>' + (data.nama_kecamatan || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-map-marker"></i> Kelurahan</strong></td><td>' + (data.nama_kelurahan || '-') + '</td></tr>';
                    html += '<tr><td><strong><i class="fa fa-home"></i> Alamat</strong></td><td>' + (data.alamat || '-') + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Gunakan modal Bootstrap biasa karena bootbox mungkin tidak tersedia
                    var modalHtml = '<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">';
                    modalHtml += '<div class="modal-dialog modal-lg" role="document">';
                    modalHtml += '<div class="modal-content">';
                    modalHtml += '<div class="modal-header">';
                    modalHtml += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    modalHtml += '<span aria-hidden="true">&times;</span></button>';
                    modalHtml += '<h4 class="modal-title"><i class="fa fa-eye"></i> Detail Kepengurusan Sosial</h4>';
                    modalHtml += '</div>';
                    modalHtml += '<div class="modal-body">' + html + '</div>';
                    modalHtml += '<div class="modal-footer">';
                    modalHtml += '<button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>';
                    modalHtml += '</div>';
                    modalHtml += '</div></div></div>';

                    // Remove existing modal if any
                    $('#modal-detail').remove();

                    // Add modal to body and show
                    $('body').append(modalHtml);
                    $('#modal-detail').modal('show');
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        // Gunakan confirm biasa karena bootbox mungkin tidak tersedia
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: '<?= base_url('kepengurusan_sosial/delete') ?>',
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
    $('#form-kepengurusan').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('kepengurusan_sosial/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#form-kepengurusan button[type="submit"]').prop('disabled', true).html('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#modal-form').modal('hide');
                    table.ajax.reload();

                    // Reset form sesuai pattern fakultas/jurusan
                    $('#form-kepengurusan')[0].reset();
                    $('#id').val('');
                    $('#modal-form .modal-title').text('Tambah Kepengurusan Sosial');
                    $('.select2').val('').trigger('change');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#form-kepengurusan button[type="submit"]').prop('disabled', false).html('Simpan');
            }
        });
    });

    // NIK validation
    $('#nik_ktp').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);

        if (value.length > 16) {
            $(this).val(value.substring(0, 16));
        }
    });

    // No KK validation
    $('#no_kk').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);

        if (value.length > 16) {
            $(this).val(value.substring(0, 16));
        }
    });

    // Phone number validation
    $('#no_wa_hp').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Photo preview
    $('#foto_profil').change(function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-img').attr('src', e.target.result).show();
                $('#preview-text').hide();
            }
            reader.readAsDataURL(file);
        } else {
            $('#preview-img').hide();
            $('#preview-text').show();
        }
    });

    // Reset form when modal is closed
    $('#modal-form').on('hidden.bs.modal', function() {
        $('#form-kepengurusan')[0].reset();
        $('#preview-img').hide();
        $('#preview-text').show();
        $('#current-foto').html('');
        $('.select2').val('').trigger('change');
    });

    // Import button - following the same pattern as detail button
    $(document).on('click', '#btn-import', function(e) {
        e.preventDefault();

        // Create modal HTML dynamically like detail button does
        var modalHtml = '<div class="modal fade" id="modal-import" tabindex="-1" role="dialog">';
        modalHtml += '<div class="modal-dialog" role="document">';
        modalHtml += '<div class="modal-content">';
        modalHtml += '<div class="modal-header">';
        modalHtml += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        modalHtml += '<span aria-hidden="true">&times;</span></button>';
        modalHtml += '<h4 class="modal-title"><i class="fa fa-upload"></i> Import Data Kepengurusan Sosial</h4>';
        modalHtml += '</div>';
        modalHtml += '<form id="form-import" enctype="multipart/form-data">';
        modalHtml += '<div class="modal-body">';
        modalHtml += '<div class="form-group">';
        modalHtml += '<label for="excel_file"><i class="fa fa-file-excel-o"></i> File Excel <span class="text-red">*</span></label>';
        modalHtml += '<input type="file" name="excel_file" class="form-control" id="excel_file" accept=".xlsx,.xls" required>';
        modalHtml += '<small class="text-muted">';
        modalHtml += 'Format: .xlsx atau .xls<br>';
        modalHtml += 'Unduh template Excel terlebih dahulu untuk format yang benar';
        modalHtml += '</small>';
        modalHtml += '</div>';
        modalHtml += '<div class="alert alert-info">';
        modalHtml += '<i class="fa fa-info-circle"></i>';
        modalHtml += '<strong>Informasi:</strong>';
        modalHtml += '<ul class="mb-0 mt-2">';
        modalHtml += '<li>Data dengan NIK atau No KK yang sudah ada akan dilewatkan</li>';
        modalHtml += '<li>Pastikan format tanggal menggunakan DD/MM/YYYY</li>';
        modalHtml += '<li>Jenis kelamin: L (Laki-laki) atau P (Perempuan)</li>';
        modalHtml += '</ul>';
        modalHtml += '</div>';
        modalHtml += '<div id="import-progress" style="display: none;">';
        modalHtml += '<div class="progress">';
        modalHtml += '<div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%">';
        modalHtml += '<span>Memproses data...</span>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '<div class="modal-footer">';
        modalHtml += '<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>';
        modalHtml += '<button type="submit" class="btn btn-success">Import Data</button>';
        modalHtml += '</div>';
        modalHtml += '</form>';
        modalHtml += '</div>';
        modalHtml += '</div>';
        modalHtml += '</div>';

        // Remove existing modal if any (like detail button does)
        $('#modal-import').remove();

        // Add modal to body (like detail button does)
        $('body').append(modalHtml);

        // Show modal (like detail button does)
        $('#modal-import').modal('show');
    });

    // Import form submit - using event delegation
    $(document).on('submit', '#form-import', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('kepengurusan_sosial/import_excel') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#import-progress').show();
                $('#modal-import button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
            },
            success: function(response) {
                if (response.status) {
                    // Close import modal
                    $('#modal-import').modal('hide');

                    // Create result modal HTML
                    var resultModalHtml = '<div class="modal fade" id="modal-import-result" tabindex="-1" role="dialog">';
                    resultModalHtml += '<div class="modal-dialog modal-lg" role="document">';
                    resultModalHtml += '<div class="modal-content">';
                    resultModalHtml += '<div class="modal-header">';
                    resultModalHtml += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
                    resultModalHtml += '<span aria-hidden="true">&times;</span></button>';
                    resultModalHtml += '<h4 class="modal-title"><i class="fa fa-check-circle"></i> Hasil Import Data</h4>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '<div class="modal-body">';

                    // Summary section
                    resultModalHtml += '<div class="row">';
                    resultModalHtml += '<div class="col-md-12">';
                    resultModalHtml += '<div class="alert alert-success">';
                    resultModalHtml += '<h4><i class="fa fa-check-circle"></i> Import Berhasil!</h4>';
                    resultModalHtml += '<p>Data telah berhasil diproses.</p>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';

                    // Statistics
                    resultModalHtml += '<div class="row">';
                    resultModalHtml += '<div class="col-md-4">';
                    resultModalHtml += '<div class="info-box bg-green">';
                    resultModalHtml += '<span class="info-box-icon"><i class="fa fa-check"></i></span>';
                    resultModalHtml += '<div class="info-box-content">';
                    resultModalHtml += '<span class="info-box-text">Berhasil Import</span>';
                    resultModalHtml += '<span class="info-box-number">' + response.imported + '</span>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '<div class="col-md-4">';
                    resultModalHtml += '<div class="info-box bg-yellow">';
                    resultModalHtml += '<span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>';
                    resultModalHtml += '<div class="info-box-content">';
                    resultModalHtml += '<span class="info-box-text">Dilewatkan</span>';
                    resultModalHtml += '<span class="info-box-number">' + response.skipped + '</span>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '<div class="col-md-4">';
                    resultModalHtml += '<div class="info-box bg-blue">';
                    resultModalHtml += '<span class="info-box-icon"><i class="fa fa-list"></i></span>';
                    resultModalHtml += '<div class="info-box-content">';
                    resultModalHtml += '<span class="info-box-text">Total Diproses</span>';
                    resultModalHtml += '<span class="info-box-number">' + response.total_processed + '</span>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';

                    // Failed data details
                    if (response.failed_data && response.failed_data.length > 0) {
                        resultModalHtml += '<div class="row">';
                        resultModalHtml += '<div class="col-md-12">';
                        resultModalHtml += '<div class="box box-danger">';
                        resultModalHtml += '<div class="box-header with-border">';
                        resultModalHtml += '<h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Detail Data yang Gagal</h3>';
                        resultModalHtml += '</div>';
                        resultModalHtml += '<div class="box-body">';
                        resultModalHtml += '<div class="table-responsive">';
                        resultModalHtml += '<table class="table table-bordered table-striped">';
                        resultModalHtml += '<thead>';
                        resultModalHtml += '<tr>';
                        resultModalHtml += '<th width="10%">Baris</th>';
                        resultModalHtml += '<th width="25%">Nama Lengkap</th>';
                        resultModalHtml += '<th width="20%">NIK KTP</th>';
                        resultModalHtml += '<th width="20%">No KK</th>';
                        resultModalHtml += '<th width="25%">Alasan Gagal</th>';
                        resultModalHtml += '</tr>';
                        resultModalHtml += '</thead>';
                        resultModalHtml += '<tbody>';

                        // Loop through failed data
                        for (var i = 0; i < response.failed_data.length; i++) {
                            var failed = response.failed_data[i];
                            resultModalHtml += '<tr>';
                            resultModalHtml += '<td>' + failed.row + '</td>';
                            resultModalHtml += '<td>' + (failed.nama_lengkap || '-') + '</td>';
                            resultModalHtml += '<td>' + (failed.nik_ktp || '-') + '</td>';
                            resultModalHtml += '<td>' + (failed.no_kk || '-') + '</td>';
                            resultModalHtml += '<td>';
                            if (failed.errors && failed.errors.length > 0) {
                                resultModalHtml += '<ul class="list-unstyled">';
                                for (var j = 0; j < failed.errors.length; j++) {
                                    resultModalHtml += '<li><small>' + failed.errors[j] + '</small></li>';
                                }
                                resultModalHtml += '</ul>';
                            }
                            resultModalHtml += '</td>';
                            resultModalHtml += '</tr>';
                        }

                        resultModalHtml += '</tbody>';
                        resultModalHtml += '</table>';
                        resultModalHtml += '</div>';
                        resultModalHtml += '</div>';
                        resultModalHtml += '</div>';
                        resultModalHtml += '</div>';
                        resultModalHtml += '</div>';
                    }

                    resultModalHtml += '</div>';
                    resultModalHtml += '<div class="modal-footer">';
                    resultModalHtml += '<button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';
                    resultModalHtml += '</div>';

                    // Remove existing result modal if any
                    $('#modal-import-result').remove();

                    // Add result modal to body
                    $('body').append(resultModalHtml);

                    // Show result modal
                    $('#modal-import-result').modal('show');

                    // Refresh table after modal is closed
                    $('#modal-import-result').on('hidden.bs.modal', function() {
                        table.ajax.reload();
                    });

                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#import-progress').hide();
                $('#modal-import button[type="submit"]').prop('disabled', false).html('Import Data');
            }
        });
    });

    // Reset import form when modal is closed
    $('#modal-import').on('hidden.bs.modal', function() {
        if ($('#form-import').length > 0) {
            $('#form-import')[0].reset();
        }
        $('#import-progress').hide();
        $('#modal-import button[type="submit"]').prop('disabled', false).html('Import Data');
    });
});
</script>
