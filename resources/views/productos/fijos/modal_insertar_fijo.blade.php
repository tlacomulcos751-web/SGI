<!-- Modal Insertar Producto Fijo -->
<div class="modal fade" id="insertarProductoFijoModal" tabindex="-1" aria-labelledby="insertarProductoFijoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('productos_fijos.insertar') }}" enctype="multipart/form-data" id="formInsertarProductoFijo" class="needs-validation" novalidate>
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <!-- Encabezado del Modal -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-5" id="insertarProductoFijoModalLabel">
                        <i class="fas fa-box-open me-2"></i>Insertar Producto Fijo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Sección 1: Información Básica -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" required maxlength="150" placeholder="Ej: Monitor LED 24''">
                                        <div class="invalid-feedback">Por favor ingrese un nombre válido</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2" placeholder="Descripción detallada del producto"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="codigoBarra" class="form-label mb-0">Código de Barras</label>
                                            <span class="text-muted small">Opcional / Autogenerable</span>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                                            <input type="text" class="form-control" id="codigoBarra" name="codigoBarra" maxlength="50" placeholder="Escanear con pistola o autogenerar..." oninput="renderBarcodeVisual('#barcode-preview-fijo-insert', this.value); document.getElementById('preview-box-fijo-insert').classList.toggle('d-none', !this.value.trim());">
                                            <button class="btn btn-outline-primary" type="button" onclick="generarCodigoBarra('codigoBarra', 'barcode-preview-fijo-insert'); document.getElementById('preview-box-fijo-insert').classList.remove('d-none');" title="Autogenerar código de barras único">
                                                <i class="fas fa-magic me-1"></i> Generar
                                            </button>
                                        </div>
                                        <div id="preview-box-fijo-insert" class="barcode-auto-preview mt-2 p-2 bg-white rounded border text-center d-none shadow-xs">
                                            <small class="text-muted d-block mb-1">Previsualización del código:</small>
                                            <svg id="barcode-preview-fijo-insert" class="img-fluid" style="max-height: 45px;"></svg>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="empresa_id" class="form-label">Empresa Interna <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="empresa_id" id="empresa_id" required>
                                            <option value="" disabled selected>Seleccione la empresa interna...</option>
                                            @foreach($empresasInternas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione la empresa dueña del producto</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="ubicacion_id" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="ubicacion_id" id="ubicacion_id" required>
                                            <option value="" disabled selected>Seleccione una ubicación...</option>
                                            @foreach($ubicaciones as $ubicacion)
                                            @if($ubicacion->puede_insertar_ubicacion)
                                            <option value="{{ $ubicacion->ubicacion_id }}">{{ $ubicacion->ubicacion->nombre }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione una ubicación</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="activo">
                        <!-- Sección 2: Detalles del Producto -->
                        <div class="col-lg-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Detalles del Producto</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="precio" class="form-label mb-0">Precio ($)</label>
                                                <div class="form-check form-check-inline mb-0 me-0">
                                                    <input class="form-check-input" type="checkbox" id="no_aplica_precio_fijo" onchange="toggleNoAplicaPrecio(this, 'precio')">
                                                    <label class="form-check-label text-muted small" for="no_aplica_precio_fijo">No aplica</label>
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" min="0" max="999999.99" value="1.00" placeholder="0.00">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cantidad_real" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select class="form-select" id="cantidad_select" onchange="document.getElementById('cantidad_real').value = this.value">
                                                    @for($i = 1; $i <= 50; $i++)
                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                <input type="number" class="form-control d-none" id="cantidad_input" placeholder="Ej. 1000" min="1" max="100000" oninput="document.getElementById('cantidad_real').value = this.value">
                                                <button type="button" class="btn btn-outline-secondary" id="btn_toggle_cantidad" onclick="toggleCantidadMode()" title="Escribir cantidad manual">
                                                    <i class="fas fa-keyboard"></i>
                                                </button>
                                            </div>
                                            <!-- Input real que se envía en el POST -->
                                            <input type="hidden" name="cantidad" id="cantidad_real" value="1">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="fechaEntrada" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fechaEntrada" name="fechaEntrada" required value="{{ date('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="calidad" class="form-label">Calidad <span class="text-danger">*</span></label>
                                            <select class="form-select" name="calidad" id="calidad" required>
                                                <option value="bueno">Bueno</option>
                                                <option value="regular">Regular</option>
                                                <option value="malo">Malo</option>
                                            </select>
                                        </div>

                                        <div class="col-12">
                                            <label for="responsable" class="form-label">Responsable <span class="text-danger">*</span></label>
                                            <select class="form-select select2" name="responsable" id="responsable" required>
                                                @foreach($usuarios as $usuario)
                                                <option value="{{ $usuario->id }}">{{ $usuario->nombre }} {{ $usuario->apellido }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 3: Multimedia y Comentarios -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-images me-2"></i>Multimedia</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Fotografías (Máx. 5MB cada una)</label>
                                        <input type="file" name="fotografias[]" class="form-control" multiple accept="image/*" id="fotografias">
                                        <small class="text-muted">Puedes seleccionar múltiples imágenes</small>
                                        <div class="preview-images mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Tomar Fotografía</label><br>
                                        <div class="mb-3">
                                            <label class="form-label">Seleccionar Cámara</label>
                                            <select id="cameraSelector" class="form-select mb-2"></select>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary" id="btnTomarFoto">
                                            <i class="fas fa-camera"></i> Usar Cámara
                                        </button>
                                        <video id="videoPreview" width="100%" height="200" autoplay playsinline style="display:none;" class="mt-2 border rounded"></video>
                                        <canvas id="canvasFoto" style="display: none;"></canvas>
                                        <input type="hidden" name="capturaCamara" id="capturaCamara">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección 4: Etiquetas -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Categorías</h6>
                                    @if(tienePermiso('insertarEtiquetas') || esSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInsertarEtiqueta">
                                        <i class="fas fa-plus me-1"></i> Nueva Categoría
                                    </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($etiquetasUnicas as $etiqueta)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="etiquetasInsertar[]" value="{{ $etiqueta->id }}" id="form-etiqueta-{{ $etiqueta->id }}">
                                            <label class="form-check-label badge bg-primary bg-opacity-10 text-primary p-2" for="form-etiqueta-{{ $etiqueta->id }}">
                                                <i class="fas fa-tag me-1"></i> {{ $etiqueta->nombre }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comentario -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-comment me-2"></i>Comentario</h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" id="comentario" name="comentario" rows="2" placeholder="Observaciones adicionales..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pie del Modal -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Escanear Código -->
<div class="modal fade" id="modalScanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-barcode me-2"></i>Escanear Código de Barras</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2">
                    <label for="scannerCameraSelector" class="form-label">Seleccionar Cámara</label>
                    <select id="scannerCameraSelector" class="form-select"></select>
                </div>
                <div id="scanner-container" class="border rounded" style="width: 100%; height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px 10px;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .preview-images img {
        max-width: 80px;
        max-height: 80px;
        border-radius: 4px;
        object-fit: cover;
    }

    .card {
        border-radius: 0.5rem;
    }

    .modal-content {
        border-radius: 0.75rem;
    }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>
    let isCantidadManual = false;

    function toggleCantidadMode() {
        const selectEl = document.getElementById('cantidad_select');
        const inputEl = document.getElementById('cantidad_input');
        const btnToggle = document.getElementById('btn_toggle_cantidad');
        const realInput = document.getElementById('cantidad_real');

        isCantidadManual = !isCantidadManual;

        if (isCantidadManual) {
            // Cambiar a modo manual
            selectEl.classList.add('d-none');
            inputEl.classList.remove('d-none');
            btnToggle.innerHTML = '<i class="fas fa-list"></i>';
            btnToggle.title = "Elegir cantidad de la lista";
            inputEl.focus();
            
            // Si el valor actual está en el rango de la lista, pre-llenarlo
            inputEl.value = realInput.value;
        } else {
            // Cambiar a modo lista
            inputEl.classList.add('d-none');
            selectEl.classList.remove('d-none');
            btnToggle.innerHTML = '<i class="fas fa-keyboard"></i>';
            btnToggle.title = "Escribir cantidad manual";
            
            // Validar si el valor escrito manualmente existe en la lista (1-50)
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

    function toggleNoAplicaPrecio(checkbox, inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        if (checkbox.checked) {
            input.dataset.oldValue = input.value;
            input.value = '';
            input.disabled = true;
            input.removeAttribute('required');
            input.classList.add('bg-light');
        } else {
            input.disabled = false;
            input.value = input.dataset.oldValue || '1.00';
            input.classList.remove('bg-light');
        }
    }
</script>