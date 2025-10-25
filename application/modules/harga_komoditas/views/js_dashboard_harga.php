<script src="<?= base_url('assets/style/bower_components/chart.js/Chart.js'); ?>"></script>
<style>
/* Hierarchical table styling */
.parent-commodity {
    background-color: #f8f9fa;
    font-weight: 600;
}

.child-commodity {
    background-color: #ffffff;
}

.child-commodity td:first-child {
    padding-left: 25px;
}

.parent-commodity:hover,
.child-commodity:hover {
    background-color: #e9ecef;
}

/* Price change styling */
.price-change-up {
    color: #28a745;
    font-weight: 600;
}

.price-change-down {
    color: #dc3545;
    font-weight: 600;
}

.price-change-stable {
    color: #6c757d;
}

/* Trend styling - brighter colors */
.trend-up {
    color: #00ff00;
    font-weight: bold;
}

.trend-down {
    color: #ff4444;
    font-weight: bold;
}

.trend-stable {
    color: #ffff00;
    font-weight: bold;
}

/* Price cell styling */
.price-cell {
    text-align: right;
    font-weight: 500;
}
</style>
<script>
$(document).ready(function() {
    // Global variables
    var disparityChart = null;
    var currentFilters = {
        pasar_id: 'all',
        filter_type: 'today',
        date_from: '<?= date('Y-m-d'); ?>',
        date_to: '<?= date('Y-m-d'); ?>'
    };

    // No CSRF needed - disabled in CI3 config

    // Initialize dashboard
    loadHierarchicalData();
    loadMarketAnalysis();

    // Auto-load disparity chart on page load (defaults to "today")
    setTimeout(function() {
        loadDisparityChart();
    }, 1000); // Small delay to let other content load first

    // Initialize drill-down functionality
    initDrillDown();

    // Filter change handlers
    $('#filter-pasar').on('change', function() {
        currentFilters.pasar_id = $(this).val();
        loadHierarchicalData();
        loadMarketAnalysis();
    });

    $('#filter-tanggal').on('change', function() {
        var filterType = $(this).val();
        currentFilters.filter_type = filterType;

        if (filterType === 'custom') {
            $('#custom-date-from, #custom-date-to').show();
        } else {
            $('#custom-date-from, #custom-date-to').hide();

            // Set dates based on filter type
            var today = new Date();
            switch (filterType) {
                case 'today':
                    currentFilters.date_from = currentFilters.date_to = formatDate(today);
                    break;
                case 'week':
                    var monday = new Date(today.setDate(today.getDate() - today.getDay() + 1));
                    currentFilters.date_from = formatDate(monday);
                    currentFilters.date_to = formatDate(new Date());
                    break;
                case 'month':
                    currentFilters.date_from = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                    currentFilters.date_to = formatDate(new Date());
                    break;
            }
        }

        loadHierarchicalData();
        loadMarketAnalysis();
    });

    $('#date-from, #date-to').on('change', function() {
        if ($('#filter-tanggal').val() === 'custom') {
            currentFilters.date_from = $('#date-from').val();
            currentFilters.date_to = $('#date-to').val();
            loadHierarchicalData();
            loadMarketAnalysis();
        }
    });

    // Refresh button
    $('#btn-refresh-data').on('click', function() {
        loadHierarchicalData();
        loadMarketAnalysis();
        toastr.success('Data berhasil direfresh');
    });

    // Load hierarchical price data
    function loadHierarchicalData() {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_hierarchical_data'); ?>',
            type: 'POST',
            data: currentFilters,
            dataType: 'json',
            beforeSend: function() {
                $('#hierarchical-table-body').html('<tr><td colspan="5" class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat data harga komoditas...</p></td></tr>');
                $('#data-info').text('Memuat data...');
            },
            success: function(response) {
                if (response.status && response.data) {
                    renderHierarchicalTable(response.data);
                    updateDataInfo(response.data);
                } else {
                    $('#hierarchical-table-body').html('<tr><td colspan="5" class="text-center text-muted">Tidak ada data untuk periode yang dipilih</td></tr>');
                    $('#data-info').text('Tidak ada data');
                }
            },
            error: function() {
                $('#hierarchical-table-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>');
                $('#data-info').text('Error');
                toastr.error('Gagal memuat data hierarki');
            }
        });
    }

    // Render hierarchical table
    function renderHierarchicalTable(data) {
        var dates = data.dates;
        var commodities = data.data;

        // Update date headers - insert date columns between Satuan and Perubahan
        var dateHeadersHtml = '';
        dates.forEach(function(date) {
            var formattedDate = formatDateDisplay(date);
            dateHeadersHtml += '<th class="date-header text-center">' + formattedDate + '</th>';
        });

        // Rebuild the entire header row with date columns in the right place
        var fullHeaderHtml = '<th style="width: 200px;">Nama Komoditas</th>' +
                            '<th style="width: 80px;">Satuan</th>' +
                            dateHeadersHtml +
                            '<th style="width: 150px;">Perubahan (%)</th>' +
                            '<th style="width: 100px;">Keterangan</th>';
        $('#main-headers').html(fullHeaderHtml);

        // Update table body
        var tableBodyHtml = '';

        commodities.forEach(function(parent) {
            // Parent row
            tableBodyHtml += renderCommodityRow(parent, dates, true);

            // Children rows
            if (parent.children && parent.children.length > 0) {
                parent.children.forEach(function(child) {
                    tableBodyHtml += renderCommodityRow(child, dates, false);
                });
            }
        });

        $('#hierarchical-table-body').html(tableBodyHtml);
    }

    // Render single commodity row
    function renderCommodityRow(commodity, dates, isParent) {
        var rowClass = isParent ? 'parent-commodity' : 'child-commodity';
        var clickableClass = isParent ? ' drilldown-clickable' : '';

        var html = '<tr class="' + rowClass + clickableClass + '" data-commodity-id="' + commodity.komoditas_id + '" data-commodity-name="' + commodity.komoditas_nama + '">';

        // Commodity name (CSS handles indentation and tree symbol for children)
        html += '<td>' + commodity.komoditas_nama + '</td>';

        // Unit
        html += '<td class="text-center">' + (commodity.komoditas_satuan || '-') + '</td>';

        // Price columns for each date
        dates.forEach(function(date) {
            var priceData = commodity.dates[date];
            var priceHtml = '-';

            if (priceData && priceData.price > 0) {
                priceHtml = 'Rp ' + formatNumber(priceData.price);
                if (priceData.market_count > 1) {
                    priceHtml += '<br><small class="text-muted">(' + priceData.market_count + ' pasar)</small>';
                }
            }

            html += '<td class="price-cell">' + priceHtml + '</td>';
        });

        // Price change percentage
        var changeData = getLatestPriceChange(commodity.price_changes);
        var changeHtml = '-';
        var changeClass = 'price-change-stable';

        if (changeData) {
            if (changeData.percentage > 0) {
                changeClass = 'price-change-up';
                changeHtml = '+' + changeData.percentage + '%';
            } else if (changeData.percentage < 0) {
                changeClass = 'price-change-down';
                changeHtml = changeData.percentage + '%';
            } else {
                changeHtml = '0%';
            }
        }

        html += '<td class="text-center ' + changeClass + '">' + changeHtml + '</td>';

        // Trend indicator
        var trendHtml = '-';
        var trendClass = 'trend-stable';

        if (changeData) {
            if (changeData.direction === 'up') {
                trendClass = 'trend-up';
                trendHtml = '<i class="fa fa-arrow-up"></i>';
            } else if (changeData.direction === 'down') {
                trendClass = 'trend-down';
                trendHtml = '<i class="fa fa-arrow-down"></i>';
            } else {
                trendHtml = '<i class="fa fa-minus"></i>';
            }
        }

        html += '<td class="text-center"><span class="trend-indicator ' + trendClass + '">' + trendHtml + '</span></td>';

        html += '</tr>';

        return html;
    }

    // Get latest price change
    function getLatestPriceChange(priceChanges) {
        if (!priceChanges) return null;

        var dates = Object.keys(priceChanges).sort().reverse();
        for (var i = 0; i < dates.length; i++) {
            if (priceChanges[dates[i]]) {
                return priceChanges[dates[i]];
            }
        }
        return null;
    }

    // Load market analysis
    function loadMarketAnalysis() {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_market_analysis'); ?>',
            type: 'POST',
            data: currentFilters,
            dataType: 'json',
            success: function(response) {

                if (response.status && response.data) {
                    renderMarketAnalysis(response.data);
                } else {
                    $('#market-analysis').html('<div class="text-muted">Tidak ada data analisis</div>');
                    $('#trend-indicator').html('<div class="text-muted">Tidak ada data trend</div>');
                }
            },
            error: function() {
                $('#market-analysis').html('<div class="text-danger">Gagal memuat analisis</div>');
                $('#trend-indicator').html('<div class="text-danger">Gagal memuat trend</div>');
            }
        });
    }

    // Render market analysis
    function renderMarketAnalysis(data) {
        var summary = data.summary;
        var trend = data.trend;

        // Market analysis
        var analysisHtml = '<div class="row">';
        analysisHtml += '<div class="col-md-6"><strong>Total Komoditas:</strong><br>' + (summary.total_commodities || 0) + '</div>';
        analysisHtml += '<div class="col-md-6"><strong>Rata-rata Harga:</strong><br>Rp ' + formatNumber(summary.avg_price || 0) + '</div>';
        analysisHtml += '<div class="col-md-6"><strong>Harga Terendah:</strong><br>Rp ' + formatNumber(summary.min_price || 0) + '</div>';
        analysisHtml += '<div class="col-md-6"><strong>Harga Tertinggi:</strong><br>Rp ' + formatNumber(summary.max_price || 0) + '</div>';
        analysisHtml += '</div>';

        $('#market-analysis').html(analysisHtml);

        // Trend indicator - with brighter colors
        var trendHtml = '<div class="text-center">';
        var trendClass = 'trend-stable';
        var trendIcon = 'fa-minus';
        var trendText = 'Stabil';

        if (trend.overall_direction === 'up') {
            trendClass = 'trend-up';
            trendIcon = 'fa-arrow-up';
            trendText = 'Naik';
        } else if (trend.overall_direction === 'down') {
            trendClass = 'trend-down';
            trendIcon = 'fa-arrow-down';
            trendText = 'Turun';
        }

        trendHtml += '<div class="trend-indicator ' + trendClass + '" style="font-size: 18px; padding: 10px 20px; color: white; font-weight: bold;">';
        trendHtml += '<i class="fa ' + trendIcon + '"></i> ' + trendText;
        if (trend.overall_percentage !== 0) {
            trendHtml += '<br><small style="color: #ffffff;">' + trend.overall_percentage + '%</small>';
        }
        trendHtml += '</div>';
        trendHtml += '</div>';

        $('#trend-indicator').html(trendHtml);
    }

    // Update data info
    function updateDataInfo(data) {
        var totalCommodities = 0;
        data.data.forEach(function(parent) {
            totalCommodities++;
            if (parent.children) {
                totalCommodities += parent.children.length;
            }
        });

        var dateRange = formatDateDisplay(data.date_from);
        if (data.date_from !== data.date_to) {
            dateRange += ' - ' + formatDateDisplay(data.date_to);
        }

        $('#data-info').text(totalCommodities + ' komoditas | ' + dateRange);
    }

    // Load trend chart (keep existing functionality)
    $('#btn-load-trend').on('click', function() {
        var komoditas_id = $('#trend_komoditas').val();
        var pasar_id = $('#trend_pasar').val();
        var days = $('#trend_periode').val();
        
        if (!komoditas_id) {
            toastr.warning('Pilih komoditas terlebih dahulu');
            return;
        }
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_trend_harga') ?>',
            type: 'POST',
            data: {
                komoditas_id: komoditas_id,
                pasar_id: pasar_id,
                days: days
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-load-trend').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
            },
            success: function(response) {
                
                if (response.status) {
                    // Destroy existing chart (Chart.js v1.x method)
                    if (trendChart) {
                        trendChart.clear();
                        trendChart = null;
                    }

                    // Clear canvas
                    var canvas = document.getElementById('trendChart');
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Create new chart (Chart.js v1.x syntax)
                    trendChart = new Chart(ctx).Line({
                        labels: response.labels,
                        datasets: [{
                            label: 'Harga Rata-rata (Rp)',
                            fillColor: 'rgba(60, 141, 188, 0.1)',
                            strokeColor: '#3c8dbc',
                            pointColor: '#3c8dbc',
                            pointStrokeColor: '#fff',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: '#3c8dbc',
                            data: response.data
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        scaleBeginAtZero: false,
                        scaleLabel: function(label) {
                            return 'Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        tooltipTemplate: function(label) {
                            return label.datasetLabel + ': Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        multiTooltipTemplate: function(label) {
                            return label.datasetLabel + ': Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        scaleFontColor: '#666',
                        scaleGridLineColor: 'rgba(0,0,0,0.1)',
                        scaleShowGridLines: true,
                        bezierCurve: true,
                        bezierCurveTension: 0.4,
                        pointDot: true,
                        pointDotRadius: 4,
                        pointDotStrokeWidth: 2,
                        datasetStroke: true,
                        datasetStrokeWidth: 3,
                        datasetFill: true,
                        animation: true,
                        animationSteps: 60,
                        animationEasing: 'easeOutQuart'
                    });
                    
                    toastr.success('Grafik trend berhasil dimuat');
                } else {
                    toastr.error(response.message || 'Gagal memuat data trend');
                }
            },
            error: function() {
                toastr.error('Gagal memuat data trend');
            },
            complete: function() {
                $('#btn-load-trend').prop('disabled', false).html('<i class="fa fa-line-chart"></i> Tampilkan Trend');
            }
        });
    });

    // Chart functions
    $('#btn-update-chart').on('click', function() {
        loadDisparityChart();
    });

    $('#chart-pasar-filter').on('change', function() {
        loadDisparityChart();
    });

    $('#chart-period-filter').on('change', function() {
        var filterType = $(this).val();

        if (filterType === 'custom') {
            $('#chart-custom-date-from, #chart-custom-date-to').show();
        } else {
            $('#chart-custom-date-from, #chart-custom-date-to').hide();
            loadDisparityChart();
        }
    });

    $('#chart-date-from, #chart-date-to').on('change', function() {
        if ($('#chart-period-filter').val() === 'custom') {
            loadDisparityChart();
        }
    });

    function loadDisparityChart() {
        var chartFilters = {
            pasar_id: $('#chart-pasar-filter').val(),
            filter_type: $('#chart-period-filter').val(),
            date_from: currentFilters.date_from,
            date_to: currentFilters.date_to
        };

        // Adjust dates for chart
        var today = new Date();
        switch (chartFilters.filter_type) {
            case 'today':
                chartFilters.date_from = chartFilters.date_to = formatDate(today);
                break;
            case 'week':
                // Calculate start of week (Monday) - create a copy to avoid modifying today
                var monday = new Date(today);
                monday.setDate(today.getDate() - today.getDay() + 1);
                chartFilters.date_from = formatDate(monday);
                chartFilters.date_to = formatDate(today);
                console.log('Week date range:', chartFilters.date_from, 'to', chartFilters.date_to);
                break;
            case 'month':
                chartFilters.date_from = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                chartFilters.date_to = formatDate(today);
                break;
            case 'custom':
                chartFilters.date_from = $('#chart-date-from').val();
                chartFilters.date_to = $('#chart-date-to').val();
                console.log('Custom date range:', chartFilters.date_from, 'to', chartFilters.date_to);
                break;
        }

        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_price_disparity'); ?>',
            type: 'POST',
            data: chartFilters,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-update-chart').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
            },
            success: function(response) {
                console.log('Disparity chart response:', response);
                if (response.status && response.data && response.data.length > 0) {
                    renderDisparityChart(response.data);
                    $('#chart-placeholder').hide();
                    $('#disparityChart').show();
                    toastr.success('Grafik disparitas berhasil dimuat (' + response.data.length + ' komoditas)');
                } else {
                    $('#chart-placeholder').show();
                    $('#disparityChart').hide();
                    var message = 'Tidak ada data disparitas untuk periode yang dipilih. ';
                    message += 'Coba ubah filter pasar atau periode waktu.';
                    $('#chart-placeholder p').text(message);
                    toastr.warning(message);
                }
            },
            error: function() {
                toastr.error('Gagal memuat data chart disparitas');
            },
            complete: function() {
                $('#btn-update-chart').prop('disabled', false).html('<i class="fa fa-refresh"></i> Update Chart');
            }
        });
    }

    function renderDisparityChart(data) {
        // Destroy existing chart
        if (disparityChart) {
            disparityChart.clear();
            disparityChart = null;
        }

        // Clear canvas
        var canvas = document.getElementById('disparityChart');
        var ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Prepare data
        var labels = [];
        var avgPrices = [];
        var minPrices = [];
        var maxPrices = [];

        data.forEach(function(item) {
            labels.push(item.komoditas_nama);
            avgPrices.push(parseFloat(item.avg_price));
            minPrices.push(parseFloat(item.min_price));
            maxPrices.push(parseFloat(item.max_price));
        });

        // Create chart (Chart.js v1.x syntax)
        disparityChart = new Chart(ctx).Bar({
            labels: labels,
            datasets: [
                {
                    label: 'Harga Minimum',
                    fillColor: 'rgba(60, 141, 188, 0.6)',
                    strokeColor: '#3c8dbc',
                    pointColor: '#3c8dbc',
                    pointStrokeColor: '#fff',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: '#3c8dbc',
                    data: minPrices
                },
                {
                    label: 'Harga Rata-rata',
                    fillColor: 'rgba(243, 156, 18, 0.6)',
                    strokeColor: '#f39c12',
                    pointColor: '#f39c12',
                    pointStrokeColor: '#fff',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: '#f39c12',
                    data: avgPrices
                },
                {
                    label: 'Harga Maksimum',
                    fillColor: 'rgba(231, 76, 60, 0.6)',
                    strokeColor: '#e74c3c',
                    pointColor: '#e74c3c',
                    pointStrokeColor: '#fff',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: '#e74c3c',
                    data: maxPrices
                }
            ]
        }, {
            responsive: true,
            maintainAspectRatio: false,
            scaleBeginAtZero: false,
            scaleLabel: function(label) {
                return 'Rp ' + formatNumber(label.value);
            },
            tooltipTemplate: function(label) {
                return label.datasetLabel + ': Rp ' + formatNumber(label.value);
            },
            multiTooltipTemplate: function(label) {
                return label.datasetLabel + ': Rp ' + formatNumber(label.value);
            },
            scaleFontColor: '#666',
            scaleGridLineColor: 'rgba(0,0,0,0.1)',
            scaleShowGridLines: true,
            barShowStroke: true,
            barStrokeWidth: 2,
            barValueSpacing: 5,
            barDatasetSpacing: 1,
            animation: true,
            animationSteps: 60,
            animationEasing: 'easeOutQuart'
        });

        toastr.success('Chart disparitas berhasil dimuat');
    }

    // Utility functions
    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function formatDateDisplay(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit'
        });
    }

    function formatNumber(number) {
        return parseInt(number).toLocaleString('id-ID');
    }

    // Export functionality
    $('#btn-export-excel').on('click', function() {
        toastr.info('Fitur export akan segera tersedia');
    });

    // Compare markets (keep existing functionality)
    $('#btn-compare-markets').on('click', function() {
        $('#modalPerbandinganPasar').modal('show');
    });

    // Load market comparison
    $('#btn-load-comparison').on('click', function() {
        var komoditas_id = $('#compare_komoditas').val();
        var tanggal = $('#compare_tanggal').val();
        
        if (!komoditas_id) {
            toastr.warning('Pilih komoditas terlebih dahulu');
            return;
        }
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_perbandingan_pasar') ?>',
            type: 'POST',
            data: {
                komoditas_id: komoditas_id,
                tanggal: tanggal
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-load-comparison').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
                $('#comparison-result').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat data perbandingan...</p></div>');
            },
            success: function(response) {
                if (response.status && response.data.length > 0) {
                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-striped">';
                    html += '<thead><tr>';
                    html += '<th>Pasar</th>';
                    html += '<th>Harga Beli</th>';
                    html += '<th>Harga Jual</th>';
                    html += '<th>Rata-rata</th>';
                    html += '<th>Stok</th>';
                    html += '<th>Kualitas</th>';
                    html += '<th>Selisih</th>';
                    html += '</tr></thead><tbody>';
                    
                    var harga_terendah = Math.min(...response.data.map(item => parseFloat(item.harga_rata_rata)));
                    
                    $.each(response.data, function(index, item) {
                        var selisih = parseFloat(item.harga_rata_rata) - harga_terendah;
                        var selisih_persen = harga_terendah > 0 ? (selisih / harga_terendah * 100) : 0;
                        
                        var stok_class = '';
                        switch (item.stok_tersedia) {
                            case 'TERSEDIA': stok_class = 'label-success'; break;
                            case 'TERBATAS': stok_class = 'label-warning'; break;
                            case 'KOSONG': stok_class = 'label-danger'; break;
                        }
                        
                        var kualitas_class = '';
                        switch (item.kualitas) {
                            case 'BAIK': kualitas_class = 'label-success'; break;
                            case 'SEDANG': kualitas_class = 'label-warning'; break;
                            case 'KURANG': kualitas_class = 'label-danger'; break;
                        }
                        
                        html += '<tr>';
                        html += '<td><strong>' + item.pasar_nama + '</strong></td>';
                        html += '<td>' + (item.harga_beli ? 'Rp ' + parseFloat(item.harga_beli).toLocaleString('id-ID') : '-') + '</td>';
                        html += '<td>Rp ' + parseFloat(item.harga_jual).toLocaleString('id-ID') + '</td>';
                        html += '<td><strong>Rp ' + parseFloat(item.harga_rata_rata).toLocaleString('id-ID') + '</strong></td>';
                        html += '<td><span class="label ' + stok_class + '">' + item.stok_tersedia + '</span></td>';
                        html += '<td><span class="label ' + kualitas_class + '">' + item.kualitas + '</span></td>';
                        html += '<td>';
                        if (selisih > 0) {
                            html += '<span class="text-red">+Rp ' + selisih.toLocaleString('id-ID') + ' (+' + selisih_persen.toFixed(1) + '%)</span>';
                        } else {
                            html += '<span class="text-green">Termurah</span>';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    
                    // Add summary
                    var harga_tertinggi = Math.max(...response.data.map(item => parseFloat(item.harga_rata_rata)));
                    var selisih_total = harga_tertinggi - harga_terendah;
                    var selisih_total_persen = harga_terendah > 0 ? (selisih_total / harga_terendah * 100) : 0;
                    
                    html += '<div class="callout callout-info">';
                    html += '<h5><i class="fa fa-info-circle"></i> Ringkasan Perbandingan</h5>';
                    html += '<p><strong>Harga Terendah:</strong> Rp ' + harga_terendah.toLocaleString('id-ID') + '</p>';
                    html += '<p><strong>Harga Tertinggi:</strong> Rp ' + harga_tertinggi.toLocaleString('id-ID') + '</p>';
                    html += '<p><strong>Selisih:</strong> Rp ' + selisih_total.toLocaleString('id-ID') + ' (' + selisih_total_persen.toFixed(1) + '%)</p>';
                    html += '</div>';
                    
                    $('#comparison-result').html(html);
                    toastr.success('Data perbandingan berhasil dimuat');
                } else {
                    $('#comparison-result').html('<div class="callout callout-warning"><h5><i class="fa fa-warning"></i> Data Tidak Ditemukan</h5><p>Tidak ada data harga untuk komoditas dan tanggal yang dipilih.</p></div>');
                }
            },
            error: function() {
                $('#comparison-result').html('<div class="callout callout-danger"><h5><i class="fa fa-times"></i> Error</h5><p>Gagal memuat data perbandingan.</p></div>');
                toastr.error('Gagal memuat data perbandingan');
            },
            complete: function() {
                $('#btn-load-comparison').prop('disabled', false).html('<i class="fa fa-search"></i> Bandingkan Harga');
            }
        });
    });

    // Daily analysis
    $('#btn-analisis-harian').on('click', function() {
        $('#modalAnalisisHarian').modal('show');
    });

    // Load daily analysis
    $('#btn-load-analisis').on('click', function() {
        var tanggal_dari = $('#analisis_tanggal_dari').val();
        var tanggal_sampai = $('#analisis_tanggal_sampai').val();
        var trend_status = $('#analisis_trend').val();
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_analisis_data') ?>',
            type: 'POST',
            data: {
                tanggal_dari: tanggal_dari,
                tanggal_sampai: tanggal_sampai,
                trend_status: trend_status
            },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-load-analisis').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
                $('#analisis-result').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat data analisis...</p></div>');
            },
            success: function(response) {
                if (response.status && response.data.length > 0) {
                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-striped table-condensed">';
                    html += '<thead><tr>';
                    html += '<th>Tanggal</th>';
                    html += '<th>Komoditas</th>';
                    html += '<th>Pasar</th>';
                    html += '<th>Harga Hari Ini</th>';
                    html += '<th>Perubahan Harian</th>';
                    html += '<th>Perubahan Mingguan</th>';
                    html += '<th>Trend</th>';
                    html += '<th>Volatilitas</th>';
                    html += '</tr></thead><tbody>';
                    
                    $.each(response.data, function(index, item) {
                        var trend_class = '';
                        var trend_icon = '';
                        switch (item.trend_status) {
                            case 'NAIK':
                                trend_class = 'text-red';
                                trend_icon = 'fa-arrow-up';
                                break;
                            case 'TURUN':
                                trend_class = 'text-green';
                                trend_icon = 'fa-arrow-down';
                                break;
                            case 'STABIL':
                                trend_class = 'text-blue';
                                trend_icon = 'fa-minus';
                                break;
                        }
                        
                        var volatilitas_class = '';
                        switch (item.volatilitas) {
                            case 'TINGGI': volatilitas_class = 'label-danger'; break;
                            case 'SEDANG': volatilitas_class = 'label-warning'; break;
                            case 'RENDAH': volatilitas_class = 'label-success'; break;
                        }
                        
                        html += '<tr>';
                        html += '<td>' + new Date(item.analisis_tanggal).toLocaleDateString('id-ID') + '</td>';
                        html += '<td><strong>' + item.komoditas_nama + '</strong></td>';
                        html += '<td>' + (item.pasar_nama || 'Global') + '</td>';
                        html += '<td>Rp ' + parseFloat(item.harga_hari_ini).toLocaleString('id-ID') + '</td>';
                        html += '<td>';
                        if (item.persentase_harian !== null) {
                            var persen_harian = parseFloat(item.persentase_harian);
                            var color = persen_harian > 0 ? 'text-red' : (persen_harian < 0 ? 'text-green' : 'text-blue');
                            html += '<span class="' + color + '">' + (persen_harian > 0 ? '+' : '') + persen_harian.toFixed(2) + '%</span>';
                        } else {
                            html += '-';
                        }
                        html += '</td>';
                        html += '<td>';
                        if (item.persentase_mingguan !== null) {
                            var persen_mingguan = parseFloat(item.persentase_mingguan);
                            var color = persen_mingguan > 0 ? 'text-red' : (persen_mingguan < 0 ? 'text-green' : 'text-blue');
                            html += '<span class="' + color + '">' + (persen_mingguan > 0 ? '+' : '') + persen_mingguan.toFixed(2) + '%</span>';
                        } else {
                            html += '-';
                        }
                        html += '</td>';
                        html += '<td><span class="' + trend_class + '"><i class="fa ' + trend_icon + '"></i> ' + item.trend_status + '</span></td>';
                        html += '<td><span class="label ' + volatilitas_class + '">' + item.volatilitas + '</span></td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    
                    $('#analisis-result').html(html);
                    toastr.success('Data analisis berhasil dimuat');
                } else {
                    $('#analisis-result').html('<div class="callout callout-warning"><h5><i class="fa fa-warning"></i> Data Tidak Ditemukan</h5><p>Tidak ada data analisis untuk periode yang dipilih.</p></div>');
                }
            },
            error: function() {
                $('#analisis-result').html('<div class="callout callout-danger"><h5><i class="fa fa-times"></i> Error</h5><p>Gagal memuat data analisis.</p></div>');
                toastr.error('Gagal memuat data analisis');
            },
            complete: function() {
                $('#btn-load-analisis').prop('disabled', false).html('<i class="fa fa-calculator"></i> Tampilkan Analisis');
            }
        });
    });

    // Export dashboard
    $('#btn-export-dashboard').on('click', function() {
        var filters = {
            tanggal_dari: '<?= date('Y-m-01'); ?>',
            tanggal_sampai: '<?= date('Y-m-d'); ?>',
            type: 'dashboard'
        };
        
        var queryString = $.param(filters);
        window.open('<?= base_url('harga_komoditas/export_excel') ?>?' + queryString, '_blank');
    });

    // Load recent activity
    function loadRecentActivity() {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_recent_activity') ?>',
            type: 'POST',
            data: {},
            dataType: 'json',
            success: function(response) {
                if (response.status && response.data.length > 0) {
                    var html = '<li class="time-label"><span class="bg-blue">Hari Ini</span></li>';
                    
                    $.each(response.data.slice(0, 5), function(index, item) {
                        var icon_class = 'fa-plus';
                        var bg_class = 'bg-green';
                        var action = 'Input harga baru';
                        
                        html += '<li>';
                        html += '<i class="fa ' + icon_class + ' ' + bg_class + '"></i>';
                        html += '<div class="timeline-item">';
                        html += '<span class="time"><i class="fa fa-clock-o"></i> ' + new Date(item.harga_created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'}) + '</span>';
                        html += '<h3 class="timeline-header">' + action + '</h3>';
                        html += '<div class="timeline-body">';
                        html += '<strong>' + item.komoditas_nama + '</strong> di ' + item.pasar_nama;
                        html += '<br><small>Harga: Rp ' + parseFloat(item.harga_rata_rata).toLocaleString('id-ID') + '</small>';
                        html += '</div>';
                        html += '</div>';
                        html += '</li>';
                    });
                    
                    html += '<li><i class="fa fa-clock-o bg-gray"></i></li>';
                    
                    $('#recent-activity').html(html);
                } else {
                    $('#recent-activity').html('<li class="time-label"><span class="bg-blue">Hari Ini</span></li><li><i class="fa fa-info bg-aqua"></i><div class="timeline-item"><h3 class="timeline-header">Belum ada aktivitas hari ini</h3></div></li>');
                }
            },
            error: function() {
                $('#recent-activity').html('<li class="time-label"><span class="bg-red">Error</span></li><li><i class="fa fa-times bg-red"></i><div class="timeline-item"><h3 class="timeline-header">Gagal memuat aktivitas</h3></div></li>');
            }
        });
    }

    // Load recent activity on page load
    loadRecentActivity();

    // Auto refresh every 5 minutes
    setInterval(function() {
        loadRecentActivity();
    }, 300000); // 5 minutes

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // =============================================
    // Drill-down Functionality
    // =============================================

    function initDrillDown() {
        // Make table rows clickable for drill-down
        $(document).on('click', '#hierarchical-price-table tbody tr.drilldown-clickable', function() {
            var commodityName = $(this).data('commodity-name');
            var commodityId = $(this).data('commodity-id');

            if (commodityId) {
                showDrillDownModal(commodityId, commodityName);
            }
        });

        // Handle drill-down chart period change
        $('#drilldown-chart-period').on('change', function() {
            var commodityId = $('#modal-drilldown-detail').data('commodity-id');
            if (commodityId) {
                loadDrillDownChart(commodityId, $(this).val());
            }
        });

        // Handle export button
        $('#btn-export-drilldown').on('click', function() {
            var commodityId = $('#modal-drilldown-detail').data('commodity-id');
            var commodityName = $('#modal-drilldown-detail').data('commodity-name');
            exportDrillDownData(commodityId, commodityName);
        });
    }

    function getCommodityIdFromRow(row) {
        // This is a simplified approach - you might need to store commodity IDs in data attributes
        // For now, we'll need to get it from the hierarchical data
        var rowIndex = $(row).index();
        // This would need to be implemented based on how the table is built
        return null; // Placeholder - needs proper implementation
    }

    function showDrillDownModal(commodityId, commodityName) {
        $('#modal-drilldown-detail').data('commodity-id', commodityId);
        $('#modal-drilldown-detail').data('commodity-name', commodityName);

        $('.modal-title', '#modal-drilldown-detail').html(
            '<i class="fa fa-search-plus"></i> Detail Komoditas: ' + commodityName
        );

        $('#modal-drilldown-detail').modal('show');

        // Load commodity information
        loadCommodityInfo(commodityId);
        loadDrillDownChart(commodityId, $('#drilldown-chart-period').val());
        loadMarketComparison(commodityId);
    }

    function loadCommodityInfo(commodityId) {
        // Load basic commodity information
        $.ajax({
            url: '<?= base_url('harga_komoditas/get_commodity_info'); ?>',
            type: 'POST',
            data: { commodity_id: commodityId },
            success: function(response) {
                if (response.status) {
                    $('#commodity-basic-info').html(`
                        <table class="table table-bordered">
                            <tr><td><strong>Nama Komoditas</strong></td><td>${response.data.komoditas_nama}</td></tr>
                            <tr><td><strong>Satuan</strong></td><td>${response.data.komoditas_satuan || '-'}</td></tr>
                            <tr><td><strong>Kategori</strong></td><td>${response.data.kategori_nama || '-'}</td></tr>
                            <tr><td><strong>Status</strong></td><td>${response.data.komoditas_status == 1 ? 'Aktif' : 'Non-Aktif'}</td></tr>
                        </table>
                    `);

                    $('#commodity-price-stats').html(`
                        <table class="table table-bordered">
                            <tr><td><strong>Harga Rata-rata</strong></td><td>Rp ${formatNumber(response.data.stats.avg_price || 0)}</td></tr>
                            <tr><td><strong>Harga Terendah</strong></td><td>Rp ${formatNumber(response.data.stats.min_price || 0)}</td></tr>
                            <tr><td><strong>Harga Tertinggi</strong></td><td>Rp ${formatNumber(response.data.stats.max_price || 0)}</td></tr>
                            <tr><td><strong>Jumlah Data</strong></td><td>${response.data.stats.data_count || 0} records</td></tr>
                        </table>
                    `);
                }
            },
            error: function() {
                $('#commodity-basic-info').html('<div class="text-danger">Gagal memuat informasi komoditas</div>');
                $('#commodity-price-stats').html('<div class="text-danger">Gagal memuat statistik harga</div>');
            }
        });
    }

    function loadDrillDownChart(commodityId, days) {
        $.ajax({
            url: '<?= base_url('harga_komoditas/get_drilldown_chart_data'); ?>',
            type: 'POST',
            data: {
                commodity_id: commodityId,
                days: days
            },
            success: function(response) {
                if (response.status && response.data.labels && response.data.data) {
                    // Destroy existing chart
                    if (drilldownChart) {
                        drilldownChart.clear();
                        drilldownChart = null;
                    }

                    var ctx = document.getElementById('drilldown-price-chart').getContext('2d');
                    drilldownChart = new Chart(ctx).Line({
                        labels: response.data.labels,
                        datasets: [{
                            label: response.data.komoditas_nama || 'Harga',
                            fillColor: 'rgba(60, 141, 188, 0.1)',
                            strokeColor: '#3c8dbc',
                            pointColor: '#3c8dbc',
                            pointStrokeColor: '#fff',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: '#3c8dbc',
                            data: response.data.data
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        scaleBeginAtZero: false,
                        scaleLabel: function(label) {
                            return 'Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        }
                    });
                }
            },
            error: function() {
                console.error('Failed to load drill-down chart data');
            }
        });
    }

    function loadMarketComparison(commodityId) {
        $.ajax({
            url: '<?= base_url('harga_komoditas/get_market_comparison'); ?>',
            type: 'POST',
            data: { commodity_id: commodityId },
            success: function(response) {
                if (response.status && response.data) {
                    var html = '';
                    response.data.forEach(function(item) {
                        html += `
                            <tr>
                                <td><strong>${item.pasar_nama}</strong></td>
                                <td>${item.harga_beli ? 'Rp ' + formatNumber(item.harga_beli) : '-'}</td>
                                <td>Rp ${formatNumber(item.harga_jual)}</td>
                                <td><strong>Rp ${formatNumber(item.harga_rata_rata)}</strong></td>
                                <td>${item.harga_tanggal ? formatDateDisplay(item.harga_tanggal) : '-'}</td>
                            </tr>
                        `;
                    });

                    if (html === '') {
                        html = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data pasar untuk komoditas ini</td></tr>';
                    }

                    $('#market-comparison-body').html(html);
                }
            },
            error: function() {
                $('#market-comparison-body').html('<tr><td colspan="5" class="text-center text-danger">Gagal memuat data perbandingan pasar</td></tr>');
            }
        });
    }

    function exportDrillDownData(commodityId, commodityName) {
        var period = $('#drilldown-chart-period').val();
        var url = '<?= base_url('harga_komoditas/export_drilldown'); ?>?' +
                  'commodity_id=' + commodityId +
                  '&commodity_name=' + encodeURIComponent(commodityName) +
                  '&period=' + period;

        window.open(url, '_blank');
        toastr.info('File export sedang diproses...');
    }

    // Global variable for drill-down chart
    var drilldownChart = null;
});
</script>
