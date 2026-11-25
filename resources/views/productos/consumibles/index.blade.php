@extends('layouts.navigation')
@section('title', 'Productos Consumibles')
<?php
$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas();
?>
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <div class="sgi-page-header d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mb-4 mb-md-5">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-boxes me-2"></i>Productos Consumibles
            </h1>
            <p class="lead text-muted mb-0">
                <i class="fas fa-info-circle me-2"></i>Inventario de productos de consumo interno
            </p>
        </div>
        <div class="sgi-header-actions d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary fs-6" id="badge-total-consumibles">
                <i class="fas fa-cubes me-1"></i> Total: {{ $productosConsumibles->total() }}
            </span>
            @if(tienePermiso('consumible - insertar'))
            <button id="btnInsertar" type="button" class="btn btn-primary btn-float"
                data-bs-toggle="modal" data-bs-target="#insertarProductoConsumibleModal">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Producto
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> Importar
            </button>
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-excel me-1"></i> Plantilla Consumibles
                </button>
                <ul class="dropdown-menu shadow dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('plantilla.consumibles') }}">
                            <i class="fas fa-file-excel text-success me-2"></i> Plantilla Estándar (Vacía)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('plantilla.consumibles.codigos') }}">
                            <i class="fas fa-barcode text-primary me-2"></i> Con 200 Códigos Prellenados
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="p-3 pb-0 d-flex flex-wrap justify-content-between align-items-center">
                    <form action="{{ route('imprimirConsumibles') }}" method="post" class="print-form mb-2">
                        @csrf
                        <input type="hidden" name="producto" value='@json($productosConsumiblesTodos)'>
                        <button type="submit" class="print-button">
                            <i class="fas fa-print me-1"></i> Imprimir Reporte
                        </button>
                    </form>
                </div>

                <form id="form-filtros" onsubmit="event.preventDefault(); fetchConsumibles();">
                    <div class="px-3 pb-3 d-flex flex-wrap align-items-center">
                        <strong class="me-2"><i class="fas fa-tags me-1"></i>Filtrar por categoría:</strong>
                        <input type="hidden" name="etiquetas[]" value="" />
                        @foreach($etiquetasUnicas as $etiqueta)
                        <input type="checkbox" class="btn-check filtro-api-consumible" name="etiquetas[]" value="{{ strtolower($etiqueta->nombre) }}" id="etiquetas-{{ $etiqueta->id }}" {{ in_array(strtolower($etiqueta->nombre), (array)request('etiquetas')) ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary me-2 mb-1" for="etiquetas-{{ $etiqueta->id }}">
                            {{ $etiqueta->nombre }}
                        </label>
                        @endforeach
                    </div>

                    @if(in_array(request('estado'), ['critico', 'criticos', 'bajo']) || request('critico') || request('stock') === 'critico')
                    <div class="mx-3 mb-3 alert alert-warning border-0 bg-warning bg-opacity-10 d-flex justify-content-between align-items-center py-2 px-3 rounded-3" id="banner-filtro-critico">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
                            <div>
                                <strong class="text-dark">Mostrando consumibles críticos (Stock ≤ 10 unidades)</strong>
                                <span class="text-muted small ms-2 d-none d-md-inline">Insumos que requieren reabastecimiento o compra inmediata.</span>
                            </div>
                        </div>
                        <a href="{{ route('productos_consumibles.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-times me-1"></i> Quitar filtro
                        </a>
                    </div>
                    @endif

                    <table class="table table-hover align-middle mb-0 sgi-mobile-cards">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th>Nombre</th>
                                <th>Empresa</th>
                                <th>Ubicación</th>
                                <th>Existencia</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                            <tr class="sgi-filter-row">
                                <th></th>
                                <th><input type="text" name="nombre" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar nombre..." value="{{ request('nombre') }}"></th>
                                <th><input type="text" name="empresa" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar empresa..." value="{{ request('empresa') }}"></th>
                                <th><input type="text" name="ubicacion" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar ubicación..." value="{{ request('ubicacion') }}"></th>
                                <th></th>
                                <th>
                                    <select name="estado" class="form-select form-select-sm filtro-api-consumible">
                                        <option value="">-- Todos --</option>
                                        <option value="critico" {{ in_array(request('estado'), ['critico', 'criticos', 'bajo']) || request('critico') || request('stock') === 'critico' ? 'selected' : '' }}>⚠️ Críticos (≤ 10)</option>
                                        <option value="agotado" {{ request('estado') == 'agotado' ? 'selected' : '' }}>🚫 Agotados (0)</option>
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>✅ Con stock (> 0)</option>
                                    </select>
                                </th>
                                <th><input type="date" name="fecha_registro" class="form-control form-control-sm filtro-api-consumible" value="{{ request('fecha_registro') }}" style="min-width: 130px;"></th>
                                <th><button type="button" class="btn btn-sm btn-primary" onclick="fetchConsumibles()"><i class="fas fa-search"></i><span class="d-md-none ms-1">Buscar</span></button></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-consumibles">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Cargando productos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <!-- Paginación Asíncrona API Consumibles -->
    <div class="sgi-pagination-bar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4" id="contenedor-paginacion-consumibles">
        <div class="text-muted" id="info-paginacion-consumibles">
            Mostrando {{ $productosConsumibles->firstItem() ?? 0 }} a {{ $productosConsumibles->lastItem() ?? 0 }} de {{ $productosConsumibles->total() ?? 0 }} registros
        </div>
        <nav aria-label="Navegación Consumibles por API" id="links-paginacion-consumibles">
            {{ $productosConsumibles->links('pagination::bootstrap-4') }}
        </nav>
    </div>
</div>

<script src="{{ asset('js/productos/consumible.js') }}"></script>
<script>
let debounceTimerConsumible;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filtro-api-consumible').forEach(el => {
        el.addEventListener('change', () => fetchConsumibles());
    });

    document.querySelectorAll('.filtro-api-consumible-text').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimerConsumible);
            debounceTimerConsumible = setTimeout(() => fetchConsumibles(), 300);
        });
    });

    // Cargar inicial
    fetchConsumibles();

    // Auto-abrir modal si viene con parametro crear
    if (new URLSearchParams(window.location.search).has('crear')) {
        setTimeout(function() {
            const btnInsertar = document.querySelector('[data-bs-target="#insertarProductoConsumibleModal"]');
            if (btnInsertar) {
                btnInsertar.click();
            } else {
                const modalEl = document.getElementById('insertarProductoConsumibleModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
            const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]crear=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
        }, 200);
    }
});

async function fetchConsumibles(page = 1) {
    const form = document.getElementById('form-filtros');
    const tbody = document.getElementById('tbody-consumibles');
    if (!form || !tbody) return;

    tbody.style.opacity = '0.4';

    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, val] of formData.entries()) {
        if (typeof val === 'string' && val.trim() !== '') {
            params.append(key, val.trim());
        }
    }

    // Sincronizar parámetro critico si venía en URL
    const urlParams = new URLSearchParams(window.location.search);
    if ((urlParams.get('critico') === '1' || urlParams.get('stock') === 'critico') && !params.has('estado')) {
        params.set('estado', 'critico');
        const selectEstado = form.querySelector('select[name="estado"]');
        if (selectEstado) selectEstado.value = 'critico';
    }

    params.set('page', page);

    const bannerCritico = document.getElementById('banner-filtro-critico');
    if (bannerCritico) {
        bannerCritico.style.display = (params.get('estado') === 'critico') ? 'flex' : 'none';
    }

    try {
        const res = await fetch("{{ route('api.productos.consumibles') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i><p class="mb-0 fw-semibold">${escapeHtmlConsumible(errJson?.message || 'Error al obtener consumibles (' + res.status + ')')}</p></td></tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaConsumibles(json.data, json.meta);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-warning"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">${escapeHtmlConsumible(json.message || 'No se encontraron resultados')}</p></td></tr>`;
        }
    } catch (e) {
        console.error('Error consultando API consumibles:', e);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-danger"><i class="fas fa-unlink fa-2x mb-2 d-block"></i><p class="mb-0">Error de conexión al cargar consumibles.</p></td></tr>`;
    } finally {
        tbody.style.opacity = '1';
    }
}

function renderTablaConsumibles(data, meta) {
    const tbody = document.getElementById('tbody-consumibles');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-3"></i>
                    <p class="mb-0">No se encontraron productos consumibles registrados</p>
                </td>
            </tr>`;
        renderPaginacionConsumibles(meta);
        return;
    }

    let html = '';
    const offset = (meta.current_page - 1) * meta.per_page;

    data.forEach((p, idx) => {
        const num = offset + idx + 1;
        const fecha = p.fecha_registro ? formatDate(p.fecha_registro) : 'N/A';
        const exist = Number(p.existencia ?? 0);

        let estadoHtml = '';
        let existenciaHtml = '';

        if (exist <= 0) {
            estadoHtml = '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"><i class="fas fa-ban me-1"></i>Agotado</span>';
            existenciaHtml = '<span class="badge bg-danger rounded-pill px-2 py-1 fw-bold">0</span>';
        } else if (exist <= 10) {
            estadoHtml = `<span class="badge rounded-pill bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1"><i class="fas fa-exclamation-triangle me-1 text-warning"></i>Crítico</span>`;
            existenciaHtml = `<span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-bold">${exist}</span>`;
        } else {
            estadoHtml = '<span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="fas fa-check me-1"></i>Activo</span>';
            existenciaHtml = `<span class="fw-semibold text-dark">${exist}</span>`;
        }

        html += `
            <tr class="hover-scale">
                <td class="ps-4 text-muted" data-label="#">${num}</td>
                <td data-label="Nombre"><strong>${escapeHtmlConsumible(p.nombre || 'Sin nombre')}</strong></td>
                <td data-label="Empresa">
                    <span class="badge bg-light text-dark border">
                        <i class="fas fa-building me-1 text-primary"></i> ${escapeHtmlConsumible(p.empresa_nombre || 'Sin empresa')}
                    </span>
                </td>
                <td data-label="Ubicación">
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-map-marker-alt me-1"></i> ${escapeHtmlConsumible(p.ubicacion || 'Sin ubicación')}
                    </span>
                </td>
                <td data-label="Existencia">${existenciaHtml}</td>
                <td data-label="Estado">${estadoHtml}</td>
                <td data-label="Fecha Registro"><span class="text-muted">${fecha}</span></td>
                <td class="pe-4 text-end" data-label="Acciones">
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark btn-float" 
                                onclick="abrirModalImprimirEtiqueta('${escapeHtmlConsumible(p.nombre || '')}', '${escapeHtmlConsumible(p.codigoBarra || '')}', '${escapeHtmlConsumible(p.empresa_nombre || 'SGI')}', '', '${p.precio !== null && p.precio !== undefined ? '$' + Number(p.precio).toFixed(2) : 'N/A'}', '${escapeHtmlConsumible(p.ubicacion || '')}')" 
                                data-bs-toggle="tooltip" title="Imprimir Etiqueta con Código de Barras">
                            <i class="fas fa-barcode"></i>
                        </button>
                        <a href="/productos-consumibles/${p.id}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    renderPaginacionConsumibles(meta);
}

function renderPaginacionConsumibles(meta) {
    const info = document.getElementById('info-paginacion-consumibles');
    const nav = document.getElementById('links-paginacion-consumibles');
    const totalBadge = document.getElementById('badge-total-consumibles');
    if (totalBadge && meta) {
        totalBadge.innerHTML = `<i class="fas fa-cubes me-1"></i> Total: ${meta.total}`;
    }
    if (!meta || !info || !nav) return;

    const first = (meta.current_page - 1) * meta.per_page + 1;
    const last = Math.min(meta.current_page * meta.per_page, meta.total);
    info.textContent = `Mostrando ${first} a ${last} de ${meta.total} registros`;

    let ul = '<ul class="pagination mb-0">';
    
    if (meta.current_page > 1) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchConsumibles(${meta.current_page - 1})"><i class="fas fa-angle-left"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>`;
    }

    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.current_page) {
            ul += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === meta.last_page || Math.abs(i - meta.current_page) <= 2) {
            ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchConsumibles(${i})">${i}</a></li>`;
        }
    }

    if (meta.current_page < meta.last_page) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchConsumibles(${meta.current_page + 1})"><i class="fas fa-angle-right"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>`;
    }

    ul += '</ul>';
    nav.innerHTML = ul;
}

function escapeHtmlConsumible(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeConsumible(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(str) {
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
@include('etiquetas.modalInsertarEtiqueta')
@include('productos.consumibles.modal.insertarProductoConsumibleModal')
@include('components.productos.modalImportarProductos', ['route' => route('importar.consumibles'), 'tipo' => 'consumibles'])
@endpush