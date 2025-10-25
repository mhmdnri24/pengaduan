<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Hero Section with SVG Background -->
<section class="relative bg-gradient-to-r from-blue-600 to-blue-700 text-white py-16 overflow-hidden">
    <!-- SVG Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,165.3C672,171,768,213,864,213.3C960,213,1056,171,1152,149.3C1248,128,1344,128,1392,128L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>
    
    <!-- Emergency Icons Pattern -->
    <div class="absolute inset-0 opacity-5">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <pattern id="emergency-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                <circle cx="50" cy="50" r="40" fill="none" stroke="white" stroke-width="2"/>
                <path d="M50,20 L50,80 M20,50 L80,50" stroke="white" stroke-width="2"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#emergency-pattern)" />
        </svg>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold mb-4">Kontak Darurat</h1>
            <p class="text-xl opacity-90">Hubungi nomor darurat berikut untuk situasi mendesak</p>
        </div>
    </div>
</section>

<!-- Skeleton Loading -->
<div id="skeleton-loader" class="hidden py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Skeleton Cards -->
            <?php for($i = 0; $i < 6; $i++): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-200 h-32 animate-pulse"></div>
                <div class="p-6">
                    <div class="h-4 bg-gray-200 rounded mb-4 animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded mb-2 animate-pulse"></div>
                    <div class="h-10 bg-gray-200 rounded animate-pulse"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Emergency Contacts Section -->
<section id="emergency-contacts" class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (isset($emergency_contacts) && is_array($emergency_contacts) && count($emergency_contacts) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($emergency_contacts as $index => $kontak): ?>
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
                    $blue_shades = ['500', '600', '700', '800', '900'];
                    $card_color = $blue_shades[($index % count($blue_shades))];
                    ?>
                    
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl <?php echo !empty($kontak['no_kontak']) ? 'cursor-pointer' : ''; ?>" 
                         <?php echo !empty($kontak['no_kontak']) ? 'onclick="makePhoneCall(\'' . $kontak['no_kontak'] . '\')"' : ''; ?>>
                        <div class="bg-gradient-to-br from-blue-<?php echo $card_color; ?> to-blue-<?php echo (int)$card_color + 100; ?> h-32 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas <?php echo $icon_class; ?> text-5xl text-white mb-2"></i>
                                <h3 class="text-white font-bold text-lg"><?php echo $kontak['nama_kontak'] ?? 'Kontak Darurat'; ?></h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <?php if (!empty($kontak['no_kontak'])): ?>
                                <div class="mb-4">
                                    <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                                    <p class="text-2xl font-bold text-blue-<?php echo $card_color; ?>"><?php echo $kontak['no_kontak']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($kontak['deskripsi'])): ?>
                                <div class="mb-4">
                                    <p class="text-gray-600 text-sm mb-1">Layanan</p>
                                    <p class="text-gray-800"><?php echo $kontak['deskripsi']; ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($kontak['no_kontak'])): ?>
                                <button class="w-full bg-gradient-to-r from-blue-<?php echo $card_color; ?> to-blue-<?php echo (int)$card_color + 100; ?> hover:from-blue-<?php echo (int)$card_color + 100; ?> hover:to-blue-<?php echo (int)$card_color + 200; ?> text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center"
                                        onclick="makePhoneCall('<?php echo $kontak['no_kontak']; ?>')">
                                    <i class="fas fa-phone mr-2"></i>
                                    Hubungi Sekarang
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
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-shield-alt text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">Polisi</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">110</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
                
                <!-- Fire Department -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer" 
                     onclick="makePhoneCall('113')">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-fire-extinguisher text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">Pemadam Kebakaran</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">113</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
                
                <!-- Ambulance -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer" 
                     onclick="makePhoneCall('118')">
                    <div class="bg-gradient-to-br from-blue-700 to-blue-800 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-ambulance text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">Ambulance</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">118</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-700 to-blue-800 hover:from-blue-800 hover:to-blue-900 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
                
                <!-- SAR -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer" 
                     onclick="makePhoneCall('115')">
                    <div class="bg-gradient-to-br from-blue-800 to-blue-900 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-life-ring text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">SAR</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">115</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-800 to-blue-900 hover:from-blue-900 hover:to-indigo-900 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
                
                <!-- BPBD -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer" 
                     onclick="makePhoneCall('129')">
                    <div class="bg-gradient-to-br from-blue-900 to-indigo-900 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-house-damage text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">BPBD</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">129</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-blue-900 to-indigo-900 hover:from-indigo-900 hover:to-indigo-800 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
                
                <!-- PLN -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-xl cursor-pointer" 
                     onclick="makePhoneCall('123')">
                    <div class="bg-gradient-to-br from-indigo-900 to-blue-600 h-32 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-bolt text-5xl text-white mb-2"></i>
                            <h3 class="text-white font-bold text-lg">PLN</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm mb-1">Nomor Darurat</p>
                            <p class="text-2xl font-bold text-blue-600">123</p>
                        </div>
                        <button class="w-full bg-gradient-to-r from-indigo-900 to-blue-600 hover:from-indigo-800 hover:to-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Hubungi Sekarang
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Information Section -->
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl mt-1"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Penting untuk Diketahui</h3>
                    <ul class="list-disc list-inside text-gray-600 space-y-1">
                        <li>Gunakan nomor darurat hanya untuk situasi yang benar-benar mendesak</li>
                        <li>Siapkan informasi penting seperti lokasi, jenis kejadian, dan jumlah korban</li>
                        <li>Ikuti petunjuk dari operator telepon dengan tenang dan jelas</li>
                        <li>Jangan menutup telepon sebelum operator memberi tahu untuk melakukannya</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for phone call functionality and skeleton loading -->
<script>
// Show skeleton loader initially
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    var skeleton = document.getElementById('skeleton-loader');
    var emergencyContacts = document.getElementById('emergency-contacts');
    
    // Simulate loading time with skeleton
    if (skeleton) skeleton.style.display = 'block';
    if (emergencyContacts) emergencyContacts.style.display = 'none';
    
    // Hide skeleton after 1.5 seconds
    setTimeout(function() {
        if (skeleton) skeleton.style.display = 'none';
        if (emergencyContacts) emergencyContacts.style.display = 'block';
    }, 1500);
});

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