@php
use App\Models\VistaPermiso;
use Illuminate\Support\Str;

$permisos = $selectedRole ? VistaPermiso::where('rol', $selectedRole->id)->get() : collect();
@endphp

<div class="card shadow-sm">
    @isset($selectedRole)
    <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-0 fw-bold text-primary">
                    <i class="bi bi-person-badge-fill me-2"></i>{{ $selectedRole->nombre }}
                </h2>
                <small class="text-primary opacity-75">ID: {{ $selectedRole->id }}</small>
            </div>
            <div>
                @if(tienePermiso('modificarRol'))
                <button class="btn btn-sm btn-outline-primary me-2" onclick='abrirModalEditar(@json($selectedRole))'>
                    <i class="bi bi-pencil-square"></i> Editar
                </button>
                @endif
                @if(tienePermiso('desactivarRol'))
                <button class="btn btn-sm btn-outline-danger" onclick='abrirModalEliminar(@json($selectedRole))'>
                    <i class="bi bi-trash"></i> Eliminar
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="card-body bg-light bg-opacity-10">
        <div class="alert alert-info bg-info bg-opacity-10 border border-info border-opacity-25">
            <i class="bi bi-info-circle-fill me-2 text-info"></i>
            Administra los permisos para este rol. Los cambios se deben guardar manualmente.
        </div>

        <form action="{{ route('permisos.actualizar') }}" method="POST">
            @csrf
            <input type="hidden" name="rol_id" value="{{ old('rol_id', $selectedRole->id) }}">

            <div class="accordion" id="mainAccordion">

                {{-- Permisos Generales --}}
                @php
                $generales = $permisos->where('tipo', 'otros');
                $gruposGenerales = $generales->groupBy(fn($p) => explode(' ', formatCamelCase($p->permiso_nombre))[1] ?? 'Otros');
                @endphp

                @include('components.permisos.permiso-acordeon', [
                'id' => 'Generales',
                'color' => 'primary',
                'icon' => 'gear-fill',
                'titulo' => 'Permisos Generales',
                'grupos' => $gruposGenerales,
                'tituloGrupoIcono' => 'sliders',
                'labelCallback' => fn($permiso) => formatCamelCase($permiso->permiso_nombre)
                ])

                {{-- Permisos de Productos --}}
                @php
                $productos = $permisos->where('tipo', 'categoria');
                $gruposProductos = $productos->groupBy(fn($p) => trim(explode('-', $p->permiso_nombre)[0]));
                @endphp

                @include('components.permisos.permiso-acordeon', [
                'id' => 'Productos',
                'color' => 'success',
                'icon' => 'box-seam',
                'titulo' => 'Permisos de Productos',
                'grupos' => $gruposProductos,
                'tituloGrupoIcono' => 'tag',
                'labelCallback' => fn($permiso) => cleanAction($permiso->permiso_nombre)
                ])

                {{-- Permisos de Empresas y Ubicaciones --}}
                @php
                $empresas = $permisos->where('tipo', 'empresa')->groupBy(fn($p) => explode(' - ', $p->permiso_nombre)[0]);
                $ubicaciones = $permisos->where('tipo', 'ubicacion')->groupBy('empresa_ubicacion');
                @endphp

                @include('components.permisos.permiso-empresas', compact('empresas', 'ubicaciones'))

            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button class="btn btn-primary me-2">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="card-body text-center py-5 bg-light bg-opacity-10">
        <i class="bi bi-person-lines-fill display-4 text-muted mb-3"></i>
        <h3 class="h4 text-muted">Selecciona un rol</h3>
        <p class="text-muted">Elige un rol de la lista para ver y editar sus permisos</p>
    </div>
    @endisset
</div>