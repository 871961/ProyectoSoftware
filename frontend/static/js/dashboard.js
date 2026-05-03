/*
    Archivo: dashboard.js
    Descripcion: Dashboard paciente con historial de consultas en cards y detalle
*/

const CONSULTAS_API = '/backend/src/controllers/ConsultasController.php';
const PERFIL_API = '/backend/src/controllers/PerfilSaludController.php';
const ANTECEDENTES_API = '/backend/src/controllers/AntecedentesController.php';
const RECORDATORIOS_API = '/backend/src/controllers/RecordatoriosController.php';

class SidebarManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.openBtn = document.getElementById('openSidebar');
        this.closeBtn = document.getElementById('closeSidebar');
    }

    init() {
        this.openBtn?.addEventListener('click', () => this.open());
        this.closeBtn?.addEventListener('click', () => this.close());
        this.sidebarOverlay?.addEventListener('click', () => this.close());
    }

    open() {
        this.sidebar?.classList.add('open');
        this.sidebarOverlay?.classList.remove('hidden');
    }

    close() {
        this.sidebar?.classList.remove('open');
        this.sidebarOverlay?.classList.add('hidden');
    }
}

class PacienteDashboard {
    constructor() {
        this.usuario = null;
        this.consultas = [];
        this.cardsContainer = document.getElementById('consultasPacienteCards');
        this.emptyEl = document.getElementById('consultasPacienteEmpty');
        this.logoutLink = document.getElementById('logoutLink');
        this.filtroDesde = document.getElementById('filtroDesdePaciente');
        this.filtroHasta = document.getElementById('filtroHastaPaciente');
        this.filtroBtn = document.getElementById('aplicarFiltroPaciente');
        this.limpiarFiltroBtn = document.getElementById('limpiarFiltroPaciente');
        this.navInicio = document.getElementById('navInicioPaciente');
        this.navPerfilSalud = document.getElementById('navPerfilSaludPaciente');
        this.mainPacienteView = document.getElementById('mainPacienteView');
        this.healthProfileView = document.getElementById('healthProfileView');

        this.modal = document.getElementById('consultaDetailModal');
        this.closeModalBtn = document.getElementById('closeConsultaDetailBtn');

        this.saludForm = document.getElementById('saludPacienteForm');
        this.saludMessage = document.getElementById('saludMessagePaciente');
        this.saludReloadBtn = document.getElementById('saludReloadPaciente');
        this.saludAlturaInput = document.getElementById('saludAlturaPaciente');
        this.saludPesoInput = document.getElementById('saludPesoPaciente');
        this.saludAlergias = document.getElementById('saludAlergiasPaciente');
        this.saludEnfermedades = document.getElementById('saludEnfermedadesPaciente');
        this.saludImcValue = document.getElementById('saludImcValuePaciente');
        this.saludImcLabel = document.getElementById('saludImcLabelPaciente');
        this.saludImcCard = document.getElementById('saludImcCardPaciente');

        this.antecedentesContainer = document.getElementById('antecedentesPacienteLista');

        // Recordatorios
        this.recordatoriosList = document.getElementById('recordatoriosPacienteList');
        this.recordatoriosEmpty = document.getElementById('recordatoriosPacienteEmpty');
        this.notificationBell = document.getElementById('notificationBell');
        this.notificationBadge = document.getElementById('notificationBadge');
        this.notificationPanel = document.getElementById('notificationPanel');
        this.notificationList = document.getElementById('notificationList');
        this.notificationEmpty = document.getElementById('notificationEmpty');
        this.closeNotificationPanelBtn = document.getElementById('closeNotificationPanel');
        this.dismissedKey = 'recordatorios_dismissed';
    }

    async init() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        new SidebarManager().init();
        this.bindEvents();
        await this.cargarSesion();
        await Promise.all([this.cargarConsultas(), this.cargarPerfilSalud(), this.cargarAntecedentes(), this.cargarRecordatorios()]);
        // **NUEVO: Cargar información adicional del usuario**
        await this.cargarInformacionUsuario();
        this.mostrarVista('inicio');
    }

    bindEvents() {
        this.logoutLink?.addEventListener('click', (e) => this.logout(e));
        this.filtroBtn?.addEventListener('click', () => this.cargarConsultas());
        this.limpiarFiltroBtn?.addEventListener('click', () => {
            if (this.filtroDesde) this.filtroDesde.value = '';
            if (this.filtroHasta) this.filtroHasta.value = '';
            this.cargarConsultas();
        });
        this.navInicio?.addEventListener('click', (e) => {
            e.preventDefault();
            this.mostrarVista('inicio');
        });
        this.navPerfilSalud?.addEventListener('click', (e) => {
            e.preventDefault();
            this.mostrarVista('perfil_salud');
        });

        const sidebarNav = this.sidebar?.querySelector('nav');
        sidebarNav?.addEventListener('click', (e) => this.bloquearSidebarSiDependiente(e), true);

        this.closeModalBtn?.addEventListener('click', () => this.closeDetailModal());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeDetailModal();
        });
        this.saludForm?.addEventListener('submit', (e) => this.guardarPerfilSalud(e));
        this.saludReloadBtn?.addEventListener('click', () => this.cargarPerfilSalud());
        this.saludAlturaInput?.addEventListener('input', () => this.actualizarImcVisual());
        this.saludPesoInput?.addEventListener('input', () => this.actualizarImcVisual());

        // Notificaciones (campana)
        this.notificationBell?.addEventListener('click', () => this.toggleNotificationPanel());
        this.closeNotificationPanelBtn?.addEventListener('click', () => this.toggleNotificationPanel(false));
        document.addEventListener('click', (e) => {
            if (!this.notificationPanel || !this.notificationBell) return;
            const inside = this.notificationPanel.contains(e.target) || this.notificationBell.contains(e.target);
            if (!inside) this.toggleNotificationPanel(false);
        });
    }

    bloquearSidebarSiDependiente(event) {
        if (!window.viewingDependiente) return;
        event.preventDefault();
        event.stopPropagation();
        alert('Debes salir del modulo de dependientes primero.');
    }

    async salirModoDependiente() {
        if (!window.viewingDependiente) return;
        if (window.dependientesManager && typeof window.dependientesManager.volverAMiPerfil === 'function') {
            await window.dependientesManager.volverAMiPerfil();
        }
    }

    mostrarVista(vista) {
        const inicio = vista === 'inicio';
        if (this.mainPacienteView) {
            this.mainPacienteView.classList.toggle('active', inicio);
        }
        if (this.healthProfileView) {
            this.healthProfileView.classList.toggle('active', !inicio);
        }
        this.navInicio?.classList.toggle('active', inicio);
        this.navPerfilSalud?.classList.toggle('active', !inicio);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async api(accion, method = 'GET', data = null, params = {}) {
        const query = new URLSearchParams({ accion, ...params }).toString();
        const url = `${CONSULTAS_API}?${query}`;
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' }
        };
        if (data) options.body = JSON.stringify(data);

        const response = await fetch(url, options);
        const raw = await response.text();
        let payload = null;
        try {
            payload = raw ? JSON.parse(raw) : null;
        } catch (_error) {
            throw new Error('Respuesta no valida del servidor.');
        }
        if (!response.ok || !payload.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    async apiRecordatorios(accion, method = 'GET', data = null, params = {}) {
        const query = new URLSearchParams({ accion, ...params }).toString();
        const url = `${RECORDATORIOS_API}?${query}`;
        const options = { method, headers: { 'Content-Type': 'application/json' } };
        if (data) options.body = JSON.stringify(data);

        const response = await fetch(url, options);
        const raw = await response.text();
        let payload = null;
        try { payload = raw ? JSON.parse(raw) : null; } catch (_e) { throw new Error('Respuesta no valida del servidor'); }
        if (!response.ok || !payload.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    async cargarSesion() {
        try {
            const res = await this.api('sesion');
            this.usuario = res.usuario;

            if (this.usuario.tipo !== 'paciente') {
                window.location.href = 'login.html';
                return;
            }

            this.pintarUsuario();
            // Notificar al módulo de citas que ya tenemos la sesión
            document.dispatchEvent(new CustomEvent('citasDashboardReady', {
                detail: { dni: this.usuario.id }
            }));
        } catch (_error) {
            window.location.href = 'login.html';
        }
    }

    async perfilApi(accion, method = 'GET', data = null, params = {}) {
        const query = new URLSearchParams({ accion, ...params }).toString();
        const url = `${PERFIL_API}?${query}`;
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' }
        };
        if (data) options.body = JSON.stringify(data);

        const response = await fetch(url, options);
        const raw = await response.text();
        let payload = null;
        try {
            payload = raw ? JSON.parse(raw) : null;
        } catch (_error) {
            throw new Error('Respuesta no valida en perfil de salud.');
        }
        if (!response.ok || !payload.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    pintarUsuario() {
        // Si estamos viendo un dependiente, no sobreescribir la cabecera del UI
        if (window.viewingDependiente) return;

        const nombre = this.usuario.nombre || '';
        const iniciales = this.obtenerIniciales(nombre);

        const profileName = document.getElementById('profileName');
        const profileAvatar = document.getElementById('profileAvatar');
        const welcomeTitle = document.getElementById('welcomeTitle');
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');

        if (profileName) profileName.textContent = nombre;
        if (profileAvatar) profileAvatar.textContent = iniciales;
        if (welcomeTitle) welcomeTitle.textContent = `Bienvenido, ${nombre}`;
        if (welcomeSubtitle) welcomeSubtitle.textContent = 'Tu historial medico se construye con cada consulta registrada.';
    }

    async cargarPerfilSalud() {
        if (window.viewingDependiente) return;
        try {
            const res = await this.perfilApi('obtener_mi_perfil');
            this.pintarPerfilSalud(res.data || {});
            this.setSaludMessage('', false);
        } catch (error) {
            this.setSaludMessage(error.message, true);
        }
    }

    pintarPerfilSalud(perfil) {
        if (window.viewingDependiente) return;
        if (!this.saludForm) return;
        this.saludForm.elements.altura_cm.value = perfil.altura_cm ?? '';
        this.saludForm.elements.peso_kg.value = perfil.peso_kg ?? '';
        this.saludForm.elements.consumo_tabaco.value = perfil.consumo_tabaco ?? '';
        this.saludForm.elements.consumo_alcohol.value = perfil.consumo_alcohol ?? '';
        this.saludForm.elements.actividad_fisica.value = perfil.actividad_fisica ?? '';
        if (this.saludAlergias) this.saludAlergias.textContent = perfil.alergias || 'No registradas';
        if (this.saludEnfermedades) this.saludEnfermedades.textContent = perfil.enfermedades || 'No registradas';
        this.actualizarImcVisual(perfil.imc, perfil.clasificacion_imc);
    }

    async guardarPerfilSalud(event) {
        event.preventDefault();
        if (!this.saludForm) return;
        const formData = new FormData(this.saludForm);
        const payload = {
            altura_cm: formData.get('altura_cm'),
            peso_kg: formData.get('peso_kg'),
            consumo_tabaco: formData.get('consumo_tabaco'),
            consumo_alcohol: formData.get('consumo_alcohol'),
            actividad_fisica: formData.get('actividad_fisica')
        };

        try {
            const res = await this.perfilApi('actualizar_mi_perfil', 'POST', payload);
            this.pintarPerfilSalud(res.data || {});
            this.setSaludMessage('Perfil actualizado correctamente.', false);
        } catch (error) {
            this.setSaludMessage(error.message, true);
        }
    }

    actualizarImcVisual(imc = null, clasificacion = null) {
        const peso = Number(this.saludPesoInput?.value);
        const altura = Number(this.saludAlturaInput?.value);
        let finalImc = imc;
        let finalClasificacion = clasificacion;

        if (finalImc === null || finalImc === undefined) {
            if (peso > 0 && altura > 0) {
                const alturaM = altura / 100;
                finalImc = Number((peso / (alturaM * alturaM)).toFixed(2));
            } else {
                finalImc = null;
            }
        }

        if (!finalClasificacion) {
            if (finalImc === null) finalClasificacion = 'datos insuficientes';
            else if (finalImc < 18.5) finalClasificacion = 'bajo peso';
            else if (finalImc < 25) finalClasificacion = 'normal';
            else finalClasificacion = 'sobrepeso';
        }

        if (this.saludImcValue) this.saludImcValue.textContent = finalImc !== null ? String(finalImc) : '--';
        if (this.saludImcLabel) this.saludImcLabel.textContent = finalClasificacion;
        if (this.saludImcCard) {
            this.saludImcCard.classList.remove('imc-low', 'imc-normal', 'imc-high', 'imc-neutral');
            if (finalImc === null) this.saludImcCard.classList.add('imc-neutral');
            else if (finalImc < 18.5) this.saludImcCard.classList.add('imc-low');
            else if (finalImc < 25) this.saludImcCard.classList.add('imc-normal');
            else this.saludImcCard.classList.add('imc-high');
        }
    }

    setSaludMessage(text, isError) {
        if (!this.saludMessage) return;
        this.saludMessage.textContent = text;
        this.saludMessage.className = `mt-3 text-sm ${isError ? 'text-red-600' : 'text-emerald-600'}`;
    }

    async cargarAntecedentes() {
        if (window.viewingDependiente) return;
        if (!this.antecedentesContainer) return;

        try {
            const response = await fetch(`${ANTECEDENTES_API}?accion=obtenerMisAntecedentes`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });

            const raw = await response.text();
            let payload = null;

            try {
                payload = raw ? JSON.parse(raw) : null;
            } catch (_error) {
                throw new Error('Respuesta no válida del servidor.');
            }

            if (!response.ok || !payload.success) {
                throw new Error(payload?.error || `Error HTTP ${response.status}`);
            }

            this.renderizarAntecedentes(payload.data || []);
        } catch (error) {
            console.error('Error al cargar antecedentes:', error);
            this.antecedentesContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-2 opacity-50 text-red-500"></i>
                    <p>Error al cargar antecedentes: ${error.message}</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    renderizarAntecedentes(antecedentes) {
        if (window.viewingDependiente) return;
        if (!this.antecedentesContainer) return;

        if (!antecedentes || antecedentes.length === 0) {
            this.antecedentesContainer.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="info" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p>No tienes antecedentes familiares registrados.</p>
                    <p class="text-sm mt-1">Tu médico puede añadirlos durante la consulta.</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }

        this.antecedentesContainer.innerHTML = '';

        antecedentes.forEach(ant => {
            const card = document.createElement('div');
            card.className = 'bg-gradient-to-br from-white to-blue-50/40 border border-blue-100 rounded-xl p-4 shadow-sm';

            const parentescoColor = this.obtenerColorParentesco(ant.parentesco);
            const ladoFamiliar = ant.lado_familiar ? `<span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">${ant.lado_familiar}</span>` : '';
            const edadDiagnostico = ant.edad_diagnostico
                ? `<span class="text-sm text-gray-600">Diagnosticado a los ${ant.edad_diagnostico} años</span>`
                : '';
            const notas = ant.notas_adicionales ? `<p class="text-sm text-gray-600 mt-2 italic">${ant.notas_adicionales}</p>` : '';

            card.innerHTML = `
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-900">${ant.nombre_patologia || 'Sin nombre'}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">${ant.categoria || 'Sin categoría'}</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full ${parentescoColor} font-medium whitespace-nowrap">
                        ${ant.parentesco}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    ${ladoFamiliar}
                    ${edadDiagnostico}
                </div>
                ${notas}
            `;

            this.antecedentesContainer.appendChild(card);
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    obtenerColorParentesco(parentesco) {
        const colores = {
            'padre': 'bg-blue-100 text-blue-700',
            'madre': 'bg-pink-100 text-pink-700',
            'abuelo': 'bg-purple-100 text-purple-700',
            'abuela': 'bg-purple-100 text-purple-700',
            'hermano': 'bg-green-100 text-green-700',
            'hermana': 'bg-green-100 text-green-700',
            'tío': 'bg-amber-100 text-amber-700',
            'tía': 'bg-amber-100 text-amber-700'
        };
        return colores[parentesco?.toLowerCase()] || 'bg-gray-100 text-gray-700';
    }

    async cargarRecordatorios() {
        if (window.viewingDependiente) return;
        if (!this.recordatoriosList) return;
        try {
            const res = await this.apiRecordatorios('listar_paciente', 'GET', null, { pendientes: '0' });
            const data = res.data || [];
            this.renderRecordatorios(data);
            this.renderNotifications(data);
        } catch (error) {
            this.recordatoriosList.innerHTML = `<p class="text-sm text-red-600">No se pudieron cargar los recordatorios: ${error.message}</p>`;
        }
    }

    renderRecordatorios(list) {
        if (window.viewingDependiente) return;
        if (!this.recordatoriosList) return;
        const dismissed = this.getDismissed();
        const filtered = list.filter((item) => !dismissed.has(String(item.id_recordatorio)));

        if (!filtered.length) {
            this.recordatoriosList.innerHTML = '';
            if (this.recordatoriosEmpty) {
                this.recordatoriosEmpty.textContent = 'No tienes recordatorios.';
                this.recordatoriosEmpty.classList.remove('hidden');
            }
            return;
        }
        this.recordatoriosEmpty?.classList.add('hidden');
        this.recordatoriosList.innerHTML = '';

        filtered.forEach((item) => {
            const card = document.createElement('div');
            card.className = 'p-4 bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col gap-2';
            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-xs text-gray-500">${item.fecha_hora ? item.fecha_hora.replace('T', ' ').substring(0, 16) : this.formatearFecha(item.fecha_recordatorio)}</p>
                        <h4 class="text-base font-semibold text-gray-900">${item.razon || item.titulo || 'Recordatorio'}</h4>
                        <p class="text-sm text-gray-600">${item.descripcion || ''}</p>
                        <p class="text-xs text-gray-500">Creado por: ${item.medico_nombre || 'Tu medico'} ${item.medico_apellidos || ''}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">${item.estado || ''}</span>
                        ${item.estado === 'Completado' ? `<button class="text-gray-400 hover:text-red-500" data-dismiss="${item.id_recordatorio}" title="Ocultar"><i data-lucide="x" class="w-4 h-4"></i></button>` : ''}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-50 text-blue-700">${item.tipo_recordatorio || item.tipo || 'Otro'}</span>
                    <span class="px-2 py-1 text-xs rounded-full ${this.getEstadoChip(item.estado)}">${item.estado}</span>
                    <span class="text-xs text-gray-500">${item.tiempo_restante || ''}</span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    ${item.estado === 'Completado' ? '<span class="text-xs text-emerald-700">Marcado como completado</span>' : `<button class="px-3 py-1.5 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100" data-completar="${item.id_recordatorio}">¡Listo! Confirmar</button>`}
                </div>
            `;
            this.recordatoriosList.appendChild(card);
        });

        this.recordatoriosList.querySelectorAll('[data-completar]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-completar');
                await this.completarRecordatorio(id, btn);
            });
        });

        this.recordatoriosList.querySelectorAll('[data-dismiss]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-dismiss');
                this.dismissRecordatorio(id);
            });
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Panel de notificaciones (campana)
    getEstadoChip(estado) {
        const e = (estado || '').toString().toLowerCase();
        const map = {
            'pendiente': 'bg-amber-50 text-amber-700',
            'completado': 'bg-emerald-50 text-emerald-700',
            'vencido': 'bg-red-50 text-red-700',
            'cancelado': 'bg-gray-100 text-gray-700'
        };
        return map[e] || 'bg-gray-100 text-gray-700';
    }

    async completarRecordatorio(id, btn = null) {
        if (!id) return;
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Marcando...';
        }
        try {
            await this.apiRecordatorios('completar', 'POST', { id_recordatorio: id });
            await this.cargarRecordatorios();
        } catch (error) {
            alert('No se pudo marcar el recordatorio: ' + error.message);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '¡Listo! Confirmar';
            }
        }
    }

    // Notificaciones (campana)
    toggleNotificationPanel(forceState = null) {
        if (!this.notificationPanel) return;
        const shouldOpen = forceState !== null ? forceState : this.notificationPanel.classList.contains('hidden');
        this.notificationPanel.classList.toggle('hidden', !shouldOpen);
        if (shouldOpen && typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    renderNotifications(list) {
        if (!this.notificationList || !this.notificationBadge) return;
        const dismissed = this.getDismissed();
        const pending = list.filter(
            (item) => item.estado !== 'Completado' && !dismissed.has(String(item.id_recordatorio))
        );

        // Stat badge de recordatorios pendientes en la tarjeta de inicio
        const statEl = document.getElementById('statRecordatoriosPendientes');
        if (statEl) statEl.textContent = pending.length;

        // Incluir citas próximas en el contador del bell
        const citasProx = window.citasModule ? window.citasModule.getCitasProximas() : [];
        const total = pending.length + citasProx.length;

        if (total) {
            this.notificationBadge.textContent = total > 9 ? '9+' : total;
            this.notificationBadge.classList.remove('hidden');
        } else {
            this.notificationBadge.classList.add('hidden');
        }

        // Construir lista combinada: citas próximas + recordatorios pendientes
        this.notificationList.innerHTML = '';
        const totalItems = pending.length + citasProx.length;

        if (!totalItems) {
            this.notificationEmpty?.classList.remove('hidden');
            return;
        }
        this.notificationEmpty?.classList.add('hidden');

        // Citas próximas
        if (citasProx.length) {
            const sectionLabel = document.createElement('div');
            sectionLabel.className = 'px-4 pt-3 pb-1';
            sectionLabel.innerHTML = `<p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Próximas citas</p>`;
            this.notificationList.appendChild(sectionLabel);

            citasProx.forEach((cita) => {
                const fecha = new Date((cita.fecha_hora || '').replace(' ', 'T'));
                const fechaStr = isNaN(fecha) ? '' : fecha.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
                const horaStr = isNaN(fecha) ? '' : fecha.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                const medico = `Dr./Dra. ${cita.medico_nombre || ''} ${cita.medico_apellidos || ''}`.trim();

                const row = document.createElement('div');
                row.className = 'flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer';
                row.innerHTML = `
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#dbeafe;color:#1d4ed8">
                        <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${medico || 'Cita médica'}</p>
                        <p class="text-xs text-gray-500">${fechaStr} · ${horaStr}</p>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:${cita.estado === 'Confirmada' ? '#dcfce7;color:#15803d' : '#fef3c7;color:#b45309'}">${cita.estado}</span>
                `;
                row.addEventListener('click', () => {
                    this.toggleNotificationPanel(false);
                    document.getElementById('navCitasPaciente')?.click();
                });
                this.notificationList.appendChild(row);
            });
        }

        // Recordatorios pendientes
        if (pending.length) {
            const sectionLabel = document.createElement('div');
            sectionLabel.className = 'px-4 pt-3 pb-1';
            sectionLabel.innerHTML = `<p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Recordatorios</p>`;
            this.notificationList.appendChild(sectionLabel);

            pending.forEach((item) => {
                const fechaTxt = item.fecha_hora ? item.fecha_hora.replace('T', ' ').substring(0, 16) : '';
                const tipo = (item.tipo_recordatorio || item.tipo || 'otro').toLowerCase();
                const tipoMap = {
                    'medicamento': { bg: '#fef3c7', fg: '#b45309', icon: 'pill' },
                    'cita': { bg: '#dbeafe', fg: '#1d4ed8', icon: 'calendar' },
                    'examen': { bg: '#ede9fe', fg: '#6d28d9', icon: 'flask-conical' },
                    'seguimiento': { bg: '#dcfce7', fg: '#15803d', icon: 'activity' },
                    'dieta': { bg: '#fef3c7', fg: '#b45309', icon: 'apple' },
                    'ejercicio': { bg: '#dcfce7', fg: '#15803d', icon: 'dumbbell' }
                };
                const t = tipoMap[tipo] || { bg: '#f1f5f9', fg: '#475569', icon: 'bell-ring' };

                const row = document.createElement('div');
                row.className = 'flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors';
                row.innerHTML = `
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:${t.bg};color:${t.fg}">
                        <i data-lucide="${t.icon}" class="w-3.5 h-3.5"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${item.razon || item.titulo || 'Recordatorio'}</p>
                        <p class="text-xs text-gray-500">${fechaTxt}</p>
                    </div>
                    <button class="text-[11px] font-semibold px-2 py-1 rounded-md transition-colors" style="background:#f0fdf4;color:#15803d" data-completar-noti="${item.id_recordatorio}" title="Marcar como completado">
                        ✓
                    </button>
                `;
                this.notificationList.appendChild(row);
            });
        }

        this.notificationList.querySelectorAll('[data-completar-noti]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const id = btn.getAttribute('data-completar-noti');
                await this.completarRecordatorio(id, btn);
            });
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    // Dismiss de recordatorios completados (solo UI)
    getDismissed() {
        try {
            const raw = localStorage.getItem(this.dismissedKey);
            const arr = raw ? JSON.parse(raw) : [];
            return new Set(arr.map(String));
        } catch (_e) {
            return new Set();
        }
    }

    saveDismissed(set) {
        localStorage.setItem(this.dismissedKey, JSON.stringify(Array.from(set)));
    }

    dismissRecordatorio(id) {
        const set = this.getDismissed();
        set.add(String(id));
        this.saveDismissed(set);
        this.cargarRecordatorios();
    }

    async cargarConsultas() {
        if (window.viewingDependiente) return;
        try {
            const params = {};
            if (this.filtroDesde?.value) params.fecha_desde = this.filtroDesde.value;
            if (this.filtroHasta?.value) params.fecha_hasta = this.filtroHasta.value;
            const res = await this.api('mis_consultas', 'GET', null, params);
            this.consultas = (res.data || []).sort((a, b) => {
                const da = new Date(a.fecha).getTime();
                const db = new Date(b.fecha).getTime();
                return db - da;
            });
            this.renderConsultas();
            // Stat: última consulta
            const uc = document.getElementById('ultimaConsultaResumen');
            if (uc && this.consultas.length > 0) {
                const c = this.consultas[0];
                const med = `Dr./Dra. ${c.medico_nombre || ''} ${c.medico_apellidos || ''}`.trim();
                const fecha = this.formatearFecha(c.fecha);
                uc.innerHTML = `<span class="font-semibold">${fecha}</span><br><span class="text-xs text-gray-500">${med}</span>`;
            } else if (uc) {
                uc.textContent = 'Sin consultas registradas';
            }
        } catch (error) {
            this.renderError(error.message);
        }
    }

    renderConsultas() {
        if (window.viewingDependiente) return;
        if (!this.cardsContainer) return;
        this.cardsContainer.innerHTML = '';

        if (this.consultas.length === 0) {
            this.emptyEl?.classList.remove('hidden');
            return;
        }
        this.emptyEl?.classList.add('hidden');
        // Agrupar consultas por día para una visualización cronológica más ordenada
        const grouped = this.consultas.reduce((acc, c) => {
            const d = new Date(c.fecha);
            const key = d.toISOString().slice(0, 10); // YYYY-MM-DD
            if (!acc[key]) acc[key] = [];
            acc[key].push(c);
            return acc;
        }, {});

        // Ordenar los días de más reciente a más antiguo
        const days = Object.keys(grouped).sort((a, b) => (a < b ? 1 : -1));

        days.forEach(dayKey => {
            const dateObj = new Date(dayKey + 'T00:00:00');
            const dayLabel = dateObj.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' });

            const section = document.createElement('section');
            section.className = 'mb-6';

            const header = document.createElement('h4');
            header.className = 'text-sm font-semibold text-gray-600 mb-3';
            header.textContent = dayLabel;
            section.appendChild(header);

            const list = document.createElement('div');
            list.className = 'space-y-3';

            // Ordenar consultas del día por hora descendente (más reciente arriba)
            const consultasDia = grouped[dayKey].sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());

            consultasDia.forEach((consulta) => {
                const medico = `${consulta.medico_nombre || ''} ${consulta.medico_apellidos || ''}`.trim() || consulta.id_medico;
                const especialidad = consulta.especialidad || 'Especialidad no indicada';
                const diagnostico = consulta.diagnostico || 'Sin diagnostico';
                const fecha = this.formatearFecha(consulta.fecha);
                const tratamiento = consulta.tratamiento || 'Sin tratamiento';

                const card = document.createElement('button');
                card.type = 'button';
                card.className = 'w-full text-left group bg-white border border-gray-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-all';
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-blue-600 font-medium">${fecha}</p>
                            <h3 class="text-sm font-semibold text-gray-900 mt-1">${especialidad}</h3>
                            <p class="text-sm text-gray-600 mt-1">Médico: <span class="text-gray-800 font-medium">${medico}</span></p>
                        </div>
                        <div class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">Consulta</div>
                    </div>
                    <div class="mt-3 text-sm text-gray-700">
                        <p class="mb-1"><span class="font-semibold text-gray-800">Diagnóstico:</span> ${diagnostico}</p>
                        <p class="text-sm"><span class="font-semibold text-gray-800">Tratamiento:</span> ${tratamiento}</p>
                    </div>
                `;
                card.addEventListener('click', () => this.openDetailModal(consulta));
                list.appendChild(card);
            });

            section.appendChild(list);
            this.cardsContainer.appendChild(section);
        });
    }

    renderError(message) {
        if (!this.cardsContainer) return;
        this.cardsContainer.innerHTML = `<div class="col-span-full text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl p-3">${message}</div>`;
        this.emptyEl?.classList.add('hidden');
    }

    openDetailModal(consulta) {
        const medico = `${consulta.medico_nombre || ''} ${consulta.medico_apellidos || ''}`.trim() || consulta.id_medico;
        const especialidad = consulta.especialidad || '-';

        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '-';
        };

        set('consultaDetailTitle', `Consulta #${consulta.id_consulta || ''}`);
        set('detailFecha', this.formatearFecha(consulta.fecha));
        set('detailMedico', medico);
        set('detailEspecialidad', especialidad);
        set('detailDiagnostico', consulta.diagnostico || '-');
        set('detailTratamiento', consulta.tratamiento || '-');
        set('detailResultados', consulta.resultados || '-');
        set('detailObservaciones', consulta.observaciones || '-');

        if (this.modal) {
            this.modal.classList.add('active');
            this.modal.style.display = 'flex';
        }
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    closeDetailModal() {
        if (!this.modal) return;
        this.modal.classList.remove('active');
        this.modal.style.display = 'none';
    }

    async logout(event) {
        event.preventDefault();
        try {
            await this.api('logout', 'POST');
        } catch (_error) {
            // Redirigir igualmente
        }
        window.location.href = 'login.html';
    }

    formatearFecha(value) {
        if (!value) return '-';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('es-ES');
    }

    obtenerIniciales(nombreCompleto) {
        const words = (nombreCompleto || '').trim().split(/\s+/).filter(Boolean);
        if (words.length === 0) return 'PA';
        if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
        return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    }

    /**
     * Carga información adicional del usuario para elementos que antes eran estáticos
     */
    async cargarInformacionUsuario() {
        if (window.viewingDependiente) return;
        console.log('🔄 Cargando información adicional del usuario...');

        try {
            // Cargar médico de cabecera
            await this.cargarMedicoCabecera();

            // Cargar próxima cita
            await this.cargarProximaCita();

            // Cargar fechas de renovación
            await this.cargarFechasRenovacion();

            // Cargar datos para perfil pediátrico (si aplica)
            await this.cargarDatosPediatricos();

            console.log('✅ Información adicional cargada');
        } catch (error) {
            console.error('❌ Error cargando información adicional:', error);
        }
    }

    async cargarMedicoCabecera() {
        const elem = document.getElementById('medicoCabecera');
        if (!elem) return;

        try {
            // Obtener datos del usuario actual
            const usuarioDatos = JSON.parse(localStorage.getItem('usuario') || sessionStorage.getItem('usuario') || '{}');

            if (usuarioDatos.id_medico_general) {
                // Se podría hacer una API call para obtener info del médico
                elem.textContent = 'Dr. Médico de Cabecera'; // Placeholder
            } else {
                elem.textContent = 'No asignado';
            }
        } catch (error) {
            elem.textContent = 'No disponible';
        }
    }

    async cargarProximaCita() {
        const elem = document.getElementById('proximaCita');
        if (!elem) return;

        try {
            // Buscar próximos recordatorios de tipo 'Cita'
            if (this.recordatorios && this.recordatorios.length > 0) {
                const proximaCita = this.recordatorios
                    .filter(r => r.tipo_recordatorio === 'Cita' && r.estado !== 'Completado')
                    .sort((a, b) => new Date(a.fecha_hora) - new Date(b.fecha_hora))[0];

                if (proximaCita) {
                    elem.textContent = `Próxima cita: ${proximaCita.razon}`;
                } else {
                    elem.textContent = 'No hay citas programadas';
                }
            } else {
                elem.textContent = 'No hay citas programadas';
            }
        } catch (error) {
            elem.textContent = 'Cargando citas...';
        }
    }

    async cargarFechasRenovacion() {
        const elem = document.getElementById('renewalDate');
        if (!elem) return;

        try {
            // Ejemplo de cálculo de renovación
            const fechaRenovacion = new Date();
            fechaRenovacion.setDate(fechaRenovacion.getDate() + 15);
            elem.textContent = `Renovar en ${Math.ceil((fechaRenovacion - new Date()) / (1000 * 60 * 60 * 24))} días`;
        } catch (error) {
            elem.textContent = 'Fecha no disponible';
        }
    }

    async cargarDatosPediatricos() {
        // Solo ejecutar si no estamos viendo un dependiente
        if (window.viewingDependiente) return;

        // Elementos del perfil pediátrico que deben mostrarse como 'No aplica' para adultos
        const elementos = {
            'pediatraAsignado': '',
            'childStatus': '',
            'childLastRecord': '',
            'childPercentile': '',
            'childGrowthStatus': '',
            'childIMCStatus': '',
            'nextVaccination': '',
            'recentGrowthTitle': '',
            'recentGrowthDetails': '',
            'recentGrowthDate': ''
        };

        Object.entries(elementos).forEach(([id, texto]) => {
            const elem = document.getElementById(id);
            if (elem) {
                elem.textContent = texto;
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const app = new PacienteDashboard();
    await app.init();
});


