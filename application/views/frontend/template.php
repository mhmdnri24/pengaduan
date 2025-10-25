<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Resource Hints -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    
    <!-- DNS Prefetch for External Resources -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="//unpkg.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= isset($page_description) ? $page_description : 'Sistem Informasi Masyarakat - Akses Data Publik Transparan untuk Harga Komoditas, Pelaporan, Kepengurusan, dan UMKM' ?>">
    <meta name="keywords" content="<?= isset($page_keywords) ? $page_keywords : 'dashboard masyarakat, harga komoditas, pelaporan masyarakat, kepengurusan, UMKM, sistem informasi publik, transparansi data, pasar tradisional' ?>">
    <meta name="author" content="Dashboard Masyarakat">
    <meta name="robots" content="index, follow">
    <meta name="language" content="Indonesian">
    <meta name="base_url" content="<?= base_url() ?>">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($page_title) ? $page_title . ' - Dashboard Masyarakat' : 'Dashboard Masyarakat - Sistem Informasi Publik' ?>">
    <meta property="og:description" content="<?= isset($page_description) ? $page_description : 'Platform digital untuk mengakses informasi publik seperti harga komoditas, pelaporan masyarakat, data kepengurusan, dan informasi UMKM.' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:site_name" content="Dashboard Masyarakat">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= isset($page_title) ? $page_title . ' - Dashboard Masyarakat' : 'Dashboard Masyarakat - Sistem Informasi Publik' ?>">
    <meta name="twitter:description" content="<?= isset($page_description) ? $page_description : 'Platform digital untuk mengakses informasi publik seperti harga komoditas, pelaporan masyarakat, data kepengurusan, dan informasi UMKM.' ?>">

    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= isset($site_name) ? $site_name : 'Dashboard Masyarakat' ?></title>

    <!-- Critical CSS (Above the Fold) -->
    <style>
        /* Critical CSS for immediate rendering */
        body{font-family:'Inter',system-ui,sans-serif;background:#f9fafb;margin:0;padding:0}
        .loading{display:flex;justify-content:center;align-items:center;height:100vh}
        .nav-item{position:relative}
        .nav-item::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background-color:#4f46e5;transition:width 0.3s ease}
        .nav-item:hover::after,.nav-item.active::after{width:100%}
        .gradient-bg{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
        .card-hover{transition:all 0.3s ease}
        .card-hover:hover{transform:translateY(-5px);box-shadow:0 20px 25px -5px rgba(0,0,0,0.1),0 10px 10px -5px rgba(0,0,0,0.04)}
        
        /* WhatsApp Widget Styles */
        .whatsapp-widget{transition:all 0.3s ease;-webkit-transition:all 0.3s ease;-moz-transition:all 0.3s ease;-o-transition:all 0.3s ease}
        .whatsapp-widget.visible{opacity:1;visibility:visible}
        .whatsapp-widget .whatsapp-icon{transform:scale(1);-webkit-transform:scale(1);-moz-transform:scale(1);-o-transform:scale(1);transition:all 0.3s cubic-bezier(0.68,-0.55,0.265,1.55);-webkit-transition:all 0.3s cubic-bezier(0.68,-0.55,0.265,1.55);-moz-transition:all 0.3s cubic-bezier(0.68,-0.55,0.265,1.55);-o-transition:all 0.3s cubic-bezier(0.68,-0.55,0.265,1.55)}
        .whatsapp-widget:hover .whatsapp-icon{transform:scale(1.1);-webkit-transform:scale(1.1);-moz-transform:scale(1.1);-o-transform:scale(1.1);box-shadow:0 10px 25px rgba(0,0,0,0.2);-webkit-box-shadow:0 10px 25px rgba(0,0,0,0.2);-moz-box-shadow:0 10px 25px rgba(0,0,0,0.2)}
        .whatsapp-widget.bounce{animation:whatsappBounce 2s infinite;-webkit-animation:whatsappBounce 2s infinite;-moz-animation:whatsappBounce 2s infinite;-o-animation:whatsappBounce 2s infinite}
        .whatsapp-widget .whatsapp-tooltip{transform:translateY(-50%) translateX(0);-webkit-transform:translateY(-50%) translateX(0);-moz-transform:translateY(-50%) translateX(0);-o-transform:translateY(-50%) translateX(0)}
        .whatsapp-widget:hover .whatsapp-tooltip{opacity:1;visibility:visible}
        .whatsapp-widget .whatsapp-badge{animation:pulse 2s infinite;-webkit-animation:pulse 2s infinite;-moz-animation:pulse 2s infinite;-o-animation:pulse 2s infinite}
        @keyframes whatsappBounce{0%,20%,53%,80%,100%{transform:translate3d(0,0,0);-webkit-transform:translate3d(0,0,0);-moz-transform:translate3d(0,0,0);-o-transform:translate3d(0,0,0)}40%,43%{transform:translate3d(0,-20px,0);-webkit-transform:translate3d(0,-20px,0);-moz-transform:translate3d(0,-20px,0);-o-transform:translate3d(0,-20px,0)}70%{transform:translate3d(0,-10px,0);-webkit-transform:translate3d(0,-10px,0);-moz-transform:translate3d(0,-10px,0);-o-transform:translate3d(0,-10px,0)}90%{transform:translate3d(0,-4px,0);-webkit-transform:translate3d(0,-4px,0);-moz-transform:translate3d(0,-4px,0);-o-transform:translate3d(0,-4px,0)}}@-webkit-keyframes whatsappBounce{0%,20%,53%,80%,100%{-webkit-transform:translate3d(0,0,0)}40%,43%{-webkit-transform:translate3d(0,-20px,0)}70%{-webkit-transform:translate3d(0,-10px,0)}90%{-webkit-transform:translate3d(0,-4px,0)}}@-moz-keyframes whatsappBounce{0%,20%,53%,80%,100%{-moz-transform:translate3d(0,0,0)}40%,43%{-moz-transform:translate3d(0,-20px,0)}70%{-moz-transform:translate3d(0,-10px,0)}90%{-moz-transform:translate3d(0,-4px,0)}}@-o-keyframes whatsappBounce{0%,20%,53%,80%,100%{-o-transform:translate3d(0,0,0)}40%,43%{-o-transform:translate3d(0,-20px,0)}70%{-o-transform:translate3d(0,-10px,0)}90%{-o-transform:translate3d(0,-4px,0)}}@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(239,68,68,0.7);-webkit-box-shadow:0 0 0 0 rgba(239,68,68,0.7);-moz-box-shadow:0 0 0 0 rgba(239,68,68,0.7)}70%{box-shadow:0 0 0 10px rgba(239,68,68,0);-webkit-box-shadow:0 0 0 10px rgba(239,68,68,0);-moz-box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{box-shadow:0 0 0 0 rgba(239,68,68,0);-webkit-box-shadow:0 0 0 0 rgba(239,68,68,0);-moz-box-shadow:0 0 0 0 rgba(239,68,68,0)}}@-webkit-keyframes pulse{0%{-webkit-box-shadow:0 0 0 0 rgba(239,68,68,0.7)}70%{-webkit-box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{-webkit-box-shadow:0 0 0 0 rgba(239,68,68,0)}}@-moz-keyframes pulse{0%{-moz-box-shadow:0 0 0 0 rgba(239,68,68,0.7)}70%{-moz-box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{-moz-box-shadow:0 0 0 0 rgba(239,68,68,0)}}@-o-keyframes pulse{0%{-o-box-shadow:0 0 0 0 rgba(239,68,68,0.7)}70%{-o-box-shadow:0 0 0 10px rgba(239,68,68,0)}100%{-o-box-shadow:0 0 0 0 rgba(239,68,68,0)}}
        
        /* Responsive WhatsApp Widget */
        @media (max-width: 768px) {
            .whatsapp-widget{width:50px !important;height:50px !important;bottom:4rem !important;right:1rem !important}
            .whatsapp-widget .whatsapp-icon svg{width:24px;height:24px}
            .whatsapp-widget .whatsapp-tooltip{display:none}
            .whatsapp-widget .whatsapp-badge{width:18px;height:18px;font-size:10px}
        }
        
        /* Accessibility */
        .whatsapp-widget:focus{outline:3px solid #4f46e5;outline-offset:2px}
        .whatsapp-widget:focus:not(:focus-visible){outline:none}
        .whatsapp-widget:focus-visible{outline:3px solid #4f46e5;outline-offset:2px}
    </style>

    <!-- Optimized Favicon -->
    <?php if (isset($site_favicon) && $site_favicon): ?>
        <link rel="icon" href="<?= base_url($site_favicon); ?>">
    <?php else: ?>
        <link rel="icon" href="<?= base_url('assets/images/favicon.ico'); ?>">
    <?php endif; ?>
    
    <!-- Google Fonts (Optimized) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script src="<?= base_url('assets/js/tailwind-config.js'); ?>"></script>
    
    <!-- Combined External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/frontend.css'); ?>">

    <!-- Page Specific CSS -->
    <?php if (isset($page_css) && is_array($page_css)): ?>
        <?php foreach ($page_css as $css_file): ?>
            <link rel="stylesheet" href="<?= base_url('assets/css/' . $css_file); ?>" media="print" onload="this.media='all'">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="bg-gray-50 font-sans antialiased" data-page="<?= isset($current_page) ? $current_page : 'beranda' ?>">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <?php if (function_exists('ce_opsi') && ce_opsi('logo')): ?>
                            <img src="<?= base_url(ce_opsi('logo')) ?>" alt="Logo" class="h-10 w-auto mr-3">
                        <?php else: ?>
                            <i class="fas fa-city text-3xl text-indigo-600 mr-3"></i>
                        <?php endif; ?>
                        <span class="text-xl font-bold text-gray-800"><?= isset($site_name) ? $site_name : 'Dashboard Masyarakat' ?></span>
                    </div>
                </div>

                <!-- Unified Navigation Menu -->
                <div class="hidden md:flex md:items-center md:space-x-1 lg:space-x-8">
                    <?php
                    $menu_items = [
                        ['url' => base_url(), 'label' => 'Beranda', 'icon' => 'fa-home', 'page' => 'beranda'],
                        //['url' => base_url('frontend/harga_komoditas'), 'label' => 'Monitoring Retribusi', 'icon' => 'fa-chart-line', 'page' => 'harga_komoditas'],
                        //['url' => base_url('frontend/fasilitas'), 'label' => 'Fasilitas Umum', 'icon' => 'fa-building', 'page' => 'fasilitas'],
                        ['url' => base_url('frontend/pelaporan'), 'label' => 'Laporan Masyarakat', 'icon' => 'fa-clipboard-list', 'page' => 'pelaporan'],
                        ['url' => base_url('frontend/kontak_darurat'), 'label' => 'Kontak Darurat', 'icon' => 'fa-phone-alt', 'page' => 'kontak_darurat']
                    ];
                    
                    foreach ($menu_items as $item):
                        $is_active = (isset($current_page) && $current_page == $item['page']);
                        $active_class = $is_active ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50';
                    ?>
                        <a href="<?= $item['url'] ?>"
                           class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-md <?= $active_class ?>">
                            <i class="fas <?= $item['icon'] ?> mr-2"></i>
                            <?= $item['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-gray-700 hover:text-indigo-600 focus:outline-none focus:text-indigo-600 p-2">
                        <i class="fas fa-bars w-6 h-6" x-show="!mobileMenuOpen"></i>
                        <i class="fas fa-times w-6 h-6" x-show="mobileMenuOpen"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Unified Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <?php foreach ($menu_items as $item):
                    $is_active = (isset($current_page) && $current_page == $item['page']);
                    $active_class = $is_active ? 'text-indigo-600 bg-indigo-50' : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50';
                ?>
                    <a href="<?= $item['url'] ?>"
                       class="flex items-center px-3 py-2 text-base font-medium rounded-md <?= $active_class ?>">
                        <i class="fas <?= $item['icon'] ?> mr-3"></i>
                        <?= $item['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        <?php
        // Load content based on current page
        $content_file = (isset($current_page) ? $current_page : 'beranda');

        // Special handling for detail pages
        if (isset($current_page) && $current_page == 'pelaporan' && $this->uri->segment(2) == 'detail') {
            $content_file = 'pelaporan-detail';
        }
        
        // Special handling for kategori detail pages
        if (isset($current_page) && $current_page == 'detail-kategori') {
            $content_file = 'detail_kategori';
        }
        
        // Special handling for fasilitas list pages
        if (isset($current_page) && $current_page == 'fasilitas-list') {
            $content_file = 'fasilitas_list';
        }
        
        // Special handling for fasilitas kategori pages
        if (isset($current_page) && $current_page == 'fasilitas') {
            $content_file = 'fasilitas_kategori';
        }
        
      
        // Try to load from module first, then fallback to regular views
        if (file_exists(APPPATH . 'modules/frontend/views/' . $content_file . '.php')) {
            $this->load->view('frontend/' . $content_file, isset($data) ? $data : []);
        } elseif (file_exists(APPPATH . 'views/frontend/' . $content_file . '.php')) {
            $this->load->view('frontend/' . $content_file, isset($data) ? $data : []);
        } else {
            $this->load->view('frontend/beranda', isset($data) ? $data : []);
        }
        ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <?php if (function_exists('ce_opsi') && ce_opsi('logo')): ?>
                            <img src="<?= base_url(ce_opsi('logo')) ?>" alt="Logo" class="h-8 w-auto mr-2">
                        <?php else: ?>
                            <i class="fas fa-city text-2xl text-indigo-400 mr-2"></i>
                        <?php endif; ?>
                        <span class="text-xl font-bold"><?= isset($site_name) ? $site_name : 'Dashboard Masyarakat' ?></span>
                    </div>
                    <p class="text-gray-400"><?= isset($tagline) ? $tagline : 'Dashboard Masyarakat' ?></p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Menu Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= base_url('frontend/fasilitas') ?>" class="text-gray-400 hover:text-white flex items-center"><i class="fas fa-building mr-2"></i> Fasilitas Umum</a></li>
                        <li><a href="<?= base_url('frontend/pelaporan') ?>" class="text-gray-400 hover:text-white flex items-center"><i class="fas fa-clipboard-list mr-2"></i> Pelaporan</a></li>
                        <li><a href="<?= base_url('frontend/kontak_darurat') ?>" class="text-gray-400 hover:text-white flex items-center"><i class="fas fa-store mr-2"></i> Kontak Darurat</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Bantuan</h3>
                    <ul class="space-y-2 text-gray-400">
                        <?php if (isset($contact_data) && is_array($contact_data) && count($contact_data) > 0): ?>
                            <?php foreach ($contact_data as $kontak): ?>
                                <?php if (!empty($kontak['nama'])): ?>
                                    <li class="font-medium text-white mb-2"><?= $kontak['nama'] ?></li>
                                <?php endif; ?>
                                
                                <?php if (!empty($kontak['telepon'])): ?>
                                    <li class="flex items-center">
                                        <i class="fas fa-phone mr-2"></i>
                                        <a href="tel:<?= $kontak['telepon'] ?>" class="hover:text-white transition-colors">
                                            <?= $kontak['telepon'] ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($kontak['email'])): ?>
                                    <li class="flex items-center">
                                        <i class="fas fa-envelope mr-2"></i>
                                        <a href="mailto:<?= $kontak['email'] ?>" class="hover:text-white transition-colors">
                                            <?= $kontak['email'] ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if (!empty($kontak['jabatan'])): ?>
                                    <li class="flex items-center">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        <?= $kontak['jabatan'] ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Default contact information if no data is available -->
                            <li class="flex items-center">
                                <i class="fas fa-envelope mr-2"></i>
                                <?php if (function_exists('ce_opsi') && ce_opsi('email')): ?>
                                    <a href="mailto:<?= ce_opsi('email') ?>" class="hover:text-white transition-colors">
                                        <?= ce_opsi('email') ?>
                                    </a>
                                <?php else: ?>
                                    <a href="mailto:info@dashboard.com" class="hover:text-white transition-colors">
                                        info@dashboard.com
                                    </a>
                                <?php endif; ?>
                            </li>
                            
                            <?php if (function_exists('ce_opsi') && ce_opsi('nomor_kontak')): ?>
                                <li class="flex items-center">
                                    <i class="fas fa-phone mr-2"></i>
                                    <a href="tel:<?= ce_opsi('nomor_kontak') ?>" class="hover:text-white transition-colors">
                                        <?= ce_opsi('nomor_kontak') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if (function_exists('ce_opsi') && ce_opsi('alamat')): ?>
                                <li class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <?= ce_opsi('alamat') ?>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-youtube text-xl"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">© <?= date('Y') ?> Dashboard Masyarakat. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Widget -->
    <?php
    // Get WhatsApp contact data from database
    $bantuan_kontak = ce_opsi('bantuan_kontak');
    $kontak_data = !empty($bantuan_kontak) ? json_decode($bantuan_kontak, true) : [];
    
    // Get first available WhatsApp number
    $whatsapp_number = '';
    if (!empty($kontak_data)) {
        foreach ($kontak_data as $kontak) {
            if (!empty($kontak['telepon'])) {
                // Clean phone number (remove non-numeric characters)
                $whatsapp_number = preg_replace('/[^0-9]/', '', $kontak['telepon']);
                break;
            }
        }
    }
    
    // WhatsApp widget configuration
    $wa_config = [
        'enabled' => ce_opsi('wa_widget_enabled', '1') === '1',
        'position' => ce_opsi('wa_widget_position', 'bottom-right'),
        'color' => ce_opsi('wa_widget_color', '#25D366'),
        'size' => ce_opsi('wa_widget_size', '60'),
        'message' => ce_opsi('wa_widget_message', 'Halo, saya membutuhkan bantuan.'),
        'show_tooltip' => ce_opsi('wa_widget_tooltip', '1') === '1',
        'tooltip_text' => ce_opsi('wa_widget_tooltip_text', 'Hubungi Kami via WhatsApp'),
        'show_badge' => ce_opsi('wa_widget_badge', '1') === '1',
        'badge_text' => ce_opsi('wa_widget_badge_text', '1'),
        'analytics' => ce_opsi('wa_widget_analytics', '1') === '1'
    ];
    ?>
    
    <?php if ($wa_config['enabled'] && !empty($whatsapp_number)): ?>
    <div id="whatsapp-widget"
         class="whatsapp-widget fixed <?php echo $wa_config['position'] === 'bottom-right' ? 'bottom-6 right-6' : 'bottom-6 left-6'; ?> z-50 opacity-0 invisible"
         role="button"
         tabindex="0"
         aria-label="Hubungi Kami via WhatsApp"
         data-phone="<?php echo $whatsapp_number; ?>"
         data-message="<?php echo htmlspecialchars($wa_config['message']); ?>"
         data-analytics="<?php echo $wa_config['analytics'] ? '1' : '0'; ?>"
         data-tooltip="<?php echo $wa_config['show_tooltip'] ? '1' : '0'; ?>"
         data-tooltip-text="<?php echo htmlspecialchars($wa_config['tooltip_text']); ?>"
         data-badge="<?php echo $wa_config['show_badge'] ? '1' : '0'; ?>"
         data-badge-text="<?php echo htmlspecialchars($wa_config['badge_text']); ?>"
         style="width: <?php echo $wa_config['size']; ?>px; height: <?php echo $wa_config['size']; ?>px;">
        
        <!-- Widget Container -->
        <div class="whatsapp-widget-container relative w-full h-full">
            <!-- Notification Badge -->
            <?php if ($wa_config['show_badge']): ?>
            <span class="whatsapp-badge absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold animate-pulse">
                <?php echo $wa_config['badge_text']; ?>
            </span>
            <?php endif; ?>
            
            <!-- WhatsApp Icon -->
            <div class="whatsapp-icon w-full h-full bg-green-500 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:shadow-xl cursor-pointer"
                 style="background-color: <?php echo $wa_config['color']; ?>;">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.149-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </div>
            
            <!-- Tooltip -->
            <?php if ($wa_config['show_tooltip']): ?>
            <div class="whatsapp-tooltip absolute <?php echo $wa_config['position'] === 'bottom-right' ? 'right-full mr-3' : 'left-full ml-3'; ?> top-1/2 transform -translate-y-1/2 bg-gray-800 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 invisible transition-all duration-300 pointer-events-none">
                <?php echo $wa_config['tooltip_text']; ?>
                <div class="absolute <?php echo $wa_config['position'] === 'bottom-right' ? 'right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2' : 'left-0 top-1/2 transform -translate-y-1/2 -translate-x-1/2'; ?> w-0 h-0 border-t-8 border-t-transparent border-b-8 border-b-transparent <?php echo $wa_config['position'] === 'bottom-right' ? 'border-l-8 border-l-gray-800' : 'border-r-8 border-r-gray-800'; ?>"></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deferred JavaScript -->
    <script defer src="<?= base_url('assets/js/frontend.js'); ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Page Specific JavaScript -->
    <?php if (isset($page_js) && is_array($page_js)): ?>
        <?php foreach ($page_js as $js_file): ?>
            <script defer src="<?= base_url('assets/js/' . $js_file); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline JavaScript for page-specific data -->
    <?php if (isset($inline_js)): ?>
        <script>
            <?= $inline_js ?>
        </script>
    <?php endif; ?>

    <!-- Centralized Frontend JavaScript -->
    <?php $this->load->view('frontend/js_frontend'); ?>
</body>
</html>