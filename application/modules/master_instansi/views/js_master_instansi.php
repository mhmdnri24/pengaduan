<script>
$(document).ready(function() {
    var csrf_token_name = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function get_post_data(data) {
        data[csrf_token_name] = csrf_hash;
        return data;
    }

    function update_csrf(new_csrf_hash) {
        csrf_hash = new_csrf_hash;
        $('input[name="' + csrf_token_name + '"]').val(csrf_hash);
    }

    function showNotification(type, message) {
        toastr[type](message);
    }

    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('master_instansi/ajax_data') ?>',
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
            { data: '0', name: '0' },
            { data: '1', name: 'instansi_nama' },
            { data: '2', name: 'jenis' },
            { data: '3', name: 'cepat_kode' },
            { data: '4', name: 'instansi_id' },
            { data: '5', name: '5', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']]
    });

    $('#modalInstansi').on('hidden.bs.modal', function() {
        $('#formInstansi')[0].reset();
        $('#id').val('');
        $('#modalInstansiLabel').text('Form Instansi');
    });

    $('#formInstansi').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append(csrf_token_name, csrf_hash);
        
        var submitBtn = $('#btnSimpan');
        var originalText = submitBtn.html();
        
        $.ajax({
            url: '<?= base_url('master_instansi/save') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                update_csrf(response.csrf_hash);
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status) {
                    $('#modalInstansi').modal('hide');
                    showNotification('success', response.message);
                    table.ajax.reload(null, false);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html(originalText);
                showNotification('error', 'Terjadi kesalahan saat menyimpan data.');
            }
        });
    });

    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        var jenis = $(this).data('jenis');
        var cepat_kode = $(this).data('cepat_kode');
        var instansi_id = $(this).data('instansi_id');
        
        $('#id').val(id);
        $('#instansi_nama').val(nama);
        $('#jenis').val(jenis);
        $('#cepat_kode').val(cepat_kode);
        $('#instansi_id').val(instansi_id);
        $('#modalInstansiLabel').text('Edit Instansi');
        $('#modalInstansi').modal('show');
    });

    $(document).on('click', '.btn-hapus', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        
        Swal.fire({
            title: 'Anda Yakin?',
            text: 'Data instansi "' + nama + '" akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('master_instansi/hapus/') ?>' + id,
                    type: 'POST',
                    data: { [csrf_token_name]: csrf_hash },
                    dataType: 'json',
                    success: function(response) {
                        update_csrf(response.csrf_hash);
                        if (response.status) {
                            showNotification('success', response.message);
                            table.ajax.reload(null, false);
                        } else {
                            showNotification('error', response.message);
                        }
                    },
                    error: function() {
                        showNotification('error', 'Tidak dapat memproses permintaan.');
                    }
                });
            }
        });
    });
});
</script>
