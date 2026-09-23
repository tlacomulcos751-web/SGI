@extends('layouts.navigation')
@section('title', 'Usuarios')
@section('content')
<div class="container main-content animate__animated animate__fadeIn">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="display-5 fw-bold text-gradient mb-2">
                <i class="fas fa-users me-2"></i>Usuarios
            </h1>
            <p class="lead text-muted">
                <i class="fas fa-info-circle me-2"></i>Gestión de usuarios registrados en el sistema
            </p>
        </div>
        @if(tienePermiso('insertarUsuarios'))
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
            <i class="fas fa-plus me-2"></i>Agregar Usuario
        </button>
        @endif
    </div>

    <div class="card glass-nav border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <form id="form-filtros" method="GET">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Nombre Completo</th>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="pe-4 text-end">Acciones</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>
                                    <input type="text" class="form-control form-control-sm" name="nombre" placeholder="Buscar nombre..." value="{{ request('nombre') }}">
                                </th>
                                <th>
                                    <input type="text" class="form-control form-control-sm" name="usuario" placeholder="Buscar usuario..." value="{{ request('usuario') }}">
                                </th>
                                <th>
                                    <input type="text" class="form-control form-control-sm" name="correo" placeholder="Buscar correo..." value="{{ request('correo') }}">
                                </th>
                                <th>
                                    <select name="rol" class="form-select form-select-sm">
                                        <option value="">-- Todos --</option>
                                        @foreach($roles as $rol)
                                        <option value="{{ $rol->nombre }}" {{ request('rol') == $rol->nombre ? 'selected' : '' }}>
                                            {{ $rol->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                </th>
                                <th>
                                    <select name="estado" class="form-select form-select-sm">
                                        <option value="">-- Todos --</option>
                                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </th>
                                <th>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $index => $usuario)
                            <tr class="hover-scale">
                                <td class="ps-4 text-muted">{{ ($usuarios->currentPage() - 1) * $usuarios->perPage() + $loop->iteration }}</td>
                                <td>{{ $usuario->nombreCompleto() }}</td>
                                <td>
                                    <span class="font-monospace">{{ $usuario->nombre_user }}</span>
                                </td>
                                <td>{{ $usuario->gmail }}</td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        {{ $usuario->rol->nombre ?? 'Sin rol' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $usuario->estado == 'activo' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $usuario->estado == 'activo' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($usuario->estado) }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('usuarios.ver', $usuario->id) }}" class="btn btn-sm btn-outline-primary btn-float" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-user-slash fa-2x mb-3"></i>
                                    <p class="mb-0">No se encontraron usuarios registrados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    @if($usuarios->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} registros
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                @if($usuarios->onFirstPage())
                <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-left"></i></span></li>
                @else
                <li class="page-item"><a class="page-link" href="{{ $usuarios->previousPageUrl() }}"><i class="fas fa-angle-left"></i></a></li>
                @endif

                @foreach($usuarios->getUrlRange(1, $usuarios->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $usuarios->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach

                @if($usuarios->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $usuarios->nextPageUrl() }}"><i class="fas fa-angle-right"></i></a></li>
                @else
                <li class="page-item disabled"><span class="page-link"><i class="fas fa-angle-right"></i></span></li>
                @endif
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection

@include('usuarios.modales.modalCrearUsuario')
