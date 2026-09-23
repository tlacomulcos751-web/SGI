<!-- MODAL ACTUALIZAR EMPRESA -->
<div class="modal fade" id="modalActualizarEmpresa" tabindex="-1" aria-labelledby="modalActualizarEmpresaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <form id="formActualizarEmpresa" action="{{ route('empresa.actualizar') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="modalActualizarEmpresaLabel">
                        <i class="fas fa-building me-2"></i> Actualizar Empresa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" name="nombre" id="act_nombre" class="form-control" maxlength="80" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre Registrado</label>
                            <input type="text" name="nombre_registrado" id="act_nombre_registrado" class="form-control" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">RFC</label>
                            <input type="text" name="rfc" id="act_rfc" class="form-control" maxlength="13" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="act_direccion" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitud</label>
                            <input type="text" name="latitud" id="act_latitud" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitud</label>
                            <input type="text" name="longitud" id="act_longitud" class="form-control" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Selecciona ubicación en el mapa</label>
                            <div id="mapActualizar" style="height: 300px; border: 1px solid #ccc;"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Fotografías (puedes subir varias)</label>
                            <input type="file" name="fotografias[]" class="form-control" multiple accept="image/*">
                        </div>
                    </div>

                    <input type="hidden" name="tipo" id="act_tipo">
                    <input type="hidden" name="id" id="act_empresa_id">
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Carga de Google Maps para el modal de actualización -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBgyL_ppypQBBeCv67DvtqICwi8xcvvRWs&callback=initMapActualizar&libraries=places" async defer></script>
<script>
    $(document).on('click', '.btn-editar-empresa', function() {
        const button = $(this);

        // Obtener los datos del botón
        const id = button.data('id');
        const nombre = button.data('nombre');
        const nombreRegistrado = button.data('nombre_registrado');
        const rfc = button.data('rfc');
        const direccion = button.data('direccion');
        const latitud = button.data('latitud');
        const longitud = button.data('longitud');
        const tipo = button.data('tipo'); // si lo tienes
        const action = button.data('action');

        // Rellenar los campos del modal
        $('#act_empresa_id').val(id);
        $('#act_nombre').val(nombre);
        $('#act_nombre_registrado').val(nombreRegistrado);
        $('#act_rfc').val(rfc);
        $('#act_direccion').val(direccion);
        $('#act_latitud').val(latitud);
        $('#act_longitud').val(longitud);
        $('#act_tipo').val(tipo ?? '');

        // Actualizar el atributo `action` del formulario
        $('#formActualizarEmpresa').attr('action', action);

        // Si necesitas actualizar el mapa con la nueva lat/lng
        if (typeof updateMapActualizar === 'function') {
            updateMapActualizar(latitud, longitud);
        }
    });

    window.onload = function() {
        initMapActualizar();
    };
</script>