/**
 * SGI Modal Draft Manager (SgiDraft)
 * Sistema inteligente de guardado y restauración de borradores en modales.
 * Permite que los empleados no pierdan lo que estaban llenando si cierran el modal,
 * cambian de pestaña, navegan o recargan la página accidentalmente.
 */
(function (window, document) {
    'use strict';

    const SgiDraft = {
        STORAGE_PREFIX: 'sgi_draft_',
        registeredForms: new Map(),
        debounceTimers: new Map(),
        lastNotifiedMap: new Map(),

        /**
         * Inicializa el autoguardado para un formulario o modal
         * @param {string|HTMLElement} formIdentifier - ID o elemento del formulario
         * @param {string|HTMLElement|null} modalIdentifier - ID o elemento del modal contenedor (opcional)
         * @param {Object} options - Opciones de configuración
         */
        init: function (formIdentifier, modalIdentifier = null, options = {}) {
            const form = typeof formIdentifier === 'string' ? document.getElementById(formIdentifier) : formIdentifier;
            if (!form) return;

            const formId = form.id || ('sgi_form_' + Math.random().toString(36).substring(2, 9));
            if (!form.id) form.id = formId;

            if (this.registeredForms.has(formId)) return;

            const modal = modalIdentifier 
                ? (typeof modalIdentifier === 'string' ? document.getElementById(modalIdentifier) : modalIdentifier)
                : form.closest('.modal');

            const config = Object.assign({
                autoRestore: true,
                showBanner: true,
                showCloseToast: true,
                onRestore: null,
                onDiscard: null
            }, options);

            this.registeredForms.set(formId, { form, modal, config });

            // 1. Escuchar eventos de entrada con debounce para autoguardar
            form.addEventListener('input', (e) => {
                if (this.shouldIgnoreElement(e.target)) return;
                this.debounceSave(formId);
            });

            form.addEventListener('change', (e) => {
                if (this.shouldIgnoreElement(e.target)) return;
                this.debounceSave(formId);
            });

            // 2. Limpiar borrador al enviar el formulario con éxito
            form.addEventListener('submit', () => {
                this.clear(formId);
            });

            // 3. Manejo de eventos del modal Bootstrap (si existe)
            if (modal) {
                // Al abrir el modal: restaurar si existe borrador guardado
                modal.addEventListener('show.bs.modal', () => {
                    if (config.autoRestore && this.hasDraft(formId)) {
                        setTimeout(() => this.restore(formId), 50);
                    }
                });

                // Al cerrar el modal: guardar inmediatamente el estado actual y avisar al empleado
                modal.addEventListener('hide.bs.modal', () => {
                    this.save(formId);
                    if (config.showCloseToast && this.hasDraft(formId)) {
                        this.notifyDraftSaved(formId);
                    }
                });
            } else {
                // Si no es un modal (o formulario directo), restaurar si hay borrador
                if (config.autoRestore && this.hasDraft(formId)) {
                    setTimeout(() => this.restore(formId), 100);
                }
            }

            // Si el modal ya está desplegado al cargar la página
            if (modal && modal.classList.contains('show') && this.hasDraft(formId)) {
                setTimeout(() => this.restore(formId), 50);
            }
        },

        /**
         * Elementos que NO deben guardarse en el borrador
         */
        shouldIgnoreElement: function (el) {
            if (!el || !el.tagName) return true;
            const tag = el.tagName.toLowerCase();
            if (tag === 'button') return true;
            if (tag === 'input') {
                const type = (el.type || '').toLowerCase();
                if (['password', 'file', 'submit', 'reset', 'button'].includes(type)) return true;
                if (el.name === '_token' || el.name === '_method') return true;
            }
            if (el.hasAttribute('data-sgi-no-draft')) return true;
            return false;
        },

        /**
         * Guarda el formulario con debounce (evita saturar localStorage)
         */
        debounceSave: function (formId, delay = 300) {
            if (this.debounceTimers.has(formId)) {
                clearTimeout(this.debounceTimers.get(formId));
            }
            const timer = setTimeout(() => {
                this.save(formId);
                this.debounceTimers.delete(formId);
            }, delay);
            this.debounceTimers.set(formId, timer);
        },

        /**
         * Guarda de forma inmediata el contenido del formulario
         */
        save: function (formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            const data = {};
            let hasContent = false;

            // Recorrer todos los elementos interactivos del formulario
            const elements = form.querySelectorAll('input, textarea, select');
            elements.forEach(el => {
                if (this.shouldIgnoreElement(el)) return;

                const name = el.name || el.id;
                if (!name) return;

                const tag = el.tagName.toLowerCase();
                const type = (el.type || '').toLowerCase();

                if (type === 'checkbox') {
                    if (name.endsWith('[]')) {
                        if (!data[name]) data[name] = [];
                        if (el.checked) {
                            data[name].push(el.value);
                            hasContent = true;
                        }
                    } else {
                        data[name] = el.checked;
                        if (el.checked) hasContent = true;
                    }
                } else if (type === 'radio') {
                    if (el.checked) {
                        data[name] = el.value;
                        hasContent = true;
                    }
                } else {
                    const val = el.value;
                    data[name] = val;
                    // Evaluar si es un valor significativo ingresado por el usuario
                    if (val && typeof val === 'string' && val.trim().length > 0) {
                        // Ignorar valores por defecto triviales si no hay nada más escrito
                        if (!['1', '1.00', 'activo', 'bueno'].includes(val.trim()) || ['nombre', 'descripcion', 'codigoBarra', 'comentario'].includes(name)) {
                            hasContent = true;
                        }
                    }
                }
            });

            // Guardar o eliminar si ya no hay datos significativos
            const key = this.STORAGE_PREFIX + formId;
            if (hasContent && this.isMeaningfulDraft(data)) {
                const payload = {
                    formId: formId,
                    savedAt: Date.now(),
                    dateString: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    data: data
                };
                try {
                    localStorage.setItem(key, JSON.stringify(payload));
                    this.showSavingIndicator(formId);
                } catch (e) {
                    console.warn('[SgiDraft] Error guardando en localStorage:', e);
                }
            } else {
                localStorage.removeItem(key);
                this.removeBanner(formId);
            }
        },

        /**
         * Determina si el borrador contiene información que vale la pena conservar
         */
        isMeaningfulDraft: function (data) {
            if (!data) return false;
            // Si el nombre, descripción, código o comentario tiene texto
            for (const key of ['nombre', 'descripcion', 'codigoBarra', 'comentario', 'numeroSerie', 'modelo', 'marca', 'observaciones']) {
                if (data[key] && typeof data[key] === 'string' && data[key].trim().length > 0) {
                    return true;
                }
            }
            // Si se seleccionaron categorías / etiquetas
            for (const key in data) {
                if (key.includes('etiqueta') || key.includes('categoria')) {
                    if (Array.isArray(data[key]) && data[key].length > 0) return true;
                }
            }
            // Si se seleccionó empresa o ubicación específica
            if (data['empresa_id'] || data['ubicacion_id'] || data['responsable'] || data['empresaVenta_id']) {
                return true;
            }
            return false;
        },

        /**
         * Comprueba si existe un borrador guardado
         */
        hasDraft: function (formId) {
            const key = this.STORAGE_PREFIX + formId;
            const raw = localStorage.getItem(key);
            if (!raw) return false;
            try {
                const parsed = JSON.parse(raw);
                return parsed && parsed.data && this.isMeaningfulDraft(parsed.data);
            } catch (e) {
                return false;
            }
        },

        /**
         * Obtiene los datos del borrador guardado
         */
        getDraft: function (formId) {
            const key = this.STORAGE_PREFIX + formId;
            const raw = localStorage.getItem(key);
            if (!raw) return null;
            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        },

        /**
         * Restaura los datos del borrador en el formulario
         */
        restore: function (formId, customOptions = {}) {
            const draft = this.getDraft(formId);
            if (!draft || !draft.data) return false;

            const form = document.getElementById(formId);
            if (!form) return false;

            const data = draft.data;
            const reg = this.registeredForms.get(formId);
            const config = Object.assign({}, reg ? reg.config : {}, customOptions);

            // 1. Restaurar cada campo en el formulario
            for (const key in data) {
                const val = data[key];

                if (Array.isArray(val)) {
                    // Checkbox múltiple (ej. etiquetasInsertar[])
                    const checkboxes = form.querySelectorAll(`[name="${key}"]`);
                    checkboxes.forEach(cb => {
                        cb.checked = val.includes(cb.value);
                    });
                } else if (typeof val === 'boolean') {
                    // Switch o checkbox booleano individual
                    const el = form.querySelector(`[name="${key}"]`) || form.querySelector(`#${key}`);
                    if (el && el.type === 'checkbox') {
                        el.checked = val;
                        // Sincronizar switches especiales de precio N.A.
                        if (el.id === 'no_aplica_precio_consumible' && typeof window.toggleNoAplicaPrecioConsumible === 'function') {
                            window.toggleNoAplicaPrecioConsumible(el, 'precio');
                        } else if (el.id === 'no_aplica_precio_fijo' && typeof window.toggleNoAplicaPrecio === 'function') {
                            window.toggleNoAplicaPrecio(el, 'precio');
                        } else if (el.id === 'no_aplica_precio_cv' && typeof window.toggleNoAplicaPrecioCV === 'function') {
                            window.toggleNoAplicaPrecioCV(el, 'precio');
                        }
                    }
                } else {
                    // Inputs de texto, número, fecha, textarea o selects
                    const el = form.querySelector(`[name="${key}"]`) || form.querySelector(`#${key}`);
                    if (el) {
                        el.value = val;

                        // Si es select con Select2
                        if (el.tagName.toLowerCase() === 'select') {
                            if (window.jQuery && (window.jQuery(el).data('select2') || window.jQuery(el).hasClass('select2'))) {
                                window.jQuery(el).val(val).trigger('change.select2');
                            } else {
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    }
                }
            }

            // 2. Sincronizaciones especiales de la UI de SGI
            // A) Previsualización del código de barras
            const codigoBarraInput = form.querySelector('#codigoBarra, [name="codigoBarra"]');
            if (codigoBarraInput && codigoBarraInput.value.trim().length > 0) {
                this.syncBarcodePreview(form, codigoBarraInput.value.trim());
            }

            // B) Modo manual de cantidad/existencia si el valor es > 50
            this.syncQuantityMode(form, data);

            // 3. Callback personalizado si fue provisto
            if (typeof config.onRestore === 'function') {
                try {
                    config.onRestore(form, data, draft);
                } catch (e) {
                    console.error('[SgiDraft] Error en onRestore callback:', e);
                }
            }

            // 4. Mostrar banner de borrador recuperado
            if (config.showBanner) {
                this.renderBanner(formId, draft);
            }

            return true;
        },

        /**
         * Sincroniza la previsualización visual del código de barras
         */
        syncBarcodePreview: function (form, code) {
            const previewEl = form.querySelector('[id^="barcode-preview-"]');
            const boxEl = form.querySelector('[id^="preview-box-"]');
            if (previewEl && typeof window.renderBarcodeVisual === 'function') {
                window.renderBarcodeVisual('#' + previewEl.id, code);
                if (boxEl) boxEl.classList.remove('d-none');
            }
        },

        /**
         * Sincroniza los selectores de cantidad / existencia (1-50 o manual)
         */
        syncQuantityMode: function (form, data) {
            // Consumibles
            const existReal = data['existencia'] || data['existencia_real'];
            if (existReal) {
                const num = parseInt(existReal);
                const selectEl = form.querySelector('#existencia_select');
                const inputEl = form.querySelector('#existencia_input');
                const btnToggle = form.querySelector('#btn_toggle_existencia');

                if (num > 50 && typeof window.toggleExistenciaMode === 'function' && selectEl && !selectEl.classList.contains('d-none')) {
                    window.toggleExistenciaMode();
                    if (inputEl) inputEl.value = num;
                } else if (selectEl && num <= 50) {
                    selectEl.value = num;
                }
            }

            // Activos Fijos
            const cantReal = data['cantidad'] || data['cantidad_real'];
            if (cantReal) {
                const num = parseInt(cantReal);
                const selectEl = form.querySelector('#cantidad_select');
                const inputEl = form.querySelector('#cantidad_input');

                if (num > 50 && typeof window.toggleCantidadMode === 'function' && selectEl && !selectEl.classList.contains('d-none')) {
                    window.toggleCantidadMode();
                    if (inputEl) inputEl.value = num;
                } else if (selectEl && num <= 50) {
                    selectEl.value = num;
                }
            }
        },

        /**
         * Renderiza el banner moderno informando que se restauró un borrador
         */
        renderBanner: function (formId, draft) {
            this.removeBanner(formId);

            const form = document.getElementById(formId);
            if (!form) return;

            const modalBody = form.querySelector('.modal-body') || form;
            const timeAgo = this.formatTimeAgo(draft.savedAt);

            const banner = document.createElement('div');
            banner.id = 'sgi-draft-banner-' + formId;
            banner.className = 'sgi-draft-banner alert d-flex align-items-center justify-content-between p-3 mb-3 rounded-3 shadow-sm border-0';
            banner.setAttribute('role', 'alert');
            banner.style.cssText = 'background: rgba(245, 158, 11, 0.12); border-left: 4px solid #f59e0b !important; animation: fadeIn 0.3s ease;';

            banner.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background: rgba(245, 158, 11, 0.22); color: #d97706;">
                        <i class="fas fa-file-signature fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-warning-emphasis d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                            <span>Borrador recuperado</span>
                            <span class="badge bg-warning bg-opacity-25 text-warning-emphasis" style="font-size: 0.72rem; font-weight: normal;">
                                <i class="fas fa-clock me-1"></i>${timeAgo}
                            </span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.3;">
                            Se restauró lo que estabas escribiendo para que no pierdas tu avance.
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-3 d-flex align-items-center gap-1 shadow-xs" onclick="SgiDraft.discard('${formId}')" title="Borrar datos guardados e iniciar formulario limpio">
                        <i class="fas fa-trash-alt me-1"></i>
                        <span>Descartar borrador</span>
                    </button>
                </div>
            `;

            modalBody.insertBefore(banner, modalBody.firstChild);
        },

        /**
         * Remueve el banner de borrador
         */
        removeBanner: function (formId) {
            const banner = document.getElementById('sgi-draft-banner-' + formId);
            if (banner) {
                banner.style.opacity = '0';
                banner.style.transition = 'opacity 0.2s ease';
                setTimeout(() => banner.remove(), 200);
            }
        },

        /**
         * Muestra un indicador visual discreto mientras el usuario escribe
         */
        showSavingIndicator: function (formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            let pill = form.querySelector('.sgi-draft-saving-pill');
            if (!pill) {
                const header = form.querySelector('.modal-header');
                if (header) {
                    pill = document.createElement('span');
                    pill.className = 'sgi-draft-saving-pill badge bg-light bg-opacity-20 text-white ms-auto me-2 px-2 py-1';
                    pill.style.cssText = 'font-size: 0.72rem; font-weight: normal; transition: opacity 0.3s ease; opacity: 0;';
                    pill.innerHTML = '<i class="fas fa-check-circle me-1 text-warning"></i>Borrador al día';
                    header.insertBefore(pill, header.querySelector('.btn-close'));
                }
            }

            if (pill) {
                pill.style.opacity = '1';
                clearTimeout(pill._hideTimeout);
                pill._hideTimeout = setTimeout(() => {
                    pill.style.opacity = '0';
                }, 1800);
            }
        },

        /**
         * Descarta el borrador y restablece el formulario a blanco
         */
        discard: function (formId) {
            this.clear(formId);

            const form = document.getElementById(formId);
            if (!form) return;

            // Restablecer campos estándar
            form.reset();

            // Limpiar campos de texto ocultos o no reseteados por form.reset()
            const realInputs = form.querySelectorAll('input[type="hidden"]');
            realInputs.forEach(i => {
                if (i.name === 'existencia' || i.name === 'cantidad') i.value = '1';
            });

            // Restablecer previsualización del código de barras
            const previewEl = form.querySelector('[id^="barcode-preview-"]');
            const boxEl = form.querySelector('[id^="preview-box-"]');
            if (previewEl) previewEl.innerHTML = '';
            if (boxEl) boxEl.classList.add('d-none');

            // Restablecer switches de precio N.A.
            const switchConsumible = form.querySelector('#no_aplica_precio_consumible');
            if (switchConsumible && switchConsumible.checked) {
                switchConsumible.checked = false;
                if (typeof window.toggleNoAplicaPrecioConsumible === 'function') {
                    window.toggleNoAplicaPrecioConsumible(switchConsumible, 'precio');
                }
            }
            const switchFijo = form.querySelector('#no_aplica_precio_fijo');
            if (switchFijo && switchFijo.checked) {
                switchFijo.checked = false;
                if (typeof window.toggleNoAplicaPrecio === 'function') {
                    window.toggleNoAplicaPrecio(switchFijo, 'precio');
                }
            }
            const switchCv = form.querySelector('#no_aplica_precio_cv');
            if (switchCv && switchCv.checked) {
                switchCv.checked = false;
                if (typeof window.toggleNoAplicaPrecioCV === 'function') {
                    window.toggleNoAplicaPrecioCV(switchCv, 'precio');
                }
            }

            // Restablecer selectores de cantidad a la lista (1-50)
            const inputExistencia = form.querySelector('#existencia_input');
            const selectExistencia = form.querySelector('#existencia_select');
            if (inputElExistenciaActive(inputExistencia)) {
                if (typeof window.toggleExistenciaMode === 'function') window.toggleExistenciaMode();
            }
            if (selectExistencia) selectExistencia.value = '1';

            const inputCantidad = form.querySelector('#cantidad_input');
            const selectCantidad = form.querySelector('#cantidad_select');
            if (inputElExistenciaActive(inputCantidad)) {
                if (typeof window.toggleCantidadMode === 'function') window.toggleCantidadMode();
            }
            if (selectCantidad) selectCantidad.value = '1';

            // Restablecer Select2 si está presente
            if (window.jQuery) {
                window.jQuery(form).find('select.select2').val('').trigger('change.select2');
            }

            // Quitar banner
            this.removeBanner(formId);

            // Callback personalizado de descarte
            const reg = this.registeredForms.get(formId);
            if (reg && typeof reg.config.onDiscard === 'function') {
                try {
                    reg.config.onDiscard(form);
                } catch (e) {
                    console.error('[SgiDraft] Error en onDiscard callback:', e);
                }
            }

            // Notificación al usuario
            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion('Borrador descartado. Formulario restablecido.', 'info', 3000);
            }
        },

        /**
         * Borra el borrador de localStorage
         */
        clear: function (formId) {
            const key = this.STORAGE_PREFIX + formId;
            localStorage.removeItem(key);
            this.removeBanner(formId);
        },

        /**
         * Muestra una notificación cuando el usuario cierra el modal teniendo datos guardados
         */
        notifyDraftSaved: function (formId) {
            const now = Date.now();
            const last = this.lastNotifiedMap.get(formId) || 0;
            // No saturar con toasts si abre y cierra rápidamente en menos de 5 segundos
            if (now - last < 5000) return;
            this.lastNotifiedMap.set(formId, now);

            if (typeof window.mostrarNotificacion === 'function') {
                window.mostrarNotificacion(
                    'Tu información se guardó como borrador. Podrás continuar cuando vuelvas a abrir el formulario.',
                    'info',
                    4000
                );
            }
        },

        /**
         * Formatea el tiempo transcurrido de forma amigable (hace 2 min, etc.)
         */
        formatTimeAgo: function (timestamp) {
            if (!timestamp) return 'recientemente';
            const diffSeconds = Math.floor((Date.now() - timestamp) / 1000);
            if (diffSeconds < 45) return 'hace un momento';
            const diffMinutes = Math.floor(diffSeconds / 60);
            if (diffMinutes < 60) return `hace ${diffMinutes} min`;
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `hace ${diffHours} h`;
            return new Date(timestamp).toLocaleDateString([], { day: '2-digit', month: '2-digit' });
        },

        /**
         * Auto-descubrimiento y registro de formularios estándar en SGI
         */
        autoRegister: function () {
            // 1. Formularios con atributo explícito data-sgi-draft="true"
            document.querySelectorAll('form[data-sgi-draft="true"]').forEach(form => {
                this.init(form);
            });

            // 2. Formularios clave de creación de productos en SGI
            const knownForms = [
                { formId: 'formInsertarProductoConsumible', modalId: 'insertarProductoConsumibleModal' },
                { formId: 'formInsertarProductoFijo', modalId: 'insertarProductoFijoModal' },
                { formId: 'formInsertarProductoCompraVenta', modalId: 'insertarProductoCompraVentaModal' },
                { formId: 'formInsertarUsuario', modalId: 'modalInsertarUsuario' }
            ];

            knownForms.forEach(item => {
                const form = document.getElementById(item.formId);
                if (form) {
                    this.init(form, item.modalId);
                }
            });
        }
    };

    function inputElExistenciaActive(el) {
        return el && !el.classList.contains('d-none');
    }

    // Exponer globalmente
    window.SgiDraft = SgiDraft;

    // Inicializar automáticamente cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SgiDraft.autoRegister());
    } else {
        SgiDraft.autoRegister();
    }

    // Reintentar cuando se dispare cualquier show.bs.modal (por si el modal fue inyectado dinámicamente)
    document.addEventListener('show.bs.modal', function (e) {
        const modal = e.target;
        if (!modal) return;
        const form = modal.querySelector('form');
        if (form && !SgiDraft.registeredForms.has(form.id)) {
            if (form.getAttribute('data-sgi-draft') === 'true' || ['formInsertarProductoConsumible', 'formInsertarProductoFijo', 'formInsertarProductoCompraVenta'].includes(form.id)) {
                SgiDraft.init(form, modal);
            }
        }
    });

})(window, document);
