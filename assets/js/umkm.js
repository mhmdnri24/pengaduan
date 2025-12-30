/**
 * UMKM Page JavaScript
 * Handles UMKM page animations and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
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
    document.querySelectorAll('.bg-white.rounded-lg.shadow-sm').forEach(card => {
        observer.observe(card);
    });
    
    // Add hover effects for cards
    document.querySelectorAll('.bg-white.rounded-lg.shadow-sm').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Auto-submit form on filter change
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select, input[type="text"]');
        
        filterInputs.forEach(input => {
            if (input.type === 'text') {
                // For text inputs, use debounced search
                let timeout;
                input.addEventListener('input', function() {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        if (this.value.length >= 3 || this.value.length === 0) {
                            filterForm.submit();
                        }
                    }, 500);
                });
            } else {
                // For selects, submit immediately
                input.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
    }
});
