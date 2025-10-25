<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="row">

    <!-- API Performance Metrics -->
    <div class="col-md-12">
        <div class="box box-solid box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">API Performance Metrics (24 Jam Terakhir)</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-aqua"><i class="fa fa-server"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Requests</span>
                                <span class="info-box-number" id="total-requests">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Success Rate</span>
                                <span class="info-box-number" id="success-rate">0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Avg Response Time</span>
                                <span class="info-box-number" id="avg-response-time">0ms</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-red"><i class="fa fa-ban"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Rate Limit Violations</span>
                                <span class="info-box-number" id="rate-limit-violations">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Usage Chart -->
    <div class="col-md-8">
        <div class="box box-solid box-info">
            <div class="box-header with-border">
                <h3 class="box-title">API Usage Trends (7 Hari Terakhir)</h3>
            </div>
            <div class="box-body">
                <canvas id="usageChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Endpoints -->
    <div class="col-md-4">
        <div class="box box-solid box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Top Endpoints</h3>
            </div>
            <div class="box-body">
                <div id="top-endpoints">
                    <p class="text-center text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent API Logs -->
    <div class="col-md-12">
        <div class="box box-solid box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Recent API Requests</h3>
                <div class="box-tools">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped" id="apiLogsTable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>IP Address</th>
                            <th>Domain</th>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs ?? [] as $log): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($log->created_at)); ?></td>
                            <td><?php echo $log->ip_address; ?></td>
                            <td><?php echo $log->domain ?: '-'; ?></td>
                            <td>
                                <span class="label label-<?php
                                    echo $log->method == 'GET' ? 'success' :
                                         ($log->method == 'POST' ? 'primary' :
                                         ($log->method == 'PUT' ? 'warning' :
                                         ($log->method == 'DELETE' ? 'danger' : 'default')));
                                ?>">
                                    <?php echo $log->method; ?>
                                </span>
                            </td>
                            <td><?php echo $log->endpoint; ?></td>
                            <td>
                                <span class="label label-<?php
                                    echo $log->status_code >= 200 && $log->status_code < 300 ? 'success' :
                                         ($log->status_code >= 400 && $log->status_code < 500 ? 'warning' : 'danger');
                                ?>">
                                    <?php echo $log->status_code; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($log->response_time, 2); ?>ms</td>
                            <td><?php echo substr($log->user_agent, 0, 50) . (strlen($log->user_agent) > 50 ? '...' : ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Load performance metrics
    loadPerformanceMetrics();

    // Load usage chart
    loadUsageChart();

    // Load top endpoints
    loadTopEndpoints();

    // Auto refresh every 30 seconds
    setInterval(function() {
        loadPerformanceMetrics();
        loadUsageChart();
        loadTopEndpoints();
    }, 30000);
});

function loadPerformanceMetrics() {
    $.ajax({
        url: '<?php echo base_url("pengaturan/api_monitoring"); ?>',
        type: 'POST',
        data: { action: 'get_metrics' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                $('#total-requests').text(response.data.total_requests || 0);
                $('#success-rate').text(response.data.success_rate || 0 + '%');
                $('#avg-response-time').text(response.data.avg_response_time ?
                    Math.round(response.data.avg_response_time) + 'ms' : '0ms');
                $('#rate-limit-violations').text(response.data.rate_limit_violations || 0);
            }
        }
    });
}

function loadUsageChart() {
    $.ajax({
        url: '<?php echo base_url("pengaturan/api_monitoring"); ?>',
        type: 'POST',
        data: { action: 'get_usage_chart' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                renderUsageChart(response.data);
            }
        }
    });
}

function loadTopEndpoints() {
    $.ajax({
        url: '<?php echo base_url("pengaturan/api_monitoring"); ?>',
        type: 'POST',
        data: { action: 'get_top_endpoints' },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.data) {
                renderTopEndpoints(response.data);
            }
        }
    });
}

function renderUsageChart(data) {
    const ctx = document.getElementById('usageChart').getContext('2d');

    const labels = data.map(item => item.date);
    const requests = data.map(item => item.total_requests);
    const errors = data.map(item => item.errors);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Requests',
                data: requests,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Errors',
                data: errors,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function renderTopEndpoints(endpoints) {
    let html = '';
    if (endpoints.length === 0) {
        html = '<p class="text-center text-muted">No data available</p>';
    } else {
        html = '<div class="list-group">';
        endpoints.forEach(function(endpoint, index) {
            html += '<div class="list-group-item">';
            html += '<span class="badge">' + endpoint.total_requests + '</span>';
            html += '<strong>' + endpoint.endpoint + '</strong><br>';
            html += '<small class="text-muted">Avg: ' + Math.round(endpoint.avg_response_time) + 'ms</small>';
            html += '</div>';
        });
        html += '</div>';
    }
    $('#top-endpoints').html(html);
}
</script>