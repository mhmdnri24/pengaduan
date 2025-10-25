<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    .table-hover .parent-row {
        background-color: #f9f9f9;
        font-weight: bold;
    }
    .child-row td:first-child {
        padding-left: 30px;
    }
    .child-row .fa {
        margin-left: 15px;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
        <?= ce_msg('info'); ?>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Pengaturan <small>Menu</small></h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalMenu">
                        <i class="fa fa-plus"></i> Tambah Menu
                    </button>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Label</th>
                            <th>Icon</th>
                            <th>URL</th>
                            <th>Kode Akses</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($menus)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada menu yang ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($menus as $menu): ?>
                                <tr class="parent-row">
                                    <td><?= $menu->menu_label; ?></td>
                                    <td><i class="<?= $menu->menu_icon; ?>"></i> <?= $menu->menu_icon; ?></td>
                                    <td><?= $menu->menu_url; ?></td>
                                    <td><?= $menu->menu_access_code; ?></td>
                                    <td>
                                        <?php if ($menu->menu_status == 1): ?>
                                            <span class="label label-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="<?= $menu->menu_id; ?>" data-label="<?= $menu->menu_label; ?>" data-icon="<?= $menu->menu_icon; ?>" data-url="<?= $menu->menu_url; ?>" data-parent="<?= $menu->menu_parent_id; ?>" data-order="<?= $menu->menu_order; ?>" data-access="<?= $menu->menu_access_code; ?>" data-status="<?= $menu->menu_status; ?>"><i class="fa fa-edit"></i></button>
                                            <button type="button" class="btn btn-sm btn-info btn-copy" data-id="<?= $menu->menu_id; ?>" title="Copy Menu"><i class="fa fa-copy"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="<?= $menu->menu_id; ?>"><i class="fa fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (!empty($menu->sub_menu)): ?>
                                    <?php foreach ($menu->sub_menu as $submenu): ?>
                                        <tr class="child-row">
                                            <td><i class="fa fa-long-arrow-right"></i> <?= $submenu->menu_label; ?></td>
                                            <td><i class="<?= $submenu->menu_icon; ?>"></i> <?= $submenu->menu_icon; ?></td>
                                            <td><?= $submenu->menu_url; ?></td>
                                            <td><?= $submenu->menu_access_code; ?></td>
                                            <td>
                                                <?php if ($submenu->menu_status == 1): ?>
                                                    <span class="label label-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="label label-danger">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-warning btn-edit" data-id="<?= $submenu->menu_id; ?>" data-label="<?= $submenu->menu_label; ?>" data-icon="<?= $submenu->menu_icon; ?>" data-url="<?= $submenu->menu_url; ?>" data-parent="<?= $submenu->menu_parent_id; ?>" data-order="<?= $submenu->menu_order; ?>" data-access="<?= $submenu->menu_access_code; ?>" data-status="<?= $submenu->menu_status; ?>"><i class="fa fa-edit"></i></button>
                                                    <button type="button" class="btn btn-sm btn-info btn-copy" data-id="<?= $submenu->menu_id; ?>" title="Copy Menu"><i class="fa fa-copy"></i></button>
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="<?= $submenu->menu_id; ?>"><i class="fa fa-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Menu -->
<div class="modal fade" id="modalMenu">
    <div class="modal-dialog">
        <?= form_open('', ['id' => 'formMenu']); ?>
            <input type="hidden" name="id" id="id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalMenuLabel">Form Menu</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="menu_label">Label Menu <span class="text-danger">*</span></label>
                        <input type="text" name="menu_label" class="form-control" id="menu_label" required>
                    </div>
                    <div class="form-group">
                        <label for="menu_icon">Icon (Font Awesome)</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i id="icon_preview" class="fa fa-home"></i></span>
                            <input type="text" name="menu_icon" class="form-control" id="menu_icon" placeholder="fa fa-home">
                        </div>
                        <small class="text-muted">Lihat daftar icon di <a href="https://fontawesome.com/v4/icons/" target="_blank">Font Awesome</a></small>
                    </div>
                    <div class="form-group">
                        <label for="menu_url">URL</label>
                        <input type="text" name="menu_url" class="form-control" id="menu_url" placeholder="contoh: beranda">
                    </div>
                    <div class="form-group">
                        <label for="menu_parent_id">Parent Menu</label>
                        <select name="menu_parent_id" id="menu_parent_id" class="form-control">
                            <option value="0">-- Menu Utama --</option>
                            <?php foreach ($parent_menus as $parent): ?>
                            <option value="<?= $parent->menu_id ?>"><?= $parent->menu_label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="menu_order">Urutan</label>
                        <input type="number" name="menu_order" class="form-control" id="menu_order" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label for="menu_access_code">Kode Akses</label>
                        <input type="text" name="menu_access_code" class="form-control" id="menu_access_code" placeholder="admin.menu.view">
                    </div>
                    <div class="form-group">
                        <label for="menu_status">Status</label>
                        <select name="menu_status" id="menu_status" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                </div>
            </div>
        <?= form_close(); ?>
    </div>
</div>