<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<script>
$(document).ready(function() {
    // CSRF Token
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    
    function update_csrf(hash) {
        if (hash) {
            csrfHash = hash;
        } else {
            $.get('<?= base_url('get_csrf'); ?>', function(result) {
                csrfHash = result.csrf_hash;
            });
        }
    }

    function get_post_data(data) {
        data = data || {};
        data[csrfName] = csrfHash;
        return data;
    }

    // Initialize DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('komoditas/ajax_data') ?>',
            type: 'POST',
            data: function(d) {
                return get_post_data(d);
            },
            dataSrc: function(json) {
                update_csrf(json.csrf_hash);
                return json.data;
            }
        },
        columns: [
            { data: '0', name: '0', orderable: false, searchable: false },
            { data: '1', name: 'komoditas_nama' },
            { data: '2', name: 'parent_nama' },
            { data: '3', name: 'komoditas_satuan' },
            { data: '4', name: 'komoditas_urutan' },
            { data: '5', name: 'komoditas_status' },
            { data: '6', name: '6', orderable: false, searchable: false }
        ],
        order: [[4, 'asc']],
        pageLength: 25,
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
        }
    });

    // Reset modal when hidden
    $('#modalKomoditas').on('hidden.bs.modal', function() {
        $('#formKomoditas')[0].reset();
        $('#id').val('');
        $('#modalKomoditasLabel').text('Form Komoditas');
        $('#komoditas_parent_id').val(0).trigger('change');
    });

    // Form submit handler
    $('#formKomoditas').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btnSimpan');
        var id = $('#id').val();
        var url = id ? '<?= base_url('komoditas/save/') ?>' + id : '<?= base_url('komoditas/save') ?>';
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                update_csrf(response.csrf_hash);
                if (response.status) {
                    $('#modalKomoditas').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message
                    }).then(() => {
                        table.ajax.reload();
                        location.reload(); // Reload untuk update tree view
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat menyimpan data.'
                });
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Edit button handler (both tree and table)
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('komoditas/get_by_id') ?>',
            type: 'POST',
            data: get_post_data({ id: id }),
            dataType: 'json',
            success: function(response) {
                update_csrf(response.csrf_hash);
                if (response.status) {
                    var data = response.data;
                    $('#id').val(data.komoditas_id);
                    $('#komoditas_nama').val(data.komoditas_nama);
                    $('#komoditas_parent_id').val(data.komoditas_parent_id).trigger('change');
                    $('#komoditas_satuan').val(data.komoditas_satuan);
                    $('#komoditas_deskripsi').val(data.komoditas_deskripsi);
                    $('#komoditas_urutan').val(data.komoditas_urutan);
                    $('#komoditas_status').val(data.komoditas_status).trigger('change');
                    $('#modalKomoditasLabel').text('Edit Komoditas');
                    $('#modalKomoditas').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengambil data.'
                });
            }
        });
    });

    // Copy button handler (both tree and table)
    $(document).on('click', '.btn-copy', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin ingin menyalin komoditas ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Salin!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('komoditas/copy') ?>',
                    type: 'POST',
                    data: get_post_data({ id: id }),
                    dataType: 'json',
                    success: function(response) {
                        update_csrf(response.csrf_hash);
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message
                            }).then(() => {
                                table.ajax.reload();
                                location.reload(); // Reload untuk update tree view
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menyalin komoditas.'
                        });
                    }
                });
            }
        });
    });

    // Delete button handler (both tree and table)
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus komoditas ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('komoditas/delete') ?>',
                    type: 'POST',
                    data: get_post_data({ id: id }),
                    dataType: 'json',
                    success: function(response) {
                        update_csrf(response.csrf_hash);
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message
                            }).then(() => {
                                table.ajax.reload();
                                location.reload(); // Reload untuk update tree view
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus komoditas.'
                        });
                    }
                });
            }
        });
    });

    // Initialize select2 when modal is shown
    $('#modalKomoditas').on('show.bs.modal', function () {
        $('#komoditas_parent_id, #komoditas_status').select2({
            dropdownParent: $(this),
            width: '100%'
        });
    });

    // Tree view expand/collapse functionality
    $(document).on('click', '.parent-item', function(e) {
        if (!$(e.target).hasClass('btn') && !$(e.target).parent().hasClass('btn')) {
            var $children = $(this).parent().find('ul.tree-view');
            var $icon = $(this).find('i.fa-folder-open, i.fa-folder');
            
            if ($children.is(':visible')) {
                $children.slideUp();
                $icon.removeClass('fa-folder-open').addClass('fa-folder');
            } else {
                $children.slideDown();
                $icon.removeClass('fa-folder').addClass('fa-folder-open');
            }
        }
    });

    // Auto-set urutan when parent changes
    $('#komoditas_parent_id').on('change', function() {
        var parent_id = $(this).val();
        
        // Get next order number for selected parent
        $.ajax({
            url: '<?= base_url('komoditas/get_next_order') ?>',
            type: 'POST',
            data: get_post_data({ parent_id: parent_id }),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#komoditas_urutan').val(response.next_order);
                }
            }
        });
    });

    // Make tree sortable for drag & drop (optional feature)
    if (typeof $.fn.sortable !== 'undefined') {
        $('#komoditasTree').sortable({
            items: 'li',
            handle: '.parent-item, .child-item',
            placeholder: 'ui-state-highlight',
            update: function(event, ui) {
                var item_id = ui.item.find('[data-id]').first().data('id');
                var new_order = ui.item.index() + 1;
                var parent_id = ui.item.parent().closest('li').find('[data-id]').first().data('id') || 0;
                
                // Update order via AJAX
                $.ajax({
                    url: '<?= base_url('komoditas/update_order') ?>',
                    type: 'POST',
                    data: get_post_data({
                        id: item_id,
                        order: new_order,
                        parent_id: parent_id
                    }),
                    dataType: 'json',
                    success: function(response) {
                        update_csrf(response.csrf_hash);
                        if (response.status) {
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                            // Revert sortable
                            $('#komoditasTree').sortable('cancel');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat mengupdate urutan.'
                        });
                        // Revert sortable
                        $('#komoditasTree').sortable('cancel');
                    }
                });
            }
        });
    }
});
</script>
