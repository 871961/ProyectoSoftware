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
            lista.innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="users" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                    <p>No hay pacientes asignados</p>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        lista.innerHTML = pacientes.map(p => {
            const iniciales = this.obtenerIniciales(p.nombre, p.apellidos);
            const colorBg = this.obtenerColorAvatar(p.dni);

            return `
                <div class="paciente-item p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors border border-transparent hover:border-gray-200"
                    data-dni="${p.dni}"
                    onclick="misPacientesManager.seleccionarPaciente('${p.dni}')">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br ${colorBg} rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                            ${iniciales}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">${p.nombre} ${p.apellidos}</p>
                            <p class="text-xs text-gray-500 truncate">${p.dni}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        lucide.createIcons();
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
        // Actualizar UI de selección
        document.querySelectorAll('.paciente-item').forEach(item => {
            item.classList.remove('bg-blue-50', 'border-blue-300');
        });
        const item = document.querySelector(`[data-dni="${dni}"]`);
        if (item) {
            item.classList.add('bg-blue-50', 'border-blue-300');
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
        const colorBg = this.obtenerColorAvatar(paciente.dni);

        // Avatar e información básica
        const avatar = document.getElementById('avatarPaciente');
        avatar.className = `w-16 h-16 bg-gradient-to-br ${colorBg} rounded-full flex items-center justify-center text-white font-bold text-xl`;
        avatar.textContent = iniciales;

        document.getElementById('nombrePaciente').textContent = `${paciente.nombre} ${paciente.apellidos}`;
        document.getElementById('dniPaciente').textContent = `DNI: ${paciente.dni}`;
        document.getElementById('emailPaciente').textContent = paciente.email || 'Sin email registrado';
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

        const antecedentesData = window.antecedentesManager?.antecedentesActuales || [];

        if (antecedentesData.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500">No hay antecedentes registrados</p>';
            return;
        }

        container.innerHTML = antecedentesData.slice(0, 3).map(ant => `
            <div class="flex items-center space-x-2 text-sm">
                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                <span class="font-medium text-gray-900">${ant.nombre_patologia || ant.nombre_enfermedad || 'Desconocida'}</span>
                <span class="text-gray-500">-</span>
                <span class="text-gray-600">${ant.parentesco}</span>
            </div>
        `).join('');

        if (antecedentesData.length > 3) {
            container.innerHTML += `<p class="text-xs text-gray-500">+ ${antecedentesData.length - 3} más...</p>`;
        }
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
            'from-blue-500 to-blue-700',
            'from-green-500 to-green-700',
            'from-purple-500 to-purple-700',
            'from-pink-500 to-pink-700',
            'from-indigo-500 to-indigo-700',
            'from-red-500 to-red-700',
            'from-yellow-500 to-yellow-700',
            'from-teal-500 to-teal-700'
        ];
        const index = (dni || '').charCodeAt(0) % colores.length;
        return colores[index];
    }

    mostrarError(mensaje) {
        const lista = document.getElementById('listaPacientes');
        if (lista) {
            lista.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-2"></i>
                    <p>${mensaje}</p>
                </div>
            `;
            lucide.createIcons();
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
