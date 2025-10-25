<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2();

    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= $ajax_url ?>',
            type: 'POST',
            data: function(d) {
                d.status_aktif = $('#filter_status').val();
                d.filter_level = $('#filter_level').val();
                d.filter_instansi = $('#filter_instansi').val();
                d.filter_unitkerja = $('#filter_unitkerja').val();
            },
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            {data: '0', name: 'checkbox', orderable: false, searchable: false},
            {data: '1', name: 'no'},
            {data: '2', name: 'foto', orderable: false, searchable: false},
            {data: '3', name: 'nama'},
            {data: '4', name: 'username'},
            {data: '5', name: 'instansi'},
            {data: '6', name: 'unitkerja'},
            {data: '7', name: 'level'},
            {data: '8', name: 'status', orderable: false},
            {data: '9', name: 'aksi', orderable: false, searchable: false}
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
        },
        order: [[1, 'desc']]
    });

    // Checkbox handling
    $('#checkAll').on('change', function() {
        $('.row-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        updateBulkActions();
    });

    $('#dataTable').on('change', '.row-checkbox', function() {
        updateBulkActions();

        // Update checkAll state
        var totalCheckboxes = $('.row-checkbox:not(:disabled)').length;
        var checkedCheckboxes = $('.row-checkbox:not(:disabled):checked').length;
        $('#checkAll').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
    });

    function updateBulkActions() {
        var checkedCount = $('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').show();
            $('#selectedCount').text(checkedCount);
        } else {
            $('#bulkActions').hide();
        }
    }

    // Bulk delete function
    window.bulkDelete = function() {
        var selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Pilih setidaknya satu user untuk dihapus');
            return;
        }

        if (confirm('Apakah Anda yakin ingin menghapus ' + selectedIds.length + ' user terpilih?')) {
            $.ajax({
                url: '<?= base_url("user/bulk_delete") ?>',
                type: 'POST',
                data: {ids: selectedIds},
                success: function(response) {
                    if (response.status) {
                        alert('Berhasil: ' + response.message);
                        table.ajax.reload(null, false);
                        $('#checkAll').prop('checked', false);
                        updateBulkActions();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    };

    // Cancel bulk actions
    window.cancelBulk = function() {
        $('.row-checkbox').prop('checked', false);
        $('#checkAll').prop('checked', false);
        updateBulkActions();
    };

    // Filter event handlers
    $('#filter_status').on('change', function() {
        table.ajax.reload(null, false);
    });

    $('#filter_level').on('change', function() {
        table.ajax.reload(null, false);
    });

    $('#filter_instansi').on('change', function() {
        var instansi_id = $(this).val();
        // Reset filter unitkerja ketika instansi berubah
        $('#filter_unitkerja').html('<option value="">Semua Unit Kerja</option>');
        if (instansi_id) {
            // Load unit kerja untuk instansi yang dipilih
            $.ajax({
                url: '<?= base_url("user/get_unitkerja_options") ?>',
                type: 'POST',
                data: {instansi_id: instansi_id},
                success: function(response) {
                    if (response.status) {
                        $.each(response.data, function(key, value) {
                            $('#filter_unitkerja').append('<option value="' + value.id_unitkerja + '">' + value.unitkerja + '</option>');
                        });
                    }
                }
            });
        }
        table.ajax.reload(null, false);
    });

    $('#filter_unitkerja').on('change', function() {
        table.ajax.reload(null, false);
    });
});
</script> 