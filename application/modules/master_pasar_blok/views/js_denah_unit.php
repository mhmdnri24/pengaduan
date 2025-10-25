<script>
$(document).ready(function() {
    var currentBlokId = null;
    
    // Event listener untuk klik blok
    $(document).on('click', '.blok-item', function(e) {
        if (e.ctrlKey || e.metaKey) {
            // Ctrl+Click untuk edit blok
            return;
        }
        
        e.preventDefault();
        var blokId = $(this).data('id');
        var blokNama = $(this).data('nama');
        
        currentBlokId = blokId;
        $('#blok-nama').text(blokNama);
        $('#unit-blok-id').val(blokId);
        $('#generate-blok-id').val(blokId);
        
        loadUnits(blokId);
        $('#modalDenahUnit').modal('show');
    });
    
    // Load units
    function loadUnits(blokId) {
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_units_by_blok') ?>',
            type: 'GET',
            data: { blok_id: blokId },
            dataType: 'json',
            success: function(data) {
                renderUnits(data);
                updateUnitStats(data);
            },
            error: function() {
                $('#units-grid').html('<div class="alert alert-warning">Gagal memuat data unit</div>');
            }
        });
    }
    
    // Render units
    function renderUnits(units) {
        var grid = $('#units-grid');
        grid.empty();
        
        if (units.length === 0) {
            grid.html('<div class="alert alert-info">Belum ada unit dalam blok ini</div>');
            return;
        }
        
        $.each(units, function(index, unit) {
            var statusClass = 'tersedia';
            switch (unit.pasar_unit_status) {
                case 'TERISI':
                    statusClass = 'terisi';
                    break;
                case 'MAINTENANCE':
                    statusClass = 'maintenance';
                    break;
            }
            
            var unitElement = $('<div class="unit-item ' + statusClass + '">')
                .attr('data-id', unit.pasar_unit_id)
                .html('<div class="unit-nomor">' + unit.pasar_unit_nomor + '</div>');
            
            // Add ukuran if available
            if (unit.pasar_unit_lebar && unit.pasar_unit_panjang) {
                unitElement.append('<div class="unit-ukuran">' + unit.pasar_unit_lebar + 'x' + unit.pasar_unit_panjang + 'm</div>');
            }
            
            // Add penyewa if terisi
            if (unit.pasar_unit_status === 'TERISI' && unit.pasar_unit_penyewa) {
                unitElement.append('<div class="unit-penyewa">' + unit.pasar_unit_penyewa + '</div>');
            }
            
            // Add tanggal if terisi
            if (unit.pasar_unit_status === 'TERISI' && unit.pasar_unit_tanggal_jatuh_tempo) {
                var tanggal = new Date(unit.pasar_unit_tanggal_jatuh_tempo);
                var formattedDate = tanggal.getDate() + '/' + (tanggal.getMonth() + 1) + '/' + tanggal.getFullYear();
                unitElement.append('<div class="unit-tanggal">Jatuh tempo: ' + formattedDate + '</div>');
            }
            
            // Add status badge
            unitElement.append('<div class="unit-status-badge">' + unit.pasar_unit_status + '</div>');
            
            grid.append(unitElement);
        });
    }
    
    // Update unit statistics
    function updateUnitStats(units) {
        var stats = {
            total: units.length,
            tersedia: 0,
            terisi: 0,
            maintenance: 0
        };
        
        $.each(units, function(index, unit) {
            stats[unit.pasar_unit_status.toLowerCase()]++;
        });
        
        $('#total-unit').text(stats.total);
        $('#unit-tersedia').text(stats.tersedia);
        $('#unit-terisi').text(stats.terisi);
        $('#unit-maintenance').text(stats.maintenance);
    }
    
    // Filter units
    $('#filter-unit-status').on('change', function() {
        var status = $(this).val();
        
        if (status === '') {
            $('.unit-item').show();
        } else {
            $('.unit-item').hide();
            $('.unit-item').each(function() {
                var unitStatus = $(this).find('.unit-status-badge').text();
                if (unitStatus === status) {
                    $(this).show();
                }
            });
        }
    });
    
    // Add unit
    $('#btn-add-unit').click(function() {
        resetUnitForm();
        $('#modal-unit-title').text('Tambah Unit');
        $('#modalUnit').modal('show');
    });
    
    // Generate units
    $('#btn-generate-units').click(function() {
        resetGenerateForm();
        $('#modalGenerateUnits').modal('show');
    });
    
    // Edit unit
    $(document).on('click', '.unit-item', function(e) {
        e.stopPropagation();
        var unitId = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/get_unit_by_id') ?>',
            type: 'GET',
            data: { id: unitId },
            dataType: 'json',
            success: function(unit) {
                populateUnitForm(unit);
                $('#modal-unit-title').text('Edit Unit');
                $('#modalUnit').modal('show');
            },
            error: function() {
                toastr.error('Gagal memuat data unit');
            }
        });
    });
    
    // Status change handler
    $('#unit-status').on('change', function() {
        var status = $(this).val();
        
        if (status === 'TERISI') {
            $('#field-penyewa, #field-tanggal').show();
            $('#unit-penyewa, #unit-tanggal-sewa, #unit-tanggal-jatuh-tempo').prop('required', true);
        } else {
            $('#field-penyewa, #field-tanggal').hide();
            $('#unit-penyewa, #unit-tanggal-sewa, #unit-tanggal-jatuh-tempo').prop('required', false);
            $('#unit-penyewa, #unit-tanggal-sewa, #unit-tanggal-jatuh-tempo').val('');
        }
    });
    
    // Calculate luas
    $('#unit-lebar, #unit-panjang').on('input', function() {
        var lebar = parseFloat($('#unit-lebar').val()) || 0;
        var panjang = parseFloat($('#unit-panjang').val()) || 0;
        var luas = lebar * panjang;
        $('#unit-luas').val(luas.toFixed(2));
    });
    
    // Submit unit form
    $('#formUnit').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/save_unit') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-simpan-unit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalUnit').modal('hide');
                    loadUnits(currentBlokId);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-simpan-unit').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });
    
    // Submit generate form
    $('#formGenerateUnits').submit(function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: '<?= base_url('master_pasar_blok/generate_units') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-generate').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalGenerateUnits').modal('hide');
                    loadUnits(currentBlokId);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem');
            },
            complete: function() {
                $('#btn-generate').prop('disabled', false).html('<i class="fa fa-magic"></i> Generate');
            }
        });
    });
    
    // Helper functions
    function resetUnitForm() {
        $('#formUnit')[0].reset();
        $('#unit-id').val('');
        $('#field-penyewa, #field-tanggal').hide();
        $('#unit-penyewa, #unit-tanggal-sewa, #unit-tanggal-jatuh-tempo').prop('required', false);
    }
    
    function resetGenerateForm() {
        $('#formGenerateUnits')[0].reset();
    }
    
    function populateUnitForm(unit) {
        $('#unit-id').val(unit.pasar_unit_id);
        $('#unit-nomor').val(unit.pasar_unit_nomor);
        $('#unit-lebar').val(unit.pasar_unit_lebar);
        $('#unit-panjang').val(unit.pasar_unit_panjang);
        $('#unit-luas').val(unit.pasar_unit_luas);
        $('#unit-harga').val(unit.pasar_unit_harga_sewa);
        $('#unit-status').val(unit.pasar_unit_status);
        $('#unit-penyewa').val(unit.pasar_unit_penyewa);
        $('#unit-tanggal-sewa').val(unit.pasar_unit_tanggal_sewa);
        $('#unit-tanggal-jatuh-tempo').val(unit.pasar_unit_tanggal_jatuh_tempo);
        $('#unit-keterangan').val(unit.pasar_unit_keterangan);
        
        // Trigger status change
        $('#unit-status').trigger('change');
    }
});
</script>