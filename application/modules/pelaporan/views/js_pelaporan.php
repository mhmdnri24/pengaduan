<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
var map, marker, geocoder;

$(document).ready(function() {

    // Initialize Select2
    initializeSelect2();
    
    // Initialize DataTable
    var table = $('#table-pelaporan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('pelaporan/ajax_data') ?>',
            type: 'POST',
            data: function(d) {
                d.status = $('#filter-status').val();
                d.kategori = $('#filter-kategori').val();
                d.prioritas = $('#filter-prioritas').val();
                d.unitkerja = $('#filter-unitkerja').val();
            }
        },
        columns: [
            {data: '0', name: 'no', orderable: false, searchable: false},
            {data: '1', name: 'kode_laporan'},
            {data: '2', name: 'judul'},
            {data: '3', name: 'kategori', orderable: false},
            //{data: '4', name: 'unitkerja', orderable: false},
            {data: '4', name: 'status', orderable: false},
            //{data: '6', name: 'prioritas', orderable: false},
            {data: '5', name: 'created_at'},
            {data: '6', name: 'aksi', orderable: false, searchable: false}
        ],
        order: [[5, 'desc']], // Urutkan berdasarkan tanggal terbaru
        language: {
            processing: "Memproses...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        drawCallback: function() {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });

    // Load initial data
    loadKategoriOptions();
    loadMasyarakatOptions();

    // Filter change events
     $('#filter-status, #filter-kategori, #filter-prioritas').change(function() {
         table.ajax.reload();
     });

     // Filter unitkerja only for super admin
     if ($('#filter-unitkerja').length > 0) {
         $('#filter-unitkerja').change(function() {
             table.ajax.reload();
         });
     }

    // Reset filter
     $('#btn-reset-filter').click(function() {
         $('#filter-status, #filter-kategori, #filter-prioritas').val('').trigger('change');
         if ($('#filter-unitkerja').length > 0) {
             $('#filter-unitkerja').val('').trigger('change');
         }
         table.ajax.reload();
     });

    // Modal events
    $('#modal-laporan').on('hidden.bs.modal', function() {
        resetForm();
    });

    $('#modal-status').on('hidden.bs.modal', function() {
        $('#form-status')[0].reset();
    });

    // Auto-reload table every 30 seconds
    setInterval(function() {
        if ($('#table-pelaporan').length > 0) {
            $('#table-pelaporan').DataTable().ajax.reload(null, false);
        }
    }, 30000);

    // Add button
    $('.btn-add').click(function() {
        resetForm();
        $('#modal-title').html('<i class="fa fa-plus"></i> Tambah Laporan');
        $('#modal-laporan').modal('show');
        // Initialize map when modal is shown
        setTimeout(function() {
            initializeMap();
        }, 500);
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        editLaporan(id);
    });

    // Update status button
    $(document).on('click', '.btn-update-status', function() {
        var id = $(this).data('id');
        var currentStatus = $(this).data('status');
        showUpdateStatusModal(id, currentStatus);
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        deleteLaporan(id);
    });

    // Export button
    $('#btn-export').click(function() {
        loadKategoriForExport();
        $('#modal-export').modal('show');
    });

    // Form submit
    $('#form-laporan').submit(function(e) {
        e.preventDefault();
        saveLaporan();
    });

    // Status form submit
    $('#form-status').submit(function(e) {
        e.preventDefault();
        updateStatus();
    });

    // Masyarakat selection change
    $('#masyarakat_id').change(function() {
        var selectedId = $(this).val();
        if (selectedId) {
            loadMasyarakatData(selectedId);
        } else {
            clearPelapor();
        }
    });

    // Location detection
    $('#btn-detect-location').click(function() {
        detectCurrentLocation();
    });

    // Clear location
    $('#btn-clear-location').click(function() {
        clearLocation();
    });

    // Tab change event to initialize map
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#tab-location') {
            setTimeout(function() {
                if (map) {
                    map.invalidateSize();
                } else {
                    initializeMap();
                }
            }, 100);
        }
    });
});

// Initialize Select2
function initializeSelect2() {
    $('.select2').select2({
        theme: 'default',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Pilih...';
        },
        allowClear: true
    });
}

// Initialize Leaflet Map
function initializeMap() {
    if (map) {
        map.remove();
    }

    // Default center (Palembang)
    var defaultLat = -2.9760285;
    var defaultLng = 104.7754794;

    map = L.map('map').setView([defaultLat, defaultLng], 13);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add geocoder (search box)
    geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Cari lokasi...',
        errorMessage: 'Lokasi tidak ditemukan'
    }).on('markgeocode', function(e) {
        var latlng = e.geocode.center;
        setMapLocation(latlng.lat, latlng.lng, e.geocode.name);
    }).addTo(map);

    // Map click event
    map.on('click', function(e) {
        setMapLocation(e.latlng.lat, e.latlng.lng);
        // Reverse geocoding to get address
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // Load existing location if editing
    var existingLat = $('#lokasi_lat').val();
    var existingLng = $('#lokasi_lng').val();
    if (existingLat && existingLng) {
        setMapLocation(existingLat, existingLng);
    }
}

// Set location on map
function setMapLocation(lat, lng, address) {
    if (marker) {
        map.removeLayer(marker);
    }

    marker = L.marker([lat, lng], {
        icon: L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })
    }).addTo(map);

    // Update form fields
    $('#lokasi_lat').val(lat.toFixed(8));
    $('#lokasi_lng').val(lng.toFixed(8));

    // Show location info
    $('#location-info').show();
    $('#selected-coordinates').text(lat.toFixed(6) + ', ' + lng.toFixed(6));
    
    if (address) {
        $('#selected-location').text(address);
    } else {
        $('#selected-location').text('Lokasi terpilih');
    }

    // Center map to marker
    map.setView([lat, lng], 15);
}

// Reverse geocoding
function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data.display_name) {
                $('#selected-location').text(data.display_name);
                // Auto-fill address if empty
                if (!$('#alamat').val()) {
                    $('#alamat').val(data.display_name);
                }
            }
        })
        .catch(error => {
            console.log('Reverse geocoding error:', error);
        });
}

// Detect current location
function detectCurrentLocation() {
    if (navigator.geolocation) {
        $('#btn-detect-location').html('<i class="fa fa-spinner fa-spin"></i> Mendeteksi...');
        
        navigator.geolocation.getCurrentPosition(function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            setMapLocation(lat, lng);
            reverseGeocode(lat, lng);
            $('#btn-detect-location').html('<i class="fa fa-crosshairs"></i> Deteksi Lokasi Saya');
        }, function(error) {
            alert('Gagal mendeteksi lokasi: ' + error.message);
            $('#btn-detect-location').html('<i class="fa fa-crosshairs"></i> Deteksi Lokasi Saya');
        });
    } else {
        alert('Browser tidak mendukung geolocation');
    }
}

// Clear location
function clearLocation() {
    if (marker && map) {
        map.removeLayer(marker);
        marker = null;
    }
    $('#lokasi_lat, #lokasi_lng').val('');
    $('#location-info').hide();
    if (map) {
        map.setView([-2.9760285, 104.7754794], 13);
    }
}

// Load kategori options
function loadKategoriOptions() {
    $.ajax({
        url: '<?= base_url('pelaporan/get_kategori') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                var options = '<option value="">Pilih Kategori</option>';
                var filterOptions = '<option value="">Semua Kategori</option>';
                
                $.each(response.data, function(index, item) {
                    options += '<option value="' + item.pelaporan_nama + '">' + item.pelaporan_nama + '</option>';
                    filterOptions += '<option value="' + item.pelaporan_nama + '">' + item.pelaporan_nama + '</option>';
                });
                
                $('#kategori').html(options);
                $('#filter-kategori').html(filterOptions);
                
                // Reinitialize select2
                $('#kategori, #filter-kategori').select2();
            }
        }
    });
}

// Load masyarakat options
function loadMasyarakatOptions() {
    $.ajax({
        url: '<?= base_url('pelaporan/get_masyarakat') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                var options = '<option value="">Pilih dari data masyarakat</option>';
                
                $.each(response.data, function(index, item) {
                    options += '<option value="' + item.id + '">' + item.nama_lengkap + ' - ' + item.nik + '</option>';
                });
                
                $('#masyarakat_id').html(options);
                $('#masyarakat_id').select2();
            }
        }
    });
}

// Load masyarakat data
function loadMasyarakatData(id) {
    $.ajax({
        url: '<?= base_url('pelaporan/get_masyarakat_by_id') ?>',
        type: 'POST',
        data: {
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                var data = response.data;
                $('#pelapor_nama').val(data.nama_lengkap);
                $('#pelapor_nik').val(data.nik);
                $('#pelapor_telepon').val(data.no_telpon);

                $('#pelapor_alamat').val(''); // Address not in masyarakat table
            }
        }
    });
}

// Clear pelapor fields
function clearPelapor() {
    $('#pelapor_nama, #pelapor_nik, #pelapor_telepon, #pelapor_alamat').val('');
}

// Load kategori for export
function loadKategoriForExport() {
    $.ajax({
        url: '<?= base_url('pelaporan/get_kategori') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                var options = '<option value="">Semua Kategori</option>';
                
                $.each(response.data, function(index, item) {
                    options += '<option value="' + item.pelaporan_nama + '">' + item.pelaporan_nama + '</option>';
                });
                
                $('#export_kategori').html(options);
                $('#export_kategori').select2();
            }
        }
    });
}

// Reset form
function resetForm() {
    $('#form-laporan')[0].reset();
    $('#laporan-id').val('');
    $('#form-laporan .form-group').removeClass('has-error');
    $('#form-laporan .help-block').remove();
    
    // Reset select2
    $('#kategori, #prioritas, #masyarakat_id').val('').trigger('change');
    
    // Clear location
    clearLocation();
    
    // Reset to first tab
    $('.nav-tabs a:first').tab('show');
}

// Edit laporan
function editLaporan(id) {
    $.ajax({
        url: '<?= base_url('pelaporan/get_by_id') ?>',
        type: 'POST',
        data: {
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                var data = response.data;
                $('#laporan-id').val(data.id);
                $('#judul').val(data.judul);
                $('#kategori').val(data.kategori).trigger('change');
                $('#deskripsi').val(data.deskripsi);
                $('#alamat').val(data.alamat);
                $('#lokasi_lat').val(data.lokasi_lat);
                $('#lokasi_lng').val(data.lokasi_lng);
                $('#prioritas').val(data.prioritas).trigger('change');
                $('#pelapor_nama').val(data.pelapor_nama);

                $('#pelapor_telepon').val(data.pelapor_telepon);
                $('#pelapor_nik').val(data.pelapor_nik);
                $('#pelapor_alamat').val(data.pelapor_alamat);
                
                if (data.masyarakat_id) {
                    $('#masyarakat_id').val(data.masyarakat_id).trigger('change');
                }
                
                $('#modal-title').html('<i class="fa fa-edit"></i> Edit Laporan');
                $('#modal-laporan').modal('show');
                
                // Initialize map with existing location
                setTimeout(function() {
                    initializeMap();
                }, 500);
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'Terjadi kesalahan saat mengambil data');
        }
    });
}

// Save laporan
function saveLaporan() {
    var formData = $('#form-laporan').serialize();
    
    $.ajax({
        url: '<?= base_url('pelaporan/save') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('#form-laporan button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            $('#form-laporan .form-group').removeClass('has-error');
            $('#form-laporan .help-block').remove();
        },
        success: function(response) {
            if (response.status) {
                $('#modal-laporan').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#table-pelaporan').DataTable().ajax.reload(null, false);
                resetForm();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Terjadi kesalahan saat menyimpan data'
            });
        },
        complete: function() {
            $('#form-laporan button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Laporan');
        }
    });
}

// Show update status modal
function showUpdateStatusModal(id, currentStatus) {
    $('#status-id').val(id);
    
    // Load available status options based on current status
    var statusOptions = '';
    var statusFlow = {
        'LAPOR': ['DITERIMA'],
        'DITERIMA': ['DIKERJAKAN'],
        'DIKERJAKAN': ['SELESAI'],
        'SELESAI': []
    };
    
    if (statusFlow[currentStatus]) {
        $.each(statusFlow[currentStatus], function(index, status) {
            var icon = '';
            switch(status) {
                case 'DITERIMA': icon = '✅ '; break;
                case 'DIKERJAKAN': icon = '⚙️ '; break;
                case 'SELESAI': icon = '✔️ '; break;
            }
            statusOptions += '<option value="' + status + '">' + icon + status + '</option>';
        });
    }
    
    $('#status').html('<option value="">Pilih Status</option>' + statusOptions);
    $('#status').select2();
    $('#modal-status').modal('show');
}

// Update status
function updateStatus() {
    var formData = $('#form-status').serialize();

    $.ajax({
        url: '<?= base_url('pelaporan/update_status') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('#form-status button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memperbarui...');
        },
        success: function(response) {
            if (response.status) {
                $('#modal-status').modal('hide');
                showAlert('success', response.message);
                $('#table-pelaporan').DataTable().ajax.reload();
            } else {
                showAlert('danger', response.message);
            }
        },
        error: function() {
            showAlert('danger', 'Terjadi kesalahan saat memperbarui status');
        },
        complete: function() {
            $('#form-status button[type="submit"]').prop('disabled', false).html('<i class="fa fa-refresh"></i> Update Status');
            $('#form-status')[0].reset();
            $('#status').select2();
        }
    });
}

// Delete laporan
function deleteLaporan(id) {
    if (confirm('Apakah Anda yakin ingin menghapus laporan ini?\n\nData yang terhapus tidak dapat dikembalikan.')) {
        $.ajax({
            url: '<?= base_url('pelaporan/delete') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    showAlert('success', response.message);
                    $('#table-pelaporan').DataTable().ajax.reload();
                } else {
                    showAlert('danger', response.message);
                }

            },
            error: function() {
                showAlert('danger', 'Terjadi kesalahan saat menghapus data');
            }
        });
    }
}

// Show alert
function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible">' +
                   '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                   '<i class="fa ' + icon + '"></i> ' + message + '</div>';
    
    $('.row:first .col-md-12').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Scroll to top
    $('html, body').animate({scrollTop: 0}, 500);
}

// Modal events
$('#modal-laporan').on('hidden.bs.modal', function() {
    resetForm();
    if (map) {
        map.remove();
        map = null;
    }
});

$('#modal-status').on('hidden.bs.modal', function() {
    $('#form-status')[0].reset();
    $('#status').select2();
});

$('#modal-export').on('shown.bs.modal', function() {
    $('#export_format, #export_status, #export_kategori').select2();
});

// Initialize tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>