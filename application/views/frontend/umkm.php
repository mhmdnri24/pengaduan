<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-orange-600 to-orange-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Data <span class="text-yellow-300">UMKM</span>
            </h1>
            <p class="text-xl text-orange-100 mb-8">
                Direktori usaha mikro, kecil, dan menengah di wilayah
            </p>
            
            <!-- Breadcrumb -->
            <nav class="flex justify-center" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-orange-200">
                    <li>
                        <a href="<?= base_url() ?>" class="hover:text-white transition-colors">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </li>
                    <li class="text-white font-medium">UMKM</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <form method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
            <!-- Search -->
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Pencarian
                </label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="<?= isset($filters['search']) ? $filters['search'] : '' ?>"
                       placeholder="Cari berdasarkan nama usaha atau pemilik..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            </div>

            <!-- Pasar Filter -->
            <div class="flex-1">
                <label for="pasar" class="block text-sm font-medium text-gray-700 mb-2">
                    Pasar
                </label>
                <select id="pasar" 
                        name="pasar" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    <option value="">Semua Pasar</option>
                    <?php if (isset($pasar_list) && !empty($pasar_list)): ?>
                        <?php foreach ($pasar_list as $pasar): ?>
                            <option value="<?= $pasar->pasar_id ?>" 
                                    <?= (isset($filters['pasar']) && $filters['pasar'] == $pasar->pasar_id) ? 'selected' : '' ?>>
                                <?= $pasar->pasar_nama ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filter Button -->
            <div>
                <button type="submit" 
                        class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- UMKM Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Data UMKM (Usaha Mikro, Kecil, dan Menengah)
                    </h2>
                    <p class="text-gray-600">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['pasar']))): ?>
                            Hasil pencarian untuk:
                            <?php if (!empty($filters['search'])): ?>
                                "<span class="font-medium"><?= $filters['search'] ?></span>"
                            <?php endif; ?>
                            <?php if (!empty($filters['pasar'])): ?>
                                <?php 
                                $pasar_name = 'Pasar Terpilih';
                                if (isset($pasar_list)) {
                                    foreach ($pasar_list as $pasar) {
                                        if ($pasar->pasar_id == $filters['pasar']) {
                                            $pasar_name = $pasar->pasar_nama;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                Pasar: <span class="font-medium"><?= $pasar_name ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            Menampilkan semua data UMKM
                        <?php endif; ?>
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        <span>Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- UMKM Grid -->
        <?php if (isset($umkm_list) && !empty($umkm_list)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($umkm_list as $umkm): ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-4 text-white">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 rounded-full p-2">
                                    <i data-lucide="store" class="w-6 h-6"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold truncate"><?= $umkm->nama_usaha ?></h3>
                                    <p class="text-orange-100 text-sm"><?= $umkm->nama_lengkap ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <!-- Business Info -->
                            <div class="space-y-3 mb-4">
                                <?php if (isset($umkm->jenis_usaha) && !empty($umkm->jenis_usaha)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= $umkm->jenis_usaha ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($umkm->pasar_nama) && !empty($umkm->pasar_nama)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= $umkm->pasar_nama ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($umkm->no_wa_hp) && !empty($umkm->no_wa_hp)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= $umkm->no_wa_hp ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($umkm->jenis_kelamin) && !empty($umkm->jenis_kelamin)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= ucfirst($umkm->jenis_kelamin) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Address -->
                            <?php if (isset($umkm->alamat) && !empty($umkm->alamat)): ?>
                                <div class="mb-4">
                                    <div class="flex items-start space-x-2">
                                        <i data-lucide="home" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                        <div class="text-sm text-gray-600">
                                            <p><?= $umkm->alamat ?></p>
                                            <?php if (isset($umkm->nama_kelurahan) || isset($umkm->nama_kecamatan)): ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?= isset($umkm->nama_kelurahan) ? $umkm->nama_kelurahan : '' ?>
                                                    <?= (isset($umkm->nama_kelurahan) && isset($umkm->nama_kecamatan)) ? ', ' : '' ?>
                                                    <?= isset($umkm->nama_kecamatan) ? $umkm->nama_kecamatan : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Business Details -->
                            <?php if (isset($umkm->modal_usaha) || isset($umkm->omzet_bulanan) || isset($umkm->jumlah_karyawan)): ?>
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Detail Usaha</h4>
                                    <div class="grid grid-cols-1 gap-2">
                                        <?php if (isset($umkm->modal_usaha) && !empty($umkm->modal_usaha) && $umkm->modal_usaha > 0): ?>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500">Modal:</span>
                                                <span class="text-xs font-medium text-gray-700">
                                                    Rp <?= number_format($umkm->modal_usaha, 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($umkm->omzet_bulanan) && !empty($umkm->omzet_bulanan) && $umkm->omzet_bulanan > 0): ?>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500">Omzet/Bulan:</span>
                                                <span class="text-xs font-medium text-green-600">
                                                    Rp <?= number_format($umkm->omzet_bulanan, 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($umkm->jumlah_karyawan) && !empty($umkm->jumlah_karyawan) && $umkm->jumlah_karyawan > 0): ?>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500">Karyawan:</span>
                                                <span class="text-xs font-medium text-gray-700">
                                                    <?= $umkm->jumlah_karyawan ?> orang
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Registration Info -->
                            <?php if (isset($umkm->tanggal_daftar) || isset($umkm->status_usaha)): ?>
                                <div class="border-t pt-4 mt-4">
                                    <div class="flex justify-between items-center text-xs">
                                        <?php if (isset($umkm->tanggal_daftar) && !empty($umkm->tanggal_daftar)): ?>
                                            <span class="text-gray-500">
                                                Terdaftar: <?= date('M Y', strtotime($umkm->tanggal_daftar)) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($umkm->status_usaha) && !empty($umkm->status_usaha)): ?>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                <?php 
                                                switch(strtolower($umkm->status_usaha)) {
                                                    case 'aktif':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'tidak aktif':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?= ucfirst($umkm->status_usaha) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if (isset($pagination) && !empty($pagination)): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-center">
                        <?= $pagination ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <div class="max-w-md mx-auto">
                    <i data-lucide="store-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Tidak Ada Data UMKM
                    </h3>
                    <p class="text-gray-600 mb-6">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['pasar']))): ?>
                            Tidak ditemukan data UMKM yang sesuai dengan kriteria pencarian.
                        <?php else: ?>
                            Belum ada data UMKM yang tersedia.
                        <?php endif; ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="<?= base_url('umkm') ?>" 
                           class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Lihat Semua
                        </a>
                        <button onclick="document.getElementById('search').value = ''; document.getElementById('pasar').value = ''; document.querySelector('form').submit();" 
                                class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Information Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-orange-50 rounded-lg p-8">
            <div class="flex items-start space-x-4">
                <div class="bg-orange-100 rounded-lg p-3">
                    <i data-lucide="info" class="w-6 h-6 text-orange-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Informasi Data UMKM
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>• <strong>UMKM:</strong> Usaha Mikro, Kecil, dan Menengah yang terdaftar di wilayah</p>
                        <p>• Data mencakup informasi usaha, pemilik, lokasi, dan detail operasional</p>
                        <p>• Status "Aktif" menunjukkan usaha yang masih beroperasi</p>
                        <p>• Informasi modal dan omzet bersifat perkiraan berdasarkan data pendaftaran</p>
                        <p>• Untuk kerjasama atau informasi lebih lanjut, hubungi kontak yang tersedia</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


