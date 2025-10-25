<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {
    // Disable Dropzone auto discover
    if (typeof Dropzone !== 'undefined') {
        Dropzone.autoDiscover = false;
    }

    // Initialize Select2 with proper configuration
    $('.select2').each(function() {
        var $this = $(this);
        $this.select2({
            theme: 'bootstrap',
            width: '100%',
            allowClear: true,
            placeholder: function() {
                return $(this).data('placeholder') || '';
            }
        });
    });

    // Load status options
    function loadStatusOptions() {
        var statusOptions = '<option value="">-- Semua Status --</option><option value="AKTIF">Aktif</option><option value="NON AKTIF">Non Aktif</option>';
        var statusOptionsForm = '<option value="">Pilih Status</option><option value="AKTIF">Aktif</option><option value="NON AKTIF">Non Aktif</option>';
        
        $('#filter_status').html(statusOptions);
        $('#slider_status').html(statusOptionsForm);
        
        // Re-initialize select2 after updating options
        $('#filter_status').select2('destroy');
        $('#slider_status').select2('destroy');
        
        $('#filter_status').select2({
            theme: 'bootstrap',
            width: '100%',
            allowClear: true,
            placeholder: '-- Semua Status --'
        });
        
        $('#slider_status').select2({
            theme: 'bootstrap',
            width: '100%',
            allowClear: true,
            placeholder: 'Pilih Status'
        });
    }

    // Load statistics
    function loadStatistics() {
        $.ajax({
            url: '<?= base_url('slider/get_statistics') ?>',
            type: 'GET',
            success: function(response) {
                if (response.status && response.data) {
                    $('#total-slider').text(response.data.total || 0);
                    $('#slider-aktif').text(response.data.active || 0);
                    $('#slider-nonaktif').text(response.data.inactive || 0);
                    $('#update-terakhir').text(response.data.last_update || '-');
                }
            },
            error: function() {
                console.log('Error loading statistics');
            }
        });
    }

    // Load initial data
    loadStatusOptions();
    loadStatistics();

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('slider/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.status = $('#filter_status').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": true }],
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
    $('#filter_status').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset').click(function() {
        $('#filter_status').val(null).trigger('change');
    });

    // Form submit
    $('#formSlider').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('slider/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#formSlider button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                
                if (response.status) {
                    $('#modalSlider').modal('hide');
                    table.ajax.reload();
                    
                    // Update statistics
                    updateStatistics();
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    
                    // Reset form
                    $('#formSlider')[0].reset();
                    $('#id').val('');
                    $('#modalSliderLabel').text('Tambah Slider');
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Terjadi kesalahan sistem');
                } else {
                    alert('Terjadi kesalahan sistem');
                }
            },
            complete: function() {
                $('#formSlider button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var judul = $(this).data('judul');
        var deskripsi = $(this).data('deskripsi');
        var file = $(this).data('file');
        var status = $(this).data('status');

        $('#id').val(id);
        $('#slider_judul').val(judul);
        $('#slider_deskripsi').val(deskripsi);
        $('#slider_status').val(status).trigger('change');
        $('#modalSliderLabel').text('Edit Slider');

        $('#modalSlider').modal('show');
        
        // Load existing image if available
        if (file) {
            loadImagePreview(file);
        } else {
            $('#image-preview').html('');
        }
        
        // Re-initialize select2 with proper configuration
        if ($('#slider_status').hasClass('select2-hidden-accessible')) {
            $('#slider_status').select2('destroy');
        }
        $('#slider_status').select2({
            theme: 'bootstrap',
            width: '100%',
            dropdownParent: $('#modalSlider'),
            allowClear: true,
            placeholder: 'Pilih Status'
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data slider ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    deleteSlider(id);
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus data slider ini?')) {
                deleteSlider(id);
            }
        }
    });

    function deleteSlider(id) {
        $.ajax({
            url: '<?= base_url('slider/delete') ?>',
            type: 'POST',
            data: {
                id: id,
            },
            dataType: 'json',
            beforeSend: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Sedang memproses permintaan',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            },
            success: function(response) {

                if (response.status) {
                    table.ajax.reload();
                    updateStatistics();
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Gagal!',
                            text: response.message,
                            icon: 'error'
                        });
                    } else {
                        alert(response.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error details:', xhr.responseText);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem: ' + error,
                        icon: 'error'
                    });
                } else {
                    alert('Terjadi kesalahan sistem: ' + error);
                }
            }
        });
    }

    // Reset form when modal is hidden
   $('#modalSlider').on('hidden.bs.modal', function() {
       $('#formSlider')[0].reset();
       $('#id').val('');
       $('#modalSliderLabel').text('Tambah Slider');
       $('#image-preview').html('');

       // Destroy Dropzone instance to allow re-initialization
       if (myDropzone) {
           myDropzone.destroy();
           myDropzone = null;
       }

       // Reset active tab to first tab
       $('.nav-tabs a:first').tab('show');
   });

   // Re-initialize Select2 and Dropzone when the modal is shown
   $('#modalSlider').on('shown.bs.modal', function() {
       // Fix Select2 z-index issue in modal
       $.fn.modal.Constructor.prototype.enforceFocus = function() {
           $(document)
               .off('focusin.bs.modal') // guard against infinite focus loop
               .on('focusin.bs.modal', $.proxy(function(e) {
                   if (
                       this.$element[0] !== e.target &&
                       !this.$element.has(e.target).length &&
                       !$(e.target).closest('.select2-dropdown').length
                   ) {
                       this.$element.trigger('focus');
                   }
               }, this));
       };
       
       // Re-initialize select2 elements in modal
       $('#modalSlider .select2').each(function() {
           var $this = $(this);
           // Only destroy if already initialized
           if ($this.hasClass('select2-hidden-accessible')) {
               $this.select2('destroy');
           }
           $this.select2({
               theme: 'bootstrap',
               width: '100%',
               dropdownParent: $('#modalSlider'),
               allowClear: true,
               placeholder: function() {
                   return $(this).data('placeholder') || '';
               }
           });
       });
       
       if (!myDropzone) {
           myDropzone = new Dropzone("#image-dropzone", {
               url: "<?= base_url('slider/upload_file') ?>",
               paramName: "slider_file",
               maxFilesize: 2, // 2MB
               acceptedFiles: "image/jpeg,image/jpg,image/png,image/gif,image/svg+xml",
               addRemoveLinks: true,
               dictDefaultMessage: "Klik atau seret gambar ke sini",
               dictRemoveFile: "Hapus file",
               sending: function(file, xhr, formData) {
                   var sliderId = $('#id').val();
                   var dropzoneInstance = this;

                   // If it's a new slider (no ID), save the basic info first
                   if (!sliderId) {
                       // Prevent Dropzone from sending the file immediately
                       xhr.abort();

                       var basicData = new FormData($('#formSlider')[0]);

                       $.ajax({
                           url: '<?= base_url('slider/save') ?>',
                           type: 'POST',
                           data: basicData,
                           processData: false,
                           contentType: false,
                           dataType: 'json',
                           success: function(response) {
                               if (response.status && response.slider_id) {
                                   // Save successful, get the new ID
                                   $('#id').val(response.slider_id);
                                   toastr.info('Informasi dasar disimpan otomatis. Anda sekarang dapat mengunggah gambar.');
                                   // Now, re-add and process the file with the new ID
                                   dropzoneInstance.addFile(file);
                               } else {
                                   toastr.error('Gagal menyimpan info dasar: ' + response.message);
                                   dropzoneInstance.removeFile(file);
                               }
                           },
                           error: function() {
                               toastr.error('Terjadi kesalahan saat menyimpan info dasar.');
                               dropzoneInstance.removeFile(file);
                           }
                       });
                   } else {
                       // If ID already exists, proceed as normal
                       formData.append('slider_id', sliderId);
                   }
               },
               success: function(file, response) {
                   if (typeof response === 'string') {
                       response = JSON.parse(response);
                   }
                   if (response.status) {
                       toastr.success(response.message);
                       loadImagePreview(response.data.file_name);
                   } else {
                       toastr.error(response.message);
                   }
                   setTimeout(() => this.removeFile(file), 2000);
               },
               error: function(file, message) {
                   var errorMessage = (typeof message === "object" && message.message) ? message.message : message;
                   toastr.error('Upload gagal: ' + errorMessage);
                   this.removeFile(file);
               }
           });
       }
   });

   var myDropzone = null;

   // Load image preview
   function loadImagePreview(fileName) {
       $('#image-preview').html(`
           <div class="col-md-12 slider-preview" id="preview-${fileName}">
               <img src="<?= base_url('uploads/slider/') ?>${fileName}" alt="Slider Image">
               <div class="slider-actions">
                   <button class="btn btn-danger btn-xs btn-delete-image" data-file="${fileName}"><i class="fa fa-trash"></i></button>
               </div>
           </div>
       `);
   }

   // Tab click handlers
   $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
       var target = $(e.target).attr("href");
       if (target === '#tab-image') {
           var sliderId = $('#id').val();
           if (sliderId) {
               if (myDropzone) myDropzone.enable();
           } else {
               if (myDropzone) myDropzone.disable();
               $('#image-preview').html('<div class="col-md-12 text-center"><p class="text-muted">Simpan informasi dasar terlebih dahulu.</p></div>');
           }
       }
   });

   // Delete image
   $(document).on('click', '.btn-delete-image', function(e) {
       e.preventDefault();
       var fileName = $(this).data('file');
       var sliderId = $('#id').val();
       
       Swal.fire({
           title: 'Konfirmasi Hapus',
           text: 'Apakah Anda yakin ingin menghapus gambar ini?',
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           cancelButtonColor: '#3085d6',
           confirmButtonText: 'Ya, Hapus!',
           cancelButtonText: 'Batal'
       }).then((result) => {
           if (result.isConfirmed) {
               $.ajax({
                   url: '<?= base_url('slider/delete_file') ?>',
                   type: 'POST',
                   data: {
                       slider_id: sliderId,
                   },
                   dataType: 'json',
                   success: function(response) {
                       if (response.status) {
                           toastr.success(response.message);
                           $('#preview-' + fileName).remove();
                       } else {
                           toastr.error(response.message);
                       }
                   },
                   error: function() {
                       toastr.error('Gagal menghapus gambar.');
                   }
               });
           }
       });
   });

   // Update statistics function
   function updateStatistics() {
       $.ajax({
           url: '<?= base_url('slider/ajax_data') ?>',
           type: 'POST',
           data: {
               draw: 1,
               start: 0,
               length: 1,
               search: { value: '' },
               order: [{ column: 0, dir: 'asc' }],
               columns: [{ data: 'id', name: '', searchable: true, orderable: true, search: { value: '' } }]
           },
           dataType: 'json',
           success: function(response) {
               $('#total-slider').text(response.recordsTotal);
               
               // Update active and inactive counts
               $.ajax({
                   url: '<?= base_url('slider/ajax_data') ?>',
                   type: 'POST',
                   data: {
                       draw: 1,
                       start: 0,
                       length: 1,
                       search: { value: '' },
                       order: [{ column: 0, dir: 'asc' }],
                       columns: [{ data: 'id', name: '', searchable: true, orderable: true, search: { value: '' } }],
                       status: 'AKTIF'
                   },
                   dataType: 'json',
                   success: function(response) {
                       $('#slider-aktif').text(response.recordsFiltered);
                   }
               });
               
               $.ajax({
                   url: '<?= base_url('slider/ajax_data') ?>',
                   type: 'POST',
                   data: {
                       draw: 1,
                       start: 0,
                       length: 1,
                       search: { value: '' },
                       order: [{ column: 0, dir: 'asc' }],
                       columns: [{ data: 'id', name: '', searchable: true, orderable: true, search: { value: '' } }],
                       status: 'NON AKTIF'
                   },
                   dataType: 'json',
                   success: function(response) {
                       $('#slider-nonaktif').text(response.recordsFiltered);
                   }
               });
           }
       });
   }
});
</script>