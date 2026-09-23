<div class="modal fade" id="modalCrearNotificacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('notificaciones_consumibles.store') }}">
            @csrf
            <input type="hidden" name="id_productos_consumibles" value="{{ $productoConsumible->id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear notificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_user" class="form-label">Usuario</label>
                        <select class="form-select" name="id_user" required>
                            @foreach($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->nombre }} {{ $usuario->apellido }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="limite" class="form-label">Límite de existencia</label>
                        <input type="number" class="form-control" name="limite" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </form>
    </div>
</div>