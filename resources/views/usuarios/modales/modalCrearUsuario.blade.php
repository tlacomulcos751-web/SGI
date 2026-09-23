<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalInsertarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('usuarios.insertar') }}" method="POST" id="formInsertarUsuario" class="needs-validation" novalidate>
            @csrf
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente azul -->
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-plus me-2"></i>Registrar Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body p-4">
                    <!-- Mensaje informativo -->
                    <div class="alert alert-info border-info bg-soft-info rounded-2 mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Complete todos los campos obligatorios (<span class="text-danger">*</span>) para registrar un nuevo usuario.
                    </div>

                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-medium">Nombre(s) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm rounded-end" name="nombre"
                                    placeholder="Ej. Juan Carlos" required>
                                <div class="invalid-feedback">Por favor ingrese el nombre del usuario</div>
                            </div>
                        </div>

                        <!-- Apellido Paterno -->
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-medium">Apellido Paterno <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm rounded-end" name="apellido"
                                    placeholder="Ej. Pérez" required>
                                <div class="invalid-feedback">Por favor ingrese el apellido paterno</div>
                            </div>
                        </div>

                        <!-- Apellido Materno -->
                        <div class="col-md-6">
                            <label for="apellido_m" class="form-label fw-medium">Apellido Materno</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm rounded-end" name="apellido_m"
                                    placeholder="Ej. López">
                            </div>
                        </div>

                        <!-- Correo -->
                        <div class="col-md-6">
                            <label for="gmail" class="form-label fw-medium">Correo Electrónico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control shadow-sm rounded-end" name="gmail"
                                    placeholder="ejemplo@gmail.com" required>
                                <div class="invalid-feedback">Por favor ingrese un correo válido</div>
                            </div>
                            <small class="text-muted">Debe ser una cuenta de Gmail válida</small>
                        </div>

                        <!-- Rol -->
                        <div class="col-md-12">
                            <label for="id_rol" class="form-label fw-medium">Rol de Usuario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-shield text-primary"></i></span>
                                <select name="id_rol" class="form-select shadow-sm" required>
                                    <option value="" selected disabled>-- Seleccione un rol --</option>
                                    @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un rol</div>
                            </div>
                            <small class="text-muted">Asigne los permisos adecuados según las responsabilidades</small>
                        </div>
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm">
                        <i class="fas fa-user-plus me-2"></i>Registrar Usuario
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    }

    .bg-soft-info {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .rounded-2 {
        border-radius: 0.5rem !important;
    }

    .rounded-3 {
        border-radius: 1rem !important;
    }

    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
    }

    .shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .175) !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .input-group-text {
        min-width: 45px;
        justify-content: center;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>