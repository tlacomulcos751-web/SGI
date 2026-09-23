@props([
'etiquetas',
'producto',
'todasLasEtiquetas',
'permiso',
'estado' => null,
'estadoRestringido' => null, // Ej: 'baja', 'vendido'
])

<div class="card border-0 shadow-lg overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-semibold text-primary">
            <i class="fas fa-tags me-2"></i>Etiquetas
        </h5>
    </div>

    <div class="card-body p-4">
        @if($etiquetas->count() > 0)
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($etiquetas as $etiquetaProducto)
            <form method="POST" action="{{ route('etiquetas.eliminar_producto') }}">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                <input type="hidden" name="clasificacion_id" value="{{ $etiquetaProducto->clasificacion_id }}">

                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill py-2 px-3 d-inline-flex align-items-center">
                    {{ $etiquetaProducto->etiqueta->nombre }}
                    @if (tienePermiso($permiso) && (!$estadoRestringido || $estado !== $estadoRestringido))
                    <button type="submit" class="btn btn-sm btn-link text-danger ms-2 p-0" title="Eliminar etiqueta">
                        <i class="fas fa-times"></i>
                    </button>
                    @endif
                </span>
            </form>
            @endforeach
        </div>
        @else
        <p class="text-muted mb-3">Este producto no tiene etiquetas asociadas</p>
        @endif

        @if (tienePermiso($permiso) && (!$estadoRestringido || $estado !== $estadoRestringido))
        <form method="POST" action="{{ route('etiquetas.agregar') }}" class="d-flex align-items-center gap-2">
            @csrf
            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
            <select name="clasificacion_id" class="form-select w-auto" required>
                <option value="">Agregar categoría...</option>
                @foreach($todasLasEtiquetas as $etiqueta)
                @if(!$etiquetas->contains('clasificacion_id', $etiqueta->id))
                <option value="{{ $etiqueta->id }}">{{ $etiqueta->nombre }}</option>
                @endif
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-success">
                <i class="fas fa-plus me-1"></i>Agregar
            </button>
            @if(tienePermiso('insertarEtiquetas') || esSuperAdmin())
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalInsertarEtiqueta">
                <i class="fas fa-plus me-1"></i> Nueva Categoría
            </button>
            @endif
        </form>
        @endif
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const modal = document.getElementById("modalInsertarEtiqueta"); // Reemplaza con el ID de tu modal

        modal.addEventListener("shown.bs.modal", function() {
            document.body.style.overflow = ""; // Quita el scroll

            // Mueve el foco al modal y hace scroll hacia él, pero no funciona muy bien
            modal.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        });

        modal.addEventListener("hidden.bs.modal", function() {
            document.body.style.overflow = ""; // Restaura el scroll cuando se cierra el modal
        });
    });
</script>