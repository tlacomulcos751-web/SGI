<!-- Modal de Confirmación con archivo -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEliminarVehiculo" action="{{route('vehiculos.eliminar', $vehiculo->id)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="textoConfirmacion"></p>
                    <div class="mb-3">
                        <label for="archivo_respaldo" class="form-label">Sube un archivo de respaldo (PDF, imagen, etc.)</label>
                        <input type="file" class="form-control" name="archivo_respaldo" id="archivo_respaldo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </div>
        </form>
    </div>
</div>