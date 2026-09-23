@extends('layouts.navigation')
@section('title', 'Productos Consumibles')
<?php
$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas();
?>
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-boxes me-2"></i>Productos Consumibles
            </h1>
            <p class="lead text-muted">
                <i class="fas fa-info-circle me-2"></i>Inventario de productos de consumo interno
            </p>
        </div>
        <div class="d-flex align-items-center">
            @if(tienePermiso('consumible - insertar'))
            <div class="d-flex gap-2">
                <button id="btnInsertar" type="button" class="btn btn-primary btn-float"
                    data-bs-toggle="modal" data-bs-target="#insertarProductoConsumibleModal">
                    <i class="fas fa-plus-circle me-2"></i> Nuevo Producto
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    Importar Productos
                </button>
                <a href="{{ route('plantilla.consumibles') }}" class="btn btn-primary">
                    Descargar Plantilla – Consumibles
                </a>
            </div>
            @endif
            <span class="badge bg-primary bg-opacity-10 text-primary fs-6 me-3" id="badge-total-consumibles">
                <i class="fas fa-cubes me-1"></i> Total: {{ $productosConsumibles->total() }}
            </span>
        </div>
    </div>

    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="mb-4" style="padding: 20px;">
                    <form action="{{ route('imprimirConsumibles') }}" method="post" class="print-form">
                        @csrf
                        <input type="hidden" name="producto" value='@json($productosConsumiblesTodos)'>
                        <button type="submit" class="print-button">
                            <i class="fas fa-print"></i> Imprimir Reporte
                        </button>
                    </form>
                    <form id="form-filtros" onsubmit="event.preventDefault(); fetchConsumibles();">
                        <strong class="me-2"><i class="fas fa-tags me-1"></i>Filtrar por categoría:</strong>
                        <input type="hidden" name="etiquetas[]" value="" />
                        @foreach($etiquetasUnicas as $etiqueta)
                        <input type="checkbox" class="btn-check filtro-api-consumible" name="etiquetas[]" value="{{ strtolower($etiqueta->nombre) }}" id="etiquetas-{{ $etiqueta->id }}" {{ in_array(strtolower($etiqueta->nombre), (array)request('etiquetas')) ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary me-2 mb-1" for="etiquetas-{{ $etiqueta->id }}">
                            {{ $etiqueta->nombre }}
                        </label>
                        @endforeach
                </div>
                <table class="table table-hover align-middle mb-0">
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
                        <tr>
                            <th></th>
                            <th><input type="text" name="nombre" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar nombre..." value="{{ request('nombre') }}"></th>
                            <th><input type="text" name="empresa" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar empresa..." value="{{ request('empresa') }}"></th>
                            <th><input type="text" name="ubicacion" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Buscar ubicación..." value="{{ request('ubicacion') }}"></th>
                            <th></th>
                            <th><input type="text" name="estado" class="form-control form-control-sm filtro-api-consumible-text" placeholder="Estado..." value="{{ request('estado') }}"></th>
                            <th><input type="date" name="fecha_registro" class="form-control form-control-sm filtro-api-consumible" value="{{ request('fecha_registro') }}" style="min-width: 130px;"></th>
                            <th><button type="button" class="btn btn-sm btn-primary" onclick="fetchConsumibles()"><i class="fas fa-search"></i></button></th>
                        </tr>
                    </thead>
                    </form>
                    <tbody id="tbody-consumibles">
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

    <!-- Paginación Asíncrona API Consumibles -->
    <div class="d-flex justify-content-between align-items-center mt-4" id="contenedor-paginacion-consumibles">
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
    params.set('page', page);

    try {
        const res = await fetch("{{ route('api.productos.consumibles') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i><p class="mb-0 fw-semibold">${escapeHtmlConsumible(errJson?.message || 'Error al obtener consumibles (' + res.status + ')')}</p></td></tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaConsumibles(json.data, json.meta);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-warning"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">${escapeHtmlConsumible(json.message || 'No se encontraron resultados')}</p></td></tr>`;
        }
    } catch (e) {
        console.error('Error consultando API consumibles:', e);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-unlink fa-2x mb-2 d-block"></i><p class="mb-0">Error de conexión al cargar consumibles.</p></td></tr>`;
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
        const estadoBadge = p.estado === 'activo' ? 'success' : 'secondary';

        html += `
            <tr class="hover-scale">
                <td class="ps-4 text-muted">${num}</td>
                <td><strong>${escapeHtmlConsumible(p.nombre || 'Sin nombre')}</strong></td>
                <td>
                    <span class="badge bg-light text-dark border">
                        <i class="fas fa-building me-1 text-primary"></i> ${escapeHtmlConsumible(p.empresa_nombre || 'Sin empresa')}
                    </span>
                </td>
                <td>
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-map-marker-alt me-1"></i> ${escapeHtmlConsumible(p.ubicacion || 'Sin ubicación')}
                    </span>
                </td>
                <td>${p.existencia || 0}</td>
                <td>
                    <span class="badge rounded-pill bg-${estadoBadge} bg-opacity-10 text-${estadoBadge}">
                        ${capitalizeConsumible(p.estado || 'desconocido')}
                    </span>
                </td>
                <td><span class="text-muted">${fecha}</span></td>
                <td class="pe-4 text-end">
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