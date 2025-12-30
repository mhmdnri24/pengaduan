// Login Page JavaScript for Lapor Pak Wali
document.addEventListener('DOMContentLoaded', function() {
    // Load site name from API
    loadSiteName();
    
    // --- Tab Switching Logic ---
    const nikTab = document.getElementById('nikLoginTab');
    const waTab = document.getElementById('waLoginTab');
    const nikForm = document.getElementById('nikLoginForm');
    const waForm = document.getElementById('waLoginForm');

    // Check if elements exist before adding event listeners
    if (nikTab && waTab && nikForm && waForm) {
        nikTab.addEventListener('click', () => switchTab('nik'));
        waTab.addEventListener('click', () => switchTab('wa'));

        function switchTab(active) {
            const isActiveNik = active === 'nik';
            nikTab.classList.toggle('bg-white', isActiveNik);
            nikTab.classList.toggle('text-primary-blue', isActiveNik);
            nikTab.classList.toggle('shadow-sm', isActiveNik);
            nikTab.classList.toggle('text-slate-500', !isActiveNik);
            waTab.classList.toggle('bg-white', !isActiveNik);
            waTab.classList.toggle('text-primary-blue', !isActiveNik);
            waTab.classList.toggle('shadow-sm', !isActiveNik);
            waTab.classList.toggle('text-slate-500', isActiveNik);
            nikForm.classList.toggle('hidden', !isActiveNik);
            waForm.classList.toggle('hidden', isActiveNik);
        }
    }

    // --- OTP Flow Logic ---
    const loginForm = document.getElementById('loginForm');
    const loginContainer = document.getElementById('loginContainer');
    const otpContainer = document.getElementById('otpVerificationContainer');
    const otpCard = document.getElementById('otpCard');

    // Check if login form exists before adding event listener
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleLogin();
        });
    }

    function handleLogin() {
        // Check if required elements exist
        if (!nikForm || !waForm) {
            console.error('Form elements not found');
            return;
        }
        
        // Simple validation check
        const activeForm = nikForm.classList.contains('hidden') ? waForm : nikForm;
        const input = activeForm.querySelector('input');
        if (!input) {
            console.error('Input element not found');
            return;
        }
        
        if (!input.checkValidity()) {
            input.reportValidity();
            return;
        }

        // Get login data based on active tab
        let loginData;
        if (!nikForm.classList.contains('hidden')) {
            // NIK Login
            const nikInput = document.getElementById('nikLogin');
            if (!nikInput) {
                console.error('NIK input not found');
                return;
            }
            const nikValue = nikInput.value.trim();
            loginData = { type: 'nik', value: nikValue };
        } else {
            // WhatsApp Login
            const waInput = document.getElementById('waLogin');
            if (!waInput) {
                console.error('WhatsApp input not found');
                return;
            }
            const waValue = waInput.value.trim();
            loginData = { type: 'wa', value: waValue };
        }

        // Show loading notification
        Swal.fire({
            title: 'Verifikasi Data...',
            text: 'Sedang memeriksa data Anda dan mengirimkan kode OTP.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Call API to authenticate user
        authenticateUser(loginData);
    }

    // --- OTP Input Handling ---
    const otpInputs = Array.from(document.querySelectorAll('#otp-inputs input'));
    otpInputs.forEach((input, index) => {
        if (input) {
            input.addEventListener('input', () => {
                if (input.value && index < otpInputs.length - 1 && otpInputs[index + 1]) {
                    otpInputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0 && otpInputs[index - 1]) {
                    otpInputs[index - 1].focus();
                }
            });
        }
    });

    // --- Resend OTP Logic ---
    const resendBtn = document.getElementById('resendOtpBtn');
    const countdownEl = document.getElementById('countdown');
    let timer;

    function startResendTimer(duration) {
        let count = duration;
        if (resendBtn) resendBtn.disabled = true;
        if (countdownEl) countdownEl.textContent = `(${count}d)`;

        timer = setInterval(() => {
            count--;
            if (countdownEl) countdownEl.textContent = `(${count}d)`;
            if (count <= 0) {
                clearInterval(timer);
                if (resendBtn) resendBtn.disabled = false;
                if (countdownEl) countdownEl.textContent = '';
            }
        }, 1000);
    }
    
    if (resendBtn) {
        resendBtn.addEventListener('click', () => {
            Swal.fire({
                icon: 'success',
                title: 'OTP Terkirim!',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            startResendTimer(60);
        });
    }

    // --- OTP Form Submission ---
    const otpForm = document.getElementById('otpForm');
    if (otpForm) {
        otpForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get OTP values
            const otpValue = otpInputs.map(input => input ? input.value : '').join('');
            
            // Get stored login data
            const loginData = JSON.parse(sessionStorage.getItem('loginData'));
            
            if (!loginData) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Sesi login telah kadaluarsa. Silakan coba lagi.'
                });
                return;
            }
            
            // Verify OTP
            verifyOTP(loginData, otpValue);
        });
    }

    // --- API Functions ---
    function loadSiteName() {
        // Get site name from API
        fetch('../api/auth_endpoint.php?action=settings')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' && data.data && data.data.nama_situs) {
                    document.title = `Login - ${data.data.nama_situs}`;
                    // Update h1 if exists
                    const titleElement = document.querySelector('h1');
                    if (titleElement) {
                        titleElement.textContent = `Selamat Datang di ${data.data.nama_situs}`;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading site name:', error);
            });
    }

    function authenticateUser(loginData) {
        // Store login data for OTP verification
        sessionStorage.setItem('loginData', JSON.stringify(loginData));
        
        // Call API based on login type
        const apiUrl = '../api/auth_endpoint.php?action=login';
        
        const requestData = {};
        if (loginData.type === 'nik') {
            requestData.nik = loginData.value;
        } else {
            requestData.phone = loginData.value;
        }
        
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            Swal.close();
            
            if (data.status === 'success') {
                // Login successful, show success notification first
                Swal.fire({
                    icon: 'success',
                    title: 'Data Ditemukan',
                    text: 'OTP telah dikirim ke WhatsApp Anda.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // Show OTP form
                    if (loginContainer) loginContainer.classList.add('opacity-0');
                    if (otpContainer) otpContainer.classList.remove('hidden');
                    
                    // Trigger slide-up animation
                    setTimeout(() => {
                        if (loginContainer) loginContainer.classList.add('hidden');
                        if (otpCard) otpCard.classList.remove('translate-y-full');
                        if (otpInputs[0]) otpInputs[0].focus(); // Focus the first OTP input
                    }, 500);

                    // Update phone number and start timer
                    const userPhoneElement = document.getElementById('userPhone');
                    if (loginData.type === 'wa') {
                        if (userPhoneElement) userPhoneElement.textContent = '******' + loginData.value.slice(-4);
                    } else {
                        // For NIK login, we need to get the phone number from the response
                        if (data.data && data.data.no_telpon) {
                            if (userPhoneElement) userPhoneElement.textContent = '******' + data.data.no_telpon.slice(-4);
                        } else {
                            if (userPhoneElement) userPhoneElement.textContent = '**********';
                        }
                    }
                    startResendTimer(60);
                });
            } else {
                // Login failed
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: data.message || 'Data tidak ditemukan. Pastikan NIK/WhatsApp yang Anda masukkan benar.'
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Login error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Terjadi kesalahan saat login. Silakan coba lagi.'
            });
        });
    }

    function verifyOTP(loginData, otpValue) {
        // Call API to verify OTP
        const formData = new FormData();
        if (loginData.type === 'nik') {
            formData.append('nik', loginData.value);
        } else {
            formData.append('no_telpon', loginData.value);
        }
        formData.append('otp', otpValue);
        
        const requestData = {};
        if (loginData.type === 'nik') {
            requestData.nik = loginData.value;
        } else {
            requestData.phone = loginData.value;
        }
        requestData.otp = otpValue;
        
        fetch('../api/auth_endpoint.php?action=verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                // OTP verification successful
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: 'Anda akan diarahkan ke halaman utama.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // Save user data to localStorage
                    if (data.data) {
                        localStorage.setItem('currentUser', JSON.stringify(data.data));
                        localStorage.setItem('isLoggedIn', 'true');
                        localStorage.setItem('token', data.data.token);
                    }
                    // Redirect to dashboard
                    window.location.href = 'dashboard.html';
                });
            } else {
                // OTP verification failed
                Swal.fire({
                    icon: 'error',
                    title: 'Verifikasi Gagal',
                    text: data.message || 'Kode OTP tidak valid. Silakan coba lagi.'
                });
                
                // Clear OTP inputs
                otpInputs.forEach(input => {
                    input.value = '';
                });
                otpInputs[0].focus();
            }
        })
        .catch(error => {
            console.error('OTP verification error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: 'Terjadi kesalahan saat verifikasi OTP. Silakan coba lagi.'
            });
        });
    }
});