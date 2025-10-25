<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<script>
$(document).ready(function() {
    // CSRF Token
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    
    function update_csrf() {
        $.get('<?= base_url('get_csrf'); ?>', function(result) {
            csrfHash = result.csrf_hash;
        });
    }

    function get_post_data(data) {
        data = data || {};
        return data;
    }

    $('#modalMenu').on('hidden.bs.modal', function() {
        $('#formMenu')[0].reset();
        $('#id').val('');
        $('#icon_preview').attr('class', 'fa fa-home');
        $('#menu_parent_id').val(0).trigger('change');
    });

    $('#menu_icon').on('keyup', function() {
        var iconClass = $(this).val();
        $('#icon_preview').attr('class', iconClass || 'fa fa-home');
    });

    $('#formMenu').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var btn = $('#btnSimpan');
        btn.prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('menu/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.status) {
                    $('#modalMenu').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message })
                        .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan.' }); },
            complete: function() { btn.prop('disabled', false); }
        });
    });

    $('.btn-edit').on('click', function() {
        var data = $(this).data();
        $('#id').val(data.id);
        $('#menu_label').val(data.label);
        $('#menu_icon').val(data.icon).trigger('keyup');
        $('#menu_url').val(data.url);
        $('#menu_parent_id').val(data.parent).trigger('change');
        $('#menu_order').val(data.order);
        $('#menu_access_code').val(data.access);
        $('#menu_status').val(data.status).trigger('change');
        $('#modalMenuLabel').text('Edit Menu');
        $('#modalMenu').modal('show');
    });

    $('.btn-delete').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('menu/delete') ?>',
                    type: 'POST',
                    data: get_post_data({ id: id }),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                        }
                    }
                });
            }
        });
    });

    $('.btn-copy').on('click', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Copy Menu?',
            text: "Menu akan dicopy dengan label '(Copy)' di akhir nama",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Ya, copy!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('menu/copy') ?>',
                    type: 'POST',
                    data: get_post_data({ id: id }),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal!', text: response.message });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat copy menu.' });
                    }
                });
            }
        });
    });

    // Initialize select2 when the modal is shown
    $('#modalMenu').on('show.bs.modal', function () {
        $('#menu_parent_id, #menu_status').select2({
            dropdownParent: $(this),
            width: '100%'
        });
    });
});
</script>