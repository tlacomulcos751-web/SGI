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
                        <strong>Advertencia:</strong> Modificar esta información cambiará la ubicación del activo fijo en los inventarios y reportes.
                    </div>

                    <!-- Ubicación y Empresa actual -->
                    <div class="mb-3 p-3 bg-light rounded-2 border">
                        <label class="form-label text-muted small mb-1 fw-bold text-uppercase">Ubicación y Empresa Actual</label>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-white text-dark border px-2 py-1 shadow-xs">
                                <i class="fas fa-building text-info me-1"></i>
                                {{ $producto->ubicacion?->empresa?->nombre ?? 'Sin empresa asignada' }}
                            </span>
                            <span class="fs-6 fw-bold text-primary">
                                <i class="fas fa-location-dot me-1 text-danger"></i>
                                {{ $producto->ubicacion?->nombre ?? 'Sin ubicación asignada' }}
                            </span>
                        </div>
                    </div>

                    <!-- Filtro por Empresa Interna -->
                    <div class="mb-3">
                        <label for="act_ubicacion_empresa_filter" class="form-label fw-medium">Filtrar por Empresa Interna</label>
                        <select class="form-select shadow-sm" id="act_ubicacion_empresa_filter" onchange="filtrarModalUbicacion(this.value)">
                            <option value="">-- Todas las empresas --</option>
                            @foreach($empresasInternas as $empresa)
                            <option value="{{ $empresa->id }}" @selected(($producto->ubicacion?->empresa_id ?? null) == $empresa->id)>
                                {{ $empresa->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selector de nueva ubicación -->
                    <div class="mb-4">
                        <label for="ubicacionSelect" class="form-label fw-medium">Nueva Ubicación <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm" id="ubicacionSelect" name="ubicacion_id" required>
                            <option value="" disabled>Seleccione una ubicación...</option>
                            @foreach($ubicaciones as $ubicacion)
                            @if($ubicacion->puede_insertar_ubicacion || $ubicacion->puede_leer_ubicacion)
                            <option value="{{ $ubicacion->ubicacion_id }}" 
                                    data-empresa-id="{{ $ubicacion->ubicacion?->empresa_id }}"
                                    @selected($producto->ubicacion_id == $ubicacion->ubicacion_id)>
                                {{ $ubicacion->ubicacion?->nombre }} ({{ $ubicacion->ubicacion?->empresa?->nombre ?? 'Sin empresa' }})
                            </option>
                            @endif
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una ubicación válida</div>
                    </div>

                    <!-- Campo Comentario -->
                    <div class="mb-0">
                        <label for="comentarioInput" class="form-label fw-medium">Comentario del traslado</label>
                        <textarea class="form-control shadow-sm" id="comentarioInput" name="comentario" rows="3" placeholder="Motivo o detalles del cambio de ubicación...">{{ old('comentario', $productoFijo->comentario) }}</textarea>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-info rounded-2 px-4 shadow-sm text-white">
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

<script>
    function filtrarModalUbicacion(empresaId) {
        const select = document.getElementById('ubicacionSelect');
        if (!select) return;
        const options = select.querySelectorAll('option[data-empresa-id]');
        let selectedStillValid = false;

        options.forEach(opt => {
            const empId = opt.getAttribute('data-empresa-id');
            if (!empresaId || empId == empresaId) {
                opt.style.display = '';
                opt.disabled = false;
                if (opt.selected) selectedStillValid = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                if (opt.selected) opt.selected = false;
            }
        });

        if (!selectedStillValid) {
            for (let opt of options) {
                if (!opt.disabled) {
                    opt.selected = true;
                    break;
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalUbicacion = document.getElementById('actualizarUbicacionModal');
        if (modalUbicacion) {
            modalUbicacion.addEventListener('show.bs.modal', function() {
                const empFilter = document.getElementById('act_ubicacion_empresa_filter');
                if (empFilter && empFilter.value) {
                    filtrarModalUbicacion(empFilter.value);
                }
            });
        }
    });
</script>