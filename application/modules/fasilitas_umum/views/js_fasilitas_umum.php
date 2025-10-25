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


    // Load kecamatan untuk Kota Palembang (16.73)
    function loadKecamatanPalembang() {
        $.ajax({
            url: '<?= base_url('daerah/option_kecamatan/16.73') ?>',
            type: 'GET',
            success: function(data) {
                // For filter - add "Semua Kecamatan" option
                var filterOptions = '<option value="">-- Semua Kecamatan --</option>' + data;
                $('#filter_kecamatan').html(filterOptions.replace('<option value=""></option>', ''));
                
                // For form modal - add "Pilih Kecamatan" option
                var formOptions = '<option value="">Pilih Kecamatan</option>' + data;
                $('#id_kecamatan').html(formOptions.replace('<option value=""></option>', ''));
                
                // Only re-initialize if select2 is already initialized
                if ($('#filter_kecamatan').hasClass('select2-hidden-accessible')) {
                    $('#filter_kecamatan').select2('destroy');
                }
                if ($('#id_kecamatan').hasClass('select2-hidden-accessible')) {
                    $('#id_kecamatan').select2('destroy');
                }
                
                // Re-initialize select2 after updating options
                $('#filter_kecamatan').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Semua Kecamatan --'
                });
                
                $('#id_kecamatan').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih Kecamatan'
                });
            },
            error: function() {
                console.log('Error loading kecamatan untuk Palembang');
                $('#filter_kecamatan').html('<option value="">-- Error loading data --</option>');
                $('#id_kecamatan').html('<option value="">Error loading data</option>');
                
                // Only re-initialize if select2 is already initialized
                if ($('#filter_kecamatan').hasClass('select2-hidden-accessible')) {
                    $('#filter_kecamatan').select2('destroy');
                }
                if ($('#id_kecamatan').hasClass('select2-hidden-accessible')) {
                    $('#id_kecamatan').select2('destroy');
                }
                
                // Re-initialize select2 after error
                $('#filter_kecamatan').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: '-- Error loading data --'
                });
                
                $('#id_kecamatan').select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Error loading data'
                });
            }
        });
    }

    // Load kelurahan berdasarkan kecamatan
    function loadKelurahanByKecamatan(id_kecamatan, target_element) {
        var targetSelect = $('#' + target_element);
        var placeholder = target_element.includes('filter') ? '-- Semua Kelurahan --' : 'Pilih Kelurahan';
        
        targetSelect.html('<option value="">' + placeholder + '</option>');
        
        if (id_kecamatan === '' || !id_kecamatan) {
            // Only re-initialize if select2 is already initialized
            if (targetSelect.hasClass('select2-hidden-accessible')) {
                targetSelect.select2('destroy');
            }
            // Re-initialize select2 with default option
            targetSelect.select2({
                theme: 'bootstrap',
                width: '100%',
                allowClear: true,
                placeholder: placeholder
            });
            return;
        }
        
        $.ajax({
            url: '<?= base_url('daerah/option_kelurahan/') ?>' + id_kecamatan,
            type: 'GET',
            success: function(data) {
                var defaultOption = target_element.includes('filter') ? '-- Semua Kelurahan --' : 'Pilih Kelurahan';
                var options = '<option value="">' + defaultOption + '</option>' + data;
                targetSelect.html(options.replace('<option value=""></option>', ''));
                
                // Only re-initialize if select2 is already initialized
                if (targetSelect.hasClass('select2-hidden-accessible')) {
                    targetSelect.select2('destroy');
                }
                // Re-initialize select2 after updating options
                targetSelect.select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: defaultOption
                });
            },
            error: function() {
                var defaultOption = target_element.includes('filter') ? '-- Error loading data --' : 'Error loading data';
                targetSelect.html('<option value="">' + defaultOption + '</option>');
                
                // Only re-initialize if select2 is already initialized
                if (targetSelect.hasClass('select2-hidden-accessible')) {
                    targetSelect.select2('destroy');
                }
                // Re-initialize select2 after error
                targetSelect.select2({
                    theme: 'bootstrap',
                    width: '100%',
                    allowClear: true,
                    placeholder: defaultOption
                });
            }
        });
    }

    // DataTable initialization
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('fasilitas_umum/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.kategori = $('#filter_kategori').val();
                d.kecamatan = $('#filter_kecamatan').val();
                d.kelurahan = $('#filter_kelurahan').val();
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

    // Load kecamatan untuk Kota Palembang (16.73) with slight delay
    setTimeout(function() {
        loadKecamatanPalembang();
    }, 100);

    // Filter events
    $('#filter_kategori').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#filter_kecamatan').select2().on('change', function() {
        var id_kecamatan = $(this).val();
        loadKelurahanByKecamatan(id_kecamatan, 'filter_kelurahan');
        table.ajax.reload();
    });

    $('#filter_kelurahan').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#filter_status').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset').click(function() {
        $('#filter_kategori, #filter_kecamatan, #filter_kelurahan, #filter_status').val(null).trigger('change');
        loadKecamatanPalembang();
    });

    // Form modal events
    $('#id_kecamatan').on('change', function() {
        var id_kecamatan = $(this).val();
        loadKelurahanByKecamatan(id_kecamatan, 'id_kelurahan');
    });

    // Form submit
    $('#formFasilitas').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('fasilitas_umum/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#formFasilitas button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                
                if (response.status) {
                    $('#modalFasilitas').modal('hide');
                    table.ajax.reload();
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    
                    // Reset form
                    $('#formFasilitas')[0].reset();
                    $('#id').val('');
                    $('#modalFasilitasLabel').text('Tambah Fasilitas');
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
                $('#formFasilitas button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var kategori = $(this).data('kategori');
        var deskripsi = $(this).data('deskripsi');
        var alamat = $(this).data('alamat');
        var kecamatan = $(this).data('kecamatan');
        var kelurahan = $(this).data('kelurahan');
        var telepon = $(this).data('telepon');
        var latitude = $(this).data('latitude');
        var longitude = $(this).data('longitude');
        var status = $(this).data('status');

        $('#id').val(id);
        $('#nama_fasilitas').val(nama);
        $('#kategori_id').val(kategori).trigger('change');
        $('#deskripsi').val(deskripsi);
        $('#alamat').val(alamat);
        $('#telepon').val(telepon);
        $('#latitude').val(latitude);
        $('#longitude').val(longitude);
        $('#status').prop('checked', status == 1);
        $('#modalFasilitasLabel').text('Edit Fasilitas');

        $('#modalFasilitas').modal('show');
        
        // Load kecamatan and set values after modal is shown
        setTimeout(function() {
            loadKecamatanPalembang();
            
            // Re-initialize kategori_id select2 with proper configuration
            if ($('#kategori_id').hasClass('select2-hidden-accessible')) {
                $('#kategori_id').select2('destroy');
            }
            $('#kategori_id').select2({
                theme: 'bootstrap',
                width: '100%',
                dropdownParent: $('#modalFasilitas'),
                allowClear: true,
                placeholder: 'Pilih Kategori'
            });
            
            setTimeout(function() {
                if (kecamatan) {
                    $('#id_kecamatan').val(kecamatan).trigger('change');
                    setTimeout(function() {
                        if (kelurahan) {
                            $('#id_kelurahan').val(kelurahan);
                        }
                    }, 500);
                }
            }, 500);
        }, 200);
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data fasilitas ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    deleteFasilitas(id);
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus data fasilitas ini?')) {
                deleteFasilitas(id);
            }
        }
    });

    function deleteFasilitas(id) {
        $.ajax({
            url: '<?= base_url('fasilitas_umum/delete') ?>',
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
   $('#modalFasilitas').on('hidden.bs.modal', function() {
       $('#formFasilitas')[0].reset();
       $('#id').val('');
       $('#modalFasilitasLabel').text('Tambah Fasilitas');
       loadKecamatanPalembang();

       // Reset map
       if (map) {
           map.remove();
           map = null;
       }

       // Destroy Dropzone instance to allow re-initialization
       if (myDropzone) {
           myDropzone.destroy();
           myDropzone = null;
       }

       // Clear photo gallery
       $('#photo-gallery').html('');

       // Reset active tab to first tab
       $('.nav-tabs a:first').tab('show');
   });

   // Re-initialize Select2 and Dropzone when the modal is shown
   $('#modalFasilitas').on('shown.bs.modal', function() {
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
       $('#modalFasilitas .select2').each(function() {
           var $this = $(this);
           // Only destroy if already initialized
           if ($this.hasClass('select2-hidden-accessible')) {
               $this.select2('destroy');
           }
           $this.select2({
               theme: 'bootstrap',
               width: '100%',
               dropdownParent: $('#modalFasilitas'),
               allowClear: true,
               placeholder: function() {
                   return $(this).data('placeholder') || '';
               }
           });
       });
       if (!myDropzone) {
           myDropzone = new Dropzone("#foto-dropzone", {
               url: "<?= base_url('fasilitas_umum/upload_foto') ?>",
               paramName: "foto",
               maxFilesize: 5,
               acceptedFiles: "image/jpeg,image/jpg,image/png,image/gif",
               addRemoveLinks: true,
               dictDefaultMessage: "Klik atau seret foto ke sini",
               sending: function(file, xhr, formData) {
                   var fasilitasId = $('#id').val();
                   var dropzoneInstance = this;

                   // If it's a new facility (no ID), save the basic info first
                   if (!fasilitasId) {
                       // Prevent Dropzone from sending the file immediately
                       xhr.abort();

                       var basicData = new FormData($('#formFasilitas')[0]);

                       $.ajax({
                           url: '<?= base_url('fasilitas_umum/save') ?>',
                           type: 'POST',
                           data: basicData,
                           processData: false,
                           contentType: false,
                           dataType: 'json',
                           success: function(response) {
                               if (response.status && response.fasilitas_id) {
                                   // Save successful, get the new ID
                                   $('#id').val(response.fasilitas_id);
                                   toastr.info('Informasi dasar disimpan otomatis. Anda sekarang dapat mengunggah foto.');
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
                       formData.append('fasilitas_id', fasilitasId);
                   }
               },
               success: function(file, response) {
                   if (typeof response === 'string') {
                       response = JSON.parse(response);
                   }
                   if (response.status) {
                       toastr.success(response.message);
                       loadPhotos($('#id').val());
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

  // Map Initialization
  var map;
  var marker;
  var geocoder;

  function initMap(lat = -3.2946, lng = 102.8821) { // Default to Lubuk Linggau
      if (map) {
          map.remove();
      }

      map = L.map('map').setView([lat, lng], 13);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      marker = L.marker([lat, lng], { draggable: true }).addTo(map);

      // Initialize Geocoder with enhanced search functionality for Lubuk Linggau
      geocoder = L.Control.geocoder({
          defaultMarkGeocode: false,
          placeholder: 'Cari jalan, tempat, atau alamat...',
          errorMessage: 'Lokasi tidak ditemukan',
          showResultIcons: true,
          collapsed: false,
          expand: 'click',
          position: 'topright',
          geocoder: L.Control.Geocoder.nominatim({
              geocodingQueryParams: {
                  countrycodes: 'id',
                  'accept-language': 'id,en',
                  limit: 8,
                  // Prioritize Lubuk Linggau area
                  viewbox: '102.7,3.4,103.0,3.1', // Bounding box for Lubuk Linggau
                  bounded: 0, // Don't restrict strictly to viewbox
                  addressdetails: 1,
                  extratags: 1,
                  namedetails: 1
              }
          })
      })
      .on('markgeocode', function(e) {
          var bbox = e.geocode.bbox;
          var latlng = e.geocode.center;

          // Fit bounds to show the found location
          if (bbox) {
              map.fitBounds(bbox);
          } else {
              map.setView(latlng, 16);
          }

          // Move marker to found location
          marker.setLatLng(latlng);
          $('#latitude').val(latlng.lat.toFixed(7));
          $('#longitude').val(latlng.lng.toFixed(7));

          // Show popup with detailed location info
          var popupContent = '<b>' + e.geocode.name + '</b>';
          if (e.geocode.properties && e.geocode.properties.display_name) {
              popupContent += '<br><small>' + e.geocode.properties.display_name + '</small>';
          }
          marker.bindPopup(popupContent).openPopup();
      })
      .addTo(map);

      // Enhance search input with better placeholder and autocomplete
      setTimeout(function() {
          var searchInput = $('.leaflet-control-geocoder-form input');
          searchInput.attr('placeholder', 'Cari: Jl. Sudirman, Pasar Lubuk Linggau, dll...');
          searchInput.attr('autocomplete', 'off');

          // Add custom styling for better UX
          searchInput.css({
              'width': '250px',
              'padding': '8px 12px',
              'border-radius': '4px',
              'border': '1px solid #ccc',
              'font-size': '14px'
          });
      }, 100);

      marker.on('dragend', function(event) {
          var position = marker.getLatLng();
          $('#latitude').val(position.lat.toFixed(7));
          $('#longitude').val(position.lng.toFixed(7));

          // Reverse geocoding to get address
          geocoder.options.geocoder.reverse(position, map.options.crs.scale(map.getZoom()), function(results) {
              if (results.length > 0) {
                  var result = results[0];
                  var popupContent = '<b>Lokasi:</b><br>' + result.name;
                  if (result.properties && result.properties.display_name) {
                      popupContent += '<br><small>' + result.properties.display_name + '</small>';
                  }
                  marker.bindPopup(popupContent).openPopup();
              }
          });
      });

      map.on('click', function(e) {
          marker.setLatLng(e.latlng);
          $('#latitude').val(e.latlng.lat.toFixed(7));
          $('#longitude').val(e.latlng.lng.toFixed(7));

          // Reverse geocoding to get address
          geocoder.options.geocoder.reverse(e.latlng, map.options.crs.scale(map.getZoom()), function(results) {
              if (results.length > 0) {
                  var result = results[0];
                  var popupContent = '<b>Lokasi:</b><br>' + result.name;
                  if (result.properties && result.properties.display_name) {
                      popupContent += '<br><small>' + result.properties.display_name + '</small>';
                  }
                  marker.bindPopup(popupContent).openPopup();
              }
          });
      });
  }

    var myDropzone = null;

   // Load photos
   function loadPhotos(fasilitasId) {
       $('#photo-gallery').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat foto...</div>');
       $.ajax({
           url: '<?= base_url('fasilitas_umum/get_fotos') ?>',
           type: 'POST',
           data: {
               fasilitas_id: fasilitasId,
           },
           dataType: 'json',
           success: function(response) {
               $('#photo-gallery').html('');
               if (response.status && response.fotos.length > 0) {
                   response.fotos.forEach(function(foto) {
                       var photoHtml = `
                           <div class="col-md-3 photo-item" id="photo-${foto.id}">
                               <img src="${foto.file_url}" alt="${foto.nama_file}">
                               <div class="photo-actions">
                                   <button class="btn btn-danger btn-xs btn-delete-foto" data-id="${foto.id}"><i class="fa fa-trash"></i></button>
                               </div>
                           </div>
                       `;
                       $('#photo-gallery').append(photoHtml);
                   });
               } else {
                   $('#photo-gallery').html('<div class="col-md-12 text-center"><p>Belum ada foto yang diunggah.</p></div>');
               }
           },
           error: function() {
               $('#photo-gallery').html('<div class="col-md-12 text-center"><p class="text-red">Gagal memuat foto.</p></div>');
           }
       });
   }

   // Tab click handlers
   $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
       var target = $(e.target).attr("href");
       if (target === '#tab-location') {
           var lat = parseFloat($('#latitude').val()) || -3.2946;
           var lng = parseFloat($('#longitude').val()) || 102.8821;
           setTimeout(function() {
               if (!map) {
                   initMap(lat, lng);
               } else {
                   map.invalidateSize();
               }
           }, 200);
       } else if (target === '#tab-photos') {
           var fasilitasId = $('#id').val();
           if (fasilitasId) {
               if (myDropzone) myDropzone.enable();
               loadPhotos(fasilitasId);
           } else {
               if (myDropzone) myDropzone.disable();
               $('#photo-gallery').html('<div class="col-md-12 text-center"><p class="text-muted">Simpan informasi dasar terlebih dahulu.</p></div>');
           }
       }
   });

  // Delete photo
  $(document).on('click', '.btn-delete-foto', function(e) {
      e.preventDefault(); // Prevent any default action
      var fotoId = $(this).data('id');
       Swal.fire({
           title: 'Konfirmasi Hapus',
           text: 'Apakah Anda yakin ingin menghapus foto ini?',
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           cancelButtonColor: '#3085d6',
           confirmButtonText: 'Ya, Hapus!',
           cancelButtonText: 'Batal'
       }).then((result) => {
           if (result.isConfirmed) {
               $.ajax({
                   url: '<?= base_url('fasilitas_umum/delete_foto') ?>',
                   type: 'POST',
                   data: {
                       foto_id: fotoId,
                   },
                   dataType: 'json',
                   success: function(response) {
                       if (response.status) {
                           toastr.success(response.message);
                           $('#photo-' + fotoId).remove();
                       } else {
                           toastr.error(response.message);
                       }
                   },
                   error: function() {
                       toastr.error('Gagal menghapus foto.');
                   }
               });
           }
       });
   });
});
</script>