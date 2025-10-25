<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%'
    });

    // DataTable initialization
    var table = $('#tabel-kategori').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('kategori_pelaporan/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                d.status = $('#filter-status').val();
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

    // Filter events
    $('#filter-status').select2().on('change', function() {
        table.ajax.reload();
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-status').val(null).trigger('change');
    });

    // Tombol tambah
    $('#btn-tambah').on('click', function() {
        $('#modal-title').text('Tambah Kategori');
        $('#modal-form').modal('show');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $.ajax({
            url: '<?= base_url('kategori_pelaporan/detail') ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#pelaporan_id').val(data.pelaporan_id);
                    $('#pelaporan_kode').val(data.pelaporan_kode);
                    $('#pelaporan_nama').val(data.pelaporan_nama);
                    $('#status').prop('checked', data.status == 1);

                    $('#modal-title').text('Edit Kategori Pelaporan');
                    $('#modal-form').modal('show');
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
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: 'Apakah Anda yakin ingin menghapus data kategori ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    deleteKategori(id);
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus data kategori ini?')) {
                deleteKategori(id);
            }
        }
    });

    function deleteKategori(id) {
        $.ajax({
            url: '<?= base_url('kategori_pelaporan/delete') ?>',
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

    // Form submit
    $('#form-kategori').submit(function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: '<?= base_url('kategori_pelaporan/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                $('#form-kategori button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {

                if (response.status) {
                    $('#modal-form').modal('hide');
                    table.ajax.reload();

                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }

                    // Reset form
                    $('#form-kategori')[0].reset();
                    $('#pelaporan_id').val('');
                    $('#modal-title').text('Tambah Kategori');
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
                $('#form-kategori button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Reset form when modal is hidden
    $('#modal-form').on('hidden.bs.modal', function() {
        $('#form-kategori')[0].reset();
        $('#pelaporan_id').val('');
        $('#modal-title').text('Tambah Kategori');
    });

});
</script>
