<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    var url = '<?= base_url("penyewaan_blok/data") ?>';
    var table;
    var formDataBackup = {}; // Store form data temporarily

    // Function to reset to step 1 (block selection)
    window.resetToStepBlok = function() {
        $('.step-blok').show();
        $('.step-unit').hide();
        $('.btn-save').show();
        $('.btn-cancel').show();
        $('#btn-back-to-blok').hide();
        $('.unit-info').hide();
    };

    // Function to show step 2 (unit selection)
    window.showStepUnit = function() {
        console.log('showStepUnit called');
        $('.step-blok').hide();
        $('.step-unit').show();
        $('.btn-save').hide();
        $('.btn-cancel').hide();
        $('#btn-back-to-blok').show();
        console.log('Step-unit visibility:', $('.step-unit').is(':visible'));
    };

    // Function to backup form data
    window.backupFormData = function() {
        formDataBackup = {
            id: $('#id').val(),
            pedagang_id: $('#pedagang_id').val(),
            pasar_id: $('#pasar_id').val(),
            jenis_id: $('#jenis_id').val(), // Changed from pasar_jenis_id to jenis_id
            tanggal_mulai: $('#tanggal_mulai').val(),
            tanggal_selesai: $('#tanggal_selesai').val(),
            biaya_sewa: $('#biaya_sewa').val(),
            keterangan: $('#keterangan').val(),
            // Also backup select2 text values
            pedagang_text: $('#pedagang_id option:selected').text(),
            pasar_text: $('#pasar_id option:selected').text(),
            jenis_text: $('#jenis_id option:selected').text()
        };
        console.log('backupFormData:', formDataBackup);
    };

    // Function to restore form data
    window.restoreFormData = function() {
        console.log('restoreFormData called with:', formDataBackup);
        if (formDataBackup.id) $('#id').val(formDataBackup.id);
        if (formDataBackup.pedagang_id) $('#pedagang_id').val(formDataBackup.pedagang_id).trigger('change');
        if (formDataBackup.pasar_id) $('#pasar_id').val(formDataBackup.pasar_id).trigger('change');
        if (formDataBackup.jenis_id) $('#jenis_id').val(formDataBackup.jenis_id).trigger('change'); // Changed from pasar_jenis_id to jenis_id
        if (formDataBackup.tanggal_mulai) $('#tanggal_mulai').val(formDataBackup.tanggal_mulai);
        if (formDataBackup.tanggal_selesai) $('#tanggal_selesai').val(formDataBackup.tanggal_selesai);
        if (formDataBackup.biaya_sewa) $('#biaya_sewa').val(formDataBackup.biaya_sewa);
        if (formDataBackup.keterangan) $('#keterangan').val(formDataBackup.keterangan);
        
        // Wait a bit for select2 to update
        setTimeout(function() {
            console.log('Form values after restore - pasar_id:', $('#pasar_id').val(), 'jenis_id:', $('#jenis_id').val());
        }, 500);
    };

    // Load functions
    window.loadPasarOptions = function() {
        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_pasar_options') ?>',
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                $('#pasar_id').html(data);
            }
        });
    };

    window.loadPedagangOptions = function() {
        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_pedagang_options') ?>',
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                $('#pedagang_id').html(data);
            }
        });
    };

    window.loadJenisByPasar = function(pasar_id) {
        console.log('loadJenisByPasar called with pasar_id:', pasar_id);
        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_jenis_by_pasar') ?>',
            type: 'POST',
            data: { pasar_id: pasar_id },
            dataType: 'html',
            success: function(data) {
                // Don't update if we're in the middle of unit selection
                if ($('.step-unit').is(':visible')) {
                    console.log('Ignoring jenis update during unit selection');
                    return;
                }
                $('#jenis_id').html(data);
                console.log('jenis options updated');
            }
        });
    };

    window.loadDenahBlocks = function(jenis_id, tanggal_mulai, tanggal_selesai) {
        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_denah_blocks') ?>',
            type: 'POST',
            data: {
                jenis_id: jenis_id,
                tanggal_mulai: tanggal_mulai,
                tanggal_selesai: tanggal_selesai
            },
            dataType: 'json',
            success: function(response) {
                if (response.status && response.data) {
                    renderDenah(response.data, response.pasar_info);
                } else {
                    showDenahPlaceholder('Tidak ada blok tersedia untuk periode ini');
                }
            },
            error: function(xhr, status, error) {
                showDenahPlaceholder('Error memuat denah. Silakan coba lagi.');
                showAlert('danger', 'Gagal memuat denah blok pasar. Silakan coba lagi.');
            }
        });
    };

    window.renderDenah = function(blocks, pasarInfo) {
        // Always target modal since denah is only used in modal
        var $denahContainer = $('#modal-form #denah-container');
        var $denahPlaceholder = $('#modal-form #denah-placeholder');
        var $denahTitle = $('#modal-form #denah-title');
        var $denahGrid = $('#modal-form #denah-grid');

        // Ensure modal is visible before rendering
        if (!$('#modal-form').is(':visible')) {
            setTimeout(function() {
                renderDenah(blocks, pasarInfo);
            }, 200);
            return;
        }

        $denahContainer.show();
        $denahPlaceholder.hide();

        if (pasarInfo) {
            $denahTitle.text('Denah ' + pasarInfo.pasar_nama + ' - ' + pasarInfo.pasar_jenis_nama);
        }

        $denahGrid.empty();

        if (blocks && blocks.length > 0) {
            // Get modal container dimensions
            var modalContent = $denahContainer.closest('.modal-content');
            var availableWidth = modalContent.width() - 80; // Subtract padding and margins
            var availableHeight = 400; // Fixed height for better UX

            // Calculate original denah dimensions from block positions
            var minX = Math.min.apply(Math, blocks.map(function(block) { return block.pasar_blok_posisi_x || 0; }));
            var maxX = Math.max.apply(Math, blocks.map(function(block) { return block.pasar_blok_posisi_x || 0; }));
            var minY = Math.min.apply(Math, blocks.map(function(block) { return block.pasar_blok_posisi_y || 0; }));
            var maxY = Math.max.apply(Math, blocks.map(function(block) { return block.pasar_blok_posisi_y || 0; }));

            // Calculate original denah size (add block size + margin)
            var originalWidth = (maxX - minX) + 120; // 120 = block width + margin
            var originalHeight = (maxY - minY) + 120; // 120 = block height + margin

            // Calculate scale factor to fit in available space
            var scaleX = availableWidth / originalWidth;
            var scaleY = availableHeight / originalHeight;
            var scale = Math.min(scaleX, scaleY, 1); // Don't scale up, only down

            // Apply minimum scale to ensure readability
            scale = Math.max(scale, 0.4);

            // Calculate final grid dimensions
            var gridWidth = Math.min(originalWidth * scale, availableWidth);
            var gridHeight = Math.min(originalHeight * scale, availableHeight);

            // Set grid size
            $denahGrid.css({
                'width': gridWidth + 'px',
                'height': gridHeight + 'px',
                'position': 'relative',
                'max-width': '100%',
                'margin': '0 auto' // Center the grid
            });

            // Render blocks with scaled positions
            blocks.forEach(function(block) {
                var blockElement = $('<div class="denah-block"></div>');
                blockElement.attr('data-id', block.pasar_blok_id);
                blockElement.attr('data-nama', block.pasar_blok_nama);
                blockElement.attr('data-harga', block.pasar_blok_harga_sewa);
                blockElement.attr('data-luas', block.pasar_blok_luas);

                // Calculate scaled position relative to minimum position
                var posX = ((block.pasar_blok_posisi_x || 0) - minX) * scale + 10;
                var posY = ((block.pasar_blok_posisi_y || 0) - minY) * scale + 10;

                // Calculate scaled block size
                var blockSize = Math.max(60 * scale, 40); // Minimum 40px, scaled from 60px

                blockElement.css({
                    'left': posX + 'px',
                    'top': posY + 'px',
                    'width': blockSize + 'px',
                    'height': blockSize + 'px',
                    'position': 'absolute',
                    'font-size': Math.max(10 * scale, 8) + 'px' // Scale font size too
                });

                // Set block status based on available units
                if (block.has_available_units) {
                    blockElement.addClass('available');
                    // Add indicator for units
                    blockElement.append('<div class="unit-indicator"><i class="fa fa-th"></i></div>');
                } else {
                    blockElement.addClass('occupied');
                }

                // Add block name and number
                var blockText = block.pasar_blok_nama;
                if (block.pasar_blok_nomor) {
                    blockText += ' - ' + block.pasar_blok_nomor;
                }
                
                blockElement.text(blockText);

                // Add info tooltip
                var infoElement = $('<div class="denah-block-info"></div>');
                infoElement.text('Klik untuk melihat unit');
                blockElement.append(infoElement);

                // Add click handler for blocks with available units
                if (block.has_available_units) {
                    blockElement.click(function() {
                        showUnitsModal(block);
                    });
                }

                $denahGrid.append(blockElement);
            });

            // Add scale info
            if (scale < 1) {
                var scaleInfo = $('<div class="denah-scale-info"></div>');
                scaleInfo.html('<small><i class="fa fa-info-circle"></i> Denah diperkecil ' + Math.round(scale * 100) + '% agar sesuai dengan modal</small>');
                scaleInfo.css({
                    'position': 'absolute',
                    'top': '5px',
                    'right': '10px',
                    'background': 'rgba(0,0,0,0.7)',
                    'color': 'white',
                    'padding': '3px 8px',
                    'border-radius': '3px',
                    'font-size': '11px',
                    'z-index': '100'
                });
                $denahGrid.append(scaleInfo);
            }

        } else {
            $denahGrid.html('<div class="denah-placeholder" style="text-align: center; padding: 40px; color: #666;">Tidak ada blok untuk ditampilkan</div>');
        }
    };

    // New function to show units when block is clicked (using two-step in one modal)
    window.showUnitsModal = function(block) {
        var tanggal_mulai = $('#tanggal_mulai').val();
        var tanggal_selesai = $('#tanggal_selesai').val();
        
        if (!tanggal_mulai || !tanggal_selesai) {
            showAlert('danger', 'Isi tanggal mulai dan selesai terlebih dahulu');
            return;
        }
        
        // Backup form data before switching to step 2
        backupFormData();
        
        // Update block info in step 2
        var blockText = block.pasar_blok_nama;
        if (block.pasar_blok_nomor) {
            blockText += ' - ' + block.pasar_blok_nomor;
        }
        $('#selected-blok-info').text('Blok: ' + blockText);
        
        // Store selected block info
        window.selectedBlock = block;
        
        // Load units for this block
        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_denah_units') ?>',
            type: 'POST',
            data: {
                blok_id: block.pasar_blok_id,
                tanggal_mulai: tanggal_mulai,
                tanggal_selesai: tanggal_selesai
            },
            dataType: 'json',
            success: function(response) {
                console.log('get_denah_units response:', response);
                if (response.status && response.data) {
                    console.log('Rendering units in step 2...');
                    renderUnitsInStep2(response.data);
                    
                    // Switch to step 2 (unit selection)
                    console.log('Switching to step 2...');
                    showStepUnit();
                } else {
                    showAlert('danger', response.message || 'Gagal memuat data unit');
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Terjadi kesalahan saat memuat data unit');
            }
        });
    };

    // Function to render units in step 2
    window.renderUnitsInStep2 = function(data) {
        console.log('renderUnitsInStep2 called with data:', data);
        var $denahGrid = $('#denah-unit-grid');
        console.log('Found denah-unit-grid element:', $denahGrid.length > 0);
        $denahGrid.empty();
        
        if (!data.units || data.units.length === 0) {
            console.log('No units found or units array is empty');
            $denahGrid.html('<div class="denah-placeholder" style="text-align: center; padding: 40px; color: #666;">Tidak ada unit untuk ditampilkan</div>');
            return;
        }
        
        console.log('Found', data.units.length, 'units to render');
        
        // Use a simpler grid layout with flexbox
        $denahGrid.css({
            'display': 'flex',
            'flex-wrap': 'wrap',
            'justify-content': 'flex-start',
            'align-items': 'flex-start',
            'gap': '10px',
            'padding': '10px',
            'width': '100%',
            'height': 'auto',
            'min-height': '200px',
            'max-height': '400px',
            'overflow-y': 'auto',
            'position': 'relative'
        });
        
        // Render units
        data.units.forEach(function(unit, index) {
            var unitElement = $('<div class="denah-block unit-block"></div>');
            unitElement.attr('data-id', unit.pasar_unit_id);
            unitElement.attr('data-nomor', unit.pasar_unit_nomor);
            unitElement.attr('data-harga', unit.pasar_unit_harga_sewa);
            unitElement.attr('data-luas', unit.pasar_unit_luas);
            unitElement.attr('data-blok-id', data.block.pasar_blok_id);
            
            // Use flexbox-friendly styling
            unitElement.css({
                'width': '70px',
                'height': '70px',
                'flex': '0 0 auto',
                'margin': '0',
                'position': 'relative',
                'font-size': '11px',
                'text-align': 'center',
                'line-height': '70px'
            });
            
            // Set unit status
            if (unit.is_available) {
                unitElement.addClass('available');
            } else {
                unitElement.addClass('occupied');
            }
            
            // Add unit number and block name
            var unitText = unit.pasar_unit_nomor;
            if (data.block && data.block.pasar_blok_nama) {
                unitText = data.block.pasar_blok_nama + '-' + unitText;
            }
            unitElement.text(unitText);
            
            // Add info tooltip
            var infoElement = $('<div class="denah-block-info"></div>');
            infoElement.text('Luas: ' + (unit.pasar_unit_luas || 0) + ' m² | Harga: Rp ' + parseInt(unit.pasar_unit_harga_sewa || 0).toLocaleString('id-ID'));
            unitElement.append(infoElement);
            
            // Add click handler for available units
            if (unit.is_available) {
                unitElement.click(function() {
                    selectUnitInStep2($(this));
                });
            }
            
            $denahGrid.append(unitElement);
        });
    };

    // Function to select unit in step 2
    window.selectUnitInStep2 = function(unitElement) {
        var unitId = unitElement.data('id');
        var unitNomor = unitElement.data('nomor');
        var unitHarga = unitElement.data('harga');
        var blokId = unitElement.data('blok-id');
        
        console.log('selectUnitInStep2 called with unitId:', unitId, 'unitNomor:', unitNomor);
        
        // Update unit info display
        $('#selected-unit-text').text(unitNomor + ' (Rp ' + parseInt(unitHarga || 0).toLocaleString('id-ID') + ')');
        $('.unit-info').show();
        
        // Set form values BEFORE restoring data to avoid overwriting
        $('#pasar_unit_id').val(unitId);
        $('#pasar_blok_id').val(blokId);
        
        // Store the unit harga to set after restore (to avoid being overwritten)
        var unitHargaToSet = unitHarga;
        
        // Restore form data first
        restoreFormData();
        
        // Then set unit-specific values (including harga_sewa)
        setTimeout(function() {
            $('#biaya_sewa').val(unitHargaToSet);
            console.log('Final form values - pasar_id:', $('#pasar_id').val(), 'jenis_id:', $('#jenis_id').val(), 'biaya_sewa:', $('#biaya_sewa').val());
        }, 300);
        
        // Go back to step 1 (form)
        resetToStepBlok();
        
        // Show success message
        showAlert('success', 'Unit ' + unitNomor + ' dipilih');
    };

    window.showDenahPlaceholder = function(message) {
        // Check if we're in modal or main page
        var isModal = $('#modal-form').hasClass('in') || $('#modal-form').hasClass('show');

        if (isModal) {
            $('#modal-form #denah-container').hide();
            $('#modal-form #denah-placeholder').show().html('<i class="fa fa-info-circle"></i> ' + message);
        } else {
            $('#denah-container').hide();
            $('#denah-placeholder').show().html('<i class="fa fa-info-circle"></i> ' + message);
        }
    };

    window.loadBlokData = function(blok_id) {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_by_id') ?>',
            type: 'POST',
            data: { id: blok_id },
            dataType: 'json',
            success: function(response) {
                if (response.status && response.data) {
                    var data = response.data;

                    // Set pasar and jenis first
                    $('#pasar_id').val(data.pasar_id).trigger('change');
                    setTimeout(function() {
                        $('#jenis_id').val(data.pasar_jenis_id).trigger('change');

                        // Wait for jenis change to complete, then load denah
                        setTimeout(function() {
                            var tanggal_mulai = $('#tanggal_mulai').val();
                            var tanggal_selesai = $('#tanggal_selesai').val();

                            if (tanggal_mulai && tanggal_selesai) {
                                loadDenahBlocks(data.pasar_jenis_id, tanggal_mulai, tanggal_selesai);

                                // After denah is loaded, mark the block as selected
                                setTimeout(function() {
                                    $('#pasar_blok_id').val(blok_id);
                                    $('#modal-form .denah-block[data-id="' + blok_id + '"]').addClass('selected');
                                }, 800);
                            }
                        }, 600);
                    }, 500);
                }
            }
        });
    };

    window.adjustDenahSize = function() {
        var $denahGrid = $('#modal-form #denah-grid');
        var $denahContainer = $('#modal-form #denah-container');

        if ($denahGrid.length > 0 && $denahGrid.is(':visible') && $denahContainer.is(':visible')) {
            // Get current modal dimensions
            var modalContent = $denahContainer.closest('.modal-content');
            var availableWidth = modalContent.width() - 80;
            var currentGridWidth = $denahGrid.width();

            // Only adjust if there's a significant difference and we have blocks
            if (Math.abs(availableWidth - currentGridWidth) > 50 && $denahGrid.find('.denah-block').length > 0) {
                // Re-trigger denah rendering with current data
                var jenis_id = $('#jenis_id').val();
                var tanggal_mulai = $('#tanggal_mulai').val();
                var tanggal_selesai = $('#tanggal_selesai').val();

                if (jenis_id && tanggal_mulai && tanggal_selesai) {
                    loadDenahBlocks(jenis_id, tanggal_mulai, tanggal_selesai);
                }
            }
        }
    };

    window.resetForm = function() {
        console.log('resetForm called');
        $('#form-penyewaan')[0].reset();
        $('#id').val('');
        $('#pasar_unit_id').val('');
        $('.select2').val('').trigger('change');
        $('#jenis_id').empty().append('<option value="">Pilih Jenis Pasar</option>');
        $('#pasar_blok_id').val('');
        $('.denah-block').removeClass('selected');
        showDenahPlaceholder('Pilih jenis pasar dan isi tanggal mulai/selesai untuk melihat denah blok yang tersedia.');
        
        // Reset form data backup
        formDataBackup = {};
        
        // Reset to step 1
        resetToStepBlok();
        
        // Hide unit info
        $('.unit-info').hide();
        $('#selected-blok-info').text('Blok: -');
        $('#selected-unit-text').text('-');
        
        // Clear selected block
        window.selectedBlock = null;
    };

    window.formatDate = function(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear();
        return day + '/' + month + '/' + year;
    };

    window.formatDateTime = function(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear();
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
    };

    window.showAlert = function(type, message) {
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
    };

    // Initialize Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true,
        minimumResultsForSearch: 10 // Enable search for all select2
    });

    // Load initial data
    loadPasarOptions();
    loadPedagangOptions();

    // DataTable initialization
    var pedagangId = '<?= isset($pedagang_id) ? $pedagang_id : ''; ?>';
    table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('penyewaan_blok/ajax_data') ?>" + (pedagangId ? '/' + pedagangId : ''),
            "type": "POST",
            "data": function(d) {
                d.filter_status = $('#filter-status').val();
                // Jika ada pedagangId, kirim sebagai parameter POST juga
                if (pedagangId) {
                    d.pedagang_id = pedagangId;
                }
            },
            "data": function(d) {
                d.filter_status = $('#filter-status').val();
            },
            "error": function(xhr, error, code) {
                alert('Terjadi kesalahan saat memuat data. Silakan periksa koneksi atau hubungi administrator.');
            }
        },
        "columns": [
            {"data": "0", "orderable": false},
            {"data": "1"},
            <?php if (!isset($pedagang_id) || !$pedagang_id): ?>
            {"data": "2"},
            <?php endif; ?>
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '2' : '3'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '3' : '4'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '4' : '5'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '5' : '6'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '6' : '7'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '7' : '8'; ?>"},
            {"data": "<?php echo (isset($pedagang_id) && $pedagang_id) ? '8' : '9'; ?>", "orderable": false}
        ],
        "order": [[1, 'desc']],
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
    $('#filter-status').change(function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-status').val('').trigger('change');
        table.ajax.reload();
    });

    $('#btn-add').click(function() {
        resetForm();
        $('.modal-title').html('<i class="fa fa-plus"></i> Tambah Penyewaan Blok');

        // If we have pedagang_id from URL, pre-select it
        var pedagangId = '<?= isset($pedagang_id) ? $pedagang_id : ''; ?>';
        if (pedagangId) {
            $('#pedagang_id').val(pedagangId).trigger('change');
        }

        $('#modal-form').modal('show');
    });

    // Initialize Select2 for modal when shown
    $('#modal-form').on('shown.bs.modal', function() {
        // Re-initialize Select2 for modal elements
        $('#modal-form .select2').select2({
            placeholder: 'Pilih...',
            allowClear: true,
            minimumResultsForSearch: 10,
            dropdownParent: $('#modal-form') // Important for modal
        });

        // Adjust denah size when modal is shown
        setTimeout(function() {
            adjustDenahSize();
        }, 300);
    });

    // Adjust denah size on window resize with debounce
    var resizeTimeout;
    $(window).on('resize', function() {
        if ($('#modal-form').is(':visible')) {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                adjustDenahSize();
            }, 250);
        }
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '<?= base_url('penyewaan_blok/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;

                    $('#id').val(data.id);
                    $('#pedagang_id').val(data.pedagang_id).trigger('change');
                    $('#tanggal_sewa').val(data.tanggal_sewa);
                    $('#tanggal_mulai').val(data.tanggal_mulai);
                    $('#tanggal_selesai').val(data.tanggal_selesai);
                    $('#harga_sewa').val(data.harga_sewa);
                    $('#status').val(data.status).trigger('change');

                    $('.modal-title').html('<i class="fa fa-edit"></i> Edit Penyewaan Blok');

                    // Show modal first
                    $('#modal-form').modal('show');

                    // Load blok data after modal is shown
                    if (data.pasar_blok_id) {
                        setTimeout(function() {
                            loadBlokData(data.pasar_blok_id);
                        }, 500);
                    }
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
            url: '<?= base_url('penyewaan_blok/get_by_id') ?>',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#detail-pedagang').text(data.nama_lengkap || '-');
                    $('#detail-nik').text(data.nik || '-');
                    $('#detail-telepon').text(data.no_telpon || '-');
                    $('#detail-tanggal-sewa').text(formatDate(data.tanggal_sewa) || '-');
                    $('#detail-pasar').text(data.pasar_nama || '-');
                    $('#detail-jenis').text(data.pasar_jenis_nama || '-');
                    $('#detail-blok').text(data.pasar_blok_nama || '-');
                    $('#detail-periode').text((formatDate(data.tanggal_mulai) || '-') + ' - ' + (formatDate(data.tanggal_selesai) || '-'));
                    $('#detail-harga').text('Rp ' + (parseInt(data.harga_sewa || 0).toLocaleString('id-ID')));
                    $('#detail-created').text(formatDateTime(data.created_at) || '-');
                    $('#detail-updated').text(formatDateTime(data.updated_at) || '-');

                    // Status badge
                    var statusConfig = <?= json_encode($this->config->item('rental_status') ?: []); ?>;
                    var statusLabels = <?= json_encode($this->config->item('rental_status_labels') ?: []); ?>;
                    var statusText = statusConfig[data.status] || data.status;
                    var statusClass = statusLabels[data.status] || 'label-default';
                    $('#detail-status').html('<span class="label ' + statusClass + '">' + statusText + '</span>');

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
            text: 'Apakah Anda yakin ingin menghapus data penyewaan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('penyewaan_blok/delete') ?>',
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
    $('#form-penyewaan').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        var submitBtn = $('#form-penyewaan button[type="submit"]');
        var originalText = submitBtn.html();

        $.ajax({
            url: '<?= base_url('penyewaan_blok/save') ?>',
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

    // Modal events
    $('#modal-form').on('hidden.bs.modal', function() {
        console.log('Modal hidden - resetting form');
        resetForm();
    });

    $('#modal-form').on('shown.bs.modal', function() {
        console.log('Modal shown');
        // Reset denah when modal is shown
        showDenahPlaceholder('Pilih jenis pasar dan isi tanggal mulai/selesai untuk melihat denah blok yang tersedia.');
        // Reset to step 1
        resetToStepBlok();
    });

    $('#modal-form').on('show.bs.modal', function() {
        console.log('Modal showing');
        // Ensure modal elements are ready before initializing
        setTimeout(function() {
            showDenahPlaceholder('Pilih jenis pasar dan isi tanggal mulai/selesai untuk melihat denah blok yang tersedia.');
            // Reset to step 1
            resetToStepBlok();
        }, 100);
    });
    
    // Back to block selection button
    $(document).on('click', '#btn-back-to-blok', function() {
        resetToStepBlok();
    });

    // Pasar change event
    $('#pasar_id').change(function() {
        console.log('pasar_id changed to:', $(this).val());
        
        // Don't reset if we're in the middle of unit selection (step-unit is visible)
        if ($('.step-unit').is(':visible')) {
            console.log('Ignoring pasar_id change during unit selection');
            return;
        }
        
        var pasar_id = $(this).val();
        $('#jenis_id').empty().append('<option value="">Pilih Jenis Pasar</option>');
        $('#pasar_blok_id').val('');
        $('.denah-block').removeClass('selected');
        showDenahPlaceholder('Pilih jenis pasar dan isi tanggal mulai/selesai untuk melihat denah blok yang tersedia.');

        if (pasar_id) {
            loadJenisByPasar(pasar_id);
        }
    });

    // Jenis change event
    $('#jenis_id').change(function() {
        console.log('jenis_id changed to:', $(this).val());
        
        // Don't reset if we're in the middle of unit selection (step-unit is visible)
        if ($('.step-unit').is(':visible')) {
            console.log('Ignoring jenis_id change during unit selection');
            return;
        }
        
        var jenis_id = $(this).val();
        var tanggal_mulai = $('#tanggal_mulai').val();
        var tanggal_selesai = $('#tanggal_selesai').val();

        if (jenis_id) {
            if (tanggal_mulai && tanggal_selesai) {
                // Check if modal is visible, if not wait a bit
                if ($('#modal-form').is(':visible')) {
                    loadDenahBlocks(jenis_id, tanggal_mulai, tanggal_selesai);
                } else {
                    setTimeout(function() {
                        loadDenahBlocks(jenis_id, tanggal_mulai, tanggal_selesai);
                    }, 200);
                }
            } else {
                showDenahPlaceholder('Isi tanggal mulai dan selesai terlebih dahulu');
            }
        } else {
            showDenahPlaceholder('Pilih jenis pasar terlebih dahulu');
        }
    });

    // Date change events
    $('#tanggal_mulai, #tanggal_selesai').change(function() {
        var jenis_id = $('#jenis_id').val();
        var tanggal_mulai = $('#tanggal_mulai').val();
        var tanggal_selesai = $('#tanggal_selesai').val();

        // Check if both dates are filled
        if (tanggal_mulai && tanggal_selesai) {
            if (jenis_id) {
                // Check if modal is visible, if not wait a bit
                if ($('#modal-form').is(':visible')) {
                    loadDenahBlocks(jenis_id, tanggal_mulai, tanggal_selesai);
                } else {
                    setTimeout(function() {
                        loadDenahBlocks(jenis_id, tanggal_mulai, tanggal_selesai);
                    }, 200);
                }
            } else {
                showDenahPlaceholder('Pilih jenis pasar terlebih dahulu');
            }
        } else {
            showDenahPlaceholder('Isi tanggal mulai dan selesai terlebih dahulu');
        }
    });

    // Note: Harga sewa sekarang diisi otomatis saat klik blok di denah
});
</script>