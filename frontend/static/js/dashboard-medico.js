/*
    Archivo: dashboard-medico.js
    Descripcion: Dashboard medico conectado a sesion y consultas reales
*/

const CONSULTAS_API = '/backend/src/controllers/ConsultasController.php';

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

class MedicoDashboard {
    constructor() {
        this.usuario = null;
        this.consultas = [];
        this.pacientes = [];
        this.editingConsultaId = null;

        this.form = document.getElementById('consultaForm');
        this.messageEl = document.getElementById('consultaMessage');
        this.pacienteSelect = document.getElementById('id_paciente');
        this.consultasBody = document.getElementById('consultasMedicoBody');
        this.logoutLink = document.getElementById('logoutLink');
        this.submitBtn = document.getElementById('submitConsultaBtn');
        this.cancelEditBtn = document.getElementById('cancelEditBtn');
        this.idConsultaInput = document.getElementById('id_consulta');
        this.toggleConsultaBtn = document.getElementById('toggleConsultaModule');
        this.toggleConsultaLabel = document.getElementById('toggleConsultaLabel');
        this.consultaPanel = document.getElementById('consultaModulePanel');
        this.filtroDesde = document.getElementById('filtroDesdeMedico');
        this.filtroHasta = document.getElementById('filtroHastaMedico');
        this.filtroBtn = document.getElementById('aplicarFiltroMedico');
        this.limpiarFiltroBtn = document.getElementById('limpiarFiltroMedico');
        this.isSaving = false;
    }

    async init() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        new SidebarManager().init();
        this.bindEvents();
        await this.cargarSesion();
        await Promise.all([this.cargarPacientes(), this.cargarConsultas()]);
    }

    bindEvents() {
        this.form?.addEventListener('submit', (e) => this.guardarConsulta(e));
        this.logoutLink?.addEventListener('click', (e) => this.logout(e));
        this.cancelEditBtn?.addEventListener('click', () => this.cancelarEdicion());
        this.toggleConsultaBtn?.addEventListener('click', () => this.toggleConsultaPanel());
        this.filtroBtn?.addEventListener('click', () => this.cargarConsultas());
        this.limpiarFiltroBtn?.addEventListener('click', () => {
            if (this.filtroDesde) this.filtroDesde.value = '';
            if (this.filtroHasta) this.filtroHasta.value = '';
            this.cargarConsultas();
        });
    }

    toggleConsultaPanel(forceOpen = null) {
        if (!this.consultaPanel) return;
        const shouldOpen = forceOpen === null ? this.consultaPanel.classList.contains('hidden') : forceOpen;
        this.consultaPanel.classList.toggle('hidden', !shouldOpen);
        if (this.toggleConsultaLabel) {
            this.toggleConsultaLabel.textContent = shouldOpen ? 'Ocultar formulario' : 'Nueva consulta';
        }
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
            throw new Error('Respuesta no valida del servidor. Recarga la pagina e intentalo otra vez.');
        }
        if (!response.ok || !payload.success) {
            throw new Error(payload.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    async cargarSesion() {
        try {
            const res = await this.api('sesion');
            this.usuario = res.usuario;
            if (this.usuario.tipo !== 'medico') {
                window.location.href = 'login.html';
                return;
            }
            this.pintarUsuario();
        } catch (error) {
            window.location.href = 'login.html';
        }
    }

    pintarUsuario() {
        const nombre = this.usuario.nombre || '';
        const especialidad = this.usuario.especialidad || 'Medico';
        const iniciales = this.obtenerIniciales(nombre);

        const profileName = document.getElementById('profileName');
        const profileRole = document.getElementById('profileRole');
        const profileAvatar = document.getElementById('profileAvatar');
        const welcomeTitle = document.getElementById('welcomeTitle');
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');

        if (profileName) profileName.textContent = `Dr./Dra. ${nombre}`;
        if (profileRole) profileRole.textContent = especialidad;
        if (profileAvatar) profileAvatar.textContent = iniciales;
        if (welcomeTitle) welcomeTitle.textContent = `Bienvenido, Dr./Dra. ${nombre}`;
        if (welcomeSubtitle) welcomeSubtitle.textContent = 'Gestiona tus consultas y registra nuevas.';
    }

    async cargarPacientes() {
        try {
            const res = await this.api('listar_pacientes');
            this.pacientes = res.data || [];
            this.renderPacientes();
        } catch (error) {
            this.setMessage(error.message, true);
        }
    }

    renderPacientes() {
        if (!this.pacienteSelect) return;
        this.pacienteSelect.innerHTML = '<option value="">Selecciona un paciente...</option>';
        this.pacientes.forEach((paciente) => {
            const option = document.createElement('option');
            option.value = paciente.dni;
            option.textContent = `${paciente.nombre} ${paciente.apellidos} (${paciente.dni})`;
            this.pacienteSelect.appendChild(option);
        });
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
            this.setMessage(error.message, true);
        }
    }

    renderConsultas() {
        if (!this.consultasBody) return;
        this.consultasBody.innerHTML = '';

        if (this.consultas.length === 0) {
            this.consultasBody.innerHTML = '<tr><td colspan="5" class="px-4 py-3 text-sm text-gray-500">Aun no hay consultas registradas.</td></tr>';
            return;
        }

        this.consultas.forEach((consulta) => {
            const fecha = this.formatearFecha(consulta.fecha);
            const paciente = `${consulta.paciente_nombre || ''} ${consulta.paciente_apellidos || ''}`.trim() || consulta.id_paciente;
            const diagnostico = consulta.diagnostico || '-';
            const tratamiento = consulta.tratamiento || '-';
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100';
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-700">${fecha}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${paciente}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${diagnostico}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${tratamiento}</td>
                <td class="px-4 py-3 text-sm text-gray-700">
                    <button class="text-blue-600 hover:text-blue-800 font-medium" data-edit-id="${consulta.id_consulta}">Editar</button>
                    <button class="text-red-600 hover:text-red-800 font-medium ml-3" data-delete-id="${consulta.id_consulta}">Eliminar</button>
                </td>
            `;
            this.consultasBody.appendChild(row);
        });

        this.consultasBody.querySelectorAll('[data-edit-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-edit-id'));
                this.iniciarEdicion(id);
            });
        });
        this.consultasBody.querySelectorAll('[data-delete-id]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = Number(btn.getAttribute('data-delete-id'));
                await this.eliminarConsulta(id);
            });
        });
    }

    async eliminarConsulta(idConsulta) {
        const ok = window.confirm('¿Seguro que quieres eliminar esta consulta? Esta accion no se puede deshacer.');
        if (!ok) return;
        try {
            await this.api('eliminar_consulta', 'POST', { id_consulta: idConsulta });
            this.setMessage('Consulta eliminada correctamente.', false);
            if (this.editingConsultaId === Number(idConsulta)) {
                this.cancelarEdicion();
            }
            await this.cargarConsultas();
        } catch (error) {
            this.setMessage(error.message, true);
        }
    }

    iniciarEdicion(idConsulta) {
        const consulta = this.consultas.find((c) => Number(c.id_consulta) === Number(idConsulta));
        if (!consulta || !this.form) return;

        this.toggleConsultaPanel(true);
        this.editingConsultaId = Number(idConsulta);
        if (this.idConsultaInput) this.idConsultaInput.value = String(this.editingConsultaId);
        this.pacienteSelect.value = consulta.id_paciente || '';
        this.pacienteSelect.disabled = true;
        this.form.elements.fecha.value = this.toInputDateTime(consulta.fecha);
        this.form.elements.diagnostico.value = consulta.diagnostico || '';
        this.form.elements.tratamiento.value = consulta.tratamiento || '';
        this.form.elements.resultados.value = consulta.resultados || '';
        this.form.elements.observaciones.value = consulta.observaciones || '';

        if (this.submitBtn) this.submitBtn.textContent = 'Actualizar consulta';
        this.cancelEditBtn?.classList.remove('hidden');
        this.setMessage(`Editando consulta #${this.editingConsultaId}`, false);
        this.form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    cancelarEdicion() {
        this.editingConsultaId = null;
        if (this.idConsultaInput) this.idConsultaInput.value = '';
        if (this.form) this.form.reset();
        if (this.pacienteSelect) this.pacienteSelect.disabled = false;
        if (this.submitBtn) this.submitBtn.textContent = 'Guardar consulta';
        this.cancelEditBtn?.classList.add('hidden');
        this.setMessage('', false);
    }

    async guardarConsulta(event) {
        event.preventDefault();
        if (this.isSaving) return;
        this.isSaving = true;
        if (this.submitBtn) this.submitBtn.disabled = true;
        const formData = new FormData(this.form);
        const payload = {
            id_paciente: formData.get('id_paciente'),
            fecha: this.toSqlDateTime(formData.get('fecha')),
            diagnostico: formData.get('diagnostico'),
            tratamiento: formData.get('tratamiento'),
            resultados: formData.get('resultados'),
            observaciones: formData.get('observaciones')
        };

        try {
            if (this.editingConsultaId) {
                payload.id_consulta = this.editingConsultaId;
                await this.api('actualizar_consulta', 'POST', payload);
                this.setMessage('Consulta actualizada correctamente.', false);
                this.cancelarEdicion();
            } else {
                await this.api('crear_consulta', 'POST', payload);
                this.setMessage('Consulta registrada correctamente.', false);
                this.form.reset();
                this.toggleConsultaPanel(false);
            }
            await this.cargarConsultas();
        } catch (error) {
            this.setMessage(error.message, true);
        } finally {
            this.isSaving = false;
            if (this.submitBtn) this.submitBtn.disabled = false;
        }
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

    setMessage(text, isError) {
        if (!this.messageEl) return;
        this.messageEl.textContent = text;
        this.messageEl.className = `mt-3 text-sm ${isError ? 'text-red-600' : 'text-green-600'}`;
    }

    obtenerIniciales(nombreCompleto) {
        const words = (nombreCompleto || '').trim().split(/\s+/).filter(Boolean);
        if (words.length === 0) return 'MD';
        if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
        return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    }

    formatearFecha(value) {
        if (!value) return '-';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('es-ES');
    }

    toSqlDateTime(value) {
        if (!value) return null;
        return value.replace('T', ' ');
    }

    toInputDateTime(value) {
        if (!value) return '';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        const year = d.getFullYear();
        const month = pad(d.getMonth() + 1);
        const day = pad(d.getDate());
        const hh = pad(d.getHours());
        const mm = pad(d.getMinutes());
        return `${year}-${month}-${day}T${hh}:${mm}`;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const app = new MedicoDashboard();
    await app.init();
});
