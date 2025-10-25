<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
    .profile-pic-wrapper {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto;
        border: 3px solid #d2d6de;
    }

    .pic-holder {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .upload-file-block {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background-color: rgba(0,0,0,0.5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    .profile-pic-wrapper:hover .upload-file-block {
        opacity: 1;
    }

    #upload-file {
        display: none;
    }

    .username-check {
        margin-top: 5px;
        font-size: 11px;
    }

    .username-available {
        color: #00a65a;
    }

    .username-taken {
        color: #dd4b39;
    }
</style>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-user"></i>
                    <?php echo isset($user) ? 'Edit User' : 'Tambah User'; ?>
                </h3>
                <div class="box-tools pull-right">
                    <a href="<?= base_url('user') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" id="userForm">
                <div class="box-body">
                    <div class="row">
                        <!-- Kolom Kiri: Informasi User -->
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" id="nama" placeholder="Nama Lengkap" value="<?= $user->nama ?? '' ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="username">NIP/Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" id="username" placeholder="Masukkan NIP atau Username" value="<?= $user->username ?? '' ?>" required>
                                        <div id="username-status" class="username-check"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?= $user->email ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="no_telp">No. Telepon</label>
                                <input type="text" name="no_telp" class="form-control" id="no_telp" placeholder="No. Telepon" value="<?= $user->no_telp ?? '' ?>">
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea name="alamat" class="form-control" id="alamat" rows="3" placeholder="Alamat Lengkap"><?= $user->alamat ?? '' ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Instansi</label>
                                        <select name="id_instansi" class="form-control select2" id="id_instansi">
                                            <option value="">&mdash; Pilih Instansi &mdash;</option>
                                            <?php foreach ($instansi as $inst) {
                                                $selected = isset($user) && $inst->id_instansi == $user->id_instansi ? 'selected' : '';
                                                echo '<option value="' . $inst->id_instansi . '" ' . $selected . '>' . $inst->instansi_nama . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Unit Kerja</label>
                                        <select name="id_unitkerja" class="form-control select2" id="id_unitkerja">
                                            <option value="">&mdash; Pilih Unit Kerja &mdash;</option>
                                            <?php if (isset($unitkerja)) {
                                                foreach ($unitkerja as $unit) {
                                                    $selected = isset($user) && $unit->id_unitkerja == $user->id_unitkerja ? 'selected' : '';
                                                    echo '<option value="' . $unit->id_unitkerja . '" ' . $selected . '>' . $unit->unitkerja . '</option>';
                                                }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Hak Akses <span class="text-danger">*</span></label>
                                        <select name="id_level" class="form-control select2" id="id_level" required>
                                            <option value="">&mdash; Pilih Hak Akses &mdash;</option>
                                            <?php foreach ($level as $lev) {
                                                $selected = isset($user) && $lev->id_level == $user->id_level ? 'selected' : '';
                                                if ($lev->id_level != 1 || ($lev->id_level == 1 && $this->session->userdata('id_level') == 1))
                                                    echo '<option value="' . $lev->id_level . '" ' . $selected . '>' . $lev->level . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                                        <small class="help-block">
                                            <?php if(isset($user)): ?>
                                                Kosongkan jika tidak ingin mengubah password
                                            <?php else: ?>
                                                Wajib diisi untuk user baru
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Foto Profil -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Foto Profil</label>
                                <div class="profile-pic-wrapper">
                                    <div class="pic-holder"></div>
                                    <label for="upload-file" class="upload-file-block">
                                        <div class="text-center">
                                            <i class="fa fa-camera"></i>
                                            <div>Ganti Foto</div>
                                        </div>
                                    </label>
                                    <input type="file" name="foto" id="upload-file" accept="image/*" style="display: none;">
                                </div>
                                <p class="help-block text-center">Klik pada gambar untuk mengganti foto</p>
                            </div>
                            <div class="form-group">
                                <label>Status Akun</label><br>
                                <input type="checkbox" name="blokir" value="1" class="minimal" <?php if (isset($user) && $user->blokir == 1) echo 'checked'; ?>> Blokir user ini
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                    <a href="<?= base_url('user') ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const picHolder = document.querySelector('.pic-holder');
    const uploadFile = document.querySelector('#upload-file');

    // Set initial image
    const initialImage = "<?= isset($user) && $user->id_user ? base_url('user/foto/' . $user->id_user) : base_url('assets/img/user/default.png') ?>";
    picHolder.style.backgroundImage = `url(${initialImage})`;

    uploadFile.addEventListener('change', function(e) {
        if (e.target.files.length) {
            const reader = new FileReader();
            reader.onload = function(e) {
                picHolder.style.backgroundImage = `url(${e.target.result})`;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Handle change instansi untuk load unitkerja
    $('#id_instansi').on('change', function() {
        var instansi_id = $(this).val();
        $('#id_unitkerja').html('<option value="">&mdash; Pilih Unit Kerja &mdash;</option>').val('').trigger('change');
        if (instansi_id) {
            $.ajax({
                url: '<?= base_url("user/get_unitkerja_options") ?>',
                type: 'POST',
                data: {instansi_id: instansi_id},
                success: function(response) {
                    if (response.status) {
                        $.each(response.data, function(key, value) {
                            $('#id_unitkerja').append('<option value="' + value.id_unitkerja + '">' + value.unitkerja + '</option>');
                        });
                    }
                }
            });
        }
    });

    // Pastikan form mengirim nilai kosong untuk unitkerja jika tidak dipilih
    $('#userForm').on('submit', function() {
        if ($('#id_unitkerja').val() === '') {
            $('#id_unitkerja').val('');
        }
    });

    // Pengecekan username realtime
    let usernameTimeout;
    $('#username').on('input', function() {
        clearTimeout(usernameTimeout);
        const username = $(this).val();
        const id_user = "<?= $user->id_user ?? '' ?>";

        if (username.length > 0) {
            usernameTimeout = setTimeout(function() {
                $.ajax({
                    url: '<?= base_url("user/check_username") ?>',
                    type: 'POST',
                    data: {
                        username: username,
                        id_user: id_user
                    },
                    success: function(response) {
                        const statusDiv = $('#username-status');
                        statusDiv.removeClass('username-available username-taken');

                        if (response.status === 'available') {
                            statusDiv.addClass('username-available');
                            statusDiv.html('<i class="fa fa-check-circle"></i> ' + response.message);
                            $('#submitBtn').prop('disabled', false);
                        } else if (response.status === 'taken') {
                            statusDiv.addClass('username-taken');
                            statusDiv.html('<i class="fa fa-exclamation-triangle"></i> ' + response.message);
                            $('#submitBtn').prop('disabled', true);
                        } else {
                            statusDiv.html('');
                            $('#submitBtn').prop('disabled', false);
                        }
                    }
                });
            }, 500); // Delay 500ms
        } else {
            $('#username-status').html('');
            $('#submitBtn').prop('disabled', false);
        }
    });

    // Inisialisasi Select2
    $('.select2').select2({
        placeholder: 'Pilih...',
        allowClear: true
    });
});
</script>