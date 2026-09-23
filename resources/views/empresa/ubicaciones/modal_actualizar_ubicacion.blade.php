{{-- Modal para actualizar ubicación --}}
<div class="modal fade" id="modalActualizarUbicacion" tabindex="-1" aria-labelledby="modalActualizarUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" id="formActualizarUbicacion">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow-lg">
                <!-- Encabezado -->
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                    <h5 class="modal-title fs-4 fw-bold">
                        <i class="fas fa-edit me-2"></i>Actualizar Ubicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo -->
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- NOMBRE --}}
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-2 border-primary" id="editarNombreUbicacion" name="nombre" required>
                                <label for="editarNombreUbicacion" class="text-muted">
                                    <i class="fas fa-tag me-1"></i> Nombre de la ubicación
                                </label>
                            </div>
                        </div>

                        {{-- ESTADO --}}
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select border-2 border-primary" id="editarEstadoUbicacion" name="estado" required>
                                    <option value="activo">Activo</option>
                                    <option value="baja">Baja</option>
                                </select>
                                <label for="editarEstadoUbicacion" class="text-muted">
                                    <i class="fas fa-power-off me-1"></i> Estado
                                </label>
                            </div>
                        </div>

                        {{-- EMPRESA (oculto si ya está asociada) --}}
                        <input type="hidden" name="empresa_id" id="editarEmpresaId">
                        <div class="col-12" id="editarEmpresaSelectContainer" style="display: none;">
                            <div class="form-floating mb-3">
                                <select class="form-select border-2 border-primary" id="editarEmpresaSelect">
                                    @foreach ($empresas as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                    @endforeach
                                </select>
                                <label for="editarEmpresaSelect" class="text-muted">
                                    <i class="fas fa-building me-1"></i> Empresa
                                </label>
                            </div>
                        </div>

                        {{-- CLAVE (readonly) --}}
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-2 border-success bg-light" id="editarClaveUbicacion" name="clave" readonly required>
                                <label for="editarClaveUbicacion" class="text-muted">
                                    <i class="fas fa-key me-1"></i> Clave
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-lg btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-lg btn-danger rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);">
                        <i class="fas fa-save me-2"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
    document.getElementById('modalActualizarUbicacion').addEventListener('shown.bs.modal', function() {
        this.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalActualizarUbicacion');
        const form = document.getElementById('formActualizarUbicacion');

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            const id = button.getAttribute('data-id');
            const nombre = button.getAttribute('data-nombre');
            const estado = button.getAttribute('data-estado');
            const clave = button.getAttribute('data-clave');
            const empresaId = button.getAttribute('data-empresa-id');
            const empresaNombre = button.getAttribute('data-empresa-nombre');

            form.action = `/empresa/ubicaciones/${id}/actualizar`;

            document.getElementById('editarNombreUbicacion').value = nombre;
            document.getElementById('editarEstadoUbicacion').value = estado;
            document.getElementById('editarClaveUbicacion').value = clave;

            document.getElementById('editarEmpresaId').value = empresaId;

            const empresaSelect = document.getElementById('editarEmpresaSelect');
            const empresaContainer = document.getElementById('editarEmpresaSelectContainer');

            if (empresaId) {
                empresaContainer.style.display = 'none';
            } else {
                empresaContainer.style.display = 'block';
                [...empresaSelect.options].forEach(opt => {
                    opt.selected = opt.text === empresaNombre;
                });
            }
        });
    });
</script>