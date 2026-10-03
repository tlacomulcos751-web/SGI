// ======================================
// SGI PREMIUM HELPERS
// ======================================

// ======================================
// 1. SISTEMA DE TEMA CLARO / OSCURO
// ======================================
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    updateThemeToggle();
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeToggle();
}
window.toggleTheme = toggleTheme;

function updateThemeToggle() {
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        themeToggle.innerHTML = currentTheme === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

// ======================================
// 2. MANEJO DE MODALES
// ======================================
function openModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalObj.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
    }
}

function closeModal(modalId) {
    const modalEl = document.getElementById(modalId);
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalObj = bootstrap.Modal.getInstance(modalEl);
            if (modalObj) {
                modalObj.hide();
            } else {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
            }
        } else {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
        }
    }
}
window.openModal = openModal;
window.closeModal = closeModal;

// ======================================
// 3. EFECTOS RIPPLE EN BOTONES
// ======================================
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('btn') || event.target.closest('.btn')) {
        const btn = event.target.classList.contains('btn') ? event.target : event.target.closest('.btn');
        const ripple = document.createElement('span');
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (event.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (event.clientY - rect.top - size / 2) + 'px';
        ripple.classList.add('ripple');
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    }
});

// ======================================
// 4. NOTIFICACIONES (TOASTS)
// ======================================
let toastContainer = null;

function mostrarNotificacion(mensaje, tipo = 'info', duracion = 4000) {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    
    let icon = '<i class="fas fa-info-circle"></i>';
    let title = 'Información';
    if (tipo === 'success') { icon = '<i class="fas fa-check-circle"></i>'; title = 'Éxito'; }
    if (tipo === 'error') { icon = '<i class="fas fa-times-circle"></i>'; title = 'Error'; }
    if (tipo === 'warning') { icon = '<i class="fas fa-exclamation-triangle"></i>'; title = 'Atención'; }

    toast.innerHTML = `
        <div class="toast-progress" style="animation: toastProgress ${duracion}ms linear forwards;"></div>
        <div class="toast-icon">${icon}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${mensaje}</div>
        </div>
        <button class="toast-close">✕</button>
    `;
    
    toastContainer.appendChild(toast);
    
    const closeBtn = toast.querySelector('.toast-close');
    let isClosed = false;
    
    const closeToast = () => {
        if (isClosed) return;
        isClosed = true;
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 300);
    };
    
    closeBtn.addEventListener('click', closeToast);
    if (duracion > 0) {
        setTimeout(closeToast, duracion);
    }
}
window.mostrarNotificacion = mostrarNotificacion;

if (!document.getElementById('toast-keyframes')) {
    const style = document.createElement('style');
    style.id = 'toast-keyframes';
    style.innerHTML = '@keyframes toastProgress { from { width: 100%; } to { width: 0%; } }';
    document.head.appendChild(style);
}

// ======================================
// 5. DIÁLOGOS DE CONFIRMACIÓN
// ======================================
function mostrarDialogoPremium(mensaje, onConfirm) {
    const existing = document.getElementById('custom-dialog-overlay');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'custom-dialog-overlay';
    overlay.className = 'custom-dialog-overlay';
    
    const isDanger = mensaje.toLowerCase().includes('eliminar') || mensaje.toLowerCase().includes('rechazar');
    const iconColor = isDanger ? 'var(--danger)' : 'var(--primary)';
    const iconBg = isDanger ? 'var(--danger-bg)' : 'var(--primary-glow)';
    const icon = isDanger ? '<i class="fas fa-exclamation-triangle"></i>' : '<i class="fas fa-question-circle"></i>';

    overlay.innerHTML = `
        <div class="custom-dialog">
            <div class="custom-dialog-icon" style="color: ${iconColor}; background: ${iconBg};">
                ${icon}
            </div>
            <h3 class="custom-dialog-title">Confirmación</h3>
            <p class="custom-dialog-msg">${mensaje}</p>
            <div class="custom-dialog-actions">
                <button type="button" class="btn btn-secondary" id="cd-cancel">Cancelar</button>
                <button type="button" class="btn ${isDanger ? 'btn-danger' : 'btn-primary'}" id="cd-confirm">Aceptar</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);

    document.getElementById('cd-cancel').addEventListener('click', () => {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 200);
    });

    document.getElementById('cd-confirm').addEventListener('click', () => {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.remove();
            if (onConfirm) onConfirm();
        }, 200);
    });
}

function confirmarAccion(mensaje) {
    const e = window.event;
    if (e && !e.target.form_confirmed) {
        e.preventDefault();
        const form = e.target.tagName.toLowerCase() === 'form' ? e.target : e.target.closest('form');
        if(!form) return true; // fallback
        mostrarDialogoPremium(mensaje, () => {
            form.form_confirmed = true;
            form.submit();
        });
        return false;
    }
    return true;
}
window.confirmarAccion = confirmarAccion;
window.mostrarDialogoPremium = mostrarDialogoPremium;

// ======================================
// 6. AJAX HELPER PARA LARAVEL
// ======================================
async function enviarFormularioAjax(event, onSuccess = null) {
    event.preventDefault();
    const form = event.target;
    
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        });
        
        let data;
        try {
            data = await response.json();
        } catch(e) {
            data = { message: 'Error procesando respuesta del servidor', status: 'error' };
        }

        mostrarNotificacion(data.message || 'Operación procesada', data.status || (response.ok ? 'success' : 'error'));
        
        if (response.ok || data.status === 'success') {
            if (typeof onSuccess === 'function') {
                ocultarLoading();
                await onSuccess(data, form);
            } else {
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            ocultarLoading();
        }
    } catch (err) {
        ocultarLoading();
        mostrarNotificacion('Error de red al procesar la solicitud', 'error');
        console.error(err);
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.dataset.originalHtml) {
                submitBtn.innerHTML = submitBtn.dataset.originalHtml;
            }
        }
    }
    return false;
}
window.enviarFormularioAjax = enviarFormularioAjax;

// ======================================
// 7. LOADING OVERLAY GLOBAL
// ======================================

// Formularios que NO deben mostrar el loading overlay
const LOADING_BLACKLIST_ACTIONS = ['logout', 'search', 'filtro', 'filter', 'descargar', 'imprimir', 'exportar', 'reporte', 'excel', 'pdf'];
const LOADING_BLACKLIST_CLASSES = ['no-loading', 'search-form', 'form-filter'];

function mostrarLoading(mensaje = 'Procesando...', subtitulo = 'Por favor espere, no cierre esta ventana.') {
    const overlay = document.getElementById('sgi-loading-overlay');
    if (!overlay) return;
    const title = overlay.querySelector('.sgi-loading-title');
    const sub   = overlay.querySelector('.sgi-loading-subtitle');
    if (title) title.textContent = mensaje;
    if (sub)   sub.textContent   = subtitulo;
    overlay.classList.add('active');
    overlay.removeAttribute('aria-hidden');
}

function ocultarLoading() {
    const overlay = document.getElementById('sgi-loading-overlay');
    if (!overlay) return;
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
}
window.mostrarLoading = mostrarLoading;
window.ocultarLoading = ocultarLoading;

function esFormularioExcluido(form) {
    // Excluir formularios GET (búsqueda/filtro)
    if ((form.method || '').toLowerCase() === 'get') return true;

    // Excluir formularios que se abren en otra pestaña (descargas normalmente)
    if (form.target === '_blank') return true;

    // Excluir por clase CSS
    for (const cls of LOADING_BLACKLIST_CLASSES) {
        if (form.classList.contains(cls)) return true;
    }

    // Excluir por atributo data
    if (form.dataset.noLoading !== undefined) return true;

    // Excluir formularios dentro de modales o que usan AJAX (tienen su propio feedback ágil)
    if (form.closest('.modal') || form.getAttribute('onsubmit')?.includes('enviarFormularioAjax')) return true;

    // Excluir por action URL (logout, search, etc.)
    const action = (form.action || '').toLowerCase();
    for (const keyword of LOADING_BLACKLIST_ACTIONS) {
        if (action.includes(keyword)) return true;
    }

    return false;
}

function initLoadingEvents() {
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        if (esFormularioExcluido(form)) return;

        // Deshabilitar todos los botones de submit del formulario
        const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        submitBtns.forEach(btn => {
            btn.classList.add('btn-loading');
            btn.disabled = true;
            // Guardar texto original y mostrar indicador en el botón
            if (btn.tagName === 'BUTTON') {
                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            }
        });

        // Mostrar overlay
        mostrarLoading();
    });

    // Seguridad: si el usuario presiona "Atrás" (bfcache),
    // asegurarse de que el loading desaparezca
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            ocultarLoading();
            // Rehabilitar botones en caso de que queden bloqueados
            document.querySelectorAll('.btn-loading').forEach(btn => {
                btn.classList.remove('btn-loading');
                btn.disabled = false;
                if (btn.dataset.originalHtml) {
                    btn.innerHTML = btn.dataset.originalHtml;
                    delete btn.dataset.originalHtml;
                }
            });
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLoadingEvents);
} else {
    initLoadingEvents();
}
