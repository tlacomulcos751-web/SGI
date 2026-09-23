{{-- Modal para editar etiqueta --}}
<div class="modal fade" id="modalEditarEtiqueta" tabindex="-1" aria-labelledby="modalEditarEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formEditarEtiqueta" method="POST" action="" onsubmit="return enviarFormularioAjax(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="idEtiqueta_editar" name="id">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalEditarEtiquetaLabel">
                        <i class="fas fa-edit me-2"></i>Editar Etiqueta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- NOMBRE --}}
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nombreEtiqueta_editar" name="nombre" placeholder=" " required maxlength="100">
                                <label for="nombreEtiqueta_editar" class="text-muted">
                                    <i class="fas fa-tag me-1"></i> Nombre de la etiqueta
                                </label>
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Máximo 100 caracteres
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnEditarEtiqueta">
                        <i class="fas fa-save me-2"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEditar = document.getElementById('modalEditarEtiqueta');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (button && button.classList.contains('btn-editar-etiqueta')) {
                const id = button.getAttribute('data-id');
                const nombre = button.getAttribute('data-nombre');
                const inputId = document.getElementById('idEtiqueta_editar');
                const inputNombre = document.getElementById('nombreEtiqueta_editar');
                if (inputId && id) inputId.value = id;
                if (inputNombre && nombre) inputNombre.value = nombre;
                // Actualizar el action del form con la ruta correcta incluyendo el ID
                const form = document.getElementById('formEditarEtiqueta');
                if (form && id) {
                    form.action = `/etiquetas/${id}`;
                }
            }
        });
    }
});
</script>