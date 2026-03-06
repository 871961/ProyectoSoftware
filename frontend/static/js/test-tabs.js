console.log('=== TEST TABS CARGADO ===');

// Script de prueba para tabs
document.addEventListener('DOMContentLoaded', () => {
    console.log('TEST: DOMContentLoaded ejecutado');

    const navLinks = document.querySelectorAll('.nav-link[data-tab]');
    const tabs = document.querySelectorAll('.tab-content');

    console.log('TEST: Nav links encontrados:', navLinks.length);
    console.log('TEST: Tabs encontrados:', tabs.length);

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = link.getAttribute('data-tab');

            console.log('TEST: Click en tab:', targetTab);

            // Ocultar todos los tabs
            tabs.forEach(tab => tab.classList.add('hidden'));

            // Remover active de todos los links
            navLinks.forEach(l => l.classList.remove('active'));

            // Mostrar el tab seleccionado
            const selectedTab = document.getElementById(`tab-${targetTab}`);
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
                console.log('TEST: Tab mostrado exitosamente');
            } else {
                console.error('TEST: Tab no encontrado:', `tab-${targetTab}`);
            }

            // Activar el link seleccionado
            link.classList.add('active');
        });
    });
});
