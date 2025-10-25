<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="detail-section">
    <h4><i class="fa fa-user"></i> Informasi Pedagang</h4>
    <div class="row">
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="30%"><strong>Nama</strong></td>
                    <td>: <?= $pedagang->nama_lengkap ?></td>
                </tr>
                <tr>
                    <td><strong>NIK</strong></td>
                    <td>: <?= $pedagang->nik ?></td>
                </tr>
                <tr>
                    <td><strong>Telepon</strong></td>
                    <td>: <?= $pedagang->no_telpon ?: '-' ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-borderless">
                <tr>
                    <td width="30%"><strong>Pasar</strong></td>
                    <td>: <?= $pedagang->pasar_nama ?></td>
                </tr>
                <tr>
                    <td><strong>Blok</strong></td>
                    <td>: <?= $pedagang->pasar_blok_nama ?> (<?= $pedagang->pasar_blok_nomor ?>)</td>
                </tr>
                <tr>
                    <td><strong>Jenis</strong></td>
                    <td>: <?= $pedagang->pasar_jenis_nama ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="detail-section">
    <h4><i class="fa fa-calendar"></i> Periode Pembayaran: <?= ce_nama_bulan($bulan) ?> <?= $tahun ?></h4>
    
    <?php if ($pembayaran): ?>
        <!-- Sudah Ada Pembayaran -->
        <div class="alert alert-<?= ($pembayaran->retribusi_status == 'LUNAS') ? 'success' : 'warning' ?>">
            <h5><i class="fa fa-info-circle"></i> Status: 
                <?php
                switch($pembayaran->retribusi_status) {
                    case 'LUNAS':
                        echo '<span class="label label-success">LUNAS</span>';
                        break;
                    case 'TERLAMBAT':
                        echo '<span class="label label-warning">TERLAMBAT</span>';
                        break;
                    case 'SEBAGIAN':
                        echo '<span class="label label-info">SEBAGIAN</span>';
                        break;
                    default:
                        echo '<span class="label label-default">BELUM BAYAR</span>';
                }
                ?>
            </h5>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Tanggal Bayar</strong></td>
                        <td><?= date('d/m/Y', strtotime($pembayaran->retribusi_tanggal)) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Nominal</strong></td>
                        <td>Rp <?= number_format($pembayaran->retribusi_nominal, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Denda</strong></td>
                        <td>Rp <?= number_format($pembayaran->retribusi_denda, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="info">
                        <td><strong>Total</strong></td>
                        <td><strong>Rp <?= number_format($pembayaran->retribusi_total, 0, ',', '.') ?></strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Metode Bayar</strong></td>
                        <td><?= $pembayaran->retribusi_metode_bayar ?></td>
                    </tr>
                    <tr>
                        <td><strong>Keterangan</strong></td>
                        <td><?= $pembayaran->retribusi_keterangan ?: '-' ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <?php if (ce_cek_hak_akses('admin.retribusi_pasar.update')): ?>
        <div class="text-right">
            <button class="btn btn-warning btn-sm" onclick="editPembayaran(<?= $pembayaran->retribusi_id ?>)">
                <i class="fa fa-edit"></i> Edit Pembayaran
            </button>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Belum Ada Pembayaran -->
        <div class="alert alert-warning">
            <h5><i class="fa fa-exclamation-triangle"></i> Belum Ada Pembayaran</h5>
            <p>Pedagang ini belum melakukan pembayaran retribusi untuk periode <?= ce_nama_bulan($bulan) ?> <?= $tahun ?>.</p>
        </div>
        
        <?php if ($tarif): ?>
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fa fa-money"></i> Informasi Tarif</h5>
                <table class="table table-striped">
                    <tr>
                        <td><strong>Tarif Harian</strong></td>
                        <td>Rp <?= number_format($tarif->tarif_harian, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tarif Bulanan</strong></td>
                        <td>Rp <?= number_format($tarif->tarif_bulanan, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Denda (%/hari)</strong></td>
                        <td><?= $tarif->tarif_denda_persen ?>%</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5><i class="fa fa-calculator"></i> Kalkulasi</h5>
                <div class="well">
                    <p><strong>Nominal yang harus dibayar:</strong></p>
                    <h4 class="text-primary">Rp <?= number_format($tarif->tarif_harian, 0, ',', '.') ?></h4>
                    <small class="text-muted">* Berdasarkan tarif harian</small>
                    
                    <?php
                    // Hitung denda jika terlambat
                    $tanggal_jatuh_tempo = date('Y-m-05'); // Asumsi jatuh tempo tanggal 5
                    if (date('Y-m-d') > $tanggal_jatuh_tempo) {
                        $hari_terlambat = (strtotime(date('Y-m-d')) - strtotime($tanggal_jatuh_tempo)) / (60*60*24);
                        $denda = ($tarif->tarif_harian * $tarif->tarif_denda_persen / 100) * $hari_terlambat;
                        ?>
                        <hr>
                        <p class="text-warning"><strong>Denda Keterlambatan:</strong></p>
                        <p class="text-warning">Rp <?= number_format($denda, 0, ',', '.') ?> (<?= floor($hari_terlambat) ?> hari)</p>
                        <h4 class="text-danger"><strong>Total: Rp <?= number_format($tarif->tarif_harian + $denda, 0, ',', '.') ?></strong></h4>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php if (ce_cek_hak_akses('admin.retribusi_pasar.add')): ?>
        <div class="text-center" style="margin-top: 20px;">
            <button class="btn btn-success btn-lg" onclick="bayarRetribusiHarian()">
                <i class="fa fa-money"></i> Bayar Retribusi Harian
            </button>
            <p class="text-muted" style="margin-top: 10px;">
                <small>Klik tombol di atas untuk melakukan pembayaran retribusi harian</small>
            </p>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Riwayat Pembayaran -->
<div class="detail-section">
    <h4><i class="fa fa-history"></i> Riwayat Pembayaran</h4>
    <div id="riwayat-pembayaran">
        <div class="text-center text-muted" style="padding: 20px;">
            <i class="fa fa-spinner fa-spin"></i> Memuat riwayat pembayaran...
        </div>
    </div>
</div>

<script>
// Set data untuk form pembayaran
window.currentPedagang = <?= json_encode($pedagang) ?>;
window.currentTarif = <?= json_encode($tarif) ?>;
window.currentPembayaran = <?= json_encode($pembayaran) ?>;
window.currentPeriode = {bulan: <?= $bulan ?>, tahun: <?= $tahun ?>};

// Load riwayat pembayaran
loadRiwayatPembayaran(<?= $pedagang->id ?>);

// Enable tombol bayar jika belum ada pembayaran
<?php if (!$pembayaran && ce_cek_hak_akses('admin.retribusi_pasar.add')): ?>
$('#btn-bayar').prop('disabled', false);
<?php endif; ?>

// Enable tombol print jika sudah ada pembayaran
<?php if ($pembayaran): ?>
$('#btn-print').prop('disabled', false);
<?php endif; ?>
</script>
