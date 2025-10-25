<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    // Initialize Select2 for filters
    $('#filter_pasar, #filter_jenis, #filter_status').select2({
        placeholder: function() {
            return $(this).data('placeholder') || 'Pilih...';
        },
        allowClear: true,
        width: '100%'
    });
    
    // Load initial data
    loadPasarOptions();
    loadJenisPasarOptions();
    loadStatistik();
    
    // Current view mode
    var currentView = 'denah'; // 'denah' or 'table'
    var table; // DataTable instance
    
    // Initialize Denah View (default)
    loadDenahData();
    
    // Toggle View Mode
    $('#btn-denah-view').click(function() {
        if (currentView !== 'denah') {
            currentView = 'denah';
            $('#denah-view').show();
            $('#table-view').hide();
            $('#btn-denah-view').removeClass('btn-default').addClass('btn-info');
            $('#btn-table-view').removeClass('btn-info').addClass('btn-default');
            loadDenahData();
        }
    });
    
    $('#btn-table-view').click(function() {
        if (currentView !== 'table') {
            currentView = 'table';
            $('#denah-view').hide();
            $('#table-view').show();
            $('#btn-denah-view').removeClass('btn-info').addClass('btn-default');
            $('#btn-table-view').removeClass('btn-default').addClass('btn-info');
            initDataTable();
        }
    });
    
    // Filter events
    $('#filter_pasar, #filter_jenis, #filter_status').on('change', function() {
        if (currentView === 'denah') {
            loadDenahData();
        } else {
            table.ajax.reload();
        }
    });

    // Reset filter
    $('#btn-reset').click(function() {
        $('#filter_pasar, #filter_jenis, #filter_status').val('').trigger('change');
        if (currentView === 'denah') {
            loadDenahData();
        } else {
            table.ajax.reload();
        }
    });

    // Cascade dropdown: When pasar is selected, load jenis options
    $(document).on('change', '#pasar_id', function() {
        var pasarId = $(this).val();
        if (pasarId) {
            loadJenisPasarOptionsByPasar(pasarId);
        } else {
            // Reset jenis dropdown
            $('#pasar_jenis_id').html('<option value="">Pilih Jenis Pasar</option>').trigger('change');
        }
    });

    // Modal events - reset form when modal is shown
    $('#modalPasarBlok').on('show.bs.modal', function() {
        // Only reset if not triggered by edit button
        if (!$('.btn-edit').hasClass('editing')) {
            resetForm();
            $('#modal-title').text('Tambah Blok Pasar');
        }

        // Reinitialize Select2 for modal compatibility
        setTimeout(function() {
            $('#pasar_id').select2({
                dropdownParent: $('#modalPasarBlok'),
                placeholder: 'Pilih Nama Pasar',
                allowClear: true,
                width: '100%'
            });

            $('#pasar_jenis_id').select2({
                dropdownParent: $('#modalPasarBlok'),
                placeholder: 'Pilih Jenis Pasar',
                allowClear: true,
                width: '100%'
            });

            $('#pasar_blok_status').select2({
                dropdownParent: $('#modalPasarBlok'),
                placeholder: 'Pilih Status',
                allowClear: false,
                width: '100%'
            });

            // Reload jenis pasar options if needed
            if ($('#pasar_jenis_id option').length <= 1) {
                loadJenisPasarOptions();
            }
        }, 100);
    });
    
    // Edit data
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var jenisId = $(this).data('jenis-id');
        var nama = $(this).data('nama');
        var nomor = $(this).data('nomor');
        var luas = $(this).data('luas');
        var status = $(this).data('status');
        var keterangan = $(this).data('keterangan');
        var x = $(this).data('x');
        var y = $(this).data('y');

        // Set editing flag to prevent form reset
        $('.btn-edit').addClass('editing');

        $('#modal-title').text('Edit Blok Pasar');
        $('#id').val(id);
        
        // Load jenis pasar data untuk mendapatkan pasar_id
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_jenis_pasar_by_id') ?>',
            type: 'GET',
            data: { id: jenisId },
            dataType: 'json',
            success: function(jenisData) {
                if (jenisData) {
                    $('#pasar_id').val(jenisData.pasar_id).trigger('change');
                    
                    // Load jenis options berdasarkan pasar yang dipilih
                    loadJenisPasarOptionsByPasar(jenisData.pasar_id, function() {
                        $('#pasar_jenis_id').val(jenisId).trigger('change');
                    });
                }
            }
        });
        
        $('#pasar_blok_nama').val(nama);
        $('#pasar_blok_nomor').val(nomor);
        $('#pasar_blok_luas').val(luas);
        $('#pasar_blok_status').val(status).trigger('change');
        $('#pasar_blok_keterangan').val(keterangan);
        $('#pasar_blok_posisi_x').val(x);
        $('#pasar_blok_posisi_y').val(y);

        $('#modalPasarBlok').modal('show');

        // Remove editing flag after modal is shown
        setTimeout(function() {
            $('.btn-edit').removeClass('editing');
        }, 100);
    });

    // Delete data
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        $('#id-hapus').val(id);
        $('#nama-hapus').text(nama);
        $('#modalHapus').modal('show');
    });

    // Form submit
    $('#formPasarBlok').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-simpan').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalPasarBlok').modal('hide');
                    if (currentView === 'denah') {
                        loadDenahData();
                    } else {
                        table.ajax.reload();
                    }
                    loadStatistik();
                    
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-simpan').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Konfirmasi hapus
    $('#btn-konfirm-hapus').click(function() {
        var id = $('#id-hapus').val();
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/delete') ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-konfirm-hapus').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menghapus...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalHapus').modal('hide');
                    if (currentView === 'denah') {
                        loadDenahData();
                    } else {
                        table.ajax.reload();
                    }
                    loadStatistik();
                    
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-konfirm-hapus').prop('disabled', false).html('<i class="fa fa-trash"></i> Hapus');
            }
        });
    });

    // Modal events
    $('#modalPasarBlok').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Functions
    function resetForm() {
        $('#formPasarBlok')[0].reset();
        $('#modal-title').text('Tambah Blok Pasar');
        $('#id').val('');

        // Properly reset Select2 instances
        $('#pasar_id').val(null).trigger('change');
        $('#pasar_jenis_id').val(null).trigger('change');
        $('#pasar_blok_status').val('TERSEDIA').trigger('change');
    }

    function loadJenisPasarOptions() {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_jenis_pasar_options') ?>',
            type: 'GET',
            success: function(data) {
                $('#pasar_jenis_id').html(data);
                // Trigger change to update Select2
                $('#pasar_jenis_id').trigger('change');
            },
            error: function() {
                console.log('Error loading jenis pasar options');
            }
        });
    }

    function loadJenisPasarOptionsByPasar(pasarId, callback) {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_jenis_pasar_options') ?>',
            type: 'GET',
            data: { pasar_id: pasarId },
            success: function(data) {
                $('#pasar_jenis_id').html(data);
                // Trigger change to update Select2
                $('#pasar_jenis_id').trigger('change');
                if (callback) callback();
            },
            error: function() {
                console.log('Error loading jenis pasar options by pasar');
            }
        });
    }

    function loadPasarOptions() {
        $.ajax({
            url: '<?= base_url('master_pasar/get_pasar_options') ?>',
            type: 'GET',
            success: function(data) {
                // Load untuk filter
                var filterOptions = '<option value="">-- Semua Pasar --</option>' + data.replace('<option value="">Pilih Pasar</option>', '');
                $('#filter_pasar').html(filterOptions);
                
                // Load untuk modal form
                $('#pasar_id').html(data);
            },
            error: function() {
                console.log('Error loading pasar options');
            }
        });
    }

    function loadStatistik() {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_statistik') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#total-blok').text(data.total_blok);
                $('#total-tersedia').text(data.total_tersedia);
                $('#total-terisi').text(data.total_terisi);
                $('#total-maintenance').text(data.total_maintenance);
            },
            error: function() {
                console.log('Error loading statistik');
            }
        });
    }

    function loadDenahData() {
        var filterParams = {
            filter_pasar: $('#filter_pasar').val(),
            filter_jenis: $('#filter_jenis').val(),
            filter_status: $('#filter_status').val()
        };
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_denah_data') ?>',
            type: 'GET',
            data: filterParams,
            dataType: 'json',
            success: function(data) {
                renderDenahGrid(data);
            },
            error: function() {
                console.log('Error loading denah data');
                $('#denah-grid').html('<div class="alert alert-warning">Gagal memuat data denah</div>');
            }
        });
    }

    function renderDenahGrid(data) {
        var grid = $('#denah-grid');
        grid.empty();
        
        if (data.length === 0) {
            grid.html('<div class="alert alert-info">Tidak ada data blok untuk ditampilkan</div>');
            return;
        }
        
        $.each(data, function(index, blok) {
            var statusClass = 'blok-tersedia';
            switch (blok.pasar_blok_status) {
                case 'TERISI':
                    statusClass = 'blok-terisi';
                    break;
                case 'MAINTENANCE':
                    statusClass = 'blok-maintenance';
                    break;
            }
            
            var blokElement = $('<div class="blok-item ' + statusClass + '" draggable="true">')
                .css({
                    left: blok.pasar_blok_posisi_x + 'px',
                    top: blok.pasar_blok_posisi_y + 'px'
                })
                .attr('data-id', blok.pasar_blok_id)
                .attr('data-jenis-id', blok.pasar_jenis_id)
                .attr('data-nama', blok.pasar_blok_nama)
                .attr('data-nomor', blok.pasar_blok_nomor)
                .attr('data-luas', blok.pasar_blok_luas)
                .attr('data-status', blok.pasar_blok_status)
                .attr('data-keterangan', blok.pasar_blok_keterangan || '')
                .attr('data-x', blok.pasar_blok_posisi_x)
                .attr('data-y', blok.pasar_blok_posisi_y)
                .html('<div class="blok-nama">' + blok.pasar_blok_nama + '</div>' +
                      '<div class="blok-info">' + blok.pasar_blok_status + '</div>');
            
            // Tooltip
            blokElement.attr('title',
                'Pasar: ' + blok.pasar_nama + '\n' +
                'Jenis: ' + blok.pasar_jenis_nama + '\n' +
                'Luas: ' + parseFloat(blok.pasar_blok_luas).toFixed(2) + ' m²\n' +
                'Status: ' + blok.pasar_blok_status
            );
            
            grid.append(blokElement);
        });
        
        // Initialize drag and drop
        initDragDrop();
        
        // Initialize tooltips
        $('[title]').tooltip();
    }

    function initDragDrop() {
        var draggedElement = null;
        var startX, startY;

        // Drag start
        $(document).on('dragstart', '.blok-item', function(e) {
            draggedElement = $(this);
            draggedElement.addClass('dragging');

            var rect = this.getBoundingClientRect();
            var containerRect = $('#denah-grid')[0].getBoundingClientRect();

            startX = e.originalEvent.clientX - rect.left;
            startY = e.originalEvent.clientY - rect.top;
        });

        // Drag end
        $(document).on('dragend', '.blok-item', function(e) {
            if (draggedElement) {
                draggedElement.removeClass('dragging');
                draggedElement = null;
            }
        });

        // Allow drop on grid
        $('#denah-grid').on('dragover', function(e) {
            e.preventDefault();
        });

        // Handle drop
        $('#denah-grid').on('drop', function(e) {
            e.preventDefault();

            if (!draggedElement) return;

            var containerRect = this.getBoundingClientRect();
            var newX = e.originalEvent.clientX - containerRect.left - startX;
            var newY = e.originalEvent.clientY - containerRect.top - startY;

            // Ensure minimum position
            newX = Math.max(0, newX);
            newY = Math.max(0, newY);

            // Update position visually
            draggedElement.css({
                left: newX + 'px',
                top: newY + 'px'
            });

            // Update position in database
            var blokId = draggedElement.data('id');
            updatePosisi(blokId, newX, newY);
        });

        // Click to edit (hanya untuk Ctrl+Click)
        $(document).on('click', '.blok-item', function(e) {
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                e.stopPropagation();
                
                // Ctrl+Click untuk edit
                var blokData = {
                    id: $(this).data('id'),
                    'jenis-id': $(this).data('jenis-id'),
                    nama: $(this).data('nama'),
                    nomor: $(this).data('nomor'),
                    luas: $(this).data('luas'),
                    status: $(this).data('status'),
                    keterangan: $(this).data('keterangan'),
                    x: $(this).data('x'),
                    y: $(this).data('y')
                };

                // Simulate edit button click
                var editBtn = $('<button class="btn-edit"></button>');
                $.each(blokData, function(key, value) {
                    editBtn.attr('data-' + key, value);
                });
                // Add editing class to prevent form reset
                editBtn.addClass('editing');
                editBtn.trigger('click');
            }
            // Regular click akan ditangani oleh js_denah_unit
        });

        // Right click to delete
        $(document).on('contextmenu', '.blok-item', function(e) {
            e.preventDefault();

            if (!confirm('Hapus blok ' + $(this).data('nama') + '?')) {
                return;
            }

            var deleteBtn = $('<button class="btn-delete"></button>');
            deleteBtn.attr('data-id', $(this).data('id'));
            deleteBtn.attr('data-nama', $(this).data('nama'));
            deleteBtn.trigger('click');
        });
    }

    function updatePosisi(id, x, y) {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/update_posisi') ?>',
            type: 'POST',
            data: {
                id: id,
                x: Math.round(x),
                y: Math.round(y)
            },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    // Update data attributes
                    var element = $('.blok-item[data-id="' + id + '"]');
                    element.attr('data-x', Math.round(x));
                    element.attr('data-y', Math.round(y));
                } else {
                    toastr.error('Gagal memperbarui posisi: ' + response.message);
                    // Reload denah to restore original position
                    loadDenahData();
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat memperbarui posisi');
                loadDenahData();
            }
        });
    }

    function initDataTable() {
        if (table) {
            table.destroy();
        }

        table = $('#table-pasar-blok').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url('master_pasar_blok/ajax_data') ?>',
                type: 'POST',
                data: function(d) {
                    d.filter_pasar = $('#filter_pasar').val();
                    d.filter_jenis = $('#filter_jenis').val();
                    d.filter_status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 0, orderable: false, searchable: false },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6, orderable: false, searchable: false },
                { data: 7, orderable: false, searchable: false },
                { data: 8, orderable: false, searchable: false },
                { data: 9, orderable: false, searchable: false }
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
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
            responsive: true,
            autoWidth: false
        });
    }
});
</script>
