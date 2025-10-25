<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= ce_opsi('nama_situs', 'E-Diklat'); ?> | Registrasi</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Security headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: <?= base_url(); ?>; connect-src 'self' https://sinanan.bkpsdm.lubuklinggaukota.go.id;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="referrer" content="no-referrer">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                        'poppins': ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#1e40af',
                        'primary-dark': '#1e3a8a',
                        'secondary': '#3b82f6',
                        'accent': '#f59e0b',
                        'success': '#10b981',
                        'gray-50': '#f8fafc',
                        'gray-100': '#f1f5f9',
                        'gray-200': '#e2e8f0',
                        'gray-300': '#cbd5e1',
                        'gray-400': '#94a3b8',
                        'gray-500': '#64748b',
                        'gray-600': '#475569',
                        'gray-700': '#334155',
                        'gray-800': '#1e293b',
                        'gray-900': '#0f172a',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
        :root {
            --primary-color: #1e40af;
            --primary-light: #3b82f6;
            --primary-dark: #1e3a8a;
            --secondary-color: #60a5fa;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border-light: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body { 
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: #f8fafc;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .gradient-bg { 
            background: #f8fafc;
            position: relative;
            min-height: 100vh;
        }
        
        /* Card Styles - Simple and Clean */
        .main-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 16px 48px rgba(30, 64, 175, 0.08), 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            margin: 0.5rem;
            max-width: none;
            width: calc(100% - 1rem);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1rem;
            box-shadow: 0 12px 36px rgba(30, 64, 175, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: calc(100% - 1rem);
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }
        
        .jalur-card {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .jalur-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(30, 64, 175, 0.15);
        }
        
        .jalur-card.selected {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(30, 64, 175, 0.2);
        }
        
        .jalur-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(30, 64, 175, 0.05), transparent);
            transition: left 0.6s;
        }
        
        .jalur-card:hover::before {
            left: 100%;
        }
        
        /* Form Input Styles */
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #ffffff;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1), 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
            background: #fafbfc;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
        }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 64, 175, 0.35);
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        /* Icon Styles */
        .icon-container {
            width: 4rem;
            height: 4rem;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            transition: all 0.3s ease;
        }
        
        .icon-container i {
            color: white;
            font-size: 1.5rem;
        }
        
        .jalur-card.selected .icon-container {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.4);
        }
        
        /* Guide Item Styles */
        .guide-item {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .guide-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.08);
        }
        
        .guide-number {
            width: 2rem;
            height: 2rem;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        /* Animation Styles */
        .slide-down {
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Responsive Design */
        .container-main {
            padding: 0.5rem;
            width: 100%;
            max-width: none;
            margin: 0 auto;
        }
        
        .form-row { 
            display: flex; 
            gap: 1rem; 
        }
        
        .form-row .w-1-3 { 
            width: 33.3333%; 
        }
        
        .form-row .w-2-3 { 
            width: 66.6667%; 
        }
        
        /* Select2 Styling */
        .select2-container .select2-selection--single { 
            height: 46px !important; 
            border-radius: 8px !important; 
            border: 2px solid #e2e8f0 !important; 
            background: #ffffff !important;
            padding: 0.5rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow { 
            height: 44px !important; 
        }
        
        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary-color) !important;
        }
        
        /* Spinner */
        .spinner { 
            border: 2px solid rgba(255, 255, 255, 0.3); 
            border-radius: 50%; 
            border-top: 2px solid white; 
            width: 16px; 
            height: 16px; 
            animation: spin 1s linear infinite; 
            display: inline-block; 
        }
        
        @keyframes spin { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container-main {
                padding: 0.25rem;
            }
            
            .main-card {
                margin: 0.25rem;
                padding: 1.75rem;
                border-radius: 16px;
                width: calc(100% - 0.5rem);
                box-shadow: 0 12px 36px rgba(30, 64, 175, 0.1), 0 2px 8px rgba(0, 0, 0, 0.06);
            }
            
            .sidebar-card {
                margin: 0.25rem;
                margin-bottom: 0.75rem;
                padding: 1.5rem;
                width: calc(100% - 0.5rem);
                box-shadow: 0 8px 24px rgba(30, 64, 175, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
            }
            
            .form-row {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-row .w-1-3,
            .form-row .w-2-3 {
                width: 100%;
            }
            
            .jalur-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 6px 20px rgba(30, 64, 175, 0.08);
            }
            
            .icon-container {
                width: 3.5rem;
                height: 3.5rem;
            }
            
            .icon-container i {
                font-size: 1.25rem;
            }
            
            .form-input {
                padding: 1rem;
                font-size: 16px; /* Prevent zoom on iOS */
                font-family: 'Poppins', sans-serif;
            }
            
            .btn-primary,
            .btn-secondary {
                padding: 1rem 1.5rem;
                width: 100%;
                margin-bottom: 0.5rem;
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
            }
            
            h1 { 
                font-size: 1.5rem; 
                font-family: 'Poppins', sans-serif;
                font-weight: 600;
            }
            h2 { 
                font-size: 1.25rem; 
                font-family: 'Poppins', sans-serif;
                font-weight: 600;
            }
            h3 { 
                font-size: 1.125rem; 
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
            }
            h4 { 
                font-size: 1rem; 
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
            }
            
            /* Ensure text is readable on mobile */
            body, p, span, div, label {
                font-family: 'Poppins', sans-serif;
                font-size: 14px;
                line-height: 1.5;
            }
            
            .form-label {
                font-family: 'Poppins', sans-serif;
                font-weight: 500;
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .main-card {
                margin: 0.125rem;
                padding: 1.25rem;
                width: calc(100% - 0.25rem);
            }
            
            .sidebar-card {
                margin: 0.125rem;
                padding: 1rem;
                width: calc(100% - 0.25rem);
            }
            
            .guide-item {
                padding: 1rem;
            }
            
            .guide-number {
                width: 1.75rem;
                height: 1.75rem;
                font-size: 0.8rem;
                font-family: 'Poppins', sans-serif;
                font-weight: 600;
            }
            
            /* Improve font readability on small screens */
            body, p, span, div, label, input, button {
                font-family: 'Poppins', sans-serif !important;
            }
        }
    </style>
</head>

<body class="font-inter antialiased gradient-bg">
    <div class="min-h-screen flex items-center justify-center py-4">
        <div class="container-main">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">
                
                <!-- Panduan Pendaftaran -->
                <div class="lg:col-span-1 order-2 lg:order-1">
                    <div class="sidebar-card text-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-center text-primary" style="font-family: 'Poppins', sans-serif;">
                            <i class="fas fa-info-circle mr-2"></i>
                            Panduan Pendaftaran
                        </h3>
                        
                        <div class="space-y-3 mb-4">
                            <div class="guide-item">
                                <div class="flex items-start space-x-3">
                                    <div class="guide-number">1</div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-sm">Pilih Jalur Pendaftaran</h4>
                                        <p class="text-xs text-gray-600">Tentukan apakah Anda ASN dalam instansi atau luar instansi</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="guide-item">
                                <div class="flex items-start space-x-3">
                                    <div class="guide-number">2</div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-sm">Isi Data Pribadi</h4>
                                        <p class="text-xs text-gray-600">Lengkapi form dengan data yang valid dan benar</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="guide-item">
                                <div class="flex items-start space-x-3">
                                    <div class="guide-number">3</div>
                                    <div>
                                        <h4 class="font-semibold mb-1 text-sm">Verifikasi Admin</h4>
                                        <p class="text-xs text-gray-600">Tunggu konfirmasi aktivasi akun dari administrator</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <h4 class="font-semibold mb-2 flex items-center text-sm text-blue-800">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Tips Penting
                            </h4>
                            <ul class="space-y-1 text-xs text-blue-700">
                                <li class="flex items-start">
                                    <i class="fas fa-check mr-2 mt-0.5 text-xs flex-shrink-0"></i>
                                    <span>Gunakan NIP ASN anda</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check mr-2 mt-0.5 text-xs flex-shrink-0"></i>
                                    <span>Password minimal 6 karakter</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check mr-2 mt-0.5 text-xs flex-shrink-0"></i>
                                    <span>Nomor Whatsapp aktif untuk notifikasi</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check mr-2 mt-0.5 text-xs flex-shrink-0"></i>
                                    <span>Periksa whatsapp untuk konfirmasi</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="text-sm font-semibold text-gray-800 mb-3 text-center" style="font-family: 'Poppins', sans-serif;">
                                <i class="fas fa-headset mr-2 text-blue-600"></i>
                                Butuh Bantuan?
                            </h5>
                            <?php if (!empty($bantuan_kontak)): ?>
                                <?php 
                                $contacts = json_decode($bantuan_kontak, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($contacts)): 
                                ?>
                                    <div class="space-y-2">
                                        <?php foreach ($contacts as $contact): ?>
                                            <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">
                                                <div class="flex flex-col space-y-2">
                                                    <?php if (!empty($contact['nama'])): ?>
                                                        <div class="text-sm font-medium text-gray-900" style="font-family: 'Poppins', sans-serif;">
                                                            <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                                                            <?= htmlspecialchars($contact['nama']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="flex flex-col space-y-1">
                                                        <?php if (!empty($contact['telepon'])): ?>
                                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact['telepon']); ?>?text=Halo,%20saya%20butuh%20bantuan%20untuk%20pendaftaran%20akun%20E-Diklat" 
                                                               target="_blank" 
                                                               class="inline-flex items-center text-green-600 hover:text-green-700 transition-colors text-xs">
                                                                <i class="fab fa-whatsapp mr-2"></i>
                                                                <span style="font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($contact['telepon']) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($contact['email'])): ?>
                                                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>?subject=Bantuan%20Pendaftaran%20Akun%20E-Diklat" 
                                                               class="inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors text-xs">
                                                                <i class="fas fa-envelope mr-2"></i>
                                                                <span style="font-family: 'Poppins', sans-serif;"><?= htmlspecialchars($contact['email']) ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Fallback jika bukan JSON yang valid -->
                                    <div class="text-center">
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $bantuan_kontak); ?>?text=Halo,%20saya%20butuh%20bantuan%20untuk%20pendaftaran%20akun" target="_blank" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 transition-colors text-sm px-3 py-2 rounded-lg">
                                            <i class="fab fa-whatsapp mr-2"></i>
                                            <span style="font-family: 'Poppins', sans-serif;">Hubungi Admin</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center">
                                    <a href="#" class="inline-flex items-center text-white bg-green-500 hover:bg-green-600 transition-colors text-sm px-3 py-2 rounded-lg">
                                        <i class="fab fa-whatsapp mr-2"></i>
                                        <span style="font-family: 'Poppins', sans-serif;">Hubungi Admin</span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Registrasi -->
                <div class="lg:col-span-3 order-1 lg:order-2">
                    <div class="main-card">
                        <!-- Header -->
                        <div class="text-center mb-6">
                            <div class="flex items-center justify-center mb-4">
                                <img src="<?= base_url(ce_opsi('logo', 'assets/img/logo-default.png')); ?>" alt="Logo" class="h-12 w-12 mr-3 rounded-lg shadow-lg">
                                <div class="text-left">
                                    <h1 class="text-xl font-semibold text-gray-900" style="font-family: 'Poppins', sans-serif;"><?= ce_opsi('nama_situs', 'E-Diklat'); ?></h1>
                                    <p class="text-sm text-gray-600" style="font-family: 'Poppins', sans-serif;">Sistem Informasi Pelatihan ASN</p>
                                </div>
                            </div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2" style="font-family: 'Poppins', sans-serif;">Buat Akun Baru</h2>
                            <p class="text-gray-600" style="font-family: 'Poppins', sans-serif;">Pilih jalur pendaftaran yang sesuai dengan status Anda</p>
                        </div>

                        <!-- Pilihan Jalur Pendaftaran -->
                        <div id="jalur-pendaftaran" class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center" style="font-family: 'Poppins', sans-serif;">Pilih Jalur Pendaftaran</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="jalur-card" data-jalur="dalam">
                                    <div class="icon-container">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2" style="font-family: 'Poppins', sans-serif;">ASN DALAM INSTANSI</h4>
                                    <p class="text-sm text-gray-600 mb-3" style="font-family: 'Poppins', sans-serif;">Untuk pegawai ASN yang bekerja di instansi penyelenggara diklat</p>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                                        <div class="flex items-center justify-center text-xs text-blue-700">
                                            <i class="fas fa-magic mr-2"></i>
                                            <span class="font-medium" style="font-family: 'Poppins', sans-serif;">Auto-fill dengan NIP</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="jalur-card" data-jalur="luar">
                                    <div class="icon-container">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2" style="font-family: 'Poppins', sans-serif;">ASN LUAR INSTANSI</h4>
                                    <p class="text-sm text-gray-600 mb-3" style="font-family: 'Poppins', sans-serif;">Untuk pegawai ASN dari instansi lain atau masyarakat umum</p>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-2">
                                        <div class="flex items-center justify-center text-xs text-blue-700">
                                            <i class="fas fa-edit mr-2"></i>
                                            <span class="font-medium" style="font-family: 'Poppins', sans-serif;">Input manual</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Jalur Terpilih -->
                        <div id="info-jalur" class="bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 mb-6 rounded-r-lg hidden" role="alert">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle mr-3 text-lg flex-shrink-0"></i>
                                <span id="info-text" class="font-medium text-sm"></span>
                            </div>
                        </div>

                        <!-- Form Pendaftaran -->
                        <div id="form-container" class="hidden">
                            <form id="formRegister" action="<?= base_url('user/register'); ?>" method="POST" class="space-y-4">
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                                <input type="hidden" id="jalur_pendaftaran" name="jalur_pendaftaran" value="">
                                
                                <div class="form-row">
                                    <div class="w-full">
                                        <label for="nip_baru" class="form-label">NIP Baru *</label>
                                        <input type="text" id="nip_baru" name="nip_baru" required class="form-input" placeholder="Masukkan NIP Anda">
                                        <div id="nip-error" class="text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="w-full">
                                        <label for="nip_lama" class="form-label">NIP Lama</label>
                                        <input type="text" id="nip_lama" name="nip_lama" class="form-input" placeholder="NIP Lama (opsional)">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="w-1-3">
                                        <label for="gelar_depan" class="form-label">Gelar Depan</label>
                                        <input type="text" id="gelar_depan" name="gelar_depan" class="form-input" placeholder="Dr., Prof., dll">
                                    </div>
                                    <div class="w-2-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                        <input type="text" id="nama_lengkap" name="nama_lengkap" required class="form-input" placeholder="Nama lengkap Anda">
                                    </div>
                                    <div class="w-1-3">
                                        <label for="gelar_belakang" class="form-label">Gelar Belakang</label>
                                        <input type="text" id="gelar_belakang" name="gelar_belakang" class="form-input" placeholder="S.Kom, M.Si, dll">
                                    </div>
                                </div>

                                <div>
                                    <label for="no_hp" class="form-label">Nomor HP/WhatsApp *</label>
                                    <input type="tel" id="no_hp" name="no_hp" required class="form-input" placeholder="08xxxxxxxxxx">
                                    <div id="notelp-error" class="text-red-500 text-xs mt-1"></div>
                                </div>

                                <div class="form-row">
                                    <div class="w-full">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" id="password" name="password" required class="form-input" placeholder="Minimal 6 karakter">
                                        <div id="password-error" class="text-red-500 text-xs mt-1"></div>
                                    </div>
                                    <div class="w-full">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required class="form-input" placeholder="Ulangi password">
                                    </div>
                                </div>

                                <div>
                                    <label for="instansi_asal" class="form-label">Instansi Asal *</label>
                                    <select id="instansi_asal" name="instansi_asal" class="form-input">
                                        <option value="">Pilih atau ketik nama instansi</option>
                                    </select>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                                    <button type="button" id="backBtn" class="btn-secondary">
                                        <i class="fa fa-arrow-left mr-2"></i>
                                        Kembali
                                    </button>
                                    <button type="submit" id="registerBtn" class="btn-primary flex-1">
                                        <span id="btnText">
                                            <i class="fa fa-user-plus mr-2"></i>Daftar Sekarang
                                        </span>
                                        <div id="btnSpinner" class="spinner hidden ml-2"></div>
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Login Link -->
                        <div class="text-center mt-6">
                            <p class="text-sm text-gray-600">Sudah memiliki akun? 
                                <a href="<?= base_url('user/login'); ?>" class="text-primary hover:text-primary-dark font-semibold transition-colors duration-200">
                                    Masuk di sini
                                </a>
                            </p>
                            
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const jalurCards = document.querySelectorAll('.jalur-card');
        const formContainer = document.getElementById('form-container');
        const infoJalur = document.getElementById('info-jalur');
        const infoText = document.getElementById('info-text');
        const jalurPendaftaranInput = document.getElementById('jalur_pendaftaran');
        const backBtn = document.getElementById('backBtn');
        
        // Handle jalur selection
        jalurCards.forEach(card => {
            card.addEventListener('click', function() {
                const jalur = this.getAttribute('data-jalur');
                
                // Remove selected class from all cards
                jalurCards.forEach(c => c.classList.remove('selected'));
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Set jalur value
                jalurPendaftaranInput.value = jalur;
                
                // Show info and form
                showFormWithInfo(jalur);
            });
        });
        
        function showFormWithInfo(jalur) {
            let infoMessage = '';
            let infoClass = '';
            
            if (jalur === 'dalam') {
                infoMessage = 'Anda memilih jalur ASN DALAM INSTANSI. Silakan isi NIP terlebih dahulu, data akan terisi otomatis jika ditemukan.';
                infoClass = 'bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-primary text-blue-800';
            } else {
                infoMessage = 'Anda memilih jalur ASN LUAR INSTANSI. Silakan lengkapi semua kolom form dengan benar.';
                infoClass = 'bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 text-green-800';
            }
            
            infoJalur.className = infoClass + ' p-4 mb-6 rounded-r-lg slide-down';
            infoText.textContent = infoMessage;
            infoJalur.classList.remove('hidden');
            
            // Show form with animation
            setTimeout(() => {
                formContainer.classList.remove('hidden');
                formContainer.classList.add('slide-down');
            }, 200);
        }
        
        // Handle back button
        backBtn.addEventListener('click', function() {
            // Hide form and info
            formContainer.classList.add('hidden');
            infoJalur.classList.add('hidden');
            
            // Reset selection
            jalurCards.forEach(c => c.classList.remove('selected'));
            jalurPendaftaranInput.value = '';
            
            // Reset form
            document.getElementById('formRegister').reset();
            $('#instansi_asal').val(null).trigger('change');
        });

        $('#instansi_asal').select2({
            placeholder: "Pilih atau ketik nama instansi",
            ajax: {
                url: "<?= base_url('user/get_instansi'); ?>",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                },
                cache: true
            }
        });

        const nipInput = document.getElementById('nip_baru');
        const notelpInput = document.getElementById('no_hp');
        const passwordInput = document.getElementById('password');
        const registerBtn = document.getElementById('registerBtn');
        const nipErrorDiv = document.getElementById('nip-error');
        const notelpErrorDiv = document.getElementById('notelp-error');
        const passwordErrorDiv = document.getElementById('password-error');

        function checkNip() {
            const nip = nipInput.value;
            const jalur = jalurPendaftaranInput.value;
            nipErrorDiv.textContent = '';
            registerBtn.disabled = false;

            if (nip.length < 10) return;

            // Check if NIP exists in our DB
            $.post('<?= base_url('user/check_nip') ?>', { nip_baru: nip, '<?= $this->security->get_csrf_token_name(); ?>': $('input[name=<?= $this->security->get_csrf_token_name(); ?>]').val() }, function(response) {
                // Update CSRF token
                $('input[name=' + response.csrf_token_name + ']').val(response.csrf_hash);

                if (response.exists) {
                    nipErrorDiv.textContent = response.message;
                    registerBtn.disabled = true;
                } else {
                    // If not exists and jalur adalah 'dalam', try to fetch from external API
                    if (jalur === 'dalam') {
                        fetchPegawaiData(nip);
                    }
                }
            }, 'json');
        }
        
        function checkNoTelp() {
            const notelp = notelpInput.value;
            notelpErrorDiv.textContent = '';
            registerBtn.disabled = false;

            if (notelp.length < 10) return;

            // Check if No Telp exists in our DB
            $.post('<?= base_url('user/check_notelp') ?>', { no_hp: notelp, '<?= $this->security->get_csrf_token_name(); ?>': $('input[name=<?= $this->security->get_csrf_token_name(); ?>]').val() }, function(response) {
                // Update CSRF token
                $('input[name=' + response.csrf_token_name + ']').val(response.csrf_hash);

                if (response.exists) {
                    notelpErrorDiv.textContent = response.message;
                    registerBtn.disabled = true;
                }
            }, 'json');
        }

        function checkPassword() {
            const password = passwordInput.value;
            if (password.length > 0 && password.length < 6) {
                passwordErrorDiv.textContent = 'Password minimal 6 karakter.';
                registerBtn.disabled = true;
            } else {
                passwordErrorDiv.textContent = '';
                registerBtn.disabled = false;
            }
        }

        function fetchPegawaiData(nip) {
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            btnText.textContent = 'Mencari NIP...';
            btnSpinner.classList.remove('hidden');

            fetch('<?= base_url('user/getDataPegawai/') ?>' + nip)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const pegawai = data.data;
                    document.getElementById('nip_lama').value = pegawai.nip_lama || '';
                    document.getElementById('gelar_depan').value = pegawai.gelar_depan || '';
                    document.getElementById('nama_lengkap').value = pegawai.nama_lengkap || '';
                    document.getElementById('gelar_belakang').value = pegawai.gelar_belakang || '';
                    document.getElementById('no_hp').value = pegawai.no_telp || '';
                    
                    var $instansiSelect = $('#instansi_asal');
                    var option = new Option(pegawai.unitkerja, pegawai.unitkerja, true, true);
                    $instansiSelect.append(option).trigger('change');

                    Swal.fire('Sukses', 'Data pegawai ditemukan dan form telah diisi.', 'success');
                } else {
                    Swal.fire('Info', 'Data pegawai tidak ditemukan. Silakan lengkapi form secara manual.', 'info');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                Swal.fire('Error', 'Gagal mengambil data pegawai. Silakan coba lagi atau isi manual.', 'error');
            })
            .finally(() => {
                btnText.innerHTML = '<i class="fa fa-user-plus"></i> Daftar';
                btnSpinner.classList.add('hidden');
            });
        }

        nipInput.addEventListener('blur', checkNip);
        notelpInput.addEventListener('blur', checkNoTelp);
        passwordInput.addEventListener('keyup', checkPassword);

        $('#formRegister').on('submit', function(e){
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const jalur = jalurPendaftaranInput.value;

            if (!jalur) {
                Swal.fire('Error', 'Silakan pilih jalur pendaftaran terlebih dahulu!', 'error');
                return;
            }

            if (password.length < 6) {
                Swal.fire('Error', 'Password minimal 6 karakter!', 'error');
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire('Error', 'Password dan Konfirmasi Password tidak cocok!', 'error');
                return;
            }

            // Disable button and show spinner
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            registerBtn.disabled = true;
            btnText.textContent = 'Mendaftar...';
            btnSpinner.classList.remove('hidden');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json', // Expect JSON response from server
                success: function(response) {
                    // Update CSRF token
                    $('input[name=' + response.csrf_token_name + ']').val(response.csrf_hash);

                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Pendaftaran berhasil. Akun Anda sedang dalam proses verifikasi.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?= base_url('user/login'); ?>';
                            }
                        });
                    } else {
                        Swal.fire('Gagal', response.message, 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                     Swal.fire('Error', 'Terjadi kesalahan: ' + textStatus + ' - ' + errorThrown, 'error');
                },
                complete: function() {
                    // Re-enable button and hide spinner
                    registerBtn.disabled = false;
                    btnText.innerHTML = '<i class="fa fa-user-plus"></i> Daftar';
                    btnSpinner.classList.add('hidden');
                }
            });
        });
    });
    </script>
</body>
</html>