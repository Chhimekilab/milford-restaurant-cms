/**
 * Milford India Spice - Quick Integration Script
 * 
 * Add this script to your existing website to integrate with the admin CMS
 * Just include this file in your HTML: <script src="quick-integration.js"></script>
 */

(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        // Update these selectors to match your website's HTML structure
        menuContainer: '#menu-container, .menu-items, #menu-grid, .menu-list',
        announcementContainer: '#announcements, .announcements, #news',
        hoursContainer: '#hours, .hours, #restaurant-hours',
        socialLinksContainer: '#social-links, .social-media, #social',
        orderingLinksContainer: '#ordering-links, .delivery-links, #delivery',
        
        // Default data fallback
        defaultImagePath: '/images/default-dish.jpg',
        
        // Auto-update interval (in milliseconds)
        updateInterval: 30000 // 30 seconds
    };

    // Main integration class
    class MilfordCMSIntegration {
        constructor() {
            this.data = null;
            this.init();
        }

        async init() {
            try {
                await this.loadData();
                this.renderAll();
                this.setupAutoUpdate();
                this.addAdminLink();
                console.log('✅ Milford CMS Integration loaded successfully');
            } catch (error) {
                console.warn('⚠️ CMS Integration failed, using fallback data:', error);
                this.loadFallbackData();
                this.renderAll();
            }
        }

        async loadData() {
            // Try localStorage first (from admin panel)
            const localData = localStorage.getItem('restaurantData');
            if (localData) {
                this.data = JSON.parse(localData);
                return;
            }

            // Try loading from JSON file
            try {
                const response = await fetch('/data/restaurant-data.json');
                if (response.ok) {
                    this.data = await response.json();
                    return;
                }
            } catch (e) {
                console.log('No static JSON file found');
            }

            // Use default data if nothing else works
            throw new Error('No data source available');
        }

        loadFallbackData() {
            // Minimal fallback data
            this.data = {
                info: {
                    name: 'Milford India Spice',
                    tagline: 'Authentic Indian Cuisine',
                    phone: '(508) 478-5800',
                    hours: {
                        monday: '11:00 AM - 9:00 PM',
                        tuesday: '11:00 AM - 9:00 PM',
                        wednesday: '11:00 AM - 9:00 PM',
                        thursday: '11:00 AM - 9:00 PM',
                        friday: '11:00 AM - 11:00 PM',
                        saturday: '11:00 AM - 10:00 PM',
                        sunday: '12:00 PM - 9:00 PM'
                    }
                },
                menuItems: [],
                announcements: [],
                orderingLinks: {}
            };
        }

        renderAll() {
            this.renderMenu();
            this.renderAnnouncements();
            this.renderHours();
            this.renderSocialLinks();
            this.renderOrderingLinks();
        }

        renderMenu() {
            const containers = document.querySelectorAll(CONFIG.menuContainer);
            if (!containers.length || !this.data.menuItems) return;

            const menuHTML = this.data.menuItems
                .filter(item => item.available !== false) // Show available items by default
                .map(item => this.renderMenuItem(item))
                .join('');

            containers.forEach(container => {
                container.innerHTML = menuHTML;
            });

            // Add category filtering if categories exist
            this.setupCategoryFiltering();
        }

        renderMenuItem(item) {
            const stockStatus = this.getStockStatusHTML(item);
            const badges = this.getItemBadges(item);
            
            return `
                <div class="menu-item ${!item.available ? 'cms-unavailable' : ''}" data-category="${item.category}">
                    ${item.image ? `<div class="item-image">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='${CONFIG.defaultImagePath}'">
                    </div>` : ''}
                    <div class="item-content">
                        <h3 class="item-name">${item.name} ${badges}</h3>
                        <p class="item-description">${item.description}</p>
                        <div class="item-footer">
                            <span class="item-price">$${item.price.toFixed(2)}</span>
                            ${item.preparationTime ? `<span class="prep-time">⏱️ ${item.preparationTime}</span>` : ''}
                        </div>
                        ${stockStatus}
                    </div>
                </div>
            `;
        }

        getStockStatusHTML(item) {
            if (item.available !== false) return '';

            const statusClass = `cms-status-${item.availabilityStatus || 'unavailable'}`;
            const statusText = this.getStatusText(item.availabilityStatus);
            
            return `
                <div class="cms-stock-status ${statusClass}">
                    <span class="cms-status-badge">${statusText}</span>
                    ${item.unavailableReason ? `<p class="cms-reason">${item.unavailableReason}</p>` : ''}
                    ${item.estimatedBackDate ? `<p class="cms-return-date">Expected: ${this.formatDate(item.estimatedBackDate)}</p>` : ''}
                </div>
            `;
        }

        getItemBadges(item) {
            let badges = '';
            if (item.isSpicy) badges += '<span class="cms-badge cms-spicy">🌶️</span>';
            if (item.isVegetarian) badges += '<span class="cms-badge cms-vegetarian">🌱</span>';
            return badges;
        }

        getStatusText(status) {
            const statusMap = {
                'out-of-stock': 'Out of Stock',
                'seasonal': 'Seasonal',
                'temporarily-unavailable': 'Temporarily Unavailable'
            };
            return statusMap[status] || 'Unavailable';
        }

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        renderAnnouncements() {
            const containers = document.querySelectorAll(CONFIG.announcementContainer);
            if (!containers.length || !this.data.announcements) return;

            const activeAnnouncements = this.data.announcements.filter(ann => ann.active);
            if (!activeAnnouncements.length) return;

            const announcementsHTML = activeAnnouncements
                .map(ann => `
                    <div class="cms-announcement cms-priority-${ann.priority}">
                        <h4>${ann.title}</h4>
                        <p>${ann.content}</p>
                        ${ann.expiryDate ? `<small>Valid until: ${this.formatDate(ann.expiryDate)}</small>` : ''}
                    </div>
                `)
                .join('');

            containers.forEach(container => {
                container.innerHTML = announcementsHTML;
            });
        }

        renderHours() {
            const containers = document.querySelectorAll(CONFIG.hoursContainer);
            if (!containers.length || !this.data.info?.hours) return;

            const hoursHTML = Object.entries(this.data.info.hours)
                .map(([day, hours]) => `
                    <div class="cms-hours-item">
                        <span class="cms-day">${day.charAt(0).toUpperCase() + day.slice(1)}</span>
                        <span class="cms-hours">${hours}</span>
                    </div>
                `)
                .join('');

            containers.forEach(container => {
                container.innerHTML = `<div class="cms-hours-list">${hoursHTML}</div>`;
            });
        }

        renderSocialLinks() {
            const containers = document.querySelectorAll(CONFIG.socialLinksContainer);
            if (!containers.length || !this.data.info?.socialLinks) return;

            const socialHTML = Object.entries(this.data.info.socialLinks)
                .filter(([platform, url]) => url)
                .map(([platform, url]) => `
                    <a href="${url}" target="_blank" rel="noopener" class="cms-social-link cms-${platform}">
                        ${this.getSocialIcon(platform)} ${platform.charAt(0).toUpperCase() + platform.slice(1)}
                    </a>
                `)
                .join('');

            containers.forEach(container => {
                container.innerHTML = socialHTML;
            });
        }

        renderOrderingLinks() {
            const containers = document.querySelectorAll(CONFIG.orderingLinksContainer);
            if (!containers.length || !this.data.orderingLinks) return;

            const orderingHTML = Object.entries(this.data.orderingLinks)
                .filter(([platform, url]) => url)
                .map(([platform, url]) => `
                    <a href="${url}" target="_blank" rel="noopener" class="cms-ordering-link cms-${platform}">
                        <span class="cms-platform-name">${platform.charAt(0).toUpperCase() + platform.slice(1)}</span>
                        <span class="cms-order-text">Order Now</span>
                    </a>
                `)
                .join('');

            containers.forEach(container => {
                container.innerHTML = `<div class="cms-ordering-grid">${orderingHTML}</div>`;
            });
        }

        getSocialIcon(platform) {
            const icons = {
                facebook: '📘',
                instagram: '📷',
                twitter: '🐦',
                tiktok: '🎵'
            };
            return icons[platform] || '🔗';
        }

        setupCategoryFiltering() {
            // Look for existing category buttons or create them
            let filterContainer = document.querySelector('.menu-filters, .category-filters');
            
            if (!filterContainer && this.data.categories) {
                // Create filter buttons if they don't exist
                const menuContainer = document.querySelector(CONFIG.menuContainer);
                if (menuContainer) {
                    filterContainer = document.createElement('div');
                    filterContainer.className = 'cms-menu-filters';
                    
                    const filtersHTML = `
                        <button class="cms-filter-btn active" data-category="all">All</button>
                        ${this.data.categories.map(cat => 
                            `<button class="cms-filter-btn" data-category="${cat}">${cat}</button>`
                        ).join('')}
                    `;
                    
                    filterContainer.innerHTML = filtersHTML;
                    menuContainer.parentNode.insertBefore(filterContainer, menuContainer);
                }
            }

            // Add event listeners for filtering
            if (filterContainer) {
                filterContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('cms-filter-btn') || e.target.dataset.category) {
                        const category = e.target.dataset.category;
                        this.filterMenuByCategory(category);
                        
                        // Update active state
                        filterContainer.querySelectorAll('.cms-filter-btn, [data-category]').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        e.target.classList.add('active');
                    }
                });
            }
        }

        filterMenuByCategory(category) {
            const menuItems = document.querySelectorAll('.menu-item[data-category]');
            
            menuItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        setupAutoUpdate() {
            setInterval(() => {
                this.loadData().then(() => {
                    this.renderAll();
                }).catch(() => {
                    // Silently fail on auto-update errors
                });
            }, CONFIG.updateInterval);
        }

        addAdminLink() {
            // Add a discreet admin link if it doesn't exist
            if (!document.querySelector('.cms-admin-link')) {
                const adminLink = document.createElement('a');
                adminLink.href = '/admin.html';
                adminLink.className = 'cms-admin-link';
                adminLink.textContent = 'Admin';
                adminLink.style.cssText = `
                    position: fixed;
                    bottom: 10px;
                    right: 10px;
                    background: #dc3545;
                    color: white;
                    padding: 8px 12px;
                    border-radius: 20px;
                    text-decoration: none;
                    font-size: 12px;
                    opacity: 0.1;
                    transition: opacity 0.3s;
                    z-index: 1000;
                `;
                
                adminLink.addEventListener('mouseenter', () => {
                    adminLink.style.opacity = '1';
                });
                
                adminLink.addEventListener('mouseleave', () => {
                    adminLink.style.opacity = '0.1';
                });
                
                document.body.appendChild(adminLink);
            }
        }

        // Public method to manually refresh data
        refresh() {
            this.init();
        }
    }

    // Auto-inject CSS styles
    function injectCSS() {
        if (document.querySelector('#cms-integration-styles')) return;

        const styles = `
            <style id="cms-integration-styles">
                /* CMS Integration Styles */
                .cms-unavailable {
                    opacity: 0.7;
                    position: relative;
                }
                
                .cms-unavailable::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.8);
                    pointer-events: none;
                    z-index: 1;
                }
                
                .cms-stock-status {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 4px;
                    padding: 8px;
                    margin-top: 8px;
                    position: relative;
                    z-index: 2;
                    font-size: 0.9em;
                }
                
                .cms-status-out-of-stock {
                    background: #f8d7da;
                    border-color: #f5c6cb;
                }
                
                .cms-status-seasonal {
                    background: #d1ecf1;
                    border-color: #bee5eb;
                }
                
                .cms-status-temporarily-unavailable {
                    background: #fff3cd;
                    border-color: #ffeaa7;
                }
                
                .cms-status-badge {
                    font-weight: bold;
                    color: #721c24;
                }
                
                .cms-reason, .cms-return-date {
                    margin: 4px 0;
                    font-size: 0.9em;
                    color: #6c757d;
                }
                
                .cms-badge {
                    margin-left: 8px;
                    font-size: 1.1em;
                }
                
                .cms-menu-filters {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 20px;
                    flex-wrap: wrap;
                }
                
                .cms-filter-btn {
                    padding: 8px 16px;
                    border: 2px solid #ddd;
                    background: white;
                    color: #333;
                    border-radius: 20px;
                    cursor: pointer;
                    transition: all 0.3s;
                }
                
                .cms-filter-btn:hover,
                .cms-filter-btn.active {
                    background: #dc3545;
                    color: white;
                    border-color: #dc3545;
                }
                
                .cms-announcement {
                    background: #e7f3ff;
                    border: 1px solid #b3d7ff;
                    border-radius: 6px;
                    padding: 15px;
                    margin-bottom: 15px;
                }
                
                .cms-priority-high {
                    background: #ffe7e7;
                    border-color: #ffb3b3;
                }
                
                .cms-priority-medium {
                    background: #fff7e6;
                    border-color: #ffd9a3;
                }
                
                .cms-hours-list {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                }
                
                .cms-hours-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .cms-day {
                    font-weight: bold;
                    text-transform: capitalize;
                }
                
                .cms-social-link, .cms-ordering-link {
                    display: inline-block;
                    padding: 10px 15px;
                    margin: 5px;
                    background: #f8f9fa;
                    color: #333;
                    text-decoration: none;
                    border-radius: 6px;
                    transition: all 0.3s;
                }
                
                .cms-social-link:hover, .cms-ordering-link:hover {
                    background: #dc3545;
                    color: white;
                    text-decoration: none;
                }
                
                .cms-ordering-grid {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                }
                
                @media (max-width: 768px) {
                    .cms-menu-filters {
                        justify-content: center;
                    }
                    
                    .cms-filter-btn {
                        flex: 1;
                        min-width: 120px;
                    }
                    
                    .cms-hours-item {
                        font-size: 0.9em;
                    }
                    
                    .cms-ordering-grid {
                        flex-direction: column;
                    }
                }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    // Initialize when DOM is ready
    function initialize() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                injectCSS();
                window.MilfordCMS = new MilfordCMSIntegration();
            });
        } else {
            injectCSS();
            window.MilfordCMS = new MilfordCMSIntegration();
        }
    }

    // Start the integration
    initialize();

    // Expose refresh function globally
    window.refreshMilfordCMS = function() {
        if (window.MilfordCMS) {
            window.MilfordCMS.refresh();
        }
    };

})();