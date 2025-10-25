<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <div class="callout callout-info">
            <h4><i class="fa fa-info icon"></i>Keterangan!</h4>
            <p>Kolom dengan tanda <span class="text-danger">*</span> wajib diisi.</p>
        </div>
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Pengaturan Aplikasi</h3>
            </div>
            <div class="box-body">
                <!-- Informasi Dasar Aplikasi -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="section-header">
                            <h4><i class="fa fa-cog"></i> Informasi Dasar Aplikasi</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Nama Situs -->
                        <form id="form-nama-situs">
                            <div class="form-group">
                                <label for="nama_situs">Nama Situs <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="nama_situs" class="form-control" id="nama_situs"
                                        placeholder="Nama Situs" value="<?= ce_opsi('nama_situs'); ?>" required>
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                            </div>
                        </form>

                        <!-- Tagline -->
                        <form id="form-tagline">
                            <div class="form-group">
                                <label for="tagline">Tagline</label>
                                <div class="input-group">
                                    <input type="text" name="tagline" class="form-control" id="tagline"
                                        placeholder="Tagline aplikasi" value="<?= ce_opsi('tagline'); ?>">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                            </div>
                        </form>

                        <!-- Meta Description -->
                        <form id="form-meta-description">
                            <div class="form-group">
                                <label for="meta_description">Meta Tag Deskripsi</label>
                                <div class="input-group">
                                    <textarea name="meta_description" class="form-control" id="meta_description" rows="3"
                                        placeholder="Deskripsi untuk SEO (maksimal 160 karakter)"><?= ce_opsi('meta_description'); ?></textarea>
                                    <span class="input-group-btn" style="vertical-align: top;">
                                        <button type="submit" class="btn btn-primary" style="height: 74px;"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                                <small class="text-muted">Deskripsi ini akan muncul di hasil pencarian Google</small>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <!-- Meta Keywords -->
                        <form id="form-meta-keywords">
                            <div class="form-group">
                                <label for="meta_keywords">Meta Tag Keywords</label>
                                <div class="input-group">
                                    <textarea name="meta_keywords" class="form-control" id="meta_keywords" rows="3"
                                        placeholder="Kata kunci untuk SEO, pisahkan dengan koma"><?= ce_opsi('meta_keywords'); ?></textarea>
                                    <span class="input-group-btn" style="vertical-align: top;">
                                        <button type="submit" class="btn btn-primary" style="height: 74px;"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                                <small class="text-muted">Contoh: diklat, pelatihan, ASN, pemerintah</small>
                            </div>
                        </form>

                        <!-- Nomor Kontak -->
                        <form id="form-nomor-kontak">
                            <div class="form-group">
                                <label for="nomor_kontak">Nomor Kontak</label>
                                <div class="input-group">
                                    <input type="text" name="nomor_kontak" class="form-control" id="nomor_kontak"
                                        placeholder="Nomor telepon/WhatsApp" value="<?= ce_opsi('nomor_kontak'); ?>">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                            </div>
                        </form>

                        <!-- Email -->
                        <form id="form-email">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <div class="input-group">
                                    <input type="email" name="email" class="form-control" id="email"
                                        placeholder="Email kontak" value="<?= ce_opsi('email'); ?>">
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="row">
                    <div class="col-md-12">
                        <form id="form-alamat">
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <div class="input-group">
                                    <textarea name="alamat" class="form-control" id="alamat" rows="3"
                                        placeholder="Alamat lengkap organisasi"><?= ce_opsi('alamat'); ?></textarea>
                                    <span class="input-group-btn" style="vertical-align: top;">
                                        <button type="submit" class="btn btn-primary" style="height: 74px;"><i class="fa fa-save"></i> Simpan</button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Branding & Media -->
                <div class="row" style="margin-top: 30px;">
                    <div class="col-md-12">
                        <div class="section-header">
                            <h4><i class="fa fa-image"></i> Branding & Media</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Logo -->
                    <div class="col-md-6">
                        <div class="box box-widget">
                            <div class="box-header with-border">
                                <h3 class="box-title">Logo Aplikasi</h3>
                            </div>
                            <div class="box-body">
                                <div class="current-image">
                                    <?php if(ce_opsi('logo')): ?>
                                    <div class="text-center">
                                        <img src="<?= base_url(ce_opsi('logo')); ?>" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-2">Logo saat ini</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="dropzone" id="logo-dropzone">
                                    <div class="dz-message">
                                        <h4>Klik atau drop file logo di sini</h4>
                                        <span>File yang diizinkan: JPG, PNG, GIF (Maks. 2MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Favicon -->
                    <div class="col-md-6">
                        <div class="box box-widget">
                            <div class="box-header with-border">
                                <h3 class="box-title">Favicon</h3>
                            </div>
                            <div class="box-body">
                                <div class="current-image">
                                    <?php if(ce_opsi('favicon')): ?>
                                    <div class="text-center">
                                        <img src="<?= base_url(ce_opsi('favicon')); ?>" class="img-thumbnail" style="max-height: 150px;">
                                        <p class="text-muted mt-2">Favicon saat ini</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="dropzone" id="favicon-dropzone">
                                    <div class="dz-message">
                                        <h4>Klik atau drop file favicon di sini</h4>
                                        <span>File yang diizinkan: JPG, PNG, GIF (Maks. 2MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS untuk Dropzone -->
<style>
    .dropzone {
        border: 2px dashed #3c8dbc;
        border-radius: 5px;
        background: #f9f9f9;
        min-height: 150px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
    }
    .dropzone .dz-message {
        margin: 2em 0;
    }
    .dropzone .dz-message h4 {
        margin-bottom: 10px;
        color: #3c8dbc;
    }
    .dropzone .dz-message span {
        color: #999;
    }
    .dropzone .dz-preview .dz-image {
        border-radius: 5px;
    }
    .current-image {
        margin-bottom: 20px;
    }
    .mt-2 {
        margin-top: 10px;
    }

    /* Styling untuk section headers */
    .section-header {
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .section-header h4 {
        margin: 0;
        color: #3c8dbc;
        font-weight: bold;
    }

    /* Form styling improvements */
    .form-group label {
        font-weight: 600;
        color: #333;
    }

    .form-group .text-danger {
        font-weight: bold;
    }

    .form-group small {
        font-style: italic;
    }

    /* Input group button alignment */
    .input-group .input-group-btn .btn {
        height: 34px;
        border-radius: 0 4px 4px 0;
    }

    /* Textarea button alignment */
    .input-group .input-group-btn .btn[style*="height"] {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Box styling improvements */
    .box.box-widget {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 1px solid #ddd;
    }

    .box.box-widget .box-header {
        background: #f7f7f7;
        border-bottom: 1px solid #ddd;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .input-group .input-group-btn {
            display: block;
            width: 100%;
        }

        .input-group .input-group-btn .btn {
            width: 100%;
            margin-top: 5px;
            border-radius: 4px;
        }

        .input-group .form-control {
            border-radius: 4px;
        }
    }
</style>

<!-- Script untuk Dropzone dan AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form nama situs
    document.getElementById('form-nama-situs').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var namaSitus = document.getElementById('nama_situs').value;
        
        if (!namaSitus) {
            Swal.fire({
                icon: 'error',
                title: 'Ups!',
                text: 'Nama situs tidak boleh kosong!'
            });
            return;
        }
        
        // Tampilkan loading
        Swal.fire({
            title: 'Menyimpan...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?= base_url('pengaturan/update_nama_situs'); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            // Tutup loading
            Swal.close();
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Nama situs berhasil diperbarui!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ups!',
                            text: 'Gagal memperbarui nama situs: ' + response.message
                        });
                    }
                } catch (e) {
                    console.error('Error parsing JSON:', e, xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups!',
                        text: 'Terjadi kesalahan saat memproses respons server.'
                    });
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Ups!',
                    text: 'Terjadi kesalahan saat mengirim data. Status: ' + xhr.status
                });
            }
        };
        
        xhr.onerror = function() {
            // Tutup loading
            Swal.close();
            
            Swal.fire({
                icon: 'error',
                title: 'Ups!',
                text: 'Terjadi kesalahan jaringan saat mengirim data.'
            });
        };
        
        xhr.send('nama_situs=' + encodeURIComponent(namaSitus));
    });

    // Function untuk menangani form umum
    function handleFormSubmit(formId, fieldName, endpoint) {
        document.getElementById(formId).addEventListener('submit', function(e) {
            e.preventDefault();

            var fieldValue = document.getElementById(fieldName).value;

            // Tampilkan loading
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= base_url('pengaturan/'); ?>' + endpoint, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                // Tutup loading
                Swal.close();

                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);

                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Data berhasil diperbarui!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ups!',
                                text: 'Gagal memperbarui data: ' + response.message
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e, xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Ups!',
                            text: 'Terjadi kesalahan saat memproses respons server.'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups!',
                        text: 'Terjadi kesalahan saat mengirim data. Status: ' + xhr.status
                    });
                }
            };

            xhr.onerror = function() {
                // Tutup loading
                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Ups!',
                    text: 'Terjadi kesalahan jaringan saat mengirim data.'
                });
            };

            xhr.send(fieldName + '=' + encodeURIComponent(fieldValue));
        });
    }

    // Inisialisasi form handlers
    handleFormSubmit('form-tagline', 'tagline', 'update_tagline');
    handleFormSubmit('form-meta-description', 'meta_description', 'update_meta_description');
    handleFormSubmit('form-meta-keywords', 'meta_keywords', 'update_meta_keywords');
    handleFormSubmit('form-nomor-kontak', 'nomor_kontak', 'update_nomor_kontak');
    handleFormSubmit('form-email', 'email', 'update_email');
    handleFormSubmit('form-alamat', 'alamat', 'update_alamat');
    
    // Inisialisasi Dropzone setelah halaman dimuat
    if (typeof Dropzone !== 'undefined') {
        // Konfigurasi Dropzone untuk logo
        Dropzone.autoDiscover = false;
        new Dropzone("#logo-dropzone", {
            url: "<?= base_url('pengaturan/upload_logo'); ?>",
            paramName: "logo",
            maxFilesize: 2, // MB
            acceptedFiles: "image/jpeg,image/png,image/gif",
            addRemoveLinks: true,
            dictRemoveFile: "Hapus",
            dictDefaultMessage: "Klik atau drop file logo di sini",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            init: function() {
                this.on("sending", function(file, xhr, formData) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Mengupload...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
                
                this.on("success", function(file, responseText) {
                    // Tutup loading
                    Swal.close();
                    
                    try {
                        var response;
                        if (typeof responseText === 'string') {
                            response = JSON.parse(responseText);
                        } else {
                            response = responseText;
                        }
                        
                        if (response.status === 'success') {
                            // Perbarui gambar yang ditampilkan
                            var currentImage = document.querySelector('#logo-dropzone').closest('.box-body').querySelector('.current-image');
                            currentImage.innerHTML = '<div class="text-center"><img src="' + response.file_url + '" class="img-thumbnail" style="max-height: 150px;"><p class="text-muted mt-2">Logo saat ini</p></div>';
                            
                            // Hapus preview upload
                            this.removeFile(file);
                            
                            // Tampilkan pesan sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Logo berhasil diupload!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ups!',
                                text: 'Gagal mengupload logo: ' + response.message
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e, responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Ups!',
                            text: 'Terjadi kesalahan saat memproses respons server.'
                        });
                    }
                });
                
                this.on("error", function(file, errorMessage) {
                    // Tutup loading
                    Swal.close();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups!',
                        text: 'Error: ' + errorMessage
                    });
                });
            }
        });
        
        // Konfigurasi Dropzone untuk favicon
        new Dropzone("#favicon-dropzone", {
            url: "<?= base_url('pengaturan/upload_favicon'); ?>",
            paramName: "favicon",
            maxFilesize: 2, // MB
            acceptedFiles: "image/jpeg,image/png,image/gif",
            addRemoveLinks: true,
            dictRemoveFile: "Hapus",
            dictDefaultMessage: "Klik atau drop file favicon di sini",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            init: function() {
                this.on("sending", function(file, xhr, formData) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Mengupload...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
                
                this.on("success", function(file, responseText) {
                    // Tutup loading
                    Swal.close();
                    
                    try {
                        var response;
                        if (typeof responseText === 'string') {
                            response = JSON.parse(responseText);
                        } else {
                            response = responseText;
                        }
                        
                        if (response.status === 'success') {
                            // Perbarui gambar yang ditampilkan
                            var currentImage = document.querySelector('#favicon-dropzone').closest('.box-body').querySelector('.current-image');
                            currentImage.innerHTML = '<div class="text-center"><img src="' + response.file_url + '" class="img-thumbnail" style="max-height: 150px;"><p class="text-muted mt-2">Favicon saat ini</p></div>';
                            
                            // Hapus preview upload
                            this.removeFile(file);
                            
                            // Tampilkan pesan sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Favicon berhasil diupload!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ups!',
                                text: 'Gagal mengupload favicon: ' + response.message
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e, responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Ups!',
                            text: 'Terjadi kesalahan saat memproses respons server.'
                        });
                    }
                });
                
                this.on("error", function(file, errorMessage) {
                    // Tutup loading
                    Swal.close();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Ups!',
                        text: 'Error: ' + errorMessage
                    });
                });
            }
        });
    } else {
        console.error('Dropzone.js tidak ditemukan. Pastikan library sudah dimuat.');
    }
});
</script>