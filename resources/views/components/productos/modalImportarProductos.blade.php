<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">

            <!-- Header con gradiente -->
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="fas fa-file-import me-2"></i>
                    Importar {{ $tipo ?? 'Productos' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <!-- Tarjeta de instrucciones mejorada -->
                <div class="info-box border-start border-primary border-4 rounded-end p-4 mb-4 bg-light">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-info-circle text-primary fs-4 me-2"></i>
                        <h5 class="mb-0 fw-semibold">Instrucciones de Importación</h5>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-file-excel text-success me-2"></i>
                            <span>Formato: Excel (.xlsx, .xls) o CSV</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-list-alt text-info me-2"></i>
                            <span>Primera fila con encabezados de columnas</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-columns text-warning me-2"></i>
                            <span>Nombres de columnas según formato de exportación</span>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span>Todos los elementos necesarios deben estar correctos</span>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-weight text-secondary me-2"></i>
                            <span>Tamaño máximo: 10MB</span>
                        </li>
                    </ul>
                </div>

                <form id="importForm" enctype="multipart/form-data" method="POST" action="{{ $route }}">
                    @csrf

                    <!-- Selector de tipo con icono -->
                    <div class="mb-4">
                        <label for="tipo_importacion" class="form-label fw-semibold">
                            <i class="fas fa-tags me-2 text-primary"></i>
                            Tipo de Importación:
                        </label>
                        <select id="tipo_importacion" name="tipo_importacion" class="form-select form-select-lg border-0 shadow-sm" required>
                            <option value="">Seleccione una opción</option>
                            <option value="compra-venta" {{ ($tipo ?? '') == 'compra-venta' ? 'selected' : '' }}>
                                <i class="fas fa-exchange-alt me-2"></i>Productos de Compra-Venta
                            </option>
                            <option value="fijos" {{ ($tipo ?? '') == 'fijos' ? 'selected' : '' }}>
                                <i class="fas fa-box me-2"></i>Productos Fijos
                            </option>
                            <option value="consumibles" {{ ($tipo ?? '') == 'consumibles' ? 'selected' : '' }}>
                                <i class="fas fa-bolt me-2"></i>Productos Consumibles
                            </option>
                            <option value="vehículos" {{ ($tipo ?? '') == 'vehículos' ? 'selected' : '' }}>
                                <i class="fas fa-car me-2"></i>Vehículos
                            </option>
                        </select>
                    </div>

                    <!-- Selector de archivo mejorado -->
                    <div class="mb-4">
                        <label for="archivo" class="form-label fw-semibold">
                            <i class="fas fa-file-upload me-2 text-primary"></i>
                            Archivo Excel:
                        </label>
                        <div class="file-input-wrapper">
                            <input type="file" id="archivo" name="archivo" class="form-control form-control-lg border-0 shadow-sm"
                                accept=".xlsx,.xls,.csv" required>
                            <div class="file-input-hint text-muted small mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Formatos aceptados: .xlsx, .xls, .csv
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer con botones mejorados -->
            <div class="modal-footer bg-light">
                <button class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button class="btn btn-primary btn-lg px-4 shadow-sm" type="submit" form="importForm">
                    <i class="fas fa-upload me-2"></i>Importar Datos
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('tipo_importacion').addEventListener('change', function() {
        const rutas = {
            'compra-venta': '{{ route("importar.compra.venta") }}',
            'fijos': '{{ route("importar.fijos") }}',
            'consumibles': '{{ route("importar.consumibles") }}',
            'vehículos': '{{ route("importar.vehiculos") }}'
        };

        const form = document.getElementById('importForm');
        form.action = rutas[this.value] || '{{ $route }}';

        // Animación sutil al cambiar
        this.style.transform = 'scale(1.02)';
        setTimeout(() => {
            this.style.transform = 'scale(1)';
        }, 200);
    });

    // Efecto hover en elementos del formulario
    document.querySelectorAll('.form-select, .form-control').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });

        element.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
</script>