<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<!-- Leaflet Geocoder CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<style>
.map-container {
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.leaflet-container {
    font-family: 'Inter', sans-serif;
}

.fasilitas-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.fasilitas-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.fasilitas-marker {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 500;
}

.status-aktif {
    background-color: #dcfce7;
    color: #166534;
}

.status-nonaktif {
    background-color: #fee2e2;
    color: #991b1b;
}

.info-window {
    font-family: 'Inter', sans-serif;
}

.info-window h4 {
    margin: 0 0 8px 0;
    color: #1f2937;
    font-size: 16px;
    font-weight: 600;
}

.info-window p {
    margin: 4px 0;
    color: #6b7280;
    font-size: 14px;
}

.info-window .info-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
    margin-top: 8px;
}

.info-window .info-link:hover {
    color: #2563eb;
}
</style>

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
                    <a href="<?= base_url('frontend/detail-' . url_title(isset($kategori) ? $kategori->nama_kategori : '', 'dash', TRUE) . '/' . (isset($kategori) ? $kategori->id : '')) ?>" 
                       class="text-gray-500 hover:text-gray-700">
                        Kategori
                    </a>
                </li>
                <li>
                    <span class="text-gray-500 mx-2">/</span>
                </li>
                <li>
                    <span class="text-gray-900 font-medium">Fasilitas</span>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Header Section -->
<section class="gradient-bg text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl md:text-4xl font-bold mb-4">
                Fasilitas <?= isset($kategori) ? $kategori->nama_kategori : '' ?>
            </h1>
            <p class="text-lg text-gray-200 max-w-3xl mx-auto">
                <?= isset($kategori) && !empty($kategori->deskripsi) ? $kategori->deskripsi : 'Daftar fasilitas yang tersedia dalam kategori ini.' ?>
            </p>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                Peta Lokasi <span class="text-gradient">Fasilitas</span>
            </h2>
            <p class="text-gray-600">
                Temukan lokasi fasilitas <?= isset($kategori) ? $kategori->nama_kategori : '' ?> terdekat di peta
            </p>
        </div>
        
        <div class="map-container" id="fasilitas-map">
            <!-- Map will be loaded here -->
        </div>
    </div>
</section>

<!-- Fasilitas List Section -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                Daftar <span class="text-gradient">Fasilitas</span>
            </h2>
            <p class="text-xl text-gray-600">
                <?= isset($fasilitas_list) && !empty($fasilitas_list) ? 'Jelajahi semua fasilitas yang tersedia' : 'Belum ada fasilitas' ?>
            </p>
        </div>

        <?php if (isset($fasilitas_list) && !empty($fasilitas_list)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($fasilitas_list as $fasilitas): ?>
                    <div class="fasilitas-card bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= $fasilitas->nama_fasilitas ?></h3>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="status-badge <?= $fasilitas->status == 1 ? 'status-aktif' : 'status-nonaktif' ?>">
                                        <i data-lucide="<?= $fasilitas->status == 1 ? 'check-circle' : 'x-circle' ?>" class="w-3 h-3"></i>
                                        <?= $fasilitas->status == 1 ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                    <?php if (!empty($fasilitas->nama_kategori)): ?>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                            <?= $fasilitas->nama_kategori ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="fasilitas-marker">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                            </div>
                        </div>
                        
                        <?php if (!empty($fasilitas->deskripsi)): ?>
                            <p class="text-gray-600 mb-4 text-sm">
                                <?= character_limiter($fasilitas->deskripsi, 120) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="space-y-2 mb-4">
                            <?php if (!empty($fasilitas->alamat)): ?>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                    <span class="text-sm text-gray-600"><?= $fasilitas->alamat ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($fasilitas->nama_kelurahan) || !empty($fasilitas->nama_kecamatan)): ?>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="map" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                    <span class="text-sm text-gray-600">
                                        <?= !empty($fasilitas->nama_kelurahan) ? $fasilitas->nama_kelurahan : '' ?>
                                        <?= !empty($fasilitas->nama_kelurahan) && !empty($fasilitas->nama_kecamatan) ? ', ' : '' ?>
                                        <?= !empty($fasilitas->nama_kecamatan) ? 'Kec. ' . $fasilitas->nama_kecamatan : '' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($fasilitas->telepon)): ?>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="phone" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                                    <span class="text-sm text-gray-600"><?= $fasilitas->telepon ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <button onclick="showDetailModal(<?= htmlspecialchars(json_encode($fasilitas)) ?>)"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center gap-2 mb-2">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Lihat Detail
                        </button>
                        
                        <?php if (!empty($fasilitas->latitude) && !empty($fasilitas->longitude)): ?>
                            <button onclick="showOnMap(<?= $fasilitas->latitude ?>, <?= $fasilitas->longitude ?>, '<?= htmlspecialchars($fasilitas->nama_fasilitas) ?>')"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center justify-center gap-2">
                                <i data-lucide="map" class="w-4 h-4"></i>
                                Tampilkan di Peta
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <i data-lucide="map-pin-off" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Fasilitas</h3>
                <p class="text-gray-500">Belum ada fasilitas yang tersedia dalam kategori ini.</p>
                <div class="mt-8">
                    <a href="<?= base_url('frontend/detail-' . url_title(isset($kategori) ? $kategori->nama_kategori : '', 'dash', TRUE) . '/' . (isset($kategori) ? $kategori->id : '')) ?>" 
                       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span>Kembali ke Kategori</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Back Button Section -->
<section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <a href="<?= base_url('frontend/detail-' . url_title(isset($kategori) ? $kategori->nama_kategori : '', 'dash', TRUE) . '/' . (isset($kategori) ? $kategori->id : '')) ?>" 
               class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-700 font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Kategori</span>
            </a>
        </div>
    </div>
</section>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900" id="modalTitle">Detail Fasilitas</h3>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Informasi Detail -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2" id="modalNamaFasilitas">Nama Fasilitas</h4>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="status-badge" id="modalStatus">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    Aktif
                                </span>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full" id="modalKategori">
                                    Kategori
                                </span>
                            </div>
                        </div>
                        
                        <div id="modalDeskripsi" class="text-gray-600"></div>
                        
                        <div class="space-y-3">
                            <div class="flex items-start gap-3" id="modalAlamatContainer">
                                <i data-lucide="map-pin" class="w-5 h-5 text-gray-400 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Alamat</p>
                                    <p class="text-gray-600" id="modalAlamat"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3" id="modalWilayahContainer">
                                <i data-lucide="map" class="w-5 h-5 text-gray-400 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Wilayah</p>
                                    <p class="text-gray-600" id="modalWilayah"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3" id="modalTeleponContainer">
                                <i data-lucide="phone" class="w-5 h-5 text-gray-400 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Telepon</p>
                                    <p class="text-gray-600" id="modalTelepon"></p>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-3">
                                <i data-lucide="coordinates" class="w-5 h-5 text-gray-400 mt-0.5"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Koordinat</p>
                                    <p class="text-gray-600" id="modalKoordinat"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Peta -->
                    <div>
                        <div class="bg-gray-100 rounded-lg p-4 h-96" id="modalMap">
                            <!-- Map will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Leaflet Geocoder JS -->
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<!-- Data for JavaScript -->
<?php if (isset($fasilitas_list) && !empty($fasilitas_list)): ?>
<div id="facilities-data" data-facilities='<?= json_encode($fasilitas_list) ?>' style="display: none;"></div>
<?php endif; ?>

<!-- Include centralized JavaScript -->
<?php $this->load->view('frontend/js_frontend'); ?>