/**
 * Landing Page JavaScript - Smart ASN
 * Combined functionality from landing_page with mobile menu fixes
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize all components
    initLoadingScreen();
    initMobileMenu();
    initSmoothScrolling();
    initCounterAnimation();
    initAOS();
    
    /**
     * Loading Screen
     */
    function initLoadingScreen() {
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            // Hide loading screen after page load
            window.addEventListener('load', function() {
                setTimeout(function() {
                    loadingScreen.classList.add('hidden');
                    // Remove from DOM after animation
                    setTimeout(function() {
                        loadingScreen.remove();
                    }, 500);
                }, 1000);
            });
        }
    }
    
    /**
     * Mobile Menu Functionality
     */
    function initMobileMenu() {
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenuLinks = document.querySelectorAll('.mobile-menu-links a');
        
        if (!mobileToggle || !mobileMenu || !mobileMenuOverlay) {
            console.warn('Mobile menu elements not found');
            return;
        }
        
        // Open mobile menu
        function openMobileMenu() {
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.documentElement.classList.add('mobile-menu-open');
            document.body.style.overflow = 'hidden';
        }

        // Close mobile menu
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.documentElement.classList.remove('mobile-menu-open');
            document.body.style.overflow = '';
        }
        
        // Event listeners
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openMobileMenu();
        });
        
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileMenu();
            });
        }
        
        mobileMenuOverlay.addEventListener('click', function(e) {
            e.preventDefault();
            closeMobileMenu();
        });
        
        // Close menu when clicking on links
        mobileMenuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Prevent menu close when clicking inside menu content
        mobileMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    /**
     * Smooth Scrolling for anchor links
     */
    function initSmoothScrolling() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip if it's just "#"
                if (href === '#') {
                    e.preventDefault();
                    return;
                }
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    
                    const headerHeight = document.querySelector('.main-header')?.offsetHeight || 80;
                    const targetPosition = target.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
    
    /**
     * Counter Animation
     */
    function initCounterAnimation() {
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 20);
        }
        
        // Initialize counters when in view
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px 0px -100px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target')) || 0;
                    
                    if (target > 0 && !counter.classList.contains('animated')) {
                        counter.classList.add('animated');
                        animateCounter(counter, target);
                    }
                }
            });
        }, observerOptions);
        
        // Observe all counter elements
        const counters = document.querySelectorAll('.stat-value[data-target]');
        counters.forEach(counter => observer.observe(counter));
    }
    
    /**
     * Initialize AOS (Animate On Scroll) if available
     */
    function initAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                offset: 100
            });
        }
    }
    
    /**
     * Header scroll effect
     */
    function initHeaderScrollEffect() {
        const header = document.querySelector('.main-header');
        if (!header) return;
        
        let lastScrollY = window.scrollY;
        
        window.addEventListener('scroll', function() {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScrollY = currentScrollY;
        });
    }
    
    // Initialize header scroll effect
    initHeaderScrollEffect();
    
    /**
     * Form handling (if needed)
     */
    function initFormHandling() {
        const forms = document.querySelectorAll('form[data-ajax]');
        
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const action = form.getAttribute('action');
                const method = form.getAttribute('method') || 'POST';
                
                // Add loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                }
                
                fetch(action, {
                    method: method,
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Handle success
                        showNotification('Berhasil!', data.message || 'Data berhasil disimpan', 'success');
                        form.reset();
                    } else {
                        // Handle error
                        showNotification('Error!', data.message || 'Terjadi kesalahan', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error!', 'Terjadi kesalahan pada server', 'error');
                })
                .finally(() => {
                    // Reset button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                    }
                });
            });
        });
    }
    
    /**
     * Simple notification system
     */
    function showNotification(title, message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <strong>${title}</strong>
                <p>${message}</p>
            </div>
            <button class="notification-close">&times;</button>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }
    
    // Initialize form handling
    initFormHandling();
    
    // Expose useful functions globally
    window.SmartASN = {
        showNotification: showNotification,
        closeMobileMenu: function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            if (mobileMenu && mobileMenuOverlay) {
                mobileMenu.classList.remove('active');
                mobileMenuOverlay.classList.remove('active');
                document.documentElement.classList.remove('mobile-menu-open');
                document.body.style.overflow = '';
            }
        }
    };
});
