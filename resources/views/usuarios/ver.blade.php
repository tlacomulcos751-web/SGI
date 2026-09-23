@extends('layouts.navigation')
@section('title', 'Usuario '. $usuario->nombreCompleto())
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header -->
    <div class="rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg,rgb(255, 65, 223) 0%,rgb(255, 43, 177) 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white gap-3">
            <div class="flex-grow-1">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-box-open me-2"></i>{{ $usuario->nombreCompleto() }}
                </h1>
                <p class="opacity-75 mb-0">Detalles del usuario</p>
            </div>
            <div class="d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">
                @if(tienePermiso('modificarUsuarios'))
                <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalActualizarUsuario">
                    <i class="bi bi-pencil"></i> Editar
                </button>
                @endif
                @if(tienePermiso('desactivarUsuarios'))
                <button type="button"
                    class="btn btn-sm btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEliminarUsuario">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Información General
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">ID: {{ $usuario->id }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Left Column -->
                <div class="col-lg-6 p-4 border-end">
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Nombre</label>
                                    <p class="fw-medium">{{ $usuario->nombre }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Apellido</label>
                                    <p class="fw-medium">{{ $usuario->apellido }}</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Apellido materno</label>
                                    <p class="text-medium">{{ $usuario->apellido_m }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-6 p-4">
                    <div class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Correo</label>
                                    <p class="fw-medium">
                                        {{ $usuario->gmail }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Estado</label>
                                    <span class="badge 
                                        @if($usuario->estado == 'activo') bg-success @else bg-secondary @endif">
                                        {{ $usuario->estado }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-usuario.productos-lista
        :productos="$productosACargo"
        :usuario="$usuario"
        titulo="Equipos asignados"
        :rutaDetalle="fn($p) => route('productos_fijos.viewFijos', $p->id)" />


    <div id="movimientos-wrapper">
        @include('components.productos.movimientos', [
        'movimientos' => $movimientos,
        'enlaceUsuario' => true,
        'class' => '',
        'badgeClass' => 'bg-primary-subtle text-primary fw-medium'
        ])
    </div>
</div>
@endsection

@include('usuarios.modales.update-user-modal')
@include('usuarios.modales.delete-user-modal')