<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <div class="mb-6">
                <i class="fas fa-exclamation-triangle text-6xl mb-4 opacity-80"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Pelaporan Insiden Publik</h1>
            <p class="text-xl opacity-90">Lihat dan pantau semua laporan insiden yang telah diajukan oleh masyarakat</p>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V120Z" fill="#F9FAFB"/>
        </svg>
    </div>
</section>

<!-- Statistics Cards -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Total Reports Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 text-center transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
                </div>
                <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $statistics['total']; ?></div>
                <div class="text-gray-600 text-sm">Total Laporan</div>
            </div>
            
            <!-- Status Cards -->
            <?php foreach ($status_list as $status_key => $status_label): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 text-center transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="w-12 h-12 bg-<?php echo get_status_bg_color($status_key); ?>-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <i class="<?php echo get_status_icon($status_key); ?> text-<?php echo get_status_bg_color($status_key); ?>-600 text-xl"></i>
                </div>
                <div class="text-3xl font-bold <?php echo get_status_color_class($status_key); ?> mb-2">
                    <?php echo isset($statistics['by_status'][$status_key]) ? $statistics['by_status'][$status_key] : 0; ?>
                </div>
                <div class="text-gray-600 text-sm"><?php echo $status_label; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-filter text-indigo-600"></i>
                </div>
                <h2 class="text-xl font-semibold">Filter dan Pencarian</h2>
            </div>
            
            <form method="GET" action="<?php echo site_url('frontend/pelaporan'); ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search mr-1"></i>Cari Laporan
                        </label>
                        <input type="text" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Judul atau deskripsi...">
                    </div>
                    
                    <!-- Category Filter -->
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-folder mr-1"></i>Kategori
                        </label>
                        <select id="kategori" name="kategori" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?php echo $kat->nama_kategori; ?>" 
                                    <?php echo ($kategori == $kat->nama_kategori) ? 'selected' : ''; ?>>
                                <?php echo $kat->nama_kategori; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-info-circle mr-1"></i>Status
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <?php foreach ($status_list as $status_key => $status_label): ?>
                            <option value="<?php echo $status_key; ?>" 
                                    <?php echo ($status == $status_key) ? 'selected' : ''; ?>>
                                <?php echo $status_label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Sort -->
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-sort mr-1"></i>Urutkan
                        </label>
                        <select id="sort" name="sort" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="terbaru" <?php echo ($sort == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="terlama" <?php echo ($sort == 'terlama') ? 'selected' : ''; ?>>Terlama</option>
                            <option value="prioritas" <?php echo ($sort == 'prioritas') ? 'selected' : ''; ?>>Prioritas</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2 rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Reports List -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <?php if (empty($reports)): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak Ada Laporan</h3>
            <p class="text-gray-500">Belum ada laporan yang sesuai dengan kriteria pencarian Anda.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($reports as $report): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <!-- Thumbnail -->
                <div class="h-48 bg-gray-200 relative overflow-hidden">
                    <?php if ($report->thumbnail): ?>
                    <img src="<?php echo base_url($report->thumbnail); ?>" 
                         alt="<?php echo htmlspecialchars($report->judul); ?>"
                         class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                        <i class="fas fa-image text-6xl text-gray-400"></i>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold text-white flex items-center <?php echo get_status_badge_color_class($report->status); ?>">
                            <i class="<?php echo get_status_icon($report->status); ?> mr-1"></i>
                            <?php echo $status_list[$report->status]; ?>
                        </span>
                    </div>
                    
                    <!-- Category Badge -->
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold text-white flex items-center"
                              style="background-color: <?php echo $report->kategori_warna ?: '#007bff'; ?>;">
                            <i class="<?php echo $report->kategori_icon ?: 'fas fa-folder'; ?> mr-1"></i>
                            <?php echo $report->kategori; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2 line-clamp-2"><?php echo htmlspecialchars($report->judul); ?></h3>
                    <p class="text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($report->deskripsi); ?></p>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle mr-1"></i>
                            <span><?php echo htmlspecialchars($report->nama_lengkap ?: $report->pelapor_nama); ?></span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            <span><?php echo date('d M Y', strtotime($report->created_at)); ?></span>
                        </div>
                    </div>
                    
                    <!-- Priority Indicator -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-sm font-medium mr-2">Prioritas:</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold flex items-center <?php echo get_priority_color_class($report->prioritas); ?>">
                                <i class="fas fa-flag mr-1"></i>
                                <?php echo $report->prioritas; ?>
                            </span>
                        </div>
                        
                        <button onclick="showPelaporanDetail(<?php echo $report->id; ?>)"
                                class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center group">
                            Lihat Detail
                            <i class="fas fa-arrow-right ml-1 transform transition-transform duration-300 group-hover:translate-x-1"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">1</a>';
                    if ($start_page > 2) {
                        echo '<span class="px-3 py-2 text-gray-500">...</span>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $active_class = ($i == $page) ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors';
                    echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="px-3 py-2 rounded-lg ' . $active_class . '">' . $i . '</a>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<span class="px-3 py-2 text-gray-500">...</span>';
                    }
                    echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">' . $total_pages . '</a>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                   class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Skeleton Loading -->
<div id="skeleton-loader" class="fixed inset-0 bg-white bg-opacity-90 z-50 flex items-center justify-center hidden">
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Memuat data...</p>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<!-- Modal Detail Pelaporan -->
<div id="pelaporanDetailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-7xl w-full max-h-[95vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-file-alt mr-3"></i>
                        Detail Laporan
                    </h2>
                    <button onclick="closePelaporanDetail()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Content -->
            <div id="pelaporanDetailContent" class="p-6">
                <!-- Loading State -->
                <div class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">Memuat detail laporan...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox for Images -->
<div id="imageLightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center">
    <div class="relative max-w-5xl max-h-screen p-4">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <i class="fas fa-times text-3xl"></i>
        </button>
        <img id="lightboxImage" src="" alt="Image" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
        <div id="lightboxCaption" class="text-white text-center mt-4 text-lg"></div>
    </div>
</div>

<!-- Include centralized JavaScript -->
<?php $this->load->view('frontend/js_frontend'); ?>

<?php
// Helper functions for status and priority colors
function get_status_color_class($status) {
    $colors = [
        'LAPOR' => 'text-yellow-600',
        'DITERIMA' => 'text-blue-600',
        'DIKERJAKAN' => 'text-indigo-600',
        'DIBATALKAN' => 'text-red-600',
        'SELESAI' => 'text-green-600'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : 'text-gray-600';
}

function get_status_bg_color($status) {
    $colors = [
        'LAPOR' => 'yellow',
        'DITERIMA' => 'blue',
        'DIKERJAKAN' => 'indigo',
        'DIBATALKAN' => 'red',
        'SELESAI' => 'green'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : 'gray';
}

function get_status_icon($status) {
    $icons = [
        'LAPOR' => 'fas fa-clock',
        'DITERIMA' => 'fas fa-check-circle',
        'DIKERJAKAN' => 'fas fa-spinner',
        'DIBATALKAN' => 'fas fa-times-circle',
        'SELESAI' => 'fas fa-check-double'
    ];
    
    return isset($icons[$status]) ? $icons[$status] : 'fas fa-info-circle';
}

function get_status_badge_color_class($status) {
    $colors = [
        'LAPOR' => 'bg-yellow-500',
        'DITERIMA' => 'bg-blue-500',
        'DIKERJAKAN' => 'bg-indigo-500',
        'DIBATALKAN' => 'bg-red-500',
        'SELESAI' => 'bg-green-500'
    ];
    
    return isset($colors[$status]) ? $colors[$status] : 'bg-gray-500';
}

function get_priority_color_class($priority) {
    $colors = [
        'RENDAH' => 'bg-green-100 text-green-800',
        'SEDANG' => 'bg-yellow-100 text-yellow-800',
        'TINGGI' => 'bg-orange-100 text-orange-800',
        'URGENT' => 'bg-red-100 text-red-800'
    ];
    
    return isset($colors[$priority]) ? $colors[$priority] : 'bg-gray-100 text-gray-800';
}
?>