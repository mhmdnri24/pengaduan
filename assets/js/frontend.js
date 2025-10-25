/**
 * Frontend JavaScript Functions
 * Dashboard Masyarakat
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Initialize page-specific functions
    initializeCommonFeatures();
    
    // Initialize page-specific features based on current page
    const currentPage = document.body.getAttribute('data-page');
    if (currentPage) {
        switch(currentPage) {
            case 'harga-komoditas':
                initializeHargaKomoditas();
                break;
            case 'pelaporan':
                initializePelaporan();
                break;
            case 'kepengurusan':
                initializeKepengurusan();
                break;
            case 'umkm':
                initializeUMKM();
                break;
        }
    }
});

/**
 * Initialize common features for all pages
 */
function initializeCommonFeatures() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add animation classes on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);
    
    // Observe all cards
    document.querySelectorAll('.card-hover').forEach(card => {
        observer.observe(card);
    });
}

/**
 * Loading state management
 */
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="loading-spinner mx-auto"></div>';
    }
}

function hideLoading(element, originalContent) {
    if (element) {
        element.innerHTML = originalContent;
    }
}

/**
 * Toast notification system
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full`;
    
    switch(type) {
        case 'success':
            toast.classList.add('bg-green-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        default:
            toast.classList.add('bg-blue-500');
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Format date
 */
function formatDate(dateString) {
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(dateString));
}

/**
 * Initialize Harga Komoditas page specific features
 */
function initializeHargaKomoditas() {
    console.log('Initializing Harga Komoditas features...');
    
    // Initialize chart if container exists
    const chartContainer = document.getElementById('priceChart');
    if (chartContainer) {
        initializePriceChart();
    }
}

/**
 * Initialize Pelaporan page specific features
 */
function initializePelaporan() {
    console.log('Initializing Pelaporan features...');
    
    // Add any pelaporan-specific initialization here
}

/**
 * Initialize Kepengurusan page specific features
 */
function initializeKepengurusan() {
    console.log('Initializing Kepengurusan features...');
    
    // Add any kepengurusan-specific initialization here
}

/**
 * Initialize UMKM page specific features
 */
function initializeUMKM() {
    console.log('Initializing UMKM features...');
    
    // Add any UMKM-specific initialization here
}

/**
 * Initialize price chart
 */
function initializePriceChart() {
    // This will be called by Alpine.js component
    // Chart initialization is handled in the Alpine component
}

/**
 * Utility function to make AJAX requests
 */
async function makeAjaxRequest(url, data = {}, method = 'POST') {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        };
        
        if (method === 'POST' && Object.keys(data).length > 0) {
            options.body = new URLSearchParams(data);
        }
        
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('AJAX request failed:', error);
        showToast('Terjadi kesalahan saat memuat data', 'error');
        throw error;
    }
}

/**
 * Debounce function for search inputs
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format number with thousand separators
 */
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

/**
 * Get relative time string
 */
function getRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Baru saja';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} menit yang lalu`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} jam yang lalu`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} hari yang lalu`;
    }
}

/**
 * Copy text to clipboard
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Teks berhasil disalin', 'success');
    } catch (err) {
        console.error('Failed to copy text: ', err);
        showToast('Gagal menyalin teks', 'error');
    }
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate phone number format (Indonesian)
 */
function isValidPhone(phone) {
    const phoneRegex = /^(\+62|62|0)[0-9]{9,13}$/;
    return phoneRegex.test(phone.replace(/\s+/g, ''));
}

// Export functions for global use
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.makeAjaxRequest = makeAjaxRequest;
window.debounce = debounce;
window.formatNumber = formatNumber;
window.getRelativeTime = getRelativeTime;
window.copyToClipboard = copyToClipboard;
window.isValidEmail = isValidEmail;
window.isValidPhone = isValidPhone;
