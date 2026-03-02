/**
 * ====================================
 * MedHistory - Patient Dashboard Module
 * ====================================
 * 
 * Purpose: Manage patient dashboard and pediatric mode
 * Responsibilities:
 *   - Family member management (add/edit/delete children)
 *   - Switch between adult and pediatric views
 *   - Modal handling for adding dependents
 *   - Age calculation and display
 *   - Profile management
 * 
 * Dependencies: Lucide Icons
 * Author: MedHistory Development Team
 * Last Modified: February 2, 2026
 */

// ========================================
// MODULE: FAMILY MEMBER MANAGER
// ========================================

class FamilyMemberManager {
    constructor() {
        this.familyMembers = [];
        this.storageKey = 'medhistory_family_members';
        this.loadFromStorage();
    }

    /**
     * Add a new family member
     * @param {object} memberData - Member information
     * @returns {object} Added member with generated ID
     */
    addMember(memberData) {
        const member = {
            id: this.generateId(),
            name: memberData.name,
            birthDate: memberData.birthDate,
            bloodType: memberData.bloodType,
            allergies: memberData.allergies || '',
            initials: this.getInitials(memberData.name),
            age: this.calculateAge(memberData.birthDate),
            createdAt: new Date().toISOString()
        };

        this.familyMembers.push(member);
        this.saveToStorage();

        return member;
    }

    /**
     * Remove a family member by ID
     * @param {string} memberId - Member ID to remove
     * @returns {boolean} Success status
     */
    removeMember(memberId) {
        const initialLength = this.familyMembers.length;
        this.familyMembers = this.familyMembers.filter(m => m.id !== memberId);

        if (this.familyMembers.length < initialLength) {
            this.saveToStorage();
            return true;
        }

        return false;
    }

    /**
     * Get member by ID
     * @param {string} memberId - Member ID
     * @returns {object|null} Member object or null
     */
    getMemberById(memberId) {
        return this.familyMembers.find(m => m.id === memberId) || null;
    }

    /**
     * Get all family members
     * @returns {Array} Array of family members
     */
    getAllMembers() {
        return this.familyMembers;
    }

    /**
     * Update member information
     * @param {string} memberId - Member ID
     * @param {object} updates - Fields to update
     * @returns {boolean} Success status
     */
    updateMember(memberId, updates) {
        const member = this.getMemberById(memberId);

        if (member) {
            Object.assign(member, updates);

            // Recalculate derived fields if needed
            if (updates.name) {
                member.initials = this.getInitials(updates.name);
            }
            if (updates.birthDate) {
                member.age = this.calculateAge(updates.birthDate);
            }

            this.saveToStorage();
            return true;
        }

        return false;
    }

    /**
     * Generate unique ID
     * @returns {string} Unique identifier
     */
    generateId() {
        return `member_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Extract initials from full name
     * @param {string} name - Full name
     * @returns {string} Initials (e.g., "JG" from "Juan García")
     */
    getInitials(name) {
        return name.split(' ')
            .filter((_, index, array) => index === 0 || index === array.length - 1)
            .map(word => word[0])
            .join('')
            .toUpperCase();
    }

    /**
     * Calculate age from birth date
     * @param {string} birthDate - Birth date in YYYY-MM-DD format
     * @returns {string} Formatted age (e.g., "18 meses" or "3 años")
     */
    calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let years = today.getFullYear() - birth.getFullYear();
        let months = today.getMonth() - birth.getMonth();

        if (months < 0) {
            years--;
            months += 12;
        }

        if (years < 2) {
            const totalMonths = years * 12 + months;
            return `${totalMonths} ${totalMonths === 1 ? 'mes' : 'meses'}`;
        } else {
            return `${years} ${years === 1 ? 'año' : 'años'}`;
        }
    }

    /**
     * Save family members to localStorage
     */
    saveToStorage() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.familyMembers));
        } catch (error) {
            console.error('Error saving family members:', error);
        }
    }

    /**
     * Load family members from localStorage
     */
    loadFromStorage() {
        try {
            const data = localStorage.getItem(this.storageKey);
            if (data) {
                this.familyMembers = JSON.parse(data);
            }
        } catch (error) {
            console.error('Error loading family members:', error);
            this.familyMembers = [];
        }
    }
}

// ========================================
// MODULE: VIEW MANAGER
// ========================================

class ViewManager {
    constructor() {
        this.currentView = 'adult'; // 'adult' or 'pediatric'
        this.currentChildId = null;
    }

    /**
     * Switch to pediatric view
     * @param {object} childData - Child information
     */
    switchToPediatricView(childData) {
        this.currentView = 'pediatric';
        this.currentChildId = childData.id;

        // Update header title
        const dashboardTitle = document.getElementById('dashboardTitle');
        if (dashboardTitle) {
            dashboardTitle.textContent = `Panel Infantil - ${childData.name}`;
        }

        // Show back button
        const backButton = document.getElementById('backToParentBtn');
        if (backButton) {
            backButton.classList.remove('hidden');
            backButton.classList.add('flex');
        }

        // Update profile information
        this.updateProfileDisplay(childData);

        // Update pediatric view content
        this.updatePediatricContent(childData);

        // Switch views
        const adultView = document.getElementById('adultView');
        const pediatricView = document.getElementById('pediatricView');

        if (adultView) adultView.classList.remove('active');
        if (pediatricView) pediatricView.classList.add('active');

        // Apply pediatric styling to body
        document.body.classList.add('pediatric-mode');

        // Re-initialize icons
        this.refreshIcons();

        // Close mobile sidebar
        this.closeMobileSidebar();
    }

    /**
     * Switch to adult view
     */
    switchToAdultView() {
        this.currentView = 'adult';
        this.currentChildId = null;

        // Reset header title
        const dashboardTitle = document.getElementById('dashboardTitle');
        if (dashboardTitle) {
            dashboardTitle.textContent = 'Mi Panel de Salud';
        }

        // Hide back button
        const backButton = document.getElementById('backToParentBtn');
        if (backButton) {
            backButton.classList.add('hidden');
            backButton.classList.remove('flex');
        }

        // Reset profile information to parent
        this.resetProfileToParent();

        // Switch views
        const adultView = document.getElementById('adultView');
        const pediatricView = document.getElementById('pediatricView');

        if (pediatricView) pediatricView.classList.remove('active');
        if (adultView) adultView.classList.add('active');

        // Remove pediatric styling
        document.body.classList.remove('pediatric-mode');

        // Re-initialize icons
        this.refreshIcons();
    }

    /**
     * Update profile display with child info
     * @param {object} childData - Child information
     */
    updateProfileDisplay(childData) {
        const profileName = document.getElementById('profileName');
        const profileRole = document.getElementById('profileRole');
        const profileAvatar = document.getElementById('profileAvatar');

        if (profileName) {
            profileName.textContent = childData.name;
        }

        if (profileRole) {
            profileRole.textContent = `${childData.age} • Hijo/a`;
        }

        if (profileAvatar) {
            profileAvatar.className = 'w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white font-semibold';
            profileAvatar.textContent = childData.initials;
        }
    }

    /**
     * Reset profile to parent user
     */
    resetProfileToParent() {
        const profileName = document.getElementById('profileName');
        const profileRole = document.getElementById('profileRole');
        const profileAvatar = document.getElementById('profileAvatar');

        if (profileName) {
            profileName.textContent = 'Juan García';
        }

        if (profileRole) {
            profileRole.textContent = 'Paciente';
        }

        if (profileAvatar) {
            profileAvatar.className = 'w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white font-semibold';
            profileAvatar.textContent = 'JG';
        }
    }

    /**
     * Update pediatric view with child-specific data
     * @param {object} childData - Child information
     */
    updatePediatricContent(childData) {
        // Update child name and age in pediatric view
        const childNameElement = document.getElementById('childName');
        const childAgeElement = document.getElementById('childAge');

        if (childNameElement) {
            childNameElement.textContent = childData.name;
        }

        if (childAgeElement) {
            childAgeElement.textContent = `${childData.age} • Última actualización: hoy`;
        }

        // You can add more dynamic content updates here
        // For example: update blood type, allergies, etc.
    }

    /**
     * Close mobile sidebar
     */
    closeMobileSidebar() {
        if (window.innerWidth < 1024) {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (sidebar) sidebar.classList.remove('open');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }
    }

    /**
     * Refresh Lucide icons
     */
    refreshIcons() {
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
    }
}

// ========================================
// MODULE: MODAL MANAGER
// ========================================

class ModalManager {
    constructor() {
        this.modal = null;
        this.form = null;
    }

    /**
     * Initialize modal functionality
     */
    init() {
        this.modal = document.getElementById('addDependentModal');
        this.form = document.getElementById('dependentForm');

        if (!this.modal || !this.form) {
            console.warn('Modal or form not found');
            return;
        }

        this.setupEventListeners();
    }

    /**
     * Setup modal event listeners
     */
    setupEventListeners() {
        // Open modal button
        const openButton = document.getElementById('addDependentBtn');
        if (openButton) {
            openButton.addEventListener('click', () => this.open());
        }

        // Close modal buttons
        const closeButton = document.getElementById('closeModalBtn');
        const cancelButton = document.getElementById('cancelModalBtn');

        if (closeButton) {
            closeButton.addEventListener('click', () => this.close());
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', () => this.close());
        }

        // Close on overlay click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
    }

    /**
     * Open modal
     */
    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            setTimeout(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 100);
        }
    }

    /**
     * Close modal
     */
    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            if (this.form) {
                this.form.reset();
            }
        }
    }
}

// ========================================
// MODULE: NOTIFICATION MANAGER
// ========================================

class NotificationManager {
    /**
     * Show notification toast
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, info)
     */
    static show(message, type = 'success') {
        const colors = {
            success: 'bg-emerald-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };

        const notification = document.createElement('div');
        notification.className = `notification-toast fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// ========================================
// MODULE: DASHBOARD APPLICATION
// ========================================

class PatientDashboard {
    constructor() {
        this.familyManager = new FamilyMemberManager();
        this.viewManager = new ViewManager();
        this.modalManager = new ModalManager();
    }

    /**
     * Initialize dashboard
     */
    init() {
        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Initialize modal
        this.modalManager.init();

        // Setup event listeners
        this.setupEventListeners();

        // Render family members list
        this.renderFamilyMembersList();

        // Setup sidebar
        this.setupSidebar();

        console.log('✅ Patient Dashboard initialized successfully');
    }

    /**
     * Setup all event listeners
     */
    setupEventListeners() {
        // Form submission
        const form = document.getElementById('dependentForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Back to parent button
        const backButton = document.getElementById('backToParentBtn');
        if (backButton) {
            backButton.addEventListener('click', () => {
                this.viewManager.switchToAdultView();
            });
        }

        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.renderFamilyMembersList();
            }
        });
    }

    /**
     * Handle form submission for adding dependent
     * @param {Event} event - Form submit event
     */
    handleFormSubmit(event) {
        event.preventDefault();

        const formData = {
            name: document.getElementById('childNameInput').value,
            birthDate: document.getElementById('childBirthDateInput').value,
            bloodType: document.getElementById('childBloodTypeInput').value,
            allergies: document.getElementById('childAllergiesInput').value
        };

        try {
            const newMember = this.familyManager.addMember(formData);
            this.renderFamilyMembersList();
            this.modalManager.close();
            NotificationManager.show('Hijo/dependiente añadido correctamente');
        } catch (error) {
            console.error('Error adding family member:', error);
            NotificationManager.show('Error al añadir familiar', 'error');
        }
    }

    /**
     * Render family members list in sidebar
     */
    renderFamilyMembersList() {
        const container = document.getElementById('familyMembersList');
        if (!container) return;

        container.innerHTML = '';
        const members = this.familyManager.getAllMembers();

        members.forEach((member) => {
            const memberElement = document.createElement('button');
            memberElement.className = 'family-profile w-full flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gradient-to-r hover:from-emerald-50 hover:to-transparent transition-all';
            memberElement.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                    ${member.initials}
                </div>
                <div class="flex-1 text-left">
                    <p class="text-sm font-medium text-gray-900">${member.name}</p>
                    <p class="text-xs text-gray-500">${member.age}</p>
                </div>
            `;

            memberElement.addEventListener('click', () => {
                this.viewManager.switchToPediatricView(member);
            });

            container.appendChild(memberElement);
        });

        // Re-initialize icons
        setTimeout(() => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }, 100);
    }

    /**
     * Setup sidebar navigation
     */
    setupSidebar() {
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', () => {
                if (sidebar) sidebar.classList.add('open');
                if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
            });
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('open');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('open');
                sidebarOverlay.classList.add('hidden');
            });
        }

        // Sidebar navigation active state
        const sidebarLinks = document.querySelectorAll('.sidebar-item');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                sidebarLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });
    }
}

// ========================================
// APPLICATION ENTRY POINT
// ========================================

/**
 * Initialize dashboard when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new PatientDashboard();
    dashboard.init();
});

// ========================================
// EXPORT FOR MODULE USAGE
// ========================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        FamilyMemberManager,
        ViewManager,
        ModalManager,
        NotificationManager,
        PatientDashboard
    };
}
