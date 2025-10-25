<?php
$bantuan_kontak = ce_opsi('bantuan_kontak');
$kontak = !empty($bantuan_kontak) ? json_decode($bantuan_kontak, true) : [];
?>

<form action="<?= base_url('pengaturan/bantuan') ?>" method="post">
    <div class="box-body">
        <div id="kontak-wrapper">
            <?php if (empty($kontak)): ?>
                <div class="row kontak-item">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="kontak[0][nama]" class="form-control" placeholder="Masukkan Nama Lengkap">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>No. WA/Telepon</label>
                            <input type="text" name="kontak[0][telepon]" class="form-control" placeholder="Masukkan Nomor Telepon">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="kontak[0][email]" class="form-control" placeholder="Masukkan Alamat Email">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" name="kontak[0][jabatan]" class="form-control" placeholder="Masukkan Jabatan">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-kontak" style="display:none;"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($kontak as $i => $k): ?>
                <div class="row kontak-item">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="kontak[<?= $i ?>][nama]" class="form-control" placeholder="Masukkan Nama Lengkap" value="<?= htmlspecialchars($k['nama']) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>No. WA/Telepon</label>
                            <input type="text" name="kontak[<?= $i ?>][telepon]" class="form-control" placeholder="Masukkan Nomor Telepon" value="<?= htmlspecialchars($k['telepon']) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="kontak[<?= $i ?>][email]" class="form-control" placeholder="Masukkan Alamat Email" value="<?= htmlspecialchars($k['email']) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" name="kontak[<?= $i ?>][jabatan]" class="form-control" placeholder="Masukkan Jabatan" value="<?= htmlspecialchars($k['jabatan']) ?>">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-kontak" <?= $i == 0 ? 'style="display:none;"' : '' ?>><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-kontak" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Kontak</button>
    </div>
    <div class="box-footer">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Pengaturan</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var wrapper = document.getElementById('kontak-wrapper');
    var addBtn = document.getElementById('add-kontak');
    var index = <?= count($kontak) ?>;

    function updateRemoveButtons() {
        var items = wrapper.getElementsByClassName('kontak-item');
        for (var i = 0; i < items.length; i++) {
            var removeBtn = items[i].querySelector('.remove-kontak');
            if (items.length > 1) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        }
    }

    addBtn.addEventListener('click', function() {
        var newItem = document.createElement('div');
        newItem.className = 'row kontak-item';
        newItem.innerHTML = `
            <div class="col-md-3">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="kontak[${index}][nama]" class="form-control" placeholder="Masukkan Nama Lengkap">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>No. WA/Telepon</label>
                    <input type="text" name="kontak[${index}][telepon]" class="form-control" placeholder="Masukkan Nomor Telepon">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="kontak[${index}][email]" class="form-control" placeholder="Masukkan Alamat Email">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="kontak[${index}][jabatan]" class="form-control" placeholder="Masukkan Jabatan">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-kontak"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
        wrapper.appendChild(newItem);
        index++;
        updateRemoveButtons();
    });

    wrapper.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-kontak') || e.target.parentElement.classList.contains('remove-kontak')) {
            var item = e.target.closest('.kontak-item');
            if (wrapper.getElementsByClassName('kontak-item').length > 1) {
                item.remove();
                updateRemoveButtons();
            }
        }
    });

    updateRemoveButtons();
});
</script>