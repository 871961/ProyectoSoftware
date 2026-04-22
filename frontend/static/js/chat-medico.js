/*
    Archivo: chat-medico.js
    Descripcion: UI de chat seguro medico-medico
*/

const CHAT_API = '/backend/src/controllers/ChatMedicosController.php';
const SESION_API = '/backend/src/controllers/ConsultasController.php?accion=sesion';

class ChatMedicosUI {
    constructor() {
        this.usuario = null;
        this.contactos = [];
        this.conversaciones = [];
        this.contactoActivo = null;
        this.refreshTimer = null;

        this.contactListEl = document.getElementById('chatContactList');
        this.messagesEl = document.getElementById('chatMessages');
        this.activeTitleEl = document.getElementById('chatActiveTitle');
        this.activeSubtitleEl = document.getElementById('chatActiveSubtitle');
        this.formEl = document.getElementById('chatForm');
        this.inputEl = document.getElementById('chatInput');
        this.sendBtnEl = document.getElementById('chatSendBtn');
        this.statusEl = document.getElementById('chatMessageStatus');
        this.refreshBtnEl = document.getElementById('chatRefreshBtn');
        this.mensajesTabEl = document.getElementById('tab-mensajes');
    }

    async init() {
        if (!this.contactListEl || !this.messagesEl) {
            return;
        }

        await this.cargarSesion();
        this.bindEvents();
        await this.cargarContactosYConversaciones();
        this.iniciarAutoRefresh();
    }

    bindEvents() {
        this.formEl?.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.enviarMensaje();
        });

        this.refreshBtnEl?.addEventListener('click', async () => {
            await this.cargarContactosYConversaciones();
            if (this.contactoActivo) {
                await this.cargarMensajes(this.contactoActivo.id_medico, false);
            }
        });
    }

    iniciarAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        this.refreshTimer = setInterval(async () => {
            const tabVisible = this.mensajesTabEl && !this.mensajesTabEl.classList.contains('hidden');
            if (!tabVisible) {
                return;
            }

            await this.cargarContactosYConversaciones();
            if (this.contactoActivo) {
                await this.cargarMensajes(this.contactoActivo.id_medico, false);
            }
        }, 12000);
    }

    async cargarSesion() {
        const response = await fetch(SESION_API, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });
        const payload = await response.json();

        if (!response.ok || !payload.success || payload.usuario?.tipo !== 'medico') {
            throw new Error('Sesion no valida para chat');
        }

        this.usuario = payload.usuario;
    }

    async chatApi(accion, method = 'GET', data = null, params = {}) {
        const query = new URLSearchParams({ accion, ...params }).toString();
        const url = `${CHAT_API}?${query}`;
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        const raw = await response.text();

        let payload = null;
        try {
            payload = raw ? JSON.parse(raw) : null;
        } catch (_err) {
            throw new Error('Respuesta invalida del servidor de chat');
        }

        if (!response.ok || !payload?.success) {
            throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
        }

        return payload;
    }

    async cargarContactosYConversaciones() {
        try {
            const [resMedicos, resConversaciones] = await Promise.all([
                this.chatApi('listar_medicos'),
                this.chatApi('listar_conversaciones')
            ]);

            this.contactos = resMedicos.data || [];
            this.conversaciones = resConversaciones.data || [];

            this.renderContactos();

            if (!this.contactoActivo) {
                const primero = this.primerContactoConPrioridad();
                if (primero) {
                    await this.seleccionarContacto(primero.id_medico);
                }
            }
        } catch (error) {
            this.setStatus(error.message, true);
        }
    }

    primerContactoConPrioridad() {
        if (this.conversaciones.length > 0) {
            return this.conversaciones[0];
        }
        if (this.contactos.length > 0) {
            return this.contactos[0];
        }
        return null;
    }

    renderContactos() {
        if (!this.contactListEl) return;

        const mapaConversaciones = new Map();
        this.conversaciones.forEach((c) => mapaConversaciones.set(Number(c.id_contacto), c));

        const mezclados = this.contactos.map((m) => {
            const conv = mapaConversaciones.get(Number(m.id_medico));
            return {
                id_medico: Number(m.id_medico),
                nombre: m.nombre,
                apellidos: m.apellidos,
                especialidad: m.especialidad || 'Medico General',
                no_leidos: Number(conv?.no_leidos || 0),
                ultimo_envio: conv?.ultimo_envio || null
            };
        }).sort((a, b) => {
            const aT = a.ultimo_envio ? new Date(a.ultimo_envio).getTime() : 0;
            const bT = b.ultimo_envio ? new Date(b.ultimo_envio).getTime() : 0;
            return bT - aT;
        });

        this.contactListEl.innerHTML = '';

        if (!mezclados.length) {
            const p = document.createElement('p');
            p.className = 'text-sm text-gray-500';
            p.textContent = 'No hay medicos disponibles.';
            this.contactListEl.appendChild(p);
            return;
        }

        mezclados.forEach((c) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'w-full text-left p-3 rounded-xl border transition-colors';

            const activo = this.contactoActivo && Number(this.contactoActivo.id_medico) === Number(c.id_medico);
            if (activo) {
                btn.classList.add('bg-blue-50', 'border-blue-200');
            } else {
                btn.classList.add('bg-white', 'border-gray-200', 'hover:bg-gray-50');
            }

            const nombreCompleto = `${c.nombre || ''} ${c.apellidos || ''}`.trim();
            const top = document.createElement('div');
            top.className = 'flex items-center justify-between gap-2';

            const title = document.createElement('p');
            title.className = 'font-medium text-gray-900 text-sm';
            title.textContent = nombreCompleto || `Medico ${c.id_medico}`;
            top.appendChild(title);

            if (c.no_leidos > 0) {
                const badge = document.createElement('span');
                badge.className = 'px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold';
                badge.textContent = String(c.no_leidos);
                top.appendChild(badge);
            }

            const sub = document.createElement('p');
            sub.className = 'text-xs text-gray-500 mt-1';
            sub.textContent = c.especialidad || 'Medico';

            btn.appendChild(top);
            btn.appendChild(sub);

            btn.addEventListener('click', async () => {
                await this.seleccionarContacto(c.id_medico);
            });

            this.contactListEl.appendChild(btn);
        });
    }

    async seleccionarContacto(idMedico) {
        const contacto = this.contactos.find((m) => Number(m.id_medico) === Number(idMedico));
        if (!contacto) {
            this.setStatus('No se encontro el medico seleccionado.', true);
            return;
        }

        this.contactoActivo = contacto;
        this.renderContactos();

        const nombre = `${contacto.nombre || ''} ${contacto.apellidos || ''}`.trim();
        if (this.activeTitleEl) this.activeTitleEl.textContent = nombre || 'Conversacion';
        if (this.activeSubtitleEl) this.activeSubtitleEl.textContent = contacto.especialidad || 'Medico';

        await this.cargarMensajes(idMedico, true);
    }

    async cargarMensajes(idMedico, autoScroll) {
        try {
            const res = await this.chatApi('listar_conversacion', 'GET', null, {
                id_medico: idMedico,
                limite: 100
            });
            const mensajes = res.data || [];
            this.renderMensajes(mensajes, autoScroll);
            await this.cargarContactosYConversaciones();
        } catch (error) {
            this.setStatus(error.message, true);
        }
    }

    renderMensajes(mensajes, autoScroll) {
        if (!this.messagesEl) return;

        this.messagesEl.innerHTML = '';

        if (!mensajes.length) {
            const p = document.createElement('p');
            p.className = 'text-sm text-gray-500';
            p.textContent = 'No hay mensajes en esta conversacion.';
            this.messagesEl.appendChild(p);
            return;
        }

        mensajes.forEach((m) => {
            const propio = Number(m.id_emisor) === Number(this.usuario.id);
            const row = document.createElement('div');
            row.className = `flex ${propio ? 'justify-end' : 'justify-start'}`;

            const bubble = document.createElement('div');
            bubble.className = propio
                ? 'max-w-[80%] px-3 py-2 rounded-xl bg-blue-600 text-white'
                : 'max-w-[80%] px-3 py-2 rounded-xl bg-white border border-gray-200 text-gray-900';

            const text = document.createElement('p');
            text.className = 'text-sm whitespace-pre-wrap break-words';
            text.textContent = m.mensaje || '';
            bubble.appendChild(text);

            const meta = document.createElement('p');
            meta.className = propio ? 'text-[11px] text-blue-100 mt-1' : 'text-[11px] text-gray-500 mt-1';

            const fecha = this.formatearFecha(m.enviado_en);
            const estado = propio ? (m.leido_en ? 'Leido' : 'Enviado') : 'Recibido';
            meta.textContent = `${fecha} · ${estado}`;
            bubble.appendChild(meta);

            row.appendChild(bubble);
            this.messagesEl.appendChild(row);
        });

        if (autoScroll) {
            this.messagesEl.scrollTop = this.messagesEl.scrollHeight;
        }
    }

    async enviarMensaje() {
        if (!this.contactoActivo) {
            this.setStatus('Selecciona un medico para enviar mensajes.', true);
            return;
        }

        const mensaje = (this.inputEl?.value || '').trim();
        if (!mensaje) {
            this.setStatus('Escribe un mensaje antes de enviar.', true);
            return;
        }

        try {
            if (this.sendBtnEl) this.sendBtnEl.disabled = true;
            this.setStatus('Enviando...', false);

            await this.chatApi('enviar', 'POST', {
                id_receptor: this.contactoActivo.id_medico,
                mensaje
            });

            if (this.inputEl) this.inputEl.value = '';
            this.setStatus('Mensaje enviado.', false);
            await this.cargarMensajes(this.contactoActivo.id_medico, true);
        } catch (error) {
            this.setStatus(error.message, true);
        } finally {
            if (this.sendBtnEl) this.sendBtnEl.disabled = false;
        }
    }

    setStatus(text, isError = false) {
        if (!this.statusEl) return;
        this.statusEl.textContent = text;
        this.statusEl.className = isError ? 'text-sm text-red-600' : 'text-sm text-gray-500';
    }

    formatearFecha(value) {
        if (!value) return '-';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const chatUI = new ChatMedicosUI();
        await chatUI.init();
    } catch (error) {
        console.warn('Chat medico no inicializado:', error.message);
    }
});
