@extends('layouts.navigation')
@section('title', 'Detalles de ' . $producto->nombre)
@section('content')
<div class="container main-content animate__animated animate__fadeIn">

    <!-- Header -->
    <div class="rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white gap-3">
            <div class="flex-grow-1">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="fas fa-box-open me-2"></i>{{ $producto->nombre }}
                </h1>
                <p class="opacity-75 mb-0">Detalles del producto de compra/venta</p>
            </div>
            <div class="d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">
                @if (tienePermiso('compra/venta - modificar') && $productoCompraVenta->estado != 'vendido')
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

                <button id="btnActualizarResponsable" type="button" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1 flex-md-grow-0"
                    data-bs-toggle="modal" data-bs-target="#actualizarEntregaModal">
                    <i class="fas fa-user-edit me-1"></i> <span class="d-none d-md-inline">Cambiar su estado</span>
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
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">ID: {{ $productoCompraVenta->id }}</span>
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
                                    <p class="fw-medium">{{ $productoCompraVenta->producto->nombre }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label text-muted small mb-0">Código de barras</label>
                                        @if($productoCompraVenta->producto->codigoBarra)
                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fs-7" 
                                                onclick="abrirModalImprimirEtiqueta('{{ addslashes($productoCompraVenta->producto->nombre) }}', '{{ $productoCompraVenta->producto->codigoBarra }}', '{{ addslashes($productoCompraVenta->empresaVenta->nombre ?? 'SGI') }}', '', '{{ is_null($productoCompraVenta->producto->precio) ? 'N/A' : '$' . number_format($productoCompraVenta->producto->precio, 2) }}', '{{ addslashes($productoCompraVenta->producto->ubicacion->nombre ?? '') }}')"
                                                title="Imprimir sticker adhesivo con código de barras">
                                            <i class="fas fa-print me-1"></i> Imprimir Etiqueta
                                        </button>
                                        @endif
                                    </div>
                                    @if($productoCompraVenta->producto->codigoBarra)
                                        <div class="p-2 bg-white rounded border d-inline-block text-center shadow-xs">
                                            <svg id="barcode-cv-svg" class="img-fluid" style="max-height: 52px;"></svg>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                if (window.renderBarcodeVisual) {
                                                    renderBarcodeVisual('#barcode-cv-svg', '{{ $productoCompraVenta->producto->codigoBarra }}', {
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
                                    <p class="text-muted">{{ $productoCompraVenta->producto->descripcion }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted small fw-bold mb-3">Ubicación y precio</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Ubicación</label>
                                    <p class="fw-medium mb-0">
                                        <a href="{{ route('empresa.ubicaciones.ver',$producto->ubicacion->id) }}"
                                            class="text-decoration-none d-inline-flex align-items-center text-break"
                                            title="Ver ubicación: {{ $producto->ubicacion->nombre }}">
                                            <i class="fas fa-map-marker-alt text-danger me-2 flex-shrink-0"></i>
                                            <span>{{ $productoCompraVenta->producto->ubicacion->nombre }}</span>
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Precio</label>
                                    <p class="fw-medium mb-0">
                                        @if(!is_null($productoCompraVenta->producto->precio))
                                            <span class="text-success fw-bold fs-6">${{ number_format($productoCompraVenta->producto->precio, 2) }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">N/A (No aplica)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de registro</label>
                                    <p class="fw-medium mb-0">{{ \Carbon\Carbon::parse($productoCompraVenta->producto->fecha_registro)->format('d/m/Y') }}</p>
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
                                    <label class="form-label text-muted small mb-1">Empresa a vender</label>
                                    <p class="fw-medium">
                                        <a href="{{ url('empresa/view/' . $productoCompraVenta->empresa->id) }}
                                            class="text-decoration-none d-flex align-items-center"
                                            title="Ver empresa: {{ $productoCompraVenta->empresa->nombre }}">
                                            {{ $productoCompraVenta->empresa->nombre }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Existencia</label>
                                    <p class="fw-medium">{{ $productoCompraVenta->existencia }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Estado</label>
                                    <span class="badge 
                                        @if($productoCompraVenta->estado == 'almacen') bg-success @else bg-secondary @endif">
                                        {{ $productoCompraVenta->estado }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de entrada</label>
                                    <p class="fw-medium">{{ \Carbon\Carbon::parse($productoCompraVenta->fechaEntrada)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de baja</label>
                                    <p class="fw-medium">
                                        {{ $productoCompraVenta->fechaSalida ? \Carbon\Carbon::parse($productoCompraVenta->fechaSalida)->format('d/m/Y') : "Activo" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Etiquetas compra venta -->
    @include('components.productos.etiquetas-producto', [
    'etiquetas' => $etiquetas,
    'producto' => $producto,
    'todasLasEtiquetas' => $todasLasEtiquetas,
    'permiso' => 'consumible - modificar'
    ])

    <!-- Movimientos de compra venta-->
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
<script src="{{ asset('js/productos/compraVentaView.js') }}"></script>
@endsection

@push('modals')
<!-- Modales -->
@include('productos.compraVenta.modal.actualizarInfo')
@include('productos.compraVenta.modal.actualizarUbicacion')
@include('productos.compraVenta.modal.entrega')
@include('etiquetas.modalInsertarEtiqueta')
@endpush