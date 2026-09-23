{{-- Modal para insertar ubicación --}}
<div class="modal fade" id="modalInsertarUbicacion" tabindex="-1" aria-labelledby="modalInsertarUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('empresa.ubicaciones.insertar') }}">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <!-- Encabezado del modal con gradiente -->
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                    <h5 class="modal-title fs-4 fw-bold">
                        <i class="fas fa-map-marker-alt me-2"></i>Nueva Ubicación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- NOMBRE --}}
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-2 border-primary" id="nombreUbicacion" name="nombre" placeholder=" " required>
                                <label for="nombreUbicacion" class="text-muted">
                                    <i class="fas fa-tag me-1"></i> Nombre de la ubicación
                                </label>
                            </div>
                        </div>

                        {{-- ESTADO --}}
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select border-2 border-primary" id="estadoUbicacion" name="estado" required>
                                    <option value="activo" selected>Activo</option>
                                    <option value="baja" disabled>Baja</option>
                                </select>
                                <label for="estadoUbicacion" class="text-muted">
                                    <i class="fas fa-power-off me-1"></i> Estado
                                </label>
                            </div>
                        </div>

                        {{-- EMPRESA (oculto por defecto) --}}
                        <input type="hidden" name="empresa_id" id="empresaIdInput">
                        <div class="col-12" id="empresaSelectContainer" style="display: none;">
                            <div class="form-floating mb-3">
                                <select class="form-select border-2 border-primary" id="empresaSelect">
                                    @foreach ($empresas as $e)
                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                    @endforeach
                                </select>
                                <label for="empresaSelect" class="text-muted">
                                    <i class="fas fa-building me-1"></i> Empresa
                                </label>
                            </div>
                        </div>

                        {{-- CLAVE (generada automáticamente) --}}
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control border-2 border-success bg-light" id="claveUbicacion" name="clave" placeholder=" " readonly required>
                                <label for="claveUbicacion" class="text-muted">
                                    <i class="fas fa-key me-1"></i> Clave generada automáticamente
                                </label>
                                <div class="form-text text-success">
                                    <i class="fas fa-info-circle me-1"></i> La clave se genera automáticamente al ingresar el nombre
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-lg btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-lg btn-primary rounded-pill px-4 shadow-sm" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                        <i class="fas fa-save me-2"></i> Guardar Ubicación
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalInsertarUbicacion');
        const nombreInput = document.getElementById('nombreUbicacion');
        const claveInput = document.getElementById('claveUbicacion');
        const empresaIdInput = document.getElementById('empresaIdInput');
        const empresaSelect = document.getElementById('empresaSelect');
        const empresaSelectContainer = document.getElementById('empresaSelectContainer');

        let empresaNombreActual = '';

        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const empresaId = button.getAttribute('data-empresa-id');
            const empresaNombre = button.getAttribute('data-empresa-nombre');

            // Si se abre desde un botón con empresa asociada
            if (empresaId) {
                console.log(`Empresa ID: ${empresaId}, Nombre: ${empresaNombre}`);
                empresaIdInput.value = empresaId;
                empresaNombreActual = empresaNombre;
                empresaSelectContainer.style.display = 'none';
            } else {
                console.log('Abriendo modal sin empresa asociada');
                empresaIdInput.value = '';
                empresaNombreActual = '';
                empresaSelectContainer.style.display = 'block';
            }

            nombreInput.value = '';
            claveInput.value = '';
        });

        function slug(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
        }

        function siglas(str) {
            return str.split(' ').map(p => p[0]?.toUpperCase()).join('');
        }

        function generarClave(nombreUbicacion, nombreEmpresa) {
            return `${siglas(nombreEmpresa)}-${slug(nombreUbicacion).substring(0, 12)}`;
        }

        nombreInput.addEventListener('input', () => {
            let nombreEmpresa = empresaNombreActual;
            if (!empresaNombreActual && empresaSelect.selectedOptions.length > 0) {
                nombreEmpresa = empresaSelect.selectedOptions[0].text;
                empresaIdInput.value = empresaSelect.value;
            }
            claveInput.value = generarClave(nombreInput.value, nombreEmpresa);
        });

        empresaSelect.addEventListener('change', () => {
            empresaNombreActual = empresaSelect.selectedOptions[0].text;
            empresaIdInput.value = empresaSelect.value;
            nombreInput.dispatchEvent(new Event('input')); // regenerar clave
        });
    });
</script>