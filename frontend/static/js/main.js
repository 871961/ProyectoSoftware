/**
 * ====================================
 * MedHistory - Main Application Logic
 * ====================================
 * 
 * Purpose: Main entry point for index.html interactions
 * Responsibilities:
 *   - Initialize Lucide icons
 *   - Manage hero carousel auto-rotation
 *   - Handle carousel indicators interaction
 * 
 * Dependencies: Lucide Icons library
 * Author: MedHistory Development Team
 * Last Modified: February 2, 2026
 */

// ========================================
// MODULE: HERO CAROUSEL MANAGER
// ========================================

class HeroCarousel {
    constructor() {
        this.currentSlide = 1;
        this.totalSlides = 2;
        this.autoRotateInterval = null;
        this.autoRotateDelay = 5000; // 5 seconds

        this.init();
    }

    /**
     * Initialize carousel functionality
     */
    init() {
        this.setupIndicators();
        this.startAutoRotate();
    }

    /**
     * Setup click handlers for carousel indicators
     */
    setupIndicators() {
        const indicators = document.querySelectorAll('.hero-indicator');

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                this.goToSlide(index + 1);
                this.resetAutoRotate();
            });
        });
    }

    /**
     * Navigate to specific slide
     * @param {number} slideNumber - Slide index (1-based)
     */
    goToSlide(slideNumber) {
        if (slideNumber < 1 || slideNumber > this.totalSlides) return;

        // Remove active class from all slides
        const slides = document.querySelectorAll('.hero-slide');
        slides.forEach(slide => slide.classList.remove('active'));

        // Add active class to target slide
        slides[slideNumber - 1].classList.add('active');

        // Update indicators
        this.updateIndicators(slideNumber);

        this.currentSlide = slideNumber;
    }

    /**
     * Update visual state of indicators
     * @param {number} activeSlide - Currently active slide number
     */
    updateIndicators(activeSlide) {
        const indicators = document.querySelectorAll('.hero-indicator');

        indicators.forEach((indicator, index) => {
            if (index + 1 === activeSlide) {
                indicator.classList.add('active');
            } else {
                indicator.classList.remove('active');
            }
        });
    }

    /**
     * Advance to next slide
     */
    nextSlide() {
        let nextSlide = this.currentSlide + 1;

        if (nextSlide > this.totalSlides) {
            nextSlide = 1;
        }

        this.goToSlide(nextSlide);
    }

    /**
     * Start automatic carousel rotation
     */
    startAutoRotate() {
        this.autoRotateInterval = setInterval(() => {
            this.nextSlide();
        }, this.autoRotateDelay);
    }

    /**
     * Stop automatic carousel rotation
     */
    stopAutoRotate() {
        if (this.autoRotateInterval) {
            clearInterval(this.autoRotateInterval);
            this.autoRotateInterval = null;
        }
    }

    /**
     * Reset auto-rotation timer (useful after manual interaction)
     */
    resetAutoRotate() {
        this.stopAutoRotate();
        this.startAutoRotate();
    }
}

// ========================================
// MODULE: LUCIDE ICONS INITIALIZER
// ========================================

class IconManager {
    /**
     * Initialize all Lucide icons on the page
     */
    static init() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        } else {
            console.error('Lucide library not loaded');
        }
    }

    /**
     * Re-initialize icons (useful after dynamic content changes)
     */
    static refresh() {
        this.init();
    }
}

// ========================================
// MODULE: APPLICATION INITIALIZER
// ========================================

class MedHistoryApp {
    constructor() {
        this.heroCarousel = null;
    }

    /**
     * Initialize the entire application
     */
    init() {
        // Initialize icons
        IconManager.init();

        // Initialize hero carousel
        this.heroCarousel = new HeroCarousel();

        // Setup global event listeners
        this.setupGlobalListeners();

        console.log('✅ MedHistory application initialized successfully');
    }

    /**
     * Setup global event listeners
     */
    setupGlobalListeners() {
        // Handle page visibility changes (pause carousel when tab not visible)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.heroCarousel.stopAutoRotate();
            } else {
                this.heroCarousel.startAutoRotate();
            }
        });
    }
}

// ========================================
// APPLICATION ENTRY POINT
// ========================================

/**
 * Initialize application when DOM is fully loaded
 */
document.addEventListener('DOMContentLoaded', () => {
    const app = new MedHistoryApp();
    app.init();
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HeroCarousel, IconManager, MedHistoryApp };
}
