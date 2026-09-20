// Inspiring EBook Store - Enhanced JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeTooltips();
    initializePopovers();
    initializeSmoothScrolling();
    initializeAlerts();
    initializeFormValidation();
    initializeReadingProgress();
    initializeSearch();
    initializeLazyLoading();
    initializeDarkMode();
    initializeBookAnimations();
    initializeSparkleEffect();
    initializeReadingStats();
    
    // Initialize payment form if present
    if (document.querySelector('#paymentForm')) {
        initializePaymentForm();
    }
});

// Initialize tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize popovers
function initializePopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Smooth scrolling for anchor links
function initializeSmoothScrolling() {
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
}

// Auto-hide alerts after 5 seconds
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-permanent')) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        }
    });
}

// Form validation enhancement
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Reading progress tracker
function initializeReadingProgress() {
    if (document.querySelector('.reading-text')) {
        trackReadingProgress();
    }
}

function trackReadingProgress() {
    const readingText = document.querySelector('.reading-text');
    if (!readingText) return;

    const progressBar = createProgressBar();
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset;
        const docHeight = document.body.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        
        progressBar.style.width = Math.min(scrollPercent, 100) + '%';
        
        // Add reading milestone celebrations
        if (scrollPercent > 25 && !progressBar.dataset.milestone25) {
            progressBar.dataset.milestone25 = 'true';
            showReadingMilestone('25% Complete! Keep reading! 📖');
        }
        if (scrollPercent > 50 && !progressBar.dataset.milestone50) {
            progressBar.dataset.milestone50 = 'true';
            showReadingMilestone('Halfway there! You\'re doing great! 🌟');
        }
        if (scrollPercent > 75 && !progressBar.dataset.milestone75) {
            progressBar.dataset.milestone75 = 'true';
            showReadingMilestone('Almost finished! Final stretch! 🚀');
        }
        if (scrollPercent >= 95 && !progressBar.dataset.milestone100) {
            progressBar.dataset.milestone100 = 'true';
            showReadingMilestone('Chapter complete! Amazing work! 🎉');
        }
    });
}

function createProgressBar() {
    const progressContainer = document.createElement('div');
    progressContainer.className = 'reading-progress';
    
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress-bar';
    
    progressContainer.appendChild(progressBar);
    document.body.appendChild(progressContainer);
    
    return progressBar;
}

function showReadingMilestone(message) {
    showNotification(message, 'success');
    createSparkleEffect();
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('#searchInput');
    if (!searchInput) return;

    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(this.value);
        }, 300);
    });
}

function performSearch(query) {
    const bookCards = document.querySelectorAll('.book-card');
    const searchQuery = query.toLowerCase().trim();
    
    bookCards.forEach(card => {
        const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
        const author = card.querySelector('.card-text')?.textContent.toLowerCase() || '';
        const description = card.querySelector('.card-text:last-of-type')?.textContent.toLowerCase() || '';
        
        const matches = title.includes(searchQuery) || 
                       author.includes(searchQuery) || 
                       description.includes(searchQuery);
        
        const cardContainer = card.closest('.col-lg-4, .col-md-6');
        if (cardContainer) {
            cardContainer.style.display = matches || !searchQuery ? 'block' : 'none';
            
            // Add search highlight animation
            if (matches && searchQuery) {
                card.style.animation = 'none';
                setTimeout(() => {
                    card.style.animation = 'bookFloat 1s ease-in-out';
                }, 10);
            }
        }
    });
}

// Image lazy loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    img.classList.add('fade-in');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Dark mode functionality
function initializeDarkMode() {
    const darkModeToggle = document.querySelector('#darkModeToggle');
    if (!darkModeToggle) return;

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.body.setAttribute('data-theme', savedTheme);
        darkModeToggle.checked = savedTheme === 'dark';
    }

    darkModeToggle.addEventListener('change', function() {
        const theme = this.checked ? 'dark' : 'light';
        document.body.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Add transition effect
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    });
}

// Book animations
function initializeBookAnimations() {
    const bookCards = document.querySelectorAll('.book-card');
    
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                entry.target.style.animationDelay = Math.random() * 0.3 + 's';
            }
        });
    }, { threshold: 0.1 });
    
    bookCards.forEach(card => {
        card.style.opacity = '0';
        observer.observe(card);
        
        // Add hover sound effect (visual feedback)
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
            createMiniSparkles(this);
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// Sparkle effect
function initializeSparkleEffect() {
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        setInterval(() => {
            createSparkleEffect(heroSection);
        }, 3000);
    }
}

function createSparkleEffect(container = document.body) {
    for (let i = 0; i < 5; i++) {
        setTimeout(() => {
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.style.position = 'absolute';
            sparkle.style.left = Math.random() * 100 + '%';
            sparkle.style.top = Math.random() * 100 + '%';
            sparkle.style.pointerEvents = 'none';
            sparkle.style.zIndex = '1000';
            
            container.style.position = 'relative';
            container.appendChild(sparkle);
            
            setTimeout(() => {
                sparkle.remove();
            }, 2000);
        }, i * 200);
    }
}

function createMiniSparkles(element) {
    const rect = element.getBoundingClientRect();
    for (let i = 0; i < 3; i++) {
        const sparkle = document.createElement('div');
        sparkle.style.position = 'fixed';
        sparkle.style.left = rect.left + Math.random() * rect.width + 'px';
        sparkle.style.top = rect.top + Math.random() * rect.height + 'px';
        sparkle.style.width = '3px';
        sparkle.style.height = '3px';
        sparkle.style.background = '#f1c40f';
        sparkle.style.borderRadius = '50%';
        sparkle.style.pointerEvents = 'none';
        sparkle.style.zIndex = '1001';
        sparkle.style.animation = 'sparkle 1s ease-out forwards';
        
        document.body.appendChild(sparkle);
        
        setTimeout(() => {
            sparkle.remove();
        }, 1000);
    }
}

// Reading statistics
function initializeReadingStats() {
    // Track reading time
    let startTime = Date.now();
    let totalReadingTime = parseInt(localStorage.getItem('totalReadingTime') || '0');
    
    // Update reading time every minute
    setInterval(() => {
        if (document.querySelector('.reading-text')) {
            totalReadingTime += 60000; // 1 minute
            localStorage.setItem('totalReadingTime', totalReadingTime.toString());
        }
    }, 60000);
    
    // Track pages read
    window.addEventListener('beforeunload', () => {
        const currentTime = Date.now();
        const sessionTime = currentTime - startTime;
        if (sessionTime > 30000) { // Only count if read for more than 30 seconds
            const pagesRead = parseInt(localStorage.getItem('pagesRead') || '0') + 1;
            localStorage.setItem('pagesRead', pagesRead.toString());
        }
    });
}

// Utility functions
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${getIconForType(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, 5000);
}

function getIconForType(type) {
    const icons = {
        'success': 'check-circle',
        'info': 'info-circle',
        'warning': 'exclamation-triangle',
        'danger': 'exclamation-circle'
    };
    return icons[type] || 'info-circle';
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(dateString) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(dateString));
}

function formatReadingTime(milliseconds) {
    const hours = Math.floor(milliseconds / 3600000);
    const minutes = Math.floor((milliseconds % 3600000) / 60000);
    
    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
}

// Book preview functionality
function toggleChapter(chapterId) {
    const chapter = document.querySelector(`#${chapterId}`);
    if (chapter) {
        const isVisible = chapter.classList.contains('show');
        
        // Hide all other chapters with animation
        document.querySelectorAll('.chapter-content .collapse').forEach(c => {
            if (c !== chapter) {
                c.classList.remove('show');
            }
        });
        
        // Toggle current chapter with animation
        if (isVisible) {
            chapter.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                chapter.classList.remove('show');
                chapter.style.animation = '';
            }, 300);
        } else {
            chapter.classList.add('show');
            chapter.style.animation = 'fadeInUp 0.3s ease-out';
        }
    }
}

// Payment form enhancements
function initializePaymentForm() {
    const paymentForm = document.querySelector('#paymentForm');
    if (!paymentForm) return;

    const paymentMethods = paymentForm.querySelectorAll('input[name="payment_method"]');
    const submitButton = paymentForm.querySelector('button[type="submit"]');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            updatePaymentUI(this.value);
            
            // Add selection animation
            const label = this.closest('.payment-option');
            label.style.animation = 'pulse 0.3s ease-out';
            setTimeout(() => {
                label.style.animation = '';
            }, 300);
        });
    });
    
    paymentForm.addEventListener('submit', function(e) {
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            // Add loading animation
            submitButton.style.animation = 'pulse 1s infinite';
        }
    });
}

function updatePaymentUI(method) {
    const mpesaDetails = document.querySelector('#mpesaDetails');
    const paypalDetails = document.querySelector('#paypalDetails');
    
    // Hide all details first
    [mpesaDetails, paypalDetails].forEach(detail => {
        if (detail) {
            detail.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                detail.style.display = 'none';
                detail.style.animation = '';
            }, 300);
        }
    });
    
    // Show relevant details with animation
    setTimeout(() => {
        if (method === 'mpesa' && mpesaDetails) {
            mpesaDetails.style.display = 'block';
            mpesaDetails.style.animation = 'fadeInUp 0.3s ease-out';
        } else if (method === 'paypal' && paypalDetails) {
            paypalDetails.style.display = 'block';
            paypalDetails.style.animation = 'fadeInUp 0.3s ease-out';
        }
    }, 300);
}

// Admin functionality
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Reading achievements
function checkReadingAchievements() {
    const totalTime = parseInt(localStorage.getItem('totalReadingTime') || '0');
    const pagesRead = parseInt(localStorage.getItem('pagesRead') || '0');
    
    const achievements = [
        { id: 'first_book', condition: pagesRead >= 1, message: 'First Book Read! 📚' },
        { id: 'bookworm', condition: pagesRead >= 10, message: 'Bookworm Achievement! 🐛' },
        { id: 'speed_reader', condition: totalTime >= 3600000, message: 'Speed Reader! ⚡' },
        { id: 'dedicated_reader', condition: totalTime >= 36000000, message: 'Dedicated Reader! 🏆' }
    ];
    
    achievements.forEach(achievement => {
        if (achievement.condition && !localStorage.getItem(achievement.id)) {
            localStorage.setItem(achievement.id, 'true');
            showNotification(achievement.message, 'success');
            createSparkleEffect();
        }
    });
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .fade-in {
        animation: fadeInUp 0.6s ease-out;
    }
`;
document.head.appendChild(style);

// Check achievements periodically
setInterval(checkReadingAchievements, 30000);

// Export functions for global use
window.EBookStore = {
    showNotification,
    formatCurrency,
    formatDate,
    formatReadingTime,
    toggleChapter,
    confirmAction,
    createSparkleEffect
};