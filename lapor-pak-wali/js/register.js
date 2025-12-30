// Registration Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoading = document.getElementById('submitLoading');
    const successMessage = document.getElementById('successMessage');

    // Form validation setup
    const validator = new FormValidator(registerForm);
    
    validator
        .addRule('namaLengkap', ValidationRules.required, 'Nama lengkap harus diisi')
        .addRule('namaLengkap', ValidationRules.minLength(3), 'Nama lengkap minimal 3 karakter')
        .addRule('namaLengkap', ValidationRules.maxLength(100), 'Nama lengkap maksimal 100 karakter')
        .addRule('namaLengkap', ValidationRules.pattern(/^[a-zA-Z\s.'-]+$/), 'Nama hanya boleh mengandung huruf, spasi, titik, tanda kutip, dan tanda hubung')
        
        .addRule('nikKtp', ValidationRules.required, 'NIK KTP harus diisi')
        .addRule('nikKtp', ValidationRules.nik, 'NIK KTP harus 16 digit angka')
        .addRule('nikKtp', checkNIKUnique, 'NIK KTP sudah terdaftar')
        
        .addRule('nomorWa', ValidationRules.required, 'Nomor WhatsApp harus diisi')
        .addRule('nomorWa', ValidationRules.phone, 'Format nomor WhatsApp tidak valid (10-13 digit)')
        .addRule('nomorWa', checkPhoneUnique, 'Nomor WhatsApp sudah terdaftar')
        
        .addRule('termsConditions', ValidationRules.checked, 'Anda harus menyetujui syarat dan ketentuan');

    // Real-time validation
    setupRealTimeValidation();

    // Form submission
    registerForm.addEventListener('submit', handleSubmit);

    function setupRealTimeValidation() {
        // NIK input formatting and validation
        const nikInput = document.getElementById('nikKtp');
        nikInput.addEventListener('input', (e) => {
            // Remove non-numeric characters
            e.target.value = e.target.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            if (e.target.value.length > 16) {
                e.target.value = e.target.value.slice(0, 16);
            }
            
            // Real-time validation
            validateField('nikKtp');
        });

        // Phone number input formatting
        const phoneInput = document.getElementById('nomorWa');
        phoneInput.addEventListener('input', (e) => {
            // Remove non-numeric characters
            e.target.value = e.target.value.replace(/\D/g, '');
            
            // Remove leading zero if present
            if (e.target.value.startsWith('0')) {
                e.target.value = e.target.value.substring(1);
            }
            
            // Limit to 13 digits
            if (e.target.value.length > 13) {
                e.target.value = e.target.value.slice(0, 13);
            }
            
            // Real-time validation
            validateField('nomorWa');
        });

        // Name input validation
        const nameInput = document.getElementById('namaLengkap');
        nameInput.addEventListener('blur', () => {
            validateField('namaLengkap');
        });

        // Terms checkbox validation
        const termsCheckbox = document.getElementById('termsConditions');
        termsCheckbox.addEventListener('change', () => {
            validateField('termsConditions');
        });
    }

    function validateField(fieldName) {
        const field = registerForm.querySelector(`[name="${fieldName}"]`);
        const errorElement = document.getElementById(`${fieldName}Error`);
        const formData = new FormData(registerForm);
        const value = formData.get(fieldName);

        let isValid = true;
        let errorMessage = '';

        // Get validation rules for this field
        const fieldRules = validator.rules[fieldName] || [];
        const fieldMessages = validator.messages[fieldName] || [];

        for (let i = 0; i < fieldRules.length; i++) {
            if (!fieldRules[i](value, formData)) {
                isValid = false;
                errorMessage = fieldMessages[i];
                break;
            }
        }

        // Update UI
        if (isValid) {
            field.classList.remove('error');
            errorElement.classList.remove('show');
        } else {
            field.classList.add('error');
            errorElement.textContent = errorMessage;
            errorElement.classList.add('show');
        }

        return isValid;
    }

    function checkNIKUnique(nik) {
        // Check if NIK is already registered
        const registeredUsers = JSON.parse(localStorage.getItem('laporPakWali_users') || '[]');
        return !registeredUsers.some(user => user.nik === nik);
    }

    function checkPhoneUnique(phone) {
        // Check if phone number is already registered
        const registeredUsers = JSON.parse(localStorage.getItem('laporPakWali_users') || '[]');
        return !registeredUsers.some(user => user.nomorWa === phone);
    }

    async function handleSubmit(e) {
        e.preventDefault();

        // Validate form
        if (!validator.validate()) {
            window.laporApp.showNotification('Mohon perbaiki data yang belum valid', 'error');
            return;
        }

        // Show loading state
        setLoadingState(true);

        try {
            // Simulate registration process
            await simulateRegistration();

            // Get form data
            const formData = new FormData(registerForm);
            const userData = {
                id: generateUserId(),
                nama: formData.get('namaLengkap').trim(),
                nik: formData.get('nikKtp'),
                nomorWa: formData.get('nomorWa'),
                tanggalDaftar: new Date().toISOString(),
                status: 'active'
            };

            // Save user data
            saveUserData(userData);

            // Show success message
            showSuccessMessage();

        } catch (error) {
            window.laporApp.showNotification('Terjadi kesalahan saat mendaftar. Silakan coba lagi.', 'error');
            console.error('Registration error:', error);
        } finally {
            setLoadingState(false);
        }
    }

    function setLoadingState(loading) {
        if (loading) {
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitLoading.classList.remove('hidden');
        } else {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitLoading.classList.add('hidden');
        }
    }

    function simulateRegistration() {
        // Simulate network delay
        return new Promise((resolve) => {
            setTimeout(resolve, 2000);
        });
    }

    function generateUserId() {
        return 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function saveUserData(userData) {
        // Get existing users
        const existingUsers = JSON.parse(localStorage.getItem('laporPakWali_users') || '[]');
        
        // Add new user
        existingUsers.push(userData);
        
        // Save updated list
        localStorage.setItem('laporPakWali_users', JSON.stringify(existingUsers));
        
        // Save current user session
        localStorage.setItem('laporPakWali_currentUser', JSON.stringify(userData));
        localStorage.setItem('laporPakWali_userData', JSON.stringify(userData));
    }

    function showSuccessMessage() {
        registerForm.style.display = 'none';
        successMessage.classList.remove('hidden');
        successMessage.classList.add('fade-in');

        // Optional: Auto redirect after 3 seconds
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 3000);
    }

    // NIK Lookup (Optional feature)
    function setupNIKLookup() {
        const nikInput = document.getElementById('nikKtp');
        let nikTimeout;

        nikInput.addEventListener('input', (e) => {
            const nik = e.target.value;
            
            // Clear previous timeout
            clearTimeout(nikTimeout);
            
            // Set new timeout for NIK lookup
            if (nik.length === 16) {
                nikTimeout = setTimeout(() => {
                    lookupNIKInfo(nik);
                }, 500);
            }
        });
    }

    function lookupNIKInfo(nik) {
        // Basic NIK info extraction (this is a simplified version)
        try {
            const provinceCode = nik.substring(0, 2);
            const cityCode = nik.substring(2, 4);
            const districtCode = nik.substring(4, 6);
            const birthDate = nik.substring(6, 12);
            
            // You can add province/city lookup here
            console.log('NIK Info:', {
                provinceCode,
                cityCode,
                districtCode,
                birthDate
            });
            
        } catch (error) {
            console.error('NIK lookup error:', error);
        }
    }

    // Initialize NIK lookup if needed
    // setupNIKLookup();

    // Phone number formatting for display
    function formatPhoneNumber(phone) {
        if (phone.length >= 10) {
            return phone.replace(/(\d{3})(\d{4})(\d{4,})/, '$1-$2-$3');
        }
        return phone;
    }

    // Add phone number preview
    const phoneInput = document.getElementById('nomorWa');
    const phonePreview = document.createElement('div');
    phonePreview.style.cssText = `
        font-size: 0.8rem;
        color: var(--gray-500);
        margin-top: 0.25rem;
        font-family: monospace;
    `;
    phoneInput.parentElement.appendChild(phonePreview);

    phoneInput.addEventListener('input', (e) => {
        const kodeNegara = document.getElementById('kodeNegara').value;
        const fullNumber = kodeNegara + e.target.value;
        phonePreview.textContent = fullNumber ? `Preview: ${fullNumber}` : '';
    });
});