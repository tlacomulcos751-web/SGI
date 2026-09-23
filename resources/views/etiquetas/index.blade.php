@extends('layouts.navigation')
@section('title', 'Categorías del Sistema')
@section('content')

<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header Section -->
    <div class="card p-4 mb-4 shadow-sm border-0" style="border-top: 4px solid var(--primary) !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold text-gradient mb-1" style="font-family: 'Inter', sans-serif;">
                    <i class="fas fa-tags me-2 text-primary"></i> Categorías del Sistema
                </h1>
                <p class="text-muted mb-0">Gestión y control de categorías para clasificar activos fijos, consumibles y compras/ventas.</p>
            </div>
            @if (tienePermiso('insertarEtiquetas') || esSuperAdmin())
            <button type="button" class="btn btn-primary btn-float shadow-sm px-3 py-2" onclick="openModal('modalInsertarEtiqueta')">
                <i class="fas fa-plus-circle me-2"></i> Nueva Categoría
            </button>
            @endif
        </div>
    </div>

    <x-table>
        <x-slot name="head">
            <th class="ps-4 py-3 text-uppercase fw-semibold text-muted">#</th>
            <th class="py-3 text-uppercase fw-semibold text-muted">Nombre</th>
            <th class="py-3 text-uppercase fw-semibold text-muted">Tipo de Productos asociados</th>
            <th class="py-3 text-uppercase fw-semibold text-muted">No. de Productos</th>
            <th class="pe-4 py-3 text-end text-uppercase fw-semibold text-muted">Acciones</th>
        </x-slot>

        @foreach ($etiquetas as $index => $etiqueta)
        <tr class="hover-scale transition-all">
            <td class="ps-4 fw-medium text-primary">{{ $index + 1 }}</td>
            <td class="fw-semibold">{{ $etiqueta->nombre }}</td>
            <td>
                <x-badge type="primary">
                    {{ $etiqueta->productos->count() }} tipos de productos
                </x-badge>
            </td>
            <td>
                <x-badge type="info">
                    {{ $etiqueta->totalExistenciaProductos() }} unidades
                </x-badge>
            </td>
            <td class="pe-4 text-end">
                <div class="btn-group btn-group-sm shadow-sm">
                    @if (tienePermiso('leerEtiquetas'))
                    <a href="{{ url('etiquetas/' . $etiqueta->id . '/ver') }}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </a>
                    @endif

                    @if (tienePermiso('modificarEtiquetas'))
                    <button type="button"
                        class="btn btn-sm btn-outline-primary btn-editar-etiqueta"
                        data-id="{{ $etiqueta->id }}"
                        data-nombre="{{ $etiqueta->nombre }}"
                        onclick="abrirModalEditarEtiqueta(this)"
                        title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    @endif

                    @if (tienePermiso('desactivarEtiquetas'))
                    <button type="button"
                        class="btn btn-sm btn-outline-danger btn-eliminar-etiqueta"
                        data-id="{{ $etiqueta->id }}"
                        data-nombre="{{ $etiqueta->nombre }}"
                        onclick="abrirModalEliminarEtiqueta(this)"
                        title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
    </x-table>

    @if($etiquetas->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Mostrando {{ $etiquetas->firstItem() }} a {{ $etiquetas->lastItem() }} de {{ $etiquetas->total() }} registros
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if($etiquetas->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-left"></i></span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $etiquetas->previousPageUrl() }}"><i class="fas fa-angle-left"></i></a>
                </li>
                @endif

                @foreach($etiquetas->getUrlRange(1, $etiquetas->lastPage()) as $page => $url)
                @if($page == $etiquetas->currentPage())
                <li class="page-item active">
                    <span class="page-link">{{ $page }}</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endif
                @endforeach

                @if($etiquetas->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $etiquetas->nextPageUrl() }}"><i class="fas fa-angle-right"></i></a>
                </li>
                @else
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-right"></i></span>
                </li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
    <!-- Leftover inline styles removed -->

    @push('scripts')
    <script>
        function abrirModalEditarEtiqueta(btn) {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            
            document.getElementById('idEtiqueta_editar').value = id;
            document.getElementById('nombreEtiqueta_editar').value = nombre;
            
            // Si el form de editar necesita action dinámico:
            const form = document.getElementById('formEditarEtiqueta');
            if (form) {
                // actualizar action si es necesario
            }
            
            openModal('modalEditarEtiqueta');
        }

        function abrirModalEliminarEtiqueta(btn) {
            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            
            const labelNombre = document.getElementById('nombreEtiqueta_eliminar');
            if (labelNombre) labelNombre.textContent = nombre;
            
            const inputId = document.getElementById('idEtiqueta_eliminar');
            if (inputId) inputId.value = id;
            
            const form = document.getElementById('formEliminarEtiqueta');
            if (form) {
                form.action = "{{ route('etiquetas.desactivar') }}";
            }
            
            openModal('modalEliminarEtiqueta');
        }
    </script>
    @endpush
</div>
@endsection
@push('modals')
@include('etiquetas.modalInsertarEtiqueta')
@include('etiquetas.modalEditarEtiqueta')
@include('etiquetas.modalEliminarEtiqueta')
@endpush