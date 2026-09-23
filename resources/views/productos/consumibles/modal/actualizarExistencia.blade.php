<div class="modal fade" id="ajustarExistenciaModal" tabindex="-1" aria-labelledby="ajustarExistenciaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('consumibles.ajustar', $productoConsumible->id) }}" class="needs-validation">
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente azul -->
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-calculator me-2"></i>Ajustar Existencia
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <!-- Advertencia importante -->
                    <div class="alert alert-warning border-warning bg-soft-warning rounded-2 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Los ajustes de inventario afectarán directamente la disponibilidad del producto.
                    </div>

                    <div class="row g-3">
                        <!-- Cantidad -->
                        <div class="col-md-6">
                            <label for="cantidad" class="form-label fw-medium">Cantidad <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-hashtag"></i></span>
                                <input type="number" name="cantidad" id="cantidad" class="form-control shadow-sm" required min="1" placeholder="Ej. 10">
                            </div>
                            <div class="invalid-feedback">Por favor ingrese una cantidad válida</div>
                        </div>

                        <!-- Tipo de ajuste -->
                        <div class="col-md-6">
                            <label for="tipo" class="form-label fw-medium">Tipo de ajuste <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-select shadow-sm" required>
                                <option value="disminuir" class="text-danger">Disminuir Existencia</option>
                                <option value="aumentar" class="text-success">Aumentar Existencia</option>
                            </select>
                        </div>

                        <!-- Comentario -->
                        <div class="col-12">
                            <label for="comentario" class="form-label fw-medium">Motivo del ajuste <span class="text-danger">*</span></label>
                            <textarea name="comentario" id="comentario" class="form-control shadow-sm" rows="3" required placeholder="Ej. Ajuste por inventario físico, entrada de mercancía, etc."></textarea>
                            <div class="invalid-feedback">Por favor proporcione un motivo para el ajuste</div>
                        </div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm">
                        <i class="fas fa-check-circle me-2"></i>Aplicar Cambio
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .rounded-2 {
        border-radius: 0.5rem !important;
    }

    .rounded-3 {
        border-radius: 1rem !important;
    }

    /* Estilo para las opciones del select */
    .text-danger {
        color: #dc3545 !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
    }

    .shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }
</style>