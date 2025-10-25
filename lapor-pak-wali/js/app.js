// Main App JavaScript
class LaporPakWaliApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupPWA();
        this.loadUserData();
        this.setupAnimations();
    }

    setupEventListeners() {
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.querySelector('.nav-menu');
        
        if (mobileMenuBtn && navMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('show');
            });
        }
    }

    setupPWA() {
        let deferredPrompt;
        const installPrompt = document.getElementById('installPrompt');
        const installBtn = document.getElementById('installBtn');
        const dismissBtn = document.getElementById('dismissBtn');

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            
            // Show the install prompt
            if (installPrompt) {
                installPrompt.classList.add('show');
            }
        });

        // Handle install button click
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    
                    if (outcome === 'accepted') {
                        console.log('User accepted the PWA install prompt');
                    }
                    
                    deferredPrompt = null;
                    installPrompt.classList.remove('show');
                }
            });
        }

        // Handle dismiss button click
        if (dismissBtn) {
            dismissBtn.addEventListener('click', () => {
                installPrompt.classList.remove('show');
                // Remember user dismissed the prompt
                localStorage.setItem('pwaPromptDismissed', 'true');
            });
        }

        // Check if user previously dismissed
        if (localStorage.getItem('pwaPromptDismissed') === 'true') {
            if (installPrompt) {
                installPrompt.style.display = 'none';
            }
        }

        // Listen for successful installation
        window.addEventListener('appinstalled', () => {
            console.log('PWA was installed');
            if (installPrompt) {
                installPrompt.classList.remove('show');
            }
        });
    }

    loadUserData() {
        // Load user data from localStorage if available
        const userData = localStorage.getItem('laporPakWali_userData');
        if (userData) {
            const user = JSON.parse(userData);
            this.updateUserInterface(user);
        }
    }

    updateUserInterface(user) {
        // Update welcome text
        const userNameElements = document.querySelectorAll('#userName, #profileName');
        userNameElements.forEach(element => {
            if (element) {
                element.textContent = user.nama || 'Pengguna';
            }
        });

        // Update NIK display
        const profileNik = document.getElementById('profileNik');
        if (profileNik && user.nik) {
            profileNik.textContent = `NIK: ${user.nik.substring(0, 4)}****${user.nik.substring(12)}`;
        }
    }

    setupAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements that should fade in
        document.querySelectorAll('.card, .dashboard-card').forEach(el => {
            observer.observe(el);
        });
    }

    // Utility methods
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success)' : type === 'error' ? 'var(--error)' : 'var(--primary-blue)'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
            max-width: 300px;
        `;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; margin-left: auto; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    formatPhoneNumber(phone) {
        // Format phone number for display
        return phone.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
    }

    validateNIK(nik) {
        // Basic NIK validation
        if (!/^\d{16}$/.test(nik)) {
            return false;
        }
        
        // Additional validation can be added here
        return true;
    }

    validatePhoneNumber(phone) {
        // Validate Indonesian phone number
        return /^[0-9]{10,13}$/.test(phone);
    }
}

// Form Validation Utilities
class FormValidator {
    constructor(form) {
        this.form = form;
        this.rules = {};
        this.messages = {};
    }

    addRule(fieldName, rule, message) {
        if (!this.rules[fieldName]) {
            this.rules[fieldName] = [];
            this.messages[fieldName] = [];
        }
        this.rules[fieldName].push(rule);
        this.messages[fieldName].push(message);
        return this;
    }

    validate() {
        let isValid = true;
        const formData = new FormData(this.form);

        Object.keys(this.rules).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            const value = formData.get(fieldName);
            const errorElement = this.form.querySelector(`#${fieldName}Error`);
            
            let fieldValid = true;
            let errorMessage = '';

            this.rules[fieldName].forEach((rule, index) => {
                if (fieldValid && !rule(value, formData)) {
                    fieldValid = false;
                    errorMessage = this.messages[fieldName][index];
                }
            });

            if (field && errorElement) {
                if (fieldValid) {
                    field.classList.remove('error');
                    errorElement.classList.remove('show');
                } else {
                    field.classList.add('error');
                    errorElement.textContent = errorMessage;
                    errorElement.classList.add('show');
                    isValid = false;
                }
            }
        });

        return isValid;
    }
}

// Common validation rules
const ValidationRules = {
    required: (value) => value && value.trim() !== '',
    
    minLength: (min) => (value) => value && value.length >= min,
    
    maxLength: (max) => (value) => !value || value.length <= max,
    
    pattern: (regex) => (value) => !value || regex.test(value),
    
    nik: (value) => /^\d{16}$/.test(value),
    
    phone: (value) => /^[0-9]{10,13}$/.test(value),
    
    checked: (value) => value === 'on' || value === true
};

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.laporApp = new LaporPakWaliApp();
});

// Add global styles for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .notification {
        animation: slideInRight 0.3s ease-out;
    }

    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);