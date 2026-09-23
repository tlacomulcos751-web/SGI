<div class="modal fade" id="actualizarEntregaModal" tabindex="-1" aria-labelledby="actualizarEntregaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form method="POST" action="{{ route('productos_compra_venta.actualizarEstado', $productoCompraVenta->id) }}" class="needs-validation" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <!-- Encabezado con gradiente -->
                <div class="modal-header bg-gradient-info text-white py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-map-marker-alt me-2"></i>Actualizar Estado
                    </h5>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <!-- Estado -->
                    <div class="col-md-6">
                        <label for="estadoSelect" class="form-label fw-medium">Estado <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm" id="estadoSelect" name="estado" required>
                            <option value="almacen" @selected($productoCompraVenta->estado === 'almacen')>Almacen</option>
                            @if(tienePermiso('compra/venta - desactivar'))
                            <option value="vendido" @selected($productoCompraVenta->estado === 'vendido')>Vendido</option>
                            @endif
                        </select>
                    </div>
                    <br>
                    <div class="col-12" id="documentoBajaField">
                        <div class="card border-danger bg-soft-danger">
                            <div class="card-body">
                                <h6 class="card-title text-danger">
                                    <i class="fas fa-file-upload me-2"></i>Documento de Respaldo para la venta
                                </h6>
                                <input type="file" class="form-control mt-2 shadow-sm" name="documento_baja"
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Formatos permitidos: PDF, JPG, PNG (Tamaño máximo: 5MB)</small>
                            </div>
                        </div>
                    </div>
                    <!-- Comentario -->
                    <div class="col-12">
                        <label for="comentarioTextarea" class="form-label fw-medium">Comentarios Adicionales</label>
                        <textarea class="form-control shadow-sm" id="comentarioTextarea" name="comentario"
                            rows="2" placeholder="Ingrese cualquier comentario relevante sobre el producto">{{ old('comentario', $productoCompraVenta->comentario) }}</textarea>
                    </div>
                    <!-- Pie del modal -->
                    <div class="modal-footer bg-light bg-gradient">
                        <button type="button" class="btn btn-outline-secondary rounded-2 px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-info rounded-2 px-4 shadow-sm">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>