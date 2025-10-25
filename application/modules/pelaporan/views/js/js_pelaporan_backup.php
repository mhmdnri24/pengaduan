<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2();

    // Inisialisasi DataTable
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
            },
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            {data: '0', name: 'no', orderable: false, searchable: false},
            {data: '1', name: 'kode_laporan'},
            {data: '2', name: 'judul'},
            {data: '3', name: 'pelapor_nama'},
            {data: '4', name: 'kategori', orderable: false},
            {data: '5', name: 'unitkerja', orderable: false},
            {data: '6', name: 'status', orderable: false},
            {data: '7', name: 'prioritas', orderable: false},
            {data: '8', name: 'created_at'},
            {data: '9', name: 'aksi', orderable: false, searchable: false}
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            emptyTable: "Tidak ada data laporan ditemukan"
        },
        order: [[8, 'desc']], // Urutkan berdasarkan tanggal terbaru
        pageLength: 25
    });

    // Load data kategori saat halaman dimuat
    loadKategoriOptions();
    loadMasyarakatOptions();

    // Event handlers untuk filter
    $('#filter-status').on('change', function() {
        table.ajax.reload(null, false);
    });

    $('#filter-kategori').on('change', function() {
        table.ajax.reload(null, false);
    });

    $('#filter-prioritas').on('change', function() {
        table.ajax.reload(null, false);
    });

    $('#filter-unitkerja').on('change', function() {
        table.ajax.reload(null, false);
    });

    // Reset filter
    $('#btn-reset-filter').on('click', function() {
        $('#filter-status').val('').trigger('change');
        $('#filter-kategori').val('').trigger('change');
        $('#filter-prioritas').val('').trigger('change');
        $('#filter-unitkerja').val('').trigger('change');
        table.ajax.reload(null, false);
    });

    // Event untuk tombol tambah laporan
    $('#btn-add').on('click', function() {
        resetForm();
        $('#modal-laporan').modal('show');
    });

    // Event untuk tombol export
    $('#btn-export').on('click', function() {
        loadExportOptions();
        $('#modal-export').modal('show');
    });

    // Form submission untuk laporan
    $('#form-laporan').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('pelaporan/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    $('#modal-laporan').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat menyimpan data');
            }
        });
    });

    // Form submission untuk update status
    $('#form-status').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: '<?= base_url('pelaporan/update_status') ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status) {
                    $('#modal-status').modal('hide');
                    table.ajax.reload(null, false);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat mengupdate status');
            }
        });
    });

    // Event untuk tombol edit
    $('#table-pelaporan').on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        editLaporan(id);
    });

    // Event untuk tombol update status
    $('#table-pelaporan').on('click', '.btn-update-status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        updateStatus(id, status);
    });

    // Event untuk tombol delete
    $('#table-pelaporan').on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        deleteLaporan(id);
    });

    // Event untuk tombol detail
    $('#table-pelaporan').on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        window.location.href = '<?= base_url('pelaporan/detail/') ?>' + id;
    });

    // Event untuk select masyarakat
    $('#masyarakat_id').on('change', function() {
        var masyarakatId = $(this).val();
        if (masyarakatId) {
            loadMasyarakatData(masyarakatId);
        }
    });

    // Fungsi untuk load kategori options
    function loadKategoriOptions() {
        $.ajax({
            url: '<?= base_url('pelaporan/get_kategori') ?>',
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    var options = '<option value="">Pilih Kategori</option>';
                    response.data.forEach(function(item) {
                        options += '<option value="' + item.nama_kategori + '">' + item.nama_kategori + '</option>';
                    });
                    $('#kategori, #export_kategori').html(options);
                }
            }
        });
    }

    // Fungsi untuk load masyarakat options
    function loadMasyarakatOptions() {
        $.ajax({
            url: '<?= base_url('pelaporan/get_masyarakat') ?>',
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    var options = '<option value="">Pilih dari data masyarakat</option>';
                    response.data.forEach(function(item) {
                        options += '<option value="' + item.id + '">' + item.nama_lengkap + ' (' + item.nik_ktp + ')</option>';
                    });
                    $('#masyarakat_id').html(options);
                }
            }
        });
    }

    // Fungsi untuk load data masyarakat berdasarkan ID
    function loadMasyarakatData(id) {
        $.ajax({
            url: '<?= base_url('pelaporan/get_masyarakat_by_id') ?>',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#pelapor_nama').val(data.nama_lengkap);
                    $('#pelapor_nik').val(data.nik_ktp);
                    $('#pelapor_telepon').val(data.no_wa_hp);
                    $('#pelapor_alamat').val(data.alamat);
                }
            }
        });
    }

    // Fungsi untuk reset form
    function resetForm() {
        $('#form-laporan')[0].reset();
        $('#laporan-id').val('');
        $('#modal-title').html('<i class="fa fa-plus"></i> Tambah Laporan');

        // Reset map jika ada
        if (typeof map !== 'undefined') {
            map.setView([-2.9760285, 104.7754794], 13);
            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }
            $('#location-info').hide();
        }
    }

    // Fungsi untuk edit laporan
    function editLaporan(id) {
        $.ajax({
            url: '<?= base_url('pelaporan/get_by_id') ?>',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#laporan-id').val(data.id);
                    $('#judul').val(data.judul);
                    $('#deskripsi').val(data.deskripsi);
                    $('#kategori').val(data.kategori).trigger('change');
                    $('#prioritas').val(data.prioritas).trigger('change');
                    $('#alamat').val(data.alamat);
                    $('#pelapor_nama').val(data.pelapor_nama);
                    $('#pelapor_nik').val(data.pelapor_nik);
                    $('#pelapor_telepon').val(data.pelapor_telepon);
                    $('#pelapor_alamat').val(data.pelapor_alamat);

                    // Set map location jika ada koordinat
                    if (data.lokasi_lat && data.lokasi_lng) {
                        $('#lokasi_lat').val(data.lokasi_lat);
                        $('#lokasi_lng').val(data.lokasi_lng);
                        // Update map marker jika map sudah diinisialisasi
                        if (typeof map !== 'undefined') {
                            var latLng = L.latLng(data.lokasi_lat, data.lokasi_lng);
                            if (marker) {
                                marker.setLatLng(latLng);
                            } else {
                                marker = L.marker(latLng).addTo(map);
                            }
                            map.setView(latLng, 15);
                            $('#selected-coordinates').text(data.lokasi_lat + ', ' + data.lokasi_lng);
                            $('#location-info').show();
                        }
                    }

                    $('#modal-title').html('<i class="fa fa-edit"></i> Edit Laporan');
                    $('#modal-laporan').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Terjadi kesalahan saat memuat data');
            }
        });
    }

    // Fungsi untuk update status
    function updateStatus(id, currentStatus) {
        $('#status-id').val(id);

        // Load status options berdasarkan status saat ini
        loadStatusOptions(currentStatus);

        $('#modal-status').modal('show');
    }

    // Fungsi untuk load status options
    function loadStatusOptions(currentStatus) {
        var allStatuses = ['LAPOR', 'DITERIMA', 'DIKERJAKAN', 'SELESAI'];
        var options = '<option value="">Pilih Status</option>';

        allStatuses.forEach(function(status) {
            if (status !== currentStatus) {
                options += '<option value="' + status + '">' + status + '</option>';
            }
        });

        $('#status').html(options);
    }

    // Fungsi untuk delete laporan
    function deleteLaporan(id) {
        if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
            $.ajax({
                url: '<?= base_url('pelaporan/delete') ?>',
                type: 'POST',
                data: {id: id},
                success: function(response) {
                    if (response.status) {
                        table.ajax.reload(null, false);
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    // Fungsi untuk load export options
    function loadExportOptions() {
        // Copy current filter values to export modal
        $('#export_status').val($('#filter-status').val()).trigger('change');
        $('#export_kategori').val($('#filter-kategori').val()).trigger('change');
    }
});
</script>