// Global Page Loader Functionality
class PageLoader {
    constructor() {
        this.loaderElement = null;
        this.isLoading = false;
        this.minLoadTime = 1000; // Minimum loading time in ms
        this.init();
    }

    init() {
        // Create loader HTML if it doesn't exist
        this.createLoader();
        
        // Show loader on page load
        this.showLoader();
        
        // Hide loader when page is fully loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.hideLoader();
                this.animatePageContent();
            }, this.minLoadTime);
        });

        // Show loader on page navigation
        this.setupNavigationLoader();
    }

    createLoader() {
        // Check if loader already exists
        if (document.getElementById('pageLoader')) {
            this.loaderElement = document.getElementById('pageLoader');
            return;
        }

        // Create loader HTML
        const loaderHTML = `
            <div id="pageLoader" class="page-loader">
                <div class="loader-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <div class="loader-text">Lapor Pak Wali</div>
                <div class="loader-subtext">Memuat halaman<span class="loading-dots"></span></div>
                <div class="loader-spinner"></div>
                <div class="loader-progress">
                    <div class="loader-progress-bar"></div>
                </div>
            </div>
        `;

        // Insert loader at the beginning of body
        document.body.insertAdjacentHTML('afterbegin', loaderHTML);
        this.loaderElement = document.getElementById('pageLoader');
    }

    showLoader() {
        if (this.loaderElement) {
            this.isLoading = true;
            this.loaderElement.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    hideLoader() {
        if (this.loaderElement && this.isLoading) {
            this.isLoading = false;
            this.loaderElement.classList.add('hidden');
            document.body.style.overflow = '';
            
            // Remove loader element after transition
            setTimeout(() => {
                if (this.loaderElement && this.loaderElement.parentNode) {
                    this.loaderElement.remove();
                }
            }, 500);
        }
    }

    animatePageContent() {
        // Add transition classes to page elements
        const pageElements = document.querySelectorAll('body > *:not(#pageLoader)');
        pageElements.forEach((element, index) => {
            element.classList.add('page-transition');
            setTimeout(() => {
                element.classList.add('loaded');
            }, index * 100);
        });
    }

    setupNavigationLoader() {
        // Add transition effect to all internal links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a, button[onclick*="location"], button[onclick*="href"]');
            
            if (link && this.isInternalLink(link)) {
                e.preventDefault();
                
                this.showNavigationLoader(() => {
                    // Navigate after showing loader
                    if (link.tagName === 'A') {
                        window.location.href = link.href;
                    } else if (link.onclick) {
                        link.onclick();
                    }
                });
            }
        });
    }

    isInternalLink(element) {
        if (element.tagName === 'A') {
            const href = element.getAttribute('href');
            return href && 
                   !href.startsWith('http') && 
                   !href.startsWith('tel:') && 
                   !href.startsWith('mailto:') &&
                   !href.startsWith('#') &&
                   href !== 'javascript:void(0)';
        }
        
        if (element.tagName === 'BUTTON') {
            const onclick = element.getAttribute('onclick');
            return onclick && (
                onclick.includes('location.href') || 
                onclick.includes('window.location') ||
                onclick.includes('.html')
            );
        }
        
        return false;
    }

    showNavigationLoader(callback) {
        // Create temporary navigation loader
        const navLoaderHTML = `
            <div id="navLoader" class="page-loader">
                <div class="loader-logo">
                    <i class="fas fa-landmark"></i>
                </div>
                <div class="loader-text">Mengarahkan</div>
                <div class="loader-subtext">Mohon tunggu sebentar<span class="loading-dots"></span></div>
                <div class="loader-spinner"></div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', navLoaderHTML);
        const navLoader = document.getElementById('navLoader');
        document.body.style.overflow = 'hidden';

        // Execute callback after short delay
        setTimeout(() => {
            if (callback) callback();
        }, 300);
    }

    // Public method to show loader programmatically
    static show() {
        const loader = new PageLoader();
        loader.showLoader();
        return loader;
    }

    // Public method to hide loader programmatically
    static hide() {
        const existingLoader = document.getElementById('pageLoader');
        if (existingLoader) {
            existingLoader.classList.add('hidden');
            document.body.style.overflow = '';
            setTimeout(() => {
                if (existingLoader.parentNode) {
                    existingLoader.remove();
                }
            }, 500);
        }
    }
}

// Auto-initialize page loader when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new PageLoader();
});

// Global utility functions
window.showPageLoader = () => PageLoader.show();
window.hidePageLoader = () => PageLoader.hide();

// Handle browser back/forward buttons
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        // Page was loaded from cache
        PageLoader.hide();
    }
});

// Handle page unload
window.addEventListener('beforeunload', () => {
    // Show a quick loader for page transitions
    const quickLoader = document.createElement('div');
    quickLoader.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); 
                    display: flex; align-items: center; justify-content: center; 
                    z-index: 10000;">
            <div style="color: white; font-size: 18px; font-weight: 600;">
                <i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i>
                Memuat...
            </div>
        </div>
    `;
    document.body.appendChild(quickLoader);
});