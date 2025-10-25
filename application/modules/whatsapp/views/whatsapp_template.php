<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Template WhatsApp</h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('whatsapp/tambah_template'); ?>" class="btn btn-sm btn-success">
                        <i class="fa fa-plus"></i> Tambah Template
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Nama Template</th>
                                <th width="15%">Kode</th>
                                <th>Isi Template</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($templates as $template): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $template->nama; ?></td>
                                <td><code><?= $template->kode; ?></code></td>
                                <td><?= nl2br(substr($template->isi_template, 0, 100)) . (strlen($template->isi_template) > 100 ? '...' : ''); ?></td>
                                <td>
                                    <a href="<?= site_url('whatsapp/edit_template/'.$template->id); ?>" class="btn btn-xs btn-warning">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= site_url('whatsapp/hapus_template/'.$template->id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
                                        <i class="fa fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data template</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.datatable').DataTable();
});
</script> 