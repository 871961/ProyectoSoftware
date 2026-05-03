/**
 * Archivo: antecedentes.js
 * Descripción: Gestión de antecedentes familiares para médicos
 * Fecha: Marzo 2026
 */

const ANTECEDENTES_API = '/backend/src/controllers/AntecedentesController.php';

class AntecedentesManager {
    constructor() {
        this.form = document.getElementById('antecedentesForm');
        this.messageEl = document.getElementById('antecedentesMessage');
        this.listaEl = document.getElementById('antecedentesLista');
        this.pacienteSelect = document.getElementById('antPacienteSelect');
        this.enfermedadSelect = document.getElementById('antEnfermedadSelect');

        this.enfermedades = [];
        this.antecedentesActuales = [];
    }

    async init() {
        await this.cargarEnfermedades();
        this.bindEvents();
    }

    bindEvents() {
        this.form?.addEventListener('submit', (e) => this.guardarAntecedente(e));
        this.pacienteSelect?.addEventListener('change', () => this.cargarAntecedentes());
    }

    /**
     * Carga el catálogo de enfermedades
     */
    async cargarEnfermedades() {
        try {
            const response = await fetch(`${ANTECEDENTES_API}?accion=obtenerEnfermedades`, {
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data?.error || data?.mensaje || `Error HTTP ${response.status}`);
            }

            if (data.success && data.data) {
                this.enfermedades = data.data;
                this.renderizarSelectEnfermedades();
            }
        } catch (error) {
            console.error('Error al cargar enfermedades:', error);
        }
    }

    /**
     * Renderiza el select de enfermedades
     */
    renderizarSelectEnfermedades() {
        if (!this.enfermedadSelect) return;

        this.enfermedadSelect.innerHTML = '<option value="">Selecciona una enfermedad...</option>';

        // Agrupar por categoría
        const porCategoria = {};
        this.enfermedades.forEach(enf => {
            const cat = enf.categoria || 'Otras';
            if (!porCategoria[cat]) porCategoria[cat] = [];
            porCategoria[cat].push(enf);
        });

        // Crear optgroups
        Object.keys(porCategoria).sort().forEach(categoria => {
            const optgroup = document.createElement('optgroup');
            optgroup.label = categoria;

            porCategoria[categoria].forEach(enf => {
                const option = document.createElement('option');
                option.value = enf.id_enfermedad;
                option.textContent = enf.nombre;
                optgroup.appendChild(option);
            });

            this.enfermedadSelect.appendChild(optgroup);
        });
    }

    /**
     * Carga los antecedentes del paciente seleccionado
     * @param {string} dniParam - DNI del paciente (opcional, si no se provee usa el del select)
     */
    async cargarAntecedentes(dniParam = null) {
        const dniPaciente = dniParam || this.pacienteSelect?.value;

        if (!dniPaciente) {
            this.listaEl.innerHTML = '<p class="text-sm text-gray-500">Selecciona un paciente para ver sus antecedentes familiares</p>';
            return;
        }

        try {
            const response = await fetch(`${ANTECEDENTES_API}?accion=obtenerPorPaciente&dni_paciente=${dniPaciente}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data?.error || data?.mensaje || `Error HTTP ${response.status}`);
            }

            if (data.success && data.data) {
                this.antecedentesActuales = data.data;
                this.renderizarAntecedentes();
            }
        } catch (error) {
            console.error('Error al cargar antecedentes:', error);
            this.mostrarMensaje('Error al cargar antecedentes', true);
        }
    }

    /**
     * Renderiza la lista de antecedentes
     */
    renderizarAntecedentes() {
        if (!this.listaEl) return;

        const countEl = document.getElementById('antecedentesCount');
        if (countEl) countEl.textContent = this.antecedentesActuales.length ? `${this.antecedentesActuales.length} registro${this.antecedentesActuales.length === 1 ? '' : 's'}` : '';

        if (this.antecedentesActuales.length === 0) {
            this.listaEl.innerHTML = `
                <div class="text-center py-10 rounded-xl" style="background:#fafbfc;border:1px dashed #e5e7eb">
                    <p class="text-xs text-gray-400">Sin antecedentes registrados</p>
                </div>`;
            return;
        }

        this.listaEl.innerHTML = this.antecedentesActuales.map(ant => {
            const par = this.getParentescoLabel(ant.parentesco);
            const pat = ant.nombre_patologia || ant.nombre_enfermedad || 'Enfermedad desconocida';
            const lado = ant.lado_familiar ? this.capitalize(ant.lado_familiar) : null;
            const edad = ant.edad_diagnostico || ant.edad_diagnóstico;
            const notas = ant.notas_adicionales;
            const colorMeta = this.getColorByParentesco(ant.parentesco);

            return `
            <div class="rounded-xl p-4 transition-colors hover:shadow-sm" style="background:#fff;border:1px solid #e8edf2">
                <div class="flex items-start gap-3">
                    <!-- Icono de parentesco -->
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:${colorMeta.bg};color:${colorMeta.fg}">
                        <i data-lucide="${colorMeta.icon}" class="w-4 h-4"></i>
                    </div>
                    <!-- Contenido -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold text-gray-900 leading-tight">${pat}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">${par}</p>
                            </div>
                            <button onclick="antecedentesManager.eliminarAntecedente(${ant.id_antecedente})"
                                title="Eliminar antecedente"
                                class="flex-shrink-0 inline-flex items-center justify-center w-7 h-7 rounded-lg transition-colors hover:bg-red-50 text-gray-300 hover:text-red-500">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <!-- Chips de metadata -->
                        <div class="flex items-center gap-1.5 flex-wrap mt-2">
                            ${lado ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#f0f4ff;color:#1d4ed8">
                                <i data-lucide="users" class="w-2.5 h-2.5"></i>${lado}
                            </span>` : ''}
                            ${edad ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#f0fdf4;color:#15803d">
                                <i data-lucide="calendar" class="w-2.5 h-2.5"></i>${edad} años
                            </span>` : ''}
                        </div>
                        ${notas ? `<p class="text-xs text-gray-500 mt-2 pt-2 leading-relaxed" style="border-top:1px dashed #f0f4f8">
                            <span class="font-medium text-gray-400">Notas:</span> ${notas}
                        </p>` : ''}
                    </div>
                </div>
            </div>`;
        }).join('');

        // Reinicializar iconos de Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    /**
     * Devuelve color e icono según parentesco
     */
    getColorByParentesco(parentesco) {
        const map = {
            'padre':            { bg: '#eff6ff', fg: '#1d4ed8', icon: 'user' },
            'madre':            { bg: '#fdf2f8', fg: '#be185d', icon: 'user' },
            'hermano':          { bg: '#f0f9ff', fg: '#0369a1', icon: 'users' },
            'hermana':          { bg: '#fdf4ff', fg: '#a21caf', icon: 'users' },
            'abuelo_paterno':   { bg: '#f5f3ff', fg: '#6d28d9', icon: 'user-cog' },
            'abuela_paterna':   { bg: '#fef2f2', fg: '#b91c1c', icon: 'user-cog' },
            'abuelo_materno':   { bg: '#ecfdf5', fg: '#047857', icon: 'user-cog' },
            'abuela_materna':   { bg: '#fff7ed', fg: '#c2410c', icon: 'user-cog' },
            'tio':              { bg: '#f8fafc', fg: '#475569', icon: 'user-round' },
            'tia':              { bg: '#f8fafc', fg: '#475569', icon: 'user-round' },
            'primo':            { bg: '#fafaf9', fg: '#57534e', icon: 'user-round' },
            'prima':            { bg: '#fafaf9', fg: '#57534e', icon: 'user-round' }
        };
        return map[parentesco] || { bg: '#f5f5f5', fg: '#374151', icon: 'circle-help' };
    }

    /**
     * Guarda un nuevo antecedente
     */
    async guardarAntecedente(event) {
        event.preventDefault();

        const formData = new FormData(this.form);
        const data = {
            id_paciente: formData.get('id_paciente'),
            id_enfermedad: formData.get('id_enfermedad'),
            parentesco: formData.get('parentesco'),
            lado_familiar: formData.get('lado_familiar') || null,
            edad_diagnostico: formData.get('edad_diagnostico') ? parseInt(formData.get('edad_diagnostico')) : null,
            notas_adicionales: formData.get('notas_adicionales') || null
        };

        try {
            const response = await fetch(`${ANTECEDENTES_API}?accion=registrar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarMensaje('Antecedente registrado correctamente', false);
                this.form.reset();
                // Mantener el paciente seleccionado
                this.pacienteSelect.value = data.id_paciente;
                await this.cargarAntecedentes();
            } else {
                this.mostrarMensaje(result.error || 'Error al guardar', true);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarMensaje('Error al guardar antecedente', true);
        }
    }

    /**
     * Elimina un antecedente
     */
    async eliminarAntecedente(idAntecedente) {
        if (!confirm('¿Estás seguro de eliminar este antecedente familiar?')) {
            return;
        }

        try {
            const response = await fetch(`${ANTECEDENTES_API}?accion=eliminar&id_antecedente=${idAntecedente}`, {
                method: 'GET'
            });

            const result = await response.json();

            if (result.success) {
                this.mostrarMensaje('Antecedente eliminado correctamente', false);
                await this.cargarAntecedentes();
            } else {
                this.mostrarMensaje(result.error || 'Error al eliminar', true);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarMensaje('Error al eliminar antecedente', true);
        }
    }

    /**
     * Muestra un mensaje
     */
    mostrarMensaje(texto, esError) {
        if (!this.messageEl) return;

        this.messageEl.textContent = texto;
        this.messageEl.className = `mt-3 text-sm ${esError ? 'text-red-600' : 'text-green-600'}`;

        setTimeout(() => {
            this.messageEl.textContent = '';
        }, 5000);
    }

    /**
     * Obtiene la etiqueta legible del parentesco
     */
    getParentescoLabel(parentesco) {
        const labels = {
            'padre': 'Padre',
            'madre': 'Madre',
            'hermano': 'Hermano',
            'hermana': 'Hermana',
            'abuelo_paterno': 'Abuelo Paterno',
            'abuela_paterna': 'Abuela Paterna',
            'abuelo_materno': 'Abuelo Materno',
            'abuela_materna': 'Abuela Materna',
            'tio': 'Tío',
            'tia': 'Tía',
            'primo': 'Primo',
            'prima': 'Prima',
            'otro': 'Otro'
        };
        return labels[parentesco] || parentesco;
    }

    /**
     * Capitaliza primera letra
     */
    capitalize(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * Formatea una fecha
     */
    formatearFecha(fecha) {
        if (!fecha) return '-';
        const d = new Date(fecha);
        if (isNaN(d.getTime())) return fecha;
        return d.toLocaleDateString('es-ES');
    }

    /**
     * Sincroniza el select de pacientes con el del perfil de salud
     */
    sincronizarPacientes(selectOrigen) {
        if (!this.pacienteSelect || !selectOrigen) return;

        // Copiar opciones
        this.pacienteSelect.innerHTML = selectOrigen.innerHTML;

        // Sincronizar cambios
        selectOrigen.addEventListener('change', () => {
            this.pacienteSelect.value = selectOrigen.value;
            this.cargarAntecedentes();
        });
    }
}

// Inicializar cuando el DOM esté listo
let antecedentesManager;
window.antecedentesManager = null;

document.addEventListener('DOMContentLoaded', () => {
    antecedentesManager = new AntecedentesManager();
    window.antecedentesManager = antecedentesManager;
    antecedentesManager.init();
});
