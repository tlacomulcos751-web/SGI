<div class="modal fade" id="modalAgregrarDocumentacion" tabindex="-1" aria-labelledby="modalAgregrarDocumentacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: 2px solid #383f8a; border-radius: 0.5rem; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #322a5a 0%, #383f8a 100%); border-bottom: 2px solid #e0b063;">
                <h5 class="modal-title text-white" id="modalAgregrarDocumentacionLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Agregar Documentación del Vehículo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vehiculos.documentacion.insertar', $vehiculo->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Tipo -->
                        <div class="card mb-4" style="border: 2px solid #4c61a9; border-radius: 0.5rem; box-shadow: 0 4px 8px rgba(36, 36, 35, 0.1);">
                            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #383f8a 0%, #6277b7 100%); border-bottom: 2px solid #e0b063;">
                                <span style="font-weight: 600;"><i class="bi bi-file-earmark-text-fill me-2"></i>Documentación</span>
                                <button type="button" class="btn btn-sm shadow-sm" style="background-color: #e0b063; color: #242423; font-weight: 500; border: none;" onclick="addDocumento()">
                                    <i class="bi bi-plus-circle me-1"></i>Agregar documento
                                </button>
                            </div>
                            <div class="card-body" id="documentos-wrapper" style="background-color: rgba(72, 97, 169, 0.05);">
                                <div class="row g-3 documento-item">
                                    @include('productos.vehiculos.documento', ['index' => 0])
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Pie del Modal -->
                    <div class="modal-footer" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-top: 2px solid #6277b7;">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" style="border: 2px solid #6277b7; color: #242423; font-weight: 500;" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #dc934a 0%, #e0b063 100%); border: none; color: #242423; font-weight: 500;">
                            <i class="fas fa-save me-2"></i>Guardar Documentación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="documento-template">
    <div class="row g-3 documento-item mt-3 border-top pt-3" style="border-color: #6277b7 !important;">
        @include('productos.vehiculos.documento', ['index' => '__INDEX__'])
        <div class="col-12 text-end">
            <button type="button" class="btn btn-sm shadow-sm rounded-pill px-3" style="background: linear-gradient(135deg, #cc5536 0%, #dc934a 100%); color: white; border: none; font-weight: 500;" onclick="this.closest('.documento-item').remove()">
                <i class="bi bi-trash me-1"></i>Eliminar
            </button>
        </div>
    </div>
</template>

<script>
    let docIndex = 1;

    function addDocumento() {
        const template = document.getElementById('documento-template').innerHTML.replace(/__INDEX__/g, docIndex++);
        document.getElementById('documentos-wrapper').insertAdjacentHTML('beforeend', template);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById("modalAgregrarDocumentacion");
        modal.addEventListener("shown.bs.modal", function() {
            document.body.style.overflow = "";
        });
    });
</script>