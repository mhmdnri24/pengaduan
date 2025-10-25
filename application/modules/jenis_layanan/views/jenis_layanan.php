<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Jenis <small>Layanan</small></h3>
                <div class="box-tools">
                    
                    <?php if (ce_hak_akses('admin.jenis_layanan.add')): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalLayanan">
                        <i class="fa fa-plus"></i> Tambah Layanan
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Statistik -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Layanan</span>
                                <span class="info-box-number">
                                    <?php
                                    $user_level = $this->session->userdata('id_level');
                                    $user_unitkerja = $this->session->userdata('id_unitkerja');
                                    if ($user_level != 1 && !empty($user_unitkerja)) {
                                        echo $this->jenis_layanan_m->count_wajib($user_unitkerja);
                                    } else {
                                        echo $this->jenis_layanan_m->count_wajib();
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Layanan Wajib</span>
                                <span class="info-box-number"><?= $this->jenis_layanan_m->count_wajib() ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Layanan Opsional</span>
                                <span class="info-box-number"><?= $this->jenis_layanan_m->count_opsional() ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-power-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Layanan Aktif</span>
                                <span class="info-box-number"><?= $this->db->where('layanan_status', 1)->count_all_results('layanan_jenis') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_kategori">
                            <option value="">-- Semua Kategori --</option>
                            <?php
                            $kategori_list = $this->kategori_layanan_m->kategori_aktif();
                            foreach ($kategori_list as $kat) {
                                echo '<option value="'.$kat->kategori_id.'">'.$kat->kategori_nama.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_wajib">
                            <option value="">-- Semua Status Wajib --</option>
                            <option value="1">Wajib</option>
                            <option value="0">Opsional</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_status">
                            <option value="">-- Semua Status Aktif --</option>
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <?php if ($this->session->userdata('id_level') == 1): ?>
                    <div class="col-md-3">
                        <select class="form-control select2" id="filter_unitkerja">
                            <option value="">-- Semua Unit Kerja --</option>
                            <?php if (isset($unitkerja_options) && !empty($unitkerja_options)): ?>
                                <?php foreach ($unitkerja_options as $unit): ?>
                                    <option value="<?= $unit->id_unitkerja ?>"><?= $unit->unitkerja ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="col-md-1"></div>
                    <?php endif; ?>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-default" id="btn-reset">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th>Kategori</th>
                                <th>Nama Layanan</th>
                                <!-- Kolom Biaya dihapus -->
                                <th>Status Wajib</th>
                                <th>Status Aktif</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Layanan -->
<div class="modal fade" id="modalLayanan" tabindex="-1" role="dialog" aria-labelledby="modalLayananLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalLayananLabel">Form Layanan</h4>
            </div>
            <form id="formLayanan">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_nama">Nama Layanan <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="layanan_nama" name="layanan_nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_kategori">Kategori Layanan <span class="text-red">*</span></label>
                                <select class="form-control select2" id="layanan_kategori" name="layanan_kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php
                                    $kategori_list = $this->kategori_layanan_m->kategori_aktif();
                                    foreach ($kategori_list as $kat) {
                                        echo '<option value="'.$kat->kategori_id.'">'.$kat->kategori_nama.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <?php if ($this->session->userdata('id_level') == 1): ?>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_unitkerja">Unit Kerja <span class="text-red">*</span></label>
                                <select class="form-control select2" id="layanan_unitkerja" name="layanan_unitkerja" required>
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <?php if (isset($unitkerja_options) && !empty($unitkerja_options)): ?>
                                        <?php foreach ($unitkerja_options as $unit): ?>
                                            <option value="<?= $unit->id_unitkerja ?>"><?= $unit->unitkerja ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" style="opacity: 0.5; pointer-events: none;">
                                <label for="layanan_unitkerja_user">Unit Kerja Anda</label>
                                <input type="text" class="form-control" id="layanan_unitkerja_user" value="<?= $this->session->userdata('unitkerja_nama') ?>" readonly>
                                <input type="hidden" id="layanan_unitkerja_hidden" name="layanan_unitkerja" value="<?= $this->session->userdata('id_unitkerja') ?>">
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="layanan_unitkerja_user">Unit Kerja</label>
                                <select class="form-control select2" id="layanan_unitkerja_user" name="layanan_unitkerja" disabled>
                                    <option value="">-- Pilih Unit Kerja --</option>
                                    <?php
                                    // Debug session data
                                    $user_unitkerja_id = $this->session->userdata('id_unitkerja');
                                    $user_unitkerja_nama = $this->session->userdata('unitkerja_nama');
                                    $user_level = $this->session->userdata('id_level');

                                    // Debug: Log session data
                                    error_log('View session - id_level: ' . $user_level . ', id_unitkerja: ' . $user_unitkerja_id . ', unitkerja_nama: ' . $user_unitkerja_nama);
                                    error_log('Unitkerja options count: ' . (isset($unitkerja_options) ? count($unitkerja_options) : 'null'));

                                    if (isset($unitkerja_options) && !empty($unitkerja_options)) {
                                        foreach ($unitkerja_options as $unit) {
                                            $selected = ($unit->id_unitkerja == $user_unitkerja_id) ? 'selected' : '';
                                            echo '<option value="' . $unit->id_unitkerja . '" ' . $selected . '>' . $unit->unitkerja . '</option>';
                                        }
                                    } elseif ($user_unitkerja_id) {
                                        echo '<option value="' . $user_unitkerja_id . '" selected>' . ($user_unitkerja_nama ?: 'Unit Kerja ID: ' . $user_unitkerja_id) . '</option>';
                                    } else {
                                        echo '<option value="" disabled>Unit kerja tidak ditemukan - Session: ' . $user_level . '/' . $user_unitkerja_id . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="help-block">Unit kerja tidak dapat diubah</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="layanan_deskripsi">Deskripsi Layanan</label>
                        <textarea class="form-control" id="layanan_deskripsi" name="layanan_deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_durasi">Durasi Layanan</label>
                                <input type="text" class="form-control" id="layanan_durasi" name="layanan_durasi" placeholder="Contoh: 1 hari, 2 minggu">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_urutan">Urutan</label>
                                <input type="number" class="form-control" id="layanan_urutan" name="layanan_urutan" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="layanan_contoh">Contoh Layanan</label>
                                <input type="text" class="form-control" id="layanan_contoh" name="layanan_contoh">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="layanan_wajib" name="layanan_wajib" value="1">
                                        Layanan Wajib
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="layanan_status" name="layanan_status" value="1" checked>
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

