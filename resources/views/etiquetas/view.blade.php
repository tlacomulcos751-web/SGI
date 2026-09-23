@extends('layouts.navigation')
@section('title', 'Etiqueta '. $etiqueta->nombre)
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <link rel="stylesheet" href="{{ asset('css/estilosEmpresa.css') }}">
    <!-- Header Section -->
    <div class="rounded-4 p-4 mb-5 shadow-sm company-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 fw-bold mb-2 company-title">
                    <i class="fas fa-map-marker-alt me-2"></i>Etiqueta: {{ $etiqueta->nombre }}
                </h1>
                <p class="company-subtitle">Detalles completos de la etiqueta</p>
            </div>
            <div class="d-flex gap-2">
                @if (tienePermiso('modificarEtiquetas'))
                <button type="button"
                    class="btn btn-sm btn-info btn-editar-etiqueta"
                    data-id="{{ $etiqueta->id }}"
                    data-nombre="{{ $etiqueta->nombre }}"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarEtiqueta"
                    title="Editar">
                    <i class="fas fa-edit"></i> Editar
                </button>
                @endif

                @if (tienePermiso('desactivarEtiquetas'))
                <button type="button"
                    class="btn btn-sm btn-danger btn-eliminar-etiqueta"
                    data-id="{{ $etiqueta->id }}"
                    data-nombre="{{ $etiqueta->nombre }}"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEliminarEtiqueta"
                    title="Eliminar">
                    <i class="fas fa-trash-alt"></i> Desactivar
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 company-details-card">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Número consecutivo</label>
                        <p class="company-data">{{ $etiqueta->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <p class="company-data">{{ $etiqueta->nombre }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 company-details-card">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos Asociados</h5>
        </div>
        <div class="card-body">
            @if ($etiqueta->productos->isEmpty())
            <p class="text-muted">No hay productos asociados a esta etiqueta.</p>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light bg-gradient">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase fw-semibold text-muted">#</th>
                            <th class="py-3 text-uppercase fw-semibold text-muted">Nombre del Producto</th>
                            <th class="py-3 text-uppercase fw-semibold text-muted">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($etiqueta->productos as $index => $producto)
                        <tr class="hover-scale transition-all">
                            <td class="ps-4 fw-medium text-primary">{{ $index + 1 }}</td>
                            <td class="fw-semibold">
                                <a href="#">{{ $producto->nombre }}
                            </td></a>
                            <td>{{ $etiqueta->existenciaProducto($producto->id) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
@include('etiquetas.modalEditarEtiqueta')
@include('etiquetas.modalEliminarEtiqueta')