@extends('layouts.navigation')
@section('title', 'Empresas ' . ($tipo == 'interna' ? 'Internas' : 'Externas'))
@section('content')
<?php

use App\Models\VistaProductosUnificada;
?>
<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header Section -->
    <div class="glass-nav rounded-4 p-4 mb-5 shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                @if($tipo == 'interna')
                <h1 class="display-5 fw-bold text-gradient mb-2">
                    <i class="fas fa-map-marker-alt me-2"></i> Nuestras Empresas
                </h1>
                <p class="lead text-muted mb-0">Aquí podrás obtener información sobre el inventario que maneja cada una de ellas</p>
                @else
                <h1 class="display-5 fw-bold text-gradient mb-2">
                    <i class="fas fa-building me-2"></i> Empresas Externas
                </h1>
                <p class="lead text-muted mb-0">Aquí podrás obtener información sobre las empresas externas con las que trabajamos</p>
                @endif
            </div>
            @if ((tienePermiso('insertarEmpresaInterna') && $tipo == 'interna') || (tienePermiso('insertarEmpresaExterna') && $tipo == 'externa'))
            <button type="button" class="btn btn-primary btn-float btn-insertar-empresa" data-bs-toggle="modal" data-bs-target="#modalInsertarEmpresa">
                <i class="fas fa-plus-circle me-2"></i> Nueva Empresa
            </button>
            @endif
        </div>
    </div>

    <!-- Barra de búsqueda en tiempo real -->
    <div class="mb-4">
        <input type="text" id="busqueda-empresa" class="form-control" placeholder="Buscar empresa...">
    </div>

    <!-- Empresas Table -->
    <div class="card glass-nav border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">#</th>
                            <th class="py-3">Nombre</th>
                            <th class="py-3">Status</th>
                            @if ($tipo == 'interna')
                            <th class="py-3">Ubicaciones</th>
                            @endif
                            <th class="py-3">Tipos de Productos</th>
                            <th class="pe-4 py-3 text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="empresas-tbody">
                        @foreach ($empresas as $index => $empresa)
                        <tr class="hover-scale">
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                    <strong>{{ $empresa->nombre }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $empresa->status == 'Activa' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $empresa->status == 'Activa' ? 'success' : 'secondary' }}">
                                    {{ $empresa->status }}
                                </span>
                            </td>
                            @if ($tipo == 'interna')
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-info me-2"></i>
                                    {{ $empresa->cantidadUbicaciones() }}
                                </div>
                            </td>
                            @endif
                            @if ($tipo == 'interna')
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-box text-success me-2"></i>
                                    {{ count(VistaProductosUnificada::obtenerActivosEmpresa($empresa->nombre)) }}
                                </div>
                            </td>
                            @else
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-boxes text-warning me-2"></i>
                                    {{ $empresa->cantidadProductosCompraVenta() }}
                                </div>
                            </td>
                            @endif
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if ($tipo == 'interna')
                                    <a href="{{ url('empresa/view/' . $empresa->id) }}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @else
<a href="{{ url('empresa/viewExterna/' . $empresa->id) }}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($empresas->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if($empresas->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">Anterior</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $empresas->previousPageUrl() }}">Anterior</a>
                </li>
                @endif

                @foreach(range(1, $empresas->lastPage()) as $i)
                <li class="page-item {{ $empresas->currentPage() == $i ? 'active' : '' }}">
                    <a class="page-link" href="{{ $empresas->url($i) }}">{{ $i }}</a>
                </li>
                @endforeach

                @if($empresas->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $empresas->nextPageUrl() }}">Siguiente</a>
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

@push('modals')
    @include('empresa.modal_insertar_empresa')
@endpush