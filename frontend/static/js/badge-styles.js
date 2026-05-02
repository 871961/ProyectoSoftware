/**
 * badge-styles.js
 * Aplica estilos dinámicos a los badges de notificación con degradado azul
 * Estilos: Degradado azul claro de #93c5fd a #60a5fa con texto azul oscuro #1e3a8a
 */

(function() {
    // Colores del degradado azul
    const BADGE_GRADIENT = 'linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%)';
    const BADGE_TEXT_COLOR = '#1e3a8a';

    const applyBadgeStyles = () => {
        // Badge del menú lateral (Mensajes)
        const sidebarBadge = document.getElementById('navMensajesBadge');
        if (sidebarBadge) {
            sidebarBadge.style.background = BADGE_GRADIENT;
            sidebarBadge.style.color = BADGE_TEXT_COLOR;
            sidebarBadge.style.width = '28px';
            sidebarBadge.style.height = '28px';
            sidebarBadge.style.padding = '0';
            sidebarBadge.style.borderRadius = '9999px';
        }

        // Badges en la lista de contactos del chat
        const chatContactList = document.getElementById('chatContactList');
        if (chatContactList) {
            const badges = chatContactList.querySelectorAll('span[class*="rounded-full"][class*="w-7"], span[class*="rounded-full"][class*="px-1.5"]');
            badges.forEach((badge) => {
                badge.style.background = BADGE_GRADIENT;
                badge.style.color = BADGE_TEXT_COLOR;
                badge.style.width = '28px';
                badge.style.height = '28px';
                badge.style.padding = '0';
                badge.style.borderRadius = '9999px';
            });
        }
    };

    // Ejecutar cuando DOM está listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyBadgeStyles);
    } else {
        applyBadgeStyles();
    }

    // Observer para badges dinámicos
    try {
        const chatContactList = document.getElementById('chatContactList');
        if (chatContactList) {
            const observer = new MutationObserver(() => {
                setTimeout(applyBadgeStyles, 50);
            });
            observer.observe(chatContactList, { childList: true, subtree: true });
        }
    } catch (e) {
        // Continue without observer if error
    }

    // Re-aplicar cada segundo como fallback
    setInterval(applyBadgeStyles, 1000);
})();
