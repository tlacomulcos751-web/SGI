<!-- Modal: Añadir Mantenimiento -->
<div class="modal fade" id="modalAgregarMantenimiento" tabindex="-1" aria-labelledby="modalAgregarMantenimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('vehiculos.mantenimiento.insertar', $vehiculo->id) }}" method="POST">
            @csrf
            <input type="hidden" name="vehiculo_id" value="{{ $vehiculo->id }}">
            <div class="modal-content border-0" style="box-shadow: 0 5px 15px rgba(51, 41, 90, 0.3);">
                <div class="modal-header" style="background-color: #322a5a; border-bottom: 2px solid #4c61a9;">
                    <h5 class="modal-title" id="modalAgregarMantenimientoLabel" style="color: #e0b063;">
                        <i class="fas fa-tools me-2"></i> Registrar Mantenimiento
                    </h5>
                    <button type="button" class="btn-close" style="filter: invert(85%) sepia(30%) saturate(415%) hue-rotate(338deg) brightness(102%) contrast(88%);" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label fw-semibold" style="color: #242423;">Fecha del mantenimiento</label>
                            <input type="date" name="fecha" id="fecha" class="form-control border-2" style="border-color: #6277b7;" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kilometraje" class="form-label fw-semibold" style="color: #242423;">Kilometraje</label>
                            <input type="number" name="kilometraje" id="kilometraje" class="form-control border-2" style="border-color: #6277b7;" required min="0" max="9999999" step="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_servicio" class="form-label fw-semibold" style="color: #242423;">Tipo de servicio</label>
                        <input type="text" name="tipo_servicio" id="tipo_servicio" class="form-control border-2" style="border-color: #6277b7;" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold" style="color: #242423;">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control border-2" style="border-color: #6277b7;" required maxlength="1000"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="taller" class="form-label fw-semibold" style="color: #242423;">Taller</label>
                        <input type="text" name="taller" id="taller" class="form-control border-2" style="border-color: #6277b7;" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="comentario" class="form-label fw-semibold" style="color: #242423;">Comentario (opcional)</label>
                        <textarea name="comentario" id="comentario" rows="2" class="form-control border-2" style="border-color: #6277b7;" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f1f3f8; border-top: 2px solid #dc934a;">
                    <button type="button" class="btn" style="background-color: #6277b7; color: white;" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn" style="background-color: #cc5536; color: white;">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    #modalAgregarMantenimiento .form-control:focus {
        border-color: #cc5536;
        box-shadow: 0 0 0 0.25rem rgba(204, 85, 54, 0.25);
    }

    #modalAgregarMantenimiento .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("modalAgregarMantenimiento");
        modal.addEventListener("shown.bs.modal", function() {
            document.body.style.overflow = "";
            // Autofocus al primer campo cuando se abre el modal
            document.getElementById("fecha").focus();
        });
    });
</script>