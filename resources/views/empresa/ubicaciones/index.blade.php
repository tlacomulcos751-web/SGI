@extends('layouts.navigation')
@php
use App\Models\VistaProductosUnificada;
@endphp
@section('title', 'Ubicaciones')

@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header with gradient and glass effect -->
    <div class="glass-nav rounded-4 p-4 mb-5 shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 fw-bold text-gradient mb-2">
                    <i class="fas fa-map-marker-alt me-2"></i> Ubicaciones
                </h1>
                <p class="lead text-muted mb-0">Administra y visualiza todas las ubicaciones disponibles en el sistema</p>
            </div>
            @if (tienePermiso('agregarUbicaciones'))
            <button type="button" class="btn btn-primary btn-lg btn-float shadow" data-bs-toggle="modal" data-bs-target="#modalInsertarUbicacion">
                <i class="fas fa-plus-circle me-2"></i> Nueva Ubicación
            </button>
            @endif
        </div>
    </div>

    <!-- Barra de búsqueda con JS -->
    <div class="mb-4">
        <input type="text" id="busqueda-ubicacion" class="form-control" placeholder="Buscar ubicación...">
    </div>

    <!-- Ubicaciones Table with enhanced design -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase fw-500">#</th>
                            <th class="py-3 text-uppercase fw-500">Clave</th>
                            <th class="py-3 text-uppercase fw-500">Nombre</th>
                            <th class="py-3 text-uppercase fw-500">Estado</th>
                            <th class="py-3 text-uppercase fw-500">Empresa</th>
                            <th class="py-3 text-uppercase fw-500">No. de productos</th>
                            <th class="pe-4 py-3 text-end text-uppercase fw-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ubicaciones-tbody">
                        @foreach ($ubicaciones as $index => $ubicacion)
                        <tr class="hover-scale">
                            <td class="ps-4 fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    {{ $ubicacion->clave }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ $ubicacion->nombre }}</td>
                            <td>
                                <span class="badge bg-{{ $ubicacion->estado == 'Activo' ? 'success' : 'secondary' }}">
                                    {{ $ubicacion->estado }}
                                </span>
                            </td>
                            <td>
                                @if($ubicacion->empresa)
                                <span class="d-flex align-items-center">
                                    <i class="fas fa-building me-2 text-muted"></i>
                                    {{ $ubicacion->empresa->nombre }}
                                </span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-cubes me-1"></i> {{ VistaProductosUnificada::contarPorUbicacion($ubicacion->id) }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('empresa/ubicaciones/' . $ubicacion->id) }}" class="btn btn-outline-primary btn-float rounded-start" data-bs-toggle="tooltip" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if (tienePermiso('modificarUbicaciones') || tienePermiso('actualizar' . $ubicacion->nombre))
                                    <button
                                        class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalActualizarUbicacion"
                                        data-id="{{ $ubicacion->id }}"
                                        data-nombre="{{ $ubicacion->nombre }}"
                                        data-estado="{{ $ubicacion->estado }}"
                                        data-clave="{{ $ubicacion->clave }}"
                                        data-empresa-id="{{ $ubicacion->empresa_id }}"
                                        data-empresa-nombre="{{ $ubicacion->empresa->nombre ?? '' }}">
                                        <i class="fas fa-edit me-1"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($ubicaciones->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-map-marked-alt fa-3x mb-3 opacity-25"></i>
                                <h5 class="fw-light">No hay ubicaciones registradas</h5>
                                <p class="text-muted">Comienza agregando una nueva ubicación</p>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($ubicaciones->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if($ubicaciones->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">Anterior</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $ubicaciones->previousPageUrl() }}">Anterior</a>
                </li>
                @endif

                @foreach(range(1, $ubicaciones->lastPage()) as $i)
                <li class="page-item {{ $ubicaciones->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $ubicaciones->url($i) }}">{{ $i }}</a>
                </li>
                @endforeach

                @if($ubicaciones->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $ubicaciones->nextPageUrl() }}">Siguiente</a>
                </li>
                @else
                <li class="page-item disabled">
                    <span class="page-link">Siguiente</span>
                </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
@include('empresa.ubicaciones.modal_insertar_ubicacion')
@include('empresa.ubicaciones.modal_actualizar_ubicacion')