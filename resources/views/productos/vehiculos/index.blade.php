@extends('layouts.navigation')
@section('title', 'Vehículos')

@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-car me-2"></i>Vehículos
            </h1>
            <p class="lead text-muted"><i class="fas fa-info-circle me-2"></i>Listado de vehículos registrados</p>
        </div>
        <div>
            @if(tienePermiso('vehiculo - insertar'))
            <a href="{{ route('vehiculos.crear') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Registrar Vehículo
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                Importar Productos
            </button>
            <a href="{{ route('plantilla.vehiculos') }}" class="btn btn-primary">
                Descargar Plantilla – Vehículos
            </a>
            @else
            <button class="btn btn-secondary" disabled>
                <i class="fas fa-plus me-2"></i>Registrar Vehículo
            </button>
            @endif
        </div>
    </div>

    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="p-3">
                    <form action="{{ route('imprimirVehiculos') }}" method="post" class="print-form d-inline-block">
                        @csrf
                        <input type="hidden" name="producto" value='@json($vehiculosTodos)'>
                        <button type="submit" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="fas fa-print me-1"></i> Imprimir Reporte
                        </button>
                    </form>
                    @if(request('responsable_id'))
                        <form action="{{ route('imprimirVehiculosResponsable') }}" method="POST" target="_blank" class="d-inline-block ms-2">
                            @csrf
                            <input type="hidden" name="responsable_id" value="{{ request('responsable_id') }}">
                            <button type="submit" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel me-1"></i> Imprimir Reporte del Responsable
                            </button>
                        </form>
                    @endif
                </div>

                <form id="form-filtros-vehiculos" onsubmit="event.preventDefault(); fetchVehiculos();">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Ubicación</th>
                                <th>Responsable</th>
                                <th>Año</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th><input type="text" name="marca" class="form-control form-control-sm filtro-api-vehiculo-text" placeholder="Marca..." value="{{ request('marca') }}"></th>
                                <th><input type="text" name="modelo" class="form-control form-control-sm filtro-api-vehiculo-text" placeholder="Modelo..." value="{{ request('modelo') }}"></th>
                                <th><input type="text" name="ubicacion" class="form-control form-control-sm filtro-api-vehiculo-text" placeholder="Ubicación..." value="{{ request('ubicacion') }}"></th>
                                <th>
                                    <select name="responsable_id" class="form-select form-select-sm filtro-api-vehiculo">
                                        <option value="">-- Todos --</option>
                                        @if(isset($usuarios))
                                            @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}" {{ request('responsable_id') == $usuario->id ? 'selected' : '' }}>{{ $usuario->nombre }} {{ $usuario->apellido }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </th>
                                <th><input type="text" name="año" class="form-control form-control-sm filtro-api-vehiculo-text" placeholder="Año..." value="{{ request('año') }}"></th>
                                <th></th>
                                <th class="text-end">
                                    <button type="button" class="btn btn-sm btn-primary" onclick="fetchVehiculos()"><i class="fas fa-search"></i></button>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tbody-vehiculos">
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    Cargando vehiculos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <!-- Paginación Asíncrona API Vehículos -->
    <div class="d-flex justify-content-between align-items-center mt-4" id="contenedor-paginacion-vehiculos">
        <div class="text-muted" id="info-paginacion-vehiculos">
            Mostrando {{ $vehiculos->firstItem() ?? 0 }} a {{ $vehiculos->lastItem() ?? 0 }} de {{ $vehiculos->total() ?? 0 }} registros
        </div>
        <nav aria-label="Navegación Vehículos por API" id="links-paginacion-vehiculos">
            {{ $vehiculos->links('pagination::bootstrap-4') }}
        </nav>
    </div>
</div>

<script>
let debounceTimerVehiculo;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filtro-api-vehiculo').forEach(el => {
        el.addEventListener('change', () => fetchVehiculos());
    });

    document.querySelectorAll('.filtro-api-vehiculo-text').forEach(el => {
        el.addEventListener('input', () => {
            clearTimeout(debounceTimerVehiculo);
            debounceTimerVehiculo = setTimeout(() => fetchVehiculos(), 300);
        });
    });

    // Cargar inicial
    fetchVehiculos();
});

async function fetchVehiculos(page = 1) {
    const form = document.getElementById('form-filtros-vehiculos');
    const tbody = document.getElementById('tbody-vehiculos');
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
        const res = await fetch("{{ route('api.vehiculos') }}?" + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!res.ok) {
            const errJson = await res.json().catch(() => null);
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning d-block"></i><p class="mb-0 fw-semibold">${escapeHtmlVehiculo(errJson?.message || 'Error al obtener vehículos (' + res.status + ')')}</p></td></tr>`;
            return;
        }

        const json = await res.json();

        if (json.success) {
            renderTablaVehiculos(json.data, json.meta);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-warning"><i class="fas fa-info-circle fa-2x mb-2 d-block"></i><p class="mb-0">${escapeHtmlVehiculo(json.message || 'No se encontraron resultados')}</p></td></tr>`;
        }
    } catch (e) {
        console.error('Error consultando API vehículos:', e);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-unlink fa-2x mb-2 d-block"></i><p class="mb-0">Error de conexión al cargar vehículos.</p></td></tr>`;
    } finally {
        tbody.style.opacity = '1';
    }
}

function renderTablaVehiculos(data, meta) {
    const tbody = document.getElementById('tbody-vehiculos');
    if (!tbody) return;

    if (!data || data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="fas fa-car fa-2x mb-3"></i>
                    <p class="mb-0">No hay vehículos registrados</p>
                </td>
            </tr>`;
        renderPaginacionVehiculos(meta);
        return;
    }

    let html = '';
    const offset = (meta.current_page - 1) * meta.per_page;

    data.forEach((v, idx) => {
        const num = offset + idx + 1;
        const estadoBadge = v.estado === 'activo' ? 'success' : 'secondary';

        html += `
            <tr class="hover-scale">
                <td>${num}</td>
                <td><strong>${escapeHtmlVehiculo(v.marca || '')}</strong></td>
                <td>${escapeHtmlVehiculo(v.modelo || '')}</td>
                <td>${escapeHtmlVehiculo(v.ubicacion_nombre || v.ubicacion || 'Sin ubicación')}</td>
                <td>${escapeHtmlVehiculo(v.responsable_nombre || 'Sin responsable')}</td>
                <td>${v.año || ''}</td>
                <td>
                    <span class="badge bg-${estadoBadge}">
                        ${capitalizeVehiculo(v.estado || 'activo')}
                    </span>
                </td>
                <td class="text-end">
                    <a href="/vehiculos/${v.id}/ver" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    renderPaginacionVehiculos(meta);
}

function renderPaginacionVehiculos(meta) {
    const info = document.getElementById('info-paginacion-vehiculos');
    const nav = document.getElementById('links-paginacion-vehiculos');
    if (!meta || !info || !nav) return;

    const first = (meta.current_page - 1) * meta.per_page + 1;
    const last = Math.min(meta.current_page * meta.per_page, meta.total);
    info.textContent = `Mostrando ${first} a ${last} de ${meta.total} registros`;

    let ul = '<ul class="pagination mb-0">';
    
    if (meta.current_page > 1) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchVehiculos(${meta.current_page - 1})"><i class="fas fa-angle-left"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>`;
    }

    for (let i = 1; i <= meta.last_page; i++) {
        if (i === meta.current_page) {
            ul += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (i === 1 || i === meta.last_page || Math.abs(i - meta.current_page) <= 2) {
            ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchVehiculos(${i})">${i}</a></li>`;
        }
    }

    if (meta.current_page < meta.last_page) {
        ul += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); fetchVehiculos(${meta.current_page + 1})"><i class="fas fa-angle-right"></i></a></li>`;
    } else {
        ul += `<li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>`;
    }

    ul += '</ul>';
    nav.innerHTML = ul;
}

function escapeHtmlVehiculo(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function capitalizeVehiculo(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>
@include('components.productos.modalImportarProductos', ['route' => route('importar.vehiculos'), 'tipo' => 'vehículos'])
@endsection