<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    // Initialize Select2
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
            "url": "<?= base_url('kepengurusan_detail/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.status = $('#filter_status').val();
                d.kategori = $('#filter_kategori').val();
                d.fasilitas = $('#filter_fasilitas').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [
            { "targets": [-1], "orderable": false },
            { "targets": [0], "orderable": false }
        ],
        "order": [[1, 'asc']]
    });

    // Filter events
    $('#filter_status, #filter_kategori, #filter_fasilitas').select2().on('change', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#btn-reset-filter').click(function() {
        $('#filter_status, #filter_kategori, #filter_fasilitas').val('').trigger('change');
        table.ajax.reload();
    });

    // Add button
    $('#btn-add').click(function() {
        $('#form-detail')[0].reset();
        $('#modal-form .modal-title').html('<i class="fa fa-plus"></i> Tambah Kepengurusan Detail');
        $('#current-file').html('');
        $('#profile-preview').hide();
        $('#modal-form').modal('show');
        $('.select2').val('').trigger('change');
    });

    // Kepengurusan sosial change event - tampilkan profil
    $('#kepengurusan_sosial_id').change(function() {
        var pengurus_id = $(this).val();
        
        if (pengurus_id) {
            // Get profil pengurus
            $.ajax({
                url: '<?= base_url('kepengurusan_sosial/detail') ?>',
                type: 'POST',
                data: get_post_data({
                    id: pengurus_id
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        var data = response.data;
                        
                        // Update profile preview
                        $('#preview-nama').text(data.nama_lengkap || '-');
                        $('#preview-nik').text('NIK: ' + (data.nik_ktp || '-'));
                        $('#preview-hp').text(data.no_wa_hp || '-');
                        $('#preview-gender').text(data.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan');
                        $('#preview-agama').text(data.agama || '-');
                        $('#preview-pendidikan').text(data.pendidikan_terakhir || '-');
                        
                        // Update foto
                        if (data.foto_profil) {
                            $('#preview-foto').attr('src', '<?= base_url('uploads/kepengurusan_sosial/') ?>' + data.foto_profil);
                        } else {
                            $('#preview-foto').attr('src', '<?= base_url('assets/style/dist/img/avatar.png') ?>');
                        }
                        
                        // Show profile preview
                        $('#profile-preview').slideDown();
                    }
                },
                error: function() {
                    $('#profile-preview').hide();
                }
            });
        } else {
            $('#profile-preview').hide();
        }
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('kepengurusan_detail/detail') ?>',
            type: 'POST',
            data: get_post_data({
                id: id
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.id);
                    $('#kepengurusan_sosial_id').val(data.kepengurusan_sosial_id).trigger('change');
                    $('#kategori_kepengurusan_id').val(data.kategori_kepengurusan_id).trigger('change');
                    $('#id_fasilitas_umum').val(data.id_fasilitas_umum).trigger('change');
                    $('#tanggal_sk').val(data.tanggal_sk);
                    $('#nomor_sk').val(data.nomor_sk);
                    $('#masa_jabatan').val(data.masa_jabatan).trigger('change');
                    $('#tanggal_mulai').val(data.tanggal_mulai);
                    $('#tanggal_selesai').val(data.tanggal_selesai);
                    $('#status').val(data.status);
                    $('#file_sk_existing').val(data.file_sk);
                    
                    // Show current file if exists dengan preview
                    if (data.file_sk) {
                        var fileExt = data.file_sk.split('.').pop().toLowerCase();
                        var previewHtml = '<small class="text-info">File saat ini: ' + data.file_sk + '</small><br>';

                        if (data.secure_file_url) {
                            if (fileExt === 'pdf') {
                                previewHtml += '<button type="button" class="btn btn-sm btn-info btn-preview-file" data-url="' + data.secure_file_url + '">Preview PDF</button>';
                            } else if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                                previewHtml += '<button type="button" class="btn btn-sm btn-info btn-preview-image" data-url="' + data.secure_file_url + '">Preview Gambar</button>';
                            }
                        }

                        $('#current-file').html(previewHtml);
                    } else {
                        $('#current-file').html('');
                    }
                    
                    $('#modal-form .modal-title').html('<i class="fa fa-edit"></i> Edit Kepengurusan Detail');
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
            url: '<?= base_url('kepengurusan_detail/detail') ?>',
            type: 'POST',
            data: get_post_data({
                id: id
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var tanggal_sk = data.tanggal_sk ? formatDate(data.tanggal_sk) : '-';
                    var tanggal_mulai = data.tanggal_mulai ? formatDate(data.tanggal_mulai) : '-';
                    var tanggal_selesai = data.tanggal_selesai ? formatDate(data.tanggal_selesai) : '-';
                    
                    var html = '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><td><strong>Nama Pengurus</strong></td><td>' + (data.nama_lengkap || '-') + '</td></tr>';
                    html += '<tr><td><strong>Kategori</strong></td><td>' + (data.kepengurusan_nama || '-') + '</td></tr>';
                    html += '<tr><td><strong>Fasilitas</strong></td><td>' + (data.nama_fasilitas || '-') + '</td></tr>';
                    html += '<tr><td><strong>Tanggal SK</strong></td><td>' + tanggal_sk + '</td></tr>';
                    html += '<tr><td><strong>Nomor SK</strong></td><td>' + (data.nomor_sk || '-') + '</td></tr>';
                    html += '<tr><td><strong>Masa Jabatan</strong></td><td>' + (data.masa_jabatan ? data.masa_jabatan + ' tahun' : '-') + '</td></tr>';
                    html += '<tr><td><strong>Tanggal Mulai</strong></td><td>' + tanggal_mulai + '</td></tr>';
                    html += '<tr><td><strong>Tanggal Selesai</strong></td><td>' + tanggal_selesai + '</td></tr>';
                    // File SK dengan secure URL dan preview
                    var fileHtml = '-';
                    if (data.file_sk && data.secure_file_url) {
                        var fileExt = data.file_sk.split('.').pop().toLowerCase();
                        fileHtml = '<div>';
                        fileHtml += '<small class="text-muted">' + data.file_sk + '</small><br>';

                        if (fileExt === 'pdf') {
                            fileHtml += '<button type="button" class="btn btn-sm btn-info btn-preview-file" data-url="' + data.secure_file_url + '">Preview PDF</button>';
                        } else if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
                            fileHtml += '<button type="button" class="btn btn-sm btn-info btn-preview-image" data-url="' + data.secure_file_url + '">Preview Gambar</button>';
                        }
                        fileHtml += '</div>';
                    }
                    html += '<tr><td><strong>File SK</strong></td><td>' + fileHtml + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    
                    // Gunakan modal Bootstrap biasa
                    var modalHtml = '<div class="modal fade" id="modal-detail-kepengurusan" tabindex="-1" role="dialog">';
                    modalHtml += '<div class="modal-dialog modal-lg" role="document">';
                    modalHtml += '<div class="modal-content">';
                    modalHtml += '<div class="modal-header bg-info">';
                    modalHtml += '<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">';
                    modalHtml += '<span aria-hidden="true">&times;</span></button>';
                    modalHtml += '<h4 class="modal-title text-white"><i class="fa fa-eye"></i> Detail Kepengurusan</h4>';
                    modalHtml += '</div>';
                    modalHtml += '<div class="modal-body">' + html + '</div>';
                    modalHtml += '<div class="modal-footer">';
                    modalHtml += '<button type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>';
                    modalHtml += '</div>';
                    modalHtml += '</div></div></div>';
                    
                    // Remove existing modal if any
                    $('#modal-detail-kepengurusan').remove();
                    
                    // Add modal to body and show
                    $('body').append(modalHtml);
                    $('#modal-detail-kepengurusan').modal('show');
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('kepengurusan_detail/delete') ?>',
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
                }
            });
        }
    });

    // Update expired assignments button
    $('#btn-update-expired').click(function() {
        if (confirm('Apakah Anda yakin ingin memperbarui status penugasan yang sudah berakhir?')) {
            $.ajax({
                url: '<?= base_url('kepengurusan_detail/update_expired_assignments') ?>',
                type: 'POST',
                data: get_post_data({}),
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        table.ajax.reload();
                    } else {
                        toastr.info(response.message);
                    }
                }
            });
        }
    });

    // Form submit
    $('#form-detail').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('kepengurusan_detail/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#form-detail button[type="submit"]').prop('disabled', true).html('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    $('#modal-form').modal('hide');
                    table.ajax.reload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#form-detail button[type="submit"]').prop('disabled', false).html('Simpan');
            }
        });
    });

    // Auto calculate end date based on start date and period
    $('#tanggal_mulai, #masa_jabatan').on('change', function() {
        var tanggal_mulai = $('#tanggal_mulai').val();
        var masa_jabatan = $('#masa_jabatan').val();
        
        if (tanggal_mulai && masa_jabatan) {
            var startDate = new Date(tanggal_mulai);
            var endDate = new Date(startDate.setFullYear(startDate.getFullYear() + parseInt(masa_jabatan)));
            var endDateString = endDate.toISOString().split('T')[0];
            $('#tanggal_selesai').val(endDateString);
        }
    });

    // Reset form when modal is closed
    $('#modal-form').on('hidden.bs.modal', function() {
        $('#form-detail')[0].reset();
        $('#profile-preview').hide();
        $('#current-file').html('');
        $('.select2').val('').trigger('change');
    });

    // Preview file handlers
    $(document).on('click', '.btn-preview-file', function() {
        var url = $(this).data('url');
        window.open(url, '_blank');
    });

    $(document).on('click', '.btn-preview-image', function() {
        var url = $(this).data('url');
        var modalHtml = '<div class="modal fade" id="modal-preview-image" tabindex="-1" role="dialog">';
        modalHtml += '<div class="modal-dialog modal-lg" role="document">';
        modalHtml += '<div class="modal-content">';
        modalHtml += '<div class="modal-header">';
        modalHtml += '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        modalHtml += '<span aria-hidden="true">&times;</span></button>';
        modalHtml += '<h4 class="modal-title">Preview Gambar</h4>';
        modalHtml += '</div>';
        modalHtml += '<div class="modal-body text-center">';
        modalHtml += '<img src="' + url + '" class="img-responsive" style="max-width: 100%; height: auto;">';
        modalHtml += '</div>';
        modalHtml += '</div></div></div>';

        // Remove existing modal if any
        $('#modal-preview-image').remove();

        // Add modal to body and show
        $('body').append(modalHtml);
        $('#modal-preview-image').modal('show');
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
});
</script>
