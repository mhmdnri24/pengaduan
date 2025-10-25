<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-purple-600 to-purple-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Data <span class="text-yellow-300">Kepengurusan</span>
            </h1>
            <p class="text-xl text-purple-100 mb-8">
                Informasi kepengurusan sosial dan organisasi masyarakat
            </p>
            
            <!-- Breadcrumb -->
            <nav class="flex justify-center" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-purple-200">
                    <li>
                        <a href="<?= base_url() ?>" class="hover:text-white transition-colors">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </li>
                    <li class="text-white font-medium">Kepengurusan</li>
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
                       placeholder="Cari berdasarkan nama atau alamat..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <!-- Category Filter -->
            <div class="flex-1">
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">
                    Kategori Kepengurusan
                </label>
                <select id="kategori" 
                        name="kategori" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Semua Kategori</option>
                    <?php if (isset($kategori_list) && !empty($kategori_list)): ?>
                        <?php foreach ($kategori_list as $kategori): ?>
                            <option value="<?= $kategori->kepengurusan_id ?>" 
                                    <?= (isset($filters['kategori']) && $filters['kategori'] == $kategori->kepengurusan_id) ? 'selected' : '' ?>>
                                <?= $kategori->kepengurusan_nama ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Filter Button -->
            <div>
                <button type="submit" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Cari</span>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Kepengurusan Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Results Info -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Data Kepengurusan Sosial
                    </h2>
                    <p class="text-gray-600">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['kategori']))): ?>
                            Hasil pencarian untuk:
                            <?php if (!empty($filters['search'])): ?>
                                "<span class="font-medium"><?= $filters['search'] ?></span>"
                            <?php endif; ?>
                            <?php if (!empty($filters['kategori'])): ?>
                                <?php 
                                $kategori_name = 'Kategori Terpilih';
                                if (isset($kategori_list)) {
                                    foreach ($kategori_list as $kat) {
                                        if ($kat->kepengurusan_id == $filters['kategori']) {
                                            $kategori_name = $kat->kepengurusan_nama;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                Kategori: <span class="font-medium"><?= $kategori_name ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            Menampilkan semua data kepengurusan sosial
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

        <!-- Kepengurusan Grid -->
        <?php if (isset($kepengurusan_list) && !empty($kepengurusan_list)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <?php foreach ($kepengurusan_list as $pengurus): ?>
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4 text-white">
                            <div class="flex items-center space-x-3">
                                <div class="bg-white bg-opacity-20 rounded-full p-2">
                                    <i data-lucide="user" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold"><?= $pengurus->nama_lengkap ?></h3>
                                    <?php if (isset($pengurus->nama_kategori)): ?>
                                        <p class="text-purple-100 text-sm"><?= $pengurus->nama_kategori ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <!-- Personal Info -->
                            <div class="space-y-3 mb-4">
                                <?php if (isset($pengurus->nik_ktp) && !empty($pengurus->nik_ktp)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600">NIK: <?= $pengurus->nik_ktp ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($pengurus->jenis_kelamin) && !empty($pengurus->jenis_kelamin)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="user-check" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= ucfirst($pengurus->jenis_kelamin) ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($pengurus->tanggal_lahir) && !empty($pengurus->tanggal_lahir)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600">
                                            <?= date('d M Y', strtotime($pengurus->tanggal_lahir)) ?>
                                            (<?= floor((time() - strtotime($pengurus->tanggal_lahir)) / (365.25 * 24 * 3600)) ?> tahun)
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($pengurus->no_wa_hp) && !empty($pengurus->no_wa_hp)): ?>
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                        <span class="text-sm text-gray-600"><?= $pengurus->no_wa_hp ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Address -->
                            <?php if (isset($pengurus->alamat) && !empty($pengurus->alamat)): ?>
                                <div class="mb-4">
                                    <div class="flex items-start space-x-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                        <div class="text-sm text-gray-600">
                                            <p><?= $pengurus->alamat ?></p>
                                            <?php if (isset($pengurus->nama_kelurahan) || isset($pengurus->nama_kecamatan)): ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?= isset($pengurus->nama_kelurahan) ? $pengurus->nama_kelurahan : '' ?>
                                                    <?= (isset($pengurus->nama_kelurahan) && isset($pengurus->nama_kecamatan)) ? ', ' : '' ?>
                                                    <?= isset($pengurus->nama_kecamatan) ? $pengurus->nama_kecamatan : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- SK Info -->
                            <?php if (isset($pengurus->nomor_sk) || isset($pengurus->tanggal_sk)): ?>
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informasi SK</h4>
                                    <div class="space-y-2">
                                        <?php if (isset($pengurus->nomor_sk) && !empty($pengurus->nomor_sk)): ?>
                                            <div class="flex items-center space-x-2">
                                                <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                                                <span class="text-sm text-gray-600">No. SK: <?= $pengurus->nomor_sk ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($pengurus->tanggal_sk) && !empty($pengurus->tanggal_sk)): ?>
                                            <div class="flex items-center space-x-2">
                                                <i data-lucide="calendar-check" class="w-4 h-4 text-gray-400"></i>
                                                <span class="text-sm text-gray-600">
                                                    Tgl SK: <?= date('d M Y', strtotime($pengurus->tanggal_sk)) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($pengurus->masa_jabatan) && !empty($pengurus->masa_jabatan)): ?>
                                            <div class="flex items-center space-x-2">
                                                <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                                                <span class="text-sm text-gray-600">
                                                    Masa Jabatan: <?= $pengurus->masa_jabatan ?> tahun
                                                </span>
                                            </div>
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
                    <i data-lucide="users-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Tidak Ada Data Kepengurusan
                    </h3>
                    <p class="text-gray-600 mb-6">
                        <?php if (isset($filters) && (!empty($filters['search']) || !empty($filters['kategori']))): ?>
                            Tidak ditemukan data kepengurusan yang sesuai dengan kriteria pencarian.
                        <?php else: ?>
                            Belum ada data kepengurusan yang tersedia.
                        <?php endif; ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="<?= base_url('kepengurusan') ?>" 
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            Lihat Semua
                        </a>
                        <button onclick="document.getElementById('search').value = ''; document.getElementById('kategori').value = ''; document.querySelector('form').submit();" 
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
        <div class="bg-purple-50 rounded-lg p-8">
            <div class="flex items-start space-x-4">
                <div class="bg-purple-100 rounded-lg p-3">
                    <i data-lucide="info" class="w-6 h-6 text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Informasi Data Kepengurusan
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>• Data kepengurusan mencakup informasi pengurus organisasi sosial dan kemasyarakatan</p>
                        <p>• Informasi SK (Surat Keputusan) menunjukkan legitimasi penugasan</p>
                        <p>• Data yang ditampilkan adalah informasi publik yang dapat diakses masyarakat</p>
                        <p>• Untuk informasi lebih detail, silakan hubungi instansi terkait</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


