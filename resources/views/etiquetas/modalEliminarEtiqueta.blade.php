{{-- Modal para eliminar etiqueta --}}
<div class="modal fade" id="modalEliminarEtiqueta" tabindex="-1" aria-labelledby="modalEliminarEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEliminarEtiqueta" method="POST" action="{{ route('etiquetas.desactivar') }}" onsubmit="return eliminarEtiquetaAjax(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="idEtiqueta_eliminar" name="id" value="">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="modalEliminarEtiquetaLabel">
                        <i class="fas fa-trash-alt me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4 text-center">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p class="fs-5 mb-0">¿Estás seguro de que deseas eliminar la etiqueta <strong id="nombreEtiqueta_eliminar" class="text-danger"></strong>?</p>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger" id="btnEliminarEtiqueta">
                        <i class="fas fa-trash-alt me-2"></i> Eliminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function eliminarEtiquetaAjax(event) {
    return enviarFormularioAjax(event, function(data, form) {
        const modalEl = document.getElementById('modalEliminarEtiqueta');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalObj = bootstrap.Modal.getInstance(modalEl);
            if (modalObj) modalObj.hide();
        }
        setTimeout(() => window.location.reload(), 600);
    });
}
</script>