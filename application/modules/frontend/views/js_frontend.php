<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!-- Centralized Frontend JavaScript -->
<script>
/**
 * Centralized Frontend JavaScript for Dashboard Masyarakat
 * Contains all JavaScript functions for frontend modules
 */

// Global variables
let detailMap = null;
let pelaporanMap = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize common features
    initializeCommonFeatures();
    
    // Initialize page-specific features based on current page
    const currentPage = document.body.getAttribute('data-page');
    if (currentPage) {
        initializePageFeatures(currentPage);
    }
});

/**
 * Initialize common features for all pages
 */
function initializeCommonFeatures() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all cards
    document.querySelectorAll('.card-hover, .transform').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Initialize skeleton loader for forms
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            const skeletonLoader = document.getElementById('skeleton-loader');
            if (skeletonLoader) {
                skeletonLoader.classList.remove('hidden');
            }
        });
    }
}

/**
 * Initialize page-specific features
 */
function initializePageFeatures(page) {
    switch(page) {
        case 'beranda':
            initializeBerandaFeatures();
            break;
        case 'fasilitas-list':
            initializeFasilitasListFeatures();
            break;
        case 'fasilitas':
        case 'detail-kategori':
            initializeFasilitasKategoriFeatures();
            break;
        case 'harga_komoditas':
            initializeHargaKomoditasFeatures();
            break;
        case 'pelaporan':
            initializePelaporanFeatures();
            break;
        case 'pelaporan-detail':
            initializePelaporanDetailFeatures();
            break;
    }
}

/**
 * Beranda page features
 */
function initializeBerandaFeatures() {
    // Slider functionality is handled by Alpine.js in the template
    console.log('Beranda page initialized');
}

/**
 * Fasilitas List page features
 */
function initializeFasilitasListFeatures() {
    // Initialize map if container exists
    const mapContainer = document.getElementById('fasilitas-map');
    if (mapContainer && typeof L !== 'undefined') {
        initializeFasilitasMap();
    }
    
    // Load Leaflet if not available
    if (typeof L === 'undefined' && mapContainer) {
        loadLeafletLibrary(() => {
            initializeFasilitasMap();
        });
    }
}

/**
 * Initialize Fasilitas Map
 */
function initializeFasilitasMap() {
    const map = L.map('fasilitas-map').setView([-3.3194, 114.5908], 12); // Default to Indonesia center
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Custom icon for markers
    const createCustomIcon = (color = '#3b82f6') => {
        return L.divIcon({
            html: `<div style="background: ${color}; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);">
                <i class="fas fa-map-pin" style="width: 16px; height: 16px;"></i>
            </div>`,
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });
    };
    
    // Add markers for each facility if data is available
    const facilitiesData = document.querySelector('[data-facilities]');
    if (facilitiesData) {
        const facilities = JSON.parse(facilitiesData.getAttribute('data-facilities'));
        const bounds = [];
        
        facilities.forEach(function(fasilitas) {
            if (fasilitas.latitude && fasilitas.longitude) {
                const lat = parseFloat(fasilitas.latitude);
                const lng = parseFloat(fasilitas.longitude);
                
                // Create popup content
                const popupContent = `
                    <div class="info-window">
                        <h4>${fasilitas.nama_fasilitas}</h4>
                        <p><strong>Kategori:</strong> ${fasilitas.nama_kategori || '-'}</p>
                        <p><strong>Alamat:</strong> ${fasilitas.alamat || '-'}</p>
                        <p><strong>Wilayah:</strong> ${fasilitas.nama_kelurahan || '-'}${fasilitas.nama_kelurahan && fasilitas.nama_kecamatan ? ', ' : ''}${fasilitas.nama_kecamatan || ''}</p>
                        ${fasilitas.telepon ? `<p><strong>Telepon:</strong> ${fasilitas.telepon}</p>` : ''}
                        <p><strong>Status:</strong> <span style="color: ${fasilitas.status == 1 ? '#16a34a' : '#dc2626'}">${fasilitas.status == 1 ? 'Aktif' : 'Nonaktif'}</span></p>
                    </div>
                `;
                
                // Create marker
                const marker = L.marker([lat, lng], {
                    icon: createCustomIcon(fasilitas.status == 1 ? '#16a34a' : '#dc2626')
                }).addTo(map);
                
                marker.bindPopup(popupContent);
                
                // Add to bounds for auto-zoom
                bounds.push([lat, lng]);
            }
        });
        
        // Fit map to show all markers
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }
}

/**
 * Fasilitas Kategori page features
 */
function initializeFasilitasKategoriFeatures() {
    console.log('Fasilitas Kategori page initialized');
}

/**
 * Harga Komoditas page features
 */
function initializeHargaKomoditasFeatures() {
    console.log('Harga Komoditas page initialized');
}

/**
 * Pelaporan page features
 */
function initializePelaporanFeatures() {
    console.log('Pelaporan page initialized');
}

/**
 * Pelaporan Detail page features
 */
function initializePelaporanDetailFeatures() {
    // Initialize map if coordinates are available
    const mapContainer = document.getElementById('map');
    if (mapContainer && typeof L !== 'undefined') {
        initializePelaporanDetailMap();
    }
    
    // Load Leaflet if not available
    if (typeof L === 'undefined' && mapContainer) {
        loadLeafletLibrary(() => {
            initializePelaporanDetailMap();
        });
    }
}

/**
 * Initialize Pelaporan Detail Map
 */
function initializePelaporanDetailMap() {
    const latElement = document.querySelector('[data-latitude]');
    const lngElement = document.querySelector('[data-longitude]');
    const addressElement = document.querySelector('[data-address]');
    
    if (latElement && lngElement) {
        const lat = parseFloat(latElement.getAttribute('data-latitude'));
        const lng = parseFloat(lngElement.getAttribute('data-longitude'));
        const address = addressElement ? addressElement.getAttribute('data-address') : '';
        
        const map = L.map('map').setView([lat, lng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup(`<b>Lokasi Kejadian</b><br>${address}`).openPopup();
    }
}

/**
 * Load Leaflet Library dynamically
 */
function loadLeafletLibrary(callback) {
    // Load CSS
    const leafletCSS = document.createElement('link');
    leafletCSS.rel = 'stylesheet';
    leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(leafletCSS);
    
    // Load JS
    const leafletJS = document.createElement('script');
    leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    leafletJS.onload = callback;
    document.head.appendChild(leafletJS);
}

/**
 * Global Functions
 */

// Show facility on map
window.showOnMap = function(lat, lng, name) {
    if (typeof L === 'undefined') {
        loadLeafletLibrary(() => {
            showOnMap(lat, lng, name);
        });
        return;
    }
    
    const mapContainer = document.getElementById('fasilitas-map');
    if (!mapContainer) return;
    
    // Get map instance
    const map = window.fasilitasMapInstance;
    if (!map) return;
    
    const marker = L.marker([lat, lng], {
        icon: L.divIcon({
            html: `<div style="background: #dc2626; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);">
                <i class="fas fa-map-pin" style="width: 16px; height: 16px;"></i>
            </div>`,
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        })
    }).addTo(map);
    
    marker.bindPopup(`<div class="info-window"><h4>${name}</h4><p>Klik marker untuk melihat detail</p></div>`).openPopup();
    map.setView([lat, lng], 16);
};

// Show detail modal for facilities
window.showDetailModal = function(fasilitas) {
    // Set modal content
    document.getElementById('modalNamaFasilitas').textContent = fasilitas.nama_fasilitas;
    document.getElementById('modalTitle').textContent = 'Detail ' + fasilitas.nama_fasilitas;
    
    // Status badge
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.className = 'status-badge ' + (fasilitas.status == 1 ? 'status-aktif' : 'status-nonaktif');
    statusBadge.innerHTML = `
        <i class="fas ${fasilitas.status == 1 ? 'check-circle' : 'x-circle'} w-3 h-3"></i>
        ${fasilitas.status == 1 ? 'Aktif' : 'Nonaktif'}
    `;
    
    // Kategori
    document.getElementById('modalKategori').textContent = fasilitas.nama_kategori || '-';
    
    // Deskripsi
    const deskripsiContainer = document.getElementById('modalDeskripsi');
    if (fasilitas.deskripsi) {
        deskripsiContainer.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="fas fa-file-text w-5 h-5 text-gray-400 mt-0.5"></i>
                <div>
                    <p class="font-medium text-gray-900">Deskripsi</p>
                    <p class="text-gray-600">${fasilitas.deskripsi}</p>
                </div>
            </div>
        `;
        deskripsiContainer.style.display = 'block';
    } else {
        deskripsiContainer.style.display = 'none';
    }
    
    // Alamat
    const alamatContainer = document.getElementById('modalAlamatContainer');
    if (fasilitas.alamat) {
        document.getElementById('modalAlamat').textContent = fasilitas.alamat;
        alamatContainer.style.display = 'flex';
    } else {
        alamatContainer.style.display = 'none';
    }
    
    // Wilayah
    const wilayahContainer = document.getElementById('modalWilayahContainer');
    const wilayahText = [];
    if (fasilitas.nama_kelurahan) wilayahText.push(fasilitas.nama_kelurahan);
    if (fasilitas.nama_kecamatan) wilayahText.push('Kec. ' + fasilitas.nama_kecamatan);
    if (wilayahText.length > 0) {
        document.getElementById('modalWilayah').textContent = wilayahText.join(', ');
        wilayahContainer.style.display = 'flex';
    } else {
        wilayahContainer.style.display = 'none';
    }
    
    // Telepon
    const teleponContainer = document.getElementById('modalTeleponContainer');
    if (fasilitas.telepon) {
        document.getElementById('modalTelepon').textContent = fasilitas.telepon;
        teleponContainer.style.display = 'flex';
    } else {
        teleponContainer.style.display = 'none';
    }
    
    // Koordinat
    const koordinatText = fasilitas.latitude && fasilitas.longitude
        ? `${fasilitas.latitude}, ${fasilitas.longitude}`
        : '-';
    document.getElementById('modalKoordinat').textContent = koordinatText;
    
    // Show modal
    document.getElementById('detailModal').classList.remove('hidden');
    
    // Initialize map in modal
    setTimeout(() => {
        if (detailMap) {
            detailMap.remove();
        }
        
        if (typeof L === 'undefined') {
            loadLeafletLibrary(() => {
                initializeModalMap(fasilitas);
            });
        } else {
            initializeModalMap(fasilitas);
        }
    }, 100);
};

/**
 * Initialize modal map
 */
function initializeModalMap(fasilitas) {
    detailMap = L.map('modalMap').setView([-3.3194, 114.5908], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(detailMap);
    
    // Add marker if coordinates available
    if (fasilitas.latitude && fasilitas.longitude) {
        const lat = parseFloat(fasilitas.latitude);
        const lng = parseFloat(fasilitas.longitude);
        
        const marker = L.marker([lat, lng], {
            icon: L.divIcon({
                html: `<div style="background: ${fasilitas.status == 1 ? '#16a34a' : '#dc2626'}; color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);">
                    <i class="fas fa-map-pin" style="width: 16px; height: 16px;"></i>
                </div>`,
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            })
        }).addTo(detailMap);
        
        const wilayahText = [];
        if (fasilitas.nama_kelurahan) wilayahText.push(fasilitas.nama_kelurahan);
        if (fasilitas.nama_kecamatan) wilayahText.push('Kec. ' + fasilitas.nama_kecamatan);
        
        marker.bindPopup(`
            <div class="info-window">
                <h4>${fasilitas.nama_fasilitas}</h4>
                <p><strong>Kategori:</strong> ${fasilitas.nama_kategori || '-'}</p>
                <p><strong>Alamat:</strong> ${fasilitas.alamat || '-'}</p>
                <p><strong>Wilayah:</strong> ${wilayahText.join(', ') || '-'}</p>
                ${fasilitas.telepon ? `<p><strong>Telepon:</strong> ${fasilitas.telepon}</p>` : ''}
                <p><strong>Status:</strong> <span style="color: ${fasilitas.status == 1 ? '#16a34a' : '#dc2626'}">${fasilitas.status == 1 ? 'Aktif' : 'Nonaktif'}</span></p>
            </div>
        `).openPopup();
        
        detailMap.setView([lat, lng], 16);
    }
}

// Close detail modal
window.closeDetailModal = function() {
    document.getElementById('detailModal').classList.add('hidden');
    if (detailMap) {
        detailMap.remove();
        detailMap = null;
    }
};

// Show pelaporan detail
window.showPelaporanDetail = function(pelaporanId) {
    // Show modal
    document.getElementById('pelaporanDetailModal').classList.remove('hidden');
    
    // Reset content to loading state
    document.getElementById('pelaporanDetailContent').innerHTML = `
        <div class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">Memuat detail laporan...</p>
        </div>
    `;
    
    // Fetch data via AJAX
    fetch('<?= site_url('frontend/ajax_get_pelaporan_detail'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'pelaporan_id=' + pelaporanId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            renderPelaporanDetail(data.data);
        } else {
            document.getElementById('pelaporanDetailContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Error</h3>
                    <p class="text-gray-600">${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('pelaporanDetailContent').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Error</h3>
                <p class="text-gray-600">Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
            </div>
        `;
    });
};

/**
 * Render pelaporan detail
 */
function renderPelaporanDetail(data) {
    const report = data.report;
    const files = data.files || [];
    const history = data.history || [];
    const comments = data.comments || [];
    const averageRating = data.average_rating || { average: 0, total: 0 };
    
    // Format tanggal
    const formatDate = (dateString) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };
    
    // Get status color
    const getStatusColor = (status) => {
        const colors = {
            'LAPOR': 'text-yellow-600 bg-yellow-100',
            'DITERIMA': 'text-blue-600 bg-blue-100',
            'DIKERJAKAN': 'text-indigo-600 bg-indigo-100',
            'DIBATALKAN': 'text-red-600 bg-red-100',
            'SELESAI': 'text-green-600 bg-green-100'
        };
        return colors[status] || 'text-gray-600 bg-gray-100';
    };
    
    // Get priority color
    const getPriorityColor = (priority) => {
        const colors = {
            'RENDAH': 'bg-green-100 text-green-800',
            'SEDANG': 'bg-yellow-100 text-yellow-800',
            'TINGGI': 'bg-orange-100 text-orange-800',
            'URGENT' : 'bg-red-100 text-red-800'
        };
        return colors[priority] || 'bg-gray-100 text-gray-800';
    };
    
    // Generate stars HTML
    const generateStars = (rating) => {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                stars += '<i class="far fa-star text-yellow-400"></i>';
            }
        }
        return stars;
    };
    
    // Check if file is image
    const isImage = (file) => {
        const fileExtension = file.file_path ? file.file_path.split('.').pop().toLowerCase() : '';
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        
        if (file.file_type) {
            return file.file_type.toLowerCase().includes('image');
        }
        
        return imageExtensions.includes(fileExtension);
    };
    
    // Render HTML
    const html = `
        <div class="space-y-6">
            <!-- Header Info -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6 border border-blue-100">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">${report.judul}</h3>
                        <div class="flex flex-wrap items-center gap-3 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusColor(report.status)}">
                                <i class="fas fa-info-circle mr-1"></i>${report.status}
                            </span>
                            <span class="px-3 py-1 rounded text-xs font-semibold ${getPriorityColor(report.prioritas)}">
                                <i class="fas fa-flag mr-1"></i>${report.prioritas}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: ${report.kategori_warna || '#007bff'}">
                                <i class="${report.kategori_icon || 'fas fa-folder'} mr-1"></i>${report.kategori}
                            </span>
                        </div>
                    </div>
                    <div class="text-right bg-white rounded-lg p-3 shadow-sm">
                        <p class="text-xs text-gray-500 flex items-center justify-end">
                            <i class="fas fa-hashtag mr-1"></i>
                            Nomor Pelapor
                        </p>
                        <p class="font-bold text-lg text-blue-600">${report.kode_laporan || '#' + report.id}</p>
                    </div>
                </div>
            </div>
            
            <!-- Basic Information -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informasi Dasar
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 flex items-center mb-1">
                            <i class="fas fa-user mr-2 text-gray-400"></i>
                            Nama Pelapor
                        </p>
                        <p class="font-medium text-gray-800">${report.pelapor_nama || report.nama_lengkap || '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 flex items-center mb-1">
                            <i class="fas fa-phone mr-2 text-gray-400"></i>
                            Kontak
                        </p>
                        <p class="font-medium text-gray-800">${report.pelapor_kontak || '-'}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 flex items-center mb-1">
                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                            Tanggal Laporan
                        </p>
                        <p class="font-medium text-gray-800">${formatDate(report.created_at)}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 flex items-center mb-1">
                            <i class="fas fa-clock mr-2 text-gray-400"></i>
                            Waktu Kejadian
                        </p>
                        <p class="font-medium text-gray-800">${report.waktu_kejadian || '-'}</p>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-align-left mr-2 text-blue-600"></i>
                    Deskripsi Laporan
                </h4>
                <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">${report.deskripsi || 'Tidak ada deskripsi'}</p>
            </div>
            
            <!-- Location Map -->
            ${report.lokasi_lat && report.lokasi_lng ? `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-map-marked-alt mr-2 text-red-600"></i>
                    Lokasi Kejadian
                </h4>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600 mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <span class="font-medium">${report.alamat || '-'}</span>
                        </p>
                        <p class="text-sm text-gray-500 flex items-center">
                            <i class="fas fa-globe mr-1"></i>
                            Koordinat: ${report.lokasi_lat}, ${report.lokasi_lng}
                        </p>
                    </div>
                    <div>
                        <div id="pelaporanMap" class="h-64 rounded-lg overflow-hidden border border-gray-300"></div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            <!-- Image Gallery -->
            ${files.length > 0 ? `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-images mr-2 text-green-600"></i>
                    Dokumentasi Gambar
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${files.map((file, index) => {
                        const imageUrl = file.file_path;
                        
                        return `
                        <div class="relative group cursor-pointer rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-all duration-300" onclick="openLightbox('${imageUrl}', '${file.file_name || 'Dokumentasi'}')">
                            <img src="/${imageUrl}" alt="${file.file_name || 'Dokumentasi'}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-2xl"></i>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-2">
                                <p class="text-white text-xs truncate">${file.file_name || 'Dokumentasi'}</p>
                            </div>
                        </div>
                    `;}).join('')}
                </div>
            </div>
            ` : '<div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm"><p class="text-gray-500 text-center">Tidak ada gambar yang tersedia untuk laporan ini.</p></div>'}
            
            <!-- Rating Section (Display Only) -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-star mr-2 text-yellow-500"></i>
                    Penilaian Masyarakat
                </h4>
                <div class="text-center">
                    <p class="text-gray-600 mb-2">Rating Rata-rata</p>
                    <div class="flex items-center justify-center mb-2">
                        ${generateStars(Math.round(averageRating.average))}
                    </div>
                    <p class="text-2xl font-bold text-gray-800">${averageRating.average}</p>
                    <p class="text-sm text-gray-500">dari ${averageRating.total} evaluasi</p>
                </div>
            </div>
            
            <!-- History -->
            ${history.length > 0 ? `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-history mr-2 text-purple-600"></i>
                    Riwayat Perubahan
                </h4>
                <div class="space-y-3">
                    ${history.map(item => `
                        <div class="flex items-start bg-gray-50 rounded-lg p-3">
                            <div class="w-3 h-3 bg-purple-600 rounded-full mt-1 mr-3"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">${item.keterangan}</p>
                                <p class="text-xs text-gray-500">${item.updated_by_name || 'System'} • ${formatDate(item.created_at)}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
            
            <!-- Comments -->
            ${comments.length > 0 ? `
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h4 class="text-lg font-semibold mb-3 flex items-center text-gray-800">
                    <i class="fas fa-comments mr-2 text-indigo-600"></i>
                    Komentar & Feedback
                </h4>
                <div class="space-y-4">
                    ${comments.map(comment => `
                        <div class="border-l-4 border-indigo-500 bg-indigo-50 rounded-r-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="font-medium text-gray-800">
                                    <i class="fas fa-user-circle mr-1"></i>${comment.nama_lengkap}
                                </p>
                                <p class="text-xs text-gray-500">${formatDate(comment.created_at)}</p>
                            </div>
                            <p class="text-gray-700 mb-2">${comment.komentar}</p>
                            ${comment.rating ? `
                            <div class="flex items-center">
                                <span class="text-xs text-gray-500 mr-2">Rating:</span>
                                ${generateStars(comment.rating)}
                            </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
    
    document.getElementById('pelaporanDetailContent').innerHTML = html;
    
    // Initialize map if coordinates are available
    if (report.lokasi_lat && report.lokasi_lng) {
        setTimeout(() => {
            initializePelaporanMap(report.lokasi_lat, report.lokasi_lng, report.alamat);
        }, 100);
    }
}

/**
 * Initialize Pelaporan Map
 */
function initializePelaporanMap(lat, lng, address) {
    if (typeof L === 'undefined') {
        loadLeafletLibrary(() => {
            initializePelaporanMap(lat, lng, address);
        });
        return;
    }
    
    const mapContainer = document.getElementById('pelaporanMap');
    if (!mapContainer) return;
    
    pelaporanMap = L.map('pelaporanMap', {
        center: [lat, lng],
        zoom: 15,
        scrollWheelZoom: false
    });
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(pelaporanMap);
    
    const customIcon = L.divIcon({
        html: '<div style="background-color: #e74c3c; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
    });
    
    const marker = L.marker([lat, lng], { icon: customIcon }).addTo(pelaporanMap);
    marker.bindPopup(`
        <div style="min-width: 200px;">
            <b>Lokasi Kejadian</b><br>
            ${address || 'Tidak ada alamat'}<br>
            <small style="color: #666;">Koordinat: ${lat}, ${lng}</small>
        </div>
    `).openPopup();
    
    setTimeout(() => {
        pelaporanMap.invalidateSize();
    }, 100);
}

// Close pelaporan detail modal
window.closePelaporanDetail = function() {
    document.getElementById('pelaporanDetailModal').classList.add('hidden');
};

// Lightbox functions
window.openLightbox = function(imageSrc, caption) {
    const imageUrl = imageSrc.startsWith('/') ? imageSrc : '/' + imageSrc;
    document.getElementById('lightboxImage').src = imageUrl;
    document.getElementById('lightboxCaption').textContent = caption || '';
    document.getElementById('imageLightbox').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeLightbox = function() {
    document.getElementById('imageLightbox').classList.add('hidden');
    document.body.style.overflow = 'auto';
};

// Chart functions for harga komoditas
window.showPriceChart = function(komoditasId, komoditasNama) {
    document.getElementById('chartModal').classList.remove('hidden');
    
    fetch('<?= site_url('frontend/ajax_harga_chart'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'komoditas_id=' + komoditasId + '&pasar_id=<?= isset($pasar_id) ? $pasar_id : "all"; ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            drawChart(data.data);
        } else {
            alert(data.message);
            closeChartModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data grafik');
        closeChartModal();
    });
};

window.closeChartModal = function() {
    document.getElementById('chartModal').classList.add('hidden');
};

function drawChart(chartData) {
    const ctx = document.getElementById('priceChart').getContext('2d');
    
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    ctx.canvas.width = ctx.canvas.offsetWidth;
    ctx.canvas.height = 300;
    
    const padding = 40;
    const chartWidth = ctx.canvas.width - (padding * 2);
    const chartHeight = ctx.canvas.height - (padding * 2);
    
    const maxValue = Math.max(...chartData.data);
    const minValue = Math.min(...chartData.data);
    const range = maxValue - minValue || 1;
    
    // Draw axes
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, ctx.canvas.height - padding);
    ctx.lineTo(ctx.canvas.width - padding, ctx.canvas.height - padding);
    ctx.stroke();
    
    // Draw data points and lines
    if (chartData.data.length > 0) {
        ctx.strokeStyle = '#3b82f6';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        for (let i = 0; i < chartData.data.length; i++) {
            const x = padding + (i / (chartData.data.length - 1)) * chartWidth;
            const y = ctx.canvas.height - padding - ((chartData.data[i] - minValue) / range) * chartHeight;
            
            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        }
        
        ctx.stroke();
        
        // Draw data points
        ctx.fillStyle = '#3b82f6';
        for (let i = 0; i < chartData.data.length; i++) {
            const x = padding + (i / (chartData.data.length - 1)) * chartWidth;
            const y = ctx.canvas.height - padding - ((chartData.data[i] - minValue) / range) * chartHeight;
            
            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
            
            // Draw value labels
            ctx.fillStyle = '#374151';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('Rp ' + chartData.data[i].toLocaleString('id-ID'), x, y - 10);
            
            // Draw date labels
            ctx.fillText(chartData.labels[i], x, ctx.canvas.height - padding + 20);
            ctx.fillStyle = '#3b82f6';
        }
    }
    
    // Draw title
    ctx.fillStyle = '#111827';
    ctx.font = 'bold 16px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Trend Harga ' + chartData.komoditas_nama + ' (' + chartData.komoditas_satuan + ')', ctx.canvas.width / 2, 20);
}

// Store map instance globally
window.fasilitasMapInstance = null;

// Set map instance when initialized
document.addEventListener('DOMContentLoaded', function() {
    const mapContainer = document.getElementById('fasilitas-map');
    if (mapContainer) {
        // Wait for map to be initialized
        setTimeout(() => {
            if (window.L && window.L && window.L.Map && document.getElementById('fasilitas-map')._leaflet_id) {
                window.fasilitasMapInstance = window.L.mapInstances?.['fasilitas-map'];
            }
        }, 1000);
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const detailModal = document.getElementById('detailModal');
    const pelaporanModal = document.getElementById('pelaporanDetailModal');
    const imageLightbox = document.getElementById('imageLightbox');
    
    if (detailModal && e.target === detailModal) {
        closeDetailModal();
    }
    
    if (pelaporanModal && e.target === pelaporanModal) {
        closePelaporanDetail();
    }
    
    if (imageLightbox && e.target === imageLightbox) {
        closeLightbox();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const imageLightbox = document.getElementById('imageLightbox');
        const pelaporanModal = document.getElementById('pelaporanDetailModal');
        const detailModal = document.getElementById('detailModal');
        
        if (imageLightbox && !imageLightbox.classList.contains('hidden')) {
            closeLightbox();
        } else if (pelaporanModal && !pelaporanModal.classList.contains('hidden')) {
            closePelaporanDetail();
        } else if (detailModal && !detailModal.classList.contains('hidden')) {
            closeDetailModal();
        }
    }
});

/**
 * WhatsApp Widget Functions
 */
function initializeWhatsAppWidget() {
    const widget = document.getElementById('whatsapp-widget');
    if (!widget) return;
    
    // Get widget data attributes
    const phone = widget.getAttribute('data-phone');
    const message = widget.getAttribute('data-message');
    const analytics = widget.getAttribute('data-analytics') === '1';
    const tooltip = widget.getAttribute('data-tooltip') === '1';
    const badge = widget.getAttribute('data-badge') === '1';
    
    // Lazy loading - show widget after page load
    setTimeout(() => {
        widget.classList.add('visible');
        // Add bounce animation on first load
        widget.classList.add('bounce');
        setTimeout(() => {
            widget.classList.remove('bounce');
        }, 2000);
    }, 1500);
    
    // Click handler for WhatsApp widget
    function handleWhatsAppClick(e) {
        e.preventDefault();
        
        // Track analytics if enabled
        if (analytics) {
            trackWhatsAppClick();
        }
        
        // Open WhatsApp with fallback
        openWhatsAppWithFallback(phone, message);
    }
    
    // Add click event listener
    widget.addEventListener('click', handleWhatsAppClick);
    
    // Add keyboard support for accessibility
    widget.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            handleWhatsAppClick(e);
        }
    });
    
    // Tooltip hover effects
    if (tooltip) {
        const tooltipElement = widget.querySelector('.whatsapp-tooltip');
        if (tooltipElement) {
            widget.addEventListener('mouseenter', function() {
                tooltipElement.style.opacity = '1';
                tooltipElement.style.visibility = 'visible';
            });
            
            widget.addEventListener('mouseleave', function() {
                tooltipElement.style.opacity = '0';
                tooltipElement.style.visibility = 'hidden';
            });
        }
    }
    
    // Badge animation
    if (badge) {
        const badgeElement = widget.querySelector('.whatsapp-badge');
        if (badgeElement) {
            // Add pulse animation periodically
            setInterval(() => {
                badgeElement.style.animation = 'none';
                setTimeout(() => {
                    badgeElement.style.animation = 'pulse 2s infinite';
                }, 10);
            }, 10000);
        }
    }
}

/**
 * Open WhatsApp with fallback mechanism
 */
function openWhatsAppWithFallback(phone, message) {
    if (!phone) {
        console.error('WhatsApp: No phone number provided');
        showWhatsAppError('Nomor telepon tidak tersedia');
        return;
    }
    
    // Format phone number (ensure it starts with country code)
    let formattedPhone = phone.replace(/[^0-9]/g, '');
    if (!formattedPhone.startsWith('62') && !formattedPhone.startsWith('+62')) {
        if (formattedPhone.startsWith('0')) {
            formattedPhone = '62' + formattedPhone.substring(1);
        } else {
            formattedPhone = '62' + formattedPhone;
        }
    }
    
    // Encode message
    const encodedMessage = encodeURIComponent(message);
    
    // Create WhatsApp URL
    const whatsappUrl = `https://wa.me/${formattedPhone}?text=${encodedMessage}`;
    
    // Try to open WhatsApp app first
    const appUrl = `whatsapp://send?phone=${formattedPhone}&text=${encodedMessage}`;
    
    // Try to open WhatsApp app
    const startTime = Date.now();
    const timeout = setTimeout(() => {
        // If app doesn't open after 500ms, open web version
        window.open(whatsappUrl, '_blank');
    }, 500);
    
    // Try to open app
    window.location.href = appUrl;
    
    // Check if app opened successfully
    const checkAppOpened = setInterval(() => {
        if (Date.now() - startTime > 1000) {
            clearInterval(checkAppOpened);
            clearTimeout(timeout);
        }
        
        // If page is still visible, app didn't open
        if (document.visibilityState === 'visible' && Date.now() - startTime > 500) {
            clearInterval(checkAppOpened);
            clearTimeout(timeout);
            window.open(whatsappUrl, '_blank');
        }
    }, 100);
}

/**
 * Track WhatsApp click for analytics
 */
function trackWhatsAppClick() {
    try {
        // Send analytics data
        const analyticsData = {
            event: 'whatsapp_widget_click',
            timestamp: new Date().toISOString(),
            page: window.location.pathname,
            user_agent: navigator.userAgent,
            referrer: document.referrer
        };
        
        // Send to analytics endpoint if available
        fetch('/api/analytics/track', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(analyticsData)
        }).catch(error => {
            console.log('Analytics tracking failed:', error);
        });
        
        // Also log to console for debugging
        console.log('WhatsApp widget clicked:', analyticsData);
    } catch (error) {
        console.error('Analytics error:', error);
    }
}

/**
 * Show WhatsApp error message
 */
function showWhatsAppError(message) {
    // Create error notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
    errorDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(errorDiv);
    
    // Show error
    setTimeout(() => {
        errorDiv.style.transform = 'translateX(0)';
    }, 100);
    
    // Hide error after 3 seconds
    setTimeout(() => {
        errorDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(errorDiv);
        }, 300);
    }, 3000);
}

/**
 * Check WhatsApp widget configuration
 */
function validateWhatsAppConfig() {
    const widget = document.getElementById('whatsapp-widget');
    if (!widget) return true;
    
    const phone = widget.getAttribute('data-phone');
    
    if (!phone) {
        console.warn('WhatsApp widget: No phone number configured');
        return false;
    }
    
    // Validate phone number format
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length < 10) {
        console.warn('WhatsApp widget: Invalid phone number format');
        return false;
    }
    
    return true;
}

// Initialize WhatsApp widget when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize WhatsApp widget
    initializeWhatsAppWidget();
    
    // Validate configuration
    if (!validateWhatsAppConfig()) {
        console.warn('WhatsApp widget configuration is invalid');
    }
});

// Re-initialize widget on dynamic content changes
window.reinitializeWhatsAppWidget = function() {
    initializeWhatsAppWidget();
};
</script>