/*
    Archivo: dashboard-medico.js
    Descripcion: Dashboard medico conectado a sesion y consultas reales
*/

console.log('*** dashboard-medico.js cargado ***');

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
        console.log('SidebarManager init() llamado');
        this.openBtn?.addEventListener('click', () => this.open());
        this.closeBtn?.addEventListener('click', () => this.close());
        this.sidebarOverlay?.addEventListener('click', () => this.close());
        console.log('Llamando a initTabNavigation()');
        this.initTabNavigation();
        console.log('initTabNavigation() completado');
    }

    initTabNavigation() {
        const navLinks = document.querySelectorAll('.nav-link[data-tab]');
        const tabs = document.querySelectorAll('.tab-content');

        console.log('Inicializando navegación por tabs');
        console.log('Enlaces encontrados:', navLinks.length);
        console.log('Tabs encontrados:', tabs.length);

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = link.getAttribute('data-tab');

                console.log('Click en tab:', targetTab);

                // Ocultar todos los tabs
                tabs.forEach(tab => {
                    tab.classList.add('hidden');
                });

                // Remover active de todos los links
                navLinks.forEach(l => {
                    l.classList.remove('active');
                });

                // Mostrar el tab seleccionado
                const selectedTab = document.getElementById(`tab-${targetTab}`);
                if (selectedTab) {
                    selectedTab.classList.remove('hidden');
                    console.log('Tab mostrado:', `tab-${targetTab}`);
                } else {
                    console.error('Tab no encontrado:', `tab-${targetTab}`);
                }

                // Activar el link seleccionado
                link.classList.add('active');

                // Cerrar sidebar en móvil
                this.close();

                // Reinicializar iconos de Lucide si están disponibles
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            });
        });

        // Mostrar el primer tab por defecto
        if (tabs.length > 0) {
            tabs[0].classList.remove('hidden');
        }
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
        this.saludForm = document.getElementById('saludMedicoForm');
        this.saludPacienteSelect = document.getElementById('saludPacienteSelect');
        this.saludMessage = document.getElementById('saludMessageMedico');
        this.saludCargarBtn = document.getElementById('saludCargarBtnMedico');
        this.saludAlturaInput = document.getElementById('saludAlturaMedico');
        this.saludPesoInput = document.getElementById('saludPesoMedico');
        this.saludImcValue = document.getElementById('saludImcValueMedico');
        this.saludImcLabel = document.getElementById('saludImcLabelMedico');
        this.saludImcCard = document.getElementById('saludImcCardMedico');
        this.isSaving = false;
    }

    async init() {
        console.log('MedicoDashboard init() llamado');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        console.log('Inicializando SidebarManager...');
        new SidebarManager().init();
        console.log('SidebarManager inicializado');

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
        this.saludForm?.addEventListener('submit', (e) => this.guardarPerfilSalud(e));
        this.saludCargarBtn?.addEventListener('click', () => this.cargarPerfilSaludSeleccionado());
        this.saludPacienteSelect?.addEventListener('change', () => this.cargarPerfilSaludSeleccionado());
        this.saludAlturaInput?.addEventListener('input', () => this.actualizarImcVisual());
        this.saludPesoInput?.addEventListener('input', () => this.actualizarImcVisual());
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
            if (this.saludPacienteSelect && this.saludPacienteSelect.value) {
                await this.cargarPerfilSaludSeleccionado();
            }
        } catch (error) {
            this.setMessage(error.message, true);
        }
    }

    renderPacientes() {
        if (!this.pacienteSelect) return;
        this.pacienteSelect.innerHTML = '<option value="">Selecciona un paciente...</option>';
        if (this.saludPacienteSelect) {
            this.saludPacienteSelect.innerHTML = '<option value="">Selecciona un paciente...</option>';
        }
        this.pacientes.forEach((paciente) => {
            const option = document.createElement('option');
            option.value = paciente.dni;
            option.textContent = `${paciente.nombre} ${paciente.apellidos} (${paciente.dni})`;
            this.pacienteSelect.appendChild(option);
            if (this.saludPacienteSelect) {
                const healthOption = option.cloneNode(true);
                this.saludPacienteSelect.appendChild(healthOption);
            }
        });
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

    async cargarPerfilSaludSeleccionado() {
        if (!this.saludPacienteSelect) return;
        const idPaciente = this.saludPacienteSelect.value;
        if (!idPaciente) {
            this.limpiarPerfilSaludForm();
            this.setSaludMessage('Selecciona un paciente para cargar su perfil.', false);
            return;
        }

        try {
            const res = await this.perfilApi('obtener_por_paciente', 'GET', null, { id_paciente: idPaciente });
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
        this.saludForm.elements.alergias.value = perfil.alergias ?? '';
        this.saludForm.elements.enfermedades.value = perfil.enfermedades ?? '';
        this.saludForm.elements.consumo_tabaco.value = perfil.consumo_tabaco ?? '';
        this.saludForm.elements.consumo_alcohol.value = perfil.consumo_alcohol ?? '';
        this.saludForm.elements.actividad_fisica.value = perfil.actividad_fisica ?? '';
        this.actualizarImcVisual(perfil.imc, perfil.clasificacion_imc);
    }

    limpiarPerfilSaludForm() {
        if (!this.saludForm) return;
        this.saludForm.elements.altura_cm.value = '';
        this.saludForm.elements.peso_kg.value = '';
        this.saludForm.elements.alergias.value = '';
        this.saludForm.elements.enfermedades.value = '';
        this.saludForm.elements.consumo_tabaco.value = '';
        this.saludForm.elements.consumo_alcohol.value = '';
        this.saludForm.elements.actividad_fisica.value = '';
        this.actualizarImcVisual(null, 'datos insuficientes');
    }

    async guardarPerfilSalud(event) {
        event.preventDefault();
        if (!this.saludForm || !this.saludPacienteSelect) return;
        const idPaciente = this.saludPacienteSelect.value;
        if (!idPaciente) {
            this.setSaludMessage('Debes seleccionar un paciente.', true);
            return;
        }

        const formData = new FormData(this.saludForm);
        const payload = {
            id_paciente: idPaciente,
            altura_cm: formData.get('altura_cm'),
            peso_kg: formData.get('peso_kg'),
            alergias: formData.get('alergias'),
            enfermedades: formData.get('enfermedades'),
            consumo_tabaco: formData.get('consumo_tabaco'),
            consumo_alcohol: formData.get('consumo_alcohol'),
            actividad_fisica: formData.get('actividad_fisica')
        };

        try {
            const res = await this.perfilApi('guardar_por_medico', 'POST', payload);
            this.pintarPerfilSalud(res.data || {});
            this.setSaludMessage('Perfil de salud guardado correctamente.', false);
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
                    <button class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium" data-edit-id="${consulta.id_consulta}">Editar</button>
                    <button class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-medium ml-2" data-delete-id="${consulta.id_consulta}">Eliminar</button>
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
    console.log('DOMContentLoaded - Iniciando aplicación');
    const app = new MedicoDashboard();
    await app.init();
    console.log('Aplicación inicializada completamente');
});
