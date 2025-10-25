<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
$(document).ready(function() {

    // DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?= base_url('hak_akses/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                return d;
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "columnDefs": [{ "targets": [-1], "orderable": false }],
        "order": [[1, 'asc']],
        "pageLength": 10,
        "language": {
            "processing": "Memproses...",
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Tidak ada data yang ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data yang tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });


    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');

        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '<?= base_url('hak_akses/hapus') ?>/' + id,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        table.draw();
                        toastr.success(response.message || 'Data berhasil dihapus');
                    } else {
                        toastr.error(response.message || 'Gagal menghapus data');
                    }
                },
                error: function() {
                    toastr.error('Terjadi kesalahan sistem');
                }
            });
        }
    });

});

</script>