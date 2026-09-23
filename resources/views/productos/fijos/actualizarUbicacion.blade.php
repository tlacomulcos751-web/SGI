<div class="modal fade" id="actualizarUbicacionModal" tabindex="-1" aria-labelledby="actualizarUbicacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form method="POST" action="{{ route('productos_fijos.actualizarUbicacion', $productoFijo->id) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente -->
                <div class="modal-header bg-gradient-info text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-map-marker-alt me-2"></i>Actualizar Ubicación del Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <!-- Advertencia importante -->
                    <div class="alert alert-warning border-warning bg-soft-warning rounded-2 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Modificar esta información puede afectar otros registros. Por favor verifique cuidadosamente antes de actualizar.
                    </div>

                    <!-- Ubicación actual -->
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">UBICACIÓN ACTUAL</label>
                        <div class="fs-5 fw-bold text-primary mb-3">
                            <i class="fas fa-location-dot me-2"></i>{{ $producto->ubicacion->nombre }}
                        </div>
                    </div>

                    <!-- Selector de nueva ubicación -->
                    <div class="mb-4">
                        <label for="ubicacionSelect" class="form-label fw-medium">Nueva Ubicación <span class="text-danger">*</span></label>
                        <select class="form-select select2 shadow-sm" id="ubicacionSelect" name="ubicacion_id" required>
                            @foreach($ubicaciones as $ubicacion)
                            @if($ubicacion->puede_insertar_ubicacion)
                            <option value="{{ $ubicacion->ubicacion_id }}">{{ $ubicacion->ubicacion->nombre }}</option>
                            @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una ubicación válida</div>
                    </div>

                    <!-- Campo Comentario -->
                    <div class="mb-0">
                        <label for="comentarioInput" class="form-label fw-medium">Comentario</label>
                        <textarea class="form-control shadow-sm" id="comentarioInput" name="comentario" rows="3" placeholder="Ingrese un comentario opcional...">{{ old('comentario', $productoFijo->comentario) }}</textarea>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-info rounded-2 px-4 shadow-sm">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #1abc9c 100%);
    }

    .bg-light.bg-gradient {
        background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .rounded-2 {
        border-radius: 0.5rem !important;
    }

    .rounded-3 {
        border-radius: 1rem !important;
    }
</style>