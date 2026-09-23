<div class="modal fade" id="actualizarInfoModal" tabindex="-1" aria-labelledby="actualizarInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('productos_fijos.actualizarInfo', $productoFijo->id) }}" class="needs-validation" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente azul -->
                <div class="modal-header bg-gradient-primary text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-edit me-2"></i>Actualizar Información del Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <!-- Advertencia importante -->
                    <div class="alert alert-warning border-warning bg-soft-warning rounded-2 mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Modificar esta información puede afectar otros registros. Por favor verifique cuidadosamente antes de actualizar.
                    </div>

                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label for="nombreInput" class="form-label fw-medium">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-sm" id="nombreInput" name="nombre"
                                value="{{ $producto->nombre }}" maxlength="150" required
                                placeholder="Ingrese el nombre del producto">
                            <div class="invalid-feedback">Por favor ingrese un nombre válido</div>
                        </div>

                        <!-- Código de Barras -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="codigoBarraInput" class="form-label fw-medium mb-0">Código de Barras</label>
                                <span class="text-muted small">Opcional</span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control shadow-sm" id="codigoBarraInput" name="codigoBarra"
                                    value="{{ $producto->codigoBarra }}" maxlength="50"
                                    placeholder="Escanear o generar..."
                                    oninput="renderBarcodeVisual('#barcode-preview-fijo-edit', this.value); document.getElementById('preview-box-fijo-edit').classList.toggle('d-none', !this.value.trim());">
                                <button class="btn btn-outline-primary" type="button" onclick="generarCodigoBarra('codigoBarraInput', 'barcode-preview-fijo-edit'); document.getElementById('preview-box-fijo-edit').classList.remove('d-none');" title="Autogenerar código de barras único">
                                    <i class="fas fa-magic me-1"></i> Generar
                                </button>
                            </div>
                            <div id="preview-box-fijo-edit" class="barcode-auto-preview mt-2 p-2 bg-white rounded border text-center {{ $producto->codigoBarra ? '' : 'd-none' }} shadow-xs">
                                <small class="text-muted d-block mb-1">Previsualización:</small>
                                <svg id="barcode-preview-fijo-edit" class="img-fluid" style="max-height: 45px;"></svg>
                            </div>
                            @if($producto->codigoBarra)
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    if (window.renderBarcodeVisual) {
                                        renderBarcodeVisual('#barcode-preview-fijo-edit', '{{ $producto->codigoBarra }}', { height: 35, fontSize: 11 });
                                    }
                                });
                            </script>
                            @endif
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcionTextarea" class="form-label fw-medium">Descripción</label>
                            <textarea class="form-control shadow-sm" id="descripcionTextarea" name="descripcion"
                                rows="2" placeholder="Ingrese una descripción detallada del producto">{{ $producto->descripcion }}</textarea>
                        </div>

                        <!-- Precio -->
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="precioInput" class="form-label fw-medium mb-0">Precio</label>
                                <div class="form-check form-check-inline mb-0 me-0">
                                    <input class="form-check-input" type="checkbox" id="no_aplica_precio_edit" 
                                           {{ is_null($producto->precio) ? 'checked' : '' }}
                                           onchange="toggleNoAplicaPrecioEdit(this, 'precioInput')">
                                    <label class="form-check-label text-muted small" for="no_aplica_precio_edit">No aplica</label>
                                </div>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.01" min="0" class="form-control shadow-sm {{ is_null($producto->precio) ? 'bg-light' : '' }}" id="precioInput"
                                    name="precio" value="{{ $producto->precio }}" {{ is_null($producto->precio) ? 'disabled' : '' }}
                                    placeholder="0.00">
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6">
                            <label for="estadoSelect" class="form-label fw-medium">Estado <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="estadoSelect" name="estado" required>
                                <option value="activo" @selected($productoFijo->estado === 'activo')>Activo</option>
                                <option value="reparacion" @selected($productoFijo->estado === 'reparacion')>En Reparación</option>
                                @if(tienePermiso('fijos - desactivar'))
                                <option value="baja" @selected($productoFijo->estado === 'baja')>Dado de Baja</option>
                                @endif
                            </select>
                        </div>

                        <!-- Calidad -->
                        <div class="col-md-6">
                            <label for="calidadSelect" class="form-label fw-medium">Estado de Conservación <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="calidadSelect" name="calidad" required>
                                <option value="bueno" @selected($productoFijo->calidad === 'bueno')>
                                    <i class="fas fa-check-circle text-success me-2"></i>Bueno
                                </option>
                                <option value="regular" @selected($productoFijo->calidad === 'regular')>
                                    <i class="fas fa-exclamation-circle text-warning me-2"></i>Regular
                                </option>
                                <option value="malo" @selected($productoFijo->calidad === 'malo')>
                                    <i class="fas fa-times-circle text-danger me-2"></i>Malo
                                </option>
                            </select>
                        </div>

                        <!-- Comentario -->
                        <div class="col-12">
                            <label for="comentarioTextarea" class="form-label fw-medium">Comentarios Adicionales</label>
                            <textarea class="form-control shadow-sm" id="comentarioTextarea" name="comentario"
                                rows="2" placeholder="Ingrese cualquier comentario relevante sobre el producto">{{ old('comentario', $productoFijo->comentario) }}</textarea>
                        </div>

                        <!-- Documento de baja (condicional) -->
                        <div class="col-12" id="documentoBajaField" style="display: none;">
                            <div class="card border-danger bg-soft-danger">
                                <div class="card-body">
                                    <h6 class="card-title text-danger">
                                        <i class="fas fa-file-upload me-2"></i>Documento de Respaldo para Baja
                                    </h6>
                                    <input type="file" class="form-control mt-2 shadow-sm" name="documento_baja"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Formatos permitidos: PDF, JPG, PNG (Tamaño máximo: 5MB)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light bg-gradient">
                    <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary rounded-2 px-4 shadow-sm">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    }

    .bg-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .rounded-2 {
        border-radius: 0.5rem !important;
    }

    .rounded-3 {
        border-radius: 1rem !important;
    }
</style>
<script>
function toggleNoAplicaPrecioEdit(checkbox, inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (checkbox.checked) {
        input.dataset.oldValue = input.value;
        input.value = '';
        input.disabled = true;
        input.classList.add('bg-light');
    } else {
        input.disabled = false;
        input.value = input.dataset.oldValue || '0.00';
        input.classList.remove('bg-light');
    }
}
</script>