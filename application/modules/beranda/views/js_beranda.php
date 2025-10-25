<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(document).ready(function() {
    // Variabel global untuk map
    var map;
    var markersGroup;
    var pelaporanMap;
    var pelaporanMarkersGroup;
    
    // Inisialisasi dashboard
    initDashboard();
    
    function initDashboard() {
        // Load semua data secara paralel
        loadStatistikMasyarakat();
        loadDetailKategoriKepengurusan();
        loadDetailFasilitasPerKategori();
        loadStatistikPelaporan();
        loadLoginHistory();

        // Inisialisasi map dengan delay untuk memastikan DOM ready
        setTimeout(function() {
            initMap();
            initPelaporanMap();
        }, 500);
    }
    
    // Load detail kategori kepengurusan
    function loadDetailKategoriKepengurusan() {
        $.ajax({
            url: '<?= base_url('beranda/ajax_detail_kategori_kepengurusan') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var html = '';
                    // Render grouped by parent if API returns children under each parent
                    if ($.isArray(data) && data.length > 0 && data[0] && typeof data[0].children !== 'undefined') {
                        function iconForKategori(nama) {
                            var s = (nama || '').toLowerCase();
                            if (s.indexOf('masjid') !== -1 || s.indexOf('gereja') !== -1 || s.indexOf('vihara') !== -1 || s.indexOf('pura') !== -1) return 'fa-building';
                            if (s.indexOf('paud') !== -1 || s === 'sd' || s === 'smp' || s.indexOf('sma') !== -1 || s.indexOf('smk') !== -1) return 'fa-graduation-cap';
                            if (s.indexOf('pt') !== -1 || s.indexOf('universitas') !== -1 || s.indexOf('kampus') !== -1) return 'fa-university';
                            return 'fa-building';
                        }
                        $.each(data, function(i, grp) {
                            if (!grp || !grp.children || grp.children.length === 0) return;
                            html += '<div class="col-md-12"><h4 class="text-bold" style="margin:10px 0;">' + (grp.parent_nama || '-') + '</h4></div>';
                            $.each(grp.children, function(j, kategori) {
                                var icon = iconForKategori(kategori.nama_kategori);
                                html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                                html += '  <div class="small-box bg-green">';
                                html += '    <div class="inner">';
                                html += '      <h3>' + (kategori.total_fasilitas || 0) + '</h3>';
                                html += '      <p>' + (kategori.nama_kategori || '-') + (kategori.kode_kategori ? ' <small>(' + kategori.kode_kategori + ')</small>' : '') + '</p>';
                                html += '    </div>';
                                html += '    <div class="icon">';
                                html += '      <i class="fa ' + icon + '"></i>';
                                html += '    </div>';
                                html += '    <a href="<?= base_url('fasilitas_umum'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>';
                                html += '  </div>';
                                html += '</div>';
                            });
                        });
                        $('#fasilitas-kategori-cards').html(html);
                        return;
                    }
                    // If API returns grouped by parent (each item has children), render grouped sections
                    if ($.isArray(data) && data.length > 0 && data[0] && typeof data[0].children !== 'undefined') {
                        function iconForKategori(nama) {
                            var s = (nama || '').toLowerCase();
                            if (s.indexOf('masjid') !== -1 || s.indexOf('gereja') !== -1 || s.indexOf('vihara') !== -1 || s.indexOf('pura') !== -1) return 'fa-building';
                            if (s.indexOf('paud') !== -1 || s === 'sd' || s === 'smp' || s.indexOf('sma') !== -1 || s.indexOf('smk') !== -1) return 'fa-graduation-cap';
                            if (s.indexOf('pt') !== -1 || s.indexOf('universitas') !== -1 || s.indexOf('kampus') !== -1) return 'fa-university';
                            return 'fa-building';
                        }
                        $.each(data, function(i, grp) {
                            if (!grp || !grp.children || grp.children.length === 0) return;
                            html += '<div class="col-md-12"><h4 class="text-bold" style="margin:10px 0;">' + (grp.parent_nama || '-') + '</h4></div>';
                            $.each(grp.children, function(j, kategori) {
                                var icon = iconForKategori(kategori.nama_kategori);
                                html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                                html += '  <div class="small-box bg-green">';
                                html += '    <div class="inner">';
                                html += '      <h3>' + kategori.total_fasilitas + '</h3>';
                                html += '      <p>' + kategori.nama_kategori + (kategori.kode_kategori ? ' <small>(' + kategori.kode_kategori + ')</small>' : '') + '</p>';
                                html += '    </div>';
                                html += '    <div class="icon">';
                                html += '      <i class="fa ' + icon + '"></i>';
                                html += '    </div>';
                                html += '    <a href="<?= base_url('fasilitas_umum'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>';
                                html += '  </div>';
                                html += '</div>';
                            });
                        });
                        $('#fasilitas-kategori-cards').html(html);
                        return;
                    }
                    
                    if (data.length > 0) {
                        $.each(data, function(index, kategori) {
                            html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                            html += '  <div class="small-box bg-aqua">';
                            html += '    <div class="inner">';
                            html += '      <h3>' + kategori.total_orang + '</h3>';
                            html += '      <p>' + kategori.kepengurusan_nama + (kategori.kepengurusan_kode ? ' <small>(' + kategori.kepengurusan_kode + ')</small>' : '') + '</p>';
                            html += '    </div>';
                            html += '    <div class="icon">';
                            html += '      <i class="fa fa-users"></i>';
                            html += '    </div>';
                            html += '    <a href="<?= base_url('kategori_kepengurusan'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>';
                            html += '  </div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div class="col-md-12 text-center text-muted"><i class="fa fa-info-circle"></i> Belum ada data kategori kepengurusan</div>';
                    }
                    
                    $('#kategori-kepengurusan-cards').html(html);
                } else {
                    $('#kategori-kepengurusan-cards').html('<div class="col-md-12"><div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div></div>');
                }
            },
            error: function() {
                $('#kategori-kepengurusan-cards').html('<div class="col-md-12"><div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div></div>');
                console.log('Error loading kategori kepengurusan detail');
            }
        });
    }
    
    // Load detail fasilitas per kategori
    function loadDetailFasilitasPerKategori() {
        var kecamatan = $('#filter-kecamatan').val() || '';
        var kelurahan = $('#filter-kelurahan').val() || '';
        var id_kota = '16.73';
        $.ajax({
            url: '<?= base_url('beranda/ajax_detail_fasilitas_per_kategori') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id_kecamatan: kecamatan, id_kelurahan: kelurahan, id_kota: id_kota },
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var html = '';
                    
                    if (data.length > 0) {
                        $.each(data, function(index, kategori) {
                            html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                            html += '  <div class="small-box bg-green">';
                            html += '    <div class="inner">';
                            html += '      <h3>' + kategori.total_fasilitas + '</h3>';
                            html += '      <p>' + kategori.nama_kategori + (kategori.kode_kategori ? ' <small>(' + kategori.kode_kategori + ')</small>' : '') + '</p>';
                            html += '    </div>';
                            html += '    <div class="icon">';
                            html += '      <i class="fa fa-building"></i>';
                            html += '    </div>';
                            html += '    <a href="<?= base_url('fasilitas_umum'); ?>" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>';
                            html += '  </div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div class="col-md-12 text-center text-muted"><i class="fa fa-info-circle"></i> Belum ada data kategori fasilitas</div>';
                    }
                    
                    $('#fasilitas-kategori-cards').html(html);
                } else {
                    $('#fasilitas-kategori-cards').html('<div class="col-md-12"><div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div></div>');
                }
            },
            error: function() {
                $('#fasilitas-kategori-cards').html('<div class="col-md-12"><div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div></div>');
                console.log('Error loading fasilitas kategori detail');
            }
        });
    }
    
    // Load statistik masyarakat
    function loadStatistikMasyarakat() {
        $.ajax({
            url: '<?= base_url('beranda/ajax_statistik_masyarakat') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    $('#total-masyarakat').html(data.total_masyarakat);
                    $('#masyarakat-aktif').html(data.masyarakat_aktif);
                    $('#masyarakat-nonaktif').html(data.masyarakat_nonaktif);
                } else {
                    $('#total-masyarakat').html('<span class="text-danger">Error</span>');
                }
            },
            error: function() {
                $('#total-masyarakat').html('<span class="text-danger">Error</span>');
                console.log('Error loading masyarakat stats');
            }
        });
    }
    
    // Load history login
    function loadLoginHistory() {
        $.ajax({
            url: '<?= base_url('beranda/ajax_login_history') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var html = '';
                    
                    if (data.length > 0) {
                        $('#login-count').html(data.length);
                        
                        $.each(data, function(index, login) {
                            var timeAgo = getTimeAgo(login.attempt_time);
                            html += '<div class="recent-item">';
                            html += '<div class="row">';
                            html += '<div class="col-xs-8">';
                            html += '<strong>' + (login.nama || 'Unknown') + '</strong>';
                            html += '<br><small class="text-muted">' + (login.username || '-') + '</small>';
                            html += '</div>';
                            html += '<div class="col-xs-4 text-right">';
                            html += '<small class="text-success"><i class="fa fa-clock-o"></i> ' + timeAgo + '</small>';
                            html += '<br><small class="text-muted">' + (login.ip_address || '-') + '</small>';
                            html += '</div>';
                            html += '</div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div class="text-center text-muted"><i class="fa fa-info-circle"></i> Belum ada data login</div>';
                        $('#login-count').html('0');
                    }
                    
                    $('#login-history-content').html(html);
                } else {
                    $('#login-history-content').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div>');
                }
            },
            error: function() {
                $('#login-history-content').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat data</div>');
                console.log('Error loading login history');
            }
        });
    }
    
    // Inisialisasi peta Leaflet
    function initMap() {
        try {
            // Center coordinates untuk Lubuklinggau (approx)
            var centerLat = -3.2967;
            var centerLng = 102.8610;
            
            // Inisialisasi map
            map = L.map('map').setView([centerLat, centerLng], 12);
            
            // Tambahkan tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Inisialisasi marker group
            markersGroup = L.layerGroup().addTo(map);
            
            // Load data fasilitas
            loadFasilitasMap();
            
            // Sembunyikan loading
            $('#map-loading').hide();
            
        } catch (error) {
            console.error('Error initializing map:', error);
            $('#map-loading').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i> Error memuat peta</div>');
        }
    }
    
    // Load data fasilitas untuk peta
    function loadFasilitasMap() {
        var kecamatan = $('#map-kecamatan').val() || '';
        var kelurahan = $('#map-kelurahan').val() || '';
        var id_kota = '16.73';
        $.ajax({
            url: '<?= base_url('beranda/ajax_data_fasilitas_map') ?>',
            type: 'POST',
            dataType: 'json',
            data: { id_kecamatan: kecamatan, id_kelurahan: kelurahan, id_kota: id_kota },
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    
                    // Clear existing markers
                    markersGroup.clearLayers();
                    
                    if (data.length > 0) {
                        $.each(data, function(index, fasilitas) {
                            if (fasilitas.latitude && fasilitas.longitude) {
                                var lat = parseFloat(fasilitas.latitude);
                                var lng = parseFloat(fasilitas.longitude);
                                
                                // Buat popup content
                                var popupContent = '<div style="min-width: 200px;">';
                                popupContent += '<h5><i class="fa fa-building"></i> ' + fasilitas.nama_fasilitas + '</h5>';
                                popupContent += '<p><strong>Kategori:</strong> ' + (fasilitas.nama_kategori || '-') + '</p>';
                                if (fasilitas.alamat) {
                                    popupContent += '<p><strong>Alamat:</strong> ' + fasilitas.alamat + '</p>';
                                }
                                if (fasilitas.telepon) {
                                    popupContent += '<p><strong>Telepon:</strong> ' + fasilitas.telepon + '</p>';
                                }
                                popupContent += '</div>';
                                
                                // Buat marker
                                var marker = L.marker([lat, lng])
                                    .bindPopup(popupContent)
                                    .addTo(markersGroup);
                            }
                        });
                        
                        // Auto fit bounds jika ada marker
                        if (markersGroup.getLayers().length > 0) {
                            map.fitBounds(markersGroup.getBounds(), {padding: [20, 20]});
                        }
                    }
                } else {
                    console.log('Error loading fasilitas map data');
                }
            },
            error: function() {
                console.log('Error loading fasilitas map data');
            }
        });
    }

    // Load statistik pelaporan
    function loadStatistikPelaporan() {
        $.ajax({
            url: '<?= base_url('beranda/ajax_statistik_pelaporan') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    var data = response.data;
                    var html = '';

                    // Card Total Pelaporan
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-red">';
                    html += '<span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Total Pelaporan</span>';
                    html += '<span class="info-box-number">' + data.total_pelaporan + '</span>';
                    html += '</div></div></div>';

                    // Card Status Baru
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-yellow">';
                    html += '<span class="info-box-icon"><i class="fa fa-clock-o"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Status Baru</span>';
                    html += '<span class="info-box-number">' + data.status_baru + '</span>';
                    html += '</div></div></div>';

                    // Card Status Proses
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-blue">';
                    html += '<span class="info-box-icon"><i class="fa fa-cogs"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Dalam Proses</span>';
                    html += '<span class="info-box-number">' + data.status_proses + '</span>';
                    html += '</div></div></div>';

                    // Card Status Selesai
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-green">';
                    html += '<span class="info-box-icon"><i class="fa fa-check"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Selesai</span>';
                    html += '<span class="info-box-number">' + data.status_selesai + '</span>';
                    html += '</div></div></div>';

                    // Card Prioritas Tinggi
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-red">';
                    html += '<span class="info-box-icon"><i class="fa fa-warning"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Prioritas Tinggi</span>';
                    html += '<span class="info-box-number">' + data.prioritas_tinggi + '</span>';
                    html += '</div></div></div>';

                    // Card Bulan Ini
                    html += '<div class="col-lg-3 col-sm-6 col-xs-12">';
                    html += '<div class="info-box bg-purple">';
                    html += '<span class="info-box-icon"><i class="fa fa-calendar"></i></span>';
                    html += '<div class="info-box-content">';
                    html += '<span class="info-box-text">Bulan Ini</span>';
                    html += '<span class="info-box-number">' + data.bulan_ini + '</span>';
                    html += '</div></div></div>';

                    $('#statistik-pelaporan-cards').html(html);
                } else {
                    $('#statistik-pelaporan-cards').html('<div class="col-md-12"><div class="alert alert-warning">Gagal memuat statistik pelaporan</div></div>');
                }
            },
            error: function() {
                $('#statistik-pelaporan-cards').html('<div class="col-md-12"><div class="alert alert-danger">Error memuat statistik pelaporan</div></div>');
            }
        });
    }

    // Inisialisasi peta pelaporan
    function initPelaporanMap() {
        console.log('Initializing pelaporan map...');

        if (typeof L === 'undefined') {
            console.log('Leaflet library not loaded');
            return;
        }

        // Check if element exists
        if (!document.getElementById('pelaporan-map')) {
            console.log('Pelaporan map element not found');
            return;
        }

        try {
            // Inisialisasi map dengan center di Bengkulu
            pelaporanMap = L.map('pelaporan-map').setView([-3.8, 102.3], 11);
            console.log('Pelaporan map initialized successfully');

            // Tambahkan tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(pelaporanMap);

            // Inisialisasi marker group
            pelaporanMarkersGroup = L.layerGroup().addTo(pelaporanMap);

            // Load data pelaporan
            loadPelaporanMap();

            // Hide loading
            $('#pelaporan-map-loading').hide();
        } catch (error) {
            console.error('Error initializing pelaporan map:', error);
        }
    }

    // Load data pelaporan untuk map
    function loadPelaporanMap() {
        if (!pelaporanMap) {
            console.log('Pelaporan map not initialized');
            return;
        }

        // Clear existing markers
        pelaporanMarkersGroup.clearLayers();

        var id_kecamatan = $('#pelaporan-map-kecamatan').val();
        var id_kelurahan = $('#pelaporan-map-kelurahan').val();
        var id_kota = 'all'; // Tampilkan semua data untuk sementara

        console.log('Loading pelaporan map data with filters:', {
            id_kecamatan: id_kecamatan,
            id_kelurahan: id_kelurahan,
            id_kota: id_kota
        });

        $.ajax({
            url: '<?= base_url('beranda/ajax_data_pelaporan_map') ?>',
            type: 'POST',
            data: {
                id_kecamatan: id_kecamatan,
                id_kelurahan: id_kelurahan,
                id_kota: id_kota
            },
            dataType: 'json',
            success: function(response) {
                console.log('Pelaporan map response:', response);
                if (response.status && response.data) {
                    var pelaporan_list = response.data;
                    console.log('Pelaporan data count:', pelaporan_list.length);

                    if (pelaporan_list.length > 0) {
                        $.each(pelaporan_list, function(index, pelaporan) {
                            if (pelaporan.lokasi_lat && pelaporan.lokasi_lng) {
                                var lat = parseFloat(pelaporan.lokasi_lat);
                                var lng = parseFloat(pelaporan.lokasi_lng);

                                // Tentukan warna marker berdasarkan status
                                var markerColor = 'red';
                                if (pelaporan.status === 'SELESAI') markerColor = 'green';
                                else if (pelaporan.status === 'PROSES') markerColor = 'blue';

                                // Tentukan icon berdasarkan prioritas
                                var iconClass = 'fa-exclamation-triangle';
                                if (pelaporan.prioritas === 'TINGGI') iconClass = 'fa-warning';
                                else if (pelaporan.prioritas === 'SEDANG') iconClass = 'fa-exclamation';
                                else if (pelaporan.prioritas === 'RENDAH') iconClass = 'fa-info';

                                // Buat popup content
                                var popupContent = '<div style="min-width: 250px;">';
                                popupContent += '<h5><i class="fa ' + iconClass + '"></i> ' + pelaporan.judul + '</h5>';
                                popupContent += '<p><strong>Kode:</strong> ' + pelaporan.kode_laporan + '</p>';
                                popupContent += '<p><strong>Status:</strong> <span class="label label-' + (pelaporan.status === 'SELESAI' ? 'success' : (pelaporan.status === 'PROSES' ? 'primary' : 'warning')) + '">' + pelaporan.status + '</span></p>';
                                popupContent += '<p><strong>Prioritas:</strong> <span class="label label-' + (pelaporan.prioritas === 'TINGGI' ? 'danger' : (pelaporan.prioritas === 'SEDANG' ? 'warning' : 'info')) + '">' + pelaporan.prioritas + '</span></p>';
                                popupContent += '<p><strong>Kategori:</strong> ' + pelaporan.kategori + '</p>';
                                if (pelaporan.alamat) {
                                    popupContent += '<p><strong>Alamat:</strong> ' + pelaporan.alamat + '</p>';
                                }
                                if (pelaporan.nama_pelapor) {
                                    popupContent += '<p><strong>Pelapor:</strong> ' + pelaporan.nama_pelapor + '</p>';
                                }
                                popupContent += '<p><strong>Tanggal:</strong> ' + new Date(pelaporan.created_at).toLocaleDateString('id-ID') + '</p>';
                                popupContent += '</div>';

                                // Buat marker dengan custom icon
                                var customIcon = L.divIcon({
                                    className: 'custom-marker',
                                    html: '<div style="background-color: ' + markerColor + '; width: 25px; height: 25px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center;"><i class="fa ' + iconClass + '" style="color: white; font-size: 12px;"></i></div>',
                                    iconSize: [25, 25],
                                    iconAnchor: [12, 12]
                                });

                                var marker = L.marker([lat, lng], {icon: customIcon})
                                    .bindPopup(popupContent)
                                    .addTo(pelaporanMarkersGroup);
                            }
                        });

                        // Auto fit bounds jika ada marker
                        if (pelaporanMarkersGroup.getLayers().length > 0) {
                            pelaporanMap.fitBounds(pelaporanMarkersGroup.getBounds(), {padding: [20, 20]});
                        }
                    }
                } else {
                    console.log('Error loading pelaporan map data - no data or status false:', response);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error loading pelaporan map data:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }

    // Event handler untuk refresh map
    $('#refresh-map').click(function() {
        if (map) {
            $('#map-loading').show();
            loadFasilitasMap();
            setTimeout(function() {
                $('#map-loading').hide();
            }, 1000);
        }
    });
    
    // Event handler untuk refresh kategori kepengurusan
    $('#refresh-kategori-kepengurusan').click(function() {
        $('#kategori-kepengurusan-cards').html('<div class="col-md-12"><div class="loading-spinner"><i class="fa fa-spinner"></i><p>Memuat ulang data...</p></div></div>');
        loadDetailKategoriKepengurusan();
    });
    
    // Event handler untuk refresh fasilitas kategori
    $('#refresh-fasilitas-kategori').click(function() {
        $('#fasilitas-kategori-cards').html('<div class="col-md-12"><div class="loading-spinner"><i class="fa fa-spinner"></i><p>Memuat ulang data...</p></div></div>');
        loadDetailFasilitasPerKategori();
    });

    // Event handler untuk refresh statistik pelaporan
    $('#refresh-statistik-pelaporan').click(function() {
        $('#statistik-pelaporan-cards').html('<div class="col-md-12"><div class="loading-spinner"><i class="fa fa-spinner"></i><p>Memuat ulang data...</p></div></div>');
        loadStatistikPelaporan();
    });

    // Event handler untuk refresh peta pelaporan
    $('#refresh-pelaporan-map').click(function() {
        if (pelaporanMap) {
            $('#pelaporan-map-loading').show();
            loadPelaporanMap();
            setTimeout(function() {
                $('#pelaporan-map-loading').hide();
            }, 1000);
        }
    });

    // Inisialisasi filter dropdown (Select2) dan auto-reload
    function initFilters() {
        // Populate kecamatan/kelurahan untuk kategori box
        opt_get_kecamatan('16.73', 'filter-kecamatan', '');
        $('#filter-kecamatan').on('change', function(){
            var kecID = $(this).val();
            opt_get_kelurahan(kecID, 'filter-kelurahan', '');
            $('#fasilitas-kategori-cards').html('<div class="col-md-12"><div class="loading-spinner"><i class="fa fa-spinner"></i><p>Memuat data...</p></div></div>');
            loadDetailFasilitasPerKategori();
        });
        $('#filter-kelurahan').on('change', function(){
            $('#fasilitas-kategori-cards').html('<div class="col-md-12"><div class="loading-spinner"><i class="fa fa-spinner"></i><p>Memuat data...</p></div></div>');
            loadDetailFasilitasPerKategori();
        });

        // Populate kecamatan/kelurahan untuk map box
        opt_get_kecamatan('16.73', 'map-kecamatan', '');
        $('#map-kecamatan').on('change', function(){
            var kecID = $(this).val();
            opt_get_kelurahan(kecID, 'map-kelurahan', '');
            $('#map-loading').show();
            loadFasilitasMap();
            setTimeout(function(){ $('#map-loading').hide(); }, 1000);
        });
        $('#map-kelurahan').on('change', function(){
            $('#map-loading').show();
            loadFasilitasMap();
            setTimeout(function(){ $('#map-loading').hide(); }, 1000);
        });

        // Populate kecamatan/kelurahan untuk peta pelaporan
        opt_get_kecamatan('16.73', 'pelaporan-map-kecamatan', '');
        $('#pelaporan-map-kecamatan').on('change', function(){
            var kecID = $(this).val();
            opt_get_kelurahan(kecID, 'pelaporan-map-kelurahan', '');
            $('#pelaporan-map-loading').show();
            loadPelaporanMap();
            setTimeout(function(){ $('#pelaporan-map-loading').hide(); }, 1000);
        });
        $('#pelaporan-map-kelurahan').on('change', function(){
            $('#pelaporan-map-loading').show();
            loadPelaporanMap();
            setTimeout(function(){ $('#pelaporan-map-loading').hide(); }, 1000);
        });
    }

    initFilters();
    
    // Utility function untuk menghitung waktu yang lalu
    function getTimeAgo(datetime) {
        var now = new Date();
        var time = new Date(datetime);
        var diffInSeconds = Math.floor((now - time) / 1000);
        
        if (diffInSeconds < 60) {
            return diffInSeconds + ' detik lalu';
        } else if (diffInSeconds < 3600) {
            return Math.floor(diffInSeconds / 60) + ' menit lalu';
        } else if (diffInSeconds < 86400) {
            return Math.floor(diffInSeconds / 3600) + ' jam lalu';
        } else {
            return Math.floor(diffInSeconds / 86400) + ' hari lalu';
        }
    }
    
    // Auto refresh data setiap 5 menit
    setInterval(function() {
        loadStatistikMasyarakat();
        loadDetailKategoriKepengurusan();
        loadDetailFasilitasPerKategori();
        loadStatistikPelaporan();
        loadLoginHistory();
    }, 300000); // 5 menit
});
</script>
