<div class="modal fade" id="modalActualizarUsuario" tabindex="-1" aria-labelledby="modalActualizarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formActualizarUsuario" method="POST" action="{{ route('usuarios.actualizar', $usuario->id) }}" class="needs-validation" novalidate>
            @method('PUT')
            @csrf
            <input type="hidden" name="id" id="usuarioId" value="{{ $usuario->id }}">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente azul -->
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user-edit me-2"></i>Actualizar Información de Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body p-4">
                    <!-- Advertencia importante -->
                    <div class="alert alert-warning border-warning bg-soft-warning rounded-2 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Los cambios realizados aquí afectarán la información personal, permisos y acceso del usuario.
                    </div>

                    <div class="row g-3">
                        <!-- Nombre(s) -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-medium">Nombre(s) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm" id="nombre" name="nombre"
                                    value="{{ old('nombre', $usuario->nombre) }}" placeholder="Ej. Juan Carlos" required>
                                <div class="invalid-feedback">Por favor ingrese el nombre del usuario</div>
                            </div>
                        </div>

                        <!-- Apellido Paterno -->
                        <div class="col-md-6">
                            <label for="apellido" class="form-label fw-medium">Apellido Paterno <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm" id="apellido" name="apellido"
                                    value="{{ old('apellido', $usuario->apellido) }}" placeholder="Ej. Pérez" required>
                                <div class="invalid-feedback">Por favor ingrese el apellido paterno</div>
                            </div>
                        </div>

                        <!-- Apellido Materno -->
                        <div class="col-md-6">
                            <label for="apellido_m" class="form-label fw-medium">Apellido Materno</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-tag text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm" id="apellido_m" name="apellido_m"
                                    value="{{ old('apellido_m', $usuario->apellido_m) }}" placeholder="Ej. López">
                            </div>
                        </div>

                        <!-- Nombre de Usuario (Login) -->
                        <div class="col-md-6">
                            <label for="nombre_user" class="form-label fw-medium">Nombre de Usuario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-id-badge text-primary"></i></span>
                                <input type="text" class="form-control shadow-sm" id="nombre_user" name="nombre_user"
                                    value="{{ old('nombre_user', $usuario->nombre_user) }}" placeholder="Ej. jperez123" required>
                                <div class="invalid-feedback">Por favor ingrese el nombre de usuario</div>
                            </div>
                            <small class="text-muted">Identificador único para iniciar sesión</small>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="col-md-6">
                            <label for="gmail" class="form-label fw-medium">Correo Electrónico <span class="text-muted fw-normal">(Opcional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control shadow-sm" id="gmail" name="gmail"
                                    value="{{ old('gmail', $usuario->gmail) }}" placeholder="ejemplo@gmail.com">
                                <div class="invalid-feedback">Por favor ingrese un correo válido</div>
                            </div>
                            <small class="text-muted">Opcional. Dejar en blanco si el usuario solo usará su usuario.</small>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <label for="estado" class="form-label fw-medium">Estado <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-check text-primary"></i></span>
                                <select class="form-select shadow-sm" id="estado" name="estado" required>
                                    <option value="activo" {{ old('estado', $usuario->estado) == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('estado', $usuario->estado) == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un estado</div>
                            </div>
                        </div>

                        <!-- Rol -->
                        <div class="col-md-6">
                            <label for="rol" class="form-label fw-medium">Rol de Usuario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user-shield text-primary"></i></span>
                                <select class="form-select shadow-sm" id="rol" name="rol" required>
                                    @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol', $usuario->id_rol) == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un rol</div>
                            </div>
                        </div>

                        <!-- Nueva Contraseña -->
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-medium">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                <input type="password" class="form-control shadow-sm" id="password" name="password"
                                    minlength="6" placeholder="Mínimo 6 caracteres">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Dejar en blanco para mantener la actual</small>
                        </div>
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
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

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
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

    .toggle-password {
        border-radius: 0 0.5rem 0.5rem 0 !important;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>

<!-- Script para mostrar/ocultar contraseña -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('#modalActualizarUsuario .toggle-password');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i');
                if (passwordInput && icon) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                }
            });
        }
    });
</script>
