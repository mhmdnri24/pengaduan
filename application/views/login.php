<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sistema | Login</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Security headers - perbaikan CSP -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.gstatic.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self';">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta name="referrer" content="no-referrer">
    <link rel="shortcut icon" href="<?= base_url('' . ce_opsi('favicon')); ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary': '#3B82F6',
                        'primary-dark': '#2563EB',
                        'gray-50': '#F9FAFB',
                        'gray-100': '#F3F4F6',
                        'gray-900': '#111827',
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Font - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #1e3a8a 0%, #3b82f6 100%);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @supports (backdrop-filter: blur(10px)) {
            .glass-effect {
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
            }
        }
        
        .input-focus:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.25);
        }
        
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        
        .btn-shimmer::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-shimmer:hover::before {
            left: 100%;
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .brand-logo {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            z-index: 1;
        }
        
        @supports (backdrop-filter: blur(10px)) {
            .floating-shape {
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
            }
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 10%;
            animation: float-1 8s ease-in-out infinite;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            top: 60%;
            left: 60%;
            animation: float-2 6s ease-in-out infinite;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            top: 30%;
            left: 80%;
            animation: float-3 10s ease-in-out infinite;
        }
        
        @keyframes float-1 {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes float-2 {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-15px) translateX(15px); }
        }
        
        @keyframes float-3 {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(20px) translateX(-10px); }
        }
        
        /* Loading animation */
        .spinner {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>

<body class="font-inter bg-gray-50 antialiased">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Side - Information Panel -->
        <div class="hidden lg:flex lg:w-1/2 gradient-bg relative overflow-hidden">
            <!-- Floating shapes -->
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            
            <div class="relative z-10 flex flex-col justify-center px-12 py-16 text-white">
                <!-- Logo -->
                <div class="mb-12">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-cube text-white text-lg"></i>
                        </div>
                        <h1 class="text-2xl font-bold"><?= ce_opsi('nama_situs'); ?></h1>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="max-w-md">
                    <h2 class="text-4xl font-bold mb-6 leading-tight">
                        <?= ce_opsi('tagline'); ?>
                    </h2>
                    <p class="text-lg mb-12 text-white/90 leading-relaxed">
                        Platform terintegrasi untuk mengelola semua aspek bisnis Anda. Dari inventory, penjualan, hingga laporan keuangan dalam satu dashboard.
                    </p>
                    
                    <!-- Features -->
                    <div class="space-y-6">
                        <div class="feature-card glass-effect rounded-2xl p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-chart-line text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-2">Analytics Real-time</h3>
                                    <p class="text-white/80 text-sm">Monitor performa bisnis Anda secara real-time dengan dashboard yang intuitif</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-card glass-effect rounded-2xl p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-shield-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-2">Keamanan Terjamin</h3>
                                    <p class="text-white/80 text-sm">Data bisnis Anda dilindungi dengan enkripsi tingkat enterprise</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="feature-card glass-effect rounded-2xl p-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-mobile-alt text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-2">Mobile Friendly</h3>
                                    <p class="text-white/80 text-sm">Akses dari mana saja, kapan saja dengan tampilan yang responsif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-5 sm:px-8 py-10 sm:py-16 lg:px-16">
            <!-- Mobile Logo -->
            <div class="lg:hidden mb-8 text-center">
                <div class="flex items-center justify-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-cube text-white text-lg"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Sistema</h1>
                </div>
            </div>
            
            <div class="w-full max-w-md mx-auto">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang</h2>
                    <p class="text-gray-600">Masuk ke akun Anda untuk melanjutkan</p>
                </div>
                
                <!-- Login Form -->
                <form id="loginForm" action="<?= base_url('user/login'); ?>" method="POST" class="space-y-6">
                    <!-- CSRF Token -->
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>" />
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">NIP / USERNAME</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="username" name="username" required 
                                   class="input-focus w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Masukkan username">
                        </div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">KATA SANDI</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" required 
                                   class="input-focus w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Masukkan password">
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600 transition-colors duration-200"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Ingat saya</span>
                        </label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            Lupa password?
                        </a>
                    </div>
                    
                    <button type="submit" id="loginBtn"
                            class="btn-shimmer w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        <span id="btnText">Masuk</span>
                        <div id="btnSpinner" class="spinner hidden"></div>
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">atau</span>
                    </div>
                </div>
                
                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Belum punya akun? 
                        <a href="#" class="text-blue-600 hover:text-blue-500 font-semibold transition-colors duration-200">
                            Daftar sekarang
                        </a>
                    </p>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-8">
                    <p class="text-xs text-gray-500">
                        © 2024 Sistema. Semua hak dilindungi.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if ($this->session->flashdata('success')) {
                echo "Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: " . json_encode($this->session->flashdata('success')) . ",
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3B82F6'
                });";
            }
            if ($this->session->flashdata('danger')) {
                // Membersihkan tag HTML dari pesan error sebelum ditampilkan
                $clean_msg = strip_tags($this->session->flashdata('danger'));
                echo "Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: " . json_encode($clean_msg) . ",
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#3B82F6'
                });";
            }
            ?>

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function () {
                    const passwordInput = document.getElementById('password');
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }
            
            // Form submission with AJAX and SweetAlert
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault(); // Mencegah pengiriman form standar

                    const form = this;
                    const formData = new FormData(form);
                    const loginBtn = document.getElementById('loginBtn');
                    const btnText = document.getElementById('btnText');
                    const btnSpinner = document.getElementById('btnSpinner');
                    const username = document.getElementById('username').value;
                    const passwordValue = document.getElementById('password').value;

                    // Validasi sisi klien
                    if (!username || !passwordValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Input Tidak Lengkap',
                            text: 'Mohon isi username dan password.',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Tampilkan status loading
                    loginBtn.disabled = true;
                    btnText.textContent = 'Sedang masuk...';
                    btnSpinner.classList.remove('hidden');

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                             return response.text().then(text => { throw new Error("Server error: " + text) });
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Perbarui token CSRF dari respons server
                        const csrfInput = form.querySelector('input[name="' + data.csrf_token_name + '"]');
                        if (csrfInput) {
                            csrfInput.value = data.csrf_hash;
                        }

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Login Berhasil!',
                                text: 'Anda akan diarahkan ke halaman dasbor.',
                                timer: 1500,
                                showConfirmButton: false,
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href = data.redirect;
                            });
                        } else {
                            // Sembunyikan loading dan tampilkan pesan error
                            loginBtn.disabled = false;
                            btnText.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Masuk';
                            btnSpinner.classList.add('hidden');
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Login Gagal',
                                html: data.message.replace(/<[^>]*>?/gm, '').trim(),
                                confirmButtonText: 'Coba Lagi'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        
                        // Sembunyikan loading dan tampilkan pesan error
                        loginBtn.disabled = false;
                        btnText.innerHTML = '<i class="fas fa-sign-in-alt mr-2"></i> Masuk';
                        btnSpinner.classList.add('hidden');

                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.',
                            confirmButtonText: 'OK'
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>