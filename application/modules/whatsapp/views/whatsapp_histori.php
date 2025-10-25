<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Histori Pengiriman WhatsApp</h3>
            </div>
            <div class="box-body">
                <!-- Filter -->
                <div class="row">
                    <div class="col-md-12">
                        <form action="<?= site_url('whatsapp/histori'); ?>" method="get" class="form-horizontal">
                            <div class="form-group">
                                <div class="col-sm-3">
                                    <input type="text" name="keyword" class="form-control" placeholder="Cari nomor/nama/pesan" 
                                        value="<?= isset($filter['keyword']) ? $filter['keyword'] : ''; ?>">
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" name="date_from" class="form-control datepicker" placeholder="Dari Tanggal" 
                                        value="<?= isset($filter['date_from']) ? $filter['date_from'] : ''; ?>">
                                </div>
                                <div class="col-sm-2">
                                    <input type="text" name="date_to" class="form-control datepicker" placeholder="Sampai Tanggal" 
                                        value="<?= isset($filter['date_to']) ? $filter['date_to'] : ''; ?>">
                                </div>
                                <div class="col-sm-2">
                                    <select name="status" class="form-control">
                                        <option value="">-- Status --</option>
                                        <option value="true" <?= (isset($filter['status']) && $filter['status'] === 'true') ? 'selected' : ''; ?>>Berhasil</option>
                                        <option value="false" <?= (isset($filter['status']) && $filter['status'] === 'false') ? 'selected' : ''; ?>>Gagal</option>
                                        <option value="unknown" <?= (isset($filter['status']) && $filter['status'] === 'unknown') ? 'selected' : ''; ?>>Tidak Diketahui</option>
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <select name="cabang_id" class="form-control">
                                        <option value="">-- Semua Cabang --</option>
                                        <?php if (isset($cabang) && is_array($cabang)): ?>
                                        <?php foreach ($cabang as $c): ?>
                                        <option value="<?= $c->id_cabang; ?>" <?= (isset($filter['cabang_id']) && $filter['cabang_id'] == $c->id_cabang) ? 'selected' : ''; ?>>
                                            <?= $c->nama_cabang; ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tabel Histori -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Waktu</th>
                                <th width="15%">Nomor Tujuan</th>
                                <th width="20%">Nama Tujuan</th>
                                <th>Pesan</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = $this->input->get('page') ? $this->input->get('page') + 1 : 1;
                            foreach ($histori as $h): 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($h->waktu_kirim)); ?></td>
                                <td><?= $h->nomor_tujuan; ?></td>
                                <td><?= $h->nama_tujuan ?: '-'; ?></td>
                                <td><?= nl2br(substr($h->pesan, 0, 100)) . (strlen($h->pesan) > 100 ? '...' : ''); ?></td>
                                <td>
                                    <?php if ($h->status === 'true'): ?>
                                        <span class="label label-success">Berhasil</span>
                                    <?php elseif ($h->status === 'false'): ?>
                                        <span class="label label-danger">Gagal</span>
                                    <?php else: ?>
                                        <span class="label label-default">Tidak Diketahui</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('whatsapp/detail_histori/'.$h->id); ?>" class="btn btn-xs btn-info">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($histori)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data histori</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="text-center">
                    <?= $this->pagination->create_links(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
});
</script> 