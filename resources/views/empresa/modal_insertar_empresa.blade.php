<!-- MODAL INSERTAR EMPRESA -->
<div class="modal fade" id="modalInsertarEmpresa" tabindex="-1" aria-labelledby="modalInsertarEmpresaLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="formEmpresa" action="{{ route('empresa.insertar') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100 needs-validation" novalidate>
                @csrf
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px;">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="modalInsertarEmpresaLabel">
                                Agregar nueva Empresa {{ $tipo }}
                            </h5>
                            <small class="text-white-50">Ingresa la información fiscal y ubicación de la empresa</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control rounded-3" maxlength="80" required placeholder="Ej. Nao Proyectos Creativos" value="{{ old('nombre') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre Registrado (Razón Social) <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_registrado" class="form-control rounded-3" maxlength="150" required placeholder="Ej. Nao Proyectos Creativos S.A. de C.V." value="{{ old('nombre_registrado') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">RFC <span class="text-danger">*</span></label>
                            <input type="text" name="rfc" class="form-control rounded-3" maxlength="13" required placeholder="Ej. NPC210315AB1" style="text-transform: uppercase;" value="{{ old('rfc') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Empresa</label>
                            <input type="text" class="form-control rounded-3 text-capitalize bg-light" value="{{ $tipo }}" readonly disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección Completa <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" id="direccion" class="form-control rounded-3" required placeholder="Calle, número, colonia, código postal, estado..." value="{{ old('direccion') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Latitud</label>
                            <input type="number" step="any" name="latitud" id="latitud" class="form-control rounded-3" value="{{ old('latitud', '19.432608') }}" placeholder="19.432608">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Longitud</label>
                            <input type="number" step="any" name="longitud" id="longitud" class="form-control rounded-3" value="{{ old('longitud', '-99.133209') }}" placeholder="-99.133209">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                <span>Selecciona ubicación en el mapa</span>
                                <small class="text-muted fw-normal">Haz clic o arrastra el pin</small>
                            </label>
                            <div id="map" style="height: 240px; border-radius: 12px; border: 1px solid var(--border-color, #cbd5e1); overflow: hidden; background: #f1f5f9;"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fotografías o Fachada (opcional)</label>
                            <input type="file" name="fotografias[]" class="form-control rounded-3" multiple accept="image/*">
                            <small class="text-muted">Formatos soportados: JPG, PNG, WEBP.</small>
                        </div>
                    </div>

                    <input type="hidden" name="tipo" value="{{ $tipo }}">
                    <input type="hidden" name="id" id="empresa_id">
                </div>

                <div class="modal-footer p-3 border-top d-flex justify-content-between align-items-center" style="background: var(--bg-card, #ffffff);">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="fas fa-save"></i>
                        <span>Guardar Empresa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDSP99t6JcTm3KI_IXSDonht_Fg35tNmKM&callback=initMap&libraries=places" async defer></script>

<script>
    let map, marker;

    function initMap() {
        const mapEl = document.getElementById("map");
        if (!mapEl) return;

        const latInput = document.getElementById("latitud");
        const lngInput = document.getElementById("longitud");

        const initLat = parseFloat(latInput?.value) || 19.432608;
        const initLng = parseFloat(lngInput?.value) || -99.133209;
        const defaultPos = { lat: initLat, lng: initLng };

        try {
            map = new google.maps.Map(mapEl, {
                zoom: 13,
                center: defaultPos,
                mapTypeControl: false,
                streetViewControl: false
            });

            marker = new google.maps.Marker({
                position: defaultPos,
                map: map,
                draggable: true
            });

            google.maps.event.addListener(marker, 'dragend', function(event) {
                updateLatLngFields(event.latLng);
            });

            map.addListener('click', function(event) {
                marker.setPosition(event.latLng);
                updateLatLngFields(event.latLng);
            });
        } catch(e) {
            console.warn("Google Maps no se pudo inicializar:", e);
        }
    }

    function updateLatLngFields(latLng) {
        const latInput = document.getElementById("latitud");
        const lngInput = document.getElementById("longitud");
        if (latInput) latInput.value = (typeof latLng.lat === 'function') ? latLng.lat().toFixed(6) : Number(latLng.lat).toFixed(6);
        if (lngInput) lngInput.value = (typeof latLng.lng === 'function') ? latLng.lng().toFixed(6) : Number(latLng.lng).toFixed(6);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalInsertarEmpresa');
        if (modalEl) {
            // Asegurar que el modal esté en body para evitar problemas de backdrop
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            modalEl.addEventListener('shown.bs.modal', function () {
                const latInput = document.getElementById("latitud");
                const lngInput = document.getElementById("longitud");
                const curLat = parseFloat(latInput?.value) || 19.432608;
                const curLng = parseFloat(lngInput?.value) || -99.133209;
                const pos = { lat: curLat, lng: curLng };

                if (map) {
                    google.maps.event.trigger(map, 'resize');
                    map.setCenter(pos);
                    if (marker) marker.setPosition(pos);
                } else if (typeof google !== 'undefined' && google.maps) {
                    initMap();
                }
            });
        }
    });
</script>