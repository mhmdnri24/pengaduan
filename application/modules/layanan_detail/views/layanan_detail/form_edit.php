<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title">Edit Layanan</h4>
</div>
<form id="formLayanan" method="post">
    <div class="modal-body">
        <input type="hidden" name="id" id="layanan_id">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="layanan_nama">Nama Layanan <span class="text-red">*</span></label>
                    <input type="text" class="form-control" id="layanan_nama" name="layanan_nama" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="layanan_durasi">Durasi (hari)</label>
                    <input type="number" class="form-control" id="layanan_durasi" name="layanan_durasi" min="1" value="1">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="layanan_kategori">Kategori</label>
                    <select class="form-control select2" id="layanan_kategori" name="layanan_kategori">
                        <option value="">Pilih Kategori</option>
                        <?php
                        // Ambil daftar kategori dari database
                        $kategori_list = $this->db->select('DISTINCT(layanan_kategori) as kategori')
                                                 ->where('layanan_kategori !=', '')
                                                 ->order_by('layanan_kategori', 'asc')
                                                 ->get('layanan_jenis')->result();
                        foreach ($kategori_list as $kat) {
                            echo '<option value="' . htmlspecialchars($kat->kategori) . '">' . htmlspecialchars($kat->kategori) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="layanan_status">Status</label>
                    <select class="form-control" id="layanan_status" name="layanan_status">
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="layanan_deskripsi">Deskripsi Layanan</label>
            <textarea class="form-control" id="layanan_deskripsi" name="layanan_deskripsi" rows="4" placeholder="Jelaskan detail layanan..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 jika diperlukan
    if ($('.select2').length > 0 && typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            placeholder: 'Pilih opsi...',
            allowClear: true
        });
    }

    // Handle form submission
    $('#formLayanan').off('submit').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: '<?= base_url('layanan_detail/save') ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#formLayanan button[type="submit"]').prop('disabled', true).text('Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    $('#modalLayanan').modal('hide');
                    // Reload halaman untuk melihat perubahan
                    location.reload();
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message);
                    } else {
                        alert(response.message);
                    }
                }

                // Update CSRF token
                if (response.csrf_token_name && response.csrf_hash) {
                    $('input[name="' + response.csrf_token_name + '"]').val(response.csrf_hash);
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
                $('#formLayanan button[type="submit"]').prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });
});
</script>