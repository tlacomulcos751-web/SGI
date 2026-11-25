<div class="modal fade" id="actualizarInfoModal" tabindex="-1" aria-labelledby="actualizarInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('productos_fijos.actualizarInfo', $productoFijo->id) }}" class="modal-content border-0 shadow-lg needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            
            <!-- Encabezado con gradiente azul -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-edit fs-6 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fs-5 fw-bold text-white mb-0" id="actualizarInfoModalLabel">
                            Editar Información del Activo Fijo
                        </h5>
                        <small class="text-white text-opacity-75 font-monospace">Clave: {{ $productoFijo->clave }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo del modal -->
            <div class="modal-body p-4 bg-light bg-opacity-50">
                <div class="row g-4">
                    <!-- Sección 1: Información Básica -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Información Básica
                                </h6>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <!-- Nombre -->
                                <div class="mb-3">
                                    <label for="act_nombre" class="form-label fw-medium">Nombre del Producto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg shadow-xs" id="act_nombre" name="nombre"
                                        value="{{ old('nombre', $producto->nombre) }}" maxlength="150" required
                                        placeholder="Ej: Monitor LED 24''">
                                    <div class="invalid-feedback">Por favor ingrese un nombre válido</div>
                                </div>

                                <!-- Descripción -->
                                <div class="mb-3">
                                    <label for="act_descripcion" class="form-label fw-medium">Descripción</label>
                                    <textarea class="form-control shadow-xs" id="act_descripcion" name="descripcion"
                                        rows="2" placeholder="Descripción detallada del producto">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                </div>

                                <!-- Código de Barras -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="act_codigoBarra" class="form-label fw-medium mb-0">Código de Barras</label>
                                        <span class="text-muted small">Opcional / Autogenerable</span>
                                    </div>
                                    <div class="input-group shadow-xs">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                                        <input type="text" class="form-control" id="act_codigoBarra" name="codigoBarra"
                                            value="{{ old('codigoBarra', $producto->codigoBarra) }}" maxlength="50"
                                            placeholder="Escanear con pistola o autogenerar..."
                                            oninput="renderBarcodeVisual('#barcode-preview-fijo-edit', this.value); document.getElementById('preview-box-fijo-edit').classList.toggle('d-none', !this.value.trim());">
                                        <button class="btn btn-outline-primary" type="button" 
                                            onclick="generarCodigoBarra('act_codigoBarra', 'barcode-preview-fijo-edit'); document.getElementById('preview-box-fijo-edit').classList.remove('d-none');" 
                                            title="Autogenerar código de barras único">
                                            <i class="fas fa-magic me-1"></i> Generar
                                        </button>
                                    </div>
                                    <div id="preview-box-fijo-edit" class="barcode-auto-preview mt-2 p-2 bg-white rounded border text-center {{ $producto->codigoBarra ? '' : 'd-none' }} shadow-xs">
                                        <small class="text-muted d-block mb-1">Previsualización del código:</small>
                                        <svg id="barcode-preview-fijo-edit" class="img-fluid" style="max-height: 45px;"></svg>
                                    </div>
                                </div>

                                <!-- Empresa Interna -->
                                <div class="mb-3">
                                    <label for="act_empresa_id" class="form-label fw-medium">Empresa Interna <span class="text-danger">*</span></label>
                                    <select class="form-select shadow-xs" name="empresa_id" id="act_empresa_id" required onchange="filtrarUbicacionesPorEmpresa(this.value)">
                                        <option value="" disabled>Seleccione la empresa interna...</option>
                                        @php
                                            $empresaActualId = $producto->ubicacion?->empresa_id ?? null;
                                        @endphp
                                        @foreach($empresasInternas as $empresa)
                                        <option value="{{ $empresa->id }}" @selected($empresaActualId == $empresa->id)>
                                            {{ $empresa->nombre }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione la empresa dueña del producto</div>
                                </div>

                                <!-- Ubicación -->
                                <div class="mb-3">
                                    <label for="act_ubicacion_id" class="form-label fw-medium">Ubicación <span class="text-danger">*</span></label>
                                    <select class="form-select shadow-xs" name="ubicacion_id" id="act_ubicacion_id" required onchange="sincronizarEmpresaPorUbicacion(this)">
                                        <option value="" disabled>Seleccione una ubicación...</option>
                                        @foreach($ubicaciones as $ubicacion)
                                        @if($ubicacion->puede_insertar_ubicacion || $ubicacion->puede_leer_ubicacion)
                                        <option value="{{ $ubicacion->ubicacion_id }}" 
                                                data-empresa-id="{{ $ubicacion->ubicacion?->empresa_id }}"
                                                @selected($producto->ubicacion_id == $ubicacion->ubicacion_id)>
                                            {{ $ubicacion->ubicacion?->nombre }} ({{ $ubicacion->ubicacion?->empresa?->nombre ?? 'Sin empresa' }})
                                        </option>
                                        @endif
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione una ubicación válida</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Detalles del Producto -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-tags me-2"></i>Detalles del Producto
                                </h6>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <div class="row g-3">
                                    <!-- Precio -->
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="act_precio" class="form-label mb-0 fw-medium">Precio ($)</label>
                                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" role="switch" id="no_aplica_precio_edit" 
                                                       {{ is_null($producto->precio) ? 'checked' : '' }}
                                                       onchange="toggleNoAplicaPrecioEdit(this, 'act_precio')"
                                                       style="cursor: pointer; width: 2.2em; height: 1.1em;">
                                                <label class="form-check-label small fw-semibold text-secondary user-select-none" for="no_aplica_precio_edit" style="cursor: pointer;">No aplica (N.A.)</label>
                                            </div>
                                        </div>
                                        <div class="input-group shadow-xs">
                                            <span class="input-group-text bg-light fw-bold {{ is_null($producto->precio) ? 'opacity-50' : '' }}" id="addon-precio-edit">$</span>
                                            <input type="number" step="0.01" min="0" max="999999.99" class="form-control {{ is_null($producto->precio) ? 'bg-light text-muted' : '' }}" id="act_precio"
                                                name="precio" value="{{ $producto->precio }}" {{ is_null($producto->precio) ? 'disabled' : '' }}
                                                placeholder="{{ is_null($producto->precio) ? 'N.A. (No aplica)' : '0.00' }}">
                                        </div>
                                        <div id="hint-na-precio-edit" class="form-text text-muted small {{ is_null($producto->precio) ? '' : 'd-none' }}">
                                            <i class="fas fa-info-circle me-1 text-primary"></i>Este artículo no tiene costo asignado (N.A.).
                                        </div>
                                    </div>

                                    <!-- Cantidad -->
                                    <div class="col-md-6">
                                        <label for="act_cantidad_real" class="form-label fw-medium">Cantidad <span class="text-danger">*</span></label>
                                        <div class="input-group shadow-xs">
                                            <select class="form-select" id="act_cantidad_select" onchange="document.getElementById('act_cantidad_real').value = this.value">
                                                @for($i = 1; $i <= 50; $i++)
                                                    <option value="{{ $i }}" @selected(($cantidadTotal ?? 1) == $i)>{{ $i }}</option>
                                                @endfor
                                            </select>
                                            <input type="number" class="form-control d-none" id="act_cantidad_input" placeholder="Ej. 100" min="1" max="10000" oninput="document.getElementById('act_cantidad_real').value = this.value" value="{{ $cantidadTotal ?? 1 }}">
                                            <button type="button" class="btn btn-outline-secondary" id="act_btn_toggle_cantidad" onclick="toggleCantidadModeEdit()" title="Escribir cantidad manual">
                                                <i class="fas fa-keyboard"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="cantidad" id="act_cantidad_real" value="{{ $cantidadTotal ?? 1 }}">
                                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Existencia actual de este producto fijo</small>
                                    </div>

                                    <!-- Fecha de Entrada -->
                                    <div class="col-md-6">
                                        <label for="act_fechaEntrada" class="form-label fw-medium">Fecha de Entrada <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control shadow-xs" id="act_fechaEntrada" name="fechaEntrada" required value="{{ old('fechaEntrada', $productoFijo->fechaEntrada ?? date('Y-m-d')) }}">
                                        <div class="invalid-feedback">Por favor seleccione la fecha de entrada</div>
                                    </div>

                                    <!-- Calidad / Estado de Conservación -->
                                    <div class="col-md-6">
                                        <label for="act_calidad" class="form-label fw-medium">Estado de Conservación <span class="text-danger">*</span></label>
                                        <select class="form-select shadow-xs" name="calidad" id="act_calidad" required>
                                            <option value="bueno" @selected($productoFijo->calidad === 'bueno')>Bueno</option>
                                            <option value="regular" @selected($productoFijo->calidad === 'regular')>Regular</option>
                                            <option value="malo" @selected($productoFijo->calidad === 'malo')>Malo</option>
                                        </select>
                                    </div>

                                    <!-- Responsable -->
                                    <div class="col-12">
                                        <label for="act_responsable" class="form-label fw-medium">Responsable <span class="text-danger">*</span></label>
                                        <select class="form-select shadow-xs" name="responsable" id="act_responsable" required>
                                            <option value="" disabled>Seleccione un responsable...</option>
                                            @foreach($usuarios as $usuario)
                                            <option value="{{ $usuario->id }}" @selected($productoFijo->responsable == $usuario->id)>
                                                {{ $usuario->nombre }} {{ $usuario->apellido }} ({{ $usuario->rol?->nombre ?? 'Usuario' }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione un responsable</div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="col-12">
                                        <label for="act_estado" class="form-label fw-medium">Estado del Producto <span class="text-danger">*</span></label>
                                        <select class="form-select shadow-xs" id="act_estado" name="estado" required onchange="toggleDocumentoBaja(this.value)">
                                            <option value="activo" @selected($productoFijo->estado === 'activo')>Activo</option>
                                            <option value="reparacion" @selected($productoFijo->estado === 'reparacion')>En Reparación</option>
                                            @if(tienePermiso('fijos - desactivar') || esSuperAdmin())
                                            <option value="baja" @selected($productoFijo->estado === 'baja')>Dado de Baja</option>
                                            @endif
                                        </select>
                                    </div>

                                    <!-- Documento de baja (condicional) -->
                                    <div class="col-12" id="act_documentoBajaField" style="display: {{ $productoFijo->estado === 'baja' ? 'block' : 'none' }};">
                                        <div class="card border-danger bg-danger bg-opacity-10 shadow-xs">
                                            <div class="card-body p-3">
                                                <h6 class="card-title text-danger fw-semibold mb-1">
                                                    <i class="fas fa-file-upload me-2"></i>Documento de Respaldo para Baja
                                                </h6>
                                                <input type="file" class="form-control mt-2 shadow-xs" name="documento_baja" accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="text-muted">Formatos: PDF, JPG, PNG (Máx: 5MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Multimedia / Fotografías -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-images me-2"></i>Multimedia
                                </h6>
                                @php $totalFotos = count(array_filter(explode(',', $productoFijo->fotografias ?? ''))); @endphp
                                <span class="badge bg-light text-muted border">{{ $totalFotos }} foto(s) guardadas</span>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Subir Nuevas Fotografías (Máx. 5MB c/u)</label>
                                    <input type="file" name="fotografias[]" class="form-control shadow-xs" multiple accept="image/*" id="act_fotografias" onchange="previewNuevasFotos(this)">
                                    <small class="text-muted">Puedes seleccionar múltiples imágenes adicionales</small>
                                    <div id="act_preview_images" class="preview-images mt-2 d-flex flex-wrap gap-2"></div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-medium">Tomar Fotografía con Cámara</label>
                                    <div class="mb-2">
                                        <select id="act_cameraSelector" class="form-select form-select-sm mb-2 shadow-xs"></select>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="act_btnIniciarCamara" onclick="iniciarCamaraEdit()">
                                            <i class="fas fa-camera me-1"></i> Usar Cámara
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success d-none" id="act_btnCapturarFoto" onclick="capturarFotoEdit()">
                                            <i class="fas fa-circle-dot me-1"></i> Capturar Foto
                                        </button>
                                    </div>
                                    <video id="act_videoPreview" width="100%" height="180" autoplay playsinline style="display:none;" class="mt-2 border rounded shadow-xs bg-dark"></video>
                                    <canvas id="act_canvasFoto" style="display: none;"></canvas>
                                    <input type="hidden" name="capturaCamara" id="act_capturaCamara">
                                    <div id="act_fotoCapturadaPreview" class="mt-2 d-none">
                                        <small class="text-success fw-semibold d-block mb-1"><i class="fas fa-check-circle me-1"></i>Foto capturada lista para guardar:</small>
                                        <img id="act_imgCapturada" src="" class="rounded border shadow-xs" style="max-height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 4: Categorías -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-tags me-2"></i>Categorías
                                </h6>
                                @if(tienePermiso('insertarEtiquetas') || esSuperAdmin())
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInsertarEtiqueta">
                                    <i class="fas fa-plus me-1"></i> Nueva Categoría
                                </button>
                                @endif
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <p class="text-muted small mb-2">Selecciona las categorías aplicables para este activo:</p>
                                @php
                                    $etiquetasAsignadas = $etiquetas->pluck('clasificacion_id')->toArray();
                                @endphp
                                <div class="d-flex flex-wrap gap-2" style="max-height: 220px; overflow-y: auto;">
                                    @foreach($todasLasEtiquetas as $etiqueta)
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input d-none" type="checkbox" name="etiquetas[]" 
                                               value="{{ $etiqueta->id }}" id="edit-etiqueta-{{ $etiqueta->id }}"
                                               @checked(in_array($etiqueta->id, $etiquetasAsignadas))>
                                        <label class="form-check-label badge p-2 user-select-none cursor-pointer border transition-all" 
                                               for="edit-etiqueta-{{ $etiqueta->id }}"
                                               style="cursor: pointer;"
                                               id="lbl-etiqueta-{{ $etiqueta->id }}">
                                            <i class="fas fa-tag me-1"></i> {{ $etiqueta->nombre }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 5: Comentarios -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-comment me-2"></i>Comentarios / Observaciones
                                </h6>
                            </div>
                            <div class="card-body p-3 p-md-4">
                                <textarea class="form-control shadow-xs" id="act_comentario" name="comentario" rows="2" 
                                    placeholder="Ingrese cualquier observación adicional sobre esta actualización...">{{ old('comentario', $productoFijo->comentario) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie del modal -->
            <div class="modal-footer bg-white border-top py-3">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-2" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="submit" class="btn btn-primary px-4 rounded-2 shadow-sm">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .shadow-xs {
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .preview-images img {
        max-width: 80px;
        max-height: 80px;
        border-radius: 4px;
        object-fit: cover;
    }
    .form-check-input:checked + label {
        background-color: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
    }
    .form-check-input:not(:checked) + label {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border-color: #e2e8f0 !important;
    }
</style>

<script>
    let isCantidadManualEdit = false;
    let cameraStreamEdit = null;

    function toggleCantidadModeEdit() {
        const selectEl = document.getElementById('act_cantidad_select');
        const inputEl = document.getElementById('act_cantidad_input');
        const btnToggle = document.getElementById('act_btn_toggle_cantidad');
        const realInput = document.getElementById('act_cantidad_real');

        isCantidadManualEdit = !isCantidadManualEdit;

        if (isCantidadManualEdit) {
            selectEl.classList.add('d-none');
            inputEl.classList.remove('d-none');
            btnToggle.innerHTML = '<i class="fas fa-list"></i>';
            btnToggle.title = "Elegir cantidad de la lista";
            inputEl.value = realInput.value;
            inputEl.focus();
        } else {
            inputEl.classList.add('d-none');
            selectEl.classList.remove('d-none');
            btnToggle.innerHTML = '<i class="fas fa-keyboard"></i>';
            btnToggle.title = "Escribir cantidad manual";
            const val = parseInt(inputEl.value);
            if (val >= 1 && val <= 50) {
                selectEl.value = val;
                realInput.value = val;
            } else {
                selectEl.value = '1';
                realInput.value = '1';
            }
        }
    }

    function toggleNoAplicaPrecioEdit(checkbox, inputId) {
        const input = document.getElementById(inputId);
        const addon = document.getElementById('addon-precio-edit');
        const hint = document.getElementById('hint-na-precio-edit');
        if (!input) return;
        if (checkbox.checked) {
            input.dataset.oldValue = input.value;
            input.value = '';
            input.disabled = true;
            input.placeholder = 'N.A. (No aplica)';
            input.classList.add('bg-light', 'text-muted');
            if (addon) addon.classList.add('opacity-50');
            if (hint) hint.classList.remove('d-none');
        } else {
            input.disabled = false;
            input.placeholder = '0.00';
            input.value = input.dataset.oldValue || '0.00';
            input.classList.remove('bg-light', 'text-muted');
            if (addon) addon.classList.remove('opacity-50');
            if (hint) hint.classList.add('d-none');
            input.focus();
        }
    }

    function toggleDocumentoBaja(estado) {
        const docField = document.getElementById('act_documentoBajaField');
        if (docField) {
            docField.style.display = (estado === 'baja') ? 'block' : 'none';
        }
    }

    function filtrarUbicacionesPorEmpresa(empresaId) {
        const selectUbicacion = document.getElementById('act_ubicacion_id');
        if (!selectUbicacion) return;
        const options = selectUbicacion.querySelectorAll('option[data-empresa-id]');
        let selectedStillValid = false;

        options.forEach(opt => {
            const empId = opt.getAttribute('data-empresa-id');
            if (!empresaId || empId == empresaId) {
                opt.style.display = '';
                opt.disabled = false;
                if (opt.selected) selectedStillValid = true;
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
                if (opt.selected) opt.selected = false;
            }
        });

        if (!selectedStillValid) {
            for (let opt of options) {
                if (!opt.disabled) {
                    opt.selected = true;
                    break;
                }
            }
        }
    }

    function sincronizarEmpresaPorUbicacion(selectUbicacion) {
        const selectedOpt = selectUbicacion.options[selectUbicacion.selectedIndex];
        if (!selectedOpt) return;
        const empId = selectedOpt.getAttribute('data-empresa-id');
        const selectEmpresa = document.getElementById('act_empresa_id');
        if (empId && selectEmpresa && selectEmpresa.value != empId) {
            selectEmpresa.value = empId;
            // No filtrar de nuevo para no resetear la ubicación recién elegida
        }
    }

    function previewNuevasFotos(input) {
        const previewContainer = document.getElementById('act_preview_images');
        previewContainer.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'border rounded shadow-xs';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // Cámara en Modal Editar
    async function listarCamarasEdit() {
        const selector = document.getElementById('act_cameraSelector');
        if (!selector || !navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return;
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            selector.innerHTML = '';
            const videoDevices = devices.filter(d => d.kind === 'videoinput');
            videoDevices.forEach((device, index) => {
                const opt = document.createElement('option');
                opt.value = device.deviceId;
                opt.text = device.label || `Cámara ${index + 1}`;
                selector.appendChild(opt);
            });
        } catch (err) {
            console.error('Error al enumerar cámaras:', err);
        }
    }

    async function iniciarCamaraEdit() {
        const video = document.getElementById('act_videoPreview');
        const selector = document.getElementById('act_cameraSelector');
        const btnCapturar = document.getElementById('act_btnCapturarFoto');
        const btnIniciar = document.getElementById('act_btnIniciarCamara');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Tu navegador no soporta el acceso a la cámara.');
            return;
        }

        try {
            if (cameraStreamEdit) {
                cameraStreamEdit.getTracks().forEach(t => t.stop());
            }

            const constraints = {
                video: selector.value ? { deviceId: { exact: selector.value } } : true
            };

            cameraStreamEdit = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = cameraStreamEdit;
            video.style.display = 'block';
            btnCapturar.classList.remove('d-none');
            btnIniciar.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Reiniciar Cámara';
            listarCamarasEdit();
        } catch (err) {
            alert('No se pudo acceder a la cámara: ' + err.message);
        }
    }

    function capturarFotoEdit() {
        const video = document.getElementById('act_videoPreview');
        const canvas = document.getElementById('act_canvasFoto');
        const inputHidden = document.getElementById('act_capturaCamara');
        const imgPreview = document.getElementById('act_imgCapturada');
        const previewBox = document.getElementById('act_fotoCapturadaPreview');

        if (!video || !canvas) return;

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        inputHidden.value = dataUrl;
        imgPreview.src = dataUrl;
        previewBox.classList.remove('d-none');

        // Detener cámara tras capturar
        if (cameraStreamEdit) {
            cameraStreamEdit.getTracks().forEach(t => t.stop());
            cameraStreamEdit = null;
        }
        video.style.display = 'none';
        document.getElementById('act_btnCapturarFoto').classList.add('d-none');
        document.getElementById('act_btnIniciarCamara').innerHTML = '<i class="fas fa-camera me-1"></i> Tomar Otra Foto';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('actualizarInfoModal');
        if (modalEl) {
            modalEl.addEventListener('show.bs.modal', function() {
                const selectEmpresa = document.getElementById('act_empresa_id');
                if (selectEmpresa && selectEmpresa.value) {
                    filtrarUbicacionesPorEmpresa(selectEmpresa.value);
                }
                listarCamarasEdit();
                const codigoBarra = document.getElementById('act_codigoBarra');
                if (codigoBarra && codigoBarra.value && window.renderBarcodeVisual) {
                    renderBarcodeVisual('#barcode-preview-fijo-edit', codigoBarra.value, { height: 35, fontSize: 11 });
                }
            });

            modalEl.addEventListener('hidden.bs.modal', function() {
                if (cameraStreamEdit) {
                    cameraStreamEdit.getTracks().forEach(t => t.stop());
                    cameraStreamEdit = null;
                }
                const video = document.getElementById('act_videoPreview');
                if (video) video.style.display = 'none';
                const btnCapturar = document.getElementById('act_btnCapturarFoto');
                if (btnCapturar) btnCapturar.classList.add('d-none');
            });
        }
    });
</script>