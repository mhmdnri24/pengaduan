<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                Monitoring <span class="text-yellow-300">Retribusi</span>
            </h1>
            <p class="text-xl text-gray-200 mb-6">
                Informasi target dan capaian retribusi pasar
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-sm">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <i class="fas fa-clock mr-2"></i>
                    Update: <span id="lastUpdate"><?= date('H:i:s'); ?></span>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg px-4 py-2">
                    <i class="fas fa-calendar mr-2"></i>
                    <span id="currentDate"><?= date('d/m/Y'); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Loading Indicator -->
<div id="loadingIndicator" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
            <span class="text-gray-700">Memuat data...</span>
        </div>
    </div>
</div>

<!-- Retribusi Cards Section -->
<section class="py-8 bg-gray-50" x-data="monitoringRetribusi()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="retribusiCards">
            <!-- Target Retribusi Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                        <i class="fas fa-bullseye text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">Target</span>
                </div>
                <h3 class="text-lg font-semibold mb-2">Target Retribusi</h3>
                <p class="text-3xl font-bold mb-2" id="targetRevenue">Rp 0</p>
                <div class="flex items-center text-sm">
                    <i class="fas fa-chart-line mr-2"></i>
                    <span>Bulan Ini</span>
                </div>
            </div>

            <!-- Capaian Retribusi Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">Capaian</span>
                </div>
                <h3 class="text-lg font-semibold mb-2">Retribusi Tercapai</h3>
                <p class="text-3xl font-bold mb-2" id="realizedRevenue">Rp 0</p>
                <div class="flex items-center text-sm">
                    <i class="fas fa-percentage mr-2"></i>
                    <span id="achievementRate">0% dari target</span>
                </div>
            </div>

            <!-- Persentase Card -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                        <i class="fas fa-percentage text-2xl"></i>
                    </div>
                    <span class="text-sm bg-white/20 backdrop-blur-sm rounded-full px-3 py-1">Persentase</span>
                </div>
                <h3 class="text-lg font-semibold mb-2">Persentase Capaian</h3>
                <p class="text-3xl font-bold mb-2" id="percentageAchievement">0%</p>
                <div class="flex items-center text-sm">
                    <i class="fas fa-chart-pie mr-2"></i>
                    <span id="achievementStatus">Belum ada data</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data for JavaScript -->
<div id="harga-data" data-pasar-id="<?= isset($pasar_id) ? $pasar_id : 'all'; ?>" style="display: none;"></div>

<!-- Include centralized JavaScript -->
<?php $this->load->view('frontend/js_frontend'); ?>
