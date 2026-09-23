<!-- MODAL EMPRESA -->
<div class="modal fade" id="modalInsertarEmpresa" tabindex="-1" aria-labelledby="modalInsertarFijoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <form id="formEmpresa" action="{{ route('empresa.insertar') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title" id="modalInsertarFijoLabel">
                        <i class="fas fa-building me-2"></i> Agregar nueva Empresa {{ $tipo }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial*</label>
                            <input type="text" name="nombre" class="form-control" maxlength="80" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre Registrado*</label>
                            <input type="text" name="nombre_registrado" class="form-control" maxlength="100" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">RFC*</label>
                            <input type="text" name="rfc" class="form-control" maxlength="13" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Dirección*</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitud*</label>
                            <input type="text" name="latitud" id="latitud" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitud*</label>
                            <input type="text" name="longitud" id="longitud" class="form-control" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Selecciona ubicación en el mapa</label>
                            <div id="map" style="height: 300px; border: 1px solid #ccc;"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Fotografías (puedes subir varias)</label>
                            <input type="file" name="fotografias[]" class="form-control" multiple accept="image/*">
                        </div>
                    </div>

                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="hidden" name="id" id="empresa_id">
                </div>

                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDSP99t6JcTm3KI_IXSDonht_Fg35tNmKM&callback=initMap&libraries=places" async defer></script>

<script>
    let map, marker;

    function initMap() {
        const defaultPos = {
            lat: 19.432608,
            lng: -99.133209
        }; // CDMX por defecto
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: defaultPos,
        });

        marker = new google.maps.Marker({
            position: defaultPos,
            map,
            draggable: true
        });

        google.maps.event.addListener(marker, 'dragend', function(event) {
            updateLatLngFields(event.latLng);
        });

        map.addListener('click', function(event) {
            marker.setPosition(event.latLng);
            updateLatLngFields(event.latLng);
        });
    }

    function updateLatLngFields(latLng) {
        document.getElementById("latitud").value = latLng.lat().toFixed(6);
        document.getElementById("longitud").value = latLng.lng().toFixed(6);
    }
</script>