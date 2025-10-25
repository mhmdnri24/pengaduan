/**
 * Login Form JavaScript
 * Handles login form functionality with secure CSRF and session management
 */

document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginText = document.getElementById('loginText');
    const loginSpinner = document.getElementById('loginSpinner');
    const nimInput = document.getElementById('nim');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const nimError = document.getElementById('nim-error');
    const passwordError = document.getElementById('password-error');

    // Configuration - Get base URL from current location
    const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -2).join('/');
    const config = {
        loginUrl: baseUrl + '/login_mahasiswa/authenticate',
        dashboardUrl: baseUrl + '/dashboard_mahasiswa',
        minPasswordLength: 6,
        maxLoginAttempts: 5,
        lockoutTime: 15 * 60 * 1000 // 15 minutes
    };

    // State management
    let loginAttempts = parseInt(localStorage.getItem('loginAttempts') || '0');
    let lastAttemptTime = parseInt(localStorage.getItem('lastAttemptTime') || '0');

    // Initialize
    init();

    function init() {
        checkLockout();
        bindEvents();
        setupValidation();
        autoHideFlashMessages();
    }

    function bindEvents() {
        // Form submission
        form.addEventListener('submit', handleFormSubmit);

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', togglePasswordVisibility);

        // Input validation
        nimInput.addEventListener('blur', validateNIM);
        nimInput.addEventListener('input', clearNIMError);
        passwordInput.addEventListener('blur', validatePassword);
        passwordInput.addEventListener('input', clearPasswordError);

        // Enter key support
        document.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && !loginBtn.disabled) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Prevent form resubmission on page refresh
        window.addEventListener('beforeunload', function () {
            if (loginBtn.disabled) {
                enableLoginButton();
            }
        });
    }

    function setupValidation() {
        // Real-time validation setup
        nimInput.setAttribute('autocomplete', 'username');
        passwordInput.setAttribute('autocomplete', 'current-password');

        // Prevent copy/paste for security (optional)
        // passwordInput.addEventListener('paste', function(e) {
        //     e.preventDefault();
        //     showError('Password tidak dapat di-paste untuk keamanan');
        // });
    }

    function checkLockout() {
        const now = Date.now();
        const timeSinceLastAttempt = now - lastAttemptTime;

        if (loginAttempts >= config.maxLoginAttempts && timeSinceLastAttempt < config.lockoutTime) {
            const remainingTime = Math.ceil((config.lockoutTime - timeSinceLastAttempt) / 60000);
            lockForm(`Terlalu banyak percobaan login. Coba lagi dalam ${remainingTime} menit.`);

            // Set timeout to unlock
            setTimeout(() => {
                unlockForm();
                resetLoginAttempts();
            }, config.lockoutTime - timeSinceLastAttempt);
        } else if (timeSinceLastAttempt >= config.lockoutTime) {
            resetLoginAttempts();
        }
    }

    function handleFormSubmit(e) {
        e.preventDefault();

        // Check if form is locked
        if (loginBtn.disabled && loginBtn.classList.contains('locked')) {
            return;
        }

        // Clear previous errors
        clearAllErrors();

        // Get form data
        const formData = new FormData(form);
        const nim = nimInput.value.trim();
        const password = passwordInput.value;

        // Validate inputs
        if (!validateInputs(nim, password)) {
            return;
        }

        // Disable button and show loading
        disableLoginButton();

        // Submit form
        submitLogin(formData);
    }

    function validateInputs(nim, password) {
        let isValid = true;

        // Validate NIM
        if (!nim) {
            showNIMError('NIM tidak boleh kosong');
            isValid = false;
        } else if (!/^[0-9]+$/.test(nim)) {
            showNIMError('NIM harus berupa angka');
            isValid = false;
        } else if (nim.length < 5) {
            showNIMError('NIM minimal 5 digit');
            isValid = false;
        }

        // Validate Password
        if (!password) {
            showPasswordError('Password tidak boleh kosong');
            isValid = false;
        } else if (password.length < config.minPasswordLength) {
            showPasswordError(`Password minimal ${config.minPasswordLength} karakter`);
            isValid = false;
        }

        return isValid;
    }

    function submitLogin(formData) {
        fetch(config.loginUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                handleLoginResponse(data);
            })
            .catch(error => {
                console.error('Login error:', error);
                handleLoginError(error);
            })
            .finally(() => {
                enableLoginButton();
            });
    }

    function handleLoginResponse(data) {
        // Update CSRF token if provided
        if (data.csrf_hash && data.csrf_token_name) {
            updateCSRFToken(data.csrf_token_name, data.csrf_hash);
        }

        if (data.status) {
            // Success
            resetLoginAttempts();
            showSuccessAndRedirect(data.message, data.redirect_url || config.dashboardUrl);
        } else {
            // Error
            incrementLoginAttempts();
            showLoginError(data.message);

            // Check if should lock after this attempt
            if (loginAttempts >= config.maxLoginAttempts) {
                lockForm('Terlalu banyak percobaan login gagal. Akun dikunci sementara.');
            }
        }
    }

    function handleLoginError(error) {
        incrementLoginAttempts();

        let errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi.';

        if (error.message.includes('Failed to fetch')) {
            errorMessage = 'Koneksi bermasalah. Periksa internet Anda.';
        } else if (error.message.includes('500')) {
            errorMessage = 'Server sedang bermasalah. Coba lagi nanti.';
        }

        showError(errorMessage);
    }

    function showSuccessAndRedirect(message, redirectUrl) {
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil!',
            html: `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
                    </div>
                    <p class="text-gray-700 mb-4">${message}</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Anda akan diarahkan ke dashboard dalam beberapa detik...
                        </p>
                    </div>
                </div>
            `,
            confirmButtonColor: '#10b981',
            confirmButtonText: '<i class="fas fa-arrow-right mr-2"></i>Lanjut ke Dashboard',
            timer: 3000,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                popup: 'swal2-show',
                title: 'text-xl font-bold text-gray-900',
                confirmButton: 'bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors'
            },
            didOpen: () => {
                // Add custom animation
                const popup = Swal.getPopup();
                popup.style.animation = 'fadeInUp 0.5s ease-out';
            }
        }).then((result) => {
            // Clear any stored login attempts
            localStorage.removeItem('loginAttempts');
            localStorage.removeItem('lastAttemptTime');
            
            // Redirect immediately if user clicks button or timer expires
            window.location.href = redirectUrl;
        });
    }

    function showLoginError(message) {
        const remainingAttempts = config.maxLoginAttempts - loginAttempts;
        let fullMessage = message;

        if (remainingAttempts > 0 && remainingAttempts <= 3) {
            fullMessage += `\n\nSisa percobaan: ${remainingAttempts}`;
        }

        Swal.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: fullMessage,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Coba Lagi'
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            text: message,
            confirmButtonColor: '#ef4444'
        });
    }

    function togglePasswordVisibility() {
        const icon = togglePasswordBtn.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function validateNIM() {
        const nim = nimInput.value.trim();

        if (nim && !/^[0-9]+$/.test(nim)) {
            showNIMError('NIM harus berupa angka');
            return false;
        } else if (nim && nim.length < 5) {
            showNIMError('NIM minimal 5 digit');
            return false;
        }

        clearNIMError();
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;

        if (password && password.length < config.minPasswordLength) {
            showPasswordError(`Password minimal ${config.minPasswordLength} karakter`);
            return false;
        }

        clearPasswordError();
        return true;
    }

    function showNIMError(message) {
        nimError.textContent = message;
        nimInput.classList.add('border-red-500');
    }

    function clearNIMError() {
        nimError.textContent = '';
        nimInput.classList.remove('border-red-500');
    }

    function showPasswordError(message) {
        passwordError.textContent = message;
        passwordInput.classList.add('border-red-500');
    }

    function clearPasswordError() {
        passwordError.textContent = '';
        passwordInput.classList.remove('border-red-500');
    }

    function clearAllErrors() {
        clearNIMError();
        clearPasswordError();
    }

    function disableLoginButton() {
        loginBtn.disabled = true;
        loginText.classList.add('hidden');
        loginSpinner.classList.remove('hidden');
        loginBtn.classList.add('opacity-75');
    }

    function enableLoginButton() {
        loginBtn.disabled = false;
        loginText.classList.remove('hidden');
        loginSpinner.classList.add('hidden');
        loginBtn.classList.remove('opacity-75', 'locked');
    }

    function lockForm(message) {
        loginBtn.disabled = true;
        loginBtn.classList.add('locked');
        loginBtn.innerHTML = `<i class="fas fa-lock mr-2"></i>${message}`;

        // Disable inputs
        nimInput.disabled = true;
        passwordInput.disabled = true;
    }

    function unlockForm() {
        loginBtn.disabled = false;
        loginBtn.classList.remove('locked');
        loginBtn.innerHTML = loginText.innerHTML;

        // Enable inputs
        nimInput.disabled = false;
        passwordInput.disabled = false;
    }

    function incrementLoginAttempts() {
        loginAttempts++;
        lastAttemptTime = Date.now();
        localStorage.setItem('loginAttempts', loginAttempts.toString());
        localStorage.setItem('lastAttemptTime', lastAttemptTime.toString());
    }

    function resetLoginAttempts() {
        loginAttempts = 0;
        localStorage.removeItem('loginAttempts');
        localStorage.removeItem('lastAttemptTime');
    }

    function updateCSRFToken(tokenName, tokenHash) {
        const csrfInput = document.querySelector(`input[name="${tokenName}"]`);
        if (csrfInput) {
            csrfInput.value = tokenHash;
        }

        // Update meta tag if exists
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            csrfMeta.setAttribute('content', tokenHash);
        }
    }

    function autoHideFlashMessages() {
        // Auto-hide flash messages after 5 seconds
        setTimeout(function () {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(function (alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(function () {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            });
        }, 5000);
    }

    // Expose some functions for debugging (remove in production)
    if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
        window.loginFormDebug = {
            resetAttempts: resetLoginAttempts,
            getAttempts: () => loginAttempts,
            unlock: unlockForm
        };
    }
});