@extends('layouts.navigation')
@section('title', 'Detalles de '. $producto->nombre)
@section('content')
<?php

use App\Models\Usuario;

$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas(); // Suponiendo que tengas un modelo Etiqueta
?>
<div class="container main-content animate__animated animate__fadeIn">

    <!-- Header Section -->
    <div class="rounded-4 p-4 mb-4 shadow-sm text-white position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.08);">
        
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center gap-3 position-relative" style="z-index: 2;">
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge rounded-pill px-3 py-1 font-monospace" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); font-size: 0.78rem; color: #cbd5e1;">
                        <iconify-icon icon="lucide:barcode" width="14" height="14" class="me-1"></iconify-icon> {{ $productoFijo->clave }}
                    </span>
                    @if(strtolower($productoFijo->estado ?? '') === 'reparacion')
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                            <iconify-icon icon="lucide:alert-triangle" width="13" height="13" class="me-1"></iconify-icon> En Reparación
                        </span>
                    @elseif(strtolower($productoFijo->estado ?? '') === 'activo')
                        <span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                            <iconify-icon icon="lucide:check-circle-2" width="13" height="13" class="me-1"></iconify-icon> Activo
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary rounded-pill px-3 py-1" style="font-size: 0.75rem;">
                            {{ ucfirst($productoFijo->estado ?? 'Baja') }}
                        </span>
                    @endif
                </div>

                <h1 class="h2 fw-bold mb-1 text-white tracking-tight">
                    {{ $producto->nombre }}
                </h1>
                <p class="text-white text-opacity-75 mb-0 small d-flex align-items-center flex-wrap gap-2">
                    <span class="d-inline-flex align-items-center">
                        <iconify-icon icon="lucide:building-2" width="14" height="14" class="me-1 text-info"></iconify-icon>
                        {{ $producto->empresa?->nombre ?? 'Grupo Nodo' }}
                    </span>
                    <span class="opacity-50">•</span>
                    <span class="d-inline-flex align-items-center">
                        <iconify-icon icon="lucide:map-pin" width="14" height="14" class="me-1 text-warning"></iconify-icon>
                        {{ $producto->ubicacion?->nombre ?? 'Sin ubicación asignada' }}
                    </span>
                </p>
            </div>

            <!-- Botones de Acción con Iconify y Estilo Corporativo Elegante -->
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-xl-auto">
                <!-- Botón Vale de Salida -->
                <a href="{{ route('productos.fijos.generarVale', $productoFijo->id) }}" 
                   class="btn btn-light btn-sm rounded-pill px-3 py-2 text-dark fw-semibold shadow-xs d-inline-flex align-items-center gap-2 border-0"
                   title="Descargar Vale de Salida en formato Word (.docx)">
                    <iconify-icon icon="lucide:file-down" width="16" height="16" class="text-primary"></iconify-icon>
                    <span>Generar Vale de Salida</span>
                </a>

                <!-- Botón Orden de Mantenimiento -->
                <a href="{{ route('productos.fijos.generarMantenimiento', $productoFijo->id) }}" 
                   class="btn btn-light btn-sm rounded-pill px-3 py-2 text-dark fw-semibold shadow-xs d-inline-flex align-items-center gap-2 border-0"
                   title="Descargar Orden de Mantenimiento en formato Word (.docx)">
                    <iconify-icon icon="lucide:wrench" width="16" height="16" class="text-warning"></iconify-icon>
                    <span>Orden de Mantenimiento</span>
                </a>

                @if (tienePermiso('fijos - modificar') && $productoFijo->estado != 'baja')
                <!-- Botón Editar Información -->
                <button id="btnActualizarInfo" type="button" 
                        class="btn btn-header-action btn-sm rounded-pill px-3 py-2 text-white fw-medium d-inline-flex align-items-center gap-2 shadow-xs"
                        data-bs-toggle="modal" data-bs-target="#actualizarInfoModal">
                    <iconify-icon icon="lucide:file-pen-line" width="15" height="15" class="text-white"></iconify-icon>
                    <span>Editar Información</span>
                </button>

                <!-- Botón Cambiar Ubicación -->
                <button id="btnActualizarUbicacion" type="button" 
                        class="btn btn-header-action btn-sm rounded-pill px-3 py-2 text-white fw-medium d-inline-flex align-items-center gap-2 shadow-xs"
                        data-bs-toggle="modal" data-bs-target="#actualizarUbicacionModal">
                    <iconify-icon icon="lucide:map-pin" width="15" height="15" class="text-white"></iconify-icon>
                    <span>Cambiar Ubicación</span>
                </button>

                <!-- Botón Cambiar Responsable -->
                <button id="btnActualizarResponsable" type="button" 
                        class="btn btn-header-action btn-sm rounded-pill px-3 py-2 text-white fw-medium d-inline-flex align-items-center gap-2 shadow-xs"
                        data-bs-toggle="modal" data-bs-target="#actualizarResponsableModal">
                    <iconify-icon icon="lucide:user-check" width="15" height="15" class="text-white"></iconify-icon>
                    <span>Cambiar Responsable</span>
                </button>

                <!-- Botón Registrar Mantenimiento Preventivo -->
                <button type="button" 
                        class="btn btn-header-action btn-sm rounded-pill px-3 py-2 text-white fw-medium d-inline-flex align-items-center gap-2 shadow-xs"
                        data-bs-toggle="modal" data-bs-target="#modalAgregarMantenimientoFijo">
                    <iconify-icon icon="lucide:wrench" width="15" height="15" class="text-white"></iconify-icon>
                    <span>Mantenimiento</span>
                </button>
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
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" 
                                                    data-bs-toggle="modal" data-bs-target="#actualizarInfoModal"
                                                    onclick="setTimeout(function(){ var input = document.getElementById('codigoBarraInput'); if(input){ input.focus(); if(!input.value.trim()){ generarCodigoBarra('codigoBarraInput', 'barcode-preview-fijo-edit'); var pBox = document.getElementById('preview-box-fijo-edit'); if(pBox) pBox.classList.remove('d-none'); } } }, 350);"
                                                    title="Asignar código de barras a este activo fijo">
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

    <!-- Comentarios Section (independiente) -->
    <div class="card border border-slate-200 rounded-xl shadow-xs overflow-hidden mb-4 bg-white" id="comentarios-fijo-container">
        <div class="px-5 py-3.5 border-b border-slate-100 d-flex align-items-center gap-2 bg-white">
            <iconify-icon icon="lucide:message-square-text" width="16" height="16" class="text-slate-700"></iconify-icon>
            <h6 class="mb-0 font-semibold text-slate-900 text-sm">Comentarios</h6>
            <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                {{ $comentarios->count() }} {{ $comentarios->count() === 1 ? 'comentario' : 'comentarios' }}
            </span>
        </div>

        <div class="p-5">
            {{-- Lista de comentarios --}}
            @if($comentarios->isNotEmpty())
            <div class="space-y-3 mb-5">
                @foreach($comentarios as $comentario)
                <div class="p-3.5 border border-slate-100 rounded-lg bg-white d-flex align-items-start gap-3">
                    <div class="flex-shrink-0 rounded-lg bg-slate-100 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <iconify-icon icon="lucide:user-round" width="15" height="15" class="text-slate-500"></iconify-icon>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                            <div>
                                <span class="font-medium text-slate-900 text-sm">
                                    {{ optional($comentario->usuario)->nombreCompleto() ?? 'Usuario' }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono ms-2">
                                    {{ \Carbon\Carbon::parse($comentario->created_at)->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            @if(tienePermiso('fijos - modificar'))
                            <form action="{{ route('productos_fijos.comentario.eliminar', $comentario->id) }}" method="POST" class="m-0"
                                  onsubmit="return confirm('¿Eliminar este comentario?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-red-500 p-1 rounded border-0 bg-transparent transition-colors" title="Eliminar comentario">
                                    <iconify-icon icon="lucide:trash-2" width="13" height="13"></iconify-icon>
                                </button>
                            </form>
                            @endif
                        </div>
                        <p class="mb-0 text-slate-600 text-sm">{{ $comentario->comentario }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-5 mb-4">
                <iconify-icon icon="lucide:message-square-dashed" width="28" height="28" class="text-slate-300 mb-2"></iconify-icon>
                <p class="text-sm font-medium text-slate-700 mb-1">Sin comentarios</p>
                <p class="text-xs text-slate-400">Agrega el primer comentario sobre este activo.</p>
            </div>
            @endif

            {{-- Formulario para agregar comentario --}}
            @if(tienePermiso('fijos - modificar') && $productoFijo->estado != 'baja')
            <form action="{{ route('productos_fijos.comentario.agregar', $productoFijo->id) }}" method="POST"
                  class="border-t border-slate-100 pt-4 d-flex align-items-start gap-3">
                @csrf
                <div class="flex-shrink-0 rounded-lg bg-slate-100 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    <iconify-icon icon="lucide:user-round" width="15" height="15" class="text-slate-500"></iconify-icon>
                </div>
                <div class="flex-grow-1 d-flex gap-2">
                    <textarea name="comentario" rows="2"
                              class="form-input-clean flex-grow-1"
                              placeholder="Escribe un comentario sobre este activo..."
                              required maxlength="2000"></textarea>
                    <button type="submit" class="btn-clean-primary align-self-end flex-shrink-0">
                        <iconify-icon icon="lucide:send" width="14" height="14"></iconify-icon>
                        <span>Enviar</span>
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>

    <!-- Mantenimiento Preventivo Section -->
    @include('components.fijo.mantenimiento', ['productoFijo' => $productoFijo])

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
    .btn-header-action {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.22);
        backdrop-filter: blur(8px);
        transition: all 0.2s ease;
    }
    .btn-header-action:hover {
        background: rgba(255, 255, 255, 0.24);
        border-color: rgba(255, 255, 255, 0.4);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
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
@include('productos.fijos.modales.modalAgregarMantenimiento', ['productoFijo' => $productoFijo, 'producto' => $producto])
@endpush