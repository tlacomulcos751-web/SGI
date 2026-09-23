<!-- Modal Insertar Producto Compra-Venta -->
<div class="modal fade" id="insertarProductoCompraVentaModal" tabindex="-1" aria-labelledby="insertarProductoCompraVentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="POST" action="{{ route('productos_compra_venta.insertar') }}" enctype="multipart/form-data" id="formInsertarProductoCompraVenta" class="needs-validation" novalidate>
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <!-- Encabezado -->
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fs-5" id="insertarProductoCompraVentaModalLabel">
                        <i class="fas fa-cart-plus me-2"></i>Insertar Producto Compra-Venta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo -->
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Información Básica -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" name="nombre" id="nombre" class="form-control" maxlength="150" required placeholder="Nombre del producto">
                                    </div>

                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea name="descripcion" id="descripcion" rows="2" class="form-control" placeholder="Descripción opcional..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="codigoBarra" class="form-label mb-0">Código de Barras</label>
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

                        <!-- Detalles Compra-Venta -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-coins me-2"></i>Compra-Venta</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label for="precio" class="form-label mb-0">Precio ($)</label>
                                            <div class="form-check form-check-inline mb-0 me-0">
                                                <input class="form-check-input" type="checkbox" id="no_aplica_precio_cv" onchange="toggleNoAplicaPrecioCV(this, 'precio')">
                                                <label class="form-check-label text-muted small" for="no_aplica_precio_cv">No aplica</label>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0" max="999999.99" value="1.00" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="empresaVenta_id" class="form-label">Empresa de Venta <span class="text-danger">*</span></label>
                                        <select class="form-select select2" name="empresaVenta_id" id="empresaVenta_id" required>
                                            @foreach($empresas as $empresa)
                                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="existencia" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                        <input type="number" name="existencia" id="existencia" class="form-control" min="1" max="100000" required placeholder="Cantidad disponible">
                                    </div>

                                    <div class="mb-3">
                                        <label for="fechaEntrada" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                                        <input type="date" name="fechaEntrada" id="fechaEntrada" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Etiquetas -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Categorías</h6>
                                    @if(tienePermiso('insertarEtiquetas') || esSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalInsertarEtiqueta">
                                        <i class="fas fa-plus me-1"></i> Nueva Categoría
                                    </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($etiquetasUnicas as $etiqueta)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="etiquetasInsertar[]" value="{{ $etiqueta->id }}" id="etiqueta-{{ $etiqueta->id }}">
                                            <label class="form-check-label badge bg-success bg-opacity-10 text-success p-2" for="etiqueta-{{ $etiqueta->id }}">
                                                <i class="fas fa-tag me-1"></i>{{ $etiqueta->nombre }}
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
                                    <h6 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Comentario</h6>
                                </div>
                                <div class="card-body">
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