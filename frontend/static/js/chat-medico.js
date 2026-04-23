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
        this.totalNoLeidosGlobal = 0;
        this.contactoActivo = null;
        this.refreshTimer = null;
        this.unreadRefreshTimer = null;
        this.searchDebounceTimer = null;
        this.searchTerm = '';
        this.selectedFile = null;
        this.previewObjectUrl = null;

        this.contactListEl = document.getElementById('chatContactList');
        this.messagesEl = document.getElementById('chatMessages');
        this.activeTitleEl = document.getElementById('chatActiveTitle');
        this.activeSubtitleEl = document.getElementById('chatActiveSubtitle');
        this.avatarEl = document.getElementById('chatAvatar');
        this.formEl = document.getElementById('chatForm');
        this.inputEl = document.getElementById('chatInput');
        this.sendBtnEl = document.getElementById('chatSendBtn');
        this.statusEl = document.getElementById('chatMessageStatus');
        this.refreshBtnEl = document.getElementById('chatRefreshBtn');
        this.searchInputEl = document.getElementById('chatSearchInput');
        this.attachBtnEl = document.getElementById('chatAttachBtn');
        this.fileInputEl = document.getElementById('chatFileInput');
        this.attachmentPreviewEl = document.getElementById('chatAttachmentPreview');
        this.removeAttachmentBtn = null;
        this.mensajesTabEl = document.getElementById('tab-mensajes');
        this.navMensajesBadgeEl = document.getElementById('navMensajesBadge');
        this.navMensajesLinkEl = document.querySelector('.nav-link[data-tab="mensajes"]');
    }

    isMensajesTabVisible() {
        return Boolean(this.mensajesTabEl && !this.mensajesTabEl.classList.contains('hidden'));
    }

    async init() {
        if (!this.contactListEl || !this.messagesEl) {
            return;
        }

        await this.cargarSesion();
        this.bindEvents();
        await this.actualizarSoloNoLeidos();
        await this.cargarContactosYConversaciones();
        this.iniciarAutoRefresh();
        this.iniciarAutoRefreshNoLeidos();
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

        this.searchInputEl?.addEventListener('input', (e) => {
            const val = (e.target?.value || '').trim();
            this.searchTerm = val;
            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }
            this.searchDebounceTimer = setTimeout(async () => {
                await this.cargarContactosYConversaciones();
            }, 220);
        });

        this.attachBtnEl?.addEventListener('click', () => {
            this.fileInputEl?.click();
        });

        this.fileInputEl?.addEventListener('change', () => {
            const f = this.fileInputEl?.files?.[0] || null;
            this.selectedFile = f;
            this.renderAttachmentPreview();
        });

        document.addEventListener('visibilitychange', async () => {
            if (!document.hidden) {
                await this.actualizarSoloNoLeidos();
            }
        });

        window.addEventListener('focus', async () => {
            await this.actualizarSoloNoLeidos();
        });

        this.navMensajesLinkEl?.addEventListener('click', async () => {
            // Fuerza refresco inmediato al abrir la pestaña de mensajes.
            await this.cargarContactosYConversaciones();
            this.contactoActivo = null;
            this.renderContactos();
            this.renderMensajes([], false);
            if (this.activeTitleEl) this.activeTitleEl.textContent = 'Selecciona una conversación';
            if (this.activeSubtitleEl) this.activeSubtitleEl.textContent = 'Mensajería cifrada en reposo (AES-256-GCM)';
            if (this.avatarEl) this.avatarEl.textContent = '--';
        });

        this.renderAttachmentPreview();
    }

    iniciarAutoRefreshNoLeidos() {
        if (this.unreadRefreshTimer) {
            clearInterval(this.unreadRefreshTimer);
        }

        this.unreadRefreshTimer = setInterval(async () => {
            await this.actualizarSoloNoLeidos();
        }, 4000);
    }

    iniciarAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        this.refreshTimer = setInterval(async () => {
            const tabVisible = this.isMensajesTabVisible();
            if (!tabVisible) {
                await this.actualizarSoloNoLeidos();
                return;
            }

            await this.cargarContactosYConversaciones();
            if (this.contactoActivo) {
                await this.cargarMensajes(this.contactoActivo.id_medico, false);
            }
        }, 12000);
    }

    async actualizarSoloNoLeidos() {
        try {
            const [resConversaciones, resNoLeidos] = await Promise.all([
                this.chatApi('listar_conversaciones'),
                this.chatApi('contar_no_leidos')
            ]);
            this.conversaciones = resConversaciones.data || [];
            this.totalNoLeidosGlobal = Number(resNoLeidos?.data?.total_no_leidos || 0);
            this.actualizarBadgeMensajes();
        } catch (_error) {
            // Silencioso: evita ruido visual en el resto de pestañas.
        }
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
                this.chatApi('listar_medicos', 'GET', null, {
                    q: this.searchTerm || ''
                }),
                this.chatApi('listar_conversaciones')
            ]);

            this.contactos = resMedicos.data || [];
            this.conversaciones = resConversaciones.data || [];
            this.actualizarBadgeMensajes();

            if (this.contactoActivo) {
                const contactoActivoId = this.getContactoId(this.contactoActivo);
                const sigueVisible = this.contactos.some((m) => Number(m.id_medico) === contactoActivoId);
                if (!sigueVisible) {
                    this.contactoActivo = null;
                    this.renderMensajes([], false);
                    if (this.activeTitleEl) this.activeTitleEl.textContent = 'Selecciona una conversación';
                    if (this.activeSubtitleEl) this.activeSubtitleEl.textContent = 'Mensajería cifrada en reposo (AES-256-GCM)';
                    if (this.avatarEl) this.avatarEl.textContent = '--';
                }
            }

            this.renderContactos();

            if (!this.contactoActivo && this.isMensajesTabVisible()) {
                this.renderMensajes([], false);
            }
        } catch (error) {
            this.setStatus(error.message, true);
        }
    }

    primerContactoConPrioridad() {
        if (this.conversaciones.length > 0) {
            const primero = this.conversaciones[0];
            const idConversacion = Number(primero.id_contacto || primero.id_medico || 0);
            const contacto = this.contactos.find((m) => Number(m.id_medico) === idConversacion);
            return contacto || primero;
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

        const unicos = new Map();
        this.contactos.forEach((m) => {
            unicos.set(Number(m.id_medico), m);
        });

        const mezclados = Array.from(unicos.values()).map((m) => {
            const conv = mapaConversaciones.get(Number(m.id_medico));
            return {
                id_medico: Number(m.id_medico),
                nombre: m.nombre,
                apellidos: m.apellidos,
                num_colegiado: m.num_colegiado,
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
            btn.className = 'w-full text-left p-3 rounded-2xl border transition-all duration-200 relative overflow-hidden';

            const activo = this.contactoActivo && Number(this.contactoActivo.id_medico) === Number(c.id_medico);
            if (activo) {
                btn.classList.add('bg-white', 'border-cyan-300', 'shadow-[0_8px_24px_rgba(8,47,73,0.08)]', 'ring-1', 'ring-cyan-200', 'scale-[1.01]');
            } else {
                btn.classList.add('bg-cyan-50/80', 'border-cyan-100', 'hover:bg-white', 'hover:border-cyan-200', 'hover:translate-x-0.5');
            }

            const nombreCompleto = `${c.nombre || ''} ${c.apellidos || ''}`.trim();
            const initials = this.obtenerIniciales(nombreCompleto);
            const tieneNoLeidos = Number(c.no_leidos || 0) > 0;

            const header = document.createElement('div');
            header.className = 'flex items-center gap-3 pr-10';

            const avatar = document.createElement('div');
            avatar.className = 'w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 via-cyan-500 to-sky-600 text-white text-xs font-semibold flex items-center justify-center shrink-0 shadow-sm';
            avatar.textContent = initials;

            const content = document.createElement('div');
            content.className = 'flex-1 min-w-0';

            const top = document.createElement('div');
            top.className = 'flex items-start justify-between gap-2';

            const title = document.createElement('p');
            title.className = 'font-semibold text-slate-900 text-sm leading-tight whitespace-normal break-words';
            title.textContent = nombreCompleto || `Medico ${c.id_medico}`;
            top.appendChild(title);

            const sub = document.createElement('p');
            sub.className = 'text-xs text-slate-500 mt-1 whitespace-normal break-words leading-tight';
            const colegiado = c.num_colegiado ? ` · Col. ${c.num_colegiado}` : '';
            sub.textContent = `${c.especialidad || 'Medico'}${colegiado}`;

            const footer = document.createElement('div');
            footer.className = 'mt-2 flex items-center justify-between gap-2';

            const preview = document.createElement('p');
            preview.className = 'text-[11px] text-slate-600 whitespace-normal break-words leading-tight';
            preview.textContent = c.ultimo_envio ? 'Último mensaje reciente' : 'Sin mensajes todavía';

            const meta = document.createElement('span');
            meta.className = 'text-[11px] text-slate-400 shrink-0 leading-none';
            meta.textContent = c.ultimo_envio ? this.formatearHora(c.ultimo_envio) : '';

            const rightFooter = document.createElement('div');
            rightFooter.className = 'flex flex-col items-end gap-1 shrink-0';

            if (tieneNoLeidos) {
                const badge = document.createElement('span');
                badge.className = 'inline-flex w-7 h-7 items-center justify-center rounded-full bg-red-950 text-blue-900 text-[11px] font-bold leading-none ring-2 ring-white shadow-md';
                badge.textContent = Number(c.no_leidos) > 99 ? '99+' : String(c.no_leidos);
                rightFooter.appendChild(badge);
            }

            rightFooter.appendChild(meta);

            footer.appendChild(preview);
            footer.appendChild(rightFooter);

            content.appendChild(top);
            content.appendChild(sub);
            content.appendChild(footer);
            header.appendChild(avatar);
            header.appendChild(content);
            btn.appendChild(header);

            btn.addEventListener('click', async () => {
                await this.seleccionarContacto(c.id_medico);
            });

            this.contactListEl.appendChild(btn);
        });
    }

    async seleccionarContacto(idMedico) {
        const idNormalizado = this.getContactoId(idMedico);
        let contacto = this.contactos.find((m) => Number(m.id_medico) === idNormalizado);

        if (!contacto && idNormalizado) {
            contacto = this.conversaciones
                .map((c) => this.contactos.find((m) => Number(m.id_medico) === Number(c.id_contacto)) || null)
                .find((m) => m && Number(m.id_medico) === idNormalizado) || null;
        }

        if (!contacto) {
            this.setStatus('No se encontro el medico seleccionado.', true);
            return;
        }

        this.contactoActivo = contacto;
        this.renderContactos();

        const nombre = `${contacto.nombre || ''} ${contacto.apellidos || ''}`.trim();
        if (this.activeTitleEl) this.activeTitleEl.textContent = nombre || 'Conversacion';
        const colegiado = contacto.num_colegiado ? ` · Col. ${contacto.num_colegiado}` : '';
        if (this.activeSubtitleEl) this.activeSubtitleEl.textContent = `${contacto.especialidad || 'Medico'}${colegiado}`;
        if (this.avatarEl) this.avatarEl.textContent = this.obtenerIniciales(nombre);

        await this.cargarMensajes(idNormalizado, true);
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

        const distanciaAlFinal = this.messagesEl.scrollHeight - this.messagesEl.scrollTop - this.messagesEl.clientHeight;
        const estabaCercaDelFinal = distanciaAlFinal < 120;

        this.messagesEl.innerHTML = '';

        if (!mensajes.length) {
            const p = document.createElement('p');
            p.className = 'text-sm text-gray-500';
            p.textContent = 'No hay mensajes en esta conversacion.';
            this.messagesEl.appendChild(p);
            return;
        }

        let ultimoDia = null;

        mensajes.forEach((m) => {
            const diaClave = this.obtenerClaveDia(m.enviado_en);
            if (diaClave && diaClave !== ultimoDia) {
                const separadorRow = document.createElement('div');
                separadorRow.className = 'flex justify-center my-2';

                const separador = document.createElement('span');
                separador.className = 'px-3 py-1 rounded-full text-[11px] font-medium bg-slate-200 text-slate-600';
                separador.textContent = this.formatearEtiquetaDia(m.enviado_en);

                separadorRow.appendChild(separador);
                this.messagesEl.appendChild(separadorRow);
                ultimoDia = diaClave;
            }

            const propio = Number(m.id_emisor) === this.getUsuarioId();
            const row = document.createElement('div');
            row.className = propio ? 'flex w-full items-end justify-end' : 'flex w-full items-end justify-start';
            row.style.display = 'flex';
            row.style.justifyContent = propio ? 'flex-end' : 'flex-start';

            const bubble = document.createElement('div');
            bubble.className = propio
                ? 'w-fit ml-auto max-w-[92%] min-w-[250px] px-4 py-1 rounded-2xl rounded-br-md bg-gradient-to-br from-[#0f172a] via-[#1d4ed8] to-[#0ea5e9] text-white shadow-[0_14px_30px_rgba(2,6,23,0.38)] border border-[#1e3a8a] hover:shadow-[0_18px_38px_rgba(2,6,23,0.48)] transition-shadow'
                : 'w-fit mr-auto max-w-[92%] min-w-[250px] px-4 py-1 rounded-2xl rounded-bl-md bg-white border border-slate-200 text-slate-950 shadow-[0_10px_24px_rgba(15,23,42,0.10)] hover:shadow-[0_14px_30px_rgba(15,23,42,0.14)] transition-shadow';

            if (propio) {
                bubble.classList.add('ml-auto');
                bubble.style.width = 'fit-content';
                bubble.style.maxWidth = '92%';
                bubble.style.minWidth = '250px';
                bubble.style.padding = '4px 14px';
                bubble.style.background = 'linear-gradient(135deg, #0f172a 0%, #1d4ed8 55%, #0ea5e9 100%)';
                bubble.style.color = '#ffffff';
                bubble.style.borderColor = '#1e3a8a';
                bubble.style.boxShadow = '0 14px 30px rgba(2, 6, 23, 0.38)';
            }

            if (!propio) {
                bubble.style.width = 'fit-content';
                bubble.style.maxWidth = '92%';
                bubble.style.minWidth = '250px';
                bubble.style.padding = '4px 14px';
            }

            if (!propio) {
                const author = document.createElement('p');
                author.className = 'text-[11px] font-semibold text-cyan-700 mb-1';
                author.textContent = this.activeTitleEl?.textContent || 'Médico';
                bubble.appendChild(author);
            }

            const text = document.createElement('p');
            text.className = propio ? 'text-base leading-snug whitespace-pre-wrap break-words text-white' : 'text-base leading-snug whitespace-pre-wrap break-words text-slate-950';
            text.textContent = m.mensaje || '';
            bubble.appendChild(text);

            if (m.tipo_contenido === 'archivo' && m.archivo_url) {
                const fileCard = document.createElement('a');
                fileCard.href = m.archivo_url;
                fileCard.target = '_blank';
                fileCard.rel = 'noopener noreferrer';
                fileCard.className = propio
                    ? 'mt-2 flex items-center gap-2 p-2 rounded-lg bg-white/10 hover:bg-white/15 border border-white/20'
                    : 'mt-2 flex items-center gap-2 p-2 rounded-lg bg-cyan-50 hover:bg-cyan-100 border border-cyan-100';

                const icon = document.createElement('span');
                icon.className = 'text-base';
                icon.textContent = this.iconoPorArchivo(m.nombre_archivo);

                const info = document.createElement('div');
                info.className = 'min-w-0';

                const name = document.createElement('p');
                name.className = propio ? 'text-xs font-semibold text-white truncate' : 'text-xs font-semibold text-slate-800 truncate';
                name.textContent = m.nombre_archivo || 'Adjunto';

                const size = document.createElement('p');
                size.className = propio ? 'text-[11px] text-cyan-100' : 'text-[11px] text-slate-500';
                size.textContent = this.formatearTamano(m.tamano_bytes);

                info.appendChild(name);
                info.appendChild(size);

                fileCard.appendChild(icon);
                fileCard.appendChild(info);
                bubble.appendChild(fileCard);

                if (this.esImagenAdjunta(m.nombre_archivo)) {
                    const imageWrap = document.createElement('a');
                    imageWrap.href = m.archivo_url;
                    imageWrap.target = '_blank';
                    imageWrap.rel = 'noopener noreferrer';
                    imageWrap.className = 'block mt-2';

                    const img = document.createElement('img');
                    img.src = m.archivo_url;
                    img.alt = m.nombre_archivo || 'Imagen adjunta';
                    img.className = propio
                        ? 'max-h-48 rounded-lg border border-white/20 object-cover'
                        : 'max-h-48 rounded-lg border border-black/10 object-cover';
                    img.loading = 'lazy';

                    imageWrap.appendChild(img);
                    bubble.appendChild(imageWrap);
                }
            }

            const meta = document.createElement('p');
            meta.className = propio ? 'text-[10px] text-cyan-100 mt-1' : 'text-[10px] text-gray-500 mt-1';

            const fecha = this.formatearHora(m.enviado_en);
            const estado = propio ? (m.leido_en ? 'Leido' : 'Enviado') : 'Recibido';
            meta.textContent = `${fecha} · ${estado}`;
            bubble.appendChild(meta);

            row.appendChild(bubble);
            this.messagesEl.appendChild(row);
        });

        if (autoScroll || estabaCercaDelFinal) {
            this.messagesEl.scrollTo({
                top: this.messagesEl.scrollHeight,
                behavior: autoScroll ? 'auto' : 'smooth'
            });
        }
    }

    async enviarMensaje() {
        if (!this.contactoActivo) {
            this.setStatus('Selecciona un medico para enviar mensajes.', true);
            return;
        }

        const mensaje = (this.inputEl?.value || '').trim();
        if (!mensaje && !this.selectedFile) {
            this.setStatus('Escribe un mensaje o adjunta un archivo.', true);
            return;
        }

        try {
            if (this.sendBtnEl) this.sendBtnEl.disabled = true;
            this.setStatus('Enviando...', false);

            if (this.selectedFile) {
                const formData = new FormData();
                formData.append('id_receptor', String(this.contactoActivo.id_medico));
                formData.append('mensaje', mensaje);
                formData.append('archivo', this.selectedFile);

                await fetch(`${CHAT_API}?accion=enviar_archivo`, {
                    method: 'POST',
                    body: formData
                }).then(async (response) => {
                    const raw = await response.text();
                    let payload = null;
                    try {
                        payload = raw ? JSON.parse(raw) : null;
                    } catch (_e) {
                        throw new Error('Respuesta invalida del servidor de chat');
                    }
                    if (!response.ok || !payload?.success) {
                        throw new Error(payload?.mensaje || `Error HTTP ${response.status}`);
                    }
                    return payload;
                });
            } else {
                await this.chatApi('enviar', 'POST', {
                    id_receptor: this.contactoActivo.id_medico,
                    mensaje
                });
            }

            if (this.inputEl) this.inputEl.value = '';
            if (this.fileInputEl) this.fileInputEl.value = '';
            this.selectedFile = null;
            this.renderAttachmentPreview();
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
        this.statusEl.className = isError ? 'text-xs text-red-600' : 'text-xs text-slate-500';
    }

    obtenerIniciales(nombreCompleto) {
        if (!nombreCompleto) return 'DR';
        const parts = String(nombreCompleto).trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return 'DR';
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
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

    renderAttachmentPreview() {
        if (!this.attachmentPreviewEl) return;

        if (this.previewObjectUrl) {
            URL.revokeObjectURL(this.previewObjectUrl);
            this.previewObjectUrl = null;
        }

        if (!this.selectedFile) {
            this.attachmentPreviewEl.classList.add('hidden');
            this.attachmentPreviewEl.textContent = '';
            this.attachmentPreviewEl.innerHTML = '';
            return;
        }
        this.attachmentPreviewEl.classList.remove('hidden');
        this.attachmentPreviewEl.innerHTML = '';

        const wrap = document.createElement('div');
        wrap.className = 'flex items-center justify-between gap-3';

        const info = document.createElement('div');
        info.className = 'min-w-0 flex items-center gap-2';

        const badge = document.createElement('span');
        badge.className = 'text-xs font-semibold px-2 py-1 rounded-full bg-slate-900 text-white';
        badge.textContent = this.iconoPorArchivo(this.selectedFile.name);

        const textWrap = document.createElement('div');
        textWrap.className = 'min-w-0';

        const fileName = document.createElement('p');
        fileName.className = 'text-xs font-medium text-slate-700 truncate';
        fileName.textContent = this.selectedFile.name;

        const fileSize = document.createElement('p');
        fileSize.className = 'text-[11px] text-slate-500';
        fileSize.textContent = this.formatearTamano(this.selectedFile.size);

        textWrap.appendChild(fileName);
        textWrap.appendChild(fileSize);
        info.appendChild(badge);
        info.appendChild(textWrap);

        if (this.esImagenAdjunta(this.selectedFile.name)) {
            this.previewObjectUrl = URL.createObjectURL(this.selectedFile);
            const thumb = document.createElement('img');
            thumb.src = this.previewObjectUrl;
            thumb.alt = 'Vista previa';
            thumb.className = 'w-12 h-12 rounded-lg object-cover border border-slate-200';
            info.insertBefore(thumb, textWrap);
        }

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'w-7 h-7 rounded-full bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center';
        removeBtn.title = 'Quitar archivo';
        removeBtn.setAttribute('aria-label', 'Quitar archivo');
        removeBtn.textContent = '×';
        removeBtn.addEventListener('click', () => {
            this.selectedFile = null;
            if (this.fileInputEl) this.fileInputEl.value = '';
            this.renderAttachmentPreview();
        });

        wrap.appendChild(info);
        wrap.appendChild(removeBtn);
        this.attachmentPreviewEl.appendChild(wrap);
        this.attachmentPreviewEl.classList.remove('hidden');
    }

    formatearTamano(bytes) {
        const value = Number(bytes || 0);
        if (!value || value <= 0) return 'tamano no disponible';
        if (value < 1024) return `${value} B`;
        if (value < 1024 * 1024) return `${(value / 1024).toFixed(1)} KB`;
        return `${(value / (1024 * 1024)).toFixed(1)} MB`;
    }

    iconoPorArchivo(name) {
        const file = String(name || '').toLowerCase();
        if (file.endsWith('.png') || file.endsWith('.jpg') || file.endsWith('.jpeg')) return '[IMG]';
        if (file.endsWith('.pdf')) return '[PDF]';
        if (file.endsWith('.doc') || file.endsWith('.docx')) return '[DOC]';
        return '[FILE]';
    }

    esImagenAdjunta(name) {
        const file = String(name || '').toLowerCase();
        return file.endsWith('.png') || file.endsWith('.jpg') || file.endsWith('.jpeg');
    }

    obtenerClaveDia(value) {
        if (!value) return null;
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return null;
        return `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`;
    }

    formatearEtiquetaDia(value) {
        if (!value) return '';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return '';

        const hoy = new Date();
        const hoyClave = `${hoy.getFullYear()}-${hoy.getMonth() + 1}-${hoy.getDate()}`;
        const diaClave = `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`;

        if (diaClave === hoyClave) {
            return 'Hoy';
        }

        return d.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    formatearHora(value) {
        if (!value) return '-';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    actualizarBadgeMensajes() {
        const totalConversaciones = (this.conversaciones || []).reduce((acc, item) => acc + Number(item.no_leidos || 0), 0);
        const totalContactos = (this.contactos || []).reduce((acc, item) => acc + Number(item.no_leidos || 0), 0);
        const totalGlobal = Number(this.totalNoLeidosGlobal || 0);
        const total = Math.max(totalConversaciones, totalContactos, totalGlobal);

        const badges = [this.navMensajesBadgeEl].filter(Boolean);
        if (!badges.length) return;

        if (total > 0) {
            badges.forEach((badgeEl) => {
                badgeEl.textContent = total > 99 ? '99+' : String(total);
                badgeEl.classList.remove('hidden');
                badgeEl.classList.add('inline-flex');
                badgeEl.style.display = 'inline-flex';
                badgeEl.style.visibility = 'visible';
                badgeEl.style.opacity = '1';
            });
        } else {
            badges.forEach((badgeEl) => {
                badgeEl.textContent = '0';
                badgeEl.classList.add('hidden');
                badgeEl.classList.remove('inline-flex');
                badgeEl.style.display = 'none';
                badgeEl.style.visibility = 'hidden';
                badgeEl.style.opacity = '0';
            });
        }
    }

    getUsuarioId() {
        return Number(this.usuario?.id_medico ?? this.usuario?.id ?? 0);
    }

    getContactoId(contacto) {
        if (contacto && typeof contacto === 'object') {
            return Number(contacto.id_medico ?? contacto.id_contacto ?? 0);
        }

        return Number(contacto ?? 0);
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
