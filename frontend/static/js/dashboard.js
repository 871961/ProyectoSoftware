/*
    Archivo: dashboard.js
    Descripcion: Dashboard paciente con historial de consultas en cards y detalle
*/

const CONSULTAS_API = '/backend/src/controllers/ConsultasController.php';
const PERFIL_API = '/backend/src/controllers/PerfilSaludController.php';

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
    }

    async init() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        new SidebarManager().init();
        this.bindEvents();
        await this.cargarSesion();
        await Promise.all([this.cargarConsultas(), this.cargarPerfilSalud()]);
    }

    bindEvents() {
        this.logoutLink?.addEventListener('click', (e) => this.logout(e));
        this.filtroBtn?.addEventListener('click', () => this.cargarConsultas());
        this.limpiarFiltroBtn?.addEventListener('click', () => {
            if (this.filtroDesde) this.filtroDesde.value = '';
            if (this.filtroHasta) this.filtroHasta.value = '';
            this.cargarConsultas();
        });

        this.closeModalBtn?.addEventListener('click', () => this.closeDetailModal());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeDetailModal();
        });
        this.saludForm?.addEventListener('submit', (e) => this.guardarPerfilSalud(e));
        this.saludReloadBtn?.addEventListener('click', () => this.cargarPerfilSalud());
        this.saludAlturaInput?.addEventListener('input', () => this.actualizarImcVisual());
        this.saludPesoInput?.addEventListener('input', () => this.actualizarImcVisual());
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

    async cargarSesion() {
        try {
            const res = await this.api('sesion');
            this.usuario = res.usuario;

            if (this.usuario.tipo !== 'paciente') {
                window.location.href = 'login.html';
                return;
            }

            this.pintarUsuario();
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
        try {
            const res = await this.perfilApi('obtener_mi_perfil');
            this.pintarPerfilSalud(res.data || {});
            this.setSaludMessage('Perfil de salud cargado.', false);
        } catch (error) {
            this.setSaludMessage(error.message, true);
        }
    }

    pintarPerfilSalud(perfil) {
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

    async cargarConsultas() {
        try {
            const params = {};
            if (this.filtroDesde?.value) params.fecha_desde = this.filtroDesde.value;
            if (this.filtroHasta?.value) params.fecha_hasta = this.filtroHasta.value;
            const res = await this.api('mis_consultas', 'GET', null, params);
            this.consultas = res.data || [];
            this.renderConsultas();
        } catch (error) {
            this.renderError(error.message);
        }
    }

    renderConsultas() {
        if (!this.cardsContainer) return;
        this.cardsContainer.innerHTML = '';

        if (this.consultas.length === 0) {
            this.emptyEl?.classList.remove('hidden');
            return;
        }
        this.emptyEl?.classList.add('hidden');

        this.consultas.forEach((consulta) => {
            const medico = `${consulta.medico_nombre || ''} ${consulta.medico_apellidos || ''}`.trim() || consulta.id_medico;
            const especialidad = consulta.especialidad || 'Especialidad no indicada';
            const diagnostico = consulta.diagnostico || 'Sin diagnostico';
            const fecha = this.formatearFecha(consulta.fecha);
            const tratamiento = consulta.tratamiento || 'Sin tratamiento';

            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'text-left group bg-gradient-to-br from-white to-blue-50/60 border border-blue-100 rounded-2xl p-5 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all';
            card.innerHTML = `
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-blue-600 font-semibold">${fecha}</p>
                        <h3 class="text-lg font-semibold text-gray-900 mt-1">${especialidad}</h3>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-medium">Consulta</span>
                </div>
                <p class="text-sm text-gray-500 mb-2">Médico: <span class="text-gray-700 font-medium">${medico}</span></p>
                <p class="text-sm text-gray-600 line-clamp-2"><span class="font-semibold text-gray-800">Diagnóstico:</span> ${diagnostico}</p>
                <p class="text-sm text-gray-600 mt-2 line-clamp-1"><span class="font-semibold text-gray-800">Tratamiento:</span> ${tratamiento}</p>
                <div class="mt-4 text-sm text-blue-700 font-medium group-hover:text-blue-800">Ver detalle completo</div>
            `;
            card.addEventListener('click', () => this.openDetailModal(consulta));
            this.cardsContainer.appendChild(card);
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
}

document.addEventListener('DOMContentLoaded', async () => {
    const app = new PacienteDashboard();
    await app.init();
});
