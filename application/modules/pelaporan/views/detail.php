<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* Timeline Styles - Compact & Minimal */
.timeline {
    position: relative;
    padding-left: 20px; /* More compact */
}

.timeline::before {
    content: '';
    position: absolute;
    left: 12px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 8px; /* Very compact */
}

.timeline-marker {
    position: absolute;
    left: -14px; /* Ultra compact */
    top: 2px; /* Adjusted */
    width: 18px; /* Ultra small */
    height: 18px; /* Ultra small */
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    border: 1px solid #fff; /* Ultra thin */
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    z-index: 1;
}

.timeline-marker i {
    font-size: 9px; /* Ultra small */
}

.timeline-content {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px; /* Even smaller radius */
    padding: 8px; /* Very compact */
    box-shadow: 0 1px 2px rgba(0,0,0,0.02); /* Minimal shadow */
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px; /* Ultra compact */
    line-height: 1.1; /* Tighter line height */
}

.timeline-title {
    font-size: 12px; /* Compact */
    font-weight: 600;
    color: #495057;
    margin: 0;
    line-height: 1.2; /* Very tight */
}

.timeline-title .label {
    font-size: 10px; /* Smaller */
    padding: 1px 5px; /* Smaller padding */
    margin-left: 3px; /* Smaller margin */
}

.timeline-time {
    color: #6c757d;
    font-size: 11px; /* Smaller */
    white-space: nowrap;
    font-weight: 500;
}

.timeline-description {
    margin: 2px 0; /* Ultra compact */
}

.timeline-description p {
    font-size: 11px; /* Ultra compact */
    line-height: 1.3; /* Very tight */
    color: #6c757d;
    margin: 0;
}

.timeline-meta {
    margin-top: 6px; /* Reduced */
    padding-top: 4px; /* Reduced */
    border-top: 1px solid #f8f9fa;
}

.timeline-meta small {
    font-size: 10px; /* Smaller */
    color: #868e96;
}

.timeline-meta strong {
    color: #495057;
    font-weight: 600;
}

/* Progress Bar Enhancement */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Compact Timeline Elements */
.timeline-info {
    margin-top: 2px;
}

.timeline-info small {
    font-size: 10px;
    line-height: 1.2;
}

.label-sm {
    font-size: 10px;
    padding: 1px 4px;
}

/* Stats Grid Styles - Enhanced */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #3498db;
}

.stat-icon {
    font-size: 22px;
    margin-right: 15px;
    width: 35px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-item:hover .stat-icon {
    transform: scale(1.1);
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
    margin-bottom: 4px;
    transition: color 0.3s ease;
}

.stat-item:hover .stat-value {
    color: #3498db;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    font-weight: 600;
}



/* Progress Summary */
.progress-summary {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
}

/* Label enhancements */
.label-lg {
    font-size: 12px !important;
    padding: 6px 12px !important;
    border-radius: 4px !important;
}

/* Info box enhancements */
.info-box {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    transition: all 0.3s ease !important;
}

.info-box:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.info-box-icon {
    background-color: rgba(255,255,255,0.9) !important;
    border-right: 1px solid rgba(0,0,0,0.1) !important;
}

.info-box-number {
    font-weight: 600 !important;
    font-size: 18px !important;
}

/* Box solid enhancements */
.box-solid.box-primary > .box-header {
    background: linear-gradient(135deg, #3c8dbc 0%, #2980b9 100%) !important;
    color: white !important;
}

.box-solid.box-success > .box-header {
    background: linear-gradient(135deg, #00a65a 0%, #27ae60 100%) !important;
    color: white !important;
}

.box-solid.box-info > .box-header {
    background: linear-gradient(135deg, #00c0ef 0%, #3498db 100%) !important;
    color: white !important;
}

.box-solid.box-warning > .box-header {
    background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
    color: white !important;
}

/* Enhanced Responsive Design */
@media (max-width: 768px) {
    .timeline {
        padding-left: 18px; /* Ultra compact for mobile */
    }

    .timeline-marker {
        left: -14px; /* Adjusted for mobile */
        width: 16px; /* Smaller for mobile */
        height: 16px; /* Smaller for mobile */
    }

    .timeline-marker i {
        font-size: 8px; /* Smaller for mobile */
    }

    .timeline-content {
        padding: 6px; /* Ultra compact for mobile */
    }

    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .timeline-time {
        align-self: flex-end;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .stat-item {
        padding: 12px;
    }

    .stat-icon {
        font-size: 20px;
        margin-right: 12px;
        width: 30px;
    }

    .stat-value {
        font-size: 18px;
    }



    /* SLA compliance responsive */
    .box-body .row .col-md-6 {
        margin-bottom: 15px;
    }
}

@media (max-width: 480px) {

    .stat-item {
        padding: 10px;
        flex-direction: column;
        text-align: center;
    }

    .stat-icon {
        margin-right: 0;
        margin-bottom: 8px;
    }

    /* Mobile enhancements for detail page */
    .info-box {
        margin-bottom: 15px !important;
    }

    .info-box-number {
        font-size: 16px !important;
    }

    .comment-item {
        padding: 12px !important;
    }

    .comment-header h5 {
        font-size: 14px !important;
    }
}

/* Additional visual enhancements */
.well {
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.box-solid .box-header > .box-title {
    font-weight: 600;
}

.thumbnail {
    transition: all 0.3s ease;
}

.thumbnail:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Additional animations and interactions */
@keyframes fadeInUp {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes slideInLeft {
    0% {
        transform: translateX(-50px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    50% {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
}

/* Apply animations */
.box {
    animation: fadeInUp 0.6s ease-out;
}

.box:nth-child(1) {
    animation-delay: 0.1s;
}

.box:nth-child(2) {
    animation-delay: 0.2s;
}

.box:nth-child(3) {
    animation-delay: 0.3s;
}

.box:nth-child(4) {
    animation-delay: 0.4s;
}

.info-box {
    animation: bounceIn 0.6s ease-out;
}

.info-box:nth-child(1) {
    animation-delay: 0.1s;
}

.info-box:nth-child(2) {
    animation-delay: 0.2s;
}

.info-box:nth-child(3) {
    animation-delay: 0.3s;
}

.info-box:nth-child(4) {
    animation-delay: 0.4s;
}

.comment-item {
    animation: slideInLeft 0.5s ease-out;
}

/* Status badges with pulse animation */
.label-success, .label-danger, .label-info, .label-warning {
    animation: pulse 2s infinite;
}

/* Interactive elements */
.btn {
    transition: all 0.3s ease !important;
}

.btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
}

/* Enhanced focus states */
.form-control:focus, .btn:focus {
    border-color: #3498db !important;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.25) !important;
}

/* Kode Laporan Badge */
.kode-laporan-badge {
    animation: bounceIn 0.8s ease-out;
    position: relative;
    overflow: hidden;
}

.kode-laporan-badge::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

/* Timeline info sections */
.timeline-info-section h4 {
    animation: slideInLeft 0.6s ease-out;
}

/* Enhanced Animations and Visual Effects */
@keyframes slideInFromLeft {
    0% {
        transform: translateX(-100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInFromRight {
    0% {
        transform: translateX(100%);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes countUp {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
}

/* Apply animations to elements */

.stat-item {
    animation: bounceIn 0.6s ease-out;
}

.stat-item:nth-child(2) {
    animation-delay: 0.1s;
}

.stat-item:nth-child(3) {
    animation-delay: 0.2s;
}

.stat-item:nth-child(4) {
    animation-delay: 0.3s;
}

.stat-value {
    animation: countUp 0.8s ease-out;
}

/* Interactive hover effects */
.interactive-card {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.interactive-card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.interactive-card:active {
    transform: translateY(-2px) scale(1.01);
    transition: all 0.1s ease;
}

/* Enhanced tooltips */
.tooltip-enhanced {
    position: relative;
    cursor: help;
}

.tooltip-enhanced:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    animation: fadeInUp 0.3s ease;
}

.tooltip-enhanced:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(100%);
    border: 5px solid transparent;
    border-top-color: rgba(0,0,0,0.9);
    z-index: 1000;
}

/* Loading skeleton animation */
.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Print optimizations */
@media print {
    .stat-item {
        animation: none !important;
        transform: none !important;
        box-shadow: none !important;
    }

    .interactive-card:hover {
        transform: none !important;
        box-shadow: none !important;
    }
}
</style>

<div class="row">
    <div class="col-md-12">
        <?= ce_msg('success'); ?>
        <?= ce_msg('danger'); ?>
    </div>
</div>

<div class="row">
    <!-- Detail Laporan -->
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-file-text"></i> Detail Laporan: <?= $laporan->kode_laporan ?>
                </h3>
                <div class="box-tools pull-right">
                    <a href="<?= base_url('pelaporan') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <?php if (ce_hak_akses('admin.pelaporan.update_status')): ?>
                    <button type="button" class="btn btn-primary btn-sm btn-update-status" data-id="<?= $laporan->id ?>" data-status="<?= $laporan->status ?>">
                        <i class="fa fa-refresh"></i> Update Status
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <!-- Header Badge - Kode Laporan -->
                <div class="row mb-4">
                    <div class="col-md-12 text-center">
                        <div class="kode-laporan-badge" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 50px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); margin-bottom: 20px;">
                            <h2 style="margin: 0; font-weight: 700; font-size: 24px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                <i class="fa fa-hashtag"></i> <?= $laporan->kode_laporan ?>
                            </h2>
                            <div style="font-size: 14px; opacity: 0.9; margin-top: 5px;">
                                <i class="fa fa-file-text"></i> Nomor Laporan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Overview Cards -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon">
                                        <?php
                                        $status_icon = 'fa-question-circle';
                                        $status_color = 'text-muted';
                                        switch($laporan->status) {
                                            case 'LAPOR': $status_icon = 'fa-exclamation-circle'; $status_color = 'text-danger'; break;
                                            case 'DITERIMA': $status_icon = 'fa-check-circle'; $status_color = 'text-info'; break;
                                            case 'DIKERJAKAN': $status_icon = 'fa-cog'; $status_color = 'text-warning'; break;
                                            case 'SELESAI': $status_icon = 'fa-check-square'; $status_color = 'text-success'; break;
                                            case 'BATAL': $status_icon = 'fa-times-circle'; $status_color = 'text-secondary'; break;
                                        }
                                        ?>
                                        <i class="fa <?= $status_icon ?> <?= $status_color ?>"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Status Laporan</span>
                                        <span class="info-box-number">
                                            <?php
                                            $status_config = $this->config->item('report_status_labels');
                                            if (isset($status_config[$laporan->status])) {
                                                $status_label = $status_config[$laporan->status];
                                            } else {
                                                switch($laporan->status) {
                                                    case 'LAPOR': $status_label = 'label-danger'; break;
                                                    case 'DITERIMA': $status_label = 'label-info'; break;
                                                    case 'DIKERJAKAN': $status_label = 'label-warning'; break;
                                                    case 'SELESAI': $status_label = 'label-success'; break;
                                                    case 'BATAL': $status_label = 'label-secondary'; break;
                                                    default: $status_label = 'label-default';
                                                }
                                            }
                                            ?>
                                            <span class="label <?= $status_label ?> label-lg" style="font-size: 12px; padding: 6px 12px;">
                                                <?= $laporan->status ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon">
                                        <?php
                                        $prioritas_icon = 'fa-exclamation-triangle';
                                        $prioritas_color = 'text-warning';
                                        switch($laporan->prioritas) {
                                            case 'URGENT': $prioritas_icon = 'fa-exclamation-triangle'; $prioritas_color = 'text-danger'; break;
                                            case 'TINGGI': $prioritas_icon = 'fa-arrow-up'; $prioritas_color = 'text-warning'; break;
                                            case 'SEDANG': $prioritas_icon = 'fa-minus'; $prioritas_color = 'text-info'; break;
                                            case 'RENDAH': $prioritas_icon = 'fa-arrow-down'; $prioritas_color = 'text-secondary'; break;
                                        }
                                        ?>
                                        <i class="fa <?= $prioritas_icon ?> <?= $prioritas_color ?>"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tingkat Prioritas</span>
                                        <span class="info-box-number">
                                            <?php
                                            $prioritas_class = '';
                                            switch($laporan->prioritas) {
                                                case 'URGENT': $prioritas_class = 'label-danger'; break;
                                                case 'TINGGI': $prioritas_class = 'label-warning'; break;
                                                case 'SEDANG': $prioritas_class = 'label-info'; break;
                                                case 'RENDAH': $prioritas_class = 'label-default'; break;
                                            }
                                            ?>
                                            <span class="label <?= $prioritas_class ?> label-lg" style="font-size: 12px; padding: 6px 12px;">
                                                <?= $laporan->prioritas ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline & Operator Info Combined -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="box box-solid box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-clock-o"></i> Timeline & Penanggung Jawab
                                </h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="timeline-info-section">
                                            <h4 style="margin-top: 0; color: #31708f; border-bottom: 2px solid #bce8f1; padding-bottom: 10px;">
                                                <i class="fa fa-calendar-plus-o"></i> Tanggal Laporan Masuk
                                            </h4>
                                            <div style="background: #d9edf7; border: 1px solid #bce8f1; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                                                <div style="font-size: 18px; font-weight: bold; color: #31708f; margin-bottom: 5px;">
                                                    <?= date('d F Y', strtotime($laporan->created_at)) ?>
                                                </div>
                                                <div style="color: #6c757d;">
                                                    <i class="fa fa-clock-o"></i> Pukul <?= date('H:i:s', strtotime($laporan->created_at)) ?> WIB
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="timeline-info-section">
                                            <h4 style="margin-top: 0; color: #3c763d; border-bottom: 2px solid #d6e9c6; padding-bottom: 10px;">
                                                <i class="fa fa-calendar-check-o"></i> Tanggal Laporan Selesai
                                            </h4>
                                            <?php if ($laporan->tanggal_selesai): ?>
                                            <div style="background: #dff0d8; border: 1px solid #d6e9c6; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                                                <div style="font-size: 18px; font-weight: bold; color: #3c763d; margin-bottom: 5px;">
                                                    <?= date('d F Y', strtotime($laporan->tanggal_selesai)) ?>
                                                </div>
                                                <div style="color: #6c757d;">
                                                    <i class="fa fa-clock-o"></i> Pukul <?= date('H:i:s', strtotime($laporan->tanggal_selesai)) ?> WIB
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div style="background: #fcf8e3; border: 1px solid #faebcc; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
                                                <div style="font-size: 16px; color: #8a6d3b;">
                                                    <i class="fa fa-clock-o"></i> Laporan belum diselesaikan
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <hr style="border-color: #bce8f1;">

                                <div class="operator-info-section">
                                    <h4 style="margin-top: 0; color: #8a6d3b; border-bottom: 2px solid #faebcc; padding-bottom: 10px;">
                                        <i class="fa fa-user-md"></i> Operator Penanggung Jawab
                                    </h4>
                                    <div style="background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%); border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
                                        <?php if ($laporan->operator_nama): ?>
                                        <div class="row">
                                            <div class="col-md-2 text-center">
                                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                                    <i class="fa fa-user fa-2x" style="color: white;"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-10">
                                                <h4 style="margin: 0 0 5px; color: #2c3e50; font-weight: 600;">
                                                    <?= $laporan->operator_nama ?>
                                                </h4>
                                                <p style="margin: 0; color: #7f8c8d; font-size: 14px;">
                                                    <i class="fa fa-user-md"></i> Operator Sistem
                                                </p>
                                                <div style="margin-top: 10px;">
                                                    <span class="label label-primary" style="font-size: 12px; padding: 4px 8px;">
                                                        <i class="fa fa-check-circle"></i> Ditugaskan
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="text-center" style="padding: 20px;">
                                            <i class="fa fa-user-times fa-3x text-muted" style="margin-bottom: 15px;"></i>
                                            <h4 style="color: #95a5a6; margin-bottom: 5px;">Belum Ditugaskan</h4>
                                            <p style="color: #bdc3c7; margin: 0;">
                                                Operator belum ditentukan untuk menangani laporan ini
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Cards -->
                <div class="row">
                    <!-- Laporan Details -->
                    <div class="col-md-6">
                        <div class="box box-solid box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-file-text"></i> Detail Laporan
                                </h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="text-muted"><i class="fa fa-header"></i> Judul Laporan</label>
                                    <p class="lead" style="margin-bottom: 0; font-weight: 600; color: #2c3e50;">
                                        <?= $laporan->judul ?>
                                    </p>
                                </div>

                                <div class="form-group">
                                    <label class="text-muted"><i class="fa fa-tags"></i> Kategori</label>
                                    <div>
                                        <?php if (isset($laporan->kategori_icon) && isset($laporan->kategori_warna)): ?>
                                        <span class="label label-lg" style="background-color: <?= $laporan->kategori_warna ?>; font-size: 12px; padding: 8px 12px;">
                                            <i class="<?= $laporan->kategori_icon ?>"></i> <?= $laporan->nama_kategori ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="label label-default label-lg" style="font-size: 12px; padding: 8px 12px;">
                                            <i class="fa fa-tag"></i> <?= $laporan->kategori ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pelapor Details -->
                    <div class="col-md-6">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-user"></i> Data Pelapor
                                </h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label class="text-muted"><i class="fa fa-user-circle"></i> Nama Lengkap</label>
                                    <p style="margin-bottom: 0; font-weight: 500; font-size: 16px;">
                                        <?= $laporan->pelapor_nama ?>
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="text-muted"><i class="fa fa-phone"></i> Telepon</label>
                                            <p style="margin-bottom: 0; font-weight: 500;">
                                                <?= $laporan->pelapor_telepon ?: '<span class="text-muted">-</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label class="text-muted"><i class="fa fa-id-card"></i> NIK</label>
                                            <p style="margin-bottom: 0; font-weight: 500;">
                                                <?= $laporan->pelapor_nik ?: '<span class="text-muted">-</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Deskripsi Laporan -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-solid box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-align-left"></i> Deskripsi Laporan
                                </h3>
                            </div>
                            <div class="box-body">
                                <div class="well" style="background-color: #f8f9fa; border-left: 4px solid #17a2b8; margin-bottom: 0;">
                                    <p style="margin-bottom: 0; font-size: 14px; line-height: 1.6; color: #495057;">
                                        <?= nl2br($laporan->deskripsi) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Kejadian -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-solid box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-map-marker"></i> Lokasi Kejadian
                                </h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="text-muted"><i class="fa fa-address-book"></i> Alamat Lengkap</label>
                                            <div class="well" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 0;">
                                                <p style="margin-bottom: 0; font-size: 14px; line-height: 1.5; color: #856404;">
                                                    <i class="fa fa-map-pin text-warning"></i> <?= $laporan->alamat ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <?php if ($laporan->lokasi_lat && $laporan->lokasi_lng): ?>
                                        <div class="form-group">
                                            <label class="text-muted"><i class="fa fa-globe"></i> Peta Lokasi</label>
                                            <div class="map-container" style="border: 2px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                                <div id="detail-map" style="height: 300px; width: 100%;"></div>
                                            </div>
                                            <div style="margin-top: 10px; padding: 12px; background: linear-gradient(135deg, #e3f2fd 0%, #fff 100%); border-radius: 6px; border-left: 4px solid #2196f3;">
                                                <div class="row">
                                                    <div class="col-sm-8">
                                                        <small class="text-muted">
                                                            <strong><i class="fa fa-crosshairs text-primary"></i> Koordinat:</strong><br>
                                                            Latitude: <?= $laporan->lokasi_lat ?><br>
                                                            Longitude: <?= $laporan->lokasi_lng ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-sm-4 text-right">
                                                                <small class="text-info">
                                                                    <i class="fa fa-info-circle"></i><br>
                                                                    Klik marker<br>untuk detail
                                                                </small>
                                                                <div style="margin-top: 8px;">
                                                                    <button type="button" class="btn btn-xs btn-primary" onclick="openInGoogleMaps(<?= $laporan->lokasi_lat ?>, <?= $laporan->lokasi_lng ?>, '<?= addslashes($laporan->alamat) ?>')" style="font-size: 10px; padding: 3px 6px; border-radius: 3px;">
                                                                        <i class="fa fa-external-link"></i> Maps
                                                                    </button>
                                                                </div>
                                                            </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                                            <h4><i class="icon fa fa-warning"></i> Koordinat Tidak Tersedia</h4>
                                            Lokasi kejadian belum dilengkapi dengan koordinat GPS.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($laporan->pelapor_alamat): ?>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h4><i class="fa fa-home"></i> Alamat Pelapor</h4>
                        <p><?= nl2br($laporan->pelapor_alamat) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Foto Pelapor -->
                <?php if ($laporan->foto): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4><strong>Foto dari Pelapor</strong></h4>
                        <div class="row">
                            <?php
                            $photos = json_decode($laporan->foto, true);
                            if ($photos && is_array($photos)):
                                foreach ($photos as $index => $photo): ?>
                                    <div class="col-md-3 col-sm-4 col-xs-6">
                                        <div class="thumbnail">
                                            <img src="<?= base_url('uploads/pelaporan/' . $photo) ?>"
                                                 class="img-responsive foto-pelapor"
                                                 style="height: 150px; object-fit: cover; cursor: pointer;"
                                                 data-src="<?= base_url('uploads/pelaporan/' . $photo) ?>"
                                                 data-title="Foto Pelapor <?= $index + 1 ?>">
                                        </div>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upload Foto Petugas -->
        <?php if (ce_hak_akses('admin.pelaporan.update')): ?>
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-camera"></i> Upload Foto Progress/Update</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Upload Foto Progress:</label>
                            <div class="dropzone" id="foto-dropzone">
                                <div class="dz-message">
                                    <i class="fa fa-cloud-upload fa-3x"></i><br>
                                    <strong>Klik atau drag foto ke sini</strong><br>
                                    <small>Format: JPG, PNG, GIF (Max: 5MB)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Preview uploaded photos -->
                        <div id="uploaded-photos" class="row">
                            <!-- Photos will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Files Section -->
        <?php if (!empty($files)): ?>
        <div class="box box-solid box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-paperclip"></i> File Lampiran</h3>
                <div class="box-tools pull-right">
                    <span class="label label-info"><?= count($files) ?> file</span>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <?php foreach ($files as $file): ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="thumbnail" style="border: 2px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #f8f9fa;">
                            <?php if (in_array(strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                            <div style="height: 120px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                                <img src="<?= base_url($file->file_path) ?>" alt="<?= $file->file_name ?>" style="max-height: 120px; max-width: 100%; object-fit: cover; border-radius: 4px;">
                            </div>
                            <?php else: ?>
                            <div style="height: 120px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 4px;">
                                <i class="fa fa-file fa-3x text-white"></i>
                            </div>
                            <?php endif; ?>
                            <div class="caption" style="text-align: center;">
                                <h5 style="font-size: 14px; margin-bottom: 5px; color: #2c3e50;">
                                    <i class="fa fa-file-text text-info"></i> <?= substr($file->file_name, 0, 20) . (strlen($file->file_name) > 20 ? '...' : '') ?>
                                </h5>
                                <p class="text-muted" style="font-size: 12px; margin-bottom: 10px;">
                                    <i class="fa fa-calendar text-muted"></i> <?= date('d/m/Y', strtotime($file->uploaded_at)) ?><br>
                                    <i class="fa fa-user text-muted"></i> <?= substr($file->uploaded_by_name, 0, 15) ?>
                                </p>
                                <a href="<?= base_url($file->file_path) ?>" class="btn btn-primary btn-sm btn-block" target="_blank" style="border-radius: 4px;">
                                    <i class="fa fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <div class="box box-solid box-warning">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-comments"></i> Komentar & Catatan</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-warning btn-sm btn-add-comment" data-id="<?= $laporan->id ?>" style="border-radius: 4px;">
                        <i class="fa fa-plus"></i> Tambah Komentar
                    </button>
                </div>
            </div>
            <div class="box-body">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #fafafa; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="comment-header" style="margin-bottom: 10px;">
                            <div class="row">
                                <div class="col-md-8">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fa fa-user"></i>
                                        </div>
                                        <div>
                                            <h5 style="margin: 0; color: #2c3e50; font-size: 16px;">
                                                <strong><?= $comment->created_by_name ?></strong>
                                            </h5>
                                            <small class="text-muted">
                                                <i class="fa fa-clock-o"></i> <?= date('d M Y, H:i', strtotime($comment->created_at)) ?>
                                                <?php if (isset($comment->is_internal) && $comment->is_internal): ?>
                                                <span class="label label-warning" style="font-size: 10px; padding: 2px 6px; margin-left: 5px;">
                                                    <i class="fa fa-lock"></i> Internal
                                                </span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <!-- Rating Section -->
                                    <?php if (isset($comment->rating) && $comment->rating > 0): ?>
                                    <div class="rating-display">
                                        <div style="margin-bottom: 5px;">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa fa-star" style="color: <?= $i <= $comment->rating ? '#f39c12' : '#ddd' ?>; font-size: 14px;"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-warning" style="font-weight: 600;">
                                            Rating: <?= $comment->rating ?>/5
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="comment-body" style="background-color: white; padding: 15px; border-radius: 6px; border-left: 4px solid #f39c12; margin-bottom: 10px;">
                            <p style="margin: 0; line-height: 1.6; color: #495057; font-size: 14px;">
                                <?= nl2br(htmlspecialchars($comment->comment)) ?>
                            </p>
                        </div>

                        <!-- Comment Actions -->
                        <div class="comment-actions" style="text-align: right;">
                            <?php if (ce_hak_akses('admin.pelaporan.update')): ?>
                            <button class="btn btn-xs btn-default" onclick="replyToComment(<?= $comment->id ?>)" style="border-radius: 3px;">
                                <i class="fa fa-reply"></i> Balas
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- Summary Rating -->
                    <?php 
                    $total_ratings = 0;
                    $rating_count = 0;
                    foreach ($comments as $comment) {
                        if (isset($comment->rating) && $comment->rating > 0) {
                            $total_ratings += $comment->rating;
                            $rating_count++;
                        }
                    }
                    if ($rating_count > 0): 
                        $avg_rating = round($total_ratings / $rating_count, 1);
                    ?>
                    <div class="rating-summary" style="background-color: #ecf0f1; padding: 15px; border-radius: 8px; text-align: center; margin-top: 20px;">
                        <h5><i class="fa fa-star-o"></i> Rating Rata-rata</h5>
                        <div style="font-size: 24px; margin: 10px 0;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star" style="color: <?= $i <= $avg_rating ? '#f39c12' : '#ddd' ?>; font-size: 20px;"></i>
                            <?php endfor; ?>
                        </div>
                        <p><strong><?= $avg_rating ?>/5</strong> dari <?= $rating_count ?> rating</p>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                <div class="text-center" style="padding: 40px;">
                    <i class="fa fa-comments-o fa-3x text-muted"></i>
                    <h4 class="text-muted">Belum ada komentar</h4>
                    <p class="text-muted">Jadilah yang pertama memberikan komentar untuk laporan ini</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="col-md-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Laporan</h3>
            </div>
            <div class="box-body">
                <?php if (!empty($timeline)): ?>
                    <div class="timeline">
                        <?php foreach ($timeline as $index => $item):
                            $status_class = '';
                            $icon_class = '';
                            switch($item->status_ke) {
                                case 'LAPOR':
                                    $status_class = 'danger';
                                    $icon_class = 'fa-exclamation-circle';
                                    break;
                                case 'DITERIMA':
                                    $status_class = 'info';
                                    $icon_class = 'fa-check-circle';
                                    break;
                                case 'DIKERJAKAN':
                                    $status_class = 'warning';
                                    $icon_class = 'fa-cogs';
                                    break;
                                case 'SELESAI':
                                    $status_class = 'success';
                                    $icon_class = 'fa-check-square';
                                    break;
                                default:
                                    $status_class = 'default';
                                    $icon_class = 'fa-circle';
                            }
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-<?= $status_class ?>">
                                <i class="fa <?= $icon_class ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <div class="timeline-title">
                                        <?php if ($item->status_dari): ?>
                                            <span class="text-muted small"><?= $item->status_dari ?></span>
                                            <i class="fa fa-arrow-right text-<?= $status_class ?> mx-1"></i>
                                            <span class="label label-<?= $status_class ?> label-sm"><?= $item->status_ke ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">Laporan dibuat</span>
                                            <span class="label label-<?= $status_class ?> label-sm ml-2"><?= $item->status_ke ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-info">
                                        <small class="text-muted">
                                            <i class="fa fa-clock-o"></i> <?= date('H:i', strtotime($item->created_at)) ?> •
                                            <strong><?= isset($item->operator_nama) && $item->operator_nama ? substr($item->operator_nama, 0, 15) : 'System' ?></strong>
                                        </small>
                                    </div>
                                </div>

                                <?php if ($item->keterangan): ?>
                                <div class="timeline-description">
                                    <small class="text-muted"><?= htmlspecialchars($item->keterangan) ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fa fa-history fa-2x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada riwayat perubahan status</p>
                    </div>
                <?php endif; ?>



                <!-- Progress Time Tracking -->
                <?php if (!empty($timeline) && count($timeline) > 0): ?>
                <div class="mt-4">
                    <div class="progress-time-tracking">
                        <h6 class="mb-3"><i class="fa fa-clock-o text-info"></i> Durasi Proses Penanganan</h6>

                        <?php
                        // Calculate total duration from laporan created_at to actual completion time from history
                        $start_time = strtotime($laporan->created_at); // Waktu laporan dibuat

                        // Cari tanggal selesai dari kolom tanggal_selesai tabel pelaporan
                        $end_time = null;
                        if ($laporan->status == 'SELESAI') {
                            if ($laporan->tanggal_selesai) {
                                $end_time = strtotime($laporan->tanggal_selesai);
                            } else {
                                // Fallback ke history jika tanggal_selesai kosong
                                foreach ($timeline as $item) {
                                    if ($item->status_ke == 'SELESAI') {
                                        $end_time = strtotime($item->created_at);
                                        break;
                                    }
                                }
                            }
                        }

                        // Jika tidak ditemukan tanggal selesai di history, gunakan waktu sekarang
                        if (!$end_time) {
                            $end_time = time(); // Waktu sekarang jika belum selesai atau tidak ada data history
                        }

                        $total_duration_seconds = max(0, $end_time - $start_time);

                        // Format duration
                        $total_days = floor($total_duration_seconds / 86400);
                        $total_hours = floor(($total_duration_seconds % 86400) / 3600);
                        $total_minutes = floor(($total_duration_seconds % 3600) / 60);
                        ?>

                        <!-- Total Duration Summary -->
                        <div class="total-duration-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 style="margin: 0; font-weight: 600;"><i class="fa fa-hourglass-half"></i> Total Durasi Penanganan</h4>
                                    <p style="margin: 5px 0 0; opacity: 0.9;">Dari laporan masuk hingga <?= $laporan->status == 'SELESAI' ? 'selesai' : 'saat ini' ?></p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">
                                        <?php
                                        if ($total_days > 0) {
                                            echo $total_days . ' hari';
                                            if ($total_hours > 0) echo ' ' . $total_hours . ' jam';
                                        } elseif ($total_hours > 0) {
                                            echo $total_hours . ' jam';
                                            if ($total_minutes > 0) echo ' ' . $total_minutes . ' menit';
                                        } else {
                                            echo $total_minutes . ' menit';
                                        }
                                        ?>
                                    </div>
                                    <small>Dari: <?= date('d/m/Y H:i', $start_time) ?></small><br>
                                    <small>
                                        <?php if ($laporan->status == 'SELESAI'): ?>
                                            Sampai: <?= date('d/m/Y H:i', $end_time) ?>
                                        <?php else: ?>
                                            Hingga: <?= date('d/m/Y H:i', $end_time) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>




                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SLA Compliance -->
        <?php if (isset($sla_compliance) && $sla_compliance): ?>
        <div class="box box-<?= $sla_compliance['is_compliant'] ? 'success' : ($sla_compliance['status'] == 'AT_RISK' ? 'warning' : 'danger') ?>">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-clock-o"></i> SLA Compliance
                </h3>
                <div class="box-tools pull-right">
                    <span class="label label-<?= $sla_compliance['is_compliant'] ? 'success' : ($sla_compliance['status'] == 'AT_RISK' ? 'warning' : 'danger') ?>">
                        <?= $sla_compliance['status'] ?>
                    </span>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 36px; font-weight: bold; color: <?= $sla_compliance['is_compliant'] ? '#28a745' : ($sla_compliance['status'] == 'AT_RISK' ? '#ffc107' : '#dc3545') ?>;">
                                <?= round($sla_compliance['compliance_percentage']) ?>%
                            </div>
                            <div style="color: #6c757d; margin-top: 5px;">Compliance Score</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless" style="margin: 0;">
                            <tr>
                                <td><strong>Target SLA:</strong></td>
                                <td><?= $sla_compliance['target_hours'] ?> jam</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Aktual:</strong></td>
                                <td>
                                    <?php
                                    $actual_hours = round($sla_compliance['actual_duration'] / 3600, 1);
                                    echo $actual_hours . ' jam';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($sla_compliance['is_compliant']): ?>
                                        <span class="text-success"><i class="fa fa-check-circle"></i> Sesuai SLA</span>
                                    <?php elseif ($sla_compliance['status'] == 'AT_RISK'): ?>
                                        <span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Berisiko Terlambat</span>
                                    <?php else: ?>
                                        <span class="text-danger"><i class="fa fa-times-circle"></i> Melebihi SLA</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="progress" style="margin-top: 15px; height: 10px;">
                    <div class="progress-bar bg-<?= $sla_compliance['is_compliant'] ? 'success' : ($sla_compliance['status'] == 'AT_RISK' ? 'warning' : 'danger') ?>"
                         style="width: <?= min(100, $sla_compliance['compliance_percentage']) ?>%">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Performance Comparison -->
        <?php if (isset($performance_comparison) && $performance_comparison): ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-line-chart"></i> Perbandingan Performa
                </h3>
                <div class="box-tools pull-right">
                    <small class="text-muted">
                        Dibandingkan dengan <?= $performance_comparison['similar_reports_count'] ?> laporan serupa
                    </small>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">
                                <?= round($performance_comparison['current_duration'] / 3600, 1) ?>h
                            </div>
                            <small style="color: #6c757d;">Waktu Saat Ini</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                                <?= round($performance_comparison['average_duration'] / 3600, 1) ?>h
                            </div>
                            <small style="color: #6c757d;">Rata-rata</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: bold; color: #ffc107;">
                                <?= round($performance_comparison['min_duration'] / 3600, 1) ?>h
                            </div>
                            <small style="color: #6c757d;">Tercepat</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                            <div style="font-size: 24px; font-weight: bold; color: #dc3545;">
                                <?= round($performance_comparison['max_duration'] / 3600, 1) ?>h
                            </div>
                            <small style="color: #6c757d;">Terlama</small>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: center; padding: 15px; background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); border-radius: 8px;">
                    <div style="font-size: 28px; font-weight: bold; color: #1976d2; margin-bottom: 5px;">
                        <?= $performance_comparison['performance_percentile'] ?>%
                    </div>
                    <div style="color: #6c757d;">
                        Persentil Performa
                        <?php if ($performance_comparison['performance_percentile'] >= 75): ?>
                            <span class="label label-success">Sangat Baik</span>
                        <?php elseif ($performance_comparison['performance_percentile'] >= 50): ?>
                            <span class="label label-info">Baik</span>
                        <?php elseif ($performance_comparison['performance_percentile'] >= 25): ?>
                            <span class="label label-warning">Cukup</span>
                        <?php else: ?>
                            <span class="label label-danger">Perlu Perbaikan</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-chart-bar"></i> Ringkasan Aktivitas</h3>
            </div>
            <div class="box-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon text-success">
                            <i class="fa fa-history"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= count($timeline) ?></div>
                            <div class="stat-label">Riwayat</div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon text-info">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= count($comments) ?></div>
                            <div class="stat-label">Komentar</div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon text-warning">
                            <i class="fa fa-paperclip"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?= count($files) ?></div>
                            <div class="stat-label">File</div>
                        </div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-icon text-danger">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">
                                <?php
                                $created = new DateTime($laporan->created_at);
                                $now = new DateTime();
                                $diff = $now->diff($created);
                                echo $diff->days;
                                ?>
                            </div>
                            <div class="stat-label">Hari Sejak Dibuat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modal-status" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Update Status Laporan</h4>
            </div>
            <form id="form-status" method="post" action="<?= base_url('pelaporan/update_status') ?>">
                <div class="modal-body">
                    <input type="hidden" name="id" id="status-id">
                    
                    <div class="form-group">
                        <label for="status">Status Baru <span class="text-red">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan perubahan status..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_selesai">Tanggal Selesai</label>
                        <input type="datetime-local" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Comment -->
<div class="modal fade" id="modal-comment" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Tambah Komentar</h4>
            </div>
            <form id="form-comment" method="post">
                <div class="modal-body">
                    <input type="hidden" name="pelaporan_id" id="comment-pelaporan-id">
                    <input type="hidden" name="parent_id" id="comment-parent-id">
                    
                    <div class="form-group">
                        <label for="comment">Komentar <span class="text-red">*</span></label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" required placeholder="Masukkan komentar atau catatan..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Rating Layanan</label>
                        <div class="rating-input" style="margin: 10px 0;">
                            <div class="stars" style="font-size: 24px;">
                                <span class="star" data-rating="1" style="color: #ddd; cursor: pointer; margin-right: 5px;"><i class="fa fa-star"></i></span>
                                <span class="star" data-rating="2" style="color: #ddd; cursor: pointer; margin-right: 5px;"><i class="fa fa-star"></i></span>
                                <span class="star" data-rating="3" style="color: #ddd; cursor: pointer; margin-right: 5px;"><i class="fa fa-star"></i></span>
                                <span class="star" data-rating="4" style="color: #ddd; cursor: pointer; margin-right: 5px;"><i class="fa fa-star"></i></span>
                                <span class="star" data-rating="5" style="color: #ddd; cursor: pointer; margin-right: 5px;"><i class="fa fa-star"></i></span>
                            </div>
                            <input type="hidden" name="rating" id="rating-value" value="0">
                            <small class="text-muted">Klik bintang untuk memberikan rating (opsional)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_internal" value="1"> Komentar Internal
                        </label>
                        <p class="help-block">Komentar internal hanya dapat dilihat oleh operator</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Komentar</button>
                </div>
            </form>
        </div>
    </div>
</div>

