@extends('layouts.navigation')
@section('title', 'Activos Fijos')
<?php
$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas(); // Suponiendo que tengas un modelo Etiqueta
?>
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-archive me-2"></i>Activos Fijos
            </h1>
            <p class="lead text-muted">
                <i class="fas fa-info-circle me-2"></i>Inventario de bienes duraderos que permanecen en la organización
            </p>
        </div>
        <div class="d-flex align-items-center">
            @if(tienePermiso('fijos - insertar'))
            <div class="d-flex gap-2">
                <button id="btnInsertar" type="button" class="btn btn-primary btn-float"
                    data-bs-toggle="modal" data-bs-target="#insertarProductoFijoModal">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Producto
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    Importar Productos
                </button>
                <a href="{{ route('plantilla.fijos') }}" class="btn btn-primary">
                    Descargar Plantilla – Fijos
                </a>
            </div>
            @endif
            <span class="badge bg-primary bg-opacity-10 text-primary fs-6 me-3">
                <i class="fas fa-cubes me-1"></i> Total: {{ $productosFijos->total() }}
            </span>
        </div>
    </div>
    <!-- Productos Table -->
    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form action="{{ route('imprimirFijos') }}" method="post" class="print-form">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-print"></i> Imprimir Reporte General
                            </button>
                        </form>
                        @if(request()->has('responsable_id') && request('responsable_id') != '')
                            <form action="{{ route('imprimirFijosResponsable') }}" method="POST" target="_blank">
                                @csrf
                                <input type="hidden" name="responsable_id" value="{{ request('responsable_id') }}">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-print"></i> Imprimir Reporte del Responsable
                                </button>
                            </form>
                        @endif
                    </div>
                    <form id="form-filtros" method="GET" class="mb-3">
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-6">
                                <label for="responsable_id" class="form-label"><strong>Filtrar por Responsable:</strong></label>
                                <select name="responsable_id" id="responsable_id" class="form-select">
                                    <option value="">-- Todos los responsables --</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ request('responsable_id') == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->nombre }} {{ $usuario->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if(!empty($etiquetasUnicas) && $etiquetasUnicas->count() > 0)
                        <div class="mb-3">
                            <strong class="me-2"><i class="fas fa-tags me-1"></i>Filtrar por categoría:</strong>
                            <input type="hidden" name="etiquetas[]" value="" />
                            @foreach($etiquetasUnicas as $etiqueta)
                            <input type="checkbox" class="btn-check" name="etiquetas[]" value="{{ strtolower($etiqueta->nombre) }}" id="etiquetas-{{ $etiqueta->id }}" {{ in_array(strtolower($etiqueta->nombre), (array)request('etiquetas')) ? 'checked' : '' }}>
                            <label class="btn btn-sm btn-outline-primary me-2 mb-1" for="etiquetas-{{ $etiqueta->id }}">
                                {{ $etiqueta->nombre }}
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </form>
                </div>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Fecha Entrada</th>
                            <th class="pe-4 text-end">Acciones</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" form="form-filtros" name="clave" id="claveInput" placeholder="Buscar clave..." value="{{ request('clave') }}">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="scanQR()"><i class="fas fa-qrcode"></i></button>
                            </th>
                            <th><input type="text" class="form-control form-control-sm" form="form-filtros" name="nombre" placeholder="Buscar nombre..." value="{{ request('nombre') }}"></th>
                            <th><input type="text" class="form-control form-control-sm" form="form-filtros" name="empresa" placeholder="Buscar empresa..." value="{{ request('empresa') }}"></th>
                            <th><input type="text" class="form-control form-control-sm" form="form-filtros" name="ubicacion" placeholder="Buscar ubicación..." value="{{ request('ubicacion') }}"></th>
                            <th><input type="text" class="form-control form-control-sm" form="form-filtros" name="estado" placeholder="Buscar estado..." value="{{ request('estado') }}"></th>
                            <th><input type="date" class="form-control form-control-sm" form="form-filtros" name="fechaEntrada" value="{{ request('fechaEntrada') }}" style="min-width: 130px;"></th>
                            <th class="text-end">
                                <button type="submit" form="form-filtros" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                            </th>
                        </tr>
                        <tr id="scannerRow" style="display: none;">
                            <td colspan="8">
                                <div id="qr-controls" class="mb-2 d-flex justify-content-between align-items-center" style="max-width: 300px;">
                                     <select id="cameraSelect" class="form-select form-select-sm w-75 me-2"></select>
                                     <button class="btn btn-sm btn-danger" onclick="stopQR()">Cancelar</button>
                                </div>
                                <div id="qr-reader" style="width: 300px;"></div>
                            </td>
                        </tr>
                    </thead>
                    <tbody id="fijosTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                Cargando productos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div id="fijosPaginationContainer">
        @include('productos.fijos.pagination')
    </div>
</div>

<script src="{{ asset('js/productos/fijos.js') }}"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript" defer></script>

<script>
let debounceTimerFijo;
const fijosApiUrl = "{{ route('api.productos.fijos') }}";

document.addEventListener('DOMContentLoaded', function() {
    const formFiltros = document.getElementById('form-filtros');

    // Escuchar tipeo en campos de texto (debounce 300ms)
    document.querySelectorAll('input[name="clave"], input[name="nombre"], input[name="empresa"], input[name="ubicacion"], input[name="estado"], input[name="fechaEntrada"]').forEach(el => {
        el.addEventListener('input', function() {
            clearTimeout(debounceTimerFijo);
            debounceTimerFijo = setTimeout(() => fetchFijosApi(), 300);
        });
    });

    const respSelect = document.getElementById('responsable_id');
    if (respSelect) {
        respSelect.addEventListener('change', function() {
            fetchFijosApi();
        });
    }

    document.querySelectorAll('input[name="etiquetas[]"]').forEach(chk => {
        chk.addEventListener('change', function() {
            fetchFijosApi();
        });
    });

    if (formFiltros) {
        formFiltros.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchFijosApi();
        });
    }

    // Cargar inicial
    fetchFijosApi();

    // Auto-abrir modal si viene con parametro crear
    if (new URLSearchParams(window.location.search).has('crear')) {
        setTimeout(function() {
            const btnInsertar = document.getElementById('btnInsertar');
            if (btnInsertar) {
                btnInsertar.click();
            } else {
                const modalEl = document.getElementById('insertarProductoFijoModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
            const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]crear=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
        }, 200);
    }
});

async function fetchFijosApi(page = 1) {
    const formFiltros = document.getElementById('form-filtros');
    const tableBody = document.getElementById('fijosTableBody');
    if (!tableBody) return;

    tableBody.style.opacity = '0.4';

    const params = new URLSearchParams();
    if (formFiltros) {
        const formData = new FormData(formFiltros);
        for (const [key, val] of formData.entries()) {
            if (typeof val === 'string' && val.trim() !== '') {
                params.append(key, val.trim());
            }
        }
    }
    params.set('page', page);

    try {
        const res = await fetch(fijosApiUrl + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i>
                        <p class="mb-0 fw-semibold">${escapeHtmlFijo(errJson?.message || 'Error al obtener productos (' + res.status + ')')}</p>
                    </td>
                </tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaFijos(json.data, json.meta);
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-warning">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        <p class="mb-0">${escapeHtmlFijo(json.message || 'No se encontraron resultados')}</p>
                    </td>
                </tr>`;
        }
    } catch (e) {
        console.error('Error consultando API fijos:', e);
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                    <i class="fas fa-unlink fa-2x mb-2 d-block"></i>
                    <p class="mb-0">Error de conexión al cargar productos fijos.</p>
                </td>
            </tr>`;
    } finally {
        tableBody.style.opacity = '1';
    }
}

function renderTablaFijos(data, meta) {
    const tableBody = document.getElementById('fijosTableBody');
    if (!tableBody) return;

    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-3"></i>
                    <p class="mb-0">No se encontraron Activos Fijos registrados</p>
                </td>
            </tr>`;
        renderPaginacionFijos(meta);
        return;
    }

    let html = '';
    const offset = ((meta?.current_page || 1) - 1) * (meta?.per_page || 15);

    data.forEach((p, idx) => {
        const num = offset + idx + 1;
        const fecha = p.fecha_entrada ? formatDateFijo(p.fecha_entrada) : 'N/A';
        const estadoBadge = p.estado === 'activo' ? 'success' : 'secondary';

        html += `
            <tr class="hover-scale">
                <td class="ps-4 text-muted">${num}</td>
                <td>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace">
                        ${escapeHtmlFijo(p.clave || '')}
                    </span>
                </td>
                <td><strong>${escapeHtmlFijo(p.nombre || 'Sin nombre')}</strong></td>
                <td>
                    <span class="badge bg-light text-dark border">
                        <i class="fas fa-building me-1 text-primary"></i> ${escapeHtmlFijo(p.empresa_nombre || 'Sin empresa')}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-map-marker-alt me-1"></i> ${escapeHtmlFijo(p.ubicacion || 'Sin ubicación')}
                    </span>
                </td>
                <td>
                    <span class="badge rounded-pill bg-${estadoBadge} bg-opacity-10 text-${estadoBadge}">
                        ${capitalizeFijo(p.estado || 'desconocido')}
                    </span>
                </td>
                <td><span class="text-muted">${fecha}</span></td>
                <td class="pe-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/responsivas/fijo/${p.id}" target="_blank" class="btn btn-sm btn-outline-info btn-float text-info" data-bs-toggle="tooltip" title="Generar Carta Responsiva (PDF)">
                            <i class="fas fa-file-signature"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-dark btn-float" 
                                onclick="abrirModalImprimirEtiqueta('${escapeHtmlFijo(p.nombre || '')}', '${escapeHtmlFijo(p.codigoBarra || p.clave || '')}', '${escapeHtmlFijo(p.empresa_nombre || 'SGI')}', '${escapeHtmlFijo(p.clave || '')}', '${p.precio !== null && p.precio !== undefined ? '$' + Number(p.precio).toFixed(2) : 'N/A'}', '${escapeHtmlFijo(p.ubicacion || '')}')" 
                                data-bs-toggle="tooltip" title="Imprimir Etiqueta con Código de Barras">
                            <i class="fas fa-barcode"></i>
                        </button>
                        <a href="/productos-fijos/${p.id}/qr" class="btn btn-sm btn-outline-success btn-float" data-bs-toggle="tooltip" title="Ver/Descargar QR">
                            <i class="fas fa-qrcode"></i>
                        </a>
                        <a href="/productos-fijos/${p.id}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
    renderPaginacionFijos(meta);
}

function renderPaginacionFijos(meta) {
    const paginationContainer = document.getElementById('fijosPaginationContainer');
    if (!meta || !paginationContainer) return;

    const first = (meta.current_page - 1) * meta.per_page + 1;
    const last = Math.min(meta.current_page * meta.per_page, meta.total);

    let html = `
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Mostrando ${first} a ${last} de ${meta.total} registros
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0">`;

    if (meta.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchFijosApi(${meta.current_page - 1})"><i class="fas fa-angle-left"></i></a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>`;
    }

    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === meta.last_page || Math.abs(i - meta.current_page) <= 2) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchFijosApi(${i})">${i}</a></li>`;
        }
    }

    if (meta.current_page < meta.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchFijosApi(${meta.current_page + 1})"><i class="fas fa-angle-right"></i></a></li>`;
    } else {
        html += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>`;
    }

    html += `
            </ul>
        </nav>
    </div>`;

    paginationContainer.innerHTML = html;
}

function escapeHtmlFijo(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeFijo(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDateFijo(str) {
    try {
        const d = new Date(str);
        if (isNaN(d)) return str;
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch(e) {
        return str;
    }
}
</script>
@endsection

@push('modals')
@include('productos.fijos.modal_insertar_fijo')
@include('etiquetas.modalInsertarEtiqueta')
@include('components.productos.modalImportarProductos', ['route' => route('importar.fijos'), 'tipo' => 'fijos'])
@endpush