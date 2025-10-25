<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!-- Lightbox CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
<!-- Dropzone CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<!-- Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
// CSRF Token

// Global variables to prevent multiple initializations
var myDropzone = null;
var mapInitialized = false;

$(document).ready(function() {
    // Initialize lightbox for foto pelapor
    initializeLightbox();

    // Initialize dropzone for foto upload only if container exists and not already initialized
    if ($('#foto-dropzone').length > 0 && !myDropzone) {
        setTimeout(function() {
            initializeDropzone();
        }, 100);
    }

    // Load existing uploaded photos
    loadUploadedPhotos();

    // Initialize map if coordinates exist with delay to ensure DOM is ready
    <?php if ($laporan->lokasi_lat && $laporan->lokasi_lng): ?>
    setTimeout(function() {
        if (!mapInitialized) {
            initDetailMap();
        }
    }, 500);
    <?php endif; ?>

    // Update status button click
    $(document).on('click', '.btn-update-status', function() {
        var id = $(this).data('id');
        var currentStatus = $(this).data('status');
        showUpdateStatusModal(id, currentStatus);
    });

    // Form comment submit
    $('#form-comment').submit(function(e) {
        e.preventDefault();
        submitComment();
    });

    // Star rating functionality
    $('.star').on('click', function() {
        var rating = $(this).data('rating');
        $('#rating-value').val(rating);
        
        // Update star colors
        $('.star').each(function(index) {
            if (index < rating) {
                $(this).css('color', '#f39c12');
            } else {
                $(this).css('color', '#ddd');
            }
        });
    });
    
    // Star rating hover effect
    $('.star').on('mouseenter', function() {
        var rating = $(this).data('rating');
        $('.star').each(function(index) {
            if (index < rating) {
                $(this).css('color', '#f39c12');
            } else {
                $(this).css('color', '#ddd');
            }
        });
    });
    
    // Reset stars on mouse leave
    $('.rating-input').on('mouseleave', function() {
        var currentRating = $('#rating-value').val();
        $('.star').each(function(index) {
            if (index < currentRating) {
                $(this).css('color', '#f39c12');
            } else {
                $(this).css('color', '#ddd');
            }
        });
    });
    
    // Comment modal events
    $('.btn-add-comment').on('click', function() {
        var pelaporan_id = $(this).data('id');
        $('#comment-pelaporan-id').val(pelaporan_id);
        $('#modal-comment').modal('show');
    });
    
    // Reset modal when hidden
    $('#modal-comment').on('hidden.bs.modal', function() {
        $('#form-comment')[0].reset();
        $('#rating-value').val('0');
        $('.star').css('color', '#ddd');
    });
});

// Additional initialization on window load for better reliability
$(window).on('load', function() {
    <?php if ($laporan->lokasi_lat && $laporan->lokasi_lng): ?>
    // Retry map initialization if it failed during document ready
    if (!mapInitialized) {
        setTimeout(function() {
            initDetailMap();
        }, 1000);
    }
    <?php endif; ?>
});

// Debug function to check dropzone status
function debugDropzoneStatus() {
    console.log('=== DROPZONE DEBUG INFO ===');
    console.log('Dropzone library loaded:', typeof Dropzone !== 'undefined');
    console.log('Dropzone container exists:', document.getElementById('foto-dropzone') !== null);
    console.log('MyDropzone instance:', myDropzone);
    
    var container = document.getElementById('foto-dropzone');
    if (container) {
        console.log('Container classes:', container.className);
        console.log('Container style:', container.style.cssText);
        console.log('Container pointer-events:', window.getComputedStyle(container).pointerEvents);
        console.log('Container cursor:', window.getComputedStyle(container).cursor);
        console.log('Container click handlers:', container.onclick);
        console.log('Has dropzone attached:', !!container.dropzone);
    }
    
    if (myDropzone) {
        console.log('Dropzone clickable:', myDropzone.options.clickable);
        console.log('Dropzone element:', myDropzone.element);
        console.log('Hidden file input:', myDropzone.hiddenFileInput);
    }
    console.log('============================');
}

// Test upload URL accessibility
function testUploadURL() {
    console.log('Testing upload URL...');
    
    var uploadUrl = '<?= base_url('pelaporan/upload_foto_progress') ?>';
    
    if (!uploadUrl || uploadUrl.trim() === '') {
        console.error('Upload URL is not configured');
        return;
    }
    
    $.ajax({
        url: uploadUrl,
        type: 'POST',
        data: { test: 'connection' },
        success: function(response) {
            console.log('Upload URL is accessible:', response);
        },
        error: function(xhr, status, error) {
            console.error('Upload URL test failed:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                url: uploadUrl
            });
        }
    });
}

// Force re-initialize dropzone (use this in console if needed)
function forceReinitDropzone() {
    console.log('Force reinitializing dropzone...');
    
    // Reset global variable
    myDropzone = null;
    
    // Clear element
    var element = document.getElementById('foto-dropzone');
    if (element) {
        if (element.dropzone) {
            element.dropzone.destroy();
            delete element.dropzone;
        }
        element.classList.remove('dz-started', 'dz-max-files-reached');
        element.hasClickHandler = false;
        element.hasManualFallback = false;
    }
    
    // Reinitialize
    setTimeout(function() {
        initializeDropzone();
    }, 100);
}
function debugMapStatus() {
    console.log('=== MAP DEBUG INFO ===');
    console.log('Leaflet loaded:', typeof L !== 'undefined');
    console.log('Map container exists:', document.getElementById('detail-map') !== null);
    console.log('Map initialized:', mapInitialized);
    console.log('Map instance:', window.detailMapInstance);
    
    var container = document.getElementById('detail-map');
    if (container) {
        console.log('Container dimensions:', container.offsetWidth, 'x', container.offsetHeight);
        console.log('Container visible:', container.offsetParent !== null);
    }
    console.log('======================');
}



// Initialize detail map with proper error handling and library check
function initDetailMap() {
    <?php if ($laporan->lokasi_lat && $laporan->lokasi_lng): ?>
    try {
        // Check if Leaflet is loaded
        if (typeof L === 'undefined') {
            console.error('Leaflet library not loaded');
            return;
        }
        
        var lat = <?= $laporan->lokasi_lat ?>;
        var lng = <?= $laporan->lokasi_lng ?>;
        
        // Check if map container exists and is visible
        var mapContainer = document.getElementById('detail-map');
        if (!mapContainer) {
            console.warn('Map container #detail-map not found');
            return;
        }
        
        // Check if container has dimensions
        if (mapContainer.offsetWidth === 0 || mapContainer.offsetHeight === 0) {
            console.warn('Map container has zero dimensions, retrying...');
            setTimeout(function() {
                initDetailMap();
            }, 1000);
            return;
        }
        
        // Clear any existing map instance
        if (window.detailMapInstance) {
            window.detailMapInstance.remove();
        }
        
        // Initialize Leaflet map
        window.detailMapInstance = L.map('detail-map').setView([lat, lng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(window.detailMapInstance);
        
        // Add marker for report location
        var marker = L.marker([lat, lng]).addTo(window.detailMapInstance);
        
        // Add popup with report information
        marker.bindPopup(`
            <div style="min-width: 220px; max-width: 300px;">
                <h5><strong><?= addslashes($laporan->judul) ?></strong></h5>
                <p><strong>Kode:</strong> <?= $laporan->kode_laporan ?></p>
                <p><strong>Alamat:</strong> <?= addslashes($laporan->alamat) ?></p>
                <p><strong>Status:</strong> <span class="label label-info label-sm" style="font-size: 11px; padding: 3px 6px;"><?= $laporan->status ?></span></p>
                <hr style="margin: 8px 0; border-color: #e9ecef;">
                <div style="display: flex; gap: 5px;">
                    <button type="button" class="btn btn-primary btn-xs" onclick="openInGoogleMaps(<?= $laporan->lokasi_lat ?>, <?= $laporan->lokasi_lng ?>, '<?= addslashes($laporan->alamat) ?>')" style="border-radius: 4px; font-size: 11px; padding: 4px 8px; flex: 1;">
                        <i class="fa fa-external-link"></i> Maps
                    </button>
                    <button type="button" class="btn btn-info btn-xs" onclick="openStreetView(<?= $laporan->lokasi_lat ?>, <?= $laporan->lokasi_lng ?>)" style="border-radius: 4px; font-size: 11px; padding: 4px 8px; flex: 1;">
                        <i class="fa fa-street-view"></i> Street View
                    </button>
                </div>
            </div>
        `).openPopup();
        
        // Add circle to show approximate area
        L.circle([lat, lng], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.2,
            radius: 100
        }).addTo(window.detailMapInstance);
        
        // Mark as initialized
        mapInitialized = true;
        console.log('Map initialized successfully at coordinates:', lat, lng);
        
        // Force map to resize after initialization
        setTimeout(function() {
            window.detailMapInstance.invalidateSize();
        }, 100);
        
    } catch (error) {
        console.error('Error initializing map:', error);
        // Retry initialization after a delay
        setTimeout(function() {
            if (!mapInitialized) {
                initDetailMap();
            }
        }, 2000);
    }
    <?php endif; ?>
}

// Show update status modal with proper status options
function showUpdateStatusModal(id, currentStatus) {
    // Set form data
    $('#status-id').val(id);
    
    // Clear and populate status options based on current status
    var statusSelect = $('#status');
    statusSelect.empty();
    statusSelect.append('<option value="">Pilih Status</option>');
    
    // Define status progression
    var statusOptions = {
        'LAPOR': [
            {value: 'DITERIMA', text: 'DITERIMA - Laporan Diterima'},
            {value: 'DITOLAK', text: 'DITOLAK - Laporan Ditolak'}
        ],
        'DITERIMA': [
            {value: 'DIKERJAKAN', text: 'DIKERJAKAN - Sedang Dikerjakan'},
            {value: 'PENDING', text: 'PENDING - Pending'}
        ],
        'DIKERJAKAN': [
            {value: 'SELESAI', text: 'SELESAI - Selesai Dikerjakan'},
            {value: 'PENDING', text: 'PENDING - Pending'}
        ],
        'PENDING': [
            {value: 'DIKERJAKAN', text: 'DIKERJAKAN - Lanjutkan Pekerjaan'},
            {value: 'SELESAI', text: 'SELESAI - Selesai'}
        ]
    };
    
    if (statusOptions[currentStatus]) {
        statusOptions[currentStatus].forEach(function(option) {
            statusSelect.append('<option value="' + option.value + '">' + option.text + '</option>');
        });
    }
    
    // Show modal
    $('#modal-status').modal('show');
}

// Handle form status submit with proper error handling
$(document).on('submit', '#form-status', function(e) {
    e.preventDefault();

    var formData = $(this).serialize();
    var submitBtn = $(this).find('button[type="submit"]');
    var originalText = submitBtn.html();

    $.ajax({
        url: '<?= base_url('pelaporan/update_status') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memperbarui...');
        },
        success: function(response) {
            submitBtn.prop('disabled', false).html(originalText);

            if (response.status) {
                $('#modal-status').modal('hide');
                toastr.success(response.message || 'Status berhasil diperbarui');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                toastr.error(response.message || 'Gagal memperbarui status');
            }
        },
        error: function(xhr, status, error) {
            submitBtn.prop('disabled', false).html(originalText);
            toastr.error('Terjadi kesalahan sistem: ' + error);
        }
    });
});

// Submit comment
function submitComment() {
    var formData = $('#form-comment').serialize();
    
    $.ajax({
        url: '<?= base_url('pelaporan/save_comment') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            $('#form-comment button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');
        },
        success: function(response) {
            $('#form-comment button[type="submit"]').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Komentar');
            
            if (response.status) {
                toastr.success(response.message);
                $('#form-comment')[0].reset();
                $('.rating-input label').removeClass('active');
                location.reload(); // Reload to show new comment
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            $('#form-comment button[type="submit"]').prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Komentar');
            toastr.error('Terjadi kesalahan sistem');
        }
    });
}

// Edit laporan function
function editLaporan(id) {
    window.location.href = '<?= base_url('pelaporan') ?>?edit=' + id;
}

// Initialize lightbox
function initializeLightbox() {
    $('.foto-pelapor').each(function(index) {
        $(this).attr('data-lightbox', 'foto-pelapor');
        $(this).attr('data-title', 'Foto Pelapor ' + (index + 1));
        $(this).wrap('<a href="' + $(this).data('src') + '"></a>');
    });
}

// Initialize dropzone with proper error handling and click functionality
function initializeDropzone() {
    // Disable auto discover to prevent double initialization
    Dropzone.autoDiscover = false;

    var dropzoneElement = document.getElementById('foto-dropzone');
    if (!dropzoneElement) {
        console.warn('Dropzone container not found');
        return;
    }

    // Check if dropzone already exists and destroy it first
    if (myDropzone) {
        console.log('Destroying existing dropzone instance');
        myDropzone.destroy();
        myDropzone = null;
    }

    // Check if element already has dropzone attached and remove it
    if (dropzoneElement.dropzone) {
        console.log('Removing existing dropzone from element');
        dropzoneElement.dropzone.destroy();
        delete dropzoneElement.dropzone;
    }

    // Clear any existing dropzone classes
    dropzoneElement.classList.remove('dz-started', 'dz-max-files-reached');

    try {
        var uploadUrl = '<?= base_url('pelaporan/upload_foto_progress') ?>';
        
        // Validate URL before initialization
        if (!uploadUrl || uploadUrl.trim() === '') {
            throw new Error('Upload URL is not configured');
        }
        
        console.log('Initializing new dropzone with URL:', uploadUrl);
        
        myDropzone = new Dropzone('#foto-dropzone', {
            url: uploadUrl,
            paramName: 'foto',
            maxFilesize: 5, // MB
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            clickable: true, // Ensure dropzone is clickable
            autoQueue: true,
            uploadMultiple: false,
            parallelUploads: 1,
            maxFiles: 10,
            dictDefaultMessage: '<i class="fa fa-cloud-upload fa-3x"></i><br><strong>Klik atau drag foto ke sini</strong><br><small>Format: JPG, PNG, GIF (Max: 5MB)</small>',
            dictRemoveFile: 'Hapus',
            dictCancelUpload: 'Batal',
            dictUploadCanceled: 'Upload dibatalkan',
            dictCancelUploadConfirmation: 'Yakin ingin membatalkan upload?',
            dictRetry: 'Coba lagi',
            dictMaxFilesExceeded: 'Maksimal file tercapai',
            dictFileTooBig: 'File terlalu besar (maksimal 5MB)',
            dictInvalidFileType: 'Tipe file tidak valid',
            dictResponseError: 'Server error',

            init: function() {
                var dropzoneInstance = this;
                
                console.log('Dropzone init function called');
                
                // Add manual click handler as backup
                this.element.addEventListener('click', function(e) {
                    console.log('Dropzone element clicked');
                    // Only handle clicks on empty areas or the message
                    if (e.target === this || e.target.closest('.dz-message')) {
                        console.log('Triggering file dialog');
                        dropzoneInstance.hiddenFileInput.click();
                    }
                });
                
                this.on('sending', function(file, xhr, formData) {
                    formData.append('pelaporan_id', <?= $laporan->id ?>);
                    console.log('Sending file:', file.name);
                });

                this.on('success', function(file, response) {
                    console.log('Upload success response:', response);
                    try {
                        var res;
                        // Handle both string and object responses
                        if (typeof response === 'string') {
                            res = JSON.parse(response);
                        } else if (typeof response === 'object' && response !== null) {
                            res = response;
                        } else {
                            throw new Error('Invalid response format');
                        }
                        
                        if (res.status) {
                            toastr.success('Foto berhasil diupload');
                            file.serverId = res.file_id || res.data?.file_id;
                            loadUploadedPhotos(); // Reload photos
                        } else {
                            toastr.error(res.message || 'Upload gagal');
                            this.removeFile(file);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, 'Response:', response);
                        toastr.error('Error parsing response: ' + e.message);
                        this.removeFile(file);
                    }
                });

                this.on('error', function(file, errorMessage) {
                    console.error('Upload error:', errorMessage);
                    toastr.error('Error upload: ' + errorMessage);
                });

                this.on('removedfile', function(file) {
                    if (file.serverId) {
                        deleteUploadedPhoto(file.serverId);
                    }
                });
                
                this.on('addedfile', function(file) {
                    console.log('File added:', file.name);
                });
                
                this.on('dragenter', function() {
                    console.log('Drag enter');
                    this.element.classList.add('dz-drag-hover');
                });
                
                this.on('dragleave', function() {
                    console.log('Drag leave');
                    this.element.classList.remove('dz-drag-hover');
                });
                
                this.on('drop', function() {
                    console.log('File dropped');
                    this.element.classList.remove('dz-drag-hover');
                });
            }
        });
        
        // Add manual click fallback after a short delay
        setTimeout(function() {
            addDropzoneClickFallback();
        }, 200);
        
        console.log('Dropzone initialized successfully');
    } catch (error) {
        console.error('Error initializing Dropzone:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack,
            dropzoneElement: dropzoneElement,
            url: '<?= base_url('pelaporan/upload_foto_progress') ?>'
        });
        
        // Show user-friendly error message
        toastr.error('Gagal menginisialisasi dropzone: ' + error.message);
        
        // Add manual fallback if dropzone fails
        addManualUploadFallback();
    }
}

// Load existing uploaded photos
function loadUploadedPhotos() {
    $.ajax({
        url: '<?= base_url('pelaporan/get_uploaded_photos') ?>',
        type: 'POST',
        data: {
            pelaporan_id: <?= $laporan->id ?>,
        },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                displayUploadedPhotos(response.data);
            }
        }
    });
}

// Display uploaded photos
function displayUploadedPhotos(photos) {
    var html = '';
    if (photos && photos.length > 0) {
        html += '<div class="col-md-12"><h4><strong>Foto Progress dari Petugas</strong></h4></div>';
        photos.forEach(function(photo, index) {
            var imageUrl = '<?= base_url() ?>' + photo.file_path;
            html += `
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="thumbnail">
                        <img src="${imageUrl}"
                             class="img-responsive foto-progress"
                             style="height: 150px; object-fit: cover; cursor: pointer;"
                             data-lightbox="foto-progress"
                             data-title="Foto Progress ${index + 1}">
                        <div class="caption">
                            <p class="text-center">
                                <small class="text-muted">
                                    ${photo.uploaded_at}<br>
                                    Oleh: ${photo.uploaded_by_name}
                                </small>
                            </p>
                            <button type="button" class="btn btn-danger btn-xs btn-block"
                                    onclick="deleteUploadedPhoto(${photo.id})">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    $('#uploaded-photos').html(html);
}

// Add manual upload fallback when dropzone completely fails
function addManualUploadFallback() {
    var dropzoneElement = document.getElementById('foto-dropzone');
    if (dropzoneElement && !dropzoneElement.hasManualFallback) {
        console.log('Adding manual upload fallback');
        
        // Replace dropzone content with manual upload button
        dropzoneElement.innerHTML = `
            <div style="text-align: center; padding: 40px; border: 2px dashed #ccc; border-radius: 8px; cursor: pointer;">
                <i class="fa fa-cloud-upload fa-3x" style="color: #0087F7; margin-bottom: 15px;"></i><br>
                <strong>Klik untuk upload foto</strong><br>
                <small>Format: JPG, PNG, GIF (Max: 5MB)</small>
                <input type="file" id="manual-file-input" accept="image/*" multiple style="display: none;">
            </div>
        `;
        
        var fileInput = document.getElementById('manual-file-input');
        
        dropzoneElement.addEventListener('click', function() {
            console.log('Manual fallback clicked');
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                for (var i = 0; i < this.files.length; i++) {
                    uploadFileManually(this.files[i]);
                }
            }
        });
        
        dropzoneElement.hasManualFallback = true;
    }
}

// Manual file upload function
function uploadFileManually(file) {
    console.log('Uploading file manually:', file.name);
    
    var uploadUrl = '<?= base_url('pelaporan/upload_foto_progress') ?>';
    
    // Validate URL
    if (!uploadUrl || uploadUrl.trim() === '') {
        toastr.error('Upload URL tidak terkonfigurasi');
        return;
    }
    
    var formData = new FormData();
    formData.append('foto', file);
    formData.append('pelaporan_id', <?= $laporan->id ?>);
    
    $.ajax({
        url: uploadUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            toastr.info('Mengupload ' + file.name + '...');
        },
        success: function(response) {
            try {
                var res;
                // Handle both string and object responses
                if (typeof response === 'string') {
                    res = JSON.parse(response);
                } else if (typeof response === 'object' && response !== null) {
                    res = response;
                } else {
                    throw new Error('Invalid response format');
                }
                
                if (res.status) {
                    toastr.success('Foto berhasil diupload: ' + file.name);
                    loadUploadedPhotos();
                } else {
                    toastr.error('Gagal upload: ' + (res.message || 'Error tidak diketahui'));
                }
            } catch (e) {
                console.error('Error parsing response:', e, 'Response:', response);
                toastr.error('Error parsing response: ' + e.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Upload error:', error);
            toastr.error('Error upload: ' + error);
        }
    });
}

// Add manual click fallback for dropzone
function addDropzoneClickFallback() {
    var dropzoneElement = document.getElementById('foto-dropzone');
    if (dropzoneElement && !dropzoneElement.hasClickHandler) {
        dropzoneElement.addEventListener('click', function(e) {
            console.log('Manual dropzone click triggered');
            
            // Only trigger if clicking on the dropzone area itself, not on previews
            if (e.target === this || e.target.classList.contains('dz-message') || 
                e.target.closest('.dz-message')) {
                
                // Try to use the dropzone's file input
                if (myDropzone && myDropzone.hiddenFileInput) {
                    console.log('Using dropzone hidden input');
                    myDropzone.hiddenFileInput.click();
                } else {
                    // Fallback: create a temporary file input
                    console.log('Creating fallback file input');
                    var input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.multiple = true;
                    input.style.display = 'none';
                    
                    input.onchange = function() {
                        if (this.files && myDropzone) {
                            for (var i = 0; i < this.files.length; i++) {
                                myDropzone.addFile(this.files[i]);
                            }
                        }
                        document.body.removeChild(input);
                    };
                    
                    document.body.appendChild(input);
                    input.click();
                }
            }
        });
        
        dropzoneElement.hasClickHandler = true;
        console.log('Manual click handler added to dropzone');
    }
}

// Reply to comment function
function replyToComment(commentId) {
    // Set parent comment ID for reply
    $('#comment-parent-id').val(commentId);
    $('#modal-comment .modal-title').text('Balas Komentar');
    $('#modal-comment').modal('show');
}

// Delete uploaded photo
function deleteUploadedPhoto(photoId) {
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
                url: '<?= base_url('pelaporan/delete_foto_progress') ?>',
                type: 'POST',
                data: {
                    id: photoId,
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        toastr.success('Foto berhasil dihapus');
                        loadUploadedPhotos(); // Reload photos
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
}

// Function to open location in Google Maps with Street View
function openInGoogleMaps(lat, lng, address) {
    // Create Google Maps URL that will open at the coordinates
    // Using the standard Google Maps URL format for direct coordinate access
    var zoom = 18; // Close zoom level
    var googleMapsUrl = `https://www.google.com/maps/@${lat},${lng},${zoom}z?entry=ttu`;

    // Alternative: Use search query format which is more reliable
    // var googleMapsUrl = `https://maps.google.com/maps?q=${lat},${lng}&ll=${lat},${lng}&z=${zoom}`;

    console.log('Opening Google Maps at coordinates:', lat, lng);
    console.log('Google Maps URL:', googleMapsUrl);

    // Open in new tab/window with security attributes
    var newWindow = window.open(googleMapsUrl, '_blank', 'noopener,noreferrer,width=1200,height=800');

    // Check if popup was blocked
    if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
        if (typeof toastr !== 'undefined') {
            toastr.warning('Popup diblokir browser. Silakan izinkan popup untuk situs ini atau klik link secara manual.');
        } else {
            alert('Popup diblokir browser. Silakan izinkan popup untuk situs ini.');
        }
        return;
    }

    // Optional: Show success message
    if (typeof toastr !== 'undefined') {
        toastr.success('Membuka lokasi di Google Maps...');
    } else {
        console.log('Opening location in Google Maps...');
    }
}

// Function to open Street View in Google Maps
function openStreetView(lat, lng) {
    // Create Google Street View URL
    // Using the embed format or direct street view URL
    var streetViewUrl = `https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=${lat},${lng}&heading=0&pitch=0&fov=80`;

    // Alternative URL format for Street View
    // var streetViewUrl = `https://maps.google.com/maps?q=&layer=c&cbll=${lat},${lng}&cbp=11,0,0,0,0`;

    console.log('Opening Street View at coordinates:', lat, lng);
    console.log('Street View URL:', streetViewUrl);

    // Open in new tab/window with security attributes
    var newWindow = window.open(streetViewUrl, '_blank', 'noopener,noreferrer,width=1200,height=800');

    // Check if popup was blocked
    if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
        if (typeof toastr !== 'undefined') {
            toastr.warning('Popup diblokir browser. Silakan izinkan popup untuk situs ini.');
        } else {
            alert('Popup diblokir browser. Silakan izinkan popup untuk situs ini.');
        }
        return;
    }

    // Optional: Show success message
    if (typeof toastr !== 'undefined') {
        toastr.info('Membuka Street View...');
    } else {
        console.log('Opening Street View...');
    }
}
</script>

<style>
/* Enhanced Timeline Styles */
.timeline-container {
    position: relative;
    padding: 20px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Main timeline line */
.timeline-container:before {
    content: '';
    position: absolute;
    left: 30px;
    top: 20px;
    bottom: 20px;
    width: 4px;
    background: linear-gradient(to bottom, 
        #e74c3c 0%, 
        #e74c3c 25%, 
        #3498db 25%, 
        #3498db 50%, 
        #f39c12 50%, 
        #f39c12 75%, 
        #27ae60 75%, 
        #27ae60 100%);
    border-radius: 2px;
    z-index: 1;
}

.timeline-item-custom {
    position: relative;
    margin-bottom: 25px;
    padding-left: 80px;
    animation: fadeInUp 0.6s ease-out;
}

.timeline-item-custom:last-child {
    margin-bottom: 0;
}

/* Timeline Icons - Enhanced */
.timeline-icon {
    position: absolute;
    left: 18px;
    top: 15px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 3;
    border: 3px solid white;
    transition: all 0.3s ease;
}

.timeline-icon:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.timeline-icon i {
    color: white;
    font-size: 10px;
    font-weight: bold;
}

/* Timeline Content - Modern Card Design */
.timeline-content {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    position: relative;
    transition: all 0.3s ease;
    overflow: hidden;
}

.timeline-content:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2980b9);
}

.timeline-content:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

/* Speech bubble effect */
.timeline-content:after {
    content: '';
    position: absolute;
    left: -10px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-right: 10px solid white;
    z-index: 2;
}

/* Timeline Header */
.timeline-header {
    margin-bottom: 15px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f8f9fa;
}

.timeline-header h5 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 16px;
    line-height: 1.4;
}

.timeline-header .label {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 3px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Timeline Description */
.timeline-description {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 15px;
    border-radius: 12px;
    border-left: 4px solid #3498db;
    margin: 15px 0;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    position: relative;
}

.timeline-description:before {
    content: '\f10d';
    font-family: 'FontAwesome';
    position: absolute;
    top: 15px;
    right: 15px;
    color: #bdc3c7;
    font-size: 14px;
}

.timeline-description p {
    margin: 0;
    line-height: 1.6;
    color: #34495e;
}

/* Timeline Footer */
.timeline-footer {
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.timeline-footer small {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

.timeline-footer small i {
    color: #95a5a6;
}

.timeline-footer strong {
    color: #2c3e50;
}

/* Time difference badge */
.time-diff-badge {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
}

/* Enhanced Progress Bar */
.timeline-progress {
    margin-top: 30px;
    padding: 25px;
    background: linear-gradient(135deg, #ecf0f1 0%, #d5dbdb 100%);
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.timeline-progress h5 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.timeline-progress h5 i {
    color: #3498db;
}

.timeline-progress .progress {
    height: 12px;
    border-radius: 10px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    background: #ecf0f1;
    overflow: hidden;
    position: relative;
}

.timeline-progress .progress-bar {
    border-radius: 10px;
    transition: width 1s ease-in-out;
    position: relative;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.timeline-progress .progress-bar:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.2) 75%, transparent 75%);
    background-size: 20px 20px;
    animation: progress-animation 2s linear infinite;
}

@keyframes progress-animation {
    0% { background-position: 0 0; }
    100% { background-position: 20px 0; }
}

/* Status-specific colors for progress bar */
.progress-bar-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

.progress-bar-info {
    background: linear-gradient(135deg, #3498db, #2980b9);
}

.progress-bar-warning {
    background: linear-gradient(135deg, #f39c12, #e67e22);
}

.progress-bar-success {
    background: linear-gradient(135deg, #27ae60, #229954);
}

/* Enhanced Dropzone Styles */
.dropzone {
    border: 2px dashed #0087F7;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    text-align: center;
    padding: 40px 20px;
    margin: 20px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropzone:hover {
    border-color: #0056b3;
    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 135, 247, 0.15);
}

.dropzone.dz-drag-hover {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #f8fff9 100%);
    transform: scale(1.02);
    box-shadow: 0 8px 30px rgba(40, 167, 69, 0.2);
}

.dropzone .dz-message {
    text-align: center;
    margin: 0;
    color: #495057;
    font-weight: 500;
    pointer-events: none;
    width: 100%;
}

.dropzone .dz-message i {
    color: #0087F7;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.dropzone:hover .dz-message i {
    color: #0056b3;
    transform: scale(1.1);
}

.dropzone .dz-message strong {
    display: block;
    font-size: 16px;
    margin: 10px 0 5px;
    color: #2c3e50;
}

.dropzone .dz-message small {
    display: block;
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

/* Dropzone preview styling */
.dropzone .dz-preview {
    position: relative;
    display: inline-block;
    width: 120px;
    margin: 10px;
}

.dropzone .dz-preview .dz-image {
    border-radius: 8px;
    overflow: hidden;
    width: 120px;
    height: 120px;
    position: relative;
    display: block;
    z-index: 10;
}

.dropzone .dz-preview .dz-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dropzone .dz-preview .dz-details {
    opacity: 1;
    font-size: 11px;
    min-width: 100%;
    max-width: 100%;
    padding: 2em 1em;
    text-align: center;
    color: rgba(0, 0, 0, 0.9);
    line-height: 150%;
}

.dropzone .dz-preview .dz-progress {
    opacity: 1;
    z-index: 1000;
    pointer-events: none;
    position: absolute;
    height: 16px;
    left: 50%;
    top: 50%;
    margin-top: -8px;
    width: 80px;
    margin-left: -40px;
    background: rgba(255, 255, 255, 0.9);
    transform: scale(1);
    border-radius: 8px;
}

.dropzone .dz-preview .dz-progress .dz-upload {
    background: #0087F7;
    background: linear-gradient(135deg, #0087F7, #0056b3);
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 0;
    transition: width 300ms ease-in-out;
    border-radius: 8px;
}

.dropzone .dz-preview .dz-error-message {
    position: absolute;
    top: -5px;
    left: -20px;
    width: 140px;
    background: #dc3545;
    color: white;
    padding: 8px 10px;
    border-radius: 4px;
    font-size: 11px;
    z-index: 1001;
}

.dropzone .dz-preview .dz-success-mark,
.dropzone .dz-preview .dz-error-mark {
    position: absolute;
    pointer-events: none;
    opacity: 0;
    z-index: 500;
    top: 50%;
    left: 50%;
    margin-left: -27px;
    margin-top: -27px;
    transform: scale(0.5);
    transition: all 0.3s ease;
}

.dropzone .dz-preview.dz-success .dz-success-mark {
    opacity: 1;
    transform: scale(1);
}

.dropzone .dz-preview.dz-error .dz-error-mark {
    opacity: 1;
    transform: scale(1);
}

.dropzone .dz-preview .dz-remove {
    font-size: 11px;
    text-align: center;
    display: block;
    cursor: pointer;
    border: none;
    background: #dc3545;
    color: white;
    padding: 4px 8px;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 5px;
    transition: all 0.3s ease;
}

.dropzone .dz-preview .dz-remove:hover {
    background: #c82333;
    transform: translateY(-1px);
}

/* Active/clicked state */
.dropzone:active {
    transform: translateY(0);
    box-shadow: 0 4px 15px rgba(0, 135, 247, 0.2);
}

/* Ensure the dropzone is always clickable */
.dropzone, .dropzone * {
    pointer-events: auto !important;
}

.dropzone .dz-message,
.dropzone .dz-message * {
    pointer-events: none !important;
}
.rating-input {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 5px;
}

.star {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: all 0.2s ease;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.star:hover {
    transform: scale(1.1);
    color: #f39c12;
}

.rating-display {
    margin: 8px 0;
}

.rating-summary {
    background: linear-gradient(135deg, #ecf0f1 0%, #d5dbdb 100%);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-top: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Enhanced Comment Styles */
.comment-item {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.comment-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.comment-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.comment-body {
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #3498db;
    margin: 15px 0;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

.comment-actions {
    margin-top: 15px;
    text-align: right;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

/* Enhanced Map Styles */
.map-container {
    border: 3px solid #ddd;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    transition: all 0.3s ease;
}

.map-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Enhanced Modal Styles */
.modal-content {
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-radius: 12px 12px 0 0;
    border-bottom: none;
}

.modal-header .close {
    color: white;
    opacity: 0.8;
}

.modal-header .close:hover {
    opacity: 1;
}

/* Enhanced Form Styles */
.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Enhanced Button Styles */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

/* Enhanced Progress Bar */
.progress {
    border-radius: 10px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}

/* Animation for new content */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.timeline-item-custom {
    animation: fadeInUp 0.6s ease-out;
}

.comment-item {
    animation: fadeInUp 0.6s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline-container {
        padding: 15px 10px;
        margin: 0 -10px;
    }
    
    .timeline-container:before {
        left: 20px;
    }
    
    .timeline-item-custom {
        padding-left: 55px;
        margin-bottom: 20px;
    }
    
    .timeline-icon {
        left: 8px;
        width: 24px;
        height: 24px;
    }
    
    .timeline-content {
        padding: 15px;
        border-radius: 12px;
    }
    
    .timeline-content:after {
        left: -8px;
        border-right-width: 8px;
    }
    
    .timeline-header h5 {
        font-size: 14px;
        line-height: 1.3;
    }
    
    .timeline-header .label {
        font-size: 10px;
        padding: 3px 6px;
    }
    
    .timeline-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .timeline-progress {
        padding: 20px 15px;
        margin: 20px -10px 0;
    }
    
    .star {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .timeline-content {
        padding: 12px;
    }
    
    .timeline-description {
        padding: 12px;
        margin: 10px 0;
    }
    
    .timeline-progress h5 {
        font-size: 14px;
    }
}

/* Enhanced Hover Effects */
.timeline-item-custom:hover .timeline-icon {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
}

/* Loading Animation */
.timeline-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
    color: #7f8c8d;
}

.timeline-loading i {
    animation: spin 1s linear infinite;
    margin-right: 10px;
    font-size: 18px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Empty State */
.timeline-empty {
    text-align: center;
    padding: 60px 20px;
    color: #95a5a6;
}

.timeline-empty i {
    font-size: 48px;
    margin-bottom: 20px;
    color: #bdc3c7;
}

.timeline-empty h4 {
    color: #7f8c8d;
    margin-bottom: 10px;
    font-weight: 500;
}

.timeline-empty p {
    color: #95a5a6;
    font-size: 14px;
    max-width: 300px;
    margin: 0 auto;
    line-height: 1.5;
}

/* Status Badge Enhancements */
.label {
    position: relative;
    overflow: hidden;
}

.label:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.label:hover:before {
    left: 100%;
}

/* Smooth Scrolling for Timeline */
.timeline-container {
    scroll-behavior: smooth;
}

/* Focus States for Accessibility */
.timeline-content:focus {
    outline: 3px solid #3498db;
    outline-offset: 2px;
}

.timeline-icon:focus {
    outline: 2px solid #3498db;
    outline-offset: 2px;
}

/* Print Styles */
@media print {
    .timeline-container {
        box-shadow: none;
        background: white;
    }
    
    .timeline-content {
        box-shadow: none;
        border: 1px solid #ddd;
        break-inside: avoid;
    }
    
    .timeline-icon {
        box-shadow: none;
    }
    
    .timeline-progress {
        box-shadow: none;
        background: #f8f9fa;
    }
}
</style>
