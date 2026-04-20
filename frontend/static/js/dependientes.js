/**
 * Archivo: dependientes.js
 * Descripción: Gestión de pacientes dependientes (menores) para tutores
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

console.log('📄 CARGANDO ARCHIVO dependientes.js');

const DEPENDIENTES_API = '/backend/src/controllers/DependienteController.php';

class DependientesManager {
    constructor() {
        console.log('🏗️ INICIALIZANDO DependientesManager');
        // Modal elements
        this.modal = document.getElementById('addDependentModal');
        this.addBtn = document.getElementById('addDependentBtn');
        this.closeModalBtn = document.getElementById('closeModalBtn');
        this.cancelModalBtn = document.getElementById('cancelModalBtn');
        this.form = document.getElementById('dependentForm');
        this.submitBtn = document.getElementById('submitDependentBtn');
        this.messageEl = document.getElementById('dependentFormMessage');

        // Form inputs
        this.nombreInput = document.getElementById('childNameInput');
        this.apellidosInput = document.getElementById('childApellidosInput');
        this.fechaNacimientoInput = document.getElementById('childBirthDateInput');
        this.grupoSanguineoInput = document.getElementById('childBloodTypeInput');
        this.numSSInput = document.getElementById('childSSNInput');
        this.alergiasInput = document.getElementById('childAllergiesInput');
        this.observacionesInput = document.getElementById('childObservationsInput');

        // Family members list in sidebar
        this.familyMembersList = document.getElementById('familyMembersList');
        console.log('👨‍👩‍👧‍👦 Elemento familyMembersList encontrado:', this.familyMembersList);

        // View elements
        this.backToParentBtn = document.getElementById('backToParentBtn');
        this.dashboardTitle = document.getElementById('dashboardTitle');

        // State
        this.dependientes = [];
        this.dependienteActual = null;
        this.modoEdicion = false;

        console.log('✅ Constructor DependientesManager completado');
    }

    init() {
        console.log('🚀 EJECUTANDO init() de DependientesManager');
        this.bindEvents();
        this.cargarDependientes();
        this.setMaxFechaNacimiento();
        console.log('✅ init() completado');
    }

    bindEvents() {
        // Modal controls
        this.addBtn?.addEventListener('click', () => this.abrirModal());
        this.closeModalBtn?.addEventListener('click', () => this.cerrarModal());
        this.cancelModalBtn?.addEventListener('click', () => this.cerrarModal());
        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) this.cerrarModal();
        });

        // Form submission
        this.form?.addEventListener('submit', (e) => this.guardarDependiente(e));

        // Back button
        this.backToParentBtn?.addEventListener('click', () => this.volverAMiPerfil());

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal?.style.display === 'flex') {
                this.cerrarModal();
            }
        });
    }

    setMaxFechaNacimiento() {
        // Set max date to today (cannot be born in the future)
        if (this.fechaNacimientoInput) {
            const today = new Date().toISOString().split('T')[0];
            this.fechaNacimientoInput.max = today;

            // Set min date to 18 years ago (must be minor)
            const minDate = new Date();
            minDate.setFullYear(minDate.getFullYear() - 17);
            minDate.setDate(minDate.getDate() + 1);
            this.fechaNacimientoInput.min = minDate.toISOString().split('T')[0];
        }
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
            throw new Error('Respuesta no válida del servidor.');
        }
        if (!response.ok || !payload?.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }
        return payload;
    }

    async cargarDependientes() {
        console.log('🔄 CARGANDO DEPENDIENTES');
        try {
            const res = await this.api('listar');
            console.log('📦 Respuesta API dependientes:', res);
            this.dependientes = res.data || [];
            console.log('👨‍👩‍👧‍👦 Dependientes cargados:', this.dependientes);
            this.renderizarListaSidebar();
        } catch (error) {
            console.error('❌ Error al cargar dependientes:', error);
            this.dependientes = [];
            this.renderizarListaSidebar();
        }
    }

    renderizarListaSidebar() {
        console.log('🎨 RENDERIZANDO LISTA SIDEBAR');
        console.log('familyMembersList element:', this.familyMembersList);
        console.log('Dependientes a renderizar:', this.dependientes);

        if (!this.familyMembersList) {
            console.log('❌ NO se encontró el elemento familyMembersList');
            return;
        }

        if (this.dependientes.length === 0) {
            console.log('📭 No hay dependientes para mostrar');
            this.familyMembersList.innerHTML = `
                <p class="px-4 py-2 text-xs text-gray-400 italic">No hay dependientes registrados</p>
            `;
            return;
        }

        console.log('✅ Renderizando', this.dependientes.length, 'dependientes');
        this.familyMembersList.innerHTML = '';

        this.dependientes.forEach((dep, index) => {
            console.log(`👶 Dependiente ${index + 1}:`, dep);
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'w-full flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-700 hover:bg-blue-50 transition-all group text-left';
            item.dataset.idDependiente = dep.id_dependiente;

            const iniciales = this.obtenerIniciales(dep.nombre_completo);
            const edad = dep.edad !== null ? `${dep.edad} años` : '';

            item.innerHTML = `
                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                    ${iniciales}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${dep.nombre_completo}</p>
                    <p class="text-xs text-gray-500">${edad}</p>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors"></i>
            `;

            console.log(`📎 Agregando event listener para dependiente: ${dep.nombre_completo} (ID: ${dep.id_dependiente})`);
            item.addEventListener('click', () => {
                console.log(`🖱️ CLICK en dependiente: ${dep.nombre_completo} (ID: ${dep.id_dependiente})`);
                this.verDependiente(dep.id_dependiente);
            });
            this.familyMembersList.appendChild(item);
        });

        // Refresh lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        console.log('🎨 Renderizado completado');
    }

    async verDependiente(id) {
        console.log('👁️ EJECUTANDO verDependiente con ID:', id);
        try {
            console.log('📞 Llamando API para obtener dependiente...');
            const res = await this.api('obtener', 'GET', null, { id });
            console.log('📨 Respuesta API obtener dependiente:', res);
            this.dependienteActual = res.data;
            console.log('✅ Dependiente actual asignado:', this.dependienteActual);
            await this.mostrarVistaDependiente();
        } catch (error) {
            console.error('❌ Error en verDependiente:', error);
            alert('Error al cargar dependiente: ' + error.message);
        }
    }

    async mostrarVistaDependiente() {
        console.log('🚀 INICIANDO mostrarVistaDependiente');
        const dep = this.dependienteActual;
        if (!dep) {
            console.log('❌ No hay dependiente actual');
            return;
        }
        console.log('📋 Dependiente actual:', dep);

        // Update title
        if (this.dashboardTitle) {
            this.dashboardTitle.textContent = `Panel de ${dep.nombre}`;
        }

        // Show back button
        this.backToParentBtn?.classList.remove('hidden');
        this.backToParentBtn?.classList.add('flex');

        // Update profile info
        const profileName = document.getElementById('profileName');
        const profileAvatar = document.getElementById('profileAvatar');
        const welcomeTitle = document.getElementById('welcomeTitle');
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');

        if (profileName) profileName.textContent = dep.nombre_completo;
        if (profileAvatar) profileAvatar.textContent = this.obtenerIniciales(dep.nombre_completo);
        if (welcomeTitle) welcomeTitle.textContent = `Panel de ${dep.nombre}`;
        if (welcomeSubtitle) {
            const pediatra = dep.pediatra_nombre_completo || 'No asignado';
            welcomeSubtitle.innerHTML = `
                <span class="text-gray-600">Edad: <strong>${dep.edad} años</strong></span>
                <span class="mx-2">•</span>
                <span class="text-gray-600">Pediatra: <strong>${pediatra}</strong></span>
            `;
        }

        // Cambiar a la vista pediátrica: ocultar vista paciente adulta y mostrar vista pediátrica
        const mainPacienteView = document.getElementById('mainPacienteView');
        const healthProfileView = document.getElementById('healthProfileView');
        const adultView = document.getElementById('adultView');
        const pediatricView = document.getElementById('pediatricView');

        console.log('🔧 CAMBIANDO A VISTA PEDIÁTRICA');
        console.log('mainPacienteView:', mainPacienteView);
        console.log('healthProfileView:', healthProfileView);
        console.log('adultView:', adultView);
        console.log('pediatricView:', pediatricView);

        if (mainPacienteView) mainPacienteView.classList.remove('active');
        if (healthProfileView) healthProfileView.classList.remove('active');
        if (adultView) adultView.classList.remove('active');
        if (pediatricView) {
            pediatricView.classList.add('active');
            console.log('✅ Vista pediátrica activada');
        } else {
            console.log('❌ Vista pediátrica NO encontrada');
        }

        // Indicar que estamos viendo un dependiente (evitar que el dashboard del tutor reescriba la UI)
        window.viewingDependiente = true;

        // Rellenar encabezados de la vista pediátrica
        const childNameEl = document.getElementById('childName');
        const childAgeEl = document.getElementById('childAge');
        if (childNameEl) childNameEl.textContent = dep.nombre_completo || (dep.nombre + ' ' + dep.apellidos || '');
        if (childAgeEl) childAgeEl.textContent = (dep.edad !== null ? dep.edad + ' años' : 'Edad no disponible');

        // Cargar perfil de salud, consultas y cartilla de vacunas del dependiente
        try {
            const perfilRes = await this.api('obtener_perfil_salud', 'GET', null, { id: dep.id_dependiente });
            const consultasRes = await this.api('obtener_consultas', 'GET', null, { id: dep.id_dependiente });
            const vacunasRes = await this.api('obtener_vacunas', 'GET', null, { id: dep.id_dependiente });

            const consultas = (consultasRes && consultasRes.data) ? consultasRes.data : [];
            const vacunas = (vacunasRes && vacunasRes.data) ? vacunasRes.data : [];

            // **NUEVO: Renderizar perfil de salud del dependiente**
            this.renderizarPerfilSaludDependiente(perfilRes.data || {}, dep);

            // **NUEVO: Actualizar resumen superior (evitar textos de adulto)**
            this.actualizarResumenDependiente(consultas, vacunas, dep);

            // Render actividad reciente (consultas + vacunas)
            const recentEl = document.getElementById('recentPediatricActivity');
            if (recentEl) {
                const consultas = (consultasRes && consultasRes.data) ? consultasRes.data : [];
                const vacunas = (vacunasRes && vacunasRes.data) ? vacunasRes.data : [];

                let contenido = '<h3 class="text-lg font-semibold text-gray-900 mb-4">Actividad Reciente</h3>';
                contenido += '<div class="space-y-4">';

                if (consultas.length === 0 && vacunas.length === 0) {
                    contenido += '<p class="text-sm text-gray-500">No hay actividad registrada.</p>';
                }

                consultas.slice(0, 5).forEach(c => {
                    const fecha = c.fecha ? new Date(c.fecha).toLocaleDateString() : '';
                    const medico = (c.medico_nombre || '') + ' ' + (c.medico_apellidos || '');
                    const especialidad = c.especialidad || '';
                    contenido += `
                        <div class="flex items-start space-x-4 pb-4 border-b border-gray-100">
                            <div class="p-2 bg-emerald-50 rounded-lg">
                                <i data-lucide="stethoscope" class="w-5 h-5 text-emerald-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">${c.diagnostico || 'Consulta médica'}</p>
                                <p class="text-sm text-gray-600 mt-1">${medico} ${especialidad ? '· ' + especialidad : ''}</p>
                                <p class="text-xs text-gray-500 mt-2">${fecha}</p>
                            </div>
                        </div>`;
                });

                vacunas.slice(0, 6).forEach(v => {
                    const fechaV = v.fecha_administracion ? new Date(v.fecha_administracion).toLocaleDateString() : '';
                    const estado = v.estado || '';
                    contenido += `
                        <div class="flex items-start space-x-4 pb-4 border-b border-gray-100">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <i data-lucide="syringe" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">${v.nombre_vacuna}</p>
                                <p class="text-sm text-gray-600 mt-1">${v.dosis || ''} · ${v.centro || ''}</p>
                                <p class="text-xs text-gray-500 mt-2">${fechaV} · ${estado}</p>
                            </div>
                        </div>`;
                });

                contenido += '</div>';
                recentEl.innerHTML = contenido;

                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            // **NUEVO: Actualizar lista principal de vacunas**
            this.actualizarListaVacunas(vacunasRes.data || []);

            // Opcional: render calendario de vacunas si existe contenedor
            const calEl = document.getElementById('vaccinationCalendar');
            if (calEl && vacunasRes && vacunasRes.data) {
                // simple resumen: lista de vacunas administradas
                const vacunas = vacunasRes.data;
                let html = '<div class="flex items-center justify-between mb-6"><div class="flex items-center space-x-3"><div class="p-2 bg-blue-50 rounded-lg"><i data-lucide="syringe" class="w-5 h-5 text-blue-600"></i></div><h3 class="text-lg font-semibold text-gray-900">Calendario de Vacunación</h3></div></div>';
                html += '<div class="space-y-3">';
                vacunas.slice(0, 8).forEach(v => {
                    const fechaV = v.fecha_administracion ? new Date(v.fecha_administracion).toLocaleDateString() : '';
                    const estado = v.estado || '';
                    html += `
                        <div class="flex items-center justify-between p-4 ${estado === 'Administrada' ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200'} rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="p-2 ${estado === 'Administrada' ? 'bg-green-600' : 'bg-amber-500'} rounded-lg">
                                    <i data-lucide="check" class="w-4 h-4 text-white"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">${v.nombre_vacuna}</p>
                                    <p class="text-sm text-gray-600">${fechaV}</p>
                                </div>
                            </div>
                            <span class="text-xs font-medium ${estado === 'Administrada' ? 'text-green-700 bg-green-100' : 'text-amber-700 bg-amber-100'} px-3 py-1 rounded-full">${estado}</span>
                        </div>`;
                });
                html += '</div>';
                calEl.innerHTML = html;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

        } catch (err) {
            console.error('Error cargando datos del dependiente:', err);
        }
    }

    async volverAMiPerfil() {
        console.log('🔄 EJECUTANDO volverAMiPerfil - Regresando al perfil del tutor');
        this.dependienteActual = null;

        // Hide back button
        this.backToParentBtn?.classList.add('hidden');
        this.backToParentBtn?.classList.remove('flex');

        // Restore title
        if (this.dashboardTitle) {
            this.dashboardTitle.textContent = 'Mi Panel de Salud';
        }

        // **NUEVO: Limpiar datos del perfil pediátrico**
        this.limpiarPerfilSaludDependiente();

        // **CRÍTICO: Restaurar información del perfil del tutor/paciente principal**
        this.restaurarPerfilTutor();

        // Restaurar flag de vista
        window.viewingDependiente = false;

        // Restore main paciente view
        const mainPacienteView = document.getElementById('mainPacienteView');
        const healthProfileView = document.getElementById('healthProfileView');
        const adultView = document.getElementById('adultView');
        const pediatricView = document.getElementById('pediatricView');

        console.log('🔄 Restaurando vistas - ocultando pediátrica, mostrando adulta');
        if (mainPacienteView) mainPacienteView.classList.add('active');
        if (healthProfileView) healthProfileView.classList.remove('active');
        if (adultView) adultView.classList.add('active');
        if (pediatricView) pediatricView.classList.remove('active');

        // **CRÍTICO: Recargar datos del paciente principal completamente**
        console.log('🔄 Recargando datos del paciente principal...');
        if (window.pacienteDashboard) {
            // Forzar recarga completa del perfil del tutor
            window.pacienteDashboard.mostrarVista('inicio');
            window.pacienteDashboard.cargarPerfilSalud();
            window.pacienteDashboard.cargarConsultas();
            window.pacienteDashboard.cargarRecordatorios();
            window.pacienteDashboard.cargarAntecedentes();

            // Restaurar información del header del usuario principal
            if (window.pacienteDashboard.cargarInformacionUsuario) {
                window.pacienteDashboard.cargarInformacionUsuario();
            }

            // **NUEVO: Cargar también información adicional del usuario**
            if (window.pacienteDashboard.cargarInformacionUsuario) {
                await window.pacienteDashboard.cargarInformacionUsuario();
            }
        } else {
            console.log('❌ window.pacienteDashboard no disponible');
        }

        console.log('✅ Regreso al perfil del tutor completado');
    }

    abrirModal(dependiente = null) {
        this.modoEdicion = !!dependiente;

        if (dependiente) {
            // Edit mode
            this.nombreInput.value = dependiente.nombre || '';
            this.apellidosInput.value = dependiente.apellidos || '';
            this.fechaNacimientoInput.value = dependiente.fecha_nacimiento || '';
            this.grupoSanguineoInput.value = dependiente.grupo_sanguineo || '';
            this.numSSInput.value = dependiente.num_seguridad_social || '';
            this.alergiasInput.value = dependiente.alergias || '';
            this.observacionesInput.value = dependiente.observaciones || '';
            this.form.dataset.idDependiente = dependiente.id_dependiente;
        } else {
            // Create mode
            this.form.reset();
            delete this.form.dataset.idDependiente;
        }

        this.ocultarMensaje();
        this.modal.style.display = 'flex';

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    cerrarModal() {
        this.modal.style.display = 'none';
        this.form.reset();
        delete this.form.dataset.idDependiente;
        this.modoEdicion = false;
        this.ocultarMensaje();
    }

    async guardarDependiente(event) {
        event.preventDefault();

        const nombre = this.nombreInput.value.trim();
        const apellidos = this.apellidosInput.value.trim();
        const fechaNacimiento = this.fechaNacimientoInput.value;
        const grupoSanguineo = this.grupoSanguineoInput.value;
        const numSS = this.numSSInput.value.trim();
        const alergias = this.alergiasInput.value.trim();
        const observaciones = this.observacionesInput.value.trim();

        // Validations
        if (!nombre) {
            this.mostrarMensaje('El nombre es requerido', true);
            return;
        }
        if (!apellidos) {
            this.mostrarMensaje('Los apellidos son requeridos', true);
            return;
        }
        if (!fechaNacimiento) {
            this.mostrarMensaje('La fecha de nacimiento es requerida', true);
            return;
        }

        // Validate age (must be minor)
        const fecha = new Date(fechaNacimiento);
        const hoy = new Date();
        const edad = hoy.getFullYear() - fecha.getFullYear();
        if (edad >= 18) {
            this.mostrarMensaje('El dependiente debe ser menor de 18 años', true);
            return;
        }

        const data = {
            nombre,
            apellidos,
            fecha_nacimiento: fechaNacimiento,
            grupo_sanguineo: grupoSanguineo || null,
            num_seguridad_social: numSS || null,
            alergias: alergias || null,
            observaciones: observaciones || null
        };

        // Add id if editing
        if (this.modoEdicion && this.form.dataset.idDependiente) {
            data.id_dependiente = this.form.dataset.idDependiente;
        }

        // Disable submit button
        this.submitBtn.disabled = true;
        this.submitBtn.textContent = 'Guardando...';

        try {
            const accion = this.modoEdicion ? 'actualizar' : 'crear';
            const res = await this.api(accion, 'POST', data);

            this.mostrarMensaje(res.mensaje || 'Guardado correctamente', false);

            // Reload dependents list
            await this.cargarDependientes();

            // Close modal after a brief delay
            setTimeout(() => {
                this.cerrarModal();
            }, 1500);

        } catch (error) {
            this.mostrarMensaje(error.message, true);
        } finally {
            this.submitBtn.disabled = false;
            this.submitBtn.textContent = 'Guardar';
        }
    }

    mostrarMensaje(texto, esError) {
        if (!this.messageEl) return;
        this.messageEl.textContent = texto;
        this.messageEl.className = `text-sm p-3 rounded-lg ${esError ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600'}`;
        this.messageEl.classList.remove('hidden');
    }

    ocultarMensaje() {
        if (!this.messageEl) return;
        this.messageEl.classList.add('hidden');
        this.messageEl.textContent = '';
    }

    obtenerIniciales(nombreCompleto) {
        const words = (nombreCompleto || '').trim().split(/\s+/).filter(Boolean);
        if (words.length === 0) return 'DP';
        if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
        return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    }

    /**
     * Renderiza el perfil de salud del dependiente en la vista pediátrica
     */
    renderizarPerfilSaludDependiente(perfil, dependiente) {
        console.log('🔧 EJECUTANDO renderizarPerfilSaludDependiente');
        console.log('Perfil recibido:', perfil);
        console.log('Dependiente recibido:', dependiente);

        // Actualizar peso
        const childWeight = document.getElementById('childWeight');
        console.log('Elemento childWeight:', childWeight);
        if (childWeight) {
            const peso = perfil.peso_kg ? `${perfil.peso_kg} kg` : '-- kg';
            childWeight.textContent = peso;
            console.log('✅ Peso actualizado:', peso);
        } else {
            console.log('❌ Elemento childWeight NO encontrado');
        }

        // Actualizar altura
        const childHeight = document.getElementById('childHeight');
        if (childHeight) {
            const altura = perfil.altura_cm ? `${perfil.altura_cm} cm` : '--';
            childHeight.textContent = altura;
        }

        // Actualizar IMC (calcularlo si tenemos peso y altura)
        const childIMC = document.getElementById('childIMC');
        if (childIMC) {
            let imcText = '--';
            if (perfil.peso_kg && perfil.altura_cm) {
                const alturaM = perfil.altura_cm / 100;
                const imc = (perfil.peso_kg / (alturaM * alturaM)).toFixed(1);
                imcText = imc;
            }
            childIMC.textContent = imcText;
        }

        // Actualizar grupo sanguíneo (desde datos del dependiente o perfil)
        const childBloodType = document.getElementById('childBloodType');
        if (childBloodType) {
            const grupoSang = dependiente.grupo_sanguineo || perfil.grupo_sanguineo || '--';
            childBloodType.textContent = grupoSang;
        }

        // Actualizar alergias
        const allergiesContainer = document.getElementById('childAllergiesContainer');
        if (allergiesContainer) {
            const alergias = dependiente.alergias || perfil.alergias || '';

            if (!alergias || alergias.trim() === '') {
                allergiesContainer.innerHTML = '<p class="text-sm text-gray-500 italic">Sin alergias reportadas</p>';
            } else {
                // Dividir alergias por comas y crear elementos individuales
                const listaAlergias = alergias.split(',').map(a => a.trim()).filter(Boolean);
                let html = '';

                listaAlergias.forEach(alergia => {
                    html += `
                        <div class="flex items-center space-x-2 p-2 bg-red-50 rounded-lg">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                            <span class="text-sm font-medium text-red-900">${alergia}</span>
                        </div>
                    `;
                });

                allergiesContainer.innerHTML = html;

                // Re-render lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }
    }

    /**
     * Actualiza las tarjetas superiores del resumen pediatrico
     */
    actualizarResumenDependiente(consultas, vacunas, dependiente) {
        const pediatraAsignado = document.getElementById('pediatraAsignado');
        if (pediatraAsignado) {
            pediatraAsignado.textContent = dependiente.pediatra_nombre_completo || 'Pediatra no asignado';
        }

        const nextReviewEl = document.getElementById('pediatricNextReviewDate');
        if (nextReviewEl) {
            const hoy = new Date();
            const proximas = (consultas || [])
                .filter(c => c.fecha && new Date(c.fecha) >= hoy)
                .sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
            if (proximas.length > 0) {
                const fecha = new Date(proximas[0].fecha).toLocaleDateString('es-ES');
                nextReviewEl.textContent = fecha;
            } else {
                nextReviewEl.textContent = 'Sin programar';
            }
        }

        const lastVaccineName = document.getElementById('pediatricLastVaccineName');
        const lastVaccineDate = document.getElementById('pediatricLastVaccineDate');
        const vacunasOrdenadas = (vacunas || [])
            .filter(v => v.fecha_administracion)
            .sort((a, b) => new Date(b.fecha_administracion) - new Date(a.fecha_administracion));
        if (vacunasOrdenadas.length > 0) {
            const ultima = vacunasOrdenadas[0];
            const fecha = new Date(ultima.fecha_administracion).toLocaleDateString('es-ES');
            if (lastVaccineName) lastVaccineName.textContent = ultima.nombre_vacuna || 'Vacuna registrada';
            if (lastVaccineDate) lastVaccineDate.textContent = fecha;
        } else {
            if (lastVaccineName) lastVaccineName.textContent = 'Sin registros';
            if (lastVaccineDate) lastVaccineDate.textContent = '--';
        }

        const lastRecordEl = document.getElementById('childLastRecord');
        const lastRecordMeta = document.getElementById('childPercentile');
        const consultasOrdenadas = (consultas || [])
            .filter(c => c.fecha)
            .sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
        if (consultasOrdenadas.length > 0) {
            const ultimaConsulta = consultasOrdenadas[0];
            const fecha = new Date(ultimaConsulta.fecha).toLocaleDateString('es-ES');
            if (lastRecordEl) lastRecordEl.textContent = ultimaConsulta.diagnostico || 'Consulta medica';
            if (lastRecordMeta) lastRecordMeta.textContent = `Actualizado: ${fecha}`;
        } else {
            if (lastRecordEl) lastRecordEl.textContent = 'Sin registros';
            if (lastRecordMeta) lastRecordMeta.textContent = '--';
        }
    }

    /**
     * Limpia los datos del perfil de salud pediátrico al volver al perfil del tutor
     */
    limpiarPerfilSaludDependiente() {
        // Resetear elementos a sus valores por defecto
        const childWeight = document.getElementById('childWeight');
        if (childWeight) childWeight.textContent = '-- kg';

        const childHeight = document.getElementById('childHeight');
        if (childHeight) childHeight.textContent = '-- cm';

        const childIMC = document.getElementById('childIMC');
        if (childIMC) childIMC.textContent = '--';

        const childBloodType = document.getElementById('childBloodType');
        if (childBloodType) childBloodType.textContent = '--';

        const allergiesContainer = document.getElementById('childAllergiesContainer');
        if (allergiesContainer) {
            allergiesContainer.innerHTML = '<p class="text-sm text-gray-500 italic">Datos no disponibles</p>';
        }

        // Limpiar también el header del niño
        const childNameEl = document.getElementById('childName');
        const childAgeEl = document.getElementById('childAge');
        if (childNameEl) childNameEl.textContent = 'Panel Infantil';
        if (childAgeEl) childAgeEl.textContent = 'Edad y última actualización';

        // **NUEVO: Limpiar lista de vacunas**
        const vaccinationList = document.getElementById('vaccinationList');
        if (vaccinationList) {
            vaccinationList.innerHTML = '<p class="text-sm text-gray-500 italic text-center py-4">Datos de vacunación para menores</p>';
        }

        const pediatraAsignado = document.getElementById('pediatraAsignado');
        if (pediatraAsignado) pediatraAsignado.textContent = '';

        const nextReviewEl = document.getElementById('pediatricNextReviewDate');
        if (nextReviewEl) nextReviewEl.textContent = '';

        const lastVaccineName = document.getElementById('pediatricLastVaccineName');
        if (lastVaccineName) lastVaccineName.textContent = '';

        const lastVaccineDate = document.getElementById('pediatricLastVaccineDate');
        if (lastVaccineDate) lastVaccineDate.textContent = '';

        const lastRecordEl = document.getElementById('childLastRecord');
        if (lastRecordEl) lastRecordEl.textContent = '';

        const lastRecordMeta = document.getElementById('childPercentile');
        if (lastRecordMeta) lastRecordMeta.textContent = '';
    }

    /**
     * Restaura la información del perfil del tutor/paciente principal
     */
    restaurarPerfilTutor() {
        console.log('🔄 EJECUTANDO restaurarPerfilTutor');

        // Obtener datos del usuario actual desde localStorage o sessionStorage
        const usuarioDatos = JSON.parse(localStorage.getItem('usuario') || sessionStorage.getItem('usuario') || '{}');
        console.log('👤 Datos del usuario obtenidos:', usuarioDatos);

        // Restaurar nombre del perfil
        const profileName = document.getElementById('profileName');
        if (profileName && usuarioDatos.nombre) {
            const nombreCompleto = `${usuarioDatos.nombre} ${usuarioDatos.apellidos || ''}`.trim();
            profileName.textContent = nombreCompleto;
            console.log('✅ Nombre del perfil restaurado:', nombreCompleto);
        }

        // Restaurar avatar del perfil
        const profileAvatar = document.getElementById('profileAvatar');
        if (profileAvatar && usuarioDatos.nombre) {
            const iniciales = this.obtenerIniciales(`${usuarioDatos.nombre} ${usuarioDatos.apellidos || ''}`);
            profileAvatar.textContent = iniciales;
            console.log('✅ Avatar del perfil restaurado:', iniciales);
        }

        // Restaurar título de bienvenida
        const welcomeTitle = document.getElementById('welcomeTitle');
        if (welcomeTitle) {
            welcomeTitle.textContent = 'Mi Panel de Salud';
            console.log('✅ Título de bienvenida restaurado');
        }

        // Restaurar subtítulo de bienvenida
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');
        if (welcomeSubtitle) {
            const fechaActual = new Date().toLocaleDateString('es-ES');
            welcomeSubtitle.innerHTML = `
                <span class="text-gray-600">Acceso completo a tu historial médico</span>
                <span class="mx-2">•</span>
                <span class="text-gray-600">Última actualización: <strong>${fechaActual}</strong></span>
            `;
            console.log('✅ Subtítulo de bienvenida restaurado');
        }

        console.log('✅ restaurarPerfilTutor completado');
    }

    /**
     * Actualiza dinámicamente la lista principal de vacunas
     */
    actualizarListaVacunas(vacunas) {
        const vaccinationList = document.getElementById('vaccinationList');
        if (!vaccinationList) return;

        console.log('🔄 Actualizando lista principal de vacunas');

        if (!vacunas || vacunas.length === 0) {
            vaccinationList.innerHTML = '<p class="text-sm text-gray-500 italic text-center py-4">No hay vacunas registradas</p>';
            return;
        }

        let html = '';
        vacunas.forEach(v => {
            const fechaV = v.fecha_administracion ? new Date(v.fecha_administracion).toLocaleDateString() : 'Fecha no registrada';
            const estado = v.estado || 'Sin estado';
            const dosis = v.dosis || 'Dosis no especificada';

            const isAdministrada = estado === 'Administrada';
            const isPendiente = estado === 'Pendiente';

            const bgColor = isAdministrada ? 'bg-green-50 border-green-200' : isPendiente ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-200';
            const iconColor = isAdministrada ? 'bg-green-600' : isPendiente ? 'bg-amber-500' : 'bg-gray-400';
            const textColor = isAdministrada ? 'text-green-700 bg-green-100' : isPendiente ? 'text-amber-700 bg-amber-100' : 'text-gray-600 bg-gray-200';
            const icon = isAdministrada ? 'check' : isPendiente ? 'clock' : 'calendar';

            html += `
                <div class="flex items-center justify-between p-4 ${bgColor} rounded-lg border">
                    <div class="flex items-center space-x-4">
                        <div class="p-2 ${iconColor} rounded-lg">
                            <i data-lucide="${icon}" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">${v.nombre_vacuna}</p>
                            <p class="text-sm text-gray-600">${dosis} - ${fechaV}</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium ${textColor} px-3 py-1 rounded-full">${estado}</span>
                </div>
            `;
        });

        vaccinationList.innerHTML = html;

        // Re-render lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        console.log('✅ Lista principal de vacunas actualizada');
    }
}

console.log('✅ CLASE DependientesManager DEFINIDA');

// Initialize DependientesManager
function initializeDependientesManager() {
    console.log('🌐 INICIALIZANDO DependientesManager');

    try {
        window.dependientesManager = new DependientesManager();
        console.log('✅ DependientesManager creado');

        window.dependientesManager.init();
        console.log('✅ DependientesManager inicializado y asignado a window.dependientesManager');
    } catch (error) {
        console.error('❌ ERROR al inicializar DependientesManager:', error);
    }
}

// Check if DOM is already loaded or wait for it
if (document.readyState === 'loading') {
    console.log('📋 DOM aún cargando, esperando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', initializeDependientesManager);
} else {
    console.log('📋 DOM ya cargado, inicializando inmediatamente...');
    initializeDependientesManager();
}
