@extends('layouts.navigation')
@section('title', 'Registrar Vehículo')
@section('content')
<div class="container">
    <h2 class="mb-4" style="color: #322a5a; font-weight: 700;">
        <i class="bi bi-car-front-fill me-2" style="color: #cc5536;"></i>Registrar Vehículo
    </h2>

    <form action="{{ route('vehiculos.insertar') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        <div class="accordion mb-4" id="vehiculoAccordion">

            {{-- DATOS DEL VEHÍCULO --}}
            <div class="accordion-item" style="border-color: #4c61a9;">
                <h2 class="accordion-header" id="headingDatos">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDatos" aria-expanded="true" aria-controls="collapseDatos"
                        style="background: linear-gradient(135deg, #322a5a 0%, #4c61a9 100%); color: white;">
                        <i class="bi bi-card-checklist me-2"></i>Datos Generales
                    </button>
                </h2>
                <div id="collapseDatos" class="accordion-collapse collapse show" aria-labelledby="headingDatos" data-bs-parent="#vehiculoAccordion">
                    <div class="accordion-body row g-3">
                        @foreach ([
                        'tipo' => 'Ej. Camioneta',
                        'marca' => 'Ej. Toyota',
                        'modelo' => 'Ej. Hilux',
                        'version' => 'Ej. 2021',
                        'año' => 'Ej. 2021',
                        'placas' => 'Ej. ABC123',
                        'color' => 'Ej. Blanco',
                        'carroceria' => 'Ej. Pickup',
                        'combustible' => 'Ej. Diesel',
                        'transmision' => 'Ej. Automática'
                        ] as $field => $placeholder)
                        <div class="col-md-4">
                            <label class="form-label text-capitalize fw-semibold" style="color: #322a5a;">{{ $field }}</label>
                            <input type="text" name="{{ $field }}" class="form-control shadow-sm"
                                style="border-color: #6277b7;" required placeholder="{{ $placeholder }}">
                        </div>
                        @endforeach

                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="color: #322a5a;">Número de Identificación Vehicular</label>
                            <input type="text" name="niv" class="form-control shadow-sm"
                                style="border-color: #6277b7;" required placeholder="Ej. 1FTFW1ET1EFA12345">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color: #322a5a;">Ubicación</label>
                            <select name="ubicacion_id" class="form-select shadow-sm" style="border-color: #6277b7;" required>
                                <option value="">Seleccione...</option>
                                @foreach($ubicaciones as $ubicacion)
                                <option value="{{ $ubicacion->ubicacion_id }}">{{ $ubicacion->ubicacion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color: #322a5a;">Responsable</label>
                            <select name="responsable" class="form-select shadow-sm" style="border-color: #6277b7;" required>
                                <option value="">Seleccione...</option>
                                @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombreCompleto() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ESPECIFICACIONES --}}
            <div class="accordion-item" style="border-color: #dc934a;">
                <h2 class="accordion-header" id="headingEspecific">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEspecific" aria-expanded="false" aria-controls="collapseEspecific"
                        style="background: linear-gradient(135deg, #cc5536 0%, #e0b063 100%); color: white;">
                        <i class="bi bi-gear-fill me-2"></i>Especificaciones Técnicas
                    </button>
                </h2>
                <div id="collapseEspecific" class="accordion-collapse collapse" aria-labelledby="headingEspecific" data-bs-parent="#vehiculoAccordion">
                    <div class="accordion-body row g-3">
                        @foreach ([
                        'cilindraje' => 'Ej. 2755 cc',
                        'potencia' => 'Ej. 201 HP',
                        'torque' => 'Ej. 500 Nm',
                        'ejes' => 'Ej. 2',
                        'ruedas' => 'Ej. 4',
                        'capacidad_carga' => 'Ej. 1,000 kg',
                        'largo' => 'Ej. 5.3 m',
                        'ancho' => 'Ej. 1.8 m',
                        'alto' => 'Ej. 1.8 m'
                        ] as $field => $placeholder)
                        <div class="col-md-4">
                            <label class="form-label text-capitalize fw-semibold" style="color: #322a5a;">
                                {{ str_replace('_', ' ', $field) }}
                            </label>
                            <input type="number" name="especificaciones[{{ $field }}]" class="form-control shadow-sm"
                                style="border-color: #6277b7;" placeholder="{{ $placeholder }}" required min="0" step="any">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- DOCUMENTACIÓN --}}
            <div class="accordion-item" style="border-color: #383f8a;">
                <h2 class="accordion-header" id="headingDocs">
                    <button class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocs" aria-expanded="false" aria-controls="collapseDocs"
                        style="background: linear-gradient(135deg, #383f8a 0%, #6277b7 100%); color: white;">
                        <span><i class="bi bi-file-earmark-text-fill me-2"></i>Documentación</span>
                    </button>
                </h2>
                <div id="collapseDocs" class="accordion-collapse collapse" aria-labelledby="headingDocs" data-bs-parent="#vehiculoAccordion">
                    <div class="accordion-body" id="documentos-wrapper">
                        <div class="row g-3 documento-item">
                            @include('productos.vehiculos.documento', ['index' => 0])
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm" style="background-color: #e0b063; color: #242423;" onclick="addDocumento()">
                                <i class="bi bi-plus-circle me-1"></i>Agregar documento
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <button type="submit" class="btn btn-lg shadow mb-5" style="background: linear-gradient(135deg, #cc5536 0%, #e0b063 100%); color: white; border: none;">
            <i class="bi bi-save-fill me-2"></i>Guardar Vehículo
        </button>
    </form>
</div>

{{-- TEMPLATE JS --}}
<template id="documento-template">
    <div class="row g-3 documento-item mt-3 border-top pt-3" style="border-color: #6277b7 !important;">
        @include('productos.vehiculos.documento', ['index' => '__INDEX__'])
        <div class="col-12 text-end">
            <button type="button" class="btn btn-sm shadow-sm" style="background-color: #cc5536; color: white;" onclick="this.closest('.documento-item').remove()">
                <i class="bi bi-trash me-1"></i>Eliminar
            </button>
        </div>
    </div>
</template>

<script>
    let docIndex = 1;

    function addDocumento() {
        const template = document.getElementById('documento-template').innerHTML.replace(/__INDEX__/g, docIndex++);
        document.getElementById('documentos-wrapper').insertAdjacentHTML('beforeend', template);
    }

    // Auto-expansión de secciones al escribir
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.accordion-collapse input, .accordion-collapse select').forEach(el => {
            el.addEventListener('input', function () {
                const section = this.closest('.accordion-collapse');
                const collapse = bootstrap.Collapse.getOrCreateInstance(section);
                collapse.show();
            });
        });
    });
</script>

<!-- Bootstrap JS (necesario para collapse) -->
<!-- Estilos adicionales -->
<style>
    .form-control,
    .form-select {
        border-radius: 0.5rem;
        transition: all 0.3s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4c61a9;
        box-shadow: 0 0 0 0.25rem rgba(76, 97, 169, 0.25);
    }

    .card, .accordion-item {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }

    .accordion-button:not(.collapsed) {
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }

    .accordion-body {
        padding: 1.25rem;
    }

    body {
        background-color: #f8f9fa;
    }
</style>
@endsection
