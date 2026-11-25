{{-- Modal para insertar etiqueta --}}
<div class="modal fade" id="modalInsertarEtiqueta" tabindex="-1" aria-labelledby="modalInsertarEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formInsertarEtiqueta" method="POST" action="{{ route('etiquetas.insertar') }}" onsubmit="return guardarEtiquetaAjax(event)">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalInsertarEtiquetaLabel">
                        <i class="fas fa-tag me-2"></i>Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div id="errorModalInsertarEtiqueta" class="alert alert-danger d-none py-2 px-3 small mb-3"></div>

                    <div class="row g-3">
                        {{-- NOMBRE --}}
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nombreEtiqueta" name="nombre" placeholder=" " required maxlength="100">
                                <label for="nombreEtiqueta" class="text-muted">
                                    <i class="fas fa-tag me-1"></i> Nombre de la categoría
                                </label>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Ingrese un nombre único para la categoría (máx. 100 caracteres)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEtiqueta">
                        <i class="fas fa-save me-2"></i> Guardar Categoría
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function guardarEtiquetaAjax(event) {
        event.preventDefault();
        const form = event.target || document.getElementById('formInsertarEtiqueta');
        const submitBtn = document.getElementById('btnGuardarEtiqueta');
        const errBox = document.getElementById('errorModalInsertarEtiqueta');

        if (errBox) {
            errBox.classList.add('d-none');
            errBox.textContent = '';
        }

        const inputNombre = document.getElementById('nombreEtiqueta');
        if (!inputNombre || !inputNombre.value.trim()) {
            if (errBox) {
                errBox.textContent = 'Por favor ingrese el nombre de la categoría.';
                errBox.classList.remove('d-none');
            }
            if (inputNombre) inputNombre.focus();
            return false;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Guardando...';
        }

        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            }
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                let errorMsg = data.message || 'Error al guardar la categoría.';
                if (data.errors && data.errors.nombre) {
                    errorMsg = data.errors.nombre[0];
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion(data.message || 'Categoría creada exitosamente.', 'success');
            }
            // Cerrar el modal
            const modalEl = document.getElementById('modalInsertarEtiqueta');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalObj = bootstrap.Modal.getInstance(modalEl);
                if (modalObj) modalObj.hide();
            }
            form.reset();

            // Si estamos en la pantalla principal de etiquetas, recargar para ver la nueva fila
            if (window.location.pathname.includes('/etiquetas')) {
                setTimeout(() => window.location.reload(), 600);
                return;
            }

            // Si estamos en un formulario de creación o edición de productos, agregar dinámicamente el checkbox
            if (data && data.etiqueta && data.etiqueta.id) {
                const tagId = data.etiqueta.id;
                const tagName = data.etiqueta.nombre;

                // Contenedores de checkboxes en modales de producto (fijos, consumibles, compra-venta)
                document.querySelectorAll('.card-body .d-flex.flex-wrap.gap-2').forEach(container => {
                    const newCheck = document.createElement('div');
                    newCheck.className = 'form-check form-check-inline animate__animated animate__fadeIn';
                    newCheck.innerHTML = `
                        <input class="form-check-input" type="checkbox" name="etiquetasInsertar[]" value="${tagId}" id="form-etiqueta-${tagId}" checked>
                        <label class="form-check-label badge bg-primary bg-opacity-10 text-primary p-2" for="form-etiqueta-${tagId}">
                            <i class="fas fa-tag me-1"></i> ${tagName}
                        </label>
                    `;
                    container.appendChild(newCheck);
                });

                // Select de etiquetas en vista de producto individual
                const selectClasificacion = document.getElementById('clasificacion_id');
                if (selectClasificacion) {
                    const opt = new Option(tagName, tagId, true, true);
                    selectClasificacion.add(opt);
                }
            }
        })
        .catch(err => {
            if (errBox) {
                errBox.textContent = err.message;
                errBox.classList.remove('d-none');
            }
            if (typeof mostrarNotificacion === 'function') {
                mostrarNotificacion(err.message, 'error');
            }
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i> Guardar Categoría';
            }
        });

        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const nombreInput = document.getElementById('nombreEtiqueta');
        if (nombreInput) {
            nombreInput.addEventListener('input', function() {
                if (this.value.length > 100) {
                    this.value = this.value.substring(0, 100);
                }
            });
        }
    });
</script>