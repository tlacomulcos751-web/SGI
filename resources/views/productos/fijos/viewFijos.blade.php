@extends('layouts.navigation')
@section('title', 'Detalles de '. $producto->nombre)
@section('content')
<?php

use App\Models\Usuario;

$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas(); // Suponiendo que tengas un modelo Etiqueta
?>
<div class="container main-content animate__animated animate__fadeIn">

    <!-- Header Section -->
    <div class="rounded-4 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center text-white gap-3">
            <div class="flex-grow-1">
                <h1 class="display-5 fw-bold mb-2">
                    {{ $producto->nombre }}
                </h1>
                <p class="opacity-75 mb-0">Detalles completos del producto</p>
            </div>
            <div class="d-flex flex-wrap justify-content-end gap-2 w-100 w-md-auto">
                @if (tienePermiso('fijos - modificar') && $productoFijo->estado != 'baja')
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

                <!-- Botón Cambiar Responsable - Color morado -->
                <button id="btnActualizarResponsable" type="button" class="btn btn-primary btn-sm rounded-pill px-3 flex-grow-1 flex-md-grow-0"
                    data-bs-toggle="modal" data-bs-target="#actualizarResponsableModal">
                    <i class="fas fa-user-edit me-1"></i> <span class="d-none d-md-inline">Cambiar responsable</span>
                </button>

                <!-- Botón Carta Responsiva / Carta Poder -->
                <div class="btn-group shadow-sm flex-grow-1 flex-md-grow-0">
                    <a href="{{ route('responsivas.fijo', ['id' => $productoFijo->id, 'formato' => 'pdf']) }}" target="_blank" class="btn btn-success btn-sm rounded-start-pill px-3 text-white">
                        <i class="fas fa-file-signature me-1"></i> <span class="d-none d-md-inline">Carta Responsiva</span>
                    </a>
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split rounded-end-pill px-2 text-white" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Opciones de responsiva</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('responsivas.fijo', ['id' => $productoFijo->id, 'formato' => 'pdf']) }}" target="_blank">
                                <span class="badge bg-danger bg-opacity-10 text-danger me-2"><i class="fas fa-file-pdf"></i></span>
                                <div>
                                    <span class="d-block fw-semibold">Ver / Imprimir PDF</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">Con membrete y códigos de barras</small>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('responsivas.fijo', ['id' => $productoFijo->id, 'formato' => 'word']) }}">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-file-word"></i></span>
                                <div>
                                    <span class="d-block fw-semibold">Descargar Word (.docx)</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">Editable con cláusulas legales</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-muted small" href="{{ route('productos.fijos.generarVale', $productoFijo->id) }}">
                                <i class="fas fa-file-alt me-2"></i> Vale de Salida anterior (Word)
                            </a>
                        </li>
                    </ul>
                </div>
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
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">ID: {{ $productoFijo->id }}</span>
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
                                                onclick="abrirModalImprimirEtiqueta('{{ addslashes($producto->nombre) }}', '{{ $producto->codigoBarra }}', '{{ addslashes($producto->empresa->nombre ?? 'SGI') }}', '{{ $productoFijo->clave ?? '' }}', '{{ is_null($producto->precio) ? 'N/A' : '$' . number_format($producto->precio, 2) }}', '{{ addslashes($producto->ubicacion->nombre ?? '') }}')"
                                                title="Imprimir sticker adhesivo con código de barras">
                                            <i class="fas fa-print me-1"></i> Imprimir Etiqueta
                                        </button>
                                        @endif
                                    </div>
                                    @if($producto->codigoBarra)
                                        <div class="p-2 bg-white rounded border d-inline-block text-center shadow-xs">
                                            <svg id="barcode-fijo-svg" class="img-fluid" style="max-height: 52px;"></svg>
                                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                if (window.renderBarcodeVisual) {
                                                    renderBarcodeVisual('#barcode-fijo-svg', '{{ $producto->codigoBarra }}', {
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
                                    <label class="form-label text-muted small mb-1">Clave</label>
                                    <p class="fw-medium">{{ $productoFijo->clave }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Estado</label>
                                    <span class="badge 
                                        @if($productoFijo->estado == 'Activo') bg-success @else bg-secondary @endif">
                                        {{ $productoFijo->estado }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Calidad</label>
                                    <p class="fw-medium">{{ $productoFijo->calidad }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Responsable</label>
                                    <p class="fw-medium">
                                        @if(esSuperAdmin())
                                        <a href="{{ route('usuarios.ver', optional($productoFijo->usuarioResponsable)->id ?? '#') }}"
                                            class="text-decoration-none d-flex align-items-center"
                                            title="Ver usuario: {{ $productoFijo->usuarioResponsable ? $productoFijo->usuarioResponsable->nombreCompleto() : 'Usuario no encontrado' }}">
                                            <i class="fas fa-user-circle text-primary me-2"></i>
                                            {{ optional($productoFijo->usuarioResponsable)->nombreCompleto() ?? 'N/A' }}
                                        </a>
                                        @else
                                        <span class="d-flex align-items-center text-secondary">
                                            <i class="fas fa-user-circle text-primary me-2"></i>
                                            {{ optional($productoFijo->usuarioResponsable)->nombreCompleto() ?? 'N/A' }}
                                        </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de entrada</label>
                                    <p class="fw-medium">{{ \Carbon\Carbon::parse($productoFijo->fechaEntrada)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Fecha de baja</label>
                                    <p class="fw-medium">
                                        {{ $productoFijo->fechaSalida ? \Carbon\Carbon::parse($productoFijo->fechaSalida)->format('d/m/Y') : "Activo" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Section -->
    <div class="card border-0 shadow-lg mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold text-primary">
                <i class="fas fa-images me-2"></i>Galería de imágenes
            </h5>
        </div>
        <div class="card-body">
            {{-- FORMULARIO PARA ELIMINAR IMÁGENES --}}
            <form action="{{ route('producto.eliminar.imagenes') }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar las imágenes seleccionadas?')">
                @csrf
                <input type="hidden" name="productoFijo_id" value="{{ $productoFijo->id }}">

                @php $fotografias = $productoFijo->getImagenes(); @endphp
                @if (count($fotografias) > 0)
                <div class="row g-3">
                    @foreach ($fotografias as $foto)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="gallery-item position-relative rounded-3 overflow-hidden shadow-sm">
                            <input type="checkbox" name="imagenes[]" value="{{ $foto }}"
                                class="form-check-input position-absolute m-2 seleccion-checkbox"
                                style="z-index: 10;">
                            <img src="{{ $foto }}" class="img-fluid w-100 imagen-seleccionable"
                                alt="Foto del producto"
                                style="height: 200px; object-fit: cover; transition: all 0.3s ease;">
                            <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center opacity-0">
                                <button type="button" class="btn btn-dark btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImage('{{ $foto }}')">
                                    <i class="fas fa-expand me-1"></i> Ampliar
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(tienePermiso("fijos - modificar") && $productoFijo->estado != 'baja')
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3">
                        <i class="fas fa-trash-alt me-1"></i>Eliminar seleccionadas
                    </button>
                </div>
                @endif
                @else
                <div class="text-center py-5 bg-light rounded-3">
                    <i class="fas fa-image fa-3x text-muted mb-3 opacity-50"></i>
                    <h5 class="text-muted">No hay imágenes disponibles</h5>
                    <p class="text-muted small">Agrega imágenes para mostrar una galería</p>
                </div>
                @endif
            </form>

            @if(tienePermiso("fijos - modificar") && $productoFijo->estado != 'baja')
            <form action="{{ route('productoFijo.subirImagenes') }}" method="POST" enctype="multipart/form-data" class="mt-4 p-4 bg-light rounded-3">
                @csrf
                <input type="hidden" name="productoFijo_id" value="{{ $productoFijo->id }}">

                <div class="mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-cloud-upload-alt me-2"></i>Subir nuevas imágenes
                    </h6>
                    <div class="input-group">
                        <input class="form-control form-control-sm" type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" required>
                        <button type="submit" class="btn btn-primary btn-sm rounded-end">
                            <i class="fas fa-upload me-1"></i>Subir
                        </button>
                    </div>
                    <div class="form-text small">Formatos: JPG, PNG, GIF. Máx. 5MB por imagen.</div>
                </div>
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

    <!-- Etiquetas Section fijos-->
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

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Imagen del producto</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-item {
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
        background: rgba(0, 0, 0, 0.3);
    }

    .seleccion-checkbox:checked+img,
    .seleccion-checkbox:checked~img {
        border: 3px solid #dc3545;
        opacity: 0.85;
    }

    .movement-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .movement-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>

<link rel="stylesheet" href="{{ asset('css/estilosEmpresa.css') }}">

<script src="{{ asset('js/productos/fijosView.js') }}"></script>
@endsection

@push('modals')
@include('productos.fijos.actualizarInfo')
@include('productos.fijos.actualizarUbicacion')
@include('productos.fijos.actualizarResponsable')
@include('etiquetas.modalInsertarEtiqueta')
@endpush