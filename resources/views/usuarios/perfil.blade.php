@extends('layouts.navigation')

@section('title', 'Tu perfil - ' . $usuario->nombre)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Tarjeta de perfil -->
            <div class="card border-0 shadow-lg mb-5">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-person-circle me-2"></i>Mi Perfil
                        </h4>
                        <span class="badge bg-light text-primary fs-6">{{ $rol->nombre }}</span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <!-- Información del usuario -->
                        <div class="col-md-5 border-end pe-md-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="symbol symbol-100px symbol-circle bg-light-primary me-4">
                                    <span class="symbol-label fs-2 text-primary">{{ substr($usuario->nombre, 0, 1) }}{{ substr($usuario->apellido, 0, 1) }}</span>
                                </div>
                                <div>
                                    <h3 class="fw-bold mb-1">{{ $usuario->nombre }} {{ $usuario->apellido }}</h3>
                                    <span class="text-muted">{{ $usuario->nombre_user }}</span>
                                </div>
                            </div>

                            <div class="user-details">
                                <div class="d-flex align-items-center py-2">
                                    <i class="bi bi-person-lines-fill text-primary me-3 fs-5"></i>
                                    <div>
                                        <small class="text-muted">Nombre completo</small>
                                        <div class="fw-medium">{{ $usuario->nombre }} {{ $usuario->apellido }} {{ $usuario->apellido_m }}</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center py-2">
                                    <i class="bi bi-person-vcard text-primary me-3 fs-5"></i>
                                    <div>
                                        <small class="text-muted">Usuario</small>
                                        <div class="fw-medium">{{ $usuario->nombre_user }}</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center py-2">
                                    <i class="bi bi-shield-check text-primary me-3 fs-5"></i>
                                    <div>
                                        <small class="text-muted">Rol</small>
                                        <div class="fw-medium">{{ $rol->nombre }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de actualización -->
                        <div class="col-md-7 ps-md-4">
                            <h5 class="fw-bold mb-4 text-primary">
                                <i class="bi bi-pencil-square me-2"></i>Actualizar información
                            </h5>

                            <form action="{{ route('usuarios.actualizarPerfil') }}" method="POST" class="needs-validation" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="gmail" class="form-label fw-medium">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope-at text-primary"></i>
                                        </span>
                                        <input type="email" name="gmail" id="gmail"
                                            class="form-control form-control-lg"
                                            value="{{ $usuario->gmail }}" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor ingresa un correo electrónico válido
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label fw-medium">Cambiar contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock text-primary"></i>
                                        </span>
                                        <input type="password" name="password" id="password"
                                            class="form-control form-control-lg"
                                            placeholder="Mínimo 6 caracteres"
                                            minlength="6" maxlength="100">
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                                </div>

                                <div class="d-grid pt-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de equipos asignados -->
            <x-usuario.productos-lista
                :productos="$misObjetos"
                :usuario="$usuario"
                titulo="Equipos asignados"
                :rutaDetalle="fn($p) => route('productos_fijos.viewFijos', $p->id)"
                class="shadow-lg" />

            <x-usuario.usuario-vehiculos
                :vehiculos="$vehiculosACargo"
                :usuario="$usuario"
                titulo="Vehículos a cargo tuyos"
                :rutaDetalle="fn($vehiculo) => route('vehiculos.ver', $vehiculo->id)" />

            <!-- Historial de movimientos -->
            <div id="movimientos-wrapper" class="card-body p-0">
                @include('components.productos.movimientos', [
                'movimientos' => $mov,
                'enlaceUsuario' => true,
                'class' => 'table-hover',
                'badgeClass' => 'bg-primary-subtle text-primary fw-medium'
                ])
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const inputGroup = this.closest('.input-group');
            const input = inputGroup.querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Validación de formulario
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
    }

    .symbol {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .symbol.symbol-circle {
        border-radius: 50%;
    }

    .symbol.symbol-100px {
        width: 100px;
        height: 100px;
    }

    .user-details div {
        transition: all 0.3s ease;
    }

    .user-details div:hover {
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .card {
        border-radius: 12px;
        overflow: hidden;
    }
</style>

@endsection