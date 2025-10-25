<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    // Initialize DataTable untuk Persyaratan (jika ada di halaman detail)
    if ($('#tablePersyaratan').length > 0) {
        var layanan_id = $('#layanan_id_form').val();
        var tablePersyaratan = $('#tablePersyaratan').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?= base_url('layanan_detail/ajax_persyaratan') ?>",
                "type": "POST",
                "data": function(d) {
                    d.layanan_id = layanan_id;
                },
                "dataSrc": function(json) {
                    return json.data;
                }
            },
            "columnDefs": [{ "targets": [-1], "orderable": false }],
            "order": [[4, 'asc'], [1, 'asc']],
            "language": {
                "processing": "Memproses...",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Belum ada persyaratan dokumen",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    }

    // DataTable
    var table = $('#table_layanan').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('layanan_detail/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.kategori = $('#filter_kategori').val();
                d.status = $('#filter_status').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": false }],
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
            }
        }
    });

    // Filter events
    $('#btn_filter').click(function() {
        table.draw();
    });

    $('#btn_reset').click(function() {
        $('#filter_kategori').val('');
        $('#filter_status').val('');
        table.draw();
    });

    // Form submit
    $('#formLayanan').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('layanan_detail/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formLayanan button[type="submit"]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalLayanan').modal('hide');
                    table.draw();
                    
                    // Show success message
                    toastr.success(response.message);
                    
                    // Reset form
                    $('#formLayanan')[0].reset();
                    $('#layanan_id').val('');
                    $('#modalTitle').text('Tambah Layanan');
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

    // Reset modal when closed
    $('#modalLayanan').on('hidden.bs.modal', function() {
        $('#formLayanan')[0].reset();
        $('#layanan_id').val('');
        $('#modalTitle').text('Tambah Layanan');
    });

    // Form submit untuk Persyaratan
    $('#formPersyaratan').submit(function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: '<?= base_url('layanan_detail/save_persyaratan') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formPersyaratan button[type="submit"]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalPersyaratan').modal('hide');
                    if (typeof tablePersyaratan !== 'undefined') {
                        tablePersyaratan.draw();
                    }
                    toastr.success(response.message);
                    $('#formPersyaratan')[0].reset();
                    $('#layanan_detail_id').val('');
                    $('#modalPersyaratanLabel').text('Form Persyaratan');
                } else {
                    toastr.error(response.message);
                }


            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#formPersyaratan button[type="submit"]').prop('disabled', false).text('Simpan');
            }
        });
    });

    // Handle file upload
    $('#contoh_file').change(function() {
        var file = this.files[0];
        if (file) {
            var formData = new FormData();
            formData.append('contoh_file', file);


            $.ajax({
                url: '<?= base_url('layanan_detail/upload_contoh_file') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#uploadProgress').show();
                    $('.progress-bar').css('width', '0%');
                },
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            $('.progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    var result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.status) {
                        $('#btnPreviewFile').show();
                        $('#btnRemoveFile').show();
                        $('#btnPreviewFile').data('file-path', result.file_path);
                        $('#btnPreviewFile').data('file-name', result.file_name);
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'layanan_detail_contoh_file',
                            value: result.file_path
                        }).appendTo('#formPersyaratan');
                        toastr.success('File berhasil diupload');
                    } else {
                        toastr.error(result.message);
                        $('#contoh_file').val('');
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan saat upload file');
                    $('#contoh_file').val('');
                },
                complete: function() {
                    $('#uploadProgress').hide();
                }
            });
        }
    });

    // Reset modal persyaratan when closed
    $('#modalPersyaratan').on('hidden.bs.modal', function() {
        $('#formPersyaratan')[0].reset();
        $('#layanan_detail_id').val('');
        $('#modalPersyaratanLabel').text('Form Persyaratan');
        $('#layanan_detail_wajib').prop('checked', true);
        $('#layanan_detail_status').prop('checked', true);
        $('#layanan_detail_urutan').val('0');
        $('#layanan_detail_tipe_file').val('PDF,JPG,PNG');
        $('#layanan_detail_ukuran_max').val('5120');
        $('#btnPreviewFile').hide();
        $('#btnRemoveFile').hide();
        $('#currentFileInfo').hide();
        $('input[name="layanan_detail_contoh_file"]').remove();
    });

});

// Function to edit data - menggunakan AJAX untuk load form edit
function edit_data(id) {
    $.ajax({
        url: '<?= base_url('layanan_detail/get_layanan_by_id') ?>',
        type: 'POST',
        data: {
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.status && response.data) {
                var data = response.data;

                // Load form edit via AJAX
                $.ajax({
                    url: '<?= base_url('layanan_detail/load_edit_form') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(formHtml) {
                        $('#modalLayanan .modal-content').html(formHtml);

                        // Populate form fields setelah form dimuat
                        setTimeout(function() {
                            $('#layanan_id').val(data.layanan_id || id);
                            $('#layanan_nama').val(data.layanan_nama);
                            $('#layanan_kategori').val(data.layanan_kategori);
                            $('#layanan_deskripsi').val(data.layanan_deskripsi);
                            $('#layanan_durasi').val(data.layanan_durasi);
                            $('#layanan_status').val(data.layanan_status);

                            $('#modalLayanan').modal('show');
                        }, 100);
                    },
                    error: function() {
                        toastr.error('Gagal memuat form edit');
                    }
                });
            } else {
                toastr.error(response.message || 'Data tidak ditemukan');
            }
        },
        error: function() {
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}

// Function to delete data
function delete_data(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        $.ajax({
            url: '<?= base_url('layanan_detail/delete') ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#table_layanan').DataTable().draw();
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
}

// Function to view detail
function view_detail(id) {
    window.location.href = '<?= base_url('layanan_detail/detail/') ?>' + id;
}

// Functions untuk CRUD Persyaratan
function tambah_persyaratan() {
    $('#formPersyaratan')[0].reset();
    $('#layanan_detail_id').val('');
    $('#modalPersyaratanLabel').text('Tambah Persyaratan');
    $('#layanan_detail_wajib').prop('checked', true);
    $('#layanan_detail_status').prop('checked', true);
    $('#layanan_detail_urutan').val('0');
    $('#layanan_detail_tipe_file').val('PDF,JPG,PNG');
    $('#layanan_detail_ukuran_max').val('5120');
    $('#btnPreviewFile').hide();
    $('#btnRemoveFile').hide();
    $('#currentFileInfo').hide();
    $('input[name="layanan_detail_contoh_file"]').remove();
    $('#modalPersyaratan').modal('show');
}

function edit_persyaratan(id) {
    $.ajax({
        url: '<?= base_url('layanan_detail/get_persyaratan/') ?>' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status && response.data) {
                var data = response.data;

                $('#layanan_detail_id').val(data.layanan_detail_id);
                $('#layanan_detail_nama').val(data.layanan_detail_nama);
                $('#layanan_detail_deskripsi').val(data.layanan_detail_deskripsi);
                $('#layanan_detail_tipe_file').val(data.layanan_detail_tipe_file);
                $('#layanan_detail_ukuran_max').val(data.layanan_detail_ukuran_max);
                $('#layanan_detail_wajib').prop('checked', data.layanan_detail_wajib == 1);
                $('#layanan_detail_urutan').val(data.layanan_detail_urutan);
                $('#layanan_detail_status').prop('checked', data.layanan_detail_status == 1);

                // Handle existing file
                if (data.layanan_detail_contoh_file) {
                    $('#currentFileInfo').show();
                    $('#currentFileName').text(data.layanan_detail_contoh_file.split('/').pop());
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'layanan_detail_contoh_file',
                        value: data.layanan_detail_contoh_file
                    }).appendTo('#formPersyaratan');
                } else {
                    $('#currentFileInfo').hide();
                }

                $('#modalPersyaratanLabel').text('Edit Persyaratan');
                $('#modalPersyaratan').modal('show');
            } else {
                toastr.error(response.message || 'Data tidak ditemukan');
            }
        },
        error: function() {
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}

function delete_persyaratan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus persyaratan ini?')) {
        $.ajax({
            url: '<?= base_url('layanan_detail/delete_persyaratan') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    if (typeof tablePersyaratan !== 'undefined') {
                        tablePersyaratan.draw();
                    }
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
}

// Functions untuk File Upload dan Preview
function previewFile() {
    var filePath = $('#btnPreviewFile').data('file-path');
    var fileName = $('#btnPreviewFile').data('file-name');

    if (filePath) {
        showFilePreview(filePath, fileName);
    }
}

function previewCurrentFile() {
    var fileName = $('#currentFileName').text();
    var filePath = $('input[name="layanan_detail_contoh_file"]').val();

    if (filePath) {
        showFilePreview(filePath, fileName);
    }
}

function previewFileFromTable(filePath, fileName) {
    showFilePreview(filePath, fileName);
}

function showFilePreview(filePath, fileName) {
    var fileExt = filePath.split('.').pop().toLowerCase();
    var fullPath = '<?= base_url() ?>' + filePath;
    var previewContainer = $('#filePreviewContainer');

    $('#modalPreviewFileLabel').text('Preview: ' + fileName);
    $('#downloadFileBtn').attr('href', fullPath);

    previewContainer.html('');

    if (['jpg', 'jpeg', 'png'].includes(fileExt)) {
        // Preview gambar
        previewContainer.html('<img src="' + fullPath + '" class="img-responsive" style="max-width: 100%; max-height: 500px;">');
    } else if (fileExt === 'pdf') {
        // Preview PDF
        previewContainer.html('<iframe src="' + fullPath + '" width="100%" height="500px" frameborder="0"></iframe>');
    } else {
        // File lain (DOC, DOCX)
        previewContainer.html(
            '<div class="text-center" style="padding: 50px;">' +
            '<i class="fa fa-file-o fa-5x text-muted"></i>' +
            '<h4>File: ' + fileName + '</h4>' +
            '<p class="text-muted">Preview tidak tersedia untuk tipe file ini.<br>Silakan download untuk melihat isi file.</p>' +
            '</div>'
        );
    }

    $('#modalPreviewFile').modal('show');
}

function removeFile() {
    var filePath = $('input[name="layanan_detail_contoh_file"]').val();

    if (filePath) {
        $.ajax({
            url: '<?= base_url('layanan_detail/delete_contoh_file') ?>',
            type: 'POST',
            data: {
                file_path: filePath
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#contoh_file').val('');
                    $('#btnPreviewFile').hide();
                    $('#btnRemoveFile').hide();
                    $('input[name="layanan_detail_contoh_file"]').remove();
                    toastr.success('File berhasil dihapus');
                } else {
                    toastr.error(response.message);
                }


            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            }
        });
    }
}
</script>
