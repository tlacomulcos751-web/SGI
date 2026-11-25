<!-- Modal Insertar Producto Compra-Venta -->
<div class="modal fade" id="insertarProductoCompraVentaModal" tabindex="-1" aria-labelledby="insertarProductoCompraVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('productos_compra_venta.insertar') }}" enctype="multipart/form-data" id="formInsertarProductoCompraVenta" data-sgi-draft="true" class="modal-content border-0 shadow-lg needs-validation" novalidate>
            @csrf
            <!-- Encabezado -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <h5 class="modal-title fs-5 text-white" id="insertarProductoCompraVentaModalLabel">
                    <i class="fas fa-cart-plus me-2"></i>Insertar Producto Compra-Venta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

                <!-- Cuerpo -->
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Columna Izquierda: Información Básica -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-success"></i>Información Básica</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label fw-medium">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" maxlength="150" required placeholder="Nombre del producto">
                                    </div>

                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label fw-medium">Descripción</label>
                                        <textarea name="descripcion" id="descripcion" rows="2" class="form-control" placeholder="Descripción opcional..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="codigoBarra" class="form-label mb-0 fw-medium">Código de Barras</label>
                                            <span class="text-muted small">Opcional / Autogenerable</span>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                                            <input type="text" name="codigoBarra" id="codigoBarra" class="form-control" maxlength="50" placeholder="Escanear o autogenerar..." oninput="renderBarcodeVisual('#barcode-preview-cv-insert', this.value); document.getElementById('preview-box-cv-insert').classList.toggle('d-none', !this.value.trim());">
                                            <button class="btn btn-outline-primary" type="button" onclick="generarCodigoBarra('codigoBarra', 'barcode-preview-cv-insert'); document.getElementById('preview-box-cv-insert').classList.remove('d-none');" title="Autogenerar código de barras único">
                                                <i class="fas fa-magic me-1"></i> Generar
                                            </button>
                                        </div>
                                        <div id="preview-box-cv-insert" class="barcode-auto-preview mt-2 p-2 bg-white rounded border text-center d-none shadow-xs">
                                            <small class="text-muted d-block mb-1">Previsualización del código:</small>
                                            <svg id="barcode-preview-cv-insert" class="img-fluid" style="max-height: 45px;"></svg>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="empresa_id" class="form-label fw-medium">Empresa Interna <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="empresa_id" id="empresa_id" required>
                                            <option value="" disabled selected>Seleccione la empresa interna...</option>
                                            @foreach($empresasInternas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione la empresa dueña del producto</div>
                                    </div>

                                    <div class="mb-0">
                                        <label for="ubicacion_id" class="form-label fw-medium">Ubicación <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="ubicacion_id" id="ubicacion_id" required>
                                            @foreach($ubicaciones as $ubicacion)
                                            @if($ubicacion->puede_insertar_ubicacion)
                                            <option value="{{ $ubicacion->ubicacion_id }}">{{ $ubicacion->ubicacion->nombre }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Compra-Venta + Categorías + Comentario -->
                        <div class="col-lg-6 d-flex flex-column gap-3">
                            <!-- Detalles Compra-Venta -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-coins me-2 text-success"></i>Compra-Venta</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-sm-6 mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="precio" class="form-label mb-0 fw-medium">Precio ($)</label>
                                                <div class="form-check form-switch m-0 d-flex align-items-center gap-1">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="no_aplica_precio_cv" onchange="toggleNoAplicaPrecioCV(this, 'precio')" style="cursor: pointer;">
                                                    <label class="form-check-label small text-muted user-select-none" for="no_aplica_precio_cv" style="cursor: pointer; font-size: 0.78rem;">N.A.</label>
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light fw-bold" id="addon-precio-cv">$</span>
                                                <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0" max="999999.99" value="1.00" placeholder="0.00">
                                            </div>
                                            <div id="hint-na-precio-cv" class="form-text text-muted small d-none">
                                                <i class="fas fa-info-circle me-1 text-primary"></i>Sin costo asignado.
                                            </div>
                                        </div>

                                        <div class="col-sm-6 mb-2">
                                            <label for="existencia" class="form-label fw-medium">Cantidad <span class="text-danger">*</span></label>
                                            <input type="number" name="existencia" id="existencia" class="form-control" min="1" max="100000" required placeholder="Cantidad disponible">
                                        </div>

                                        <div class="col-sm-6 mb-2">
                                            <label for="empresaVenta_id" class="form-label fw-medium">Empresa Venta <span class="text-danger">*</span></label>
                                            <select class="form-select select2" name="empresaVenta_id" id="empresaVenta_id" required>
                                                <option value="" disabled selected>Seleccione empresa de venta...</option>
                                                @if(isset($empresas) && $empresas->where('status', 'externa')->count() > 0)
                                                    <optgroup label="Empresas Externas / Clientes">
                                                        @foreach($empresas->where('status', 'externa') as $empresa)
                                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                    <optgroup label="Empresas Internas">
                                                        @foreach($empresas->where('status', 'interna') as $empresa)
                                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @else
                                                    @foreach($empresas as $empresa)
                                                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="invalid-feedback">Por favor seleccione la empresa de venta</div>
                                        </div>

                                        <div class="col-sm-6 mb-2">
                                            <label for="fechaEntrada" class="form-label fw-medium">Fecha Entrada <span class="text-danger">*</span></label>
                                            <input type="date" name="fechaEntrada" id="fechaEntrada" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categorías -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <h6 class="mb-0"><i class="fas fa-tags me-2 text-success"></i>Categorías</h6>
                                    @if(tienePermiso('insertarEtiquetas') || esSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalInsertarEtiqueta" style="font-size: 0.8rem;">
                                        <i class="fas fa-plus me-1"></i> Nueva
                                    </button>
                                    @endif
                                </div>
                                <div class="card-body py-2">
                                    <div class="d-flex flex-wrap gap-2" style="max-height: 120px; overflow-y: auto;">
                                        @foreach($etiquetasUnicas as $etiqueta)
                                        <div class="form-check form-check-inline m-0 d-flex align-items-center gap-1">
                                            <input class="form-check-input" type="checkbox" name="etiquetasInsertar[]" value="{{ $etiqueta->id }}" id="etiqueta-{{ $etiqueta->id }}" style="cursor: pointer;">
                                            <label class="form-check-label badge bg-success bg-opacity-10 text-success p-2 user-select-none" for="etiqueta-{{ $etiqueta->id }}" style="cursor: pointer;">
                                                <i class="fas fa-tag me-1"></i>{{ $etiqueta->nombre }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Comentario -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0"><i class="fas fa-comment-dots me-2 text-success"></i>Comentario</h6>
                                </div>
                                <div class="card-body py-2">
                                    <textarea name="comentario" id="comentario" rows="2" class="form-control" placeholder="Observaciones del ingreso..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Guardar Producto</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>
function toggleNoAplicaPrecioCV(checkbox, inputId) {
    const input = document.getElementById(inputId);
    const addon = document.getElementById('addon-precio-cv');
    const hint = document.getElementById('hint-na-precio-cv');
    if (!input) return;
    if (checkbox.checked) {
        input.dataset.oldValue = input.value;
        input.value = '';
        input.disabled = true;
        input.placeholder = 'N.A. (No aplica)';
        input.removeAttribute('required');
        input.classList.add('bg-light', 'text-muted');
        if (addon) addon.classList.add('opacity-50');
        if (hint) hint.classList.remove('d-none');
    } else {
        input.disabled = false;
        input.placeholder = '0.00';
        input.value = input.dataset.oldValue || '1.00';
        input.classList.remove('bg-light', 'text-muted');
        if (addon) addon.classList.remove('opacity-50');
        if (hint) hint.classList.add('d-none');
        input.focus();
    }
}
</script>