<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
 $content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Hero Slider Section -->
<section class="relative w-full overflow-hidden" x-data="sliderData()">
    <!-- Slider Container with Fixed Aspect Ratio -->
    <div class="slider-container relative w-full" style="padding-bottom: 39.0625%; /* 500/1280 * 100 for 1280x500 aspect ratio */">
        <!-- Slides -->
        <template x-for="(slide, index) in slides" :key="index">
            <div class="absolute inset-0 transition-opacity duration-1000"
                 :class="currentSlide === index ? 'opacity-100' : 'opacity-0'">
                <picture>
                    <img :src="slide.image" :alt="slide.title"
                         class="slider-image slider-bg absolute inset-0 w-full h-full"
                         loading="lazy">
                </picture>
                <div class="absolute inset-0 bg-gradient-to-r from-black/30 to-black/30"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white px-4">
                        <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold mb-2 sm:mb-4" x-text="slide.title"></h1>
                        <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl mb-4 sm:mb-6 md:mb-8 max-w-2xl lg:max-w-3xl mx-auto opacity-90" x-text="slide.subtitle"></p>
                        
                        
                    </div>
                </div>
            </div>
        </template>
    </div>
    <!-- Slide Indicators -->
    <div class="absolute bottom-4 sm:bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10">
        <template x-for="(slide, index) in slides" :key="index">
            <button @click="currentSlide = index"
                    class="w-2 h-2 sm:w-3 sm:h-3 rounded-full transition-all duration-300"
                    :class="currentSlide === index ? 'bg-white w-6 sm:w-8' : 'bg-white/50'"></button>
        </template>
    </div>
    
    <!-- Navigation Arrows -->
    <button @click="currentSlide = (currentSlide - 1 + slides.length) % slides.length"
            class="absolute left-2 sm:left-4 top-1/2 transform -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white p-2 sm:p-3 rounded-full hover:bg-white/30 transition z-10">
        <i class="fas fa-chevron-left text-sm sm:text-base"></i>
    </button>
    <button @click="currentSlide = (currentSlide + 1) % slides.length"
            class="absolute right-2 sm:right-4 top-1/2 transform -translate-y-1/2 bg-white/20 backdrop-blur-sm text-white p-2 sm:p-3 rounded-full hover:bg-white/30 transition z-10">
        <i class="fas fa-chevron-right text-sm sm:text-base"></i>
    </button>
</section>

<!-- Statistics Section -->
<section class="py-16 bg-gradient-to-br from-indigo-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Statistik <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600">Fasilitas Umum</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Ringkasan data fasilitas umum yang tersedia di platform kami untuk kemudahan akses informasi publik
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Kategori -->
            <div class="bg-white rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Kategori</p>
                        <p class="text-3xl font-bold text-gray-900"><?= isset($kategori_list) ? count($kategori_list) : '0' ?></p>
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>
                            <span>Aktif hari ini</span>
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow-lg">
                        <i class="fas fa-layer-group text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Kategori Aktif -->
            <div class="bg-white rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Kategori Aktif</p>
                        <p class="text-3xl font-bold text-gray-900"><?= isset($kategori_list) ? count(array_filter($kategori_list, function($k) { return $k->status == 1; })) : '0' ?></p>
                        <p class="text-xs text-green-600 mt-2">
                            <i class="fas fa-check-circle mr-1"></i>
                            <span>Terverifikasi</span>
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-green-400 to-green-600 text-white shadow-lg">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Fasilitas Publik -->
            <div class="bg-white rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Fasilitas Publik</p>
                        <p class="text-3xl font-bold text-gray-900"><?= isset($total_fasilitas) ? $total_fasilitas : '0' ?></p>
                        <p class="text-xs text-blue-600 mt-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span>Tersedia</span>
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow-lg">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pengunjung Hari Ini -->
            <div class="bg-white rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Pengunjung Hari Ini</p>
                        <p class="text-3xl font-bold text-gray-900"><?= isset($pengunjung_hari_ini) ? $pengunjung_hari_ini : '0' ?></p>
                        <p class="text-xs text-orange-600 mt-2">
                            <i class="fas fa-users mr-1"></i>
                            <span>Online</span>
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 text-white shadow-lg">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Fasilitas Umum Section -->
<section id="kategori" class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Kategori <span class="text-gradient">Fasilitas Umum</span>
            </h2>
            <p class="text-xl text-gray-600">
                Jelajahi berbagai kategori fasilitas umum yang tersedia
            </p>
        </div>

        <?php
        // Define functions outside the loop to avoid redeclaration
        function get_category_color($nama_kategori) {
            $nama_lower = strtolower($nama_kategori);
            
            $color_map = [
                'kesehatan' => 'red',
                'pendidikan' => 'blue',
                'transportasi' => 'yellow',
                'pasar' => 'orange',
                'ibadah' => 'purple',
                'olahraga' => 'red',
                'sosial' => 'pink',
                'keamanan' => 'indigo',
                'lingkungan' => 'emerald',
                'wisata' => 'cyan',
                'kuliner' => 'amber',
                'hiburan' => 'rose',
                'bank' => 'slate',
                'kantor' => 'gray',
                'fasilitas' => 'teal',
                'umum' => 'zinc'
            ];
            
            foreach ($color_map as $keyword => $color) {
                if (strpos($nama_lower, $keyword) !== false) {
                    return $color;
                }
            }
            
            return 'blue';
        }
        
        function get_category_icon($nama_kategori) {
            $nama_lower = strtolower($nama_kategori);
            
            $icon_map = [
                'kesehatan' => 'fa-hospital',
                'pendidikan' => 'fa-school',
                'transportasi' => 'fa-road',
                'pasar' => 'fa-shopping-cart',
                'ibadah' => 'fa-mosque',
                'olahraga' => 'fa-trophy',
                'sosial' => 'fa-users',
                'keamanan' => 'fa-shield-alt',
                'lingkungan' => 'fa-tree',
                'wisata' => 'fa-map-pin',
                'kuliner' => 'fa-utensils',
                'hiburan' => 'fa-music',
                'bank' => 'fa-landmark',
                'kantor' => 'fa-building',
                'fasilitas' => 'fa-home',
                'umum' => 'fa-plus'
            ];
            
            foreach ($icon_map as $keyword => $icon) {
                if (strpos($nama_lower, $keyword) !== false) {
                    return $icon;
                }
            }
            
            return 'fa-folder';
        }
        
        $icon_colors = [
            'red' => 'text-red-500',
            'blue' => 'text-blue-500',
            'green' => 'text-green-500',
            'yellow' => 'text-yellow-500',
            'purple' => 'text-purple-500',
            'pink' => 'text-pink-500',
            'indigo' => 'text-indigo-500',
            'emerald' => 'text-emerald-500',
            'cyan' => 'text-cyan-500',
            'amber' => 'text-amber-500',
            'rose' => 'text-rose-500',
            'slate' => 'text-slate-500',
            'gray' => 'text-gray-500',
            'teal' => 'text-teal-500',
            'zinc' => 'text-zinc-500',
            'orange' => 'text-orange-500'
        ];
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (isset($kategori_list) && !empty($kategori_list)): ?>
                <?php foreach ($kategori_list as $kategori): ?>
                    <a href="<?= base_url('frontend/detail-kategori/' . $kategori->id) ?>"
                       class="text-center p-6 rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer group"
                       title="Lihat detail fasilitas <?= $kategori->nama_kategori ?>"
                       aria-label="Kategori <?= $kategori->nama_kategori ?>">
                        <div class="mb-4">
                            <?php
                            $icon_color = get_category_color($kategori->nama_kategori);
                            $icon_class = isset($icon_colors[$icon_color]) ? $icon_colors[$icon_color] : 'text-gray-500';
                            $icon = get_category_icon($kategori->nama_kategori);
                            ?>
                            <i class="fas <?= $icon ?> text-4xl <?= $icon_class ?> mb-4"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800"><?= $kategori->nama_kategori ?></h3>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-folder-open w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Kategori</h3>
                    <p class="text-gray-500">Belum ada data kategori fasilitas umum yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Widget Harga Komoditas -->
<?php if (isset($latest_prices) && !empty($latest_prices)): ?>
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Harga <span class="text-gradient">Komoditas Terkini</span>
                </h2>
                <p class="text-xl text-gray-600">
                    Informasi harga komoditas terbaru di berbagai pasar
                </p>
            </div>
            <a href="<?= site_url('frontend/harga_komoditas'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 flex items-center">
                Lihat Selengkapnya
                <i class="fas fa-arrow-right w-5 h-5 ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest_prices as $price): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden card-hover">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i class="fas fa-box text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="font-semibold text-gray-800"><?= $price->komoditas_nama; ?></h3>
                                <p class="text-sm text-gray-500">per <?= $price->komoditas_satuan; ?></p>
                            </div>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Stabil</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-2">
                        Rp <?= number_format($price->harga_rata_rata, 0, ',', '.'); ?>
                    </div>
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt w-4 h-4 mr-1 inline"></i>
                        <?= $price->pasar_nama; ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        <?= date('d/m/Y', strtotime($price->harga_tanggal)); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<!-- Skeleton Loading untuk Harga Komoditas -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Harga <span class="text-gradient">Komoditas Terkini</span>
                </h2>
                <p class="text-xl text-gray-600">
                    Informasi harga komoditas terbaru di berbagai pasar
                </p>
            </div>
            <div class="h-10 bg-gray-200 rounded-lg w-40 animate-pulse"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php for($i = 0; $i < 3; $i++): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-pulse">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-gray-200 rounded-lg">
                                <div class="w-6 h-6 bg-gray-300 rounded"></div>
                            </div>
                            <div class="ml-3">
                                <div class="h-5 bg-gray-200 rounded w-24 mb-1"></div>
                                <div class="h-4 bg-gray-200 rounded w-16"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-200 rounded w-16"></div>
                    </div>
                    <div class="h-8 bg-gray-200 rounded w-32 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-40 mb-1"></div>
                    <div class="h-3 bg-gray-200 rounded w-24"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Fitur Unggulan</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4">
                    <i class="fas fa-search text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Pencarian Mudah</h3>
                <p class="text-gray-600">Temukan fasilitas umum yang Anda butuhkan dengan cepat dan mudah</p>
            </div>
            
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-info-circle text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Informasi Lengkap</h3>
                <p class="text-gray-600">Dapatkan informasi detail mengenai setiap fasilitas umum</p>
            </div>
            
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-purple-100 mb-4">
                    <i class="fas fa-sync-alt text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Data Terupdate</h3>
                <p class="text-gray-600">Informasi fasilitas umum selalu diperbarui secara berkala</p>
            </div>
        </div>
    </div>
</section>



<!-- Slider Data Function -->
<script>
function sliderData() {
    return {
        currentSlide: 0,
        slides: <?php
            // Prepare slides data from database
            $slides = [];
            if (isset($slider_data) && !empty($slider_data)) {
                foreach ($slider_data as $slider) {
                    $slide_image = $slider->slider_file ? base_url('uploads/slider/' . $slider->slider_file) : base_url('assets/images/hero-1.svg');
                    $slides[] = [
                        'image' => $slide_image,
                        'title' => $slider->slider_judul,
                        'subtitle' => $slider->slider_deskripsi ?: 'Temukan informasi terbaik untuk Anda'
                    ];
                }
            } else {
                // Fallback to default slides if no data in database
                $slides = [
                    [
                        'image' => '<?= base_url("assets/images/hero-1.svg") ?>',
                        'title' => '<?= isset($site_name) ? $site_name : "Dashboard Masyarakat" ?>',
                        'subtitle' => 'Sistem Informasi Publik untuk Transparansi Data Fasilitas Umum'
                    ],
                    [
                        'image' => '<?= base_url("assets/images/hero-2.svg") ?>',
                        'title' => 'Akses Informasi Mudah',
                        'subtitle' => 'Temukan berbagai fasilitas umum dengan cepat dan transparan'
                    ],
                    [
                        'image' => '<?= base_url("assets/images/hero-3.svg") ?>',
                        'title' => 'Data Terupdate',
                        'subtitle' => 'Informasi fasilitas umum selalu diperbarui secara berkala'
                    ]
                ];
            }
            echo json_encode($slides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        ?>,
        init() {
            setInterval(() => {
                this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            }, 5000);
        }
    }
}
</script>



<!-- Include centralized JavaScript -->
<?php $this->load->view('frontend/js_frontend'); ?>