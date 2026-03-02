/**
 * ====================================
 * MedHistory - Index Page Logic
 * ====================================
 * 
 * Purpose: Carrusel automático del hero y efectos de botones
 * Responsibilities:
 *   - Gestión del carrusel auto-rotativo (5 segundos)
 *   - Indicadores manuales del carrusel
 *   - Animaciones premium de botones
 *   - Inicialización de iconos Lucide
 * 
 * Dependencies: Lucide Icons
 * Author: MedHistory Development Team
 * Last Modified: Febrero 2, 2026
 */

// ========================================
// CAROUSEL FUNCTIONALITY
// ========================================

let currentHeroSlide = 1;
let heroCarouselInterval;

/**
 * Muestra un slide específico del carrusel
 * @param {number} slideNumber - Número del slide a mostrar (1-indexed)
 */
function showHeroSlide(slideNumber) {
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.hero-indicator');

    // Remove active classes
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));

    // Add active classes
    slides[slideNumber - 1].classList.add('active');
    indicators[slideNumber - 1].classList.add('active');

    currentHeroSlide = slideNumber;
}

/**
 * Avanza al siguiente slide del carrusel
 */
function nextHeroSlide() {
    const nextSlideNum = currentHeroSlide === 2 ? 1 : currentHeroSlide + 1;
    showHeroSlide(nextSlideNum);
}

/**
 * Inicia la rotación automática del carrusel
 */
function startHeroCarousel() {
    heroCarouselInterval = setInterval(nextHeroSlide, 5000); // 5 segundos
}

/**
 * Detiene la rotación automática del carrusel
 */
function stopHeroCarousel() {
    clearInterval(heroCarouselInterval);
}

// ========================================
// BUTTON ENHANCEMENTS
// ========================================

/**
 * Configura animaciones avanzadas para botones premium
 */
function initializeButtonEffects() {
    const buttons = document.querySelectorAll('.medical-btn-primary, .medical-btn-secondary');

    buttons.forEach(button => {
        // Loading state on click
        button.addEventListener('click', function (e) {
            this.classList.add('loading');
            const loadingState = this.querySelector('.loading-state');
            if (loadingState) {
                loadingState.classList.remove('opacity-0');
                loadingState.classList.add('opacity-100');
            }
        });

        // Enhanced hover effects
        button.addEventListener('mouseenter', function () {
            const icon = this.querySelector('.btn-icon-arrow');
            if (icon) {
                icon.style.transform = 'translateX(4px) scale(1.1)';
            }
        });

        button.addEventListener('mouseleave', function () {
            const icon = this.querySelector('.btn-icon-arrow');
            if (icon) {
                icon.style.transform = 'translateX(0) scale(1)';
            }
        });
    });

    // Pulse animation for primary button
    const primaryBtn = document.querySelector('.medical-btn-primary');
    if (primaryBtn) {
        setInterval(() => {
            primaryBtn.style.boxShadow = '0 4px 25px rgba(2, 132, 199, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
            setTimeout(() => {
                primaryBtn.style.boxShadow = '0 4px 15px rgba(2, 132, 199, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.2)';
            }, 1500);
        }, 3000);
    }
}

// ========================================
// CAROUSEL CONTROLS
// ========================================

/**
 * Configura los controles manuales del carrusel
 */
function initializeCarouselControls() {
    const heroIndicators = document.querySelectorAll('.hero-indicator');
    heroIndicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function () {
            const slideNumber = index + 1;
            showHeroSlide(slideNumber);

            // Restart carousel after manual interaction
            stopHeroCarousel();
            setTimeout(startHeroCarousel, 3000);
        });
    });

    // Pause carousel on hover
    const heroCarousel = document.querySelector('.hero-content-carousel');
    if (heroCarousel) {
        heroCarousel.addEventListener('mouseenter', stopHeroCarousel);
        heroCarousel.addEventListener('mouseleave', startHeroCarousel);
    }
}

// ========================================
// INITIALIZATION
// ========================================

/**
 * Inicializa todos los componentes de la página
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Start hero carousel
    startHeroCarousel();

    // Initialize carousel controls
    initializeCarouselControls();

    // Initialize button effects
    initializeButtonEffects();

    console.log('✅ MedHistory Index Page initialized successfully');
});