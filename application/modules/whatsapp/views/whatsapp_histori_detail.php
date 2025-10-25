<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detail Histori WhatsApp</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('whatsapp/histori'); ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <th width="30%">ID</th>
                                <td><?= $histori->id; ?></td>
                            </tr>
                            <tr>
                                <th>Nomor Tujuan</th>
                                <td><?= $histori->nomor_tujuan; ?></td>
                            </tr>
                            <tr>
                                <th>Nama Tujuan</th>
                                <td><?= $histori->nama_tujuan ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Waktu Kirim</th>
                                <td><?= date('d/m/Y H:i:s', strtotime($histori->waktu_kirim)); ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if ($histori->status === 'true'): ?>
                                        <span class="label label-success">Berhasil</span>
                                    <?php elseif ($histori->status === 'false'): ?>
                                        <span class="label label-danger">Gagal</span>
                                    <?php else: ?>
                                        <span class="label label-default">Tidak Diketahui</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (isset($template)): ?>
                            <tr>
                                <th>Template</th>
                                <td>
                                    <a href="<?= site_url('whatsapp/edit_template/' . $template->id); ?>">
                                        <?= $template->nama; ?> (<?= $template->kode; ?>)
                                    </a>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($histori->cabang_id): ?>
                            <tr>
                                <th>Cabang</th>
                                <td>
                                    <?php
                                    $this->load->model('cabang/cabang_m');
                                    $cabang = $this->cabang_m->cabang_by_id($histori->cabang_id);
                                    echo $cabang ? $cabang->nama_cabang : 'Cabang #' . $histori->cabang_id;
                                    ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title">Isi Pesan</h3>
                            </div>
                            <div class="box-body">
                                <div class="well well-sm" style="white-space: pre-wrap;"><?= $histori->pesan; ?></div>
                            </div>
                        </div>
                        
                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title">Respon API</h3>
                            </div>
                            <div class="box-body">
                                <div class="well well-sm" style="white-space: pre-wrap;"><?php
                                    if ($histori->response_data) {
                                        $response = json_decode($histori->response_data);
                                        echo json_encode($response, JSON_PRETTY_PRINT);
                                    } else {
                                        echo 'Tidak ada data respon';
                                    }
                                ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 