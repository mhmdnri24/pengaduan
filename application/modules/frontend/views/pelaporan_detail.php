<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-12 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto">
            <div class="flex flex-wrap items-center mb-4 gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white flex items-center"
                      style="background-color: <?php echo $report->kategori_warna ?: '#007bff'; ?>;">
                    <i class="<?php echo $report->kategori_icon ?: 'fas fa-folder'; ?> mr-1"></i>
                    <?php echo $report->kategori; ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold text-white flex items-center <?php echo get_status_badge_color_class($report->status); ?>">
                    <i class="fas fa-info-circle mr-1"></i>
                    <?php 
                    $status_labels = [
                        'LAPOR' => 'Menunggu Proses',
                        'DITERIMA' => 'Diterima',
                        'DIKERJAKAN' => 'Sedang Diproses',
                        'DIBATALKAN' => 'Dibatalkan',
                        'SELESAI' => 'Selesai'
                    ];
                    echo $status_labels[$report->status];
                    ?>
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold flex items-center <?php echo get_priority_color_class($report->prioritas); ?>">
                    <i class="fas fa-flag mr-1"></i>
                    Prioritas: <?php echo $report->prioritas; ?>
                </span>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php echo htmlspecialchars($report->judul); ?></h1>
            <div class="flex flex-wrap items-center text-white opacity-90 gap-4">
                <div class="flex items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($report->nama_lengkap ?: $report->pelapor_nama); ?></span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    <span><?php echo date('d F Y, H:i', strtotime($report->created_at)); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V120Z" fill="#F9FAFB"/>
        </svg>
    </div>
</section>

<!-- Report Details -->
<section class="py-8 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Deskripsi Laporan</h2>
                    </div>
                    <div class="prose max-w-none text-gray-700">
                        <p><?php echo nl2br(htmlspecialchars($report->deskripsi)); ?></p>
                    </div>
                </div>
                
                <!-- Location Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-map-marker-alt text-red-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Lokasi Insiden</h2>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-pin text-red-500 mr-2 mt-1"></i>
                            <span class="text-gray-700"><?php echo htmlspecialchars($report->alamat); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($report->lokasi_lat && $report->lokasi_lng): ?>
                    <div id="map" class="h-64 rounded-lg bg-gray-200 shadow-inner"></div>
                    <?php else: ?>
                    <div class="bg-gray-100 p-8 rounded-lg text-center text-gray-500">
                        <i class="fas fa-map-marked-alt text-4xl mb-2"></i>
                        <p>Koordinat lokasi tidak tersedia</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Media Gallery -->
                <?php if (!empty($files)): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-images text-green-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Dokumentasi</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($files as $file): ?>
                        <div class="relative group cursor-pointer overflow-hidden rounded-lg shadow-md transform transition-all duration-300 hover:scale-105" onclick="openLightbox('<?php echo base_url($file->file_path); ?>')">
                            <?php if (strpos($file->file_type, 'image') !== false): ?>
                            <img src="<?php echo base_url($file->file_path); ?>" 
                                 alt="<?php echo htmlspecialchars($file->file_name); ?>"
                                 class="w-full h-48 object-cover">
                            <?php else: ?>
                            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                <i class="fas fa-file text-4xl text-gray-400"></i>
                            </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-end justify-center p-4">
                                <div class="text-white text-center">
                                    <i class="fas fa-search-plus text-2xl mb-2"></i>
                                    <p class="text-sm"><?php echo htmlspecialchars($file->file_name); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Status History -->
                <?php if (!empty($history)): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-history text-purple-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Riwayat Perubahan Status</h2>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($history as $item): ?>
                        <div class="flex items-start p-4 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <i class="fas fa-exchange-alt text-blue-600"></i>
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center mb-1">
                                    <span class="font-medium mr-2">
                                        <?php 
                                        $status_labels = [
                                            'LAPOR' => 'Menunggu Proses',
                                            'DITERIMA' => 'Diterima',
                                            'DIKERJAKAN' => 'Sedang Diproses',
                                            'DIBATALKAN' => 'Dibatalkan',
                                            'SELESAI' => 'Selesai'
                                        ];
                                        echo $status_labels[$item->status_dari] ?: $item->status_dari;
                                        ?>
                                    </span>
                                    <i class="fas fa-arrow-right text-gray-400 mx-2"></i>
                                    <span class="font-medium">
                                        <?php echo $status_labels[$item->status_ke] ?: $item->status_ke; ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mb-1">
                                    <?php if ($item->keterangan): ?>
                                    <?php echo htmlspecialchars($item->keterangan); ?>
                                    <?php else: ?>
                                    Status diperbarui
                                    <?php endif; ?>
                                </p>
                                <p class="text-gray-500 text-xs">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php echo date('d F Y, H:i', strtotime($item->created_at)); ?>
                                    <?php if ($item->updated_by_name): ?>
                                    • oleh <?php echo htmlspecialchars($item->updated_by_name); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Comments Section -->
                <?php if (!empty($comments)): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-comments text-indigo-600"></i>
                        </div>
                        <h2 class="text-xl font-semibold">Komentar</h2>
                    </div>
                    <div class="space-y-4">
                        <?php foreach ($comments as $comment): ?>
                        <div class="border-b border-gray-200 pb-4 last:border-b-0">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <div class="flex-grow">
                                    <div class="flex items-center mb-1">
                                        <span class="font-medium"><?php echo htmlspecialchars($comment->nama_lengkap); ?></span>
                                        <?php if ($comment->rating): ?>
                                        <div class="ml-2 text-yellow-400">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $comment->rating ? '' : 'text-gray-300'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-gray-700 mb-1"><?php echo nl2br(htmlspecialchars($comment->comment)); ?></p>
                                    <p class="text-gray-500 text-xs">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?php echo date('d F Y, H:i', strtotime($comment->created_at)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Report Info Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Informasi Laporan</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500 text-sm flex items-center">
                                <i class="fas fa-hashtag mr-2"></i>Kode Laporan
                            </span>
                            <span class="font-medium"><?php echo htmlspecialchars($report->kode_laporan); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500 text-sm flex items-center">
                                <i class="fas fa-info mr-2"></i>Status Saat Ini
                            </span>
                            <span class="px-2 py-1 rounded text-xs font-semibold text-white <?php echo get_status_badge_color_class($report->status); ?>">
                                <?php echo $status_labels[$report->status]; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500 text-sm flex items-center">
                                <i class="fas fa-flag mr-2"></i>Prioritas
                            </span>
                            <span class="px-2 py-1 rounded text-xs font-semibold <?php echo get_priority_color_class($report->prioritas); ?>">
                                <?php echo $report->prioritas; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500 text-sm flex items-center">
                                <i class="fas fa-calendar mr-2"></i>Tanggal Pelaporan
                            </span>
                            <span class="font-medium"><?php echo date('d F Y', strtotime($report->created_at)); ?></span>
                        </div>
                        <?php if ($report->tanggal_selesai): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-500 text-sm flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>Tanggal Selesai
                            </span>
                            <span class="font-medium"><?php echo date('d F Y', strtotime($report->tanggal_selesai)); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Reporter Info Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-circle text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Informasi Pelapor</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-user text-gray-500 mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Nama</p>
                                <p class="font-medium"><?php echo htmlspecialchars($report->nama_lengkap ?: $report->pelapor_nama); ?></p>
                            </div>
                        </div>
                        <?php if ($report->pelapor_telepon): ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-phone text-gray-500 mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Telepon</p>
                                <p class="font-medium"><?php echo htmlspecialchars($report->pelapor_telepon); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($report->pelapor_alamat): ?>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-home text-gray-500 mr-3"></i>
                            <div>
                                <p class="text-xs text-gray-500">Alamat</p>
                                <p class="font-medium"><?php echo htmlspecialchars($report->pelapor_alamat); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Rating Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-star text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold">Penilaian</h3>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg">
                        <div class="text-3xl font-bold text-yellow-500 mb-2">
                            <?php echo $average_rating->average; ?>
                        </div>
                        <div class="text-yellow-400 mb-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $average_rating->average ? '' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-600 text-sm">
                            <?php echo $average_rating->total; ?> penilaian
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4" onclick="closeLightbox()">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-2xl z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 transition-all">
            <i class="fas fa-times"></i>
        </button>
        <img id="lightbox-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
</div>

<!-- Skeleton Loading -->
<div id="skeleton-loader" class="fixed inset-0 bg-white bg-opacity-90 z-50 flex items-center justify-center hidden">
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">Memuat data...</p>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Data for JavaScript -->
<?php if ($report->lokasi_lat && $report->lokasi_lng): ?>
<div id="pelaporan-detail-data"
     data-latitude="<?= $report->lokasi_lat; ?>"
     data-longitude="<?= $report->lokasi_lng; ?>"
     data-address="<?= htmlspecialchars($report->alamat); ?>"
     data-title="<?= htmlspecialchars($report->judul); ?>"
     style="display: none;"></div>
<?php endif; ?>

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