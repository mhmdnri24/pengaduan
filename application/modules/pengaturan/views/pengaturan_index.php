<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="<?= ($active_tab ?? '') == 'aplikasi' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/aplikasi') ?>">Aplikasi</a></li>
        <li class="<?= ($active_tab ?? '') == 'api' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/api') ?>">API</a></li>
        <li class="<?= ($active_tab ?? '') == 'api_internal' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/api_internal') ?>">API Internal</a></li>
        <li class="<?= ($active_tab ?? '') == 'api_monitoring' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/api_monitoring') ?>">Monitoring API</a></li>
        <li class="<?= ($active_tab ?? '') == 'bantuan' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/bantuan') ?>">Bantuan</a></li>
        <li class="<?= ($active_tab ?? '') == 'kontak_darurat' ? 'active' : '' ?>"><a href="<?= base_url('pengaturan/kontak_darurat') ?>">Kontak Darurat</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="<?= $active_tab ?? '' ?>">
            <?php
            // Muat konten dari tab yang aktif
            if (!empty($active_tab)) {
                $this->load->view('pengaturan_' . $active_tab);
            }
            ?>
        </div>
    </div>
</div>