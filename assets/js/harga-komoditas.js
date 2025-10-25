/**
 * Monitoring Retribusi Page JavaScript
 * Dashboard Masyarakat
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Set base URL for AJAX requests
    window.baseUrl = document.querySelector('meta[name="base_url"]')?.getAttribute('content') ||
                     window.location.origin + '/';
});

// Wait for Alpine.js to initialize
document.addEventListener('alpine:init', () => {
    Alpine.data('monitoringRetribusi', () => ({
        // Data properties
        filters: {
            date_range_type: 'month',
            start_date: new Date().toISOString().split('T')[0], // Default hari ini
            end_date: new Date().toISOString().split('T')[0],   // Default hari ini
            pasar_id: window.selectedPasar || 'all'
        },
        
        retribusiData: {
            target_revenue: 0,
            realized_revenue: 0,
            percentage: 0
        },
        
        loading: {
            retribusi: false
        },
        
        // Initialize component
        init() {
            console.log('Initializing Monitoring Retribusi component...');
            this.loadData(); // Load retribusi data

            // Watch for filter changes
            this.$watch('filters.date_range_type', () => {
                this.updateDateRange();
                this.loadData();
            });

            this.$watch('filters.pasar_id', () => {
                this.loadData();
            });
        },

        // Handle date range change
        handleDateRangeChange() {
            this.updateDateRange();
            this.loadData();
        },

        // Update date range based on selected type
        updateDateRange() {
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];

            switch (this.filters.date_range_type) {
                case 'today':
                    this.filters.start_date = todayStr;
                    this.filters.end_date = todayStr;
                    break;
                    
                case 'week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(today.getDate() - 6);
                    this.filters.start_date = weekAgo.toISOString().split('T')[0];
                    this.filters.end_date = todayStr;
                    break;
                    
                case 'month':
                    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                    this.filters.start_date = monthStart.toISOString().split('T')[0];
                    this.filters.end_date = todayStr;
                    break;
                    
                case 'custom':
                    // Keep current dates for custom range
                    break;
            }
        },

        // Load retribusi data
        async loadData() {
            await this.loadRetribusiData();
        },

        // Load retribusi data via AJAX
        async loadRetribusiData() {
            this.loading.retribusi = true;
            
            try {
                const requestData = {
                    dateRange: this.filters.date_range_type,
                    startDate: this.filters.start_date,
                    endDate: this.filters.end_date,
                    pasarSelect: [this.filters.pasar_id]
                };

                const response = await fetch(window.baseUrl + 'frontend/ajax_revenue_cards', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(requestData)
                });

                const result = await response.json();

                if (result.status) {
                    this.retribusiData = {
                        target_revenue: result.data.target_revenue,
                        realized_revenue: result.data.realized_revenue,
                        percentage: result.data.target_revenue > 0
                            ? (result.data.realized_revenue / result.data.target_revenue * 100).toFixed(1)
                            : 0
                    };
                    this.updateRetribusiCards();
                } else {
                    console.error('Gagal memuat data retribusi:', result.message);
                }
            } catch (error) {
                console.error('Error loading retribusi data:', error);
            } finally {
                this.loading.retribusi = false;
            }
        },

        // Update retribusi cards with data
        updateRetribusiCards() {
            // Update target retribusi
            const targetElement = document.getElementById('targetRevenue');
            if (targetElement) {
                targetElement.textContent = this.formatCurrency(this.retribusiData.target_revenue);
            }

            // Update realized retribusi
            const realizedElement = document.getElementById('realizedRevenue');
            if (realizedElement) {
                realizedElement.textContent = this.formatCurrency(this.retribusiData.realized_revenue);
            }

            // Update achievement rate
            const achievementElement = document.getElementById('achievementRate');
            if (achievementElement) {
                achievementElement.textContent = `${this.retribusiData.percentage}% dari target`;
            }

            // Update percentage achievement
            const percentageElement = document.getElementById('percentageAchievement');
            if (percentageElement) {
                percentageElement.textContent = `${this.retribusiData.percentage}%`;
            }

            // Update achievement status
            const statusElement = document.getElementById('achievementStatus');
            if (statusElement) {
                const percentage = parseFloat(this.retribusiData.percentage);
                if (percentage >= 100) {
                    statusElement.textContent = 'Target tercapai';
                } else if (percentage >= 75) {
                    statusElement.textContent = 'Hampir tercapai';
                } else if (percentage >= 50) {
                    statusElement.textContent = 'Sedang dalam proses';
                } else if (percentage > 0) {
                    statusElement.textContent = 'Masih rendah';
                } else {
                    statusElement.textContent = 'Belum ada capaian';
                }
            }
        },
        
        // Format currency for display
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },
        
        // Format date for display
        formatDate(dateString) {
            return new Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(dateString));
        },
        
        // Check if custom date range is selected
        get isCustomRange() {
            return this.filters.date_range_type === 'custom';
        },
        
        // Get filter description
        get filterDescription() {
            switch (this.filters.date_range_type) {
                case 'today':
                    return 'Data hari ini';
                case 'week':
                    return 'Data 7 hari terakhir';
                case 'month':
                    return 'Data bulan ini';
                case 'custom':
                    return `Data ${this.formatDate(this.filters.start_date)} - ${this.formatDate(this.filters.end_date)}`;
                default:
                    return 'Data harga komoditas';
            }
        }
    }));
});
