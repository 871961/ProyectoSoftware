/*
    Archivo: dependiente-dashboard.js
    Descripcion: Dashboard infantil para dependientes
*/

const DEPENDIENTES_API = '/backend/src/controllers/DependienteController.php';

class DependienteDashboard {
    constructor() {
        const params = new URLSearchParams(window.location.search);
        this.id = params.get('id') || sessionStorage.getItem('dependiente_id');
        this.backBtn = document.getElementById('backToPacienteBtn');
        this.errorBanner = document.getElementById('errorBanner');
    }

    init() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        this.bindEvents();
        if (!this.id) {
            this.mostrarError('No se encontro el dependiente seleccionado.');
            this.irAPaciente();
            return;
        }
        sessionStorage.setItem('dependiente_id', String(this.id));
        this.cargarDatos();
    }

    bindEvents() {
        this.backBtn?.addEventListener('click', () => this.irAPaciente());
    }

    irAPaciente() {
        sessionStorage.removeItem('dependiente_id');
        window.location.href = 'paciente.html';
    }

    async api(accion, method = 'GET', data = null, params = {}) {
        const query = new URLSearchParams({ accion, ...params }).toString();
        const url = `${DEPENDIENTES_API}?${query}`;
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
        if (!response.ok || !payload?.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    async cargarDatos() {
        try {
            const [depRes, perfilRes, consultasRes, vacunasRes] = await Promise.all([
                this.api('obtener', 'GET', null, { id: this.id }),
                this.api('obtener_perfil_salud', 'GET', null, { id: this.id }),
                this.api('obtener_consultas', 'GET', null, { id: this.id }),
                this.api('obtener_vacunas', 'GET', null, { id: this.id })
            ]);

            const dependiente = depRes.data || {};
            const perfil = perfilRes.data || {};
            const consultas = consultasRes.data || [];
            const vacunas = vacunasRes.data || [];

            this.renderHeader(dependiente, consultas, vacunas);
            this.renderCrecimiento(perfil);
            this.renderConsultas(consultas);
            this.renderVacunas(vacunas);
            this.renderAlergias(dependiente, perfil);
            this.renderBasics(dependiente);

            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        } catch (error) {
            console.error('Error cargando dashboard dependiente:', error);
            this.mostrarError(error.message);
        }
    }

    renderHeader(dependiente, consultas, vacunas) {
        const nombreCompleto = dependiente.nombre_completo || `${dependiente.nombre || ''} ${dependiente.apellidos || ''}`.trim() || 'Dependiente';
        const edad = dependiente.edad ?? this.calcularEdad(dependiente.fecha_nacimiento);
        const fechaNacimiento = dependiente.fecha_nacimiento ? new Date(dependiente.fecha_nacimiento).toLocaleDateString('es-ES') : '--';

        this.setText('depName', nombreCompleto);
        this.setText('depMeta', `Edad: ${edad !== null ? edad + ' anos' : 'Sin datos'} · Nacimiento: ${fechaNacimiento}`);
        this.setText('depPediatra', `Pediatra: ${dependiente.pediatra_nombre_completo || 'No asignado'}`);

        const avatar = document.getElementById('depAvatar');
        if (avatar) {
            avatar.textContent = this.obtenerIniciales(nombreCompleto);
        }

        const proxima = (consultas || [])
            .filter(c => c.fecha && new Date(c.fecha) >= new Date())
            .sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
        const proximaFecha = proxima.length > 0 ? new Date(proxima[0].fecha).toLocaleDateString('es-ES') : 'Sin programar';
        this.setText('depNextReview', `Proxima revision: ${proximaFecha}`);

        const vacunasOrdenadas = (vacunas || [])
            .filter(v => v.fecha_administracion)
            .sort((a, b) => new Date(b.fecha_administracion) - new Date(a.fecha_administracion));
        const ultimaVacuna = vacunasOrdenadas.length > 0 ? vacunasOrdenadas[0].nombre_vacuna : 'Sin registros';
        this.setText('depLastVaccine', `Ultima vacuna: ${ultimaVacuna}`);
    }

    renderCrecimiento(perfil) {
        const peso = perfil.peso_kg ? `${perfil.peso_kg}` : '--';
        const altura = perfil.altura_cm ? `${perfil.altura_cm}` : '--';
        const imc = this.calcularImc(perfil.peso_kg, perfil.altura_cm) || '--';

        this.setText('depWeight', peso);
        this.setText('depHeight', altura);
        this.setText('depImc', imc);
    }

    renderConsultas(consultas) {
        const container = document.getElementById('depConsultas');
        if (!container) return;
        if (!consultas || consultas.length === 0) {
            container.innerHTML = '<p class="kid-empty">Sin consultas registradas todavía</p>';
            return;
        }
        // Agrupar por día y mostrar hasta 4 registros más recientes
        const ordenadas = (consultas || [])
            .filter(c => c.fecha)
            .sort((a, b) => new Date(b.fecha) - new Date(a.fecha));

        const grouped = ordenadas.reduce((acc, c) => {
            const key = new Date(c.fecha).toISOString().slice(0, 10);
            if (!acc[key]) acc[key] = [];
            if (acc._count == null) acc._count = 0;
            if (acc._count < 4) { acc[key].push(c); acc._count++; }
            return acc;
        }, {});

        const days = Object.keys(grouped).sort((a, b) => (a < b ? 1 : -1));

        const parts = [];
        for (const day of days) {
            const items = grouped[day];
            if (!items || items.length === 0) continue;
            const dateLabel = new Date(day + 'T00:00:00').toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric', month: 'short' });
            parts.push(`<div class="kid-day-header">${dateLabel}</div>`);
            items.forEach((c) => {
                const fecha = new Date(c.fecha).toLocaleDateString('es-ES');
                const medico = `${c.medico_nombre || ''} ${c.medico_apellidos || ''}`.trim();
                const especialidad = c.especialidad ? `· ${c.especialidad}` : '';
                const diagnostico = c.diagnostico || 'Consulta pediátrica';
                parts.push(`
                    <div class="kid-row">
                        <div class="kid-icon" style="background:#d1fae5;color:#047857;width:36px;height:36px;border-radius:12px">
                            <i data-lucide="stethoscope" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold text-slate-900 text-sm truncate">${diagnostico}</p>
                            <p class="text-xs text-slate-600 font-semibold truncate">${medico} ${especialidad}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">${fecha}</p>
                        </div>
                    </div>
                `);
            });
        }

        container.innerHTML = parts.join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    renderVacunas(vacunas) {
        const container = document.getElementById('depVacunas');
        if (!container) return;
        if (!vacunas || vacunas.length === 0) {
            container.innerHTML = '<p class="kid-empty">Sin vacunas registradas todavía</p>';
            return;
        }

        const ordenadas = vacunas
            .filter(v => v.fecha_administracion)
            .sort((a, b) => new Date(b.fecha_administracion) - new Date(a.fecha_administracion))
            .slice(0, 4);

        container.innerHTML = ordenadas.map((v) => {
            const fecha = new Date(v.fecha_administracion).toLocaleDateString('es-ES');
            const dosis = v.dosis ? `· ${v.dosis}` : '';
            const estado = v.estado ? `· ${v.estado}` : '';
            return `
                <div class="kid-row">
                    <div class="kid-icon" style="background:#fef3c7;color:#b45309;width:36px;height:36px;border-radius:12px">
                        <i data-lucide="syringe" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-extrabold text-slate-900 text-sm truncate">${v.nombre_vacuna}</p>
                        <p class="text-xs text-slate-600 font-semibold truncate">${fecha} ${dosis} ${estado}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">${v.centro || 'Centro no especificado'}</p>
                    </div>
                </div>
            `;
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    renderAlergias(dependiente, perfil) {
        const container = document.getElementById('depAlergias');
        if (!container) return;
        const alergias = dependiente.alergias || perfil.alergias || '';
        const lista = alergias.split(',').map(a => a.trim()).filter(Boolean);
        if (lista.length === 0) {
            container.innerHTML = '<span class="kid-tag">Sin alergias reportadas</span>';
            return;
        }
        container.innerHTML = lista.map(a => `<span class="kid-tag">${a}</span>`).join('');
    }

    renderBasics(dependiente) {
        const nacimiento = dependiente.fecha_nacimiento ? new Date(dependiente.fecha_nacimiento).toLocaleDateString('es-ES') : '--';
        this.setText('depBirthDate', nacimiento);
        this.setText('depBloodType', dependiente.grupo_sanguineo || '--');
        this.setText('depSsn', dependiente.num_seguridad_social || '--');
    }

    setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    mostrarError(mensaje) {
        if (!this.errorBanner) return;
        this.errorBanner.textContent = mensaje;
        this.errorBanner.classList.remove('hidden');
    }

    obtenerIniciales(nombreCompleto) {
        const words = (nombreCompleto || '').trim().split(/\s+/).filter(Boolean);
        if (words.length === 0) return 'DP';
        if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
        return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    }

    calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return null;
        const fecha = new Date(fechaNacimiento);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fecha.getFullYear();
        const m = hoy.getMonth() - fecha.getMonth();
        if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
            edad -= 1;
        }
        return edad;
    }

    calcularImc(peso, alturaCm) {
        if (!peso || !alturaCm) return null;
        const alturaM = alturaCm / 100;
        return (peso / (alturaM * alturaM)).toFixed(1);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new DependienteDashboard();
    dashboard.init();
});
