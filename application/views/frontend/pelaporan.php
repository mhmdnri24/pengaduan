<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-green-600 to-green-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Pelaporan <span class="text-yellow-300">Masyarakat</span>
            </h1>
            <p class="text-xl text-green-100 mb-8">
                Pantau status pelaporan dan pengaduan dari masyarakat
            </p>
            
            <!-- Breadcrumb -->
            <nav class="flex justify-center" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-green-200">
                    <li>
                        <a href="<?= base_url() ?>" class="hover:text-white transition-colors">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </li>
                    <li class="text-white font-medium">Pelaporan</li>
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
                       placeholder="Cari berdasarkan judul, kode, atau alamat..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Status Filter -->
            <div class="flex-1">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Status
                </label>
                <select id="status" 
                        name="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <?php if (isset($status_list)): ?>
                        <?php foreach ($status_list as $status): ?>
                            <option value="<?= $status ?>" 
                                    <?= (isset($filters['status']) && $filters['status'] == $status) ? 'selected' : '' ?>>
                                <?= $status ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="flex-1">
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori
                </label>
                <select id="kategori" 
                        name="kategori" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Kategori</option>
                    <?php if (isset($kategori_list) && !empty($kategori_list)): ?>
                        <?php foreach ($kategori_list as $kategori): ?>
                            <option value="<?= $kategori->pelaporan_nama ?>" 
                                    <?= (isset($filters['kategori']) && $filters['kategori'] == $kategori->pelaporan_nama) ? 'selected' : '' ?>>
                                <?= $kategori->pelaporan_nama ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filter Button -->
            <div>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Reports Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Data Pelaporan Masyarakat
                    </h2>
                    <p class="text-gray-600">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['kategori']))): ?>
                            Hasil pencarian untuk:
                            <?php if (!empty($filters['search'])): ?>
                                "<span class="font-medium"><?= $filters['search'] ?></span>"
                            <?php endif; ?>
                            <?php if (!empty($filters['status'])): ?>
                                Status: <span class="font-medium"><?= $filters['status'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($filters['kategori'])): ?>
                                Kategori: <span class="font-medium"><?= $filters['kategori'] ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            Menampilkan semua pelaporan masyarakat
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

        <!-- Reports Grid -->
        <?php if (isset($pelaporan_list) && !empty($pelaporan_list)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($pelaporan_list as $laporan): ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <!-- Status Badge -->
                        <div class="p-4 pb-0">
                            <div class="flex justify-between items-start mb-3">
                                <span class="px-3 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch($laporan->status) {
                                        case 'BARU':
                                            echo 'bg-blue-100 text-blue-800';
                                            break;
                                        case 'PROSES':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'SELESAI':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'DITOLAK':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        default:
                                            echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= $laporan->status ?>
                                </span>
                                <span class="text-xs text-gray-500">
                                    <?= $laporan->kode_laporan ?>
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4 pt-0">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                <?= $laporan->judul ?>
                            </h3>
                            
                            <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                <?= substr($laporan->deskripsi, 0, 150) . (strlen($laporan->deskripsi) > 150 ? '...' : '') ?>
                            </p>

                            <!-- Category -->
                            <?php if (isset($laporan->nama_kategori)): ?>
                                <div class="flex items-center space-x-2 mb-3">
                                    <i data-lucide="tag" class="w-4 h-4 text-gray-400"></i>
                                    <span class="text-sm text-gray-600"><?= $laporan->nama_kategori ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Location -->
                            <?php if (isset($laporan->alamat) && !empty($laporan->alamat)): ?>
                                <div class="flex items-center space-x-2 mb-3">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                    <span class="text-sm text-gray-600 line-clamp-1"><?= $laporan->alamat ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Reporter -->
                            <?php if (isset($laporan->pelapor_nama)): ?>
                                <div class="flex items-center space-x-2 mb-4">
                                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    <span class="text-sm text-gray-600"><?= $laporan->pelapor_nama ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Date -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center space-x-1">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span><?= date('d M Y', strtotime($laporan->created_at)) ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span><?= date('H:i', strtotime($laporan->created_at)) ?></span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="<?= base_url('pelaporan/detail/' . $laporan->id) ?>" 
                               class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg font-medium transition-colors duration-200">
                                Lihat Detail
                            </a>
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
                    <i data-lucide="file-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Tidak Ada Pelaporan
                    </h3>
                    <p class="text-gray-600 mb-6">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['kategori']))): ?>
                            Tidak ditemukan pelaporan yang sesuai dengan kriteria pencarian.
                        <?php else: ?>
                            Belum ada pelaporan dari masyarakat.
                        <?php endif; ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="<?= base_url('pelaporan') ?>" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Lihat Semua
                        </a>
                        <button onclick="document.getElementById('search').value = ''; document.getElementById('status').value = ''; document.getElementById('kategori').value = ''; document.querySelector('form').submit();" 
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
        <div class="bg-green-50 rounded-lg p-8">
            <div class="flex items-start space-x-4">
                <div class="bg-green-100 rounded-lg p-3">
                    <i data-lucide="info" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Informasi Pelaporan
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>• <strong>BARU:</strong> Pelaporan baru diterima dan sedang menunggu verifikasi</p>
                        <p>• <strong>PROSES:</strong> Pelaporan sedang ditindaklanjuti oleh pihak terkait</p>
                        <p>• <strong>SELESAI:</strong> Pelaporan telah selesai ditangani</p>
                        <p>• <strong>DITOLAK:</strong> Pelaporan tidak dapat diproses karena tidak memenuhi syarat</p>
                        <p>• Klik "Lihat Detail" untuk melihat informasi lengkap dan timeline pelaporan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


