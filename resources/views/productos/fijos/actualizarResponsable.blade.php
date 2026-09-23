<div class="modal fade" id="actualizarResponsableModal" tabindex="-1" aria-labelledby="actualizarResponsableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form method="POST" action="{{ route('productos_fijos.actualizarResponsable', $productoFijo->id) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente amarillo/naranja -->
                <div class="modal-header bg-gradient-warning text-dark py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-edit me-2"></i>Cambio de Responsable
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <!-- Responsable actual -->
                    <div class="mb-4">
                        <label class="form-label text-muted small mb-1">RESPONSABLE ACTUAL</label>
                        <div class="d-flex align-items-center bg-soft-warning rounded-2 p-3 mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-light text-warning rounded-circle">
                                    <i class="fas fa-user fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">
                                    {{ $productoFijo->usuarioResponsable ? $productoFijo->usuarioResponsable->nombreCompleto() : 'Sin responsable asignado' }}
                                </h6>
                                <small class="text-muted">Actual</small>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de nuevo responsable -->
                    <div class="mb-4">
                        <label for="responsableSelect" class="form-label fw-medium">Nuevo Responsable <span class="text-danger">*</span></label>
                        <select class="form-select select2 shadow-sm" id="responsableSelect" name="responsable_id" required>
                            <option value="" disabled selected>Seleccione un responsable...</option>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" @selected(optional($productoFijo->usuarioResponsable)->id === $usuario->id)>
                                {{ $usuario->nombreCompleto() }}
                            </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un responsable</div>
                    </div>

                    <!-- Campo Comentario -->
                    <div class="mb-0">
                        <label for="comentarioResponsable" class="form-label fw-medium">Motivo del cambio</label>
                        <textarea class="form-control shadow-sm" id="comentarioResponsable" name="comentario" rows="3" placeholder="Explique el motivo del cambio de responsable...">{{ old('comentario', $productoFijo->comentario) }}</textarea>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning rounded-2 px-4 shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i>Actualizar Responsable
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-title {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>