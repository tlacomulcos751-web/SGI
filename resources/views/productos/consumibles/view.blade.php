@extends('layouts.navigation')
@section('title', 'Detalles de ' . $producto->nombre)
@section('content')
<div class="container main-content animate__animated animate__fadeIn">

    <!-- Header -->
    <div class="rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg,rgb(103, 65, 255) 0%,rgb(103, 43, 255) 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white gap-3">
            <div class="flex-grow-1">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-box-open me-2"></i>{{ $producto->nombre }}
                </h1>
                <p class="opacity-75 mb-0">Detalles del producto de compra/venta</p>
            </div>
            <div class="d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">
                @if (tienePermiso('consumible - modificar'))
                <!-- Botón Editar Información - Color verde -->
                <button id="btnActualizarInfo" type="button" class="btn btn-success btn-sm rounded-pill px-3 flex-grow-1 flex-md-grow-0"
                    data-bs-toggle="modal" data-bs-target="#actualizarInfoModal">
                    <i class="fas fa-info-circle me-1"></i> <span class="d-none d-md-inline">Editar información</span>
                </button>

                <!-- Botón Cambiar Ubicación - Color naranja -->
                <button id="btnActualizarUbicacion" type="button" class="btn btn-warning btn-sm rounded-pill px-3 flex-grow-1 flex-md-grow-0 text-white"
                    data-bs-toggle="modal" data-bs-target="#actualizarUbicacionModal">
                    <i class="fas fa-map-marker-alt me-1"></i> <span class="d-none d-md-inline">Cambiar ubicación</span>
                </button>

                <button id="btnActualizarExistencia" type="button" class="btn btn-success btn-sm rounded-pill px-3 flex-grow-1 flex-md-grow-0"
                    data-bs-toggle="modal" data-bs-target="#ajustarExistenciaModal">
                    <i class="fas fa-info-circle me-1"></i> <span class="d-none d-md-inline">Modificar Existencia</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Información General -->
    <div class="card border-0 shadow-lg overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Información General
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">ID: {{ $productoConsumible->id }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="row g-0">
                <!-- Left Column -->
                <div class="col-lg-6 p-4 border-end">
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Datos básicos</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Nombre</label>
                                    <p class="fw-medium">{{ $producto->nombre }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label text-muted small mb-0">Código de barras</label>
                                        @if($producto->codigoBarra)
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fs-7" 
                                                onclick="abrirModalImprimirEtiqueta('{{ addslashes($producto->nombre) }}', '{{ $producto->codigoBarra }}', '{{ addslashes($producto->empresa->nombre ?? 'SGI') }}', '', '{{ is_null($producto->precio) ? 'N/A' : '$' . number_format($producto->precio, 2) }}', '{{ addslashes($producto->ubicacion->nombre ?? '') }}')"
                                                title="Imprimir sticker adhesivo con código de barras">
                                            <i class="fas fa-print me-1"></i> Imprimir Etiqueta
                                        </button>
                                        @endif
                                    </div>
                                    @if($producto->codigoBarra)
                                        <div class="p-2 bg-white rounded border d-inline-block text-center shadow-xs">
                                            <svg id="barcode-consumible-svg" class="img-fluid" style="max-height: 52px;"></svg>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                if (window.renderBarcodeVisual) {
                                                    renderBarcodeVisual('#barcode-consumible-svg', '{{ $producto->codigoBarra }}', {
                                                        width: 1.8,
                                                        height: 40,
                                                        fontSize: 12,
                                                        margin: 3
                                                    });
                                                }
                                            });
                                        </script>
                                    @else
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">Sin código asignado</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#actualizarInfo">
                                                <i class="fas fa-magic me-1"></i> Asignar
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Descripción</label>
                                    <p class="text-muted">{{ $producto->descripcion }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Ubicación y precio</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Empresa Propietaria</label>
                                    <p class="fw-medium mb-0">
                                        <span class="badge bg-light text-dark border px-2 py-1 text-wrap text-start lh-base" style="font-size: 0.9rem; max-width: 100%;">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            {{ $producto->empresa->nombre ?? 'Sin empresa asignada' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Ubicación</label>
                                    <p class="fw-medium mb-0">
                                        <a href="{{ route('empresa.ubicaciones.ver',$producto->ubicacion->id) }}"
                                            class="text-decoration-none d-inline-flex align-items-center text-break"
                                            title="Ver ubicación: {{ $producto->ubicacion->nombre }}">
                                            <i class="fas fa-map-marker-alt text-danger me-2 flex-shrink-0"></i>
                                            <span>{{ $producto->ubicacion->nombre }}</span>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Precio</label>
                                    <p class="fw-medium mb-0">
                                        @if(!is_null($producto->precio))
                                            <span class="text-success fw-bold fs-6">${{ number_format($producto->precio, 2) }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">N/A (No aplica)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de registro</label>
                                    <p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($producto->fecha_registro)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-6 p-4">
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Estado del producto</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Existencia</label>
                                    <p class="fw-medium">{{ $productoConsumible->existencia }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Estado</label>
                                    <span class="badge @if($productoConsumible->existencia > 0) bg-success @else bg-secondary @endif">
                                        @if($productoConsumible->existencia > 0) Activo @else Agotado @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-lg overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold text-warning">
                <i class="fas fa-bell me-2"></i>Notificaciones
            </h5>
        </div>
        <div class="card-body p-4">

            @if($productoConsumible->notificaciones->count() > 0)
            <div class="d-flex flex-column gap-2 mb-3">
                @foreach($productoConsumible->notificaciones as $notificacion)
                <form method="POST" action="{{ route('notificaciones_consumibles.destroy', $notificacion->id) }}" class="d-flex align-items-center justify-content-between border rounded px-3 py-2">
                    @csrf
                    <div>
                        <strong>Usuario:</strong> {{ $notificacion->usuario->nombreCompleto() }} <br>
                        <strong>Límite:</strong> {{ $notificacion->limite }} unidades
                    </div>
                    @if (tienePermiso('consumible - modificar'))
                    <button type="submit" class="btn btn-sm btn-link text-danger ms-2 p-0" title="Eliminar notificación">
                        <i class="fas fa-times"></i>
                    </button>
                    @endif
                </form>
                @endforeach
            </div>
            @else
            <p class="text-muted mb-3">Este producto no tiene notificaciones configuradas.</p>
            @endif

            @if (tienePermiso('consumible - modificar'))
            <form method="POST" action="{{ route('notificaciones_consumibles.store') }}" class="d-flex flex-wrap align-items-end gap-2">
                @csrf
                <input type="hidden" name="id_productos_consumibles" value="{{ $productoConsumible->id }}">
                <div>
                    <label class="form-label mb-1">Usuario</label>
                    <select name="id_user" class="form-select" required>
                        <option value="">Seleccionar usuario</option>
                        @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->nombreCompleto() }} </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1">Límite</label>
                    <input type="number" name="limite" class="form-control" min="0" placeholder="Ej: 10" required>
                </div>
                <button type="submit" class="btn btn-sm btn-success mt-3">
                    <i class="fas fa-plus me-1"></i>Agregar
                </button>
            </form>
            @endif

        </div>
    </div>

    <!-- Comentarios Section -->
    @php
        $comentariosMovimientos = $movimientos->filter(fn($m) => !empty($m->comentario));
    @endphp
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold text-primary">
                <i class="fas fa-comment-dots me-2"></i>Comentarios
            </h5>
        </div>
        <div class="card-body p-4">
            @if($comentariosMovimientos->isNotEmpty())
            <div class="d-flex flex-column gap-3">
                @foreach($comentariosMovimientos as $movComentario)
                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-comment text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark">
                                {{ optional($movComentario->usuario)->nombreCompleto() ?? 'Usuario' }}
                            </strong>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($movComentario->fecha)->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-2" style="font-size: 0.75rem;">
                            {{ $movComentario->accion }}
                        </span>
                        <p class="mb-0 text-muted fst-italic">"{{ $movComentario->comentario }}"</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4">
                <i class="fas fa-comment-slash fa-2x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-0">No hay comentarios registrados para este producto.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Etiquetas consumibles-->
    @include('components.productos.etiquetas-producto', [
    'etiquetas' => $etiquetas,
    'producto' => $producto,
    'todasLasEtiquetas' => $todasLasEtiquetas,
    'permiso' => 'consumible - modificar'
    ])

    <div id="movimientos-wrapper">
        @include('components.productos.movimientos', [
        'movimientos' => $movimientos,
        'enlaceUsuario' => true,
        'class' => '',
        'badgeClass' => 'bg-primary-subtle text-primary fw-medium'
        ])
    </div>

</div>

<link rel="stylesheet" href="{{ asset('css/estilosEmpresa.css') }}">
@endsection

@push('modals')
@include('productos.consumibles.modal.actualizarInfo')
@include('notificaciones.modal_crear')
@include('productos.consumibles.modal.actualizarUbicacion')
@include('etiquetas.modalInsertarEtiqueta')
@include('productos.consumibles.modal.actualizarExistencia')
@endpush