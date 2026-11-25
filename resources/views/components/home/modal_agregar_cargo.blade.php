<!-- Modal: Agregar Producto a mi Cargo -->
<div class="modal fade" id="modalAgregarProductoACargo" tabindex="-1" aria-labelledby="modalAgregarProductoACargoLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Header del Modal -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px;">
                        <i class="fas fa-box-open fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalAgregarProductoACargoLabel">Agregar Producto a mi Cargo</h5>
                        <small class="text-white-50">Gestiona tus herramientas, equipo o vehículos bajo tu resguardo</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Cuerpo del Modal (Scrollable) -->
            <div class="modal-body p-4">
                <!-- Pestañas de Selección -->
                <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-4 p-1 rounded-4 bg-light border gap-1" id="tabAgregarCargo" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs" 
                                id="tab-asignar-existente" 
                                data-bs-toggle="pill" 
                                data-bs-target="#panel-asignar-existente" 
                                type="button" 
                                role="tab" 
                                aria-controls="panel-asignar-existente" 
                                aria-selected="true">
                            <i class="fas fa-link text-primary"></i>
                            <span>Asignar Artículo Existente</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-xs" 
                                id="tab-registrar-nuevo" 
                                data-bs-toggle="pill" 
                                data-bs-target="#panel-registrar-nuevo" 
                                type="button" 
                                role="tab" 
                                aria-controls="panel-registrar-nuevo" 
                                aria-selected="false">
                            <i class="fas fa-plus-circle text-success"></i>
                            <span>Registrar Nuevo Activo Fijo</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tabAgregarCargoContent">
                    <!-- 1. PESTAÑA: ASIGNAR ARTÍCULO EXISTENTE -->
                    <div class="tab-pane fade show active" id="panel-asignar-existente" role="tabpanel" aria-labelledby="tab-asignar-existente">
                        <form action="{{ route('articulos.cargo.asignar') }}" method="POST" id="formAsignarCargo">
                            @csrf
                            <div class="row g-3">
                                <!-- Tipo de Artículo -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tipo de Artículo <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="tipo" id="selectTipoArticulo" required onchange="cambiarTipoArticuloCargo(this.value)">
                                        <option value="fijo" selected>Mobiliario / Equipo de Cómputo (Activo Fijo)</option>
                                        <option value="vehiculo">Vehículo / Unidad de Flotilla</option>
                                    </select>
                                </div>

                                <!-- Responsable -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Empleado a Cargo <span class="text-danger">*</span></label>
                                    @if(puedeAsignarArticulos())
                                        <select class="form-select rounded-3" name="responsable_id" required>
                                            <option value="{{ session('usuario')->id }}" selected>{{ session('usuario')->nombre }} {{ session('usuario')->apellido ?? '' }} (A mi cargo)</option>
                                            @foreach($todosUsuarios as $usr)
                                                @if($usr->id != session('usuario')->id)
                                                    <option value="{{ $usr->id }}">{{ $usr->nombre }} {{ $usr->apellido ?? '' }} ({{ $usr->nombre_user }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="text-primary d-block mt-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-shield-alt me-1"></i> Puedes asignar artículos a ti o a otros colaboradores.
                                        </small>
                                    @else
                                        <div class="form-control bg-light rounded-3 d-flex align-items-center justify-content-between">
                                            <span>
                                                <i class="fas fa-user-check text-success me-2"></i>
                                                <strong>{{ session('usuario')->nombre }} {{ session('usuario')->apellido ?? '' }}</strong>
                                            </span>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">Personal</span>
                                        </div>
                                        <input type="hidden" name="responsable_id" value="{{ session('usuario')->id }}">
                                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                            <i class="fas fa-info-circle me-1"></i> El artículo se asignará a tu resguardo personal.
                                        </small>
                                    @endif
                                </div>

                                <!-- Selector de Activo Fijo -->
                                <div class="col-12" id="grupoSelectFijo">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold mb-0">Seleccionar Activo Fijo <span class="text-danger">*</span></label>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" id="contadorFijosBadge" style="font-size: 0.75rem;">
                                            {{ count($activosFijosDisponibles) }} disponibles
                                        </span>
                                    </div>

                                    <!-- Barra de Búsqueda con Lupa -->
                                    <div class="input-group mb-2 shadow-xs rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-white bg-opacity-10 border-end-0 text-primary">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="filtroBuscarFijo" 
                                               class="form-control border-start-0 border-end-0 ps-1" 
                                               placeholder="Buscar activo por nombre, clave o ubicación (ej. estante, 127-..., laptop)..." 
                                               autocomplete="off">
                                        <button class="btn btn-outline-secondary border-start-0 d-none" 
                                                type="button" 
                                                id="btnLimpiarFiltroFijo" 
                                                onclick="limpiarFiltroFijo()"
                                                title="Limpiar búsqueda">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <select class="form-select rounded-3" name="item_id" id="selectItemFijo" required>
                                        <option value="" disabled selected>Selecciona un activo disponible...</option>
                                        @forelse($activosFijosDisponibles as $fijo)
                                            <option value="{{ $fijo->id }}">
                                                {{ $fijo->producto?->nombre ?? 'Sin nombre' }} — Clave: {{ $fijo->clave }} ({{ $fijo->producto?->ubicacion?->nombre ?? 'Sin ubicación' }})
                                            </option>
                                        @empty
                                            <option value="" disabled>No hay activos fijos registrados en el sistema</option>
                                        @endforelse
                                    </select>
                                    <small class="text-muted d-block mt-1">Si el artículo no aparece aquí, puedes darlo de alta en la pestaña "Registrar Nuevo Activo Fijo".</small>
                                </div>

                                <!-- Selector de Vehículo (Oculto inicialmente) -->
                                <div class="col-12 d-none" id="grupoSelectVehiculo">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold mb-0">Seleccionar Vehículo <span class="text-danger">*</span></label>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" id="contadorVehiculosBadge" style="font-size: 0.75rem;">
                                            {{ count($vehiculosDisponibles) }} disponibles
                                        </span>
                                    </div>

                                    <!-- Barra de Búsqueda con Lupa para Vehículos -->
                                    <div class="input-group mb-2 shadow-xs rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-white bg-opacity-10 border-end-0 text-primary">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="filtroBuscarVehiculo" 
                                               class="form-control border-start-0 border-end-0 ps-1" 
                                               placeholder="Buscar vehículo por marca, modelo o placas..." 
                                               autocomplete="off">
                                        <button class="btn btn-outline-secondary border-start-0 d-none" 
                                                type="button" 
                                                id="btnLimpiarFiltroVehiculo" 
                                                onclick="limpiarFiltroVehiculo()"
                                                title="Limpiar búsqueda">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <select class="form-select rounded-3" name="item_id" id="selectItemVehiculo" disabled>
                                        <option value="" disabled selected>Selecciona un vehículo de flotilla...</option>
                                        @forelse($vehiculosDisponibles as $veh)
                                            <option value="{{ $veh->id }}">
                                                {{ $veh->marca }} {{ $veh->modelo }} ({{ $veh->año ?? 'N/A' }}) — Placas: {{ $veh->placas }}
                                            </option>
                                        @empty
                                            <option value="" disabled>No hay vehículos registrados en el sistema</option>
                                        @endforelse
                                    </select>
                                </div>

                                <!-- Proyecto Asignado (Nodo Group PR) -->
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold mb-0">
                                            <i class="fas fa-project-diagram text-primary me-1"></i>Proyecto Asignado
                                        </label>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal" style="font-size: 0.72rem;">Opcional — Nodo PR</span>
                                    </div>
                                    <select class="form-select rounded-3 select-proyecto-nodo" name="proyecto" id="selectProyectoAsignar">
                                        <option value="">-- Sin proyecto asignado / General --</option>
                                    </select>
                                    <small class="text-muted" style="font-size: 0.75rem;">Vincula este activo o vehículo a un proyecto activo de la empresa.</small>
                                </div>

                                <!-- Comentario / Motivo -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Comentario / Motivo de Asignación</label>
                                    <input type="text" class="form-control rounded-3" name="comentario" placeholder="Ej. Asignado para labores diarias / Home Office..." maxlength="255">
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- 2. PESTAÑA: REGISTRAR NUEVO ACTIVO FIJO -->
                    <div class="tab-pane fade" id="panel-registrar-nuevo" role="tabpanel" aria-labelledby="tab-registrar-nuevo">
                        <form action="{{ route('articulos.cargo.registrar') }}" method="POST" id="formRegistrarCargo">
                            @csrf
                            <div class="row g-3">
                                <!-- Nombre del Artículo -->
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Nombre del Artículo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control rounded-3" name="nombre" placeholder="Ej. Laptop Dell Latitude 5420, Silla Ergonómica..." required maxlength="150">
                                </div>

                                <!-- Responsable -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Empleado a Cargo <span class="text-danger">*</span></label>
                                    @if(puedeAsignarArticulos())
                                        <select class="form-select rounded-3" name="responsable_id" required>
                                            <option value="{{ session('usuario')->id }}" selected>{{ session('usuario')->nombre }} (Yo)</option>
                                            @foreach($todosUsuarios as $usr)
                                                @if($usr->id != session('usuario')->id)
                                                    <option value="{{ $usr->id }}">{{ $usr->nombre }} {{ $usr->apellido ?? '' }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="form-control bg-light rounded-3 text-truncate">
                                            <i class="fas fa-user-check text-success me-1"></i>
                                            <strong>{{ session('usuario')->nombre }}</strong> (Tú)
                                        </div>
                                        <input type="hidden" name="responsable_id" value="{{ session('usuario')->id }}">
                                    @endif
                                </div>

                                <!-- Descripción -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Descripción o Especificaciones</label>
                                    <textarea class="form-control rounded-3" name="descripcion" rows="2" placeholder="Detalles como procesador, RAM, color, número de serie de fábrica..."></textarea>
                                </div>

                                <!-- Marca y Modelo -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Marca</label>
                                    <input type="text" class="form-control rounded-3" name="marca" placeholder="Ej. Dell, HP, Herman Miller..." maxlength="100">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Modelo</label>
                                    <input type="text" class="form-control rounded-3" name="modelo" placeholder="Ej. Latitude 5420, Aeron..." maxlength="100">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Número de Serie</label>
                                    <input type="text" class="form-control rounded-3" name="serie" placeholder="Ej. SN-98234-XYZ..." maxlength="100">
                                </div>

                                <!-- Código de Barras -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Código de Barras / Clave</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-barcode"></i></span>
                                        <input type="text" class="form-control" id="codigoBarraCargo" name="codigoBarra" placeholder="Escanear o autogenerar..." maxlength="100">
                                        <button class="btn btn-outline-primary" type="button" onclick="generarCodigoCargo()" title="Autogenerar código único">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Opcional. Si lo dejas vacío, puedes generarlo automáticamente con el botón mágico.</small>
                                </div>

                                <!-- Precio estimado -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Precio Estimado ($ MXN)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">$</span>
                                        <input type="number" step="0.01" class="form-control" name="precio" placeholder="0.00" min="0" max="99999999.99">
                                    </div>
                                    <small class="text-muted">Opcional.</small>
                                </div>

                                <!-- Empresa Interna -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Empresa <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="empresa_id" required>
                                        <option value="" disabled selected>Selecciona una empresa...</option>
                                        @foreach($empresas as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Ubicación -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Ubicación / Área <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="ubicacion_id" required>
                                        <option value="" disabled selected>Selecciona una ubicación...</option>
                                        @foreach($ubicaciones as $ub)
                                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Estado y Calidad -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Estado Operativo <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="estado" required>
                                        <option value="activo" selected>Activo / En uso</option>
                                        <option value="reparacion">En Reparación</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Condición / Calidad <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" name="calidad" required>
                                        <option value="bueno" selected>Bueno</option>
                                        <option value="regular">Regular</option>
                                        <option value="malo">Malo</option>
                                    </select>
                                </div>

                                <!-- Proyecto Asignado (Nodo Group PR) -->
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold mb-0">
                                            <i class="fas fa-project-diagram text-primary me-1"></i>Proyecto Asignado
                                        </label>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-normal" style="font-size: 0.72rem;">Opcional — Nodo PR</span>
                                    </div>
                                    <select class="form-select rounded-3 select-proyecto-nodo" name="proyecto" id="selectProyectoRegistrar">
                                        <option value="">-- Sin proyecto asignado / General --</option>
                                    </select>
                                    <small class="text-muted" style="font-size: 0.75rem;">Asocia este activo a un proyecto activo de la empresa desde su alta.</small>
                                </div>

                                <!-- Comentario -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Comentario Adicional</label>
                                    <input type="text" class="form-control rounded-3" name="comentario" placeholder="Ej. Equipo entregado para soporte / Nuevo ingreso..." maxlength="255">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer Fijo (Siempre Visible, Nunca se Corta) -->
            <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <div class="d-flex gap-2">
                    <!-- Botón para Tab 1 (Asignar existente) -->
                    <button type="submit" form="formAsignarCargo" id="btnSubmitCargoAsignar" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="fas fa-check-circle"></i>
                        <span>Poner a mi cargo</span>
                    </button>
                    <!-- Botón para Tab 2 (Registrar nuevo) -->
                    <button type="submit" form="formRegistrarCargo" id="btnSubmitCargoRegistrar" class="btn btn-success rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm d-none">
                        <i class="fas fa-save"></i>
                        <span>Registrar y Poner a mi cargo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Quitar Producto de Cargo -->
<div class="modal fade" id="modalQuitarProductoCargo" tabindex="-1" aria-labelledby="modalQuitarProductoCargoLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Header del Modal -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px;">
                        <i class="fas fa-user-minus fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalQuitarProductoCargoLabel">Retirar Artículo de Cargo</h5>
                        <small class="text-white-50">Devolución o liberación de resguardo personal</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('articulos.cargo.quitar') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo" id="quitarInputTipo">
                <input type="hidden" name="item_id" id="quitarInputItemId">

                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="fas fa-exclamation-triangle mt-1 flex-shrink-0"></i>
                        <div>
                            <span>¿Confirmas que deseas retirar este artículo de tu custodia personal?</span>
                            <div class="fw-bold mt-1 text-dark" id="quitarNombreArticulo">...</div>
                        </div>
                    </div>

                    @if(puedeAsignarArticulos())
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reasignar a: <span class="badge bg-primary ms-1">Reasignación</span></label>
                            <select class="form-select rounded-3" name="nuevo_responsable_id">
                                <option value="1" selected>Almacén General (Sin responsable asignado)</option>
                                @foreach($todosUsuarios as $u)
                                    @if($u->id != 1)
                                        <option value="{{ $u->id }}">{{ $u->nombre }} {{ $u->apellido ?? '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="nuevo_responsable_id" value="1">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-info-circle me-1"></i> El artículo se devolverá al almacén general y quedará libre de tu resguardo.
                        </p>
                    @endif

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Motivo de Devolución / Retiro</label>
                        <input type="text" class="form-control rounded-3" name="motivo" placeholder="Ej. Cambio de equipo, fin de resguardo, mantenimiento..." required maxlength="255">
                    </div>
                </div>

                <div class="modal-footer p-3 bg-light border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 d-inline-flex align-items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        <span>Confirmar Retiro</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reubicar modales directamente en <body> para evitar que queden atrapados detrás del backdrop
    const modalCargo = document.getElementById('modalAgregarProductoACargo');
    if (modalCargo && modalCargo.parentElement !== document.body) {
        document.body.appendChild(modalCargo);
    }
    const modalQuitar = document.getElementById('modalQuitarProductoCargo');
    if (modalQuitar && modalQuitar.parentElement !== document.body) {
        document.body.appendChild(modalQuitar);
    }

    const tabAsignar = document.getElementById('tab-asignar-existente');
    const tabRegistrar = document.getElementById('tab-registrar-nuevo');
    const btnAsignar = document.getElementById('btnSubmitCargoAsignar');
    const btnRegistrar = document.getElementById('btnSubmitCargoRegistrar');

    if (tabAsignar && tabRegistrar && btnAsignar && btnRegistrar) {
        tabAsignar.addEventListener('shown.bs.tab', function() {
            btnAsignar.classList.remove('d-none');
            btnRegistrar.classList.add('d-none');
        });
        tabRegistrar.addEventListener('shown.bs.tab', function() {
            btnAsignar.classList.add('d-none');
            btnRegistrar.classList.remove('d-none');
        });
    }

    inicializarBuscadoresCargo();

    if (modalCargo) {
        modalCargo.addEventListener('shown.bs.modal', function() {
            inicializarBuscadoresCargo();
            const inputFijo = document.getElementById('filtroBuscarFijo');
            if (inputFijo && !inputFijo.closest('.d-none')) {
                setTimeout(() => inputFijo.focus(), 100);
            }
        });
    }
});

let listaOriginalFijos = [];
let listaOriginalVehiculos = [];

function normalizarTexto(txt) {
    return (txt || '').toString().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
}

function inicializarBuscadoresCargo() {
    const selectFijo = document.getElementById('selectItemFijo');
    const selectVeh = document.getElementById('selectItemVehiculo');

    if (selectFijo && listaOriginalFijos.length === 0) {
        listaOriginalFijos = Array.from(selectFijo.options)
            .filter(opt => opt.value !== "")
            .map(opt => ({ value: opt.value, text: opt.text }));
    }

    if (selectVeh && listaOriginalVehiculos.length === 0) {
        listaOriginalVehiculos = Array.from(selectVeh.options)
            .filter(opt => opt.value !== "")
            .map(opt => ({ value: opt.value, text: opt.text }));
    }

    const inputFijo = document.getElementById('filtroBuscarFijo');
    if (inputFijo && !inputFijo.dataset.bound) {
        inputFijo.dataset.bound = "1";
        inputFijo.addEventListener('input', function() {
            filtrarOpcionesFijo(this.value);
        });
    }

    const inputVeh = document.getElementById('filtroBuscarVehiculo');
    if (inputVeh && !inputVeh.dataset.bound) {
        inputVeh.dataset.bound = "1";
        inputVeh.addEventListener('input', function() {
            filtrarOpcionesVehiculo(this.value);
        });
    }
}

function filtrarOpcionesFijo(criterio) {
    const selectFijo = document.getElementById('selectItemFijo');
    const btnLimpiar = document.getElementById('btnLimpiarFiltroFijo');
    const badge = document.getElementById('contadorFijosBadge');
    if (!selectFijo) return;

    if (btnLimpiar) {
        if (criterio.trim().length > 0) {
            btnLimpiar.classList.remove('d-none');
        } else {
            btnLimpiar.classList.add('d-none');
        }
    }

    const normCriterio = normalizarTexto(criterio);
    const palabras = normCriterio.split(/\s+/).filter(p => p.length > 0);

    const coincidencias = listaOriginalFijos.filter(item => {
        if (palabras.length === 0) return true;
        const normItem = normalizarTexto(item.text);
        return palabras.every(palabra => normItem.includes(palabra));
    });

    selectFijo.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.disabled = true;

    if (coincidencias.length === 0) {
        defaultOpt.selected = true;
        defaultOpt.textContent = '❌ Sin activos para "' + criterio.trim() + '"';
        selectFijo.appendChild(defaultOpt);
    } else {
        defaultOpt.textContent = palabras.length > 0 
            ? 'Selecciona un activo (' + coincidencias.length + ' encontrados)...' 
            : 'Selecciona un activo disponible...';
        defaultOpt.selected = (coincidencias.length !== 1);
        selectFijo.appendChild(defaultOpt);

        coincidencias.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.text;
            if (coincidencias.length === 1) {
                opt.selected = true;
            }
            selectFijo.appendChild(opt);
        });
    }

    if (badge) {
        badge.textContent = palabras.length > 0 
            ? coincidencias.length + ' encontrados' 
            : listaOriginalFijos.length + ' disponibles';
    }
}

function limpiarFiltroFijo() {
    const inputFijo = document.getElementById('filtroBuscarFijo');
    if (inputFijo) {
        inputFijo.value = '';
        filtrarOpcionesFijo('');
        inputFijo.focus();
    }
}

function filtrarOpcionesVehiculo(criterio) {
    const selectVeh = document.getElementById('selectItemVehiculo');
    const btnLimpiar = document.getElementById('btnLimpiarFiltroVehiculo');
    const badge = document.getElementById('contadorVehiculosBadge');
    if (!selectVeh) return;

    if (btnLimpiar) {
        if (criterio.trim().length > 0) {
            btnLimpiar.classList.remove('d-none');
        } else {
            btnLimpiar.classList.add('d-none');
        }
    }

    const normCriterio = normalizarTexto(criterio);
    const palabras = normCriterio.split(/\s+/).filter(p => p.length > 0);

    const coincidencias = listaOriginalVehiculos.filter(item => {
        if (palabras.length === 0) return true;
        const normItem = normalizarTexto(item.text);
        return palabras.every(palabra => normItem.includes(palabra));
    });

    selectVeh.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    defaultOpt.disabled = true;

    if (coincidencias.length === 0) {
        defaultOpt.selected = true;
        defaultOpt.textContent = ' Sin vehículos para "' + criterio.trim() + '"';
        selectVeh.appendChild(defaultOpt);
    } else {
        defaultOpt.textContent = palabras.length > 0 
            ? 'Selecciona un vehículo (' + coincidencias.length + ' encontrados)...' 
            : 'Selecciona un vehículo de flotilla...';
        defaultOpt.selected = (coincidencias.length !== 1);
        selectVeh.appendChild(defaultOpt);

        coincidencias.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.value;
            opt.textContent = item.text;
            if (coincidencias.length === 1) {
                opt.selected = true;
            }
            selectVeh.appendChild(opt);
        });
    }

    if (badge) {
        badge.textContent = palabras.length > 0 
            ? coincidencias.length + ' encontrados' 
            : listaOriginalVehiculos.length + ' disponibles';
    }
}

function limpiarFiltroVehiculo() {
    const inputVeh = document.getElementById('filtroBuscarVehiculo');
    if (inputVeh) {
        inputVeh.value = '';
        filtrarOpcionesVehiculo('');
        inputVeh.focus();
    }
}

function cambiarTipoArticuloCargo(tipo) {
    const grupoFijo = document.getElementById('grupoSelectFijo');
    const grupoVeh = document.getElementById('grupoSelectVehiculo');
    const selectFijo = document.getElementById('selectItemFijo');
    const selectVeh = document.getElementById('selectItemVehiculo');

    if (tipo === 'fijo') {
        grupoFijo.classList.remove('d-none');
        grupoVeh.classList.add('d-none');
        selectFijo.disabled = false;
        selectVeh.disabled = true;
        const inputFijo = document.getElementById('filtroBuscarFijo');
        if (inputFijo) setTimeout(() => inputFijo.focus(), 50);
    } else {
        grupoFijo.classList.add('d-none');
        grupoVeh.classList.remove('d-none');
        selectFijo.disabled = true;
        selectVeh.disabled = false;
        const inputVeh = document.getElementById('filtroBuscarVehiculo');
        if (inputVeh) setTimeout(() => inputVeh.focus(), 50);
    }
}

function abrirModalQuitarCargo(tipo, id, nombre) {
    document.getElementById('quitarInputTipo').value = tipo;
    document.getElementById('quitarInputItemId').value = id;
    document.getElementById('quitarNombreArticulo').textContent = nombre;

    const modal = new bootstrap.Modal(document.getElementById('modalQuitarProductoCargo'));
    modal.show();
}

async function generarCodigoCargo() {
    try {
        const input = document.getElementById('codigoBarraCargo');
        const res = await fetch("{{ route('api.generar_codigo_barra') }}");
        const json = await res.json();
        if (json.success && json.data?.codigo) {
            input.value = json.data.codigo;
        } else {
            const rand = '2026' + Math.floor(10000000 + Math.random() * 90000000);
            input.value = rand;
        }
    } catch(e) {
        const rand = '2026' + Math.floor(10000000 + Math.random() * 90000000);
        document.getElementById('codigoBarraCargo').value = rand;
    }
}

// Cargar proyectos de Nodo Group en los selectores correspondientes
let _proyectosNodoCargados = false;
async function cargarProyectosNodo() {
    if (_proyectosNodoCargados) return;
    const selects = document.querySelectorAll('.select-proyecto-nodo');
    if (!selects.length) return;

    try {
        const res = await fetch("{{ route('api.proyectos.nodo') }}");
        const data = await res.json();
        if (data.success && Array.isArray(data.data) && data.data.length > 0) {
            _proyectosNodoCargados = true;
            selects.forEach(sel => {
                const valorActual = sel.value;
                sel.innerHTML = '<option value="">-- Sin proyecto asignado / General --</option>';
                data.data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.valor;
                    opt.textContent = p.label;
                    if (p.valor === valorActual) opt.selected = true;
                    sel.appendChild(opt);
                });
            });
        }
    } catch(err) {
        console.warn('No se pudo cargar la lista de proyectos de Nodo:', err);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    cargarProyectosNodo();
    const modalCargo = document.getElementById('modalAgregarProductoCargo');
    if (modalCargo) {
        modalCargo.addEventListener('show.bs.modal', function() {
            if (!_proyectosNodoCargados) cargarProyectosNodo();
        });
    }
});
</script>
