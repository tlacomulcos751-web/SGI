@extends('layouts.navigation')
@section('title', 'Productos de Compra/Venta')
<?php
$etiquetasUnicas = \App\Models\Etiqueta::obtenerEtiquetasActivas();
?>
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
        <div class="mb-3 mb-md-0">
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-store me-2"></i>Productos de Compra/Venta
            </h1>
            <p class="lead text-muted">
                <i class="fas fa-info-circle me-2"></i>Inventario de productos disponibles para comercialización
            </p>
        </div>

        <div class="d-flex flex-column flex-md-row align-items-end align-items-md-center gap-2">
            <div class="d-flex gap-2">
                @if(tienePermiso('compra/venta - insertar'))
                <button id="btnInsertar" type="button" class="btn btn-primary"
                    data-bs-toggle="modal" data-bs-target="#insertarProductoCompraVentaModal">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Producto
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    Importar Productos
                </button>
                <a href="{{ route('plantilla.compraventa') }}" class="btn btn-primary">
                    Descargar Plantilla – Compra/Venta
                </a>
                @endif
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary fs-6" id="badge-total-compraventa">
                <i class="fas fa-cubes me-1"></i> Total: {{ $productosCompraVenta->total() }}
            </span>
        </div>
    </div>

    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <form id="form-filtros" onsubmit="event.preventDefault(); fetchCompraVenta();">
                    <div class="mb-4" style="padding: 20px;">
                        <form action="{{ route('imprimirCompraVenta') }}" method="post" class="print-form">
                            @csrf
                            <input type="hidden" name="producto" value='@json($productosCompraVentaTodos)'>
                            <button type="submit" class="print-button">
                                <i class="fas fa-print"></i> Imprimir Reporte
                            </button>
                        </form>
                        <strong class="me-2"><i class="fas fa-tags me-1"></i>Filtrar por categoría:</strong>
                        <input type="hidden" name="etiquetas[]" value="" />
                        @foreach($etiquetasUnicas as $etiqueta)
                        <input type="checkbox" class="btn-check filtro-api-cv" name="etiquetas[]" value="{{ strtolower($etiqueta->nombre) }}" id="etiquetas-{{ $etiqueta->id }}" {{ in_array(strtolower($etiqueta->nombre), (array)request('etiquetas')) ? 'checked' : '' }}>
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
                                <th>Empresa a vender</th>
                                <th>Ubicación</th>
                                <th>Existencia</th>
                                <th>Estado</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="text" name="nombre" class="form-control form-control-sm filtro-api-cv-text" placeholder="Buscar nombre..." value="{{ request('nombre') }}"></th>
                                <th><input type="text" name="empresa" class="form-control form-control-sm filtro-api-cv-text" placeholder="Buscar empresa..." value="{{ request('empresa') }}"></th>
                                <th><input type="text" name="ubicacion" class="form-control form-control-sm filtro-api-cv-text" placeholder="Buscar ubicación..." value="{{ request('ubicacion') }}"></th>
                                <th></th>
                                <th><input type="text" name="estado" class="form-control form-control-sm filtro-api-cv-text" placeholder="Estado..." value="{{ request('estado') }}"></th>
                                <th><input type="date" name="fechaEntrada" class="form-control form-control-sm filtro-api-cv" value="{{ request('fechaEntrada') }}" style="min-width: 130px;"></th>
                                <th><input type="date" name="fechaSalida" class="form-control form-control-sm filtro-api-cv" value="{{ request('fechaSalida') }}" style="min-width: 130px;"></th>
                                <th><button type="button" class="btn btn-sm btn-primary" onclick="fetchCompraVenta()"><i class="fas fa-search"></i></button></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-compraventa">
                                <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
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

    <!-- Paginación Asíncrona API Compra/Venta -->
    <div class="d-flex justify-content-between align-items-center mt-4" id="contenedor-paginacion-cv">
        <div class="text-muted" id="info-paginacion-cv">
            Mostrando {{ $productosCompraVenta->firstItem() ?? 0 }} a {{ $productosCompraVenta->lastItem() ?? 0 }} de {{ $productosCompraVenta->total() ?? 0 }} registros
        </div>
        <nav aria-label="Navegación Compra/Venta por API" id="links-paginacion-cv">
            {{ $productosCompraVenta->links('pagination::bootstrap-4') }}
        </nav>
    </div>
</div>

<script src="{{ asset('js/productos/cv.js') }}"></script>
<script>
let debounceTimerCV;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filtro-api-cv').forEach(el => {
        el.addEventListener('change', () => fetchCompraVenta());
    });

    document.querySelectorAll('.filtro-api-cv-text').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimerCV);
            debounceTimerCV = setTimeout(() => fetchCompraVenta(), 300);
        });
    });

    // Cargar inicial
    fetchCompraVenta();

    // Auto-abrir modal si viene con parametro crear
    if (new URLSearchParams(window.location.search).has('crear')) {
        setTimeout(function() {
            const btnInsertar = document.querySelector('[data-bs-target="#insertarProductoCompraVentaModal"]');
            if (btnInsertar) {
                btnInsertar.click();
            } else {
                const modalEl = document.getElementById('insertarProductoCompraVentaModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            }
            const cleanUrl = window.location.pathname + window.location.search.replace(/[?&]crear=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, cleanUrl || window.location.pathname);
        }, 200);
    }
});

async function fetchCompraVenta(page = 1) {
    const form = document.getElementById('form-filtros');
    const tbody = document.getElementById('tbody-compraventa');
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
        const res = await fetch("{{ route('api.productos.compraventa') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i><p class="mb-0 fw-semibold">${escapeHtmlCV(errJson?.message || 'Error al obtener productos (' + res.status + ')')}</p></td></tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaCompraVenta(json.data, json.meta);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-warning"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">${escapeHtmlCV(json.message || 'No se encontraron resultados')}</p></td></tr>`;
        }
    } catch (e) {
        console.error('Error consultando API compra/venta:', e);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-unlink fa-2x mb-2 d-block"></i><p class="mb-0">Error de conexión al cargar productos.</p></td></tr>`;
    } finally {
        tbody.style.opacity = '1';
    }
}

function renderTablaCompraVenta(data, meta) {
    const tbody = document.getElementById('tbody-compraventa');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-3"></i>
                    <p class="mb-0">No se encontraron productos de compra/venta registrados</p>
                </td>
            </tr>`;
        renderPaginacionCV(meta);
        return;
    }

    let html = '';
    const offset = (meta.current_page - 1) * meta.per_page;

    data.forEach((p, idx) => {
        const num = offset + idx + 1;
        const fechaE = p.fecha_entrada ? formatDateCV(p.fecha_entrada) : 'N/A';
        const fechaS = p.fecha_salida ? formatDateCV(p.fecha_salida) : '-';
        const estadoBadge = p.estado === 'activo' ? 'success' : 'secondary';

        html += `
            <tr class="hover-scale">
                <td class="ps-4 text-muted">${num}</td>
                <td><strong>${escapeHtmlCV(p.nombre || 'Sin nombre')}</strong></td>
                <td><span class="badge bg-info bg-opacity-10 text-info">${escapeHtmlCV(p.empresa || 'Sin empresa')}</span></td>
                <td>
                    <span class="badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-map-marker-alt me-1"></i> ${escapeHtmlCV(p.ubicacion || 'Sin ubicación')}
                    </span>
                </td>
                <td>${p.existencia || 0}</td>
                <td>
                    <span class="badge rounded-pill bg-${estadoBadge} bg-opacity-10 text-${estadoBadge}">
                        ${capitalizeCV(p.estado || 'desconocido')}
                    </span>
                </td>
                <td><span class="text-muted">${fechaE}</span></td>
                <td><span class="text-muted">${fechaS}</span></td>
                <td class="pe-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="/productos-compraventa/${p.id}" class="btn btn-sm btn-outline-primary btn-float" data-bs-toggle="tooltip" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    renderPaginacionCV(meta);
}

function renderPaginacionCV(meta) {
    const info = document.getElementById('info-paginacion-cv');
    const nav = document.getElementById('links-paginacion-cv');
    if (!meta || !info || !nav) return;

    const first = (meta.current_page - 1) * meta.per_page + 1;
    const last = Math.min(meta.current_page * meta.per_page, meta.total);
    info.textContent = `Mostrando ${first} a ${last} de ${meta.total} registros`;

    let ul = '<ul class="pagination mb-0">';
    
    if (meta.current_page > 1) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchCompraVenta(${meta.current_page - 1})"><i class="fas fa-angle-left"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>`;
    }

    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.current_page) {
            ul += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === meta.last_page || Math.abs(i - meta.current_page) <= 2) {
            ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchCompraVenta(${i})">${i}</a></li>`;
        }
    }

    if (meta.current_page < meta.last_page) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchCompraVenta(${meta.current_page + 1})"><i class="fas fa-angle-right"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>`;
    }

    ul += '</ul>';
    nav.innerHTML = ul;
}

function escapeHtmlCV(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeCV(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDateCV(str) {
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
@include('productos.compraVenta.modal.insertarProductoCompraVentaModal')
@include('components.productos.modalImportarProductos', ['route' => route('importar.compra.venta'), 'tipo' => 'compra-venta'])
@endpush