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
            const response = await fetch(`${ANTECEDENTES_API}?accion=obtenerEnfermedades`);
            const data = await response.json();

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
            const response = await fetch(`${ANTECEDENTES_API}?accion=obtenerPorPaciente&dni_paciente=${dniPaciente}`);
            const data = await response.json();

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

        if (this.antecedentesActuales.length === 0) {
            this.listaEl.innerHTML = '<p class="text-sm text-gray-500">No hay antecedentes familiares registrados</p>';
            return;
        }

        this.listaEl.innerHTML = this.antecedentesActuales.map(ant => {
            const par = this.getParentescoLabel(ant.parentesco);
            const pat = ant.nombre_patologia || ant.nombre_enfermedad || 'Enfermedad desconocida';
            const lado = ant.lado_familiar ? this.capitalize(ant.lado_familiar) : null;
            const edad = ant.edad_diagnostico || ant.edad_diagnóstico;
            const notas = ant.notas_adicionales;
            return `
            <div class="flex items-start justify-between gap-3 py-3" style="border-bottom:1px solid #f0f4f8">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap mb-0.5">
                        <span class="text-xs font-semibold" style="color:#1d4ed8">${par}</span>
                        ${lado ? `<span class="text-xs text-gray-400">·</span><span class="text-xs text-gray-500">${lado}</span>` : ''}
                        ${edad ? `<span class="text-xs text-gray-400">·</span><span class="text-xs text-gray-500">${edad} años</span>` : ''}
                    </div>
                    <p class="text-sm font-medium text-gray-900">${pat}</p>
                    ${notas ? `<p class="text-xs text-gray-500 mt-0.5 truncate">${notas}</p>` : ''}
                </div>
                <button onclick="antecedentesManager.eliminarAntecedente(${ant.id_antecedente})"
                    class="flex-shrink-0 p-1.5 text-gray-300 hover:text-red-500 rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>`;
        }).join('');

        // Reinicializar iconos de Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
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
