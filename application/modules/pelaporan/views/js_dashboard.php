<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- Chart.js sudah dimuat di template utama -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- Leaflet Routing Machine -->
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

<script>
var dashboardMap;
var dashboardMarkersGroup;

$(function() {
	// Initialize tooltips
	$('[data-toggle="tooltip"]').tooltip();

	// Add animation delay for cards
	$('.small-box').each(function(index) {
		$(this).css('animation-delay', (index * 0.1) + 's');
	});

	// Add animation delay for boxes
	$('.box').each(function(index) {
		$(this).css('animation-delay', (index * 0.2 + 0.4) + 's');
	});

	// Initialize dashboard map
	initializeDashboardMap();

	// Populate kecamatan/kelurahan (id_kota=13.05 - Padang Pariaman)
	opt_get_kecamatan('16.73', 'map-kecamatan', '');
	$('#map-kecamatan').on('change', function(){
		var kecID = $(this).val();
		opt_get_kelurahan(kecID, 'map-kelurahan', '');
		reloadDashboardMap();
	});
	$('#map-kelurahan').on('change', function(){ reloadDashboardMap(); });
	$('#map-refresh').on('click', function(){ reloadDashboardMap(); });
	$('#map-fullscreen').on('click', function(){
		toggleFullscreenMap();
	});

	// Fullscreen controls
	$('#fullscreen-kecamatan').on('change', function(){
		var kecID = $(this).val();
		opt_get_kelurahan(kecID, 'fullscreen-kelurahan', '');
		reloadDashboardMap();
	});
	$('#fullscreen-kelurahan').on('change', function(){ reloadDashboardMap(); });
	$('#fullscreen-refresh').on('click', function(){ reloadDashboardMap(); });
	$('#fullscreen-exit').on('click', function(){ toggleFullscreenMap(); });

	// Charts via AJAX
	loadStatusChart();
	loadCategoryChart();
});

// Global variables for dashboard map
var dashboardMap = null;
var dashboardMarkersGroup = null;
var geojsonLayer = null;
var geojsonKecamatanLayer = null;
var geojsonKelurahanLayer = null;

// Routing variables
var routingControl = null;
var operatorMarker = null;
var currentRoute = null;
var routingEnabled = false;

/**
 * LEAFLET ROUTING MACHINE IMPLEMENTATION
 * =====================================
 *
 * Fitur routing menggunakan Leaflet Routing Machine untuk menampilkan rute
 * dari lokasi operator ke lokasi laporan yang dipilih.
 *
 * Fitur utama:
 * - GPS location detection untuk operator
 * - Manual location setting jika GPS gagal
 * - Toggle routing mode on/off
 * - Klik marker laporan untuk melihat rute
 * - Drag operator marker untuk update rute
 * - Clear route functionality
 *
 * Dependencies:
 * - Leaflet Routing Machine CSS & JS (sudah dimuat di atas)
 * - OSRM routing service (default)
 */

// Color palette for kecamatan
var kecamatanColors = [
    '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
    '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9',
    '#F8C471', '#82E0AA', '#F1948A', '#85C1E9', '#D7BDE2',
    '#A9DFBF', '#FAD7A0', '#AED6F1', '#D2B4DE', '#A3E4D7'
];

// Initialize dashboard map
function initializeDashboardMap() {
    // Default center (Lubuklinggau)
    var defaultLat = -3.2967, defaultLng = 102.8610;

    // Remove existing map if any
    if (dashboardMap) {
        dashboardMap.remove();
        dashboardMap = null;
    }

    dashboardMap = L.map('reports-map').setView([defaultLat, defaultLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(dashboardMap);

    dashboardMarkersGroup = L.layerGroup().addTo(dashboardMap);
    loadGeoJSONOverlays();
    addRoutingControls();

    // Try to get GPS location, fallback to default if failed
    getOperatorGPSLocation();

    reloadDashboardMap();
}

// Load all GeoJSON overlays
function loadGeoJSONOverlays() {
    loadKotaOverlay();
    loadKecamatanOverlay();
    loadKelurahanOverlay();
    addLayerControls();
}

// Load Kota (City) GeoJSON overlay
function loadKotaOverlay() {
    if (!dashboardMap || typeof geojsonData === 'undefined') return;

    // Remove existing layer if any
    if (geojsonLayer) {
        dashboardMap.removeLayer(geojsonLayer);
    }

    // Create GeoJSON layer from data
    geojsonLayer = L.geoJSON(geojsonData, {
        style: function(feature) {
            return {
                color: '#3388ff',
                weight: 3,
                opacity: 0.9,
                fillColor: '#3388ff',
                fillOpacity: 0.1
            };
        },
        onEachFeature: function(feature, layer) {
            if (feature.properties && feature.properties.nm_dati2) {
                layer.bindPopup('<strong>Kota: ' + feature.properties.nm_dati2 + '</strong><br/>' +
                               'Kode Provinsi: ' + feature.properties.kd_propinsi + '<br/>' +
                               'Kode Kota: ' + feature.properties.kd_dati2);
            }
        }
    });

    console.log('Kota GeoJSON overlay loaded successfully');
}

// Load Kecamatan (District) GeoJSON overlay
function loadKecamatanOverlay() {
    if (!dashboardMap || typeof geojsonKecamatanData === 'undefined') return;

    // Remove existing layer if any
    if (geojsonKecamatanLayer) {
        dashboardMap.removeLayer(geojsonKecamatanLayer);
    }

    // Create GeoJSON layer from data
    geojsonKecamatanLayer = L.geoJSON(geojsonKecamatanData, {
        style: function(feature) {
            // Get color based on kecamatan code
            var kecCode = feature.properties.kd_kecamatan;
            var colorIndex = parseInt(kecCode) % kecamatanColors.length;
            var color = kecamatanColors[colorIndex];

            return {
                color: color,
                weight: 2,
                opacity: 0.8,
                fillColor: color,
                fillOpacity: 0.1
            };
        },
        onEachFeature: function(feature, layer) {
            if (feature.properties && feature.properties.nm_kecamatan) {
                // Add tooltip that shows on hover
                layer.bindTooltip(feature.properties.nm_kecamatan, {
                    permanent: false,
                    direction: 'center',
                    className: 'kecamatan-tooltip'
                });

                // Add popup for detailed info
                layer.bindPopup('<strong>Kecamatan: ' + feature.properties.nm_kecamatan + '</strong><br/>' +
                               'Kode Provinsi: ' + feature.properties.kd_propinsi + '<br/>' +
                               'Kode Kota: ' + feature.properties.kd_dati2 + '<br/>' +
                               'Kode Kecamatan: ' + feature.properties.kd_kecamatan);
            }
        }
    });

    console.log('Kecamatan GeoJSON overlay loaded successfully');
}

// Load Kelurahan (Village) GeoJSON overlay
function loadKelurahanOverlay() {
    if (!dashboardMap || typeof geojsonKelurahanData === 'undefined') return;

    // Remove existing layer if any
    if (geojsonKelurahanLayer) {
        dashboardMap.removeLayer(geojsonKelurahanLayer);
    }

    // Create GeoJSON layer from data
    geojsonKelurahanLayer = L.geoJSON(geojsonKelurahanData, {
        style: function(feature) {
            return {
                color: '#32cd32',
                weight: 1,
                opacity: 0.7,
                fillColor: '#32cd32',
                fillOpacity: 0.03
            };
        },
        onEachFeature: function(feature, layer) {
            if (feature.properties && feature.properties.nm_kelurahan) {
                // Add tooltip that shows on hover
                layer.bindTooltip(feature.properties.nm_kelurahan, {
                    permanent: false,
                    direction: 'center',
                    className: 'kelurahan-tooltip'
                });

                // Add popup for detailed info
                layer.bindPopup('<strong>Kelurahan: ' + feature.properties.nm_kelurahan + '</strong><br/>' +
                               'Kode Provinsi: ' + feature.properties.kd_propinsi + '<br/>' +
                               'Kode Kota: ' + feature.properties.kd_dati2 + '<br/>' +
                               'Kode Kecamatan: ' + feature.properties.kd_kecamatan + '<br/>' +
                               'Kode Kelurahan: ' + feature.properties.kd_kelurahan);
            }
        }
    });

    console.log('Kelurahan GeoJSON overlay loaded successfully');
}

// Add layer controls for multiple overlays
function addLayerControls() {
    if (!dashboardMap) return;

    // Remove existing layer control if any
    dashboardMap.eachLayer(function(layer) {
        if (layer instanceof L.Control.Layers) {
            dashboardMap.removeControl(layer);
        }
    });

    var overlayMaps = {};

    // Add overlays if they exist
    if (geojsonLayer) {
        overlayMaps["Batas Kota"] = geojsonLayer;
    }
    if (geojsonKecamatanLayer) {
        overlayMaps["Batas Kecamatan"] = geojsonKecamatanLayer;
    }
    if (geojsonKelurahanLayer) {
        overlayMaps["Batas Kelurahan"] = geojsonKelurahanLayer;
    }

    // Only add control if there are overlays
    if (Object.keys(overlayMaps).length > 0) {
        L.control.layers(null, overlayMaps, {
            collapsed: false,
            position: 'topright'
        }).addTo(dashboardMap);
    }
}

// Get operator GPS location using browser geolocation
function getOperatorGPSLocation() {
    if (!dashboardMap) return;

    // Check if geolocation is supported
    if (!navigator.geolocation) {
        console.error('Geolocation is not supported by this browser');
        showGPSNotification('Browser Anda tidak mendukung GPS', 'error');
        return;
    }

    // Show loading notification
    showGPSNotification('Meminta akses lokasi GPS...', 'loading');

    // Get current position
    navigator.geolocation.getCurrentPosition(
        function(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            var accuracy = position.coords.accuracy;

            console.log('GPS location obtained:', lat, lng, 'Accuracy:', accuracy, 'meters');

            // Create operator marker at GPS location
            createOperatorMarkerAtLocation(lat, lng);

            // Hide manual location button since GPS worked
            $('#set-manual-location').hide();

            // Show success notification
            showGPSNotification('Lokasi GPS berhasil didapatkan (akurasi: ' + Math.round(accuracy) + 'm)', 'success');

            // Center map on operator location
            dashboardMap.setView([lat, lng], 15);
        },
        function(error) {
            console.error('GPS Error:', error);
            var errorMessage = 'Gagal mendapatkan lokasi GPS';

            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'Akses GPS ditolak. Izinkan akses lokasi di browser Anda.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Lokasi tidak tersedia. Pastikan GPS aktif.';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'Timeout mendapatkan lokasi. Coba lagi.';
                    break;
            }

            showGPSNotification(errorMessage, 'error');

            // Show manual location button
            $('#set-manual-location').show();

            // Fallback to default location (Lubuklinggau center)
            console.log('Using fallback location');
            createOperatorMarkerAtLocation(-3.2967, 102.8610);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

// Create operator marker at specific location
function createOperatorMarkerAtLocation(lat, lng) {
    if (!dashboardMap) return;

    // Remove existing operator marker
    if (operatorMarker) {
        dashboardMap.removeLayer(operatorMarker);
    }

    // Create operator marker with GPS icon
    operatorMarker = L.marker([lat, lng], {
        icon: L.divIcon({
            className: 'operator-marker',
            html: '<div style="background-color: #28a745; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="fa fa-crosshairs" style="color: white; font-size: 16px;"></i></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        draggable: true // Allow operator to manually adjust location
    });

    operatorMarker.bindPopup('<div style="min-width:150px;"><h5><i class="fa fa-crosshairs"></i> Lokasi Operator</h5><p>Koordinat: ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</p><p>Drag marker untuk mengubah posisi manual.</p></div>');
    operatorMarker.addTo(dashboardMap);

    // Listen for drag events to update routing
    operatorMarker.on('dragend', function(e) {
        var newLatLng = e.target.getLatLng();
        console.log('Operator marker dragged to:', newLatLng.lat, newLatLng.lng);

        // Update popup with new coordinates
        operatorMarker.setPopupContent('<div style="min-width:150px;"><h5><i class="fa fa-crosshairs"></i> Lokasi Operator</h5><p>Koordinat: ' + newLatLng.lat.toFixed(6) + ', ' + newLatLng.lng.toFixed(6) + '</p><p>Posisi manual.</p></div>');

        if (routingEnabled && currentRoute) {
            updateRouteToReport(currentRoute);
        }
    });

    console.log('Operator marker created at GPS location:', lat, lng);
}

// Update GPS status in control
function updateGPSStatus(message, type) {
    var statusElement = $('#gps-status');
    if (statusElement.length) {
        var iconClass = 'fa-info-circle';
        var color = '#666';

        switch(type) {
            case 'success':
                iconClass = 'fa-check-circle';
                color = '#28a745';
                break;
            case 'error':
                iconClass = 'fa-exclamation-triangle';
                color = '#dc3545';
                break;
            case 'warning':
                iconClass = 'fa-exclamation-circle';
                color = '#ffc107';
                break;
            case 'loading':
                iconClass = 'fa-spinner fa-spin';
                color = '#007bff';
                break;
        }

        statusElement.html('<i class="fa ' + iconClass + '" style="color: ' + color + '"></i> ' + message);
    }
}

// Show GPS notification
function showGPSNotification(message, type) {
    // Update control status
    updateGPSStatus(message, type);

    // Use toastr if available, otherwise use console
    if (typeof toastr !== 'undefined') {
        switch(type) {
            case 'success':
                toastr.success(message, 'GPS Status');
                break;
            case 'error':
                toastr.error(message, 'GPS Error');
                break;
            case 'info':
                toastr.info(message, 'GPS Info');
                break;
            case 'warning':
                toastr.warning(message, 'GPS Warning');
                break;
        }
    } else {
        console.log('GPS Notification:', type.toUpperCase(), '-', message);
    }
}

// Add routing controls
function addRoutingControls() {
    if (!dashboardMap) {
        console.error('Dashboard map not initialized when adding routing controls');
        return;
    }

    console.log('Adding routing controls to map...');

    // Create a custom control for routing and GPS
    var RoutingControl = L.Control.extend({
        options: {
            position: 'topleft'
        },

        onAdd: function(map) {
            console.log('Creating routing control container...');
            var container = L.DomUtil.create('div', 'routing-control');

            // Set container attributes for better visibility
            container.setAttribute('data-control', 'routing');
            container.id = 'routing-control-container';

            container.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <button id="toggle-routing" class="btn btn-default btn-sm" title="Aktifkan mode routing">
                        <i class="fa fa-route"></i> Routing
                    </button>
                    <button id="refresh-gps" class="btn btn-default btn-sm" title="Refresh GPS Location">
                        <i class="fa fa-crosshairs"></i> GPS
                    </button>
                    <button id="set-manual-location" class="btn btn-default btn-sm" title="Set lokasi manual dengan klik peta" style="display: none;">
                        <i class="fa fa-map-marker"></i> Manual
                    </button>
                    <div id="gps-status" style="font-size: 10px; color: #666; text-align: center; margin-top: 2px;">
                        <i class="fa fa-spinner fa-spin"></i> GPS...
                    </div>
                </div>
            `;

            // Enhanced styling for cross-device compatibility
            container.style.backgroundColor = 'white';
            container.style.padding = '8px';
            container.style.borderRadius = '4px';
            container.style.boxShadow = '0 1px 5px rgba(0,0,0,0.4)';
            container.style.minWidth = '80px';
            container.style.zIndex = '1000';
            container.style.position = 'relative';
            container.style.display = 'block';
            container.style.visibility = 'visible';
            container.style.opacity = '1';

            // Prevent map interactions when clicking the control
            L.DomEvent.disableClickPropagation(container);
            L.DomEvent.disableScrollPropagation(container);

            console.log('Routing control container created successfully');
            return container;
        }
    });

    // Add the control to map
    var routingControlInstance = new RoutingControl();
    dashboardMap.addControl(routingControlInstance);

    console.log('Routing control added to map');

    // Add click handlers with delay to ensure DOM is ready
    setTimeout(function() {
        console.log('Attaching event handlers to routing controls...');

        // Check if elements exist
        var toggleBtn = $('#toggle-routing');
        var gpsBtn = $('#refresh-gps');
        var manualBtn = $('#set-manual-location');

        console.log('Toggle button found:', toggleBtn.length > 0);
        console.log('GPS button found:', gpsBtn.length > 0);
        console.log('Manual button found:', manualBtn.length > 0);

        if (toggleBtn.length > 0) {
            toggleBtn.off('click').on('click', function() {
                console.log('Toggle routing button clicked');
                toggleRoutingMode();
            });
        }

        if (gpsBtn.length > 0) {
            gpsBtn.off('click').on('click', function() {
                console.log('GPS button clicked');
                $('#gps-status').html('<i class="fa fa-spinner fa-spin"></i> GPS...');
                getOperatorGPSLocation();
            });
        }

        if (manualBtn.length > 0) {
            manualBtn.off('click').on('click', function() {
                console.log('Manual location button clicked');
                enableManualLocationMode();
            });
        }

        console.log('Event handlers attached successfully');
    }, 500); // 500ms delay

    // Initial GPS status
    updateGPSStatus('GPS belum aktif');

    // Create default operator marker if GPS fails after timeout
    setTimeout(function() {
        if (!operatorMarker) {
            console.log('Creating default operator marker after GPS timeout');
            createOperatorMarkerAtLocation(-3.2967, 102.8610);
            updateGPSStatus('Lokasi default');
        }
    }, 15000); // 15 seconds timeout

    // Verify routing control visibility after initialization
    setTimeout(function() {
        verifyRoutingControlVisibility();
    }, 1000); // 1 second delay

    // Additional check specifically for desktop
    setTimeout(function() {
        if (window.innerWidth >= 768) { // Desktop screen
            console.log('Desktop detected, checking routing control...');
            var controlExists = $('#routing-control-container').length > 0 || $('#toggle-routing').length > 0;
            console.log('Routing control exists:', controlExists);

            if (!controlExists) {
                console.log('Routing control not found on desktop, creating fallback...');
                createFallbackRoutingControl();
            }
        }
    }, 3000); // 3 seconds delay for desktop check
}

// Toggle routing mode
function toggleRoutingMode() {
    routingEnabled = !routingEnabled;
    var button = $('#toggle-routing');

    if (routingEnabled) {
        // Check if operator marker exists
        if (!operatorMarker) {
            showGPSNotification('Lokasi operator belum tersedia. Klik tombol GPS terlebih dahulu.', 'warning');
            routingEnabled = false;
            return;
        }

        button.removeClass('btn-default').addClass('btn-success');
        button.html('<i class="fa fa-route"></i> Routing ON');
        button.attr('title', 'Mode routing aktif - klik marker laporan untuk melihat rute');

        // Also update fallback button if exists
        var fallbackButton = $('#fallback-toggle-routing');
        if (fallbackButton.length > 0) {
            fallbackButton.removeClass('btn-default').addClass('btn-success');
            fallbackButton.html('<i class="fa fa-route"></i> Routing ON');
            fallbackButton.attr('title', 'Mode routing aktif - klik marker laporan untuk melihat rute');
        }

        // Show instruction
        showGPSNotification('Mode routing aktif. Klik marker laporan untuk melihat rute.', 'info');

        if (typeof toastr !== 'undefined') {
            toastr.info('Klik marker laporan untuk melihat rute dari lokasi operator', 'Mode Routing Aktif', {
                timeOut: 5000
            });
        }
    } else {
        button.removeClass('btn-success').addClass('btn-default');
        button.html('<i class="fa fa-route"></i> Routing');
        button.attr('title', 'Aktifkan mode routing');

        // Also update fallback button if exists
        var fallbackButton = $('#fallback-toggle-routing');
        if (fallbackButton.length > 0) {
            fallbackButton.removeClass('btn-success').addClass('btn-default');
            fallbackButton.html('<i class="fa fa-route"></i> Routing');
            fallbackButton.attr('title', 'Aktifkan mode routing');
        }

        // Clear current route
        clearCurrentRoute();
        showGPSNotification('Mode routing dinonaktifkan', 'info');
    }
}

// Create route from operator to report location
function createRouteToReport(reportLat, reportLng, reportData) {
    if (!dashboardMap || !routingEnabled) {
        showGPSNotification('Mode routing belum aktif', 'warning');
        return;
    }

    if (!operatorMarker) {
        showGPSNotification('Lokasi operator belum tersedia. Klik tombol GPS untuk mendapatkan lokasi.', 'warning');
        return;
    }

    // Clear existing route
    clearCurrentRoute();

    var operatorLatLng = operatorMarker.getLatLng();

    // Show loading notification
    showGPSNotification('Membuat rute...', 'info');

    try {
        // Create routing control with better error handling
        // Show routing panel on desktop, hide on mobile
        var showRoutingPanel = window.innerWidth >= 768; // Desktop

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(operatorLatLng.lat, operatorLatLng.lng),
                L.latLng(reportLat, reportLng)
            ],
            routeWhileDragging: false,
            createMarker: function() { return null; }, // Don't create default markers
            lineOptions: {
                styles: [{ color: '#007bff', weight: 6, opacity: 0.8 }]
            },
            show: showRoutingPanel, // Show routing instructions panel on desktop
            addWaypoints: false, // Disable adding waypoints by clicking
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile: 'driving'
            }),
            // Customize the routing panel appearance
            collapsible: true,
            collapsed: false
        });

        // Add error handling
        routingControl.on('routingerror', function(e) {
            console.error('Routing error:', e.error);
            showGPSNotification('Gagal membuat rute: ' + (e.error.message || 'Server routing tidak tersedia'), 'error');
            clearCurrentRoute();
        });

        // Listen for route found event to show distance
        routingControl.on('routesfound', function(e) {
            var routes = e.routes;
            if (routes && routes.length > 0) {
                var route = routes[0];
                var distance = (route.summary.totalDistance / 1000).toFixed(2); // Convert to km
                var time = Math.round(route.summary.totalTime / 60); // Convert to minutes

                // Show success notification
                showGPSNotification('Rute berhasil dibuat: ' + distance + ' km, ~' + time + ' menit', 'success');

                // Update popup with route info
                var popupContent = '<div style="min-width:250px;">'
                    + '<h5><i class="fa fa-route"></i> Rute ke Laporan</h5>'
                    + '<p><strong>Jarak:</strong> ' + distance + ' km</p>'
                    + '<p><strong>Estimasi Waktu:</strong> ' + time + ' menit</p>'
                    + '<hr>'
                    + '<h6><strong>' + (reportData.judul || '') + '</strong></h6>'
                    + '<p><strong>Kode:</strong> ' + (reportData.kode_laporan || '-') + '</p>'
                    + '<p><strong>Prioritas:</strong> <span class="label label-' + getPriorityClass(reportData.prioritas) + '">' + (reportData.prioritas || '-') + '</span></p>'
                    + '<p><button class="btn btn-danger btn-sm" onclick="clearCurrentRoute()"><i class="fa fa-times"></i> Hapus Rute</button> '
                    + '<a class="btn btn-primary btn-sm" href="<?= base_url('pelaporan/detail/') ?>' + reportData.id + '" target="_blank"><i class="fa fa-eye"></i> Detail</a></p>'
                    + '</div>';

                // Update operator marker popup temporarily
                if (operatorMarker) {
                    operatorMarker.setPopupContent(popupContent);
                    operatorMarker.openPopup();
                }

                // Ensure routing panel is visible on desktop
                if (showRoutingPanel) {
                    setTimeout(function() {
                        var routingContainer = $('.leaflet-routing-container');
                        if (routingContainer.length > 0) {
                            console.log('Making routing panel visible...');
                            routingContainer.show();

                            // Add close button to routing panel if not exists
                            var closeBtn = routingContainer.find('.routing-close-btn');
                            if (closeBtn.length === 0) {
                                var closeBtnHtml = '<button class="routing-close-btn" style="position: absolute; top: 8px; right: 8px; background: #dc3545; color: white; border: none; border-radius: 3px; width: 25px; height: 25px; cursor: pointer; font-size: 14px; z-index: 1001;" title="Tutup panel rute">&times;</button>';
                                routingContainer.prepend(closeBtnHtml);

                                $('.routing-close-btn').on('click', function() {
                                    routingContainer.hide();
                                });
                            }
                        }
                    }, 500);
                }
            }
        });

        // Add to map
        routingControl.addTo(dashboardMap);

        // Style the routing panel for better appearance on desktop
        if (showRoutingPanel) {
            setTimeout(function() {
                var routingContainer = $('.leaflet-routing-container');
                if (routingContainer.length > 0) {
                    console.log('Styling routing panel for desktop...');

                    // Add custom styling
                    routingContainer.css({
                        'position': 'absolute',
                        'top': '10px',
                        'right': '10px',
                        'z-index': '1000',
                        'max-width': '350px',
                        'max-height': '400px',
                        'overflow-y': 'auto',
                        'background': 'white',
                        'border-radius': '8px',
                        'box-shadow': '0 4px 12px rgba(0,0,0,0.3)',
                        'border': '1px solid #ddd'
                    });

                    // Style the header
                    var routingHeader = routingContainer.find('.leaflet-routing-container h2, .leaflet-routing-container h3');
                    routingHeader.css({
                        'background': '#007bff',
                        'color': 'white',
                        'margin': '0',
                        'padding': '10px 15px',
                        'font-size': '14px',
                        'border-radius': '8px 8px 0 0'
                    });

                    // Style the instructions
                    var instructions = routingContainer.find('.leaflet-routing-instructions');
                    instructions.css({
                        'padding': '10px',
                        'font-size': '12px'
                    });

                    // Style individual instruction items
                    var instructionItems = routingContainer.find('.leaflet-routing-instruction');
                    instructionItems.css({
                        'padding': '5px 0',
                        'border-bottom': '1px solid #eee'
                    });

                    console.log('Routing panel styled successfully');
                }
            }, 1000); // Wait for routing panel to be created
        }

        // Store current route data
        currentRoute = {
            lat: reportLat,
            lng: reportLng,
            data: reportData
        };

        console.log('Route created from operator to report:', reportLat, reportLng);

    } catch (error) {
        console.error('Error creating route:', error);
        showGPSNotification('Gagal membuat rute: ' + error.message, 'error');
    }
}

// Update existing route when operator marker is dragged
function updateRouteToReport(routeData) {
    if (!routingControl || !routeData) return;

    var operatorLatLng = operatorMarker.getLatLng();

    // Update waypoints
    routingControl.setWaypoints([
        L.latLng(operatorLatLng.lat, operatorLatLng.lng),
        L.latLng(routeData.lat, routeData.lng)
    ]);
}

// Clear current route
function clearCurrentRoute() {
    if (routingControl) {
        dashboardMap.removeControl(routingControl);
        routingControl = null;
        console.log('Route cleared');
    }

    // Hide routing panel
    $('.leaflet-routing-container').hide();

    // Reset operator marker popup if exists
    if (operatorMarker) {
        operatorMarker.setPopupContent('<div style="min-width:150px;"><h5><i class="fa fa-crosshairs"></i> Lokasi Operator</h5><p>Koordinat: ' + operatorMarker.getLatLng().lat.toFixed(6) + ', ' + operatorMarker.getLatLng().lng.toFixed(6) + '</p><p>Drag marker untuk mengubah posisi manual.</p></div>');
    }

    currentRoute = null;
    showGPSNotification('Rute dihapus', 'info');
}

// Function to toggle routing panel visibility
function toggleRoutingPanel() {
    var routingContainer = $('.leaflet-routing-container');
    if (routingContainer.length > 0) {
        if (routingContainer.is(':visible')) {
            routingContainer.hide();
            console.log('Routing panel hidden');
        } else {
            routingContainer.show();
            console.log('Routing panel shown');
        }
    }
}

// Function to show route details (can be called from popup)
function showRouteDetails() {
    var routingContainer = $('.leaflet-routing-container');
    if (routingContainer.length > 0) {
        routingContainer.show();
        // Scroll to routing panel if needed
        routingContainer[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Enable manual location setting mode
function enableManualLocationMode() {
    showGPSNotification('Klik pada peta untuk menentukan lokasi operator', 'info');

    // Change cursor to crosshair
    dashboardMap.getContainer().style.cursor = 'crosshair';

    // Add one-time click handler to map
    var mapClickHandler = function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Create operator marker at clicked location
        createOperatorMarkerAtLocation(lat, lng);

        // Reset cursor
        dashboardMap.getContainer().style.cursor = '';

        // Remove click handler
        dashboardMap.off('click', mapClickHandler);

        showGPSNotification('Lokasi operator berhasil diset secara manual', 'success');

        // Hide manual button
        $('#set-manual-location').hide();
    };

    dashboardMap.on('click', mapClickHandler);
}

// Verify routing control visibility and fix if needed
function verifyRoutingControlVisibility() {
    console.log('Verifying routing control visibility...');

    var controlContainer = $('#routing-control-container');
    var toggleButton = $('#toggle-routing');

    console.log('Control container found:', controlContainer.length > 0);
    console.log('Toggle button found:', toggleButton.length > 0);

    if (controlContainer.length > 0) {
        var isVisible = controlContainer.is(':visible');
        var opacity = controlContainer.css('opacity');
        var display = controlContainer.css('display');
        var visibility = controlContainer.css('visibility');

        console.log('Control visibility status:', {
            isVisible: isVisible,
            opacity: opacity,
            display: display,
            visibility: visibility
        });

        // Force visibility if hidden
        if (!isVisible || opacity == '0' || display == 'none' || visibility == 'hidden') {
            console.log('Forcing routing control visibility...');
            controlContainer.css({
                'display': 'block !important',
                'visibility': 'visible !important',
                'opacity': '1 !important',
                'z-index': '1000 !important'
            });

            // Also check parent containers
            controlContainer.parents().each(function() {
                $(this).css({
                    'display': 'block',
                    'visibility': 'visible',
                    'opacity': '1'
                });
            });
        }
    } else {
        console.warn('Routing control container not found, attempting to recreate...');
        // Try recreating Leaflet control first
        setTimeout(function() {
            addRoutingControls();

            // If still not found after recreation, create fallback HTML control
            setTimeout(function() {
                if ($('#routing-control-container').length === 0) {
                    console.log('Creating fallback HTML routing control...');
                    createFallbackRoutingControl();
                }
            }, 1000);
        }, 1000);
    }

    // Additional check for Leaflet control containers
    var leafletControls = $('.leaflet-control-container .leaflet-top.leaflet-left');
    if (leafletControls.length > 0) {
        console.log('Leaflet control containers found:', leafletControls.length);
        leafletControls.css({
            'z-index': '1000',
            'position': 'relative'
        });
    }
}

// Debug function for troubleshooting routing control visibility
window.debugRoutingControl = function() {
    console.log('=== ROUTING CONTROL DEBUG INFO ===');

    // Check map
    console.log('Dashboard map exists:', !!dashboardMap);

    // Check control container
    var container = $('#routing-control-container');
    console.log('Control container found:', container.length > 0);

    if (container.length > 0) {
        console.log('Container CSS:', {
            display: container.css('display'),
            visibility: container.css('visibility'),
            opacity: container.css('opacity'),
            zIndex: container.css('z-index'),
            position: container.css('position')
        });

        console.log('Container dimensions:', {
            width: container.width(),
            height: container.height(),
            offset: container.offset()
        });
    }

    // Check buttons
    var buttons = ['#toggle-routing', '#refresh-gps', '#set-manual-location'];
    buttons.forEach(function(selector) {
        var btn = $(selector);
        console.log(selector + ' found:', btn.length > 0);
        if (btn.length > 0) {
            console.log(selector + ' visible:', btn.is(':visible'));
        }
    });

    // Check Leaflet controls
    var leafletControls = $('.leaflet-control-container');
    console.log('Leaflet control containers found:', leafletControls.length);

    var topLeftControls = $('.leaflet-top.leaflet-left');
    console.log('Top-left control containers found:', topLeftControls.length);

    // Check for conflicting CSS
    var allControls = $('.leaflet-control');
    console.log('All Leaflet controls found:', allControls.length);

    console.log('=== END DEBUG INFO ===');
};

// Create fallback HTML routing control if Leaflet control fails
function createFallbackRoutingControl() {
    console.log('Creating fallback HTML routing control...');

    // Remove any existing fallback
    $('#fallback-routing-control').remove();

    // Create fallback control as HTML element with better styling
    var fallbackHtml = `
        <div id="fallback-routing-control" style="
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1002;
            background: white;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            min-width: 120px;
            font-family: Arial, sans-serif;
            border: 1px solid #ccc;
        ">
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <button id="fallback-toggle-routing" class="btn btn-success btn-sm" title="Aktifkan mode routing" style="
                    width: 100%;
                    padding: 6px 8px;
                    font-size: 12px;
                    border-radius: 4px;
                    border: none;
                    background: #28a745;
                    color: white;
                    cursor: pointer;
                ">
                    <i class="fa fa-route"></i> Routing ON
                </button>
                <button id="fallback-refresh-gps" class="btn btn-primary btn-sm" title="Refresh GPS Location" style="
                    width: 100%;
                    padding: 6px 8px;
                    font-size: 12px;
                    border-radius: 4px;
                    border: none;
                    background: #007bff;
                    color: white;
                    cursor: pointer;
                ">
                    <i class="fa fa-crosshairs"></i> GPS
                </button>
                <button id="fallback-set-manual-location" class="btn btn-warning btn-sm" title="Set lokasi manual dengan klik peta" style="
                    width: 100%;
                    padding: 6px 8px;
                    font-size: 12px;
                    border-radius: 4px;
                    border: none;
                    background: #ffc107;
                    color: #212529;
                    cursor: pointer;
                    display: none;
                ">
                    <i class="fa fa-map-marker"></i> Manual
                </button>
                <button id="fallback-toggle-route-panel" class="btn btn-info btn-sm" title="Tampilkan/sembunyikan panel rute" style="
                    width: 100%;
                    padding: 6px 8px;
                    font-size: 12px;
                    border-radius: 4px;
                    border: none;
                    background: #17a2b8;
                    color: white;
                    cursor: pointer;
                    display: none;
                ">
                    <i class="fa fa-list"></i> Panel Rute
                </button>
                <div id="fallback-gps-status" style="
                    font-size: 11px;
                    color: #666;
                    text-align: center;
                    margin-top: 4px;
                    padding: 4px;
                    background: #f8f9fa;
                    border-radius: 3px;
                    border: 1px solid #e9ecef;
                ">
                    <i class="fa fa-spinner fa-spin"></i> GPS...
                </div>
            </div>
        </div>
    `;

    // Add to map container
    $('#reports-map').append(fallbackHtml);

    // Add hover effects
    $('#fallback-routing-control button').hover(
        function() {
            $(this).css('opacity', '0.8');
        },
        function() {
            $(this).css('opacity', '1');
        }
    );

    // Attach event handlers
    $('#fallback-toggle-routing').on('click', function() {
        console.log('Fallback toggle routing clicked');
        toggleRoutingMode();
    });

    $('#fallback-refresh-gps').on('click', function() {
        console.log('Fallback GPS button clicked');
        $('#fallback-gps-status').html('<i class="fa fa-spinner fa-spin"></i> GPS...');
        getOperatorGPSLocation();
    });

    $('#fallback-toggle-route-panel').on('click', function() {
        console.log('Fallback toggle route panel clicked');
        toggleRoutingPanel();
    });

    $('#fallback-set-manual-location').on('click', function() {
        console.log('Fallback manual location clicked');

        // Try multiple ways to enable manual location
        if (typeof enableManualLocationMode === 'function') {
            enableManualLocationMode();
        } else if (typeof setManualLocation === 'function') {
            setManualLocation();
        } else if (typeof window.setManualLocation === 'function') {
            window.setManualLocation();
        } else {
            // Fallback implementation
            console.log('Creating basic manual location implementation');
            alert('Klik pada peta untuk menentukan lokasi manual Anda');

            // Change cursor to crosshair
            $('#reports-map').css('cursor', 'crosshair');

            // Add one-time click handler to map
            map.once('click', function(e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;

                console.log('Manual location set to:', lat, lng);

                // Reset cursor
                $('#reports-map').css('cursor', '');

                // Remove existing current location marker
                if (currentLocationMarker) {
                    map.removeLayer(currentLocationMarker);
                }

                // Add new marker at clicked location
                currentLocationMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'current-location-marker',
                        html: '<div style="background: #007bff; border: 3px solid white; border-radius: 50%; width: 20px; height: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                        iconSize: [20, 20],
                        iconAnchor: [10, 10]
                    })
                }).addTo(map);

                // Update current location
                currentLocation = {lat: lat, lng: lng};

                // Update GPS status
                updateGPSStatus('Lokasi manual berhasil diset', 'success');

                // Hide manual location button
                $('#fallback-set-manual-location').hide();
            });
        }
    });

    // Update GPS status function to work with fallback
    if (typeof window.originalUpdateGPSStatus === 'undefined') {
        window.originalUpdateGPSStatus = updateGPSStatus;

        updateGPSStatus = function(message, type) {
            window.originalUpdateGPSStatus(message, type);

            // Also update fallback status
            var fallbackStatus = $('#fallback-gps-status');
            if (fallbackStatus.length > 0) {
                var iconClass = 'fa-info-circle';
                var color = '#666';

                switch(type) {
                    case 'success':
                        iconClass = 'fa-check-circle';
                        color = '#28a745';
                        break;
                    case 'error':
                        iconClass = 'fa-exclamation-triangle';
                        color = '#dc3545';
                        break;
                    case 'warning':
                        iconClass = 'fa-exclamation-circle';
                        color = '#ffc107';
                        break;
                    case 'loading':
                        iconClass = 'fa-spinner fa-spin';
                        color = '#007bff';
                        break;
                }

                fallbackStatus.html('<i class="fa ' + iconClass + '" style="color: ' + color + '"></i> ' + message);
            }

            // Show/hide manual location button in fallback
            if (type === 'error' && message.includes('GPS')) {
                $('#fallback-set-manual-location').show();
            } else if (type === 'success') {
                $('#fallback-set-manual-location').hide();
            }

            // Show/hide route panel button based on route status
            if (message.includes('Rute berhasil dibuat')) {
                $('#fallback-toggle-route-panel').show();
            } else if (message.includes('Rute dihapus')) {
                $('#fallback-toggle-route-panel').hide();
            }
        };
    }

    console.log('Fallback routing control created successfully');
}

// Auto-run debug in development
if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
    setTimeout(function() {
        window.debugRoutingControl();
    }, 3000);
}

function reloadDashboardMap() {
    if (!dashboardMap) {
        console.log('Dashboard map not initialized');
        return;
    }

    // Use fullscreen filters if in fullscreen mode
    var isFullscreen = $('#reports-map').hasClass('fullscreen');
    var kec = isFullscreen ? ($('#fullscreen-kecamatan').val() || '') : ($('#map-kecamatan').val() || '');
    var kel = isFullscreen ? ($('#fullscreen-kelurahan').val() || '') : ($('#map-kelurahan').val() || '');

    console.log('Loading map data with filters:', { id_kota: '', id_kecamatan: kec, id_kelurahan: kel });

    $.post('<?= base_url('pelaporan/ajax_reports_map') ?>', {
        id_kota: '',
        id_kecamatan: kec,
        id_kelurahan: kel
    }, function(resp){
        console.log('Map data response:', resp);

        if (!resp || !resp.status) {
            console.log('Invalid response or status false');
            return;
        }

        // Clear existing markers
        dashboardMarkersGroup.clearLayers();
        var group = new L.featureGroup();
        var markerCount = 0;

        (resp.data || []).forEach(function(r){
            if (!r.lokasi_lat || !r.lokasi_lng) {
                console.log('Skipping report without coordinates:', r);
                return;
            }

            var lat = parseFloat(r.lokasi_lat);
            var lng = parseFloat(r.lokasi_lng);

            if (isNaN(lat) || isNaN(lng)) {
                console.log('Invalid coordinates:', r.lokasi_lat, r.lokasi_lng);
                return;
            }

            var icon = getPriorityIcon(r.prioritas);
            var markerColor = getPriorityMarkerColor(r.prioritas);
            var marker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background-color: ' + markerColor + '; width: 25px; height: 25px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center;"><i class="fa ' + icon + '" style="color: white; font-size: 12px;"></i></div>',
                    iconSize: [25, 25],
                    iconAnchor: [12, 12]
                })
            });

            var statusColor = getStatusColor(r.status);
            var popup = '<div style="min-width:200px;">'
                + '<h5><strong>'+(r.judul||'')+'</strong></h5>'
                + '<p><strong>Kode:</strong> '+(r.kode_laporan||'-')+'</p>'
                + '<p><strong>Status:</strong> <span class="label" style="background-color:'+statusColor+';color:#fff;">'+(r.status||'-')+'</span></p>'
                + '<p><strong>Prioritas:</strong> <span class="label label-'+ getPriorityClass(r.prioritas) +'">'+(r.prioritas||'-')+'</span></p>'
                + (r.alamat ? '<p><strong>Alamat:</strong> '+r.alamat+'</p>' : '')
                + (r.kategori ? '<p><strong>Kategori:</strong> '+r.kategori+'</p>' : '')
                + '<a class="btn btn-primary btn-sm" href="<?= base_url('pelaporan/detail/') ?>'+r.id+'" target="_blank"><i class="fa fa-eye"></i> Detail</a>'
                + '</div>';

            marker.bindPopup(popup).addTo(dashboardMarkersGroup);

            // Add click event for routing
            marker.on('click', function(e) {
                if (routingEnabled) {
                    // Prevent popup from opening when routing
                    e.originalEvent.stopPropagation();
                    createRouteToReport(lat, lng, r);
                } else {
                    // Normal popup behavior when routing is disabled
                    marker.openPopup();
                }
            });

            group.addLayer(L.marker([lat, lng]));
            markerCount++;
        });

        console.log('Added', markerCount, 'markers to map');

        if (group.getLayers().length > 0) {
            dashboardMap.fitBounds(group.getBounds().pad(0.1));
        } else {
            console.log('No markers to display, keeping current view');
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('AJAX request failed:', status, error);
        console.error('Response:', xhr.responseText);
    });

    // Ensure all GeoJSON overlays remain visible after reload
    if (geojsonLayer && !dashboardMap.hasLayer(geojsonLayer)) {
        geojsonLayer.addTo(dashboardMap);
    }
    if (geojsonKecamatanLayer && !dashboardMap.hasLayer(geojsonKecamatanLayer)) {
        geojsonKecamatanLayer.addTo(dashboardMap);
    }
    if (geojsonKelurahanLayer && !dashboardMap.hasLayer(geojsonKelurahanLayer)) {
        geojsonKelurahanLayer.addTo(dashboardMap);
    }
}

// Get status color
function getStatusColor(status) {
	switch(status) {
		case 'LAPOR': return '#f39c12';
		case 'DITERIMA': return '#3c8dbc';
		case 'DIKERJAKAN': return '#00a65a';
		case 'SELESAI': return '#00c0ef';
		default: return '#999';
	}
}

// Get priority icon
function getPriorityIcon(prioritas) {
	switch(prioritas) {
		case 'URGENT': return 'fa-exclamation-triangle';
		case 'TINGGI': return 'fa-arrow-up';
		case 'SEDANG': return 'fa-minus';
		case 'RENDAH': return 'fa-arrow-down';
		default: return 'fa-circle';
	}
}

// Get priority class
function getPriorityClass(prioritas) {
	switch(prioritas) {
		case 'URGENT': return 'danger';
		case 'TINGGI': return 'warning';
		case 'SEDANG': return 'info';
		case 'RENDAH': return 'default';
		default: return 'default';
	}
}

// Get priority marker color (background color for the marker circle)
function getPriorityMarkerColor(prioritas) {
	switch(prioritas) {
		case 'URGENT': return '#d9534f'; // danger color - red
		case 'TINGGI': return '#f0ad4e'; // warning color - orange
		case 'SEDANG': return '#5bc0de'; // info color - blue
		case 'RENDAH': return '#777'; // default color - gray
		default: return '#777';
	}
}

function loadStatusChart() {
    console.log('Loading status chart...');

    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }

    // Check if canvas element exists
    var canvasElement = $('#statusChart');
    if (canvasElement.length === 0) {
        console.error('Status chart canvas element not found');
        return;
    }

    $.getJSON('<?= base_url('pelaporan/ajax_reports_by_status') ?>', function(resp){
        console.log('Status chart data:', resp);
        if (!resp || !resp.status) {
            console.log('No status chart data available');
            return;
        }

        try {
            var ctx = canvasElement.get(0).getContext('2d');

            // Destroy existing chart if exists
            if (window.statusChartInstance) {
                window.statusChartInstance.destroy();
            }

            window.statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: resp.labels,
                    datasets: [{
                        data: resp.data,
                        backgroundColor: resp.colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            console.log('Status chart created successfully');
        } catch (error) {
            console.error('Error creating status chart:', error);
        }
    }).fail(function(xhr, status, error) {
        console.error('Failed to load status chart:', status, error, xhr.responseText);
    });
}

function loadCategoryChart() {
    console.log('Loading category chart...');

    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }

    // Check if canvas element exists
    var canvasElement = $('#categoryChart');
    if (canvasElement.length === 0) {
        console.error('Category chart canvas element not found');
        return;
    }

    $.getJSON('<?= base_url('pelaporan/ajax_reports_by_kategori') ?>', function(resp){
        console.log('Category chart data:', resp);
        if (!resp || !resp.status) {
            console.log('No category chart data available');
            return;
        }

        try {
            var ctx = canvasElement.get(0).getContext('2d');

            // Destroy existing chart if exists
            if (window.categoryChartInstance) {
                window.categoryChartInstance.destroy();
            }

            window.categoryChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: resp.labels,
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: resp.data,
                        backgroundColor: resp.colors,
                        borderWidth: 1,
                        borderColor: resp.colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            console.log('Category chart created successfully');
        } catch (error) {
            console.error('Error creating category chart:', error);
        }
    }).fail(function(xhr, status, error) {
        console.error('Failed to load category chart:', status, error, xhr.responseText);
    });
}

// Toggle fullscreen map
function toggleFullscreenMap() {
	var isFullscreen = $('#reports-map').hasClass('fullscreen');

	if (!isFullscreen) {
		// Enter fullscreen
		$('#reports-map').addClass('fullscreen');
		$('body').addClass('fullscreen-active');
		$('#fullscreen-controls').show();

		// Sync filter values
		$('#fullscreen-kecamatan').val($('#map-kecamatan').val());
		$('#fullscreen-kelurahan').val($('#map-kelurahan').val());

		// Populate fullscreen kecamatan options
		opt_get_kecamatan('16.73', 'fullscreen-kecamatan', $('#map-kecamatan').val());
		if ($('#map-kecamatan').val()) {
			opt_get_kelurahan($('#map-kecamatan').val(), 'fullscreen-kelurahan', $('#map-kelurahan').val());
		}

		$('#map-fullscreen i').removeClass('fa-arrows-alt').addClass('fa-compress');
		$('#map-fullscreen').html('<i class="fa fa-compress"></i> Exit Full Screen');
	} else {
		// Exit fullscreen
		$('#reports-map').removeClass('fullscreen');
		$('body').removeClass('fullscreen-active');
		$('#fullscreen-controls').hide();

		// Sync filter values back
		$('#map-kecamatan').val($('#fullscreen-kecamatan').val());
		$('#map-kelurahan').val($('#fullscreen-kelurahan').val());

		$('#map-fullscreen i').removeClass('fa-compress').addClass('fa-arrows-alt');
		$('#map-fullscreen').html('<i class="fa fa-arrows-alt"></i> Full Screen');
	}

	setTimeout(function(){
		if(dashboardMap) {
			dashboardMap.invalidateSize();
		}
	}, 300);
}
</script>
