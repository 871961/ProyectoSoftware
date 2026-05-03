/**
 * citas.js — Módulo de citas para el dashboard de paciente
 * Gestiona solicitar, ver y cancelar citas médicas.
 */

const API = '/backend/src/controllers/CitasController.php';

class CitasModule {
    constructor() {
        this.medicos = [];
        this.citas   = [];
        this.dni     = null; // paciente activo (propio o dependiente)
    }

    async init(dniPaciente) {
        this.dni = dniPaciente || null;
        await this.cargarMedicos();
        await this.cargarCitas();
        this._bindForm();
        this._bindCancelar();
    }

    setDni(dni) {
        this.dni = dni;
    }

    // ── Carga médicos para el selector del formulario ─────────────────────────
    // Si this.dni corresponde a un dependiente, el backend devolverá solo el pediatra.
    // Si es un paciente normal, devolverá su médico general + todos los especialistas.
    async cargarMedicos() {
        try {
            const url = this.dni
                ? `${API}?accion=obtener_medicos&id_paciente=${encodeURIComponent(this.dni)}`
                : `${API}?accion=obtener_medicos`;
            const res  = await fetch(url);
            const data = await res.json();
            if (data.success) {
                this.medicos = data.data || [];
                this._rellenarSelectMedicos();
            }
        } catch (e) {
            console.error('Error al cargar médicos:', e);
        }
    }

    _rellenarSelectMedicos() {
        const sel = document.getElementById('citaMedicoSelect');
        if (!sel) return;
        if (!this.medicos.length) {
            sel.innerHTML = '<option value="">No hay médicos disponibles</option>';
            return;
        }
        sel.innerHTML = '<option value="">Selecciona un médico...</option>';
        // Agrupar: primero el general/pediatra del paciente, luego especialistas
        const generales = this.medicos.filter(m => m.tipo_medico === 'general');
        const especialistas = this.medicos.filter(m => m.tipo_medico !== 'general');

        const buildOption = (m, prefix = '') => {
            const opt = document.createElement('option');
            opt.value = m.id_medico;
            const esp = m.especialidad ? ` · ${m.especialidad}` : '';
            opt.textContent = `${prefix}Dr./Dra. ${m.nombre} ${m.apellidos}${esp}`;
            return opt;
        };

        if (generales.length) {
            const gGen = document.createElement('optgroup');
            gGen.label = generales.length === 1 && generales[0].tipo_medico === 'general'
                ? 'Tu médico de cabecera'
                : 'Tu pediatra';
            generales.forEach(m => gGen.appendChild(buildOption(m)));
            sel.appendChild(gGen);
        }
        if (especialistas.length) {
            const gEsp = document.createElement('optgroup');
            gEsp.label = 'Especialistas';
            especialistas.forEach(m => gEsp.appendChild(buildOption(m)));
            sel.appendChild(gEsp);
        }
    }

    // ── Carga y renderiza la lista de citas del paciente ──────────────────────
    async cargarCitas(dni) {
        if (dni) this.dni = dni;
        const url = this.dni
            ? `${API}?accion=listar_paciente&id_paciente=${encodeURIComponent(this.dni)}`
            : `${API}?accion=listar_paciente`;

        try {
            const res  = await fetch(url);
            const data = await res.json();
            if (data.success) {
                this.citas = data.data || [];
                this._renderLista();
                this._actualizarProximaCita();
            }
        } catch (e) {
            console.error('Error al cargar citas:', e);
        }
    }

    _renderLista() {
        const container = document.getElementById('citasList');
        const empty     = document.getElementById('citasEmpty');
        if (!container) return;

        container.innerHTML = '';

        const pendientes  = this.citas.filter(c => c.estado === 'Pendiente');
        const confirmadas = this.citas.filter(c => c.estado === 'Confirmada');
        const historial   = this.citas.filter(c => ['Cancelada', 'Completada'].includes(c.estado));

        if (this.citas.length === 0) {
            container.classList.add('hidden');
            if (empty) empty.classList.remove('hidden');
            return;
        }

        container.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');

        if (confirmadas.length > 0) {
            container.appendChild(this._grupo('Citas confirmadas', confirmadas, 'confirmed'));
        }
        if (pendientes.length > 0) {
            container.appendChild(this._grupo('Solicitudes pendientes', pendientes, 'pending'));
        }
        if (historial.length > 0) {
            container.appendChild(this._grupo('Historial', historial, 'history'));
        }
    }

    _grupo(titulo, citas, tipo) {
        const wrap = document.createElement('div');
        wrap.className = 'mb-6';
        wrap.innerHTML = `<h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">${titulo}</h3>`;
        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-1 md:grid-cols-2 gap-3';
        citas.forEach(c => grid.appendChild(this._card(c)));
        wrap.appendChild(grid);
        return wrap;
    }

    _card(cita) {
        const div = document.createElement('div');
        const { bg, badge, badgeTxt } = this._estadoStyles(cita.estado);

        const fecha  = this._formatFecha(cita.fecha_hora);
        const hora   = this._formatHora(cita.fecha_hora);
        const medico = `Dr./Dra. ${cita.medico_nombre} ${cita.medico_apellidos}`;
        const esp    = cita.medico_especialidad || (cita.tipo_medico === 'general' ? 'Medicina General' : '');

        const puedeCancel = ['Pendiente', 'Confirmada'].includes(cita.estado);

        div.className = `relative bg-white border border-gray-200 rounded-xl p-4 ${bg}`;
        div.innerHTML = `
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">${fecha}</p>
                        <p class="text-sm font-bold text-gray-900">${hora}</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded-full ${badge}">${badgeTxt}</span>
            </div>
            <p class="text-sm font-semibold text-gray-900 mb-1">${medico}</p>
            ${esp ? `<p class="text-xs text-gray-500 mb-2">${esp}</p>` : ''}
            <p class="text-sm text-gray-700 mb-3">${this._esc(cita.motivo)}</p>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">${cita.tipo === 'Telematica' ? '💻 Telemática' : '🏥 Presencial'}</span>
                ${puedeCancel ? `
                <button data-id="${cita.id_cita}"
                    class="btn-cancelar-cita ml-auto text-xs text-red-500 hover:text-red-700 font-medium">
                    Cancelar
                </button>` : ''}
            </div>
            ${cita.notas_cancelacion ? `<p class="text-xs text-gray-400 mt-2 italic">${this._esc(cita.notas_cancelacion)}</p>` : ''}
        `;
        return div;
    }

    _estadoStyles(estado) {
        const map = {
            'Pendiente':  { bg: '',             badge: 'bg-amber-100 text-amber-700',  badgeTxt: 'Pendiente' },
            'Confirmada': { bg: 'border-l-4 border-l-green-400', badge: 'bg-green-100 text-green-700', badgeTxt: 'Confirmada' },
            'Cancelada':  { bg: 'opacity-60',   badge: 'bg-red-100 text-red-700',     badgeTxt: 'Cancelada' },
            'Completada': { bg: '',             badge: 'bg-gray-100 text-gray-600',   badgeTxt: 'Completada' },
        };
        return map[estado] || map['Pendiente'];
    }

    // ── Bind formulario de solicitud ─────────────────────────────────────────
    _bindForm() {
        const form = document.getElementById('citaForm');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this._solicitarCita(form);
        });
    }

    async _solicitarCita(form) {
        const btn = document.getElementById('citaSubmitBtn');
        const msg = document.getElementById('citaFormMsg');
        if (btn) btn.disabled = true;
        if (msg) { msg.textContent = ''; msg.className = 'text-sm'; }

        const body = {
            id_medico:   parseInt(form.id_medico.value),
            fecha:       form.fecha.value,
            hora:        form.hora.value,
            motivo:      form.motivo.value,
            tipo:        form.tipo.value,
            id_paciente: this.dni || undefined,
        };

        try {
            const res  = await fetch(`${API}?accion=solicitar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (data.success) {
                if (msg) { msg.textContent = data.mensaje; msg.className = 'text-sm text-green-600'; }
                form.reset();
                this._cerrarModal();
                await this.cargarCitas();
            } else {
                if (msg) { msg.textContent = data.mensaje; msg.className = 'text-sm text-red-600'; }
            }
        } catch (e) {
            if (msg) { msg.textContent = 'Error de red'; msg.className = 'text-sm text-red-600'; }
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // ── Bind botones cancelar ─────────────────────────────────────────────────
    _bindCancelar() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-cancelar-cita');
            if (!btn) return;
            const id = parseInt(btn.dataset.id);
            if (!confirm('¿Seguro que quieres cancelar esta cita?')) return;
            await this._cancelarCita(id);
        });
    }

    async _cancelarCita(id) {
        try {
            const res  = await fetch(`${API}?accion=cancelar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_cita: id })
            });
            const data = await res.json();
            if (data.success) await this.cargarCitas();
        } catch (e) {
            console.error('Error al cancelar cita:', e);
        }
    }

    // ── Próxima cita para el panel de inicio ─────────────────────────────────
    _actualizarProximaCita() {
        const prox = this.citas.find(c => ['Pendiente', 'Confirmada'].includes(c.estado));
        const el   = document.getElementById('proximaCitaInfo');
        if (!el) return;

        if (!prox) {
            el.textContent = 'Sin citas próximas';
            return;
        }
        const fecha  = this._formatFecha(prox.fecha_hora);
        const hora   = this._formatHora(prox.fecha_hora);
        const medico = `Dr./Dra. ${prox.medico_nombre} ${prox.medico_apellidos}`;
        el.innerHTML = `<span class="font-semibold">${fecha} a las ${hora}</span><br><span class="text-xs text-gray-500">${medico}</span>`;
    }

    // ── Retorna citas confirmadas/pendientes para el bell de notificaciones ──
    getCitasProximas() {
        const ahora = new Date();
        return this.citas
            .filter(c => ['Pendiente', 'Confirmada'].includes(c.estado) && new Date(c.fecha_hora) > ahora)
            .slice(0, 5);
    }

    // ── Modal ─────────────────────────────────────────────────────────────────
    async abrirModal() {
        // Recargar médicos por si cambió el contexto (paciente <-> dependiente)
        await this.cargarMedicos();

        const m = document.getElementById('citaModal');
        if (m) m.classList.remove('hidden');
        if (m) m.classList.add('flex');

        // Pre-fill min date
        const fechaInput = document.getElementById('citaFechaInput');
        if (fechaInput) fechaInput.min = new Date().toISOString().split('T')[0];

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    _cerrarModal() {
        const m = document.getElementById('citaModal');
        if (m) { m.classList.add('hidden'); m.classList.remove('flex'); }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    _formatFecha(ts) {
        if (!ts) return '--';
        const d = new Date(ts.replace(' ', 'T'));
        return d.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    }

    _formatHora(ts) {
        if (!ts) return '--:--';
        const d = new Date(ts.replace(' ', 'T'));
        return d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    _esc(s) {
        if (!s) return '';
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
}

// Instancia global
window.citasModule = new CitasModule();
