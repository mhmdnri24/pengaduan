<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });

    // DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('master_kategori/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.parent = $('#filter_parent').val();
                d.status = $('#filter_status').val();
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [
            { "targets": [-1], "orderable": false },
            { "targets": [0], "orderable": false }
        ],
        "order": [[2, 'asc']],
        "createdRow": function(row, data, dataIndex) {
            if (data.DT_RowClass) {
                $(row).addClass(data.DT_RowClass);
            }
        },
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
        },
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]]
    });

    // Filter events
    $('#filter_parent, #filter_status').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset').click(function() {
        $('#filter_parent, #filter_status').val(null).trigger('change');
    });

    // Reset form ketika modal ditutup
    $('#modalKategori').on('hidden.bs.modal', function() {
        resetForm();
    });

    // Reset form ketika tombol tambah diklik
    $('[data-target="#modalKategori"]').click(function() {
        resetForm();
        $('#modalKategoriLabel').text('Tambah Kategori');
    });

    // Reset form ketika tombol batal atau close diklik
    $('#modalKategori .btn[data-dismiss="modal"], #modalKategori .close').click(function() {
        resetForm();
    });

    // Function untuk reset form
    function resetForm() {
        $('#formKategori')[0].reset();
        $('#id').val('');
        $('#parent_id').val(null).trigger('change');
        $('#status').prop('checked', true);
        // Clear validation states
        $('#formKategori .form-group').removeClass('has-error has-success');
        $('#formKategori .help-block').remove();
    }

    // Form submit
    $('#formKategori').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('master_kategori/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#formKategori button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                
                if (response.status) {
                    $('#modalKategori').modal('hide');
                    table.ajax.reload(null, false); // Reload tanpa reset paging
                    
                    // Show success message
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Reset form
                    resetForm();
                    
                    // Auto-reload statistik dalam 1 detik
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem',
                    icon: 'error'
                });
            },
            complete: function() {
                $('#formKategori button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        // Reset form terlebih dahulu
        resetForm();
        
        var id = $(this).data('id');
        var parent = $(this).data('parent');
        var kode = $(this).data('kode');
        var nama = $(this).data('nama');
        var deskripsi = $(this).data('deskripsi');
        var status = $(this).data('status');
        
        $('#id').val(id);
        $('#parent_id').val(parent).trigger('change');
        $('#kode_kategori').val(kode);
        $('#nama_kategori').val(nama);
        $('#deskripsi').val(deskripsi);
        $('#status').prop('checked', status == 1);
        $('#modalKategoriLabel').text('Edit Kategori');
        $('#modalKategori').modal('show');
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus kategori ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('master_kategori/delete') ?>',
                    type: 'POST',
                    data: {
                        id: id,
                    },
                    dataType: 'json',
                    beforeSend: function() {
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
                    },
                    success: function(response) {

                        if (response.status) {
                            table.ajax.reload(null, false); // Reload tanpa reset paging
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Auto-reload statistik dalam 1 detik
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error details:', xhr.responseText);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Input validation dan formatting
    $('#kode_kategori').on('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Refresh tooltips after table reload
    table.on('draw', function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
});
</script>