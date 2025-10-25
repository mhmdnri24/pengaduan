<script src="<?= base_url('assets/style/bower_components/chart.js/Chart.js'); ?>"></script>
<script>
$(document).ready(function() {
    // Global variables
    var selectedCommodityId = null;
    var priceChart = null;
    var currentDateFrom = $('#date-from').val();
    var currentDateTo = $('#date-to').val();
    
    // No CSRF token needed
    
    // Initialize dashboard
    loadCommodityList();
    loadRecentActivity();
    loadHierarchicalData();
    
    // Load commodity list
    function loadCommodityList() {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_commodity_list'); ?>',
            type: 'POST',
            data: {},
            success: function(response) {
                
                if (response.status && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(index, item) {
                        html += '<a href="#" class="list-group-item commodity-item" data-id="' + item.komoditas_id + '" data-name="' + item.komoditas_nama + '">';
                        html += '<div class="row">';
                        html += '<div class="col-md-8">';
                        html += '<h5 class="list-group-item-heading">' + item.komoditas_nama + '</h5>';
                        html += '<p class="list-group-item-text text-muted">Satuan: ' + item.komoditas_satuan + '</p>';
                        html += '</div>';
                        html += '<div class="col-md-4 text-right">';
                        html += '<span class="badge bg-blue">' + item.total_records + ' data</span>';
                        html += '</div>';
                        html += '</div>';
                        html += '</a>';
                    });
                    $('#commodity-list').html(html);
                } else {
                    $('#commodity-list').html('<div class="text-center text-muted"><i class="fa fa-info-circle"></i><p>Tidak ada data komoditas</p></div>');
                }
            },
            error: function() {
                $('#commodity-list').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i><p>Gagal memuat data komoditas</p></div>');
                toastr.error('Gagal memuat daftar komoditas');
            }
        });
    }
    
    // Load recent activity
    function loadRecentActivity() {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_recent_activity'); ?>',
            type: 'POST',
            data: {},
            success: function(response) {
                
                if (response.status && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(index, item) {
                        var time = new Date(item.harga_created_at).toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        html += '<div class="callout callout-info callout-sm">';
                        html += '<h5>' + item.komoditas_nama + ' <small class="pull-right">' + time + '</small></h5>';
                        html += '<p>Harga: <strong>Rp ' + parseInt(item.harga_rata_rata).toLocaleString('id-ID') + '</strong> - ' + item.pasar_nama + '</p>';
                        html += '</div>';
                    });
                    $('#recent-activity-list').html(html);
                } else {
                    $('#recent-activity-list').html('<div class="text-center text-muted"><i class="fa fa-info-circle"></i><p>Belum ada aktivitas hari ini</p></div>');
                }
            },
            error: function() {
                $('#recent-activity-list').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle"></i><p>Gagal memuat aktivitas</p></div>');
                toastr.error('Gagal memuat aktivitas terbaru');
            }
        });
    }
    
    // Commodity selection
    $(document).on('click', '.commodity-item', function(e) {
        e.preventDefault();
        
        // Remove active class from all items
        $('.commodity-item').removeClass('active');
        
        // Add active class to selected item
        $(this).addClass('active');
        
        // Get commodity data
        selectedCommodityId = $(this).data('id');
        var commodityName = $(this).data('name');
        
        // Update selected commodity label
        $('#selected-commodity').text(commodityName);
        
        // Load price comparison data
        loadPriceComparison();
        
        // Load chart data
        loadChartData();
    });
    
    // Load price comparison data
    function loadPriceComparison() {
        if (!selectedCommodityId) return;
        
        var filterType = $('#quick-filter').val();
        var dateFrom = $('#date-from').val();
        var dateTo = $('#date-to').val();
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_price_comparison'); ?>',
            type: 'POST',
            data: {
                komoditas_id: selectedCommodityId,
                date_from: dateFrom,
                date_to: dateTo,
                filter_type: filterType
            },
            beforeSend: function() {
                $('#price-table-body').html('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>');
            },
            success: function(response) {
                
                if (response.status && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(index, item) {
                        var changeClass = '';
                        var changeIcon = '';
                        var changeText = '';
                        
                        if (item.persentase_perubahan > 0) {
                            changeClass = 'text-red';
                            changeIcon = 'fa-arrow-up';
                            changeText = '+' + item.persentase_perubahan.toFixed(2) + '%';
                        } else if (item.persentase_perubahan < 0) {
                            changeClass = 'text-green';
                            changeIcon = 'fa-arrow-down';
                            changeText = item.persentase_perubahan.toFixed(2) + '%';
                        } else {
                            changeClass = 'text-muted';
                            changeIcon = 'fa-minus';
                            changeText = '0%';
                        }
                        
                        html += '<tr class="price-row" data-komoditas="' + selectedCommodityId + '" data-tanggal="' + item.harga_tanggal + '" style="cursor: pointer;">';
                        html += '<td>' + formatDate(item.harga_tanggal) + '</td>';
                        html += '<td>' + item.pasar_nama + '</td>';
                        html += '<td>Rp ' + parseInt(item.harga_beli || 0).toLocaleString('id-ID') + '</td>';
                        html += '<td>Rp ' + parseInt(item.harga_jual || 0).toLocaleString('id-ID') + '</td>';
                        html += '<td><strong>Rp ' + parseInt(item.harga_rata_rata).toLocaleString('id-ID') + '</strong></td>';
                        html += '<td class="' + changeClass + '"><i class="fa ' + changeIcon + '"></i> ' + changeText + '</td>';
                        html += '<td>' + (item.stok_tersedia || '-') + '</td>';
                        html += '<td>' + (item.kualitas || '-') + '</td>';
                        html += '<td><button class="btn btn-xs btn-info btn-detail" data-komoditas="' + selectedCommodityId + '" data-tanggal="' + item.harga_tanggal + '"><i class="fa fa-eye"></i></button></td>';
                        html += '</tr>';
                    });
                    $('#price-table-body').html(html);
                } else {
                    $('#price-table-body').html('<tr><td colspan="9" class="text-center text-muted">Tidak ada data untuk periode yang dipilih</td></tr>');
                }
            },
            error: function() {
                $('#price-table-body').html('<tr><td colspan="9" class="text-center text-danger">Gagal memuat data harga</td></tr>');
                toastr.error('Gagal memuat data perbandingan harga');
            }
        });
    }
    
    // Load chart data
    function loadChartData() {
        if (!selectedCommodityId) return;
        
        var dateFrom = $('#date-from').val();
        var dateTo = $('#date-to').val();
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_chart_data'); ?>',
            type: 'POST',
            data: {
                komoditas_id: selectedCommodityId,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                
                if (response.status) {
                    // Destroy existing chart
                    if (priceChart) {
                        priceChart.clear();
                        priceChart = null;
                    }
                    
                    // Clear canvas
                    var canvas = document.getElementById('priceChart');
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    
                    // Hide placeholder and show chart
                    $('#chart-placeholder').hide();
                    $('#priceChart').show();
                    
                    // Create new chart (Chart.js v1.x syntax)
                    priceChart = new Chart(ctx).Line({
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
                            return 'Harga: Rp ' + parseInt(label.value).toLocaleString('id-ID');
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
                    
                    toastr.success('Grafik berhasil dimuat');
                } else {
                    toastr.error(response.message || 'Gagal memuat data chart');
                }
            },
            error: function() {
                toastr.error('Gagal memuat data chart');
            }
        });
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

    function formatDateFull(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }
    
    // Quick filter change
    $('#quick-filter').on('change', function() {
        var filterType = $(this).val();
        var today = new Date();
        var dateFrom, dateTo;
        
        switch (filterType) {
            case 'week':
                // Monday of current week
                var monday = new Date(today.setDate(today.getDate() - today.getDay() + 1));
                dateFrom = monday.toISOString().split('T')[0];
                dateTo = new Date().toISOString().split('T')[0];
                break;
            case 'month':
                // First day of current month
                dateFrom = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                dateTo = new Date().toISOString().split('T')[0];
                break;
            default:
                // Keep current values for custom
                return;
        }
        
        $('#date-from').val(dateFrom);
        $('#date-to').val(dateTo);
        
        // Auto apply filter if commodity is selected
        if (selectedCommodityId) {
            loadPriceComparison();
            loadChartData();
        }
    });
    
    // Apply filter button
    $('#btn-apply-filter').on('click', function() {
        currentDateFrom = $('#date-from').val();
        currentDateTo = $('#date-to').val();
        
        if (selectedCommodityId) {
            loadPriceComparison();
            loadChartData();
        } else {
            toastr.warning('Pilih komoditas terlebih dahulu');
        }
    });
    
    // Search commodity
    $('#search-commodity').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.commodity-item').each(function() {
            var commodityName = $(this).find('.list-group-item-heading').text().toLowerCase();
            if (commodityName.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filter change handlers
    $('#filter-pasar').on('change', function() {
        currentFilters.pasar_id = $(this).val();
        loadHierarchicalData();
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
    });

    $('#date-from, #date-to').on('change', function() {
        if ($('#filter-tanggal').val() === 'custom') {
            currentFilters.date_from = $('#date-from').val();
            currentFilters.date_to = $('#date-to').val();
            loadHierarchicalData();
        }
    });

    // Refresh button
    $('#btn-refresh-data').on('click', function() {
        loadHierarchicalData();
        toastr.success('Data berhasil direfresh');
    });

    // Refresh buttons
    $('#btn-refresh-dashboard').on('click', function() {
        location.reload();
    });
    
    $('#btn-refresh-commodities').on('click', function() {
        loadCommodityList();
    });
    
    $('#btn-refresh-activity').on('click', function() {
        loadRecentActivity();
    });
    
    // Price row click for detail
    $(document).on('click', '.price-row', function() {
        var komoditas_id = $(this).data('komoditas');
        var tanggal = $(this).data('tanggal');
        showPriceDetail(komoditas_id, tanggal);
    });
    
    // Detail button click
    $(document).on('click', '.btn-detail', function(e) {
        e.stopPropagation();
        var komoditas_id = $(this).data('komoditas');
        var tanggal = $(this).data('tanggal');
        showPriceDetail(komoditas_id, tanggal);
    });
    
    // Show price detail modal
    function showPriceDetail(komoditas_id, tanggal) {
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_price_detail'); ?>',
            type: 'POST',
            data: {
                komoditas_id: komoditas_id,
                tanggal: tanggal
            },
            beforeSend: function() {
                $('#modal-price-detail').modal('show');
                $('#price-detail-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Memuat detail harga...</p></div>');
            },
            success: function(response) {

                if (response.status && response.data.length > 0) {
                    var html = '<div class="row">';

                    $.each(response.data, function(index, item) {
                        html += '<div class="col-md-6">';
                        html += '<div class="box box-primary">';
                        html += '<div class="box-header with-border">';
                        html += '<h3 class="box-title">' + item.pasar_nama + '</h3>';
                        html += '</div>';
                        html += '<div class="box-body">';
                        html += '<table class="table table-bordered">';
                        html += '<tr><td><strong>Komoditas</strong></td><td>' + item.komoditas_nama + '</td></tr>';
                        html += '<tr><td><strong>Tanggal</strong></td><td>' + formatDate(item.harga_tanggal) + '</td></tr>';
                        html += '<tr><td><strong>Harga Beli</strong></td><td>Rp ' + parseInt(item.harga_beli || 0).toLocaleString('id-ID') + '</td></tr>';
                        html += '<tr><td><strong>Harga Jual</strong></td><td>Rp ' + parseInt(item.harga_jual || 0).toLocaleString('id-ID') + '</td></tr>';
                        html += '<tr><td><strong>Harga Rata-rata</strong></td><td><strong>Rp ' + parseInt(item.harga_rata_rata).toLocaleString('id-ID') + '</strong></td></tr>';
                        html += '<tr><td><strong>Stok</strong></td><td>' + (item.stok_tersedia || '-') + '</td></tr>';
                        html += '<tr><td><strong>Kualitas</strong></td><td>' + (item.kualitas || '-') + '</td></tr>';
                        html += '<tr><td><strong>Alamat Pasar</strong></td><td>' + (item.pasar_alamat || '-') + '</td></tr>';
                        html += '</table>';
                        html += '</div>';
                        html += '</div>';
                        html += '</div>';
                    });

                    html += '</div>';
                    $('#price-detail-content').html(html);
                } else {
                    $('#price-detail-content').html('<div class="text-center text-muted"><i class="fa fa-info-circle fa-2x"></i><p>Tidak ada detail data untuk tanggal ini</p></div>');
                }
            },
            error: function() {
                $('#price-detail-content').html('<div class="text-center text-danger"><i class="fa fa-exclamation-triangle fa-2x"></i><p>Gagal memuat detail harga</p></div>');
                toastr.error('Gagal memuat detail harga');
            }
        });
    }

    // =============================================
    // Hierarchical Table Functions
    // =============================================

    // Global variables for hierarchical table
    var currentFilters = {
        pasar_id: 'all',
        filter_type: 'week',
        date_from: '<?= date('Y-m-d', strtotime('monday this week')); ?>',
        date_to: '<?= date('Y-m-d'); ?>'
    };

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

    function renderCommodityRow(commodity, dates, isParent) {
        var rowClass = isParent ? 'parent-commodity' : 'child-commodity';

        var html = '<tr class="' + rowClass + '" data-commodity-id="' + commodity.komoditas_id + '" data-commodity-name="' + commodity.komoditas_nama + '">';

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

    function formatDateDisplay(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit'
        });
    }


    function loadCommodityInfo(commodityId) {
        // Load basic commodity information
        $.ajax({
            url: '<?= base_url('harga_komoditas/get_commodity_info'); ?>',
            type: 'POST',
            data: { commodity_id: commodityId },
            success: function(response) {
                if (response.status && response.data) {
                    $('#commodity-basic-info').html(`
                        <table class="table table-bordered">
                            <tr><td><strong>Nama Komoditas</strong></td><td>${response.data.komoditas_nama || 'N/A'}</td></tr>
                            <tr><td><strong>Satuan</strong></td><td>${response.data.komoditas_satuan || '-'}</td></tr>
                            <tr><td><strong>Status</strong></td><td>${response.data.komoditas_status == 1 ? 'Aktif' : 'Non-Aktif'}</td></tr>
                        </table>
                    `);

                    var stats = response.data.stats || {};
                    $('#commodity-price-stats').html(`
                        <table class="table table-bordered">
                            <tr><td><strong>Harga Rata-rata</strong></td><td>Rp ${formatNumber(stats.avg_price || 0)}</td></tr>
                            <tr><td><strong>Harga Terendah</strong></td><td>Rp ${formatNumber(stats.min_price || 0)}</td></tr>
                            <tr><td><strong>Harga Tertinggi</strong></td><td>Rp ${formatNumber(stats.max_price || 0)}</td></tr>
                            <tr><td><strong>Jumlah Data</strong></td><td>${stats.data_count || 0} records</td></tr>
                        </table>
                    `);
                } else {
                    $('#commodity-basic-info').html('<div class="text-warning">Tidak ada data komoditas</div>');
                    $('#commodity-price-stats').html('<div class="text-warning">Tidak ada data statistik</div>');
                }
            },
            error: function(xhr, status, error) {
                $('#commodity-basic-info').html('<div class="text-danger">Gagal memuat informasi komoditas</div>');
                $('#commodity-price-stats').html('<div class="text-danger">Gagal memuat statistik harga</div>');
                toastr.error('Gagal memuat informasi komoditas');
            }
        });
    }

    // Utility function to format numbers
    function formatNumber(number) {
        return parseInt(number).toLocaleString('id-ID');
    }
});
</script>
