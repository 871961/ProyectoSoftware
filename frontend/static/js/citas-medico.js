/**
 * citas-medico.js — Módulo de agenda de citas para el dashboard de médico
 * Gestiona confirmar, cancelar y completar citas.
 */

const CITAS_API = '../backend/src/controllers/CitasController.php';

class CitasMedicoModule {
    constructor() {
        this.citas   = [];
        this.filtros = { fecha: null, solo_pendientes: false };
    }

    async init() {
        this._bindFiltros();
        this._bindAcciones();
        await this.cargar();
    }

    // ── Carga citas del médico ────────────────────────────────────────────────
    async cargar(fecha, soloPendientes) {
        if (fecha !== undefined)           this.filtros.fecha            = fecha;
        if (soloPendientes !== undefined)  this.filtros.solo_pendientes  = soloPendientes;

        let url = `${CITAS_API}?accion=listar_medico`;
        if (this.filtros.fecha)           url += `&fecha=${this.filtros.fecha}`;
        if (this.filtros.solo_pendientes) url += `&pendientes=1`;

        try {
            const res  = await fetch(url);
            const data = await res.json();
            if (data.success) {
                this.citas = data.data || [];
                this._render();
                this._actualizarContadores();
            }
        } catch (e) {
            console.error('Error al cargar citas del médico:', e);
        }
    }

    // ── Render de la agenda ───────────────────────────────────────────────────
    _render() {
        const container = document.getElementById('agendaList');
        const empty     = document.getElementById('agendaEmpty');
        if (!container) return;

        container.innerHTML = '';

        if (this.citas.length === 0) {
            container.classList.add('hidden');
            if (empty) empty.classList.remove('hidden');
            return;
        }
        container.classList.remove('hidden');
        if (empty) empty.classList.add('hidden');

        // Agrupar por fecha
        const grupos = {};
        this.citas.forEach(c => {
            const d = c.fecha_hora.split(' ')[0];
            if (!grupos[d]) grupos[d] = [];
            grupos[d].push(c);
        });

        Object.entries(grupos).sort(([a],[b]) => a.localeCompare(b)).forEach(([fecha, list]) => {
            const section = document.createElement('div');
            section.className = 'mb-6';

            const fechaLabel = new Date(fecha + 'T00:00:00').toLocaleDateString('es-ES', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });

            section.innerHTML = `
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-sm font-semibold text-gray-700 capitalize">${fechaLabel}</span>
                    <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">${list.length} cita${list.length !== 1 ? 's' : ''}</span>
                </div>
            `;

            const rows = document.createElement('div');
            rows.className = 'space-y-2';
            list.forEach(c => rows.appendChild(this._row(c)));
            section.appendChild(rows);
            container.appendChild(section);
        });
    }

    _row(cita) {
        const div     = document.createElement('div');
        const hora    = this._formatHora(cita.fecha_hora);
        const pacient = `${cita.paciente_nombre} ${cita.paciente_apellidos}`;
        const { badge, badgeTxt } = this._estadoBadge(cita.estado);

        const acciones = this._botonesAccion(cita);

        div.className = 'flex flex-col sm:flex-row sm:items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-200 transition-colors';
        div.innerHTML = `
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">
                    ${pacient.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase()}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">${this._esc(pacient)}</p>
                    <p class="text-xs text-gray-500 truncate">${this._esc(cita.motivo)}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
                <span class="text-sm font-bold text-gray-800">${hora}</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold ${badge}">${badgeTxt}</span>
                <span class="text-xs text-gray-400">${cita.tipo === 'Telematica' ? '💻' : '🏥'}</span>
                <div class="flex gap-1">${acciones}</div>
            </div>
        `;
        return div;
    }

    _botonesAccion(cita) {
        let btns = '';
        if (cita.estado === 'Pendiente') {
            btns += `<button data-id="${cita.id_cita}" data-accion="confirmar"
                class="btn-accion-cita text-xs px-2.5 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-medium">
                Confirmar</button>`;
            btns += `<button data-id="${cita.id_cita}" data-accion="cancelar"
                class="btn-accion-cita text-xs px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-medium">
                Rechazar</button>`;
        }
        if (cita.estado === 'Confirmada') {
            btns += `<button data-id="${cita.id_cita}" data-accion="completar"
                class="btn-accion-cita text-xs px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-medium">
                Completar</button>`;
            btns += `<button data-id="${cita.id_cita}" data-accion="cancelar"
                class="btn-accion-cita text-xs px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg font-medium">
                Cancelar</button>`;
        }
        return btns;
    }

    _estadoBadge(estado) {
        const map = {
            'Pendiente':  { badge: 'bg-amber-100 text-amber-700', badgeTxt: 'Pendiente' },
            'Confirmada': { badge: 'bg-green-100 text-green-700', badgeTxt: 'Confirmada' },
            'Cancelada':  { badge: 'bg-red-100 text-red-600',    badgeTxt: 'Cancelada' },
            'Completada': { badge: 'bg-gray-100 text-gray-600',  badgeTxt: 'Completada' },
        };
        return map[estado] || map['Pendiente'];
    }

    // ── Actualiza contadores en los stat-cards del dashboard ─────────────────
    _actualizarContadores() {
        const pendientes  = this.citas.filter(c => c.estado === 'Pendiente').length;
        const hoyStr      = new Date().toISOString().split('T')[0];
        const hoy         = this.citas.filter(c => c.fecha_hora.startsWith(hoyStr) && c.estado !== 'Cancelada').length;

        const elPend = document.getElementById('statCitasPendientes');
        const elHoy  = document.getElementById('statCitasHoy');
        if (elPend) elPend.textContent = pendientes;
        if (elHoy)  elHoy.textContent  = hoy;
    }

    // ── Filtros de la agenda ──────────────────────────────────────────────────
    _bindFiltros() {
        const fechaIn = document.getElementById('agendaFiltroFecha');
        const checkP  = document.getElementById('agendaFiltroPendientes');
        const btnHoy  = document.getElementById('agendaBtnHoy');
        const btnTodo = document.getElementById('agendaBtnTodo');

        if (fechaIn) {
            fechaIn.addEventListener('change', () => this.cargar(fechaIn.value || null));
        }
        if (checkP) {
            checkP.addEventListener('change', () => this.cargar(undefined, checkP.checked));
        }
        if (btnHoy) {
            btnHoy.addEventListener('click', () => {
                const hoy = new Date().toISOString().split('T')[0];
                if (fechaIn) fechaIn.value = hoy;
                this.cargar(hoy);
            });
        }
        if (btnTodo) {
            btnTodo.addEventListener('click', () => {
                if (fechaIn) fechaIn.value = '';
                if (checkP) checkP.checked = false;
                this.cargar(null, false);
            });
        }
    }

    // ── Acciones sobre citas ──────────────────────────────────────────────────
    _bindAcciones() {
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-accion-cita');
            if (!btn) return;

            const id     = parseInt(btn.dataset.id);
            const accion = btn.dataset.accion;

            if (accion === 'confirmar') {
                await this._accion('confirmar', { id_cita: id });
            } else if (accion === 'completar') {
                await this._accion('completar', { id_cita: id });
            } else if (accion === 'cancelar') {
                const notas = prompt('Motivo de cancelación (opcional):') ?? '';
                await this._accion('cancelar', { id_cita: id, notas });
            }
        });
    }

    async _accion(accion, body) {
        try {
            const res  = await fetch(`${CITAS_API}?accion=${accion}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) await this.cargar();
        } catch (e) {
            console.error(`Error en acción ${accion}:`, e);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
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

window.citasMedicoModule = new CitasMedicoModule();
