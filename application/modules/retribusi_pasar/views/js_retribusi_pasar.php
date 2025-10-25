<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
// Fungsi global untuk handling data
function get_post_data(data) {
    data = data || {};
    return data;
}

function get_post_data_from_form(formData) {
    var data = {};
    var formArray = formData.split('&');
    for (var i = 0; i < formArray.length; i++) {
        var pair = formArray[i].split('=');
        data[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
    }
    return data;
}

$(document).ready(function() {
    // Global variables
    window.currentPedagang = null;
    window.currentTarif = null;
    window.currentPembayaran = null;
    window.currentPeriode = {
        bulan: $('#filter-periode-bulan').val(),
        tahun: $('#filter-periode-tahun').val()
    };

    // Initialize
    loadPedagangList();
    
    // Event handlers
    $('#filter-periode-bulan, #filter-periode-tahun').change(function() {
        window.currentPeriode = {
            bulan: $('#filter-periode-bulan').val(),
            tahun: $('#filter-periode-tahun').val()
        };
        loadPedagangList();
        resetDetailContent();
    });

    $('#btn-search, #search-pedagang').on('keyup click', function(e) {
        if (e.type === 'keyup' && e.keyCode !== 13) return;
        loadPedagangList();
    });

    $('#btn-apply-filter').click(function() {
        loadPedagangList();
        $('#modalFilter').modal('hide');
    });

    $('#btn-bayar').click(function() {
        if (!window.currentPedagang) {
            alert('Pilih pedagang terlebih dahulu');
            return;
        }
        showModalPembayaran();
    });

    $('#btn-print').click(function() {
        if (!window.currentPembayaran) {
            alert('Tidak ada data pembayaran untuk dicetak');
            return;
        }
        printKwitansi(window.currentPembayaran.retribusi_id);
    });

    // Form pembayaran submit
    $('#formPembayaran').submit(function(e) {
        e.preventDefault();
        savePembayaran();
    });
});

// Load daftar pedagang
function loadPedagangList() {
    var data = get_post_data({
        pasar_id: $('#filter-pasar').val(),
        jenis_id: $('#filter-jenis').val(),
        status_filter: $('#filter-status').val(),
        search: $('#search-pedagang').val(),
        bulan: window.currentPeriode.bulan,
        tahun: window.currentPeriode.tahun
    });

    $.ajax({
        url: '<?= base_url('retribusi_pasar/ajax_pedagang_list') ?>',
        type: 'POST',
        data: data,
        beforeSend: function() {
            $('#pedagang-list').html('<div class="text-center text-muted" style="padding: 50px;"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat data pedagang...</p></div>');
        },
        success: function(response) {
            if (response.status) {
                $('#pedagang-list').html(response.html);
                
                // Event click pedagang
                $('.pedagang-item').click(function(e) {
                    e.preventDefault();
                    var pedagang_id = $(this).data('id');
                    loadDetailPembayaran(pedagang_id);
                    
                    // Update active state
                    $('.pedagang-item').removeClass('active');
                    $(this).addClass('active');
                });
            } else {
                $('#pedagang-list').html('<div class="text-center text-danger" style="padding: 20px;">Error: ' + response.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading pedagang list:', error);
            console.error('XHR Response:', xhr.responseText);
            console.error('Status:', status);

            // Cek apakah response adalah halaman login
            if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE html>') !== -1) {
                $('#pedagang-list').html('<div class="text-center text-warning" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Sesi telah berakhir. Silakan refresh halaman dan login kembali.</div>');
            } else {
                $('#pedagang-list').html('<div class="text-center text-danger" style="padding: 20px;">Terjadi kesalahan saat memuat data</div>');
            }
        }
    });
}

// Load detail pembayaran pedagang
function loadDetailPembayaran(pedagang_id) {
    var data = get_post_data({
        pedagang_id: pedagang_id,
        bulan: window.currentPeriode.bulan,
        tahun: window.currentPeriode.tahun
    });

    $.ajax({
        url: '<?= base_url('retribusi_pasar/ajax_detail_pembayaran') ?>',
        type: 'POST',
        data: data,
        beforeSend: function() {
            $('#detail-content').html('<div class="text-center text-muted" style="padding: 50px;"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat detail pembayaran...</p></div>');
            $('#btn-bayar, #btn-print').prop('disabled', true);
        },
        success: function(response) {
            if (response.status) {
                $('#detail-content').html(response.html);
                
                // Set global variables
                window.currentPedagang = response.pedagang;
                window.currentTarif = response.tarif;
                window.currentPembayaran = response.pembayaran;
            } else {
                $('#detail-content').html('<div class="text-center text-danger" style="padding: 20px;">Error: ' + response.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading detail pembayaran:', error);
            console.error('XHR Response:', xhr.responseText);
            console.error('Status:', status);

            // Cek apakah response adalah halaman login
            if (xhr.responseText && xhr.responseText.indexOf('<!DOCTYPE html>') !== -1) {
                $('#detail-content').html('<div class="text-center text-warning" style="padding: 20px;"><i class="fa fa-exclamation-triangle"></i> Sesi telah berakhir. Silakan refresh halaman dan login kembali.</div>');
            } else {
                $('#detail-content').html('<div class="text-center text-danger" style="padding: 20px;">Terjadi kesalahan saat memuat detail</div>');
            }
        }
    });
}

// Load riwayat pembayaran
function loadRiwayatPembayaran(pedagang_id) {
    $.ajax({
        url: '<?= base_url('retribusi_pasar/ajax_riwayat_pembayaran') ?>',
        type: 'POST',
        data: get_post_data({pedagang_id: pedagang_id}),
        success: function(response) {
            if (response.status) {
                $('#riwayat-pembayaran').html(response.html);
            } else {
                $('#riwayat-pembayaran').html('<div class="text-center text-muted" style="padding: 20px;">Tidak ada riwayat pembayaran</div>');
            }
        },
        error: function() {
            $('#riwayat-pembayaran').html('<div class="text-center text-danger" style="padding: 20px;">Error memuat riwayat</div>');
        }
    });
}

// Show modal pembayaran
function showModalPembayaran() {
    if (!window.currentPedagang || !window.currentTarif) {
        alert('Data pedagang atau tarif tidak lengkap');
        return;
    }

    var isEdit = window.currentPembayaran ? true : false;
    var nominal = window.currentTarif.tarif_harian;
    var denda = 0;

    // Hitung denda jika terlambat
    if (!isEdit) {
        var tanggal_jatuh_tempo = window.currentPeriode.tahun + '-' + 
                                 String(window.currentPeriode.bulan).padStart(2, '0') + '-05';
        var today = new Date().toISOString().split('T')[0];
        
        if (today > tanggal_jatuh_tempo) {
            var diffTime = Math.abs(new Date(today) - new Date(tanggal_jatuh_tempo));
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            denda = (nominal * window.currentTarif.tarif_denda_persen / 100) * diffDays;
        }
    }

    var formHtml = `
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fa fa-user"></i> Data Pedagang</h5>
                <table class="table table-condensed">
                    <tr><td>Nama</td><td>: ${window.currentPedagang.nama_lengkap}</td></tr>
                    <tr><td>NIK</td><td>: ${window.currentPedagang.nik}</td></tr>
                    <tr><td>Blok</td><td>: ${window.currentPedagang.pasar_blok_nama}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5><i class="fa fa-calendar"></i> Periode</h5>
                <table class="table table-condensed">
                    <tr><td>Bulan</td><td>: ${getBulanName(window.currentPeriode.bulan)}</td></tr>
                    <tr><td>Tahun</td><td>: ${window.currentPeriode.tahun}</td></tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal Pembayaran</label>
                    <input type="date" name="retribusi_tanggal" class="form-control" 
                           value="${isEdit ? window.currentPembayaran.retribusi_tanggal : new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="form-group">
                    <label>Nominal Retribusi</label>
                    <input type="number" name="retribusi_nominal" class="form-control" 
                           value="${isEdit ? window.currentPembayaran.retribusi_nominal : nominal}" 
                           step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Denda</label>
                    <input type="number" name="retribusi_denda" class="form-control" 
                           value="${isEdit ? window.currentPembayaran.retribusi_denda : denda}" 
                           step="0.01">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Status Pembayaran</label>
                    <select name="retribusi_status" class="form-control" required>
                        <option value="LUNAS" ${isEdit && window.currentPembayaran.retribusi_status == 'LUNAS' ? 'selected' : ''}>Lunas</option>
                        <option value="SEBAGIAN" ${isEdit && window.currentPembayaran.retribusi_status == 'SEBAGIAN' ? 'selected' : ''}>Sebagian</option>
                        <option value="TERLAMBAT" ${isEdit && window.currentPembayaran.retribusi_status == 'TERLAMBAT' ? 'selected' : ''}>Terlambat</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="retribusi_metode_bayar" class="form-control" required>
                        <option value="TUNAI" ${isEdit && window.currentPembayaran.retribusi_metode_bayar == 'TUNAI' ? 'selected' : ''}>Tunai</option>
                        <option value="TRANSFER" ${isEdit && window.currentPembayaran.retribusi_metode_bayar == 'TRANSFER' ? 'selected' : ''}>Transfer</option>
                        <option value="QRIS" ${isEdit && window.currentPembayaran.retribusi_metode_bayar == 'QRIS' ? 'selected' : ''}>QRIS</option>
                        <option value="LAINNYA" ${isEdit && window.currentPembayaran.retribusi_metode_bayar == 'LAINNYA' ? 'selected' : ''}>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="retribusi_keterangan" class="form-control" rows="3">${isEdit ? (window.currentPembayaran.retribusi_keterangan || '') : ''}</textarea>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="id" value="${isEdit ? window.currentPembayaran.retribusi_id : ''}">
        <input type="hidden" name="pedagang_id" value="${window.currentPedagang.id}">
        <input type="hidden" name="pasar_blok_id" value="${window.currentPedagang.pasar_blok_id}">
        <input type="hidden" name="penyewaan_blok_id" value="${window.currentPedagang.penyewaan_blok_id || ''}">
        <input type="hidden" name="retribusi_periode_bulan" value="${window.currentPeriode.bulan}">
        <input type="hidden" name="retribusi_periode_tahun" value="${window.currentPeriode.tahun}">
    `;

    $('#form-pembayaran-content').html(formHtml);
    $('#modalPembayaran').modal('show');
}

// Save pembayaran
function savePembayaran() {
    var formData = $('#formPembayaran').serialize();
    var data = get_post_data_from_form(formData);

    $.ajax({
        url: '<?= base_url('retribusi_pasar/save') ?>',
        type: 'POST',
        data: data,
        beforeSend: function() {
            $('#formPembayaran button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        },
        success: function(response) {
            $('#formPembayaran button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Pembayaran');
            
            if (response.status) {
                $('#modalPembayaran').modal('hide');
                show_success_message(response.message);
                
                // Reload data
                loadPedagangList();
                if (window.currentPedagang) {
                    loadDetailPembayaran(window.currentPedagang.id);
                }
            } else {
                show_error_message(response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#formPembayaran button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Pembayaran');
            show_error_message('Terjadi kesalahan saat menyimpan data');
            console.error('Error saving pembayaran:', error);
        }
    });
}

// Edit pembayaran
function editPembayaran(retribusi_id) {
    // Data sudah ada di window.currentPembayaran
    showModalPembayaran();
}

// Print kwitansi
function printKwitansi(retribusi_id) {
    var url = '<?= base_url('retribusi_pasar/print_kwitansi/') ?>' + retribusi_id;
    window.open(url, '_blank', 'width=800,height=600');
}

// Reset detail content
function resetDetailContent() {
    $('#detail-content').html(`
        <div class="text-center text-muted" style="padding: 80px 20px;">
            <i class="fa fa-user-o fa-4x" style="color: #ddd;"></i>
            <h4 style="color: #999; margin-top: 20px;">Pilih Pedagang</h4>
            <p style="color: #bbb;">Pilih pedagang dari daftar di sebelah kiri untuk melihat detail pembayaran retribusi</p>
        </div>
    `);
    $('#btn-bayar, #btn-print').prop('disabled', true);
    window.currentPedagang = null;
    window.currentTarif = null;
    window.currentPembayaran = null;
}

// Helper functions
function getBulanName(bulan) {
    var bulanNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return bulanNames[parseInt(bulan)];
}

function show_success_message(message) {
    toastr.success(message);
}

function show_error_message(message) {
    toastr.error(message);
}

// Fungsi untuk pembayaran retribusi harian
function bayarRetribusiHarian() {
    if (!window.currentPedagang) {
        show_error_message('Silakan pilih pedagang terlebih dahulu');
        return;
    }

    if (!window.currentTarif) {
        show_error_message('Tarif untuk jenis pasar ini belum ditetapkan');
        return;
    }

    // Isi data ke modal
    $('#pedagang-nama-display').val(window.currentPedagang.nama_lengkap);
    $('#pedagang-id-bayar').val(window.currentPedagang.id);
    $('#jenis-pasar-display').val(window.currentPedagang.pasar_jenis_nama);
    $('#tarif-harian-display').val(formatRupiah(window.currentTarif.tarif_harian));

    // Reset form
    $('#tanggal-bayar').val(getCurrentDate());
    $('#metode-bayar').val('TUNAI');
    $('#keterangan-bayar').val('');

    // Tampilkan modal
    $('#modalPembayaranHarian').modal('show');
}

// Handle submit form pembayaran harian
$(document).on('submit', '#formPembayaranHarian', function(e) {
    e.preventDefault();

    var formData = $(this).serialize();
    var postData = get_post_data_from_form(formData);

    $.ajax({
        url: '<?= base_url('retribusi_pasar/bayar_retribusi_harian') ?>',
        type: 'POST',
        data: postData,
        dataType: 'json',
        beforeSend: function() {
            $('#modalPembayaranHarian .btn-success').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');
        },
        success: function(response) {
            if (response.status) {
                $('#modalPembayaranHarian').modal('hide');
                show_success_message(response.message);

                // Reload data
                loadPedagangList();
                if (window.currentPedagang) {
                    loadDetailPembayaran(window.currentPedagang.id);
                }
            } else {
                show_error_message(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            show_error_message('Terjadi kesalahan saat memproses pembayaran');
        },
        complete: function() {
            $('#modalPembayaranHarian .btn-success').prop('disabled', false).html('<i class="fa fa-money"></i> Bayar Retribusi');
        }
    });
});

// Fungsi helper
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}

function getCurrentDate() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    return yyyy + '-' + mm + '-' + dd;
}

// Fungsi untuk validasi tanggal pembayaran
$(document).on('change', '#tanggal-bayar', function() {
    var selectedDate = $(this).val();
    var today = getCurrentDate();

    if (selectedDate > today) {
        show_error_message('Tanggal pembayaran tidak boleh lebih dari hari ini');
        $(this).val(today);
    }
});
</script>
