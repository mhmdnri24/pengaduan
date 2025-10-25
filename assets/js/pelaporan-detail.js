/**
 * Pelaporan Detail Page JavaScript
 * Handles pelaporan detail page functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Print functionality
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Share functionality
    const shareBtn = document.getElementById('shareBtn');
    if (shareBtn && navigator.share) {
        shareBtn.addEventListener('click', async function() {
            try {
                await navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } catch (err) {
                console.log('Error sharing:', err);
                // Fallback to copy URL
                copyToClipboard(window.location.href);
            }
        });
    } else if (shareBtn) {
        // Fallback for browsers without Web Share API
        shareBtn.addEventListener('click', function() {
            copyToClipboard(window.location.href);
        });
    }
    
    // Copy to clipboard function
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showToast('Link berhasil disalin!', 'success');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Link berhasil disalin!', 'success');
        }
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Animate in
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.transition = 'transform 0.3s ease-out';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
    
    // Image modal functionality
    const images = document.querySelectorAll('.report-image');
    images.forEach(img => {
        img.addEventListener('click', function() {
            openImageModal(this.src, this.alt);
        });
    });
    
    function openImageModal(src, alt) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="relative max-w-4xl max-h-full p-4">
                <img src="${src}" alt="${alt}" class="max-w-full max-h-full object-contain">
                <button class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(modal);
        lucide.createIcons();
        
        // Close modal on click
        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.closest('button')) {
                document.body.removeChild(modal);
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(modal);
            }
        });
    }
});
