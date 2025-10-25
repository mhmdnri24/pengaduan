<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Fasilitas Umum</h1>
        <p class="text-xl md:text-2xl mb-8 opacity-90">Jelajahi berbagai kategori fasilitas umum yang tersedia</p>
        
        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" placeholder="Cari kategori fasilitas..."
                       class="w-full px-6 py-4 text-gray-800 bg-white rounded-full shadow-lg focus:outline-none focus:ring-4 focus:ring-indigo-300">
                <button class="absolute right-2 top-2 bg-indigo-600 text-white px-6 py-2 rounded-full hover:bg-indigo-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Fasilitas Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Kategori <span class="text-gradient">Fasilitas Umum</span>
            </h2>
            <p class="text-xl text-gray-600">
                Pilih kategori untuk melihat daftar fasilitas yang tersedia
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
                'ibadah' => 'fa-place-of-worship',
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
        
        $bg_colors = [
            'red' => 'bg-red-100',
            'blue' => 'bg-blue-100',
            'green' => 'bg-green-100',
            'yellow' => 'bg-yellow-100',
            'purple' => 'bg-purple-100',
            'pink' => 'bg-pink-100',
            'indigo' => 'bg-indigo-100',
            'emerald' => 'bg-emerald-100',
            'cyan' => 'bg-cyan-100',
            'amber' => 'bg-amber-100',
            'rose' => 'bg-rose-100',
            'slate' => 'bg-slate-100',
            'gray' => 'bg-gray-100',
            'teal' => 'bg-teal-100',
            'zinc' => 'bg-zinc-100',
            'orange' => 'bg-orange-100'
        ];
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if (isset($kategori_list) && !empty($kategori_list)): ?>
                <?php foreach ($kategori_list as $kategori): ?>
                    <a href="<?= base_url('frontend/detail-' . url_title($kategori->nama_kategori, 'dash', TRUE) . '/' . $kategori->id) ?>"
                       class="group bg-white rounded-xl shadow-lg overflow-hidden card-hover border border-gray-200 hover:border-indigo-500 hover:shadow-xl transition-all duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-center mb-4">
                                <?php
                                $icon_color = get_category_color($kategori->nama_kategori);
                                $bg_color = isset($bg_colors[$icon_color]) ? $bg_colors[$icon_color] : 'bg-gray-100';
                                $icon_class = isset($icon_colors[$icon_color]) ? $icon_colors[$icon_color] : 'text-gray-500';
                                $icon = get_category_icon($kategori->nama_kategori);
                                ?>
                                <div class="w-16 h-16 rounded-full <?= $bg_color ?> flex items-center justify-center">
                                    <i class="fas <?= $icon ?> text-2xl <?= $icon_class ?>"></i>
                                </div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2 text-center"><?= $kategori->nama_kategori ?></h3>
                            <div class="text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= isset($kategori->jumlah_fasilitas) ? $kategori->jumlah_fasilitas : 0 ?> Fasilitas
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Kategori</h3>
                    <p class="text-gray-500">Belum ada data kategori fasilitas umum yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">Fitur Fasilitas Umum</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4">
                    <i class="fas fa-map-marked-alt text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Lokasi Lengkap</h3>
                <p class="text-gray-600">Informasi lengkap mengenai lokasi fasilitas umum dengan peta interaktif</p>
            </div>
            
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-info-circle text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Informasi Detail</h3>
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