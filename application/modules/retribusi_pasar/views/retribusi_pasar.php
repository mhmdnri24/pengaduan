<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
.pedagang-item {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}
.pedagang-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}
.pedagang-item.active {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}
.list-group-item-heading {
    margin-bottom: 5px;
    font-weight: 600;
}
.status-card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}
.status-card:hover {
    transform: translateY(-2px);
}
.detail-section {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.filter-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        
        <!-- Statistik Dashboard -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua status-card">
                    <div class="inner">
                        <h3><?= $statistik->total_transaksi ?: 0 ?></h3>
                        <p>Total Transaksi</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green status-card">
                    <div class="inner">
                        <h3><?= $statistik->lunas ?: 0 ?></h3>
                        <p>Sudah Lunas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow status-card">
                    <div class="inner">
                        <h3><?= count($pedagang_belum_bayar) ?></h3>
                        <p>Belum Bayar</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red status-card">
                    <div class="inner">
                        <h3>Rp <?= number_format($statistik->total_pendapatan ?: 0, 0, ',', '.') ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Kolom Kiri: Daftar Pedagang -->
    <div class="col-md-4">
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Daftar Pedagang</h3>
                <div class="box-tools">
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalFilter">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row">
                    <div class="col-xs-6">
                        <select id="filter-periode-bulan" class="form-control form-control-sm">
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == date('n')) ? 'selected' : '' ?>>
                                    <?= ce_nama_bulan($i) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-xs-6">
                        <select id="filter-periode-tahun" class="form-control form-control-sm">
                            <?php for($i = date('Y'); $i >= date('Y')-2; $i--): ?>
                                <option value="<?= $i ?>" <?= ($i == date('Y')) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-xs-12">
                        <div class="input-group">
                            <input type="text" id="search-pedagang" class="form-control form-control-sm" placeholder="Cari pedagang...">
                            <span class="input-group-btn">
                                <button class="btn btn-default btn-sm" type="button" id="btn-search">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="box-body no-padding" style="max-height: 600px; overflow-y: auto;">
                <div class="list-group" id="pedagang-list">
                    <div class="text-center text-muted" style="padding: 50px;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p>Memuat data pedagang...</p>
                    </div>
                </div>
            </div>
            
            <div class="box-footer">
                <small class="text-muted">
                    <i class="fa fa-info-circle"></i> Klik pedagang untuk melihat detail pembayaran
                </small>
            </div>
        </div>
    </div>
    
    <!-- Kolom Kanan: Detail Pembayaran -->
    <div class="col-md-8">
        <div class="box box-solid box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-credit-card"></i> Detail Pembayaran Retribusi</h3>
                <div class="box-tools">
                    <button class="btn btn-success btn-sm" id="btn-bayar" disabled>
                        <i class="fa fa-money"></i> Bayar Retribusi
                    </button>
                    <button class="btn btn-info btn-sm" id="btn-print" disabled>
                        <i class="fa fa-print"></i> Print Kwitansi
                    </button>
                </div>
            </div>
            <div class="box-body" id="detail-content">
                <div class="text-center text-muted" style="padding: 80px 20px;">
                    <i class="fa fa-user-o fa-4x" style="color: #ddd;"></i>
                    <h4 style="color: #999; margin-top: 20px;">Pilih Pedagang</h4>
                    <p style="color: #bbb;">Pilih pedagang dari daftar di sebelah kiri untuk melihat detail pembayaran retribusi</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Filter -->
<div class="modal fade" id="modalFilter" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-filter"></i> Filter Pedagang</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Pasar</label>
                    <select id="filter-pasar" class="form-control">
                        <option value="">Semua Pasar</option>
                        <?php foreach($pasar_options as $pasar): ?>
                            <option value="<?= $pasar->pasar_id ?>"><?= $pasar->pasar_nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Pasar</label>
                    <select id="filter-jenis" class="form-control">
                        <option value="">Semua Jenis</option>
                        <?php foreach($jenis_pasar_options as $jenis): ?>
                            <option value="<?= $jenis->pasar_jenis_id ?>"><?= $jenis->pasar_nama ?> - <?= $jenis->pasar_jenis_nama ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Pembayaran</label>
                    <select id="filter-status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="LUNAS">Lunas</option>
                        <option value="BELUM_BAYAR">Belum Bayar</option>
                        <option value="TERLAMBAT">Terlambat</option>
                        <option value="SEBAGIAN">Sebagian</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-apply-filter">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="modalPembayaran" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="formPembayaran">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-money"></i> Form Pembayaran Retribusi</h4>
                </div>
                <div class="modal-body">
                    <div id="form-pembayaran-content">
                        <!-- Content akan dimuat via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pembayaran Retribusi Harian -->
<div class="modal fade" id="modalPembayaranHarian" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formPembayaranHarian">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-money"></i> Pembayaran Retribusi Harian</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Pedagang</label>
                                <input type="text" id="pedagang-nama-display" class="form-control" readonly>
                                <input type="hidden" id="pedagang-id-bayar" name="pedagang_id">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Pembayaran</label>
                                <input type="date" id="tanggal-bayar" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Metode Pembayaran</label>
                                <select id="metode-bayar" name="metode_bayar" class="form-control">
                                    <option value="TUNAI">Tunai</option>
                                    <option value="TRANSFER">Transfer</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="LAINNYA">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Pasar</label>
                                <input type="text" id="jenis-pasar-display" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tarif Harian</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="text" id="tarif-harian-display" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Keterangan <small class="text-muted">(opsional)</small></label>
                                <textarea id="keterangan-bayar" name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Informasi:</strong> Pembayaran retribusi harian akan dicatat untuk tanggal yang dipilih.
                        Pastikan pedagang belum melakukan pembayaran untuk tanggal tersebut.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-money"></i> Bayar Retribusi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
