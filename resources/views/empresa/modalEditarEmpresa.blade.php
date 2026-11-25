<!-- MODAL ACTUALIZAR EMPRESA -->
<div class="modal fade" id="modalActualizarEmpresa" tabindex="-1" aria-labelledby="modalActualizarEmpresaLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="formActualizarEmpresa" action="{{ route('empresa.actualizar') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column h-100 needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px;">
                            <i class="fas fa-edit fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="modalActualizarEmpresaLabel">
                                Actualizar Empresa
                            </h5>
                            <small class="text-white-50">Modifica los datos fiscales o de ubicación de la empresa</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" id="act_nombre" class="form-control rounded-3" maxlength="80" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre Registrado (Razón Social) <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_registrado" id="act_nombre_registrado" class="form-control rounded-3" maxlength="150" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">RFC <span class="text-danger">*</span></label>
                            <input type="text" name="rfc" id="act_rfc" class="form-control rounded-3" maxlength="13" required style="text-transform: uppercase;">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección Completa <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" id="act_direccion" class="form-control rounded-3" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Latitud</label>
                            <input type="number" step="any" name="latitud" id="act_latitud" class="form-control rounded-3">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Longitud</label>
                            <input type="number" step="any" name="longitud" id="act_longitud" class="form-control rounded-3">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold d-flex justify-content-between align-items-center mb-1">
                                <span>Selecciona ubicación en el mapa</span>
                                <small class="text-muted fw-normal">Haz clic o arrastra el pin</small>
                            </label>
                            <div id="mapActualizar" style="height: 240px; border-radius: 12px; border: 1px solid var(--border-color, #cbd5e1); overflow: hidden; background: #f1f5f9;"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fotografías adicionales (opcional)</label>
                            <input type="file" name="fotografias[]" class="form-control rounded-3" multiple accept="image/*">
                        </div>
                    </div>

                    <input type="hidden" name="tipo" id="act_tipo">
                    <input type="hidden" name="id" id="act_empresa_id">
                </div>

                <div class="modal-footer p-3 border-top d-flex justify-content-between align-items-center" style="background: var(--bg-card, #ffffff);">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="fas fa-save"></i>
                        <span>Actualizar Empresa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let mapAct, markerAct;

    function initMapActualizar(lat, lng) {
        const mapEl = document.getElementById("mapActualizar");
        if (!mapEl) return;

        const defaultPos = {
            lat: parseFloat(lat) || 19.432608,
            lng: parseFloat(lng) || -99.133209
        };

        try {
            mapAct = new google.maps.Map(mapEl, {
                zoom: 13,
                center: defaultPos,
                mapTypeControl: false,
                streetViewControl: false
            });

            markerAct = new google.maps.Marker({
                position: defaultPos,
                map: mapAct,
                draggable: true
            });

            google.maps.event.addListener(markerAct, 'dragend', function(event) {
                updateActLatLngFields(event.latLng);
            });

            mapAct.addListener('click', function(event) {
                markerAct.setPosition(event.latLng);
                updateActLatLngFields(event.latLng);
            });
        } catch(e) {
            console.warn("Map actualizacion no se pudo inicializar:", e);
        }
    }

    function updateActLatLngFields(latLng) {
        const latInput = document.getElementById("act_latitud");
        const lngInput = document.getElementById("act_longitud");
        if (latInput) latInput.value = (typeof latLng.lat === 'function') ? latLng.lat().toFixed(6) : Number(latLng.lat).toFixed(6);
        if (lngInput) lngInput.value = (typeof latLng.lng === 'function') ? latLng.lng().toFixed(6) : Number(latLng.lng).toFixed(6);
    }

    function updateMapActualizar(lat, lng) {
        const pos = {
            lat: parseFloat(lat) || 19.432608,
            lng: parseFloat(lng) || -99.133209
        };
        if (mapAct) {
            google.maps.event.trigger(mapAct, 'resize');
            mapAct.setCenter(pos);
            if (markerAct) markerAct.setPosition(pos);
        } else if (typeof google !== 'undefined' && google.maps) {
            initMapActualizar(pos.lat, pos.lng);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('modalActualizarEmpresa');
        if (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            modalEl.addEventListener('shown.bs.modal', function () {
                const lat = document.getElementById("act_latitud")?.value;
                const lng = document.getElementById("act_longitud")?.value;
                updateMapActualizar(lat, lng);
            });
        }

        // Delegación de clic para botones de editar
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-editar-empresa');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const nombre = btn.getAttribute('data-nombre');
            const nombreRegistrado = btn.getAttribute('data-nombre_registrado');
            const rfc = btn.getAttribute('data-rfc');
            const direccion = btn.getAttribute('data-direccion');
            const latitud = btn.getAttribute('data-latitud');
            const longitud = btn.getAttribute('data-longitud');
            const tipo = btn.getAttribute('data-tipo');

            const actId = document.getElementById('act_empresa_id');
            const actNom = document.getElementById('act_nombre');
            const actReg = document.getElementById('act_nombre_registrado');
            const actRfc = document.getElementById('act_rfc');
            const actDir = document.getElementById('act_direccion');
            const actLat = document.getElementById('act_latitud');
            const actLng = document.getElementById('act_longitud');
            const actTipo = document.getElementById('act_tipo');

            if (actId) actId.value = id || '';
            if (actNom) actNom.value = nombre || '';
            if (actReg) actReg.value = nombreRegistrado || '';
            if (actRfc) actRfc.value = rfc || '';
            if (actDir) actDir.value = direccion || '';
            if (actLat) actLat.value = latitud || '19.432608';
            if (actLng) actLng.value = longitud || '-99.133209';
            if (actTipo) actTipo.value = tipo || '';
        });
    });
</script>