// Dashboard Page JavaScript
document.addEventListener('DOMContentLoaded', () => {
    // Check authentication
    checkAuthentication();
    
    // Initialize dashboard
    initializeDashboard();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load user data
    loadUserData();
    
    // Load dashboard statistics
    loadDashboardStats();

    function checkAuthentication() {
        const currentUser = localStorage.getItem('laporPakWali_currentUser');
        const sessionExpiry = localStorage.getItem('laporPakWali_sessionExpiry');
        
        if (!currentUser || !sessionExpiry) {
            redirectToLogin();
            return;
        }
        
        const now = new Date();
        const expiry = new Date(sessionExpiry);
        
        if (now >= expiry) {
            // Session expired
            clearSession();
            redirectToLogin();
            return;
        }
    }

    function redirectToLogin() {
        window.laporApp.showNotification('Silakan login terlebih dahulu', 'error');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1000);
    }

    function clearSession() {
        localStorage.removeItem('laporPakWali_currentUser');
        localStorage.removeItem('laporPakWali_userData');
        localStorage.removeItem('laporPakWali_sessionExpiry');
    }

    function initializeDashboard() {
        // Setup profile dropdown
        setupProfileDropdown();
        
        // Setup floating action button
        setupFloatingActionButton();
        
        // Setup bottom navigation
        setupBottomNavigation();
        
        // Load recent reports
        loadRecentReports();
    }

    function setupEventListeners() {
        // New report button
        const newReportBtn = document.getElementById('newReportBtn');
        if (newReportBtn) {
            newReportBtn.addEventListener('click', () => {
                window.location.href = 'create-report.html';
            });
        }

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', handleLogout);
        }

        // Profile navigation button
        const profileNavBtn = document.getElementById('profileNavBtn');
        if (profileNavBtn) {
            profileNavBtn.addEventListener('click', (e) => {
                e.preventDefault();
                toggleProfileDropdown();
            });
        }
    }

    function setupProfileDropdown() {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', () => {
                profileMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.add('hidden');
                }
            });
        }
    }

    function toggleProfileDropdown() {
        const profileMenu = document.getElementById('profileMenu');
        if (profileMenu) {
            profileMenu.classList.toggle('hidden');
        }
    }

    function setupFloatingActionButton() {
        const fabBtn = document.getElementById('fabBtn');
        if (fabBtn) {
            fabBtn.addEventListener('click', () => {
                window.location.href = 'create-report.html';
            });

            // Hide FAB when scrolling down, show when scrolling up
            let lastScrollTop = 0;
            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scrolling down
                    fabBtn.style.transform = 'translateY(100px)';
                } else {
                    // Scrolling up
                    fabBtn.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = scrollTop;
            });
        }
    }

    function setupBottomNavigation() {
        const navItems = document.querySelectorAll('.nav-item');
        
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Remove active class from all items
                navItems.forEach(nav => nav.classList.remove('active'));
                
                // Add active class to clicked item
                item.classList.add('active');
            });
        });
    }

    function loadUserData() {
        const userData = JSON.parse(localStorage.getItem('laporPakWali_userData') || '{}');
        
        if (userData.nama) {
            // Update user name displays
            const userNameElements = document.querySelectorAll('#userName, #profileName');
            userNameElements.forEach(element => {
                element.textContent = userData.nama;
            });

            // Update NIK display
            const profileNik = document.getElementById('profileNik');
            if (profileNik && userData.nik) {
                const maskedNik = `${userData.nik.substring(0, 4)}****${userData.nik.substring(12)}`;
                profileNik.textContent = `NIK: ${maskedNik}`;
            }

            // Update welcome text
            const welcomeText = document.getElementById('welcomeText');
            if (welcomeText) {
                const currentHour = new Date().getHours();
                let greeting = 'Selamat pagi';
                
                if (currentHour >= 11 && currentHour < 15) {
                    greeting = 'Selamat siang';
                } else if (currentHour >= 15 && currentHour < 18) {
                    greeting = 'Selamat sore';
                } else if (currentHour >= 18 || currentHour < 5) {
                    greeting = 'Selamat malam';
                }
                
                welcomeText.innerHTML = `${greeting}, <span id="userName">${userData.nama}</span>`;
            }
        }
    }

    function loadDashboardStats() {
        // Get user reports from localStorage
        const userReports = JSON.parse(localStorage.getItem('laporPakWali_userReports') || '[]');
        const currentUser = JSON.parse(localStorage.getItem('laporPakWali_currentUser') || '{}');
        
        // Filter reports for current user
        const myReports = userReports.filter(report => report.userId === currentUser.id);
        
        // Calculate statistics
        const totalReports = myReports.length;
        const pendingReports = myReports.filter(report => 
            ['baru', 'diproses'].includes(report.status)
        ).length;
        const resolvedReports = myReports.filter(report => 
            report.status === 'selesai'
        ).length;

        // Update dashboard stats
        updateStat('totalReports', totalReports);
        updateStat('pendingReports', pendingReports);
        updateStat('resolvedReports', resolvedReports);

        // Animate counters
        animateCounters();
    }

    function updateStat(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
        }
    }

    function animateCounters() {
        const counters = document.querySelectorAll('#totalReports, #pendingReports, #resolvedReports');
        
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            let current = 0;
            const increment = target / 20;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.ceil(current);
                    setTimeout(updateCounter, 50);
                } else {
                    counter.textContent = target;
                }
            };
            
            updateCounter();
        });
    }

    function loadRecentReports() {
        const recentReportsContainer = document.getElementById('recentReports');
        if (!recentReportsContainer) return;

        const userReports = JSON.parse(localStorage.getItem('laporPakWali_userReports') || '[]');
        const currentUser = JSON.parse(localStorage.getItem('laporPakWali_currentUser') || '{}');
        
        // Get user's recent reports (last 3)
        const myReports = userReports
            .filter(report => report.userId === currentUser.id)
            .sort((a, b) => new Date(b.tanggalLapor) - new Date(a.tanggalLapor))
            .slice(0, 3);

        if (myReports.length === 0) {
            // Show empty state (already in HTML)
            return;
        }

        // Generate recent reports HTML
        const reportsHTML = myReports.map(report => `
            <div class="card" style="margin-bottom: 1rem; cursor: pointer;" onclick="viewReport('${report.id}')">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                        <h4 class="card-title" style="margin: 0; font-size: 1rem;">${report.judul}</h4>
                        <span class="status-badge status-${report.status}">${getStatusLabel(report.status)}</span>
                    </div>
                    <p class="card-text" style="margin: 0.5rem 0; font-size: 0.9rem; color: var(--gray-600);">
                        ${report.deskripsi.length > 100 ? report.deskripsi.substring(0, 100) + '...' : report.deskripsi}
                    </p>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--gray-500);">
                        <span>
                            <i class="fas fa-map-marker-alt"></i>
                            ${report.lokasi}
                        </span>
                        <span>
                            <i class="fas fa-clock"></i>
                            ${formatDate(report.tanggalLapor)}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');

        recentReportsContainer.innerHTML = reportsHTML;

        // Add status badge styles
        addStatusBadgeStyles();
    }

    function getStatusLabel(status) {
        const statusLabels = {
            'baru': 'Baru',
            'diproses': 'Diproses',
            'selesai': 'Selesai',
            'ditolak': 'Ditolak'
        };
        return statusLabels[status] || status;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInHours = Math.abs(now - date) / 36e5;

        if (diffInHours < 24) {
            return `${Math.floor(diffInHours)} jam lalu`;
        } else if (diffInHours < 168) { // 7 days
            return `${Math.floor(diffInHours / 24)} hari lalu`;
        } else {
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }
    }

    function addStatusBadgeStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .status-badge {
                padding: 0.25rem 0.75rem;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .status-baru {
                background: var(--light-blue);
                color: var(--primary-blue);
            }
            
            .status-diproses {
                background: rgba(251, 191, 36, 0.1);
                color: var(--warning);
            }
            
            .status-selesai {
                background: rgba(16, 185, 129, 0.1);
                color: var(--success);
            }
            
            .status-ditolak {
                background: rgba(239, 68, 68, 0.1);
                color: var(--error);
            }
        `;
        document.head.appendChild(style);
    }

    function handleLogout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            // Clear all session data
            clearSession();
            
            // Show logout message
            window.laporApp.showNotification('Anda telah keluar. Terima kasih!', 'success');
            
            // Redirect to login
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1000);
        }
    }

    // Global function to view report details
    window.viewReport = function(reportId) {
        // For now, redirect to reports page with ID
        window.location.href = `my-reports.html?id=${reportId}`;
    };

    // Update notification badge
    function updateNotificationBadge() {
        const notifBadge = document.getElementById('notifBadge');
        if (notifBadge) {
            // Get unread notifications count (mock data for now)
            const unreadCount = getUnreadNotificationsCount();
            
            if (unreadCount > 0) {
                notifBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                notifBadge.classList.remove('hidden');
            } else {
                notifBadge.classList.add('hidden');
            }
        }
    }

    function getUnreadNotificationsCount() {
        // Mock function - replace with actual notification logic
        const notifications = JSON.parse(localStorage.getItem('laporPakWali_notifications') || '[]');
        const currentUser = JSON.parse(localStorage.getItem('laporPakWali_currentUser') || '{}');
        
        return notifications.filter(notif => 
            notif.userId === currentUser.id && !notif.read
        ).length;
    }

    // Load notifications count
    updateNotificationBadge();

    // Auto-refresh dashboard data every 30 seconds
    setInterval(() => {
        loadDashboardStats();
        updateNotificationBadge();
    }, 30000);

    // Handle page visibility change
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            // Page is now visible, refresh data
            loadDashboardStats();
            updateNotificationBadge();
        }
    });

    // Add pull-to-refresh functionality for mobile
    let startY = 0;
    let pullDistance = 0;
    const refreshThreshold = 60;

    document.addEventListener('touchstart', (e) => {
        if (window.scrollY === 0) {
            startY = e.touches[0].pageY;
        }
    });

    document.addEventListener('touchmove', (e) => {
        if (window.scrollY === 0 && startY > 0) {
            pullDistance = e.touches[0].pageY - startY;
            
            if (pullDistance > 0 && pullDistance < refreshThreshold * 2) {
                e.preventDefault();
                
                // Visual feedback for pull-to-refresh
                const refreshIndicator = document.getElementById('refreshIndicator');
                if (!refreshIndicator) {
                    const indicator = document.createElement('div');
                    indicator.id = 'refreshIndicator';
                    indicator.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 50%;
                        transform: translateX(-50%);
                        background: var(--primary-blue);
                        color: white;
                        padding: 0.5rem 1rem;
                        border-radius: 0 0 1rem 1rem;
                        font-size: 0.9rem;
                        z-index: 1000;
                        transition: opacity 0.3s ease;
                    `;
                    indicator.innerHTML = '<i class="fas fa-arrow-down"></i> Tarik untuk refresh';
                    document.body.appendChild(indicator);
                }
                
                const opacity = Math.min(pullDistance / refreshThreshold, 1);
                refreshIndicator.style.opacity = opacity;
                
                if (pullDistance >= refreshThreshold) {
                    refreshIndicator.innerHTML = '<i class="fas fa-sync-alt"></i> Lepas untuk refresh';
                }
            }
        }
    });

    document.addEventListener('touchend', () => {
        const refreshIndicator = document.getElementById('refreshIndicator');
        
        if (pullDistance >= refreshThreshold) {
            // Trigger refresh
            if (refreshIndicator) {
                refreshIndicator.innerHTML = '<span class="loading"></span> Memperbarui...';
            }
            
            setTimeout(() => {
                loadDashboardStats();
                loadRecentReports();
                updateNotificationBadge();
                
                if (refreshIndicator) {
                    refreshIndicator.remove();
                }
                
                window.laporApp.showNotification('Dashboard diperbarui', 'success');
            }, 1000);
        } else if (refreshIndicator) {
            refreshIndicator.remove();
        }
        
        startY = 0;
        pullDistance = 0;
    });
});