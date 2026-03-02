/**
 * Archivo: admin.js
 * Descripción: Funcionalidad JavaScript renovada para el panel de administrador
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class AdminPanel {
    constructor() {
        this.apiUrl = '/backend/src/controllers/AdminController.php';
        this.isLoggedIn = false;
        this.currentTab = 'dashboard';
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkSession();
        // El modal de login ya está visible por defecto en el HTML
    }

    // Oculta la UI del panel hasta que el admin inicie sesión
    hideAdminUI() {
        const layout = document.querySelector('.admin-layout');
        if (layout) layout.classList.add('not-authenticated');
    }

    // Muestra la UI del panel tras iniciar sesión
    showAdminUI() {
        const layout = document.querySelector('.admin-layout');
        if (layout) layout.classList.remove('not-authenticated');
    }

    setupEventListeners() {
        // Login
        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
        document.getElementById('btn-logout').addEventListener('click', () => this.handleLogout());

        // Navigation tabs
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => this.switchTab(e));
        });

        // Modal controls
        document.querySelectorAll('.close').forEach(close => {
            close.addEventListener('click', (e) => this.closeModal(e.target.dataset.modal));
        });

        document.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', (e) => this.closeModal(e.target.dataset.close));
        });

        // New forms
        document.getElementById('btn-nuevo-medico').addEventListener('click', () => this.showModal('medicoModal'));
        document.getElementById('btn-nuevo-paciente').addEventListener('click', () => this.showModal('pacienteModal'));

        // Form submissions
        document.getElementById('medicoForm').addEventListener('submit', (e) => this.handleCreateMedico(e));
        document.getElementById('pacienteForm').addEventListener('submit', (e) => this.handleCreatePaciente(e));

        // Close modals on background click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });
    }

    async checkSession() {
        // In a real application, you'd check with the server
        // For now, we'll just show the login modal
        this.isLoggedIn = false;
        // La clase 'not-authenticated' ya está en el HTML, oculta el panel automáticamente
    }

    showLoginModal() {
        if (!this.isLoggedIn) {
            this.showModal('loginModal');
        }
    }

    async handleLogin(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        // Mostrar estado de carga
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="loading"></div> Verificando...';
        submitBtn.disabled = true;

        try {
            const response = await this.apiCall('login', 'POST', data);

            if (response.success) {
                this.isLoggedIn = true;

                // Obtener iniciales del administrador
                const nombres = response.admin.nombre.split(' ');
                const apellidos = response.admin.apellidos.split(' ');
                const iniciales = (nombres[0]?.[0] || '') + (apellidos[0]?.[0] || '');

                // Actualizar UI
                document.querySelector('.admin-avatar').innerHTML = iniciales.toUpperCase();
                document.getElementById('admin-nombre').textContent = response.admin.nombre + ' ' + response.admin.apellidos;

                // Mostrar la UI del panel y cargar datos
                this.showAdminUI();
                this.closeModal('loginModal');
                this.loadDashboard();
                this.showAlert('Bienvenido al panel de administración', 'success');
            } else {
                this.showAlert(response.mensaje || 'Credenciales inválidas', 'error');
            }
        } catch (error) {
            console.error('Error de login:', error);
            this.showAlert('Error de conexión. Verifique el servidor.', 'error');
        } finally {
            // Restaurar botón
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async handleLogout() {
        if (!confirm('¿Está seguro de que desea cerrar sesión?')) {
            return;
        }

        try {
            await this.apiCall('logout', 'POST');
            this.isLoggedIn = false;
            // Ocultar UI y volver a mostrar modal de login
            this.hideAdminUI();
            // Limpiar formulario de login
            document.getElementById('loginForm').reset();
            this.showModal('loginModal');
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
            this.showAlert('Error al cerrar sesión', 'error');
        }
    }

    switchTab(e) {
        e.preventDefault();

        const tabName = e.target.dataset.tab;

        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        e.target.classList.add('active');

        // Update content
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tabName + '-tab').classList.add('active');

        // Load tab-specific data
        this.loadTabData(tabName);
    }

    async loadTabData(tabName) {
        if (!this.isLoggedIn) return;

        switch (tabName) {
            case 'dashboard':
                await this.loadDashboard();
                break;
            case 'medicos':
                await this.loadMedicos();
                break;
            case 'pacientes':
                await this.loadPacientes();
                break;
            case 'consultas':
                await this.loadConsultas();
                break;
            case 'auditoria':
                await this.loadAuditoria();
                break;
        }
    }

    async loadDashboard() {
        try {
            const response = await this.apiCall('estadisticas', 'GET');

            if (response.success) {
                const stats = response.data;
                document.getElementById('stats-pacientes').textContent = stats.pacientes_activos;
                document.getElementById('stats-medicos').textContent = stats.medicos_activos;
                document.getElementById('stats-consultas').textContent = stats.consultas_mes;

                this.renderEspecialidadesChart(stats.especialidades_demandadas);
            }
        } catch (error) {
            this.showAlert('Error al cargar estadísticas', 'error');
        }
    }

    renderEspecialidadesChart(especialidades) {
        const chartContainer = document.getElementById('especialidades-chart');

        if (especialidades && especialidades.length > 0) {
            chartContainer.innerHTML = '';
            chartContainer.style.padding = '0';

            especialidades.forEach((esp, index) => {
                const bar = document.createElement('div');
                bar.className = 'especialidad-bar';

                // Calcular porcentaje para el ancho de la barra
                const maxConsultas = Math.max(...especialidades.map(e => parseInt(e.total_consultas)));
                const porcentaje = (parseInt(esp.total_consultas) / maxConsultas) * 100;

                bar.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fas fa-stethoscope" style="font-size: 1.1rem;"></i>
                            <span style="font-weight: 600;">${esp.especialidad}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="
                                width: 60px; 
                                height: 6px; 
                                background: rgba(255,255,255,0.3); 
                                border-radius: 3px; 
                                overflow: hidden;
                            ">
                                <div style="
                                    width: ${porcentaje}%; 
                                    height: 100%; 
                                    background: white; 
                                    border-radius: 3px;
                                    transition: width 0.5s ease;
                                "></div>
                            </div>
                            <span style="font-weight: 700; font-size: 1.1rem;">
                                ${esp.total_consultas}
                            </span>
                        </div>
                    </div>
                `;

                // Añadir animación escalonada
                bar.style.opacity = '0';
                bar.style.transform = 'translateX(-20px)';
                chartContainer.appendChild(bar);

                setTimeout(() => {
                    bar.style.transition = 'all 0.4s ease';
                    bar.style.opacity = '1';
                    bar.style.transform = 'translateX(0)';
                }, index * 100);
            });
        } else {
            chartContainer.innerHTML = `
                <div style="text-align: center; color: var(--gray-500); padding: 2rem;">
                    <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="font-weight: 500;">No hay datos de especialidades disponibles</p>
                    <p style="font-size: 0.875rem; opacity: 0.8;">Los datos aparecerán cuando se registren consultas</p>
                </div>
            `;
        }
    }

    async loadMedicos() {
        try {
            const response = await this.apiCall('listar_medicos', 'GET');

            if (response.success) {
                this.renderMedicosTable(response.data);
            }
        } catch (error) {
            this.showAlert('Error al cargar médicos', 'error');
        }
    }

    renderMedicosTable(medicos) {
        const tbody = document.querySelector('#tabla-medicos tbody');
        tbody.innerHTML = '';

        if (medicos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-user-doctor" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="font-weight: 500;">No hay médicos registrados</p>
                        <p style="font-size: 0.875rem;">Use el botón "Nuevo Médico" para agregar profesionales</p>
                    </td>
                </tr>
            `;
            return;
        }

        medicos.forEach(medico => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="font-weight: 600; color: var(--primary-blue);">#${medico.id_medico}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="
                            width: 32px; height: 32px; 
                            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                            border-radius: 8px; 
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: 600; font-size: 0.75rem;
                        ">
                            ${medico.nombre[0]}${medico.apellidos[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600;">${medico.nombre} ${medico.apellidos}</div>
                            <div style="font-size: 0.75rem; color: var(--gray-500);">Dr/a. ${medico.especialidad}</div>
                        </div>
                    </div>
                </td>
                <td style="color: var(--gray-600);">${medico.email}</td>
                <td>
                    <span style="
                        background: rgba(37, 99, 235, 0.1);
                        color: var(--primary-blue);
                        padding: 0.25rem 0.75rem;
                        border-radius: 12px;
                        font-size: 0.75rem;
                        font-weight: 600;
                    ">${medico.especialidad}</span>
                </td>
                <td style="font-family: monospace; font-weight: 600;">${medico.num_colegiado}</td>
                <td>
                    <span class="estado-badge ${medico.activo ? 'estado-activo' : 'estado-inactivo'}">
                        <i class="fas fa-${medico.activo ? 'check-circle' : 'times-circle'}"></i>
                        ${medico.activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        ${medico.activo ? `
                            <button class="btn btn-danger btn-sm" onclick="adminPanel.darDeBajaMedico(${medico.id_medico})" 
                                    title="Dar de baja (borrado lógico)">
                                <i class="fas fa-user-slash"></i> 
                                Dar de Baja
                            </button>
                        ` : `
                            <span style="color: var(--gray-400); font-size: 0.75rem;">
                                <i class="fas fa-info-circle"></i> Inactivo
                            </span>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadPacientes() {
        try {
            const response = await this.apiCall('listar_pacientes', 'GET');

            if (response.success) {
                this.renderPacientesTable(response.data);
            }
        } catch (error) {
            this.showAlert('Error al cargar pacientes', 'error');
        }
    }

    renderPacientesTable(pacientes) {
        const tbody = document.querySelector('#tabla-pacientes tbody');
        tbody.innerHTML = '';

        if (pacientes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="font-weight: 500;">No hay pacientes registrados</p>
                        <p style="font-size: 0.875rem;">Use el botón "Nuevo Paciente" para agregar usuarios</p>
                    </td>
                </tr>
            `;
            return;
        }

        pacientes.forEach(paciente => {
            const row = document.createElement('tr');
            const fechaNac = paciente.fecha_nacimiento ? new Date(paciente.fecha_nacimiento) : null;
            const edad = fechaNac ? new Date().getFullYear() - fechaNac.getFullYear() : 'N/A';

            row.innerHTML = `
                <td style="font-weight: 600; color: var(--medical-green);">#${paciente.id_paciente}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="
                            width: 32px; height: 32px; 
                            background: linear-gradient(135deg, var(--medical-green), #10b981);
                            border-radius: 8px; 
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: 600; font-size: 0.75rem;
                        ">
                            ${paciente.nombre[0]}${paciente.apellidos[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600;">${paciente.nombre} ${paciente.apellidos}</div>
                            <div style="font-size: 0.75rem; color: var(--gray-500);">Edad: ${edad} años</div>
                        </div>
                    </div>
                </td>
                <td style="color: var(--gray-600);">${paciente.email}</td>
                <td style="font-weight: 500;">${paciente.telefono || '<span style="color: var(--gray-400);">No registrado</span>'}</td>
                <td style="font-weight: 500;">${this.formatDate(paciente.fecha_nacimiento)}</td>
                <td>
                    <span class="estado-badge ${paciente.activo ? 'estado-activo' : 'estado-inactivo'}">
                        <i class="fas fa-${paciente.activo ? 'heart' : 'heart-broken'}"></i>
                        ${paciente.activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        ${paciente.activo ? `
                            <button class="btn btn-danger btn-sm" onclick="adminPanel.darDeBajaPaciente(${paciente.id_paciente})"
                                    title="Dar de baja (borrado lógico)">
                                <i class="fas fa-user-slash"></i> 
                                Dar de Baja
                            </button>
                        ` : `
                            <span style="color: var(--gray-400); font-size: 0.75rem;">
                                <i class="fas fa-info-circle"></i> Inactivo
                            </span>
                        `}
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadConsultas() {
        try {
            const response = await this.apiCall('listar_consultas', 'GET');

            if (response.success) {
                this.renderConsultasTable(response.data);
            }
        } catch (error) {
            this.showAlert('Error al cargar consultas', 'error');
        }
    }

    renderConsultasTable(consultas) {
        const tbody = document.querySelector('#tabla-consultas tbody');
        tbody.innerHTML = '';

        if (consultas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-calendar-day" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p style="font-weight: 500;">No hay consultas registradas</p>
                        <p style="font-size: 0.875rem;">Las consultas aparecerán cuando los pacientes agenden citas</p>
                    </td>
                </tr>
            `;
            return;
        }

        consultas.forEach((consulta, index) => {
            const row = document.createElement('tr');
            const fechaConsulta = new Date(consulta.fecha_consulta);
            const esHoy = this.isToday(fechaConsulta);
            const esManana = this.isTomorrow(fechaConsulta);

            row.innerHTML = `
                <td style="font-weight: 600; color: var(--primary-blue);">#${consulta.id_consulta}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="
                            width: 28px; height: 28px; 
                            background: linear-gradient(135deg, var(--medical-green), #10b981);
                            border-radius: 6px; 
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: 600; font-size: 0.7rem;
                        ">
                            ${consulta.paciente_nombre[0]}${consulta.paciente_apellidos[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">${consulta.paciente_nombre} ${consulta.paciente_apellidos}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="
                            width: 28px; height: 28px; 
                            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
                            border-radius: 6px; 
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: 600; font-size: 0.7rem;
                        ">
                            ${consulta.medico_nombre[0]}${consulta.medico_apellidos[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;">Dr/a. ${consulta.medico_nombre} ${consulta.medico_apellidos}</div>
                            <div style="font-size: 0.75rem; color: var(--gray-500);">${consulta.especialidad}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="
                        display: flex; align-items: center; gap: 0.5rem;
                        padding: 0.25rem 0.75rem;
                        background: ${esHoy ? 'rgba(239, 68, 68, 0.1)' : esManana ? 'rgba(251, 146, 60, 0.1)' : 'rgba(107, 114, 128, 0.1)'};
                        border-radius: 8px;
                        color: ${esHoy ? '#dc2626' : esManana ? '#ea580c' : 'var(--gray-600)'};
                        font-weight: 600;
                        font-size: 0.8rem;
                    ">
                        <i class="fas fa-${esHoy ? 'exclamation-circle' : esManana ? 'clock' : 'calendar'}"></i>
                        <span>${this.formatDate(consulta.fecha_consulta)}</span>
                    </div>
                </td>
                <td>
                    <div style="
                        background: rgba(16, 185, 129, 0.1);
                        color: var(--medical-green);
                        padding: 0.25rem 0.75rem;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 0.8rem;
                        text-align: center;
                    ">
                        <i class="fas fa-clock"></i>
                        ${consulta.hora_consulta}
                    </div>
                </td>
                <td>
                    ${consulta.diagnostico ? `
                        <div style="background: rgba(59, 130, 246, 0.1); padding: 0.5rem; border-radius: 6px; font-size: 0.8rem;">
                            <i class="fas fa-notes-medical" style="color: var(--primary-blue); margin-right: 0.5rem;"></i>
                            <span style="color: var(--gray-700);">${this.truncateText(consulta.diagnostico, 60)}</span>
                        </div>
                    ` : `
                        <span style="color: var(--gray-400); font-style: italic; font-size: 0.8rem;">
                            <i class="fas fa-hourglass-half"></i> Pendiente
                        </span>
                    `}
                </td>
            `;

            // Añadir animación escalonada
            row.style.opacity = '0';
            row.style.transform = 'translateY(10px)';
            tbody.appendChild(row);

            setTimeout(() => {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    async loadAuditoria() {
        try {
            const response = await this.apiCall('logs', 'GET');

            if (response.success) {
                this.renderAuditoriaTable(response.data);
            }
        } catch (error) {
            this.showAlert('Error al cargar logs de auditoría', 'error');
        }
    }

    renderAuditoriaTable(logs) {
        const tbody = document.querySelector('#tabla-auditoria tbody');
        tbody.innerHTML = '';

        logs.forEach(log => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.formatDateTime(log.fecha_hora)}</td>
                <td>${log.usuario_responsable || 'Sistema'}</td>
                <td>${log.accion}</td>
                <td>${log.tabla_afectada}</td>
                <td>${log.registro_id}</td>
                <td><small>${log.detalles}</small></td>
            `;
            tbody.appendChild(row);
        });
    }

    async handleCreateMedico(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const response = await this.apiCall('crear_medico', 'POST', data);

            if (response.success) {
                this.showAlert('Médico creado exitosamente', 'success');
                this.closeModal('medicoModal');
                e.target.reset();
                await this.loadMedicos();
            } else {
                this.showAlert(response.mensaje, 'error');
            }
        } catch (error) {
            this.showAlert('Error al crear médico: ' + error.message, 'error');
        }
    }

    async handleCreatePaciente(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const response = await this.apiCall('crear_paciente', 'POST', data);

            if (response.success) {
                this.showAlert('Paciente creado exitosamente', 'success');
                this.closeModal('pacienteModal');
                e.target.reset();
                await this.loadPacientes();
            } else {
                this.showAlert(response.mensaje, 'error');
            }
        } catch (error) {
            this.showAlert('Error al crear paciente: ' + error.message, 'error');
        }
    }

    async darDeBajaMedico(id) {
        if (!confirm('¿Está seguro de que desea dar de baja a este médico? Esta acción realizará un borrado lógico por cumplimiento legal.')) {
            return;
        }

        try {
            const response = await this.apiCall(`dar_baja_medico?id=${id}`, 'DELETE');

            if (response.success) {
                this.showAlert('Médico dado de baja exitosamente', 'success');
                await this.loadMedicos();
            } else {
                this.showAlert(response.mensaje, 'error');
            }
        } catch (error) {
            this.showAlert('Error al dar de baja al médico', 'error');
        }
    }

    async darDeBajaPaciente(id) {
        if (!confirm('¿Está seguro de que desea dar de baja a este paciente? Esta acción realizará un borrado lógico por cumplimiento legal.')) {
            return;
        }

        try {
            const response = await this.apiCall(`dar_baja_paciente?id=${id}`, 'DELETE');

            if (response.success) {
                this.showAlert('Paciente dado de baja exitosamente', 'success');
                await this.loadPacientes();
            } else {
                this.showAlert(response.mensaje, 'error');
            }
        } catch (error) {
            this.showAlert('Error al dar de baja al paciente', 'error');
        }
    }

    async apiCall(endpoint, method = 'GET', data = null) {
        const url = `${this.apiUrl}?accion=${endpoint}`;

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // Añadir clase para animación
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }
    }

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 200);
        }
    }

    showAlert(message, type = 'info') {
        // Eliminar alertas existentes
        document.querySelectorAll('.alert-toast').forEach(alert => alert.remove());

        const alert = document.createElement('div');
        alert.className = 'alert-toast';
        alert.style.cssText = `
            position: fixed;
            top: 2rem;
            right: 2rem;
            min-width: 300px;
            max-width: 500px;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        `;

        // Definir estilos según el tipo
        let bgColor, textColor, borderColor, icon;
        switch (type) {
            case 'success':
                bgColor = 'rgba(16, 185, 129, 0.1)';
                textColor = '#065f46';
                borderColor = '#10b981';
                icon = 'check-circle';
                break;
            case 'error':
                bgColor = 'rgba(239, 68, 68, 0.1)';
                textColor = '#991b1b';
                borderColor = '#ef4444';
                icon = 'exclamation-circle';
                break;
            case 'warning':
                bgColor = 'rgba(251, 146, 60, 0.1)';
                textColor = '#9a3412';
                borderColor = '#fb923c';
                icon = 'exclamation-triangle';
                break;
            default:
                bgColor = 'rgba(59, 130, 246, 0.1)';
                textColor = '#1e40af';
                borderColor = '#3b82f6';
                icon = 'info-circle';
        }

        alert.style.backgroundColor = bgColor;
        alert.style.color = textColor;
        alert.style.borderColor = borderColor;

        alert.innerHTML = `
            <i class="fas fa-${icon}" style="font-size: 1.25rem;"></i>
            <span style="flex: 1;">${message}</span>
            <button onclick="this.parentElement.remove()" style="
                background: none; 
                border: none; 
                color: ${textColor}; 
                opacity: 0.6;
                font-size: 1.1rem;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <i class="fas fa-times"></i>
            </button>
        `;

        document.body.appendChild(alert);

        // Animación de entrada
        setTimeout(() => {
            alert.style.transform = 'translateX(0)';
            alert.style.opacity = '1';
        }, 10);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alert.parentElement) {
                alert.style.transform = 'translateX(100%)';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }

    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    }

    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('es-ES');
    }

    // Utilidades adicionales para las nuevas funciones
    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    isTomorrow(date) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        return date.toDateString() === tomorrow.toDateString();
    }

    truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
}

// Initialize the admin panel
const adminPanel = new AdminPanel();

// Global error handler
window.addEventListener('error', (e) => {
    console.error('Error:', e.error);
    adminPanel.showAlert('Ha ocurrido un error inesperado', 'error');
});

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
    adminPanel.showAlert('Error de conexión o servidor', 'error');
});