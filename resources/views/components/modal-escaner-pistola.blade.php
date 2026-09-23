<!-- MODAL BÚSQUEDA RÁPIDA / PISTOLA LECTORA DE CÓDIGOS DE BARRAS -->
<div class="modal fade" id="modalEscanerPistola" tabindex="-1" aria-labelledby="modalEscanerPistolaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient bg-primary text-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="fas fa-barcode fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalEscanerPistolaLabel">Lector de Código de Barras</h5>
                        <small class="text-white-50">Escanee con la pistola lectora o ingrese el código manualmente</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Indicador de estado del escáner -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill d-flex align-items-center gap-2">
                            <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 0.6rem; height: 0.6rem;"></span>
                            <span class="fw-semibold">Pistola lista para disparar</span>
                        </span>
                    </div>
                    <small class="text-muted"><kbd class="bg-light text-secondary border px-2 py-1">F2</kbd> o <kbd class="bg-light text-secondary border px-2 py-1">Alt+B</kbd> para activar</small>
                </div>

                <!-- Campo de lectura principal -->
                <div class="mb-4">
                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden border">
                        <span class="input-group-text bg-white border-0 text-primary px-3">
                            <i class="fas fa-barcode fs-3"></i>
                        </span>
                        <input type="text" 
                               class="form-control border-0 ps-2" 
                               id="escanerCodigoInput" 
                               placeholder="Esperando escaneo o escriba aquí..." 
                               autocomplete="off"
                               onkeydown="if(event.key==='Enter'){ event.preventDefault(); ejecutarBusquedaEscaner(); }">
                        <button class="btn btn-outline-secondary border-0 px-3" type="button" onclick="document.getElementById('escanerCodigoInput').value=''; document.getElementById('escanerCodigoInput').focus();" title="Limpiar">
                            <i class="fas fa-times"></i>
                        </button>
                        <button class="btn btn-primary px-4 fw-semibold" type="button" onclick="ejecutarBusquedaEscaner()">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                    <div class="form-text mt-2 text-muted">
                        <i class="fas fa-info-circle me-1 text-primary"></i> Al disparar la pistola lectora sobre cualquier etiqueta, el sistema buscará el producto al instante.
                    </div>
                </div>

                <!-- Cargando -->
                <div id="escanerLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Buscando...</span>
                    </div>
                    <p class="text-muted mb-0">Consultando inventario...</p>
                </div>

                <!-- Error / No encontrado -->
                <div id="escanerError" class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 rounded-3 d-none">
                    <i class="fas fa-exclamation-triangle fs-2 text-warning"></i>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Producto no encontrado</h6>
                        <p class="error-text mb-0 small text-muted">No se encontró ningún artículo con ese código.</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="document.getElementById('escanerCodigoInput').focus();">Reintentar</button>
                </div>

                <!-- Resultado de producto -->
                <div id="escanerResult" class="card border rounded-3 shadow-sm overflow-hidden d-none">
                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary" id="escanerProdTipo">Activo</span>
                        <span class="badge bg-light text-dark border" id="escanerProdEmpresa">Empresa</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <h5 class="fw-bold text-dark mb-1" id="escanerProdNombre">Nombre del Producto</h5>
                                <div class="text-muted small mb-2" id="escanerRowClave">
                                    <strong>Clave:</strong> <span id="escanerProdClave"></span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i> <span id="escanerProdUbicacion">Ubicación</span>
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" id="escanerRowPrecio">
                                        <i class="fas fa-tag me-1"></i> <span id="escanerProdPrecio">Precio</span>
                                    </span>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25" id="escanerRowStock">
                                        <i class="fas fa-boxes-stacked me-1"></i> <span id="escanerProdStock">Stock</span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-5 text-center border-start">
                                <div class="p-2 bg-white rounded border d-inline-block shadow-xs mb-1">
                                    <svg id="escanerProdSvg" style="max-height: 50px; max-width: 100%;"></svg>
                                </div>
                                <div class="text-muted font-monospace small" id="escanerProdCodigo"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" id="escanerBtnPrint">
                            <i class="fas fa-print me-1"></i> Imprimir Etiqueta
                        </button>
                        <a href="#" target="_blank" class="btn btn-sm btn-outline-success d-none" id="escanerBtnVale">
                            <i class="fas fa-file-signature me-1"></i> Carta Responsiva
                        </a>
                        <a href="#" class="btn btn-sm btn-primary px-3" id="escanerBtnVer">
                            <i class="fas fa-arrow-right me-1"></i> Ver Detalle Completo
                        </a>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-keyboard me-1"></i> Compatible con pistolas USB, inalámbricas Bluetooth y lectores 1D/2D
                </small>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Foco automático en el input al abrir el modal
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('modalEscanerPistola');
        if (modalEl) {
            modalEl.addEventListener('shown.bs.modal', function () {
                var input = document.getElementById('escanerCodigoInput');
                if (input) {
                    input.focus();
                    input.select();
                }
            });
        }
    });
</script>
