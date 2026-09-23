<!-- MODAL IMPRESIÓN DE ETIQUETAS ADHESIVAS / CÓDIGO DE BARRAS -->
<div class="modal fade" id="modalImprimirEtiqueta" tabindex="-1" aria-labelledby="modalImprimirEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-print fs-5 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalImprimirEtiquetaLabel">Impresión de Etiquetas Adhesivas</h5>
                        <small class="text-white-50">Formato para impresoras térmicas (50x25mm / 58mm) o planillas de stickers</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- Controles de impresión -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white no-print">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <label for="printCopiasSelect" class="form-label small fw-bold text-muted mb-1">
                                <i class="fas fa-copy me-1"></i> Cantidad de etiquetas:
                            </label>
                            <select id="printCopiasSelect" class="form-select form-select-sm" onchange="actualizarCopiasEtiqueta(this.value)">
                                <option value="1" selected>1 etiqueta (Individual)</option>
                                <option value="2">2 etiquetas</option>
                                <option value="4">4 etiquetas</option>
                                <option value="8">8 etiquetas</option>
                                <option value="12">12 etiquetas (Media planilla)</option>
                                <option value="24">24 etiquetas (Planilla completa)</option>
                            </select>
                        </div>
                        <div class="col-md-7 text-md-end pt-3 pt-md-0">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border me-2">
                                <i class="fas fa-ruler-combined me-1"></i> Optimizado 50×25mm
                            </span>
                            <button type="button" class="btn btn-primary px-4 shadow-sm fw-semibold" onclick="ejecutarImpresionEtiqueta()">
                                <i class="fas fa-print me-1"></i> Imprimir Ahora
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenedor visible de las etiquetas a imprimir -->
                <div class="d-flex justify-content-center">
                    <div id="printEtiquetasGrid" class="sgi-stickers-container">
                        <!-- Generado dinámicamente por actualizarCopiasEtiqueta() -->
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white py-2 px-4 d-flex justify-content-between no-print">
                <small class="text-muted">
                    <i class="fas fa-lightbulb text-warning me-1"></i> En el diálogo de impresión, desactiva "Encabezados y pies de página" para un acabado perfecto.
                </small>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos del Sticker en pantalla */
    .sgi-stickers-container {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
        max-height: 480px;
        overflow-y: auto;
        padding: 10px;
    }
    .sgi-sticker-item {
        width: 220px;
    }
    .sgi-sticker-card {
        border: 1px dashed #cbd5e1 !important;
        border-radius: 8px;
        background: #ffffff;
        padding: 8px 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    }
    .sgi-sticker-empresa {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #0f172a;
        line-height: 1.1;
    }
    .sgi-sticker-nombre {
        font-size: 0.82rem;
        font-weight: 600;
        color: #1e293b;
        margin-top: 2px;
        line-height: 1.2;
    }
    .sgi-sticker-clave {
        font-size: 0.7rem;
        color: #64748b;
        margin-top: 1px;
    }
    .sgi-sticker-barcode svg {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* Reglas de Impresión (@media print) */
    @media print {
        /* Ocultar todo el sitio web excepto el modal de impresión */
        body * {
            visibility: hidden;
        }
        #modalImprimirEtiqueta, 
        #modalImprimirEtiqueta #printEtiquetasGrid,
        #modalImprimirEtiqueta #printEtiquetasGrid * {
            visibility: visible;
        }
        #modalImprimirEtiqueta {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            background: transparent !important;
        }
        .modal-dialog {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .modal-content {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        .modal-header,
        .modal-footer,
        .no-print {
            display: none !important;
        }
        .modal-body {
            padding: 0 !important;
            background: transparent !important;
        }
        .sgi-stickers-container {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6mm !important;
            justify-content: flex-start !important;
            overflow: visible !important;
            max-height: none !important;
            padding: 0 !important;
        }
        .sgi-sticker-item {
            width: 50mm !important;
            page-break-inside: avoid !important;
            margin-bottom: 4mm !important;
        }
        .sgi-sticker-card {
            border: 1px solid #94a3b8 !important;
            border-radius: 4px !important;
            padding: 4px 6px !important;
            box-shadow: none !important;
        }
        .sgi-sticker-barcode svg {
            max-height: 25mm !important;
        }
    }
</style>
