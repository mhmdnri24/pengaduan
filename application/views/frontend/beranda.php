<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                Dashboard <span class="text-yellow-300">Masyarakat</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-200 animate-slide-up">
                Akses Informasi Publik dengan Mudah dan Transparan
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up">
                <a href="<?= base_url('harga-komoditas') ?>" 
                   class="bg-white text-primary-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200 flex items-center justify-center space-x-2">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                    <span>Lihat Harga Komoditas</span>
                </a>
                <a href="<?= base_url('pelaporan') ?>" 
                   class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary-600 transition-colors duration-200 flex items-center justify-center space-x-2">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    <span>Lihat Pelaporan</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Statistik <span class="text-gradient">Data Publik</span>
            </h2>
            <p class="text-xl text-gray-600">
                Ringkasan data terkini yang tersedia di platform
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Total Komoditas -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Komoditas</p>
                        <p class="text-3xl font-bold"><?= isset($stats['total_komoditas']) ? number_format($stats['total_komoditas']) : '0' ?></p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                        <i data-lucide="package" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pelaporan -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Pelaporan</p>
                        <p class="text-3xl font-bold"><?= isset($stats['total_pelaporan']) ? number_format($stats['total_pelaporan']) : '0' ?></p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-lg p-3">
                        <i data-lucide="file-text" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>

            <!-- Total Kepengurusan -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Data Kepengurusan</p>
                        <p class="text-3xl font-bold"><?= isset($stats['total_kepengurusan']) ? number_format($stats['total_kepengurusan']) : '0' ?></p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-lg p-3">
                        <i data-lucide="users" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>

            <!-- Total UMKM -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Data UMKM</p>
                        <p class="text-3xl font-bold"><?= isset($stats['total_umkm']) ? number_format($stats['total_umkm']) : '0' ?></p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-lg p-3">
                        <i data-lucide="store" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Fitur <span class="text-gradient">Unggulan</span>
            </h2>
            <p class="text-xl text-gray-600">
                Akses berbagai informasi publik dalam satu platform
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Harga Komoditas -->
            <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                <div class="bg-blue-100 rounded-lg p-3 w-fit mb-4">
                    <i data-lucide="trending-up" class="w-8 h-8 text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Harga Komoditas</h3>
                <p class="text-gray-600 mb-4">
                    Pantau harga komoditas terkini dari berbagai pasar tradisional
                </p>
                <a href="<?= base_url('harga-komoditas') ?>" 
                   class="text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                    <span>Lihat Detail</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Pelaporan -->
            <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                <div class="bg-green-100 rounded-lg p-3 w-fit mb-4">
                    <i data-lucide="file-text" class="w-8 h-8 text-green-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Pelaporan Masyarakat</h3>
                <p class="text-gray-600 mb-4">
                    Lihat status pelaporan dan pengaduan dari masyarakat
                </p>
                <a href="<?= base_url('pelaporan') ?>" 
                   class="text-green-600 hover:text-green-700 font-medium flex items-center space-x-1">
                    <span>Lihat Detail</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Kepengurusan -->
            <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                <div class="bg-purple-100 rounded-lg p-3 w-fit mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data Kepengurusan</h3>
                <p class="text-gray-600 mb-4">
                    Informasi kepengurusan sosial dan organisasi masyarakat
                </p>
                <a href="<?= base_url('kepengurusan') ?>" 
                   class="text-purple-600 hover:text-purple-700 font-medium flex items-center space-x-1">
                    <span>Lihat Detail</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- UMKM -->
            <div class="bg-white rounded-xl p-6 shadow-lg card-hover">
                <div class="bg-orange-100 rounded-lg p-3 w-fit mb-4">
                    <i data-lucide="store" class="w-8 h-8 text-orange-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data UMKM</h3>
                <p class="text-gray-600 mb-4">
                    Direktori usaha mikro, kecil, dan menengah di wilayah
                </p>
                <a href="<?= base_url('umkm') ?>" 
                   class="text-orange-600 hover:text-orange-700 font-medium flex items-center space-x-1">
                    <span>Lihat Detail</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Latest Updates Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Latest Prices -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Harga Terbaru</h3>
                    <a href="<?= base_url('harga-komoditas') ?>" 
                       class="text-blue-600 hover:text-blue-700 font-medium flex items-center space-x-1">
                        <span>Lihat Semua</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="space-y-4">
                    <?php if (isset($harga_terbaru) && !empty($harga_terbaru)): ?>
                        <?php foreach ($harga_terbaru as $harga): ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-900"><?= $harga->komoditas_nama ?></h4>
                                        <p class="text-sm text-gray-600"><?= $harga->pasar_nama ?></p>
                                        <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($harga->harga_tanggal)) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-green-600">
                                            Rp <?= number_format($harga->harga_rata_rata, 0, ',', '.') ?>
                                        </p>
                                        <p class="text-xs text-gray-500">per <?= $harga->komoditas_satuan ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="package" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>Belum ada data harga terbaru</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest Reports -->
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">Pelaporan Terbaru</h3>
                    <a href="<?= base_url('pelaporan') ?>" 
                       class="text-green-600 hover:text-green-700 font-medium flex items-center space-x-1">
                        <span>Lihat Semua</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <div class="space-y-4">
                    <?php if (isset($pelaporan_terbaru) && !empty($pelaporan_terbaru)): ?>
                        <?php foreach ($pelaporan_terbaru as $laporan): ?>
                            <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900 mb-1"><?= $laporan->judul ?></h4>
                                        <p class="text-sm text-gray-600 mb-2"><?= $laporan->nama_kategori ?></p>
                                        <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($laporan->created_at)) ?></p>
                                    </div>
                                    <div class="ml-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
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
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= $laporan->status ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                            <p>Belum ada pelaporan terbaru</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 gradient-bg text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
            Butuh Akses Lebih Lengkap?
        </h2>
        <p class="text-xl text-gray-200 mb-8">
            Login sebagai admin untuk mengakses fitur manajemen data
        </p>
        <a href="<?= base_url('admin') ?>" 
           class="bg-white text-primary-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors duration-200 inline-flex items-center space-x-2">
            <i data-lucide="log-in" class="w-5 h-5"></i>
            <span>Login Admin</span>
        </a>
    </div>
</section>

<!-- Emergency Contacts Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Kontak <span class="text-gradient">Darurat</span>
            </h2>
            <p class="text-xl text-gray-600">
                Hubungi nomor darurat berikut untuk situasi mendesak
            </p>
        </div>

        <?php if (isset($emergency_contacts) && is_array($emergency_contacts) && count($emergency_contacts) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach (array_slice($emergency_contacts, 0, 6) as $index => $kontak): ?>
                    <?php
                    // Determine icon based on available data or service type
                    $icon_class = 'fa-phone-alt';
                    
                    // Check if there's specific icon data
                    if (!empty($kontak['ikon'])) {
                        $icon_class = $kontak['ikon'];
                    } else {
                        // Determine icon based on contact name or service
                        $nama_lower = strtolower($kontak['nama_kontak'] ?? '');
                        if (strpos($nama_lower, 'polisi') !== false || strpos($nama_lower, 'police') !== false) {
                            $icon_class = 'fa-shield-alt';
                        } elseif (strpos($nama_lower, 'pemadam') !== false || strpos($nama_lower, 'damkar') !== false || strpos($nama_lower, 'fire') !== false) {
                            $icon_class = 'fa-fire-extinguisher';
                        } elseif (strpos($nama_lower, 'ambulans') !== false || strpos($nama_lower, 'medis') !== false || strpos($nama_lower, 'rumah sakit') !== false) {
                            $icon_class = 'fa-ambulance';
                        } elseif (strpos($nama_lower, 'bpbd') !== false || strpos($nama_lower, 'bencana') !== false) {
                            $icon_class = 'fa-house-damage';
                        } elseif (strpos($nama_lower, 'pln') !== false || strpos($nama_lower, 'listrik') !== false) {
                            $icon_class = 'fa-bolt';
                        } elseif (strpos($nama_lower, 'pdam') !== false || strpos($nama_lower, 'air') !== false) {
                            $icon_class = 'fa-tint';
                        }
                    }
                    
                    // Generate different shades of blue for variety
                    $blue_shades = ['500', '600', '700'];
                    $card_color = $blue_shades[($index % count($blue_shades))];
                    ?>
                    
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl <?php echo !empty($kontak['no_kontak']) ? 'cursor-pointer' : ''; ?>"
                         <?php echo !empty($kontak['no_kontak']) ? 'onclick="makePhoneCall(\'' . $kontak['no_kontak'] . '\')"' : ''; ?>>
                        <div class="bg-gradient-to-br from-blue-<?php echo $card_color; ?> to-blue-<?php echo (int)$card_color + 100; ?> h-24 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas <?php echo $icon_class; ?> text-3xl text-white mb-1"></i>
                                <h3 class="text-white font-bold text-sm"><?php echo $kontak['nama_kontak'] ?? 'Kontak Darurat'; ?></h3>
                            </div>
                        </div>
                        <div class="p-4">
                            <?php if (!empty($kontak['no_kontak'])): ?>
                                <div class="mb-3">
                                    <p class="text-gray-600 text-xs mb-1">Nomor Darurat</p>
                                    <p class="text-xl font-bold text-blue-<?php echo $card_color; ?>"><?php echo $kontak['no_kontak']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($kontak['no_kontak'])): ?>
                                <button class="w-full bg-gradient-to-r from-blue-<?php echo $card_color; ?> to-blue-<?php echo (int)$card_color + 100; ?> hover:from-blue-<?php echo (int)$card_color + 100; ?> hover:to-blue-<?php echo (int)$card_color + 200; ?> text-white font-bold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center text-sm"
                                        onclick="makePhoneCall('<?php echo $kontak['no_kontak']; ?>')">
                                    <i class="fas fa-phone mr-2"></i>
                                    Hubungi
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Fallback emergency contacts if no data is available -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Police -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer"
                     onclick="makePhoneCall('110')">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-24 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-shield-alt text-3xl text-white mb-1"></i>
                            <h3 class="text-white font-bold text-sm">Polisi</h3>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-gray-600 text-xs mb-1">Nomor Darurat</p>
                            <p class="text-xl font-bold text-blue-600">110</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center text-sm">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi
                        </button>
                    </div>
                </div>
                
                <!-- Fire Department -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer"
                     onclick="makePhoneCall('113')">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 h-24 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-fire-extinguisher text-3xl text-white mb-1"></i>
                            <h3 class="text-white font-bold text-sm">Pemadam Kebakaran</h3>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-gray-600 text-xs mb-1">Nomor Darurat</p>
                            <p class="text-xl font-bold text-blue-600">113</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center text-sm">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi
                        </button>
                    </div>
                </div>
                
                <!-- Ambulance -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer"
                     onclick="makePhoneCall('118')">
                    <div class="bg-gradient-to-br from-blue-700 to-blue-800 h-24 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-ambulance text-3xl text-white mb-1"></i>
                            <h3 class="text-white font-bold text-sm">Ambulance</h3>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-gray-600 text-xs mb-1">Nomor Darurat</p>
                            <p class="text-xl font-bold text-blue-600">118</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-700 to-blue-800 hover:from-blue-800 hover:to-blue-900 text-white font-bold py-2 px-3 rounded-lg transition-all duration-300 flex items-center justify-center text-sm">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-8">
            <a href="<?= base_url('kontak-darurat') ?>"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-phone-alt mr-2"></i>
                Lihat Semua Kontak Darurat
            </a>
        </div>
    </div>
</section>

<!-- JavaScript for phone call functionality -->
<script>
function makePhoneCall(phoneNumber) {
    // Remove any non-numeric characters
    var cleanNumber = phoneNumber.replace(/[^\d+]/g, '');
    
    // Check if the device supports making phone calls
    if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
        // Mobile device - open phone dialer
        window.location.href = 'tel:' + cleanNumber;
    } else {
        // Desktop device - show confirmation dialog
        if (confirm('Apakah Anda ingin menelepon ' + phoneNumber + '?')) {
            // Try to open default telephony application
            window.open('tel:' + cleanNumber, '_self');
        }
    }
}

// Add touch feedback for mobile devices
document.addEventListener('DOMContentLoaded', function() {
    var cards = document.querySelectorAll('.cursor-pointer');
    cards.forEach(function(card) {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });
});
</script>


