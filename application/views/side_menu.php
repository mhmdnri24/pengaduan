<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="user-panel">
  <div class="pull-left image">
    <img src="<?= base_url('user/foto/' . $usr->id_user) ?>" class="img-circle" alt="avatar">
  </div>
  <div class="pull-left info">
    <p><?= $usr->nama;?></p>
    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
  </div>
</div>
<ul class="sidebar-menu" data-widget="tree">
  <li class="header divider">NAVIGASI</li>
  <?php 
  // Ambil menu dari database
  $dynamic_menu = ce_get_dynamic_menu();
  
  // Jika menu dari database kosong, gunakan menu dari konfigurasi statis
  if (empty($dynamic_menu)) {
    echo ce_nav_menu($this->config->item('nav_menu'));
  } else {
    echo ce_nav_menu($dynamic_menu);
  }
  ?>
</ul>