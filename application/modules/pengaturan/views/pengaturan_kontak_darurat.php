<?php
$kontak_darurat = ce_get_kontak_darurat();
?>

<style>
.icon-preview {
    display: inline-block;
    width: 30px;
    height: 30px;
    text-align: center;
    line-height: 30px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;
    margin-right: 10px;
}

.icon-selector {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    display: none;
    position: absolute;
    background: white;
    z-index: 1000;
    width: 300px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.icon-option {
    display: inline-block;
    width: 40px;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 2px;
    cursor: pointer;
    transition: all 0.2s;
}

.icon-option:hover {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.icon-selector-toggle {
    cursor: pointer;
    padding: 6px 12px;
    border: 1px solid #ccc;
    background: white;
    border-radius: 4px;
}

.icon-selector-toggle:hover {
    border-color: #007bff;
}
</style>

<form action="<?= base_url('pengaturan/kontak_darurat') ?>" method="post">
    <div class="box-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <strong>Informasi:</strong> Kontak darurat akan ditampilkan di aplikasi mobile untuk keadaan darurat.
        </div>

        <div id="kontak-darurat-wrapper">
            <?php if (empty($kontak_darurat)): ?>
                <div class="row kontak-darurat-item">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nama Kontak <span class="text-red">*</span></label>
                            <input type="text" name="kontak_darurat[0][nama_kontak]" class="form-control" placeholder="Masukkan Nama Kontak" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>No. Kontak <span class="text-red">*</span></label>
                            <input type="text" name="kontak_darurat[0][no_kontak]" class="form-control" placeholder="Masukkan Nomor Telepon" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ikon</label>
                            <div class="input-group">
                                <div class="icon-preview" id="icon-preview-0">
                                    <i class="fa fa-phone"></i>
                                </div>
                                <input type="hidden" name="kontak_darurat[0][ikon]" value="fa-phone" id="icon-input-0">
                                <button type="button" class="btn btn-default icon-selector-toggle" onclick="toggleIconSelector(0)">
                                    <i class="fa fa-list"></i> Pilih Ikon
                                </button>
                            </div>
                            <div class="icon-selector" id="icon-selector-0">
                                <?php
                                $icons = [
                                    'fa-phone', 'fa-ambulance', 'fa-hospital-o', 'fa-medkit', 'fa-user-md',
                                    'fa-stethoscope', 'fa-heartbeat', 'fa-plus-square', 'fa-h-square',
                                    'fa-shield', 'fa-exclamation-triangle', 'fa-bell', 'fa-warning'
                                ];
                                foreach ($icons as $icon): ?>
                                    <div class="icon-option" onclick="selectIcon(0, '<?= $icon ?>')">
                                        <i class="fa <?= $icon ?>"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-kontak-darurat" style="display:none;"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($kontak_darurat as $i => $k): ?>
                <div class="row kontak-darurat-item">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nama Kontak <span class="text-red">*</span></label>
                            <input type="text" name="kontak_darurat[<?= $i ?>][nama_kontak]" class="form-control" placeholder="Masukkan Nama Kontak" value="<?= htmlspecialchars($k['nama_kontak']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>No. Kontak <span class="text-red">*</span></label>
                            <input type="text" name="kontak_darurat[<?= $i ?>][no_kontak]" class="form-control" placeholder="Masukkan Nomor Telepon" value="<?= htmlspecialchars($k['no_kontak']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ikon</label>
                            <div class="input-group">
                                <div class="icon-preview" id="icon-preview-<?= $i ?>">
                                    <i class="fa <?= htmlspecialchars($k['ikon'] ?? 'fa-phone') ?>"></i>
                                </div>
                                <input type="hidden" name="kontak_darurat[<?= $i ?>][ikon]" value="<?= htmlspecialchars($k['ikon'] ?? 'fa-phone') ?>" id="icon-input-<?= $i ?>">
                                <button type="button" class="btn btn-default icon-selector-toggle" onclick="toggleIconSelector(<?= $i ?>)">
                                    <i class="fa fa-list"></i> Pilih Ikon
                                </button>
                            </div>
                            <div class="icon-selector" id="icon-selector-<?= $i ?>">
                                <?php
                                $icons = [
                                    'fa-phone', 'fa-ambulance', 'fa-hospital-o', 'fa-medkit', 'fa-user-md',
                                    'fa-stethoscope', 'fa-heartbeat', 'fa-plus-square', 'fa-h-square',
                                    'fa-shield', 'fa-exclamation-triangle', 'fa-bell', 'fa-warning'
                                ];
                                foreach ($icons as $icon): ?>
                                    <div class="icon-option" onclick="selectIcon(<?= $i ?>, '<?= $icon ?>')">
                                        <i class="fa <?= $icon ?>"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm remove-kontak-darurat" <?= $i == 0 ? 'style="display:none;"' : '' ?>><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="add-kontak-darurat" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Kontak Darurat</button>
    </div>
    <div class="box-footer">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Pengaturan</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var wrapper = document.getElementById('kontak-darurat-wrapper');
    var addBtn = document.getElementById('add-kontak-darurat');
    var index = <?= count($kontak_darurat) ?>;

    function updateRemoveButtons() {
        var items = wrapper.getElementsByClassName('kontak-darurat-item');
        for (var i = 0; i < items.length; i++) {
            var removeBtn = items[i].querySelector('.remove-kontak-darurat');
            if (items.length > 1) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        }
    }

    addBtn.addEventListener('click', function() {
        var newItem = document.createElement('div');
        newItem.className = 'row kontak-darurat-item';
        newItem.innerHTML = `
            <div class="col-md-4">
                <div class="form-group">
                    <label>Nama Kontak <span class="text-red">*</span></label>
                    <input type="text" name="kontak_darurat[${index}][nama_kontak]" class="form-control" placeholder="Masukkan Nama Kontak" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>No. Kontak <span class="text-red">*</span></label>
                    <input type="text" name="kontak_darurat[${index}][no_kontak]" class="form-control" placeholder="Masukkan Nomor Telepon" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Ikon</label>
                    <div class="input-group">
                        <div class="icon-preview" id="icon-preview-${index}">
                            <i class="fa fa-phone"></i>
                        </div>
                        <input type="hidden" name="kontak_darurat[${index}][ikon]" value="fa-phone" id="icon-input-${index}">
                        <button type="button" class="btn btn-default icon-selector-toggle" onclick="toggleIconSelector(${index})">
                            <i class="fa fa-list"></i> Pilih Ikon
                        </button>
                    </div>
                    <div class="icon-selector" id="icon-selector-${index}">
                        <?php
                        $icons = [
                            'fa-phone', 'fa-ambulance', 'fa-hospital-o', 'fa-medkit', 'fa-user-md',
                            'fa-stethoscope', 'fa-heartbeat', 'fa-plus-square', 'fa-h-square',
                            'fa-shield', 'fa-exclamation-triangle', 'fa-bell', 'fa-warning'
                        ];
                        foreach ($icons as $icon): ?>
                            <div class="icon-option" onclick="selectIcon(${index}, '<?= $icon ?>')">
                                <i class="fa <?= $icon ?>"></i>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-kontak-darurat"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        `;
        wrapper.appendChild(newItem);
        index++;
        updateRemoveButtons();
    });

    wrapper.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-kontak-darurat') || e.target.parentElement.classList.contains('remove-kontak-darurat')) {
            var item = e.target.closest('.kontak-darurat-item');
            if (wrapper.getElementsByClassName('kontak-darurat-item').length > 1) {
                item.remove();
                updateRemoveButtons();
            }
        }
    });

    updateRemoveButtons();
});

// Close icon selectors when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.icon-selector') && !e.target.closest('.icon-selector-toggle')) {
        var selectors = document.getElementsByClassName('icon-selector');
        for (var i = 0; i < selectors.length; i++) {
            selectors[i].style.display = 'none';
        }
    }
});

function toggleIconSelector(index) {
    var selector = document.getElementById('icon-selector-' + index);
    var isVisible = selector.style.display === 'block';

    // Hide all selectors first
    var allSelectors = document.getElementsByClassName('icon-selector');
    for (var i = 0; i < allSelectors.length; i++) {
        allSelectors[i].style.display = 'none';
    }

    // Show/hide the clicked selector
    if (!isVisible) {
        selector.style.display = 'block';
    }
}

function selectIcon(index, iconClass) {
    document.getElementById('icon-preview-' + index).innerHTML = '<i class="fa ' + iconClass + '"></i>';
    document.getElementById('icon-input-' + index).value = iconClass;
    document.getElementById('icon-selector-' + index).style.display = 'none';
}
</script>