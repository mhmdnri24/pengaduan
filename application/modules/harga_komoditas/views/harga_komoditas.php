<div class="row">
    <!-- Statistik Cards -->
    <div class="col-md-3">
        <div class="info-box bg-aqua">
            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Data Hari Ini</span>
                <span class="info-box-number"><?= $statistik['total_harga_hari_ini'] ?? 0; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Komoditas Dipantau</span>
                <span class="info-box-number"><?= $statistik['total_komoditas_dipantau'] ?? 0; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-yellow">
            <span class="info-box-icon"><i class="fa fa-building"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pasar Dipantau</span>
                <span class="info-box-number"><?= $statistik['total_pasar_dipantau'] ?? 0; ?></span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-red">
            <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Volatil Tinggi</span>
                <span class="info-box-number"><?= count($top_volatil ?? []); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Form Input Harga -->
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Input Harga Komoditas</h3>
            </div>
            <form id="formHarga" method="post">
                <div class="box-body">
                    <input type="hidden" id="harga_id" name="harga_id">
                    
                    <div class="form-group">
                        <label for="harga_tanggal">Tanggal <span class="text-red">*</span></label>
                        <input type="date" class="form-control" id="harga_tanggal" name="harga_tanggal" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori_komoditas">Kategori Komoditas</label>
                        <select class="form-control select2" id="kategori_komoditas" style="width: 100%;">
                            <option value="">Pilih Kategori</option>
                            <?php foreach ($komoditas_tree as $kategori): ?>
                                <option value="<?= $kategori->komoditas_id; ?>"><?= $kategori->komoditas_nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="komoditas_id">Komoditas <span class="text-red">*</span></label>
                        <select class="form-control select2" id="komoditas_id" name="komoditas_id" style="width: 100%;" required>
                            <option value="">Pilih Komoditas</option>
                            <?php foreach ($komoditas_list as $komoditas): ?>
                                <option value="<?= $komoditas->komoditas_id; ?>" data-satuan="<?= $komoditas->komoditas_satuan; ?>">
                                    <?= $komoditas->komoditas_nama; ?>
                                    <?php if ($komoditas->komoditas_satuan): ?>
                                        (<?= $komoditas->komoditas_satuan; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pasar_id">Pasar <span class="text-red">*</span></label>
                        <select class="form-control select2" id="pasar_id" name="pasar_id" style="width: 100%;" required>
                            <option value="">Pilih Pasar</option>
                            <?php foreach ($pasar_list as $pasar): ?>
                                <option value="<?= $pasar->pasar_id; ?>"><?= $pasar->pasar_nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="harga_beli">Harga Beli (Rp)</label>
                                <input type="number" class="form-control" id="harga_beli" name="harga_beli" min="0" step="100" placeholder="Opsional">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="harga_jual">Harga Jual (Rp) <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual" min="0" step="100" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stok_tersedia">Stok <span class="text-red">*</span></label>
                                <select class="form-control" id="stok_tersedia" name="stok_tersedia" required>
                                    <option value="TERSEDIA">Tersedia</option>
                                    <option value="TERBATAS">Terbatas</option>
                                    <option value="KOSONG">Kosong</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kualitas">Kualitas <span class="text-red">*</span></label>
                                <select class="form-control" id="kualitas" name="kualitas" required>
                                    <option value="BAIK">Baik</option>
                                    <option value="SEDANG">Sedang</option>
                                    <option value="KURANG">Kurang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan">Catatan</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga_status">Status</label>
                        <select class="form-control" id="harga_status" name="harga_status">
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>
                    
                    <div id="duplicate-warning" class="alert alert-warning" style="display: none;">
                        <i class="fa fa-warning"></i> Data harga untuk tanggal, komoditas, dan pasar ini sudah ada!
                    </div>
                </div>
                
                <div class="box-footer">
                    <button type="button" class="btn btn-default" id="btn-reset">Reset</button>
                    <button type="submit" class="btn btn-primary pull-right" id="btn-save">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Harga Tertinggi & Terendah -->
        <?php if (!empty($statistik['harga_tertinggi']) || !empty($statistik['harga_terendah'])): ?>
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Harga Hari Ini</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($statistik['harga_tertinggi'])): ?>
                <div class="callout callout-danger">
                    <h5><i class="fa fa-arrow-up"></i> Harga Tertinggi</h5>
                    <strong><?= $statistik['harga_tertinggi']->komoditas_nama; ?></strong><br>
                    <small><?= $statistik['harga_tertinggi']->pasar_nama; ?></small><br>
                    <span class="text-red"><strong>Rp <?= number_format($statistik['harga_tertinggi']->harga_rata_rata, 0, ',', '.'); ?></strong></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($statistik['harga_terendah'])): ?>
                <div class="callout callout-success">
                    <h5><i class="fa fa-arrow-down"></i> Harga Terendah</h5>
                    <strong><?= $statistik['harga_terendah']->komoditas_nama; ?></strong><br>
                    <small><?= $statistik['harga_terendah']->pasar_nama; ?></small><br>
                    <span class="text-green"><strong>Rp <?= number_format($statistik['harga_terendah']->harga_rata_rata, 0, ',', '.'); ?></strong></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Tabel Data Harga -->
    <div class="col-md-8">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Data Harga Komoditas</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-info btn-sm" id="btn-filter">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-export">
                        <i class="fa fa-download"></i> Export
                    </button>
                </div>
            </div>
            
            <!-- Filter Panel -->
            <div class="box-body" id="filter-panel" style="display: none; border-bottom: 1px solid #f0f0f0; margin-bottom: 10px;">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Dari</label>
                            <input type="date" class="form-control" id="filter_tanggal_dari" value="<?= date('Y-m-01'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Sampai</label>
                            <input type="date" class="form-control" id="filter_tanggal_sampai" value="<?= date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Komoditas</label>
                            <select class="form-control select2" id="filter_komoditas" style="width: 100%;">
                                <option value="">Semua Komoditas</option>
                                <?php foreach ($komoditas_list as $komoditas): ?>
                                    <option value="<?= $komoditas->komoditas_id; ?>"><?= $komoditas->komoditas_nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Pasar</label>
                            <select class="form-control select2" id="filter_pasar" style="width: 100%;">
                                <option value="">Semua Pasar</option>
                                <?php foreach ($pasar_list as $pasar): ?>
                                    <option value="<?= $pasar->pasar_id; ?>"><?= $pasar->pasar_nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-apply-filter">
                            <i class="fa fa-search"></i> Terapkan Filter
                        </button>
                        <button type="button" class="btn btn-default btn-sm" id="btn-reset-filter">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="box-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="10%">Tanggal</th>
                                <th width="15%">Komoditas</th>
                                <th width="12%">Kategori</th>
                                <th width="12%">Pasar</th>
                                <th width="10%">Harga Beli</th>
                                <th width="10%">Harga Jual</th>
                                <th width="10%">Rata-rata</th>
                                <th width="8%">Stok</th>
                                <th width="8%">Kualitas</th>
                                <th width="6%">Status</th>
                                <th width="10%">Aksi</th>
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

<!-- Modal untuk Chart/Analisis -->
<div class="modal fade" id="modalChart" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Analisis Harga Komoditas</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Komoditas</label>
                            <select class="form-control select2" id="chart_komoditas" style="width: 100%;">
                                <option value="">Pilih Komoditas</option>
                                <?php foreach ($komoditas_list as $komoditas): ?>
                                    <option value="<?= $komoditas->komoditas_id; ?>"><?= $komoditas->komoditas_nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pasar (Opsional)</label>
                            <select class="form-control select2" id="chart_pasar" style="width: 100%;">
                                <option value="">Semua Pasar</option>
                                <?php foreach ($pasar_list as $pasar): ?>
                                    <option value="<?= $pasar->pasar_id; ?>"><?= $pasar->pasar_nama; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" id="btn-load-chart">
                            <i class="fa fa-line-chart"></i> Tampilkan Grafik
                        </button>
                    </div>
                </div>
                <hr>
                <div id="chart-container">
                    <canvas id="priceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
