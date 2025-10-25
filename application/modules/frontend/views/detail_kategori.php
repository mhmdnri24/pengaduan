<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Breadcrumb Section -->
<section class="bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="<?= base_url('frontend') ?>" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="home" class="w-4 h-4"></i>
                    </a>
                </li>
                <li>
                    <span class="text-gray-500 mx-2">/</span>
                </li>
                <li>
                    <span class="text-gray-900">Kategori</span>
                </li>
                <li>
                    <span class="text-gray-500 mx-2">/</span>
                </li>
                <li>
                    <span class="text-gray-900 font-medium"><?= isset($kategori) ? $kategori->nama_kategori : '' ?></span>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Header Section -->
<section class="gradient-bg text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <div class="bg-white bg-opacity-20 rounded-lg p-4 w-fit mb-4 mx-auto">
                <i data-lucide="<?= isset($kategori) ? $kategori->icon : 'folder' ?>" class="w-12 h-12 text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?= isset($kategori) ? $kategori->nama_kategori : '' ?>
            </h1>
            <p class="text-xl text-gray-200 max-w-3xl mx-auto">
                <?= isset($kategori) && !empty($kategori->deskripsi) ? $kategori->deskripsi : '' ?>
            </p>
            
        </div>
    </div>
</section>

<!-- Sub Categories Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Sub Kategori <span class="text-gradient"><?= isset($kategori) ? $kategori->nama_kategori : '' ?></span>
            </h2>
            <p class="text-xl text-gray-600">
                <?= isset($children) && !empty($children) ? 'Jelajahi sub kategori yang tersedia' : 'Belum ada sub kategori' ?>
            </p>
        </div>

        <?php if (isset($children) && !empty($children)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($children as $child): ?>
                    <a href="<?= base_url('frontend/fasilitas/' . url_title($child->nama_kategori, 'dash', TRUE) . '/' . $child->id) ?>"
                       class="block group">
                        <div class="bg-gradient-to-br from-<?= $child->color ?>-500 to-<?= $child->color ?>-600 rounded-xl p-6 shadow-lg card-hover text-white transform transition-all duration-300 hover:scale-105">
                            <div class="flex items-center justify-between mb-4">
                                <div class="bg-white bg-opacity-20 rounded-lg p-3 group-hover:scale-110 transition-transform duration-200">
                                    <i data-lucide="<?= $child->icon ?>" class="w-8 h-8 text-white"></i>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-white bg-opacity-20">
                                    <?= $child->status == 1 ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2"><?= $child->nama_kategori ?></h3>
                            <p class="text-white text-opacity-90 mb-4 text-sm">
                                <?= !empty($child->deskripsi) ? character_limiter($child->deskripsi, 80) : '' ?>
                            </p>
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 text-center">
                                <div class="text-3xl font-bold mb-1"><?= $child->total_fasilitas ?></div>
                                
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i data-lucide="folder-open" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Sub Kategori</h3>
                <p class="text-gray-500">Kategori ini belum memiliki sub kategori saat ini.</p>
                <div class="mt-8">
                    <a href="<?= base_url('frontend') ?>" 
                       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Back Button Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <a href="<?= base_url('frontend') ?>" 
               class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-700 font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>
    </div>
</section>