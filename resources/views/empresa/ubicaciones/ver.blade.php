@extends('layouts.navigation')

@section('title', 'Ubicacion:' . $ubicacion->nombre)
<?php

use App\Models\VistaProductosUnificada;
?>
@section('content')

<div class="container main-content animate__animated animate__fadeIn">

    <!-- Header Section -->
    <div class="rounded-4 p-4 mb-5 shadow-sm company-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 fw-bold mb-2 company-title">
                    <i class="fas fa-map-marker-alt me-2"></i>Ubicación: {{ $ubicacion->nombre }}
                </h1>
                <p class="company-subtitle">Detalles completos de la ubicación</p>
            </div>
            <div class="d-flex gap-2">
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
                    <i class="fas fa-edit me-1"></i> Actualizar Ubicación
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
                        <p class="company-data">{{ $ubicacion->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <p class="company-data">{{ $ubicacion->nombre }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <p class="company-data">{{ $ubicacion->estado }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Clave</label>
                        <p class="company-data">{{ $ubicacion->clave }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Empresa a la que pertenece</label>
                       <p class="company-data"><a href="{{ url('empresa/view/' . $ubicacion->empresa->id) }} "
                                class="text-decoration-none text-primary"
                                title="Ver Empresa: {{ $ubicacion->empresa->nombre }}
                        ">{{ $ubicacion->empresa->nombre }} </a> </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm products-card">
        <div class="card-header bg-transparent py-3">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0 fw-bold">
                    Número de tipos de productos:
                    <span class="badge bg-primary">
                        {{$a = count(VistaProductosUnificada::obtenerActivosUbicacion($ubicacion->nombre)) }}
                    </span>
                </p>
                <p class="mb-0 fw-bold">
                    Total de productos:
                    <span class="badge bg-secondary">{{ VistaProductosUnificada::contarPorUbicacion($ubicacion->id) }}</span>

                </p>
            </div>

            <div class="accordion" id="productsAccordion">
                <div class="accordion-item mb-2 shadow-sm">
                    <h2 class="accordion-header" id="heading{{ $ubicacion->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $ubicacion->id }}" aria-expanded="false"
                            aria-controls="collapse{{ $ubicacion->id }}">
                            <i class="fas fa-map-marker-alt me-2" style="color: #20c997;"></i>{{ $ubicacion->nombre }}
                            <span class="badge bg-primary ms-2">{{ $a }}</span>
                        </button>
                    </h2>
                    <div id="collapse{{ $ubicacion->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading{{ $ubicacion->id }}" data-bs-parent="#productsAccordion">
                        <div class="accordion-body">
                            @php
                            $productos = VistaProductosUnificada::where('ubicacion_nombre', $ubicacion->nombre)
                            ->whereNotIn('estado', ['baja', 'inexistente'])
                            ->get();
                            @endphp
                            @if ($productos->count() == 0)
                            <div class="alert alert-info mb-0">
                                No hay productos en esta ubicación
                            </div>
@else
<div class="list-group list-group-flush">
    @foreach ($productos as $producto)
        @php
            // valor por defecto para evitar error
            $ruta = '#'; 

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
                    // podrías registrar un log o mostrar algo especial aquí
                    $ruta = '#';
                    break;
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
            </div>
        </div>
    </div>


</div>
<link rel="stylesheet" href="{{ asset('css/estilosEmpresa.css') }}">

@endsection
@include('empresa.ubicaciones.modal_actualizar_ubicacion')