@extends('layouts.navigation')
@section('title', $vehiculo?->marca . ' ' . $vehiculo?->modelo ?? 'Vehículo no. ' . $vehiculo->id)

@section('content')
<div class="rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg,rgb(203, 85, 55) 0%,rgb(220, 147, 73) 100%);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white gap-3">
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="display-5 fw-bold mb-2">
                    {{ $vehiculo?->marca . ' ' . $vehiculo?->modelo ?? 'Vehículo no. ' . $vehiculo->id }}
                </h1>
                @if($vehiculo->eliminado)
                    <span class="badge bg-dark bg-opacity-50 border border-white text-white rounded-pill px-3 py-1 fs-6 mb-2">
                        <i class="fas fa-ban me-1 text-warning"></i> Inactivo (Dado de baja)
                    </span>
                @else
                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-1 fs-6 mb-2">
                        <i class="fas fa-check me-1"></i> Activo
                    </span>
                @endif
            </div>
            <p class="opacity-75 mb-0">Detalles completos del vehículo</p>
        </div>
            <a href="{{ route('vehiculos.generarVale', $vehiculo->id) }}" 
               class="btn btn-light btn-sm rounded-pill px-3 text-dark fw-semibold shadow-sm d-flex align-items-center"
               title="Descargar Vale de Salida en formato Word (.docx)">
                <i class="fas fa-file-word text-primary me-2 fs-6"></i>
                <span>Generar Vale de Salida</span>
            </a>
            <a href="{{ route('vehiculos.generarMantenimiento', $vehiculo->id) }}" 
               class="btn btn-light btn-sm rounded-pill px-3 text-dark fw-semibold shadow-sm d-flex align-items-center"
               title="Descargar Orden de Mantenimiento en formato Word (.docx)">
                <i class="fas fa-wrench text-warning me-2 fs-6"></i>
                <span>Orden de Mantenimiento</span>
            </a>
            @if(tienePermiso('vehiculo - modificar') && !$vehiculo->eliminado)
            <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalAgregrarDocumentacion">
                <i class="bi bi-pencil"></i> Agregar documentación
            </button>
            <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#modalAgregarMantenimiento">
                <i class="fas fa-tools me-2"></i> Agregar Mantenimiento
            </button>
            @endif

            @if(esSuperAdmin())
                @if(!$vehiculo->eliminado)
                <button type="button"
                    class="btn btn-danger btn-eliminar rounded-pill px-3 shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalConfirmarEliminacion">
                    <i class="fas fa-ban me-1"></i> Dar de Baja Vehículo
                </button>
                @else
                <form action="{{ route('vehiculos.reactivar', $vehiculo->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success rounded-pill px-3 shadow-sm text-white" onclick="return confirm('¿Deseas reactivar este vehículo a estado activo?');">
                        <i class="fas fa-check-circle me-1"></i> Reactivar Vehículo
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>
</div>
<!-- Main Card -->
<div class="card border-0 shadow-lg overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold text-primary">
                <i class="fas fa-info-circle me-2"></i>Información General
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">ID: {{ $vehiculo->id }}</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="row g-0">
            <!-- Left Column -->
            <div class="col-lg-6 p-4 border-end">
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Datos básicos</h6>
                    <div class="row g-3">
                        @php
                        $campos = [
                        'Tipo' => $vehiculo->tipo,
                        'Marca' => $vehiculo->marca,
                        'Modelo' => $vehiculo->modelo,
                        'Versión' => $vehiculo->version,
                        'Año' => $vehiculo->año,
                        'Placa' => $vehiculo->placas,
                        'Color' => $vehiculo->color,
                        'Carrocería' => $vehiculo->carroceria,
                        'Combustible' => $vehiculo->combustible,
                        'Transmisión' => $vehiculo->transmision,
                        'NIV' => $vehiculo->niv,
                        ];
                        @endphp

                        @foreach ($campos as $label => $valor)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="mb-1"><strong>{{ $label }}:</strong> {{ $valor }}</p>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="mb-1">
                                    <strong><i class="fas fa-map-marker-alt text-danger me-2"></i>Ubicación</strong>
                                    <a href="{{ route('empresa.ubicaciones.ver',$vehiculo->ubicacion->id) }}"
                                        class="text-decoration-none align-items-center"
                                        title="Ver ubicación: {{ $vehiculo->ubicacion->nombre }}">
                                        {{ $vehiculo->ubicacion->nombre }}
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="fw-medium">
                                    <strong><i class="fas fa-user-circle text-primary me-2"></i>Responsable</strong>
                                    @if(esSuperAdmin() && $vehiculo->usuarioResponsable)
                                    <a href="{{ route('usuarios.ver', $vehiculo->usuarioResponsable->id) }}"
                                        class="text-decoration-none d-flex align-items-center"
                                        title="Ver usuario: {{ $vehiculo->usuarioResponsable->nombreCompleto() }}">
                                        {{ $vehiculo->usuarioResponsable->nombreCompleto() }}
                                    </a>
                                    @else
                                    <span class="d-flex align-items-center text-secondary">
                                        {{ $vehiculo->usuarioResponsable ? $vehiculo->usuarioResponsable->nombreCompleto() : 'N/A' }}
                                    </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column -->
            <div class="col-lg-6 p-4">
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">Datos tecnicos</h6>
                    <div class="row g-3">
                        @php
                        $campos = [
                        'Cilindraje' => $vehiculo->especificaciones->cilindraje,
                        'Potencia' => $vehiculo->especificaciones->potencia,
                        'Torque' => $vehiculo->especificaciones->torque,
                        'Ejes' => $vehiculo->especificaciones->ejes,
                        'Ruedas' => $vehiculo->especificaciones->ruedas,
                        'Capacidad de carga' => $vehiculo->especificaciones->capacidad_carga,
                        'Largo' => $vehiculo->especificaciones->largo,
                        'Ancho' => $vehiculo->especificaciones->ancho,
                        'Alto' => $vehiculo->especificaciones->alto,
                        ];
                        @endphp

                        @foreach ($campos as $label => $valor)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="mb-1"><strong>{{ $label }}:</strong> {{ $valor }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mantenimientos Card -->
@include('components.vehiculo.mantenimiento', ['vehiculo' => $vehiculo])
<!-- Documentación Card -->
@include('components.vehiculo.documentacion', ['vehiculo' => $vehiculo])


<div id="movimientos-wrapper">
    @include('components.productos.movimientos', [
    'movimientos' => $movimientos,
    'enlaceUsuario' => true,
    'class' => '',
    'badgeClass' => 'bg-primary-subtle text-primary fw-medium'
    ])
</div>
<style>
    .documentation-list {
        border-radius: 0.5rem;
    }

    .documentation-item {
        background-color: var(--bg-hover);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .documentation-item:hover {
        background-color: var(--bg-card);
        box-shadow: var(--shadow-sm);
        transform: translateY(-1px);
    }

    .document-icon {
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--bg-card);
        border-radius: 50%;
        border: 1px solid var(--border-color);
    }
</style>
@endsection

@include('productos.vehiculos.modales.modalAgregrarDocumentacion')
@include('productos.vehiculos.modales.modalAgregrarMantenimiento')
@include('productos.vehiculos.modales.modalEliminar')