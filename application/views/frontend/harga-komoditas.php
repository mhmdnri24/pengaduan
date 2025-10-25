<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific data
$page_title = 'Harga Komoditas';
$page_description = 'Pantau harga komoditas terkini dari berbagai pasar tradisional di Indonesia. Data harga real-time untuk beras, sayuran, buah-buahan, dan komoditas lainnya.';
$page_keywords = 'harga komoditas, pasar tradisional, harga beras, harga sayuran, harga buah, inflasi, ekonomi rakyat';
$current_page = 'harga-komoditas';

// Page-specific assets
$page_js = ['harga-komoditas.js'];

// Add base URL for AJAX
$inline_js = 'window.baseUrl = "' . base_url() . '";';
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Harga <span class="text-yellow-300">Komoditas</span>
            </h1>
            <p class="text-xl text-blue-100 mb-8">
                Pantau harga komoditas terkini dari berbagai pasar tradisional
            </p>

            <!-- Breadcrumb -->
            <nav class="flex justify-center" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-blue-200">
                    <li>
                        <a href="<?= base_url() ?>" class="hover:text-white transition-colors">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </a>
                    </li>
                    <li>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </li>
                    <li class="text-white font-medium">Harga Komoditas</li>
                </ol>
            </nav>
        </div>
    </div>
</section>

<!-- Filter Section -->
<section class="bg-white shadow-sm border-b" x-data="hargaKomoditas()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Date Range Filter -->
            <div class="flex-1">
                <label for="date_range_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Periode Tanggal
                </label>
                <select id="date_range_type"
                        x-model="filters.date_range_type"
                        @change="handleDateRangeChange()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini (7 hari terakhir)</option>
                    <option value="month">Bulan Ini</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Custom Date Range (shown when custom is selected) -->
            <div class="flex-1" x-show="filters.date_range_type === 'custom'" x-transition>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Dari Tanggal - Sampai Tanggal
                </label>
                <div class="flex gap-2">
                    <input type="date"
                           x-model="filters.start_date"
                           @change="loadData()"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="date"
                           x-model="filters.end_date"
                           @change="loadData()"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Market Filter -->
            <div class="flex-1">
                <label for="pasar" class="block text-sm font-medium text-gray-700 mb-2">
                    Pasar
                </label>
                <select id="pasar"
                        x-model="filters.pasar_id"
                        @change="loadData()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">Semua Pasar</option>
                    <?php if (isset($pasar_list) && !empty($pasar_list)): ?>
                        <?php foreach ($pasar_list as $pasar): ?>
                            <option value="<?= $pasar->pasar_id ?>">
                                <?= $pasar->pasar_nama ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>



            <!-- Loading Indicator -->
            <div x-show="loading" class="flex items-center space-x-2 text-blue-600">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                <span class="text-sm">Memuat...</span>
            </div>
        </div>
    </div>
</section>

<!-- Chart Section -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">
                    <i data-lucide="trending-up" class="w-5 h-5 inline mr-2 text-blue-600"></i>
                    Trend Harga Komoditas Utama
                </h2>
                <div class="text-sm text-gray-500">
                    <span x-text="getDateRangeText()"></span>
                </div>
            </div>
            <div class="h-80">
                <canvas id="priceChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- Price Data Section -->
<section class="py-8 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Info Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Data Harga Komoditas
                    </h2>
                    <p class="text-gray-600">
                        Periode: <span class="font-medium" x-text="getDateRangeText()"></span>
                        <span x-show="filters.pasar_id !== 'all'">
                            | Pasar: <span class="font-medium" x-text="getSelectedPasarName()"></span>
                        </span>
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                        <span>Terakhir diperbarui: <span x-text="lastUpdated"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Harga Komoditas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Satuan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Persentase
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Keterangan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="priceTableBody">
                        <!-- Data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
                                    <span>Memuat data...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State Template (hidden) -->
        <template id="emptyStateTemplate">
            <tr>
                <td colspan="5" class="px-6 py-12 text-center">
                    <div class="max-w-md mx-auto">
                        <i data-lucide="package-x" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            Tidak Ada Data Harga
                        </h3>
                        <p class="text-gray-600 mb-6">
                            Belum ada data harga komoditas untuk tanggal dan pasar yang dipilih.
                        </p>
                    </div>
                </td>
            </tr>
        </template>
    </div>
</section>

<!-- Information Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-blue-50 rounded-lg p-8">
            <div class="flex items-start space-x-4">
                <div class="bg-blue-100 rounded-lg p-3">
                    <i data-lucide="info" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Informasi Harga Komoditas
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>• Data harga diperbarui secara berkala dari berbagai pasar tradisional</p>
                        <p>• Harga yang ditampilkan adalah harga rata-rata dari harga beli dan jual</p>
                        <p>• Informasi stok dan kualitas dapat berubah sewaktu-waktu</p>
                        <p>• Untuk informasi lebih akurat, silakan kunjungi pasar terkait</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>





