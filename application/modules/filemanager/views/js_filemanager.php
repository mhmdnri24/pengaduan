<script>
$(document).ready(function() {
    var current_dir_id = '';

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('filemanager/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.directory = current_dir_id;
                return get_post_data(d);
            },
            "dataSrc": function(json) {
                current_dir_id = json.current_dir_id;
                updateBreadcrumb(json.data);
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [0, 1, 3], "orderable": false }],
        "order": []
    });

    function updateBreadcrumb(data) {
        var breadcrumb = '<li><a href="#" data-dir="uploads"><i class="fa fa-home"></i> Root</a></li>';
        
        if (typeof table.ajax.json().breadcrumb !== 'undefined') {
            var breadcrumbData = table.ajax.json().breadcrumb;
            
            $.each(breadcrumbData, function(i, item) {
                breadcrumb += '<li><a href="#" data-dir="' + item.path + '">' + item.name + '</a></li>';
            });
        }
        
        $('#directory-breadcrumb').html(breadcrumb);
    }

    $(document).on('click', '#directory-breadcrumb a', function(e) {
        e.preventDefault();
        var dir = $(this).data('dir');
        current_dir_id = dir;
        table.ajax.reload();
    });

    $(document).on('click', '.btn-navigate', function() {
        var dir = $(this).data('dir');
        current_dir_id = dir;
        table.ajax.reload();
    });

    $('#btn-upload-file').on('click', function() {
        $('#uploadDir').val(current_dir_id);
        $('#modalFileList').modal('show');
    });

    $('#btn-create-folder').on('click', function() {
        $('#parentDir').val(current_dir_id);
        $('#folder-name').val('');
        $('#modalCreateFolder').modal('show');
    });

    $('#btn-trigger-upload').on('click', function() {
        $('#file-input').click();
    });

    $('#file-input').on('change', function() {
        if ($(this).val() != '') {
            var files = $(this)[0].files;
            var totalFiles = files.length;
            var successCount = 0;
            var errorCount = 0;
            
            if (totalFiles > 0) {
                var btnUpload = $('#btn-trigger-upload');
                var originalBtnText = btnUpload.html();
                
                btnUpload.html('<i class="fa fa-spinner fa-spin"></i> Mengunggah...').prop('disabled', true);
                $('#upload-progress-container').show();
                
                for (var i = 0; i < totalFiles; i++) {
                    var formData = new FormData();
                    formData.append('file', files[i]);
                    formData.append('dir', $('#uploadDir').val());
                    
                    $.ajax({
                        xhr: function() {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function(evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = parseInt((evt.loaded / evt.total) * 100);
                                    $('#upload-progress-bar').css('width', percentComplete + '%').text(percentComplete + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        url: '<?= base_url('filemanager/upload_file') ?>',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                successCount++;
                            } else {
                                errorCount++;
                            }
                            
                            if ((successCount + errorCount) === totalFiles) {
                                if (errorCount > 0) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Upload Selesai',
                                        text: successCount + ' file berhasil diunggah, ' + errorCount + ' file gagal.'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: 'Semua file berhasil diunggah.'
                                    });
                                }
                                btnUpload.html(originalBtnText).prop('disabled', false);
                                $('#upload-progress-container').hide();
                                $('#file-input').val('');
                                table.ajax.reload();
                            }
                        },
                        error: function() {
                            errorCount++;
                            if ((successCount + errorCount) === totalFiles) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Upload Selesai',
                                    text: successCount + ' file berhasil diunggah, ' + errorCount + ' file gagal.'
                                });
                                btnUpload.html(originalBtnText).prop('disabled', false);
                                $('#upload-progress-container').hide();
                                $('#file-input').val('');
                                table.ajax.reload();
                            }
                        }
                    });
                }
            }
        }
    });

    $('#btn-submit-folder').on('click', function() {
        var folderName = $('#folder-name').val().trim();
        if (!folderName) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Nama folder tidak boleh kosong!'
            });
            return;
        }
        
        var btnSubmit = $(this);
        var originalBtnText = btnSubmit.html();
        
        $.ajax({
            url: '<?= base_url('filemanager/create_folder') ?>',
            type: 'POST',
            data: get_post_data({
                parent_dir: $('#parentDir').val(),
                folder_name: folderName
            }),
            dataType: 'json',
            beforeSend: function() {
                btnSubmit.html('<i class="fa fa-spinner fa-spin"></i> Memproses...').prop('disabled', true);
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    });
                    $('#modalCreateFolder').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat membuat folder.'
                });
            },
            complete: function() {
                btnSubmit.html(originalBtnText).prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-delete-file', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Anda yakin?',
            text: "File akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('filemanager/delete_file') ?>',
                    type: 'POST',
                    data: get_post_data({
                        id: id
                    }),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-delete-directory', function() {
        var dir_id = $(this).data('dir');
        var dir_name = $(this).data('name');
        
        Swal.fire({
            title: 'Anda yakin?',
            text: "Folder '" + dir_name + "' dan semua isinya akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('filemanager/delete_directory') ?>',
                    type: 'POST',
                    data: get_post_data({
                        dir_id: dir_id
                    }),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menghapus folder.'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-preview-file', function() {
        var fileId = $(this).data('id');
        var fileName = $(this).data('name');
        var fileExt = fileName.split('.').pop().toLowerCase();
        
        $('#modalPreviewLabel').text('Preview: ' + fileName);
        $('#preview-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Memuat preview...</p></div>');
        $('#modalPreview').modal('show');
        
        // Dapatkan URL aman untuk file ini
        $.ajax({
            url: '<?= base_url('filemanager/get_secure_url') ?>',
            type: 'POST',
            data: get_post_data({
                file_id: fileId
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var secureUrl = response.secure_url;
                    var imageTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    var pdfTypes = ['pdf'];
                    var officeTypes = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
                    
                    if (imageTypes.includes(fileExt)) {
                        // Preview gambar
                        $('#preview-content').html('<img src="' + secureUrl + '" class="img-responsive" style="max-width: 100%; max-height: 80vh; margin: auto;">');
                        $('#preview-download-btn').html('<a href="' + site_url + 'filemanager/download_file/' + fileId + '" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>');
                    } else if (pdfTypes.includes(fileExt)) {
                        // Preview PDF
                        $('#preview-content').html('<iframe src="' + secureUrl + '" style="width:100%; height:80vh;" frameborder="0"></iframe>');
                        $('#preview-download-btn').html('<a href="' + site_url + 'filemanager/download_file/' + fileId + '" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>');
                    } else if (officeTypes.includes(fileExt)) {
                        // Preview dokumen Office dengan Google Docs Viewer
                        var encodedUrl = encodeURIComponent(secureUrl);
                        $('#preview-content').html('<iframe src="https://docs.google.com/viewer?url=' + encodedUrl + '&embedded=true" style="width:100%; height:80vh;" frameborder="0"></iframe>');
                        $('#preview-download-btn').html('<a href="' + site_url + 'filemanager/download_file/' + fileId + '" class="btn btn-primary"><i class="fa fa-download"></i> Download</a>');
                    } else {
                        // File tidak dapat di-preview
                        $('#preview-content').html('<div class="text-center"><i class="fa fa-file-o" style="font-size: 100px; color: #ccc;"></i><h4 class="mt-3">File tidak dapat di-preview</h4><p>Silakan download file untuk melihatnya.</p></div>');
                        $('#preview-download-btn').html('<a href="' + site_url + 'filemanager/download_file/' + fileId + '" class="btn btn-primary"><i class="fa fa-download"></i> Download File</a>');
                    }
                } else {
                    $('#preview-content').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Error: ' + response.message + '</div>');
                    $('#preview-download-btn').html('');
                }
            },
            error: function() {
                $('#preview-content').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> Terjadi kesalahan saat memuat preview file.</div>');
                $('#preview-download-btn').html('');
            }
        });
    });
});

var site_url = '<?= site_url() ?>';
</script>