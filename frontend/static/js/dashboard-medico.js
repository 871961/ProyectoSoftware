/*
    Archivo: dashboard-medico.js
    Descripción: Archivo JavaScript para la funcionalidad del dashboard médico en la aplicación MedHistory.
    Fecha: Febrero 2026
    Autoras: Yousra y Claudia
*/

/**
 * ====================================
 * MedHistory - Doctor Dashboard Module
 * ====================================
 * 
 * Purpose: Gestión del panel de control del médico
 * Responsibilities:
 *   - Gestión del sidebar móvil (abrir/cerrar)
 *   - Estados activos de navegación
 *   - Inicialización de iconos Lucide
 *   - Interacciones de la interfaz del dashboard
 * 
 * Dependencies: Lucide Icons
 * Author: MedHistory Development Team
 * Last Modified: Febrero 2, 2026
 */

// ========================================
// MODULE: SIDEBAR MANAGER
// ========================================

class SidebarManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.openBtn = document.getElementById('openSidebar');
        this.closeBtn = document.getElementById('closeSidebar');

        this.init();
    }

    /**
     * Inicializa los event listeners del sidebar
     */
    init() {
        if (!this.sidebar || !this.sidebarOverlay) {
            console.warn('⚠️ Sidebar elements not found');
            return;
        }

        // Open sidebar
        this.openBtn?.addEventListener('click', () => this.open());

        // Close sidebar
        this.closeBtn?.addEventListener('click', () => this.close());

        // Close on overlay click
        this.sidebarOverlay.addEventListener('click', () => this.close());
    }

    /**
     * Abre el sidebar en modo móvil
     */
    open() {
        this.sidebar.classList.add('open');
        this.sidebarOverlay.classList.remove('hidden');
    }

    /**
     * Cierra el sidebar en modo móvil
     */
    close() {
        this.sidebar.classList.remove('open');
        this.sidebarOverlay.classList.add('hidden');
    }
}

// ========================================
// MODULE: NAVIGATION MANAGER
// ========================================

class NavigationManager {
    constructor() {
        this.sidebarLinks = document.querySelectorAll('.sidebar-item');
        this.init();
    }

    /**
     * Inicializa la navegación del sidebar
     */
    init() {
        this.sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => this.setActive(link));
        });
    }

    /**
     * Establece el link activo
     * @param {HTMLElement} activeLink - Link a activar
     */
    setActive(activeLink) {
        // Remove active class from all links
        this.sidebarLinks.forEach(link => link.classList.remove('active'));

        // Add active class to clicked link
        activeLink.classList.add('active');
    }
}

// ========================================
// MODULE: DOCTOR DASHBOARD
// ========================================

class DoctorDashboard {
    constructor() {
        this.sidebarManager = null;
        this.navigationManager = null;
    }

    /**
     * Inicializa el dashboard del médico
     */
    init() {
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Initialize sidebar
        this.sidebarManager = new SidebarManager();

        // Initialize navigation
        this.navigationManager = new NavigationManager();

        console.log('✅ Doctor Dashboard initialized successfully');
    }
}

// ========================================
// APPLICATION ENTRY POINT
// ========================================

/**
 * Inicializa el dashboard cuando el DOM esté listo
 */
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new DoctorDashboard();
    dashboard.init();
});
