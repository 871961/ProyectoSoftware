/**
 * Gestor de Mis Pacientes
 * Maneja la lista de pacientes, búsqueda, selección y integración con modales
 */

class MisPacientesManager {
    constructor() {
        this.pacientes = [];
        this.pacienteSeleccionado = null;
        this.PERFIL_API = '/backend/src/controllers/PerfilSaludController.php';
        this.init();
    }

    init() {
        console.log('Inicializando MisPacientesManager...');
        this.cargarPacientes();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Búsqueda de pacientes
        const buscarInput = document.getElementById('buscarPaciente');
        if (buscarInput) {
            buscarInput.addEventListener('input', (e) => this.filtrarPacientes(e.target.value));
        }

        // Botones de modales
        const btnPerfilSalud = document.getElementById('btnEditarPerfilSalud');
        const btnAntecedentes = document.getElementById('btnAñadirAntecedentes');

        if (btnPerfilSalud) {
            btnPerfilSalud.addEventListener('click', () => this.abrirModalPerfilSalud());
        }
        if (btnAntecedentes) {
            btnAntecedentes.addEventListener('click', () => this.abrirModalAntecedentes());
        }

        // Cerrar modales
        const cerrarModalSalud = document.getElementById('cerrarModalSalud');
        const cerrarModalAntecedentes = document.getElementById('cerrarModalAntecedentes');
        const modalPerfilSalud = document.getElementById('modalPerfilSalud');
        const modalAntecedentes = document.getElementById('modalAntecedentes');

        if (cerrarModalSalud) {
            cerrarModalSalud.addEventListener('click', () => this.cerrarModal('modalPerfilSalud'));
        }
        if (cerrarModalAntecedentes) {
            cerrarModalAntecedentes.addEventListener('click', () => this.cerrarModal('modalAntecedentes'));
        }

        // Cerrar modal al hacer clic fuera
        if (modalPerfilSalud) {
            modalPerfilSalud.addEventListener('click', (e) => {
                if (e.target === modalPerfilSalud) this.cerrarModal('modalPerfilSalud');
            });
        }
        if (modalAntecedentes) {
            modalAntecedentes.addEventListener('click', (e) => {
                if (e.target === modalAntecedentes) this.cerrarModal('modalAntecedentes');
            });
        }
    }

    async cargarPacientes() {
        try {
            const response = await fetch(this.PERFIL_API + '?accion=obtenerPacientes');
            const data = await response.json();

            if (data.success) {
                this.pacientes = data.pacientes || [];
                this.renderizarListaPacientes(this.pacientes);
            } else {
                this.mostrarError('No se pudieron cargar los pacientes');
            }
        } catch (error) {
            console.error('Error al cargar pacientes:', error);
            this.mostrarError('Error de conexión al cargar pacientes');
        }
    }

    renderizarListaPacientes(pacientes) {
        const lista = document.getElementById('listaPacientes');
        if (!lista) return;

        if (pacientes.length === 0) {
            lista.innerHTML = `<p class="text-xs text-gray-400 py-6 text-center">No hay pacientes asignados</p>`;
            return;
        }

        lista.innerHTML = pacientes.map(p => {
            const iniciales = this.obtenerIniciales(p.nombre, p.apellidos);
            const color = this.obtenerColorAvatar(p.dni);
            return `
                <div class="paciente-item flex items-center gap-3 px-3 py-2.5 rounded-xl cursor-pointer transition-colors hover:bg-gray-50"
                    style="border:1px solid transparent"
                    data-dni="${p.dni}"
                    onclick="misPacientesManager.seleccionarPaciente('${p.dni}')">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
                        style="background:${color}">
                        ${iniciales}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${p.nombre} ${p.apellidos}</p>
                        <p class="text-xs text-gray-400 truncate">${p.dni}</p>
                    </div>
                </div>
            `;
        }).join('');
    }

    filtrarPacientes(busqueda) {
        const termino = busqueda.toLowerCase().trim();

        if (!termino) {
            this.renderizarListaPacientes(this.pacientes);
            return;
        }

        const filtrados = this.pacientes.filter(p => {
            return p.dni.toLowerCase().includes(termino) ||
                p.nombre.toLowerCase().includes(termino) ||
                p.apellidos.toLowerCase().includes(termino) ||
                `${p.nombre} ${p.apellidos}`.toLowerCase().includes(termino);
        });

        this.renderizarListaPacientes(filtrados);
    }

    async seleccionarPaciente(dni) {
        document.querySelectorAll('.paciente-item').forEach(item => {
            item.style.background = '';
            item.style.borderColor = 'transparent';
        });
        const item = document.querySelector(`[data-dni="${dni}"]`);
        if (item) {
            item.style.background = '#eef2ff';
            item.style.borderColor = '#c7d2fe';
        }

        // Buscar paciente
        const paciente = this.pacientes.find(p => p.dni === dni);
        if (!paciente) return;

        this.pacienteSeleccionado = paciente;

        // Mostrar información básica
        this.mostrarInfoPaciente(paciente);

        // Cargar perfil de salud
        await this.cargarPerfilSalud(dni);

        // Cargar antecedentes
        if (window.antecedentesManager) {
            await window.antecedentesManager.cargarAntecedentes(dni);
            this.mostrarAntecedentesResumen();
        }
    }

    mostrarInfoPaciente(paciente) {
        document.getElementById('sinSeleccion').classList.add('hidden');
        document.getElementById('infoPaciente').classList.remove('hidden');

        const iniciales = this.obtenerIniciales(paciente.nombre, paciente.apellidos);
        const color = this.obtenerColorAvatar(paciente.dni);

        const avatar = document.getElementById('avatarPaciente');
        avatar.className = 'w-14 h-14 rounded-xl flex items-center justify-center text-white font-semibold text-lg flex-shrink-0';
        avatar.style.background = color;
        avatar.textContent = iniciales;

        document.getElementById('nombrePaciente').textContent = `${paciente.nombre} ${paciente.apellidos}`;
        document.getElementById('dniPaciente').textContent = `DNI ${paciente.dni}`;
        document.getElementById('emailPaciente').textContent = paciente.email || '';
    }

    async cargarPerfilSalud(dni) {
        try {
            const response = await fetch(`${this.PERFIL_API}?accion=obtener&dni_paciente=${dni}`);
            const data = await response.json();

            if (data.success && data.perfil) {
                this.mostrarPerfilSalud(data.perfil);
            } else {
                this.mostrarPerfilSaludVacio();
            }
        } catch (error) {
            console.error('Error al cargar perfil de salud:', error);
            this.mostrarPerfilSaludVacio();
        }
    }

    mostrarPerfilSalud(perfil) {
        // Mostrar datos básicos
        document.getElementById('alturaDisplay').textContent = perfil.altura_cm ? `${perfil.altura_cm} cm` : '--';
        document.getElementById('pesoDisplay').textContent = perfil.peso_kg ? `${perfil.peso_kg} kg` : '--';

        // Calcular y mostrar IMC
        if (perfil.altura_cm && perfil.peso_kg) {
            const imc = (perfil.peso_kg / Math.pow(perfil.altura_cm / 100, 2)).toFixed(1);
            document.getElementById('imcDisplay').textContent = imc;
        } else {
            document.getElementById('imcDisplay').textContent = '--';
        }

        document.getElementById('actividadDisplay').textContent = perfil.actividad_fisica || '--';

        // Mostrar alergias si existen
        const alergiaDiv = document.getElementById('alergiaDisplay');
        if (perfil.alergias && perfil.alergias.trim()) {
            alergiaDiv.classList.remove('hidden');
            alergiaDiv.querySelector('p.text-sm').textContent = perfil.alergias;
        } else {
            alergiaDiv.classList.add('hidden');
        }
    }

    mostrarPerfilSaludVacio() {
        document.getElementById('alturaDisplay').textContent = '--';
        document.getElementById('pesoDisplay').textContent = '--';
        document.getElementById('imcDisplay').textContent = '--';
        document.getElementById('actividadDisplay').textContent = '--';
        document.getElementById('alergiaDisplay').classList.add('hidden');
    }

    mostrarAntecedentesResumen() {
        const container = document.getElementById('antecedentesResumen');
        if (!container) return;

        const data = window.antecedentesManager?.antecedentesActuales || [];

        if (data.length === 0) {
            container.innerHTML = '<p class="text-xs text-gray-400">Sin antecedentes registrados</p>';
            return;
        }

        container.innerHTML = data.slice(0, 4).map(ant => {
            const pat = ant.nombre_patologia || ant.nombre_enfermedad || 'Desconocida';
            const par = (ant.parentesco || '').replace(/_/g,' ');
            return `<div class="flex items-center justify-between py-1.5" style="border-bottom:1px solid #f0f4f8">
                <span class="text-xs font-medium text-gray-800">${pat}</span>
                <span class="text-xs text-gray-400 ml-3">${par}</span>
            </div>`;
        }).join('') +
        (data.length > 4 ? `<p class="text-xs text-gray-400 pt-1.5">+${data.length-4} más</p>` : '');
    }

    abrirModalPerfilSalud() {
        if (!this.pacienteSeleccionado) {
            alert('Selecciona un paciente primero');
            return;
        }

        const modal = document.getElementById('modalPerfilSalud');
        const nombreDisplay = document.getElementById('modalSaludPacienteNombre');
        const pacienteInput = document.getElementById('saludPacienteSelect');

        if (nombreDisplay) {
            nombreDisplay.textContent = `${this.pacienteSeleccionado.nombre} ${this.pacienteSeleccionado.apellidos}`;
        }
        if (pacienteInput) {
            pacienteInput.value = this.pacienteSeleccionado.dni;
        }

        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        // Cargar datos existentes si hay
        const btnCargar = document.getElementById('saludCargarBtnMedico');
        if (btnCargar) {
            btnCargar.click();
        }

        lucide.createIcons();
    }

    abrirModalAntecedentes() {
        if (!this.pacienteSeleccionado) {
            alert('Selecciona un paciente primero');
            return;
        }

        const modal = document.getElementById('modalAntecedentes');
        const nombreDisplay = document.getElementById('modalAntPacienteNombre');
        const pacienteInput = document.getElementById('antPacienteSelect');

        if (nombreDisplay) {
            nombreDisplay.textContent = `${this.pacienteSeleccionado.nombre} ${this.pacienteSeleccionado.apellidos}`;
        }
        if (pacienteInput) {
            pacienteInput.value = this.pacienteSeleccionado.dni;
        }

        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        lucide.createIcons();
    }

    cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        // Recargar información del paciente después de cerrar modal
        if (this.pacienteSeleccionado) {
            this.seleccionarPaciente(this.pacienteSeleccionado.dni);
        }
    }

    obtenerIniciales(nombre, apellidos) {
        const n = (nombre || '').charAt(0).toUpperCase();
        const a = (apellidos || '').split(' ')[0]?.charAt(0).toUpperCase() || '';
        return n + a;
    }

    obtenerColorAvatar(dni) {
        const colores = [
            '#1d4ed8', '#0f766e', '#7c3aed',
            '#be185d', '#0369a1', '#b45309',
            '#15803d', '#9333ea'
        ];
        const index = (dni || '').charCodeAt(0) % colores.length;
        return colores[index];
    }

    mostrarError(mensaje) {
        const lista = document.getElementById('listaPacientes');
        if (lista) {
            lista.innerHTML = `<p class="text-xs text-red-500 py-4 text-center">${mensaje}</p>`;
        }
    }
}

// Inicializar cuando el DOM esté listo
let misPacientesManager;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        misPacientesManager = new MisPacientesManager();
    });
} else {
    misPacientesManager = new MisPacientesManager();
}
