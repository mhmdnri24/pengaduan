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
                Detail <span class="text-yellow-300">Pelaporan</span>
            </h1>
            <p class="text-xl text-green-100 mb-8">
                <?= isset($pelaporan->kode_laporan) ? $pelaporan->kode_laporan : 'Detail Pelaporan' ?>
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
                    <li>
                        <a href="<?= base_url('pelaporan') ?>" class="hover:text-white transition-colors">
                            Pelaporan
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </li>
                    <li class="text-white font-medium">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<?php if (isset($pelaporan)): ?>
<!-- Report Details Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Report Info Card -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                <?= $pelaporan->judul ?>
                            </h2>
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <span class="flex items-center space-x-1">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                    <span><?= date('d F Y', strtotime($pelaporan->created_at)) ?></span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span><?= date('H:i', strtotime($pelaporan->created_at)) ?></span>
                                </span>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            <?php 
                            switch($pelaporan->status) {
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
                            <?= $pelaporan->status ?>
                        </span>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Deskripsi</h3>
                        <div class="prose max-w-none text-gray-700">
                            <?= nl2br(htmlspecialchars($pelaporan->deskripsi)) ?>
                        </div>
                    </div>

                    <!-- Location -->
                    <?php if (isset($pelaporan->alamat) && !empty($pelaporan->alamat)): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Lokasi</h3>
                            <div class="flex items-start space-x-2">
                                <i data-lucide="map-pin" class="w-5 h-5 text-gray-400 mt-0.5"></i>
                                <p class="text-gray-700"><?= $pelaporan->alamat ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Files -->
                    <?php if (isset($files) && !empty($files)): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Lampiran</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <?php foreach ($files as $file): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-blue-100 rounded-lg p-2">
                                                <?php if (in_array(strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                    <i data-lucide="image" class="w-5 h-5 text-blue-600"></i>
                                                <?php else: ?>
                                                    <i data-lucide="file" class="w-5 h-5 text-blue-600"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <?= $file->file_name ?>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    <?= date('d M Y', strtotime($file->uploaded_at)) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline -->
                <?php if (isset($timeline) && !empty($timeline)): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Timeline Pelaporan</h3>
                        <div class="space-y-6">
                            <?php foreach ($timeline as $index => $item): ?>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                                            <?php 
                                            switch($item->status) {
                                                case 'BARU':
                                                    echo 'bg-blue-100 text-blue-600';
                                                    break;
                                                case 'PROSES':
                                                    echo 'bg-yellow-100 text-yellow-600';
                                                    break;
                                                case 'SELESAI':
                                                    echo 'bg-green-100 text-green-600';
                                                    break;
                                                case 'DITOLAK':
                                                    echo 'bg-red-100 text-red-600';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-600';
                                            }
                                            ?>">
                                            <i data-lucide="circle" class="w-4 h-4"></i>
                                        </div>
                                        <?php if ($index < count($timeline) - 1): ?>
                                            <div class="w-0.5 h-6 bg-gray-200 ml-4 mt-2"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">
                                                Status: <?= $item->status ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?= date('d M Y H:i', strtotime($item->created_at)) ?>
                                            </p>
                                        </div>
                                        <?php if (isset($item->keterangan) && !empty($item->keterangan)): ?>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <?= $item->keterangan ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Report Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pelaporan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Kode Laporan</label>
                            <p class="text-sm text-gray-900 font-mono"><?= $pelaporan->kode_laporan ?></p>
                        </div>
                        
                        <?php if (isset($pelaporan->nama_kategori)): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Kategori</label>
                                <p class="text-sm text-gray-900"><?= $pelaporan->nama_kategori ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($pelaporan->pelapor_nama)): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Pelapor</label>
                                <p class="text-sm text-gray-900"><?= $pelaporan->pelapor_nama ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($pelaporan->prioritas)): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Prioritas</label>
                                <p class="text-sm text-gray-900"><?= ucfirst($pelaporan->prioritas) ?></p>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Dibuat</label>
                            <p class="text-sm text-gray-900"><?= date('d F Y H:i', strtotime($pelaporan->created_at)) ?></p>
                        </div>

                        <?php if (isset($pelaporan->updated_at)): ?>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Terakhir Diperbarui</label>
                                <p class="text-sm text-gray-900"><?= date('d F Y H:i', strtotime($pelaporan->updated_at)) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                    <div class="space-y-3">
                        <a href="<?= base_url('pelaporan') ?>" 
                           class="block w-full bg-gray-600 hover:bg-gray-700 text-white text-center py-2 rounded-lg font-medium transition-colors duration-200">
                            <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>
                            Kembali ke Daftar
                        </a>
                        
                        <button onclick="window.print()" 
                                class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 text-center py-2 rounded-lg font-medium transition-colors duration-200">
                            <i data-lucide="printer" class="w-4 h-4 inline mr-2"></i>
                            Cetak
                        </button>
                    </div>
                </div>

                <!-- Help -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <div class="flex items-start space-x-3">
                        <div class="bg-blue-100 rounded-lg p-2">
                            <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">
                                Butuh Bantuan?
                            </h4>
                            <p class="text-sm text-gray-600">
                                Jika Anda memiliki pertanyaan tentang status pelaporan ini, 
                                silakan hubungi layanan masyarakat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Error State -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="max-w-md mx-auto">
                <i data-lucide="file-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    Pelaporan Tidak Ditemukan
                </h3>
                <p class="text-gray-600 mb-6">
                    Pelaporan yang Anda cari tidak ditemukan atau tidak dapat diakses.
                </p>
                <a href="<?= base_url('pelaporan') ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 inline-flex items-center space-x-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Kembali ke Daftar</span>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


