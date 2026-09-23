 @extends('layouts.navigation')
 @section('title', 'Empresa '. $empresa->nombre)
 @section('content')
 <?php

    use App\Models\VistaProductosUnificada;
    ?>
 <div class="container main-content animate__animated animate__fadeIn">

     <!-- Header Section -->
     <div class="rounded-4 p-4 mb-5 shadow-sm company-header">
         <div class="d-flex justify-content-between align-items-center">
             <div>
                 <h1 class="display-5 fw-bold mb-2 company-title">
                     <i class="fas fa-building me-2"></i>Empresa {{ $empresa->nombre }}
                 </h1>
                 <p class="company-subtitle">Detalles completos de la empresa</p>
             </div>
             <div class="d-flex gap-2">
                 @if (tienePermiso('modificarEmpresaInterna'))
                 <button type="button"
                     data-bs-toggle="modal"
                     data-bs-target="#modalActualizarEmpresa"
                     class="btn btn-sm btn-primary btn-editar-empresa"
                     data-id="{{ $empresa->id }}"
                     data-nombre="{{ $empresa->nombre }}"
                     data-nombre_registrado="{{ $empresa->nombre_registrado }}"
                     data-rfc="{{ $empresa->rfc }}"
                     data-direccion="{{ $empresa->direccion }}"
                     data-latitud="{{ $empresa->latitud }}"
                     data-longitud="{{ $empresa->longitud }}"
                     data-fotos="{{ $empresa->fotografias }}"
                     data-action="{{ route('empresa.actualizar', $empresa->id) }}">
                     <i class="fas fa-edit"></i> Editar empresa
                 </button>
                 @endif
                 @if (tienePermiso('agregarUbicaciones') && $tipo == 'interna')
                 <a href="#" class="btn btn-add-location"
                     data-empresa-id="{{ $empresa->id ?? '' }}"
                     data-empresa-nombre="{{ $empresa->nombre ?? '' }}"
                     data-bs-toggle="modal"
                     data-bs-target="#modalInsertarUbicacion">
                     <i class="fas fa-plus me-2"></i> Nueva Ubicación
                 </a>
                 @endif
             </div>
         </div>
     </div>

     <!-- Company Details Card -->
     <div class="card border-0 shadow-lg mb-4 company-details-card">
         <div class="card-header bg-gradient-primary py-3">
             <h5 class="mb-0 fw-semibold"><i class="fas fa-building me-2"></i>Información General de la Empresa</h5>
         </div>
         <div class="card-body p-4">
             <div class="row g-4">
                 <div class="col-md-6">
                     <div class="bg-light p-3 rounded">
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Número consecutivo</label>
                             <p class="company-data fw-bold text-primary">{{ $empresa->id }}</p>
                         </div>
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Nombre comercial</label>
                             <p class="company-data fw-bold">{{ $empresa->nombre }}</p>
                         </div>
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Nombre registrado</label>
                             <p class="company-data fw-bold">{{ $empresa->nombre_registrado }}</p>
                         </div>
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">RFC</label>
                             <p class="company-data fw-bold font-monospace">{{ $empresa->rfc }}</p>
                         </div>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="bg-light p-3 rounded">
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Tipo</label>
                             <p class="company-data fw-bold text-capitalize">{{ $empresa->status }}</p>
                         </div>
                         @if($tipo == 'interna')
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Número de ubicaciones</label>
                             <p class="company-data fw-bold">{{ $empresa->cantidadUbicaciones() }}</p>
                         </div>
                         @endif
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Dirección</label>
                             <p class="company-data fw-bold">{{ $empresa->direccion }}</p>
                         </div>
                         <div class="mb-3">
                             <label class="form-label text-muted small mb-1">Ubicación geográfica</label>
                             <p class="company-data">
                                 <span class="d-block"><i class="fas fa-map-marker-alt text-danger me-2"></i> Latitud: {{ $empresa->latitud ?? 'No disponible' }}</span>
                                 <span class="d-block"><i class="fas fa-map-marker-alt text-primary me-2"></i> Longitud: {{ $empresa->longitud ?? 'No disponible' }}</span>
                             </p>
                         </div>
                     </div>
                 </div>

                 <form action="{{ route('empresa.eliminar.imagenes') }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar las imágenes seleccionadas?')">
                     @csrf
                     <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">

                     <div class="col-12 mt-3">
                         <div class="border-top pt-4">
                             <label class="form-label text-muted small mb-2">
                                 <i class="fas fa-camera me-2"></i>Galería de imágenes
                             </label>
                             <div class="row g-3">
                                 @php $fotografias = $empresa->getImagenes(); @endphp
                                 @if (count($fotografias) > 0)
                                 @foreach ($fotografias as $foto)
                                 <div class="col-md-3 col-6">
                                     <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative">
                                         <input type="checkbox" name="imagenes[]" value="{{ asset($foto) }}" class="form-check-input position-absolute m-2" style="z-index: 10;">
                                         <img src="{{ asset($foto) }}" class="img-fluid rounded-3"
                                             alt="Foto de la empresa"
                                             style="height: 180px; width: 100%; object-fit: cover; transition: transform 0.3s;"
                                             onmouseover="this.style.transform='scale(1.03)'"
                                             onmouseout="this.style.transform='scale(1)'">
                                     </div>
                                 </div>
                                 @endforeach

                                 <div class="col-12 mt-3">
                                     <button type="submit" class="btn btn-danger">
                                         <i class="fas fa-trash-alt me-2"></i>Eliminar seleccionadas
                                     </button>
                                 </div>
                                 @else
                                 <div class="col-12">
                                     <div class="text-center py-4 bg-light rounded">
                                         <i class="fas fa-image fa-2x text-muted mb-3"></i>
                                         <p class="text-muted mb-0">No hay fotografías disponibles</p>
                                     </div>
                                 </div>
                                 @endif
                             </div>
                         </div>
                     </div>
                 </form>

             </div>
         </div>
     </div>
     <!-- Locations Section -->
     @if($tipo == 'interna')
     <div class="card border-0 shadow-sm mb-4 locations-card">
         <div class="card-header bg-transparent py-3">
             <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Ubicaciones</h5>
         </div>
         <div class="card-body">
             @php $ubicaciones = $empresa->ubicaciones()->get(); @endphp
             @if ($ubicaciones->count() == 0)
             <div class="alert alert-warning mb-0">
                 <i class="fas fa-info-circle me-2"></i>No hay ubicaciones registradas
             </div>
             @else
             <div class="row">
                 @foreach ($ubicaciones as $ubicacion)
                 <div class="col-md-4 mb-3">
                     <div class="card h-100 location-item">
                         <div class="card-body">
                             <h6 class="card-title">
                                 <i class="fas fa-map-marker-alt me-2" style="color: #20c997;"></i>{{ $ubicacion->nombre }}
                             </h6>
                             @php $productos = VistaProductosUnificada::obtenerActivosUbicacion($ubicacion->nombre) @endphp
                             <p class="text-muted mb-2">
                                 {{ $productos->count() }} productos asociados
                             </p>
                             <a href="{{ url('empresa/ubicaciones/' . $ubicacion->id) }}" class="btn btn-sm btn-view-details">
                                 Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                             </a>
                         </div>
                     </div>
                 </div>
                 @endforeach
             </div>
             @endif
         </div>
     </div>
     @endif

     <!-- Products Section -->
     <div class="card border-0 shadow-sm products-card">
         <div class="card-header bg-transparent py-3">
             <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos</h5>
         </div>
         <div class="card-body">
             <div class="d-flex justify-content-between align-items-center mb-3">
                 <p class="mb-0 fw-bold">Cantidad de tipos de Productos:
                     @if ($tipo == 'interna')
                     {{ count(VistaProductosUnificada::obtenerActivosEmpresa($empresa->nombre)) }}
                     @else
                     {{ $empresa->cantidadProductosCompraVenta() }}
                     @endif
                 </p>
                 @if ($tipo == 'interna')
                 <p class="mb-0 fw-bold">
                     Número de productos:
                     {{ VistaProductosUnificada::contarPorUbicaciones($ubicaciones->pluck('id')) }}
                 </p>
                 @else
                 <p class="mb-0 fw-bold">
                     Número de productos en almacen:
                     {{ $empresa->existenciaProductos() }}
                 </p>
                 <p class="mb-0 fw-bold">
                     Número de productos vendidos:
                     {{ $empresa->existenciaProductosVendidos() }}
                 </p>
                 @endif
             </div>

             @if ($tipo == 'interna')
             @if ($ubicaciones->count() > 0)
             <div class="accordion" id="productsAccordion">
                 @foreach ($ubicaciones as $ubicacion)
                 @php

                 $productos = VistaProductosUnificada::where('ubicacion_nombre', $ubicacion->nombre)
                 ->whereNotIn('estado', ['baja', 'inexistente', 'eliminado'])
                 ->get();

                 $totalActivos = $productos->count();
                 @endphp

                 <div class="accordion-item mb-2 shadow-sm">
                     <h2 class="accordion-header" id="heading{{ $ubicacion->id }}">
                         <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                             data-bs-target="#collapse{{ $ubicacion->id }}" aria-expanded="false"
                             aria-controls="collapse{{ $ubicacion->id }}">
                             <i class="fas fa-map-marker-alt me-2" style="color: #20c997;"></i>{{ $ubicacion->nombre }}
                             <span class="badge bg-primary ms-2">{{ $totalActivos }}</span>
                         </button>
                     </h2>

                     <div id="collapse{{ $ubicacion->id }}" class="accordion-collapse collapse"
                         aria-labelledby="heading{{ $ubicacion->id }}" data-bs-parent="#productsAccordion">
                         <div class="accordion-body">
                             @if ($productos->isEmpty())
                             <div class="alert alert-info mb-0">
                                 No hay productos activos en esta ubicación
                             </div>
                             @else
                             <div class="list-group list-group-flush">
                                 @foreach ($productos as $producto)
@php
switch($producto->tipo){
    case 'fijo':
        $ruta = route('productos_fijos.viewFijos', $producto->tipo_id);
        break;
    case 'consumible':
        $ruta = route('productos_consumibles.view', $producto->tipo_id);
        break;
    case 'vehiculo':
        $ruta = route('vehiculos.ver', $producto->id);
        break;
    case 'compraventa':
        $ruta = route('productos_compra_venta.view', $producto->tipo_id);
        break;
    default:
        $ruta = '#'; // o alguna ruta segura
}
@endphp
                                 <a href="{{ $ruta }}" class="list-group-item list-group-item-action">
                                     <div class="d-flex justify-content-between align-items-center">
                                         <span>{{ $producto->nombre }}: {{ $producto->existencia }}</span>
                                         <i class="fas fa-chevron-right text-muted"></i>
                                     </div>
                                 </a>
                                 @endforeach
                             </div>
                             @endif
                         </div>
                     </div>
                 </div>
                 @endforeach
             </div>
             @endif
             @else
             @php $productos = $empresa->productosCompraVenta()->get(); @endphp
             @if ($productos->count() == 0)
             <div class="alert alert-info">
                 <i class="fas fa-info-circle me-2"></i>No hay productos asociados
             </div>
             @else
             <div class="table-responsive">
                 <table class="table table-hover">
                     <thead>
                         <tr>
                             <th>Producto</th>
                             <th>Acciones</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($productos as $producto)
                         <tr>
                             <td>
                                 {{ $producto->producto->nombre }}: {{ VistaProductosUnificada::existenciaPorNombre($producto->producto->nombre) }}
                             </td>
                             <td>
                                 <a href="{{ route('productos_compra_venta.view', $producto->id) }}" class="btn btn-sm btn-view-product">
                                     <i class="fas fa-eye"></i>
                                 </a>
                             </td>
                         </tr>
                         @endforeach
                     </tbody>
                 </table>
             </div>
             @endif
             @endif
         </div>
     </div>
 </div>

 <link rel="stylesheet" href="{{ asset('css/estilosEmpresa.css') }}">
 <script src="{{ asset('js/empresa/empresa.js') }}"></script>

 @endsection

 @include('empresa.modal_insertar_empresa')
 @include('empresa.modalEditarEmpresa')
 @include('empresa.ubicaciones.modal_insertar_ubicacion')