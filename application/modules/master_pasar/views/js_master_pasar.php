<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();
    
    // Initialize Map
    var map;
    var marker;
    
    function initMap() {
        // Default koordinat Palembang
        var defaultLat = -2.9760285;
        var defaultLng = 104.7754794;
        
        map = L.map('map').setView([defaultLat, defaultLng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Event click pada map
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            
            // Update input koordinat
            $('#pasar_latitude').val(lat);
            $('#pasar_longitude').val(lng);
            
            // Hapus marker lama jika ada
            if (marker) {
                map.removeLayer(marker);
            }
            
            // Tambah marker baru
            marker = L.marker([lat, lng]).addTo(map);
        });
    }

    // DataTable
    var table = $('#table-pasar').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('master_pasar/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.filter_kecamatan = $('#filter_kecamatan').val();
                d.filter_kelurahan = $('#filter_kelurahan').val();
                d.filter_status = $('#filter_status').val();
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

    // Load kecamatan untuk Kota Palembang (16.73)
    loadKecamatanPalembang();

    // Filter events
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
        $('#filter_kecamatan, #filter_kelurahan, #filter_status').val(null).trigger('change');
        loadKecamatanPalembang();
    });

    // Form modal events
    $('#pasar_id_kecamatan').on('change', function() {
        var id_kecamatan = $(this).val();
        loadKelurahanByKecamatan(id_kecamatan, 'pasar_id_kelurahan');
    });

    // Modal events
    $('#modalPasar').on('shown.bs.modal', function() {
        // Initialize map when modal is shown
        setTimeout(function() {
            if (typeof map !== 'undefined') {
                map.invalidateSize();
            } else {
                initMap();
            }
        }, 100);
    });

    $('#modalPasar').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Form submit
    $('#formPasar').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('master_pasar/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalPasar').modal('hide');
                    table.ajax.reload();
                    loadStatistik();
                    
                    toastr.success(response.message, 'Berhasil');
                } else {
                    toastr.error(response.message, 'Gagal');
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem', 'Error');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var alamat = $(this).data('alamat');
        var deskripsi = $(this).data('deskripsi');
        var kecamatan = $(this).data('kecamatan');
        var kelurahan = $(this).data('kelurahan');
        var telepon = $(this).data('telepon');
        var latitude = $(this).data('latitude');
        var longitude = $(this).data('longitude');
        var status = $(this).data('status');
        
        $('#modal-title').text('Edit Pasar');
        $('#id').val(id);
        $('#pasar_nama').val(nama);
        $('#pasar_alamat').val(alamat);
        $('#pasar_deskripsi').val(deskripsi);
        $('#pasar_telepon').val(telepon);
        $('#pasar_latitude').val(latitude);
        $('#pasar_longitude').val(longitude);
        
        if (status == 1) {
            $('#pasar_status').prop('checked', true);
        } else {
            $('#pasar_status').prop('checked', false);
        }
        
        // Load kecamatan dan kelurahan
        loadKecamatanPalembang(function() {
            $('#pasar_id_kecamatan').val(kecamatan).trigger('change');
            
            if (kecamatan) {
                loadKelurahanByKecamatan(kecamatan, 'pasar_id_kelurahan', function() {
                    $('#pasar_id_kelurahan').val(kelurahan).trigger('change');
                });
            }
        });
        
        $('#modalPasar').modal('show');
    });

    // Delete button
    var deleteId;
    $(document).on('click', '.btn-delete', function() {
        deleteId = $(this).data('id');
        var nama = $(this).data('nama');
        
        $('#nama-pasar-hapus').text(nama);
        $('#modalHapus').modal('show');
    });

    // Confirm delete
    $('#btn-konfirm-hapus').click(function() {
        $.ajax({
            url: '<?= base_url('master_pasar/delete') ?>',
            type: 'POST',
            data: { id: deleteId },
            dataType: 'json',
            beforeSend: function() {
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menghapus...');
            },
            success: function(response) {
                $('#modalHapus').modal('hide');
                
                if (response.status) {
                    table.ajax.reload();
                    loadStatistik();
                    toastr.success(response.message, 'Berhasil');
                } else {
                    toastr.error(response.message, 'Gagal');
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan sistem', 'Error');
            },
            complete: function() {
                $('#btn-konfirm-hapus').prop('disabled', false).html('<i class="fa fa-trash"></i> Hapus');
            }
        });
    });

    // Functions
    function resetForm() {
        $('#formPasar')[0].reset();
        $('#modal-title').text('Tambah Pasar');
        $('#id').val('');
        $('#pasar_status').prop('checked', true);
        $('#pasar_id_kecamatan, #pasar_id_kelurahan').val(null).trigger('change');
        
        // Reset map
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        $('#pasar_latitude, #pasar_longitude').val('');
    }

    function loadKecamatanPalembang(callback) {
        $.ajax({
            url: '<?= base_url('daerah/option_kecamatan/16.73') ?>',
            type: 'GET',
            success: function(data) {
                // For filter - add "Semua Kecamatan" option
                var filterOptions = '<option value="">-- Semua Kecamatan --</option>' + data;
                $('#filter_kecamatan').html(filterOptions.replace('<option value=""></option>', ''));

                // For form modal - add "Pilih Kecamatan" option
                var formOptions = '<option value="">Pilih Kecamatan</option>' + data;
                $('#pasar_id_kecamatan').html(formOptions.replace('<option value=""></option>', ''));

                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function() {
                console.log('Error loading kecamatan untuk Palembang');
                $('#filter_kecamatan').html('<option value="">-- Error loading data --</option>');
                $('#pasar_id_kecamatan').html('<option value="">Error loading data</option>');
            }
        });
    }

    function loadKelurahanByKecamatan(id_kecamatan, target, callback) {
        var targetSelect = $('#' + target);
        targetSelect.html('<option value="">Loading...</option>');

        if (id_kecamatan === '' || !id_kecamatan) {
            var defaultOption = target.includes('filter') ? '-- Semua Kelurahan --' : 'Pilih Kelurahan';
            targetSelect.html('<option value="">' + defaultOption + '</option>');
            return;
        }

        $.ajax({
            url: '<?= base_url('daerah/option_kelurahan/') ?>' + id_kecamatan,
            type: 'GET',
            success: function(data) {
                var defaultOption = target.includes('filter') ? '-- Semua Kelurahan --' : 'Pilih Kelurahan';
                var options = '<option value="">' + defaultOption + '</option>' + data;
                targetSelect.html(options.replace('<option value=""></option>', ''));

                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function() {
                var defaultOption = target.includes('filter') ? '-- Error loading data --' : 'Error loading data';
                targetSelect.html('<option value="">' + defaultOption + '</option>');
            }
        });
    }

    function loadStatistik() {
        // Load statistik pasar
        // Implementasi sesuai kebutuhan
    }

    // Load initial data
    loadStatistik();
});
</script>
