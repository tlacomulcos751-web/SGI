@extends('layouts.navigation')
@section('title', 'Productos - general')
@section('content')

<div class="container main-content animate__animated animate__fadeIn">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-boxes me-2"></i>Nuestros Productos
            </h1>
            <p class="lead text-muted">
                <i class="fas fa-info-circle me-2"></i>Gestión completa del inventario de productos
            </p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge bg-primary bg-opacity-10 text-primary fs-6 me-3" id="badge-total-existencias">
                <i class="fas fa-cubes me-1"></i> Total: {{ $totalExistencias ?? 0 }}
            </span>
            <a href="{{ route('productos.imprimir_por_responsable', request()->all()) }}" target="_blank" class="btn btn-outline-secondary shadow-sm">
                <i class="fas fa-print me-2"></i>Imprimir Reporte
            </a>
            @if(request('responsable_id'))
                <form action="{{ route('imprimirGeneralResponsable') }}" method="POST" target="_blank" class="ms-2 mb-0">
                    @csrf
                    <input type="hidden" name="responsable_id" value="{{ request('responsable_id') }}">
                    <button type="submit" class="btn btn-success shadow-sm">
                        <i class="fas fa-file-excel me-2"></i> Imprimir Reporte del Responsable
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Error Display -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Productos Table -->
    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <form id="form-filtros" onsubmit="event.preventDefault(); fetchProductos();">
                    <!-- Filtros de etiquetas -->
                    @if(!empty($etiquetasUnicas) && $etiquetasUnicas->count() > 0)
                    <div class="mb-4" style="padding: 20px;">
                        <strong class="me-2"><i class="fas fa-tags me-1"></i>Filtrar por categoría:</strong>
                        @foreach($etiquetasUnicas as $etiqueta)
                        <input type="checkbox" 
                               class="btn-check filtro-api" 
                               name="etiquetas[]" 
                               value="{{ strtolower($etiqueta->nombre) }}" 
                               id="etiqueta-{{ $etiqueta->id }}" 
                               {{ in_array(strtolower($etiqueta->nombre), (array)request('etiquetas', [])) ? 'checked' : '' }}>
                        <label class="btn btn-sm btn-outline-primary me-2 mb-1" for="etiqueta-{{ $etiqueta->id }}">
                            {{ $etiqueta->nombre }}
                        </label>
                        @endforeach
                        
                        <button type="button" id="btn-limpiar-filtros" class="btn btn-sm btn-outline-secondary ms-2" onclick="limpiarFiltrosApi()">
                            <i class="fas fa-times me-1"></i>Limpiar filtros
                        </button>
                    </div>
                    @endif

                    <table class="table table-hover align-middle" id="tabla-productos">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 60px;">#</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Existencia</th>
                                <th>Ubicación</th>
                                <th>Empresa</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>
                                    <input type="text" 
                                           class="form-control form-control-sm filtro-api-text" 
                                           name="nombre" 
                                           placeholder="Buscar nombre..." 
                                           value="{{ request('nombre') }}">
                                </th>
                                <th>
                                    <input type="number" 
                                           step="0.01"
                                           class="form-control form-control-sm filtro-api-text" 
                                           name="precio" 
                                           placeholder="Buscar precio..." 
                                           value="{{ request('precio') }}">
                                </th>
                                <th>
                                    <input type="number" 
                                           class="form-control form-control-sm filtro-api-text" 
                                           name="existencia" 
                                           placeholder="Buscar existencia..." 
                                           value="{{ request('existencia') }}">
                                </th>
                                <th>
                                    <input type="text" 
                                           class="form-control form-control-sm filtro-api-text" 
                                           name="ubicacion" 
                                           placeholder="Buscar ubicación..." 
                                           value="{{ request('ubicacion') }}">
                                </th>
                                <th>
                                    <input type="text" 
                                           class="form-control form-control-sm filtro-api-text" 
                                           name="empresa" 
                                           placeholder="Buscar empresa..." 
                                           value="{{ request('empresa') }}">
                                </th>
                                <th>
                                    <select name="responsable_id" id="responsable_id" class="form-select form-select-sm filtro-api">
                                        <option value="">-- Todos --</option>
                                        @if(isset($usuarios))
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ request('responsable_id') == $usuario->id ? 'selected' : '' }}>{{ $usuario->nombre }} {{ $usuario->apellido }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </th>
                                <th>
                                    <select class="form-select form-select-sm filtro-api" name="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                        <option value="disponible" {{ request('estado') == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                        <option value="inexistente" {{ request('estado') == 'inexistente' ? 'selected' : '' }}>Inexistente</option>
                                    </select>
                                </th>
                                <th>
                                    <select class="form-select form-select-sm filtro-api" name="tipo">
                                        <option value="">Todos los tipos</option>
                                        <option value="consumible" {{ request('tipo') == 'consumible' ? 'selected' : '' }}>Consumible</option>
                                        <option value="fijo" {{ request('tipo') == 'fijo' ? 'selected' : '' }}>Fijo</option>
                                        <option value="compraventa" {{ request('tipo') == 'compraventa' ? 'selected' : '' }}>Compraventa</option>
                                    </select>
                                </th>
                                <th class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary me-1" onclick="fetchProductos()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbody-productos">
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
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

    <!-- Paginación Asíncrona API -->
    <div class="d-flex justify-content-between align-items-center mt-4" id="contenedor-paginacion">
        <div class="text-muted" id="info-paginacion">
            Mostrando {{ $productos->firstItem() ?? 0 }} a {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() ?? 0 }} registros
        </div>
        <nav aria-label="Navegación por API" id="links-paginacion">
            {{ $productos->links('pagination::bootstrap-4') }}
        </nav>
    </div>
</div>

<script>
let debounceTimer;

document.addEventListener('DOMContentLoaded', function () {
    // Escuchar cambios en selects y checkboxes (filtro instantáneo)
    document.querySelectorAll('.filtro-api').forEach(el => {
        el.addEventListener('change', () => fetchProductos());
    });

    // Escuchar tipeo en campos de texto (debounce 300ms)
    document.querySelectorAll('.filtro-api-text').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchProductos(), 300);
        });
    });

    // Cargar productos iniciales
    fetchProductos();
});

//función para limpiar filtros
function limpiarFiltros() {
    const form = document.getElementById('form-filtros');
    if (form) {
        form.reset();
        fetchProductos();
    }
}
//Filtros de la API de productos
async function fetchProductos(page = 1) {
    const form = document.getElementById('form-filtros');
    const tbody = document.getElementById('tbody-productos');
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
        const res = await fetch("{{ route('api.productos') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i><p class="mb-0 fw-semibold">${escapeHtml(errJson?.message || 'Error al obtener productos (' + res.status + ')')}</p></td></tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaProductos(json.data, json.meta);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-warning"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">${escapeHtml(json.message || 'No se encontraron resultados')}</p></td></tr>`;
        }
    } catch (e) {
        console.error('Error consultando API de productos:', e);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-unlink fa-2x mb-2 d-block"></i><p class="mb-0">Error de conexión al cargar productos.</p></td></tr>`;
    } finally {
        tbody.style.opacity = '1';
    }
}

function renderTablaProductos(data, meta) {
    const tbody = document.getElementById('tbody-productos');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-3"></i>
                    <p class="mb-0">No se encontraron productos con los filtros seleccionados</p>
                </td>
            </tr>`;
        renderPaginacion(meta);
        return;
    }

    let html = '';
    const offset = (meta.current_page - 1) * meta.per_page;

    data.forEach((p, idx) => {
        const num = offset + idx + 1;
        const precioHtml = (p.precio !== null && p.precio !== '' && !isNaN(p.precio)) 
            ? `<span class="text-success fw-bold">$${parseFloat(p.precio).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`
            : `<span class="badge bg-secondary bg-opacity-10 text-secondary border">N/A</span>`;
        const badgeExistencia = (p.existencia || 0) > 0 ? 'success' : 'warning';
        const estadoBadge = (p.estado == 'activo' || p.estado == 'disponible') ? 'primary' : 'secondary';

        html += `
            <tr class="hover-scale">
                <td class="ps-4 text-muted">${num}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="fas fa-box text-primary"></i>
                        </div>
                        <div>
                            <strong class="d-block">${escapeHtml(p.nombre || 'Sin nombre')}</strong>
                        </div>
                    </div>
                </td>
                <td class="text-end">${precioHtml}</td>
                <td class="text-center">
                    <span class="badge rounded-pill bg-${badgeExistencia} bg-opacity-10 text-${badgeExistencia}">
                        ${p.existencia || 0}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill bg-info bg-opacity-10 text-info">
                        ${escapeHtml(p.ubicacion_nombre || 'Sin ubicación')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary">
                        ${escapeHtml(p.empresa_nombre || 'Sin empresa')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill bg-dark bg-opacity-10 text-dark d-block">
                        ${escapeHtml(p.responsable_nombre || '')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge rounded-pill bg-${estadoBadge} bg-opacity-10 text-${estadoBadge}">
                        ${capitalize(p.estado || 'desconocido')}
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-info bg-opacity-10 text-info">
                        ${capitalize(p.tipo || 'desconocido')}
                    </span>
                </td>
                <td class="pe-4 text-end">
                    <button type="button" class="btn btn-sm btn-outline-dark btn-float" 
                            onclick="abrirModalImprimirEtiqueta('${escapeHtml(p.nombre || '')}', '${escapeHtml(p.codigoBarra || '')}', '${escapeHtml(p.empresa_nombre || 'SGI')}', '', '${p.precio !== null && p.precio !== '' && !isNaN(p.precio) ? '$' + parseFloat(p.precio).toFixed(2) : 'N/A'}', '${escapeHtml(p.ubicacion_nombre || '')}')" 
                            data-bs-toggle="tooltip" title="Imprimir Etiqueta con Código de Barras">
                        <i class="fas fa-barcode"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    renderPaginacion(meta);
}

function renderPaginacion(meta) {
    const info = document.getElementById('info-paginacion');
    const nav = document.getElementById('links-paginacion');
    if (!meta || !info || !nav) return;

    const first = (meta.current_page - 1) * meta.per_page + 1;
    const last = Math.min(meta.current_page * meta.per_page, meta.total);
    info.textContent = `Mostrando ${first} a ${last} de ${meta.total} registros`;

    let ul = '<ul class="pagination mb-0">';
    
    // Anterior
    if (meta.current_page > 1) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchProductos(${meta.current_page - 1})"><i class="fas fa-angle-left"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>`;
    }

    // Páginas
    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.current_page) {
            ul += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === meta.last_page || Math.abs(i - meta.current_page) <= 2) {
            ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchProductos(${i})">${i}</a></li>`;
        }
    }

    // Siguiente
    if (meta.current_page < meta.last_page) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchProductos(${meta.current_page + 1})"><i class="fas fa-angle-right"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>`;
    }

    ul += '</ul>';
    nav.innerHTML = ul;
}

function limpiarFiltrosApi() {
    const form = document.getElementById('form-filtros');
    if (!form) return;
    form.reset();
    form.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
    fetchProductos(1);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>
</div>

@endsection