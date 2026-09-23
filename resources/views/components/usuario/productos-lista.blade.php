@props([
'productos',
'usuario',
'titulo' => 'Productos del Usuario',
'rutaDetalle' => fn($producto) => route('productos_fijos.viewFijos', $producto->id),
])

<div id="usuario-productos-wrapper" class="mb-4">
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-semibold text-primary">
                    <i class="fas fa-boxes me-2"></i>{{ $titulo }}
                </h5>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                    Total: {{ $usuario->numeroACargo() }}
                </span>
            </div>
            @if($usuario->numeroACargo() > 0)
            <div class="btn-group shadow-sm">
                <a href="{{ route('responsivas.responsable', ['id' => $usuario->id, 'formato' => 'pdf']) }}" target="_blank" class="btn btn-sm btn-success rounded-start-pill px-3 text-white">
                    <i class="fas fa-file-signature me-1"></i> Carta Responsiva Consolidada
                </a>
                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split rounded-end-pill px-2 text-white" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Opciones de responsiva</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('responsivas.responsable', ['id' => $usuario->id, 'formato' => 'pdf']) }}" target="_blank">
                            <span class="badge bg-danger bg-opacity-10 text-danger me-2"><i class="fas fa-file-pdf"></i></span>
                            <div>
                                <span class="d-block fw-semibold">Ver / Imprimir PDF</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Documento formal para firma del empleado</small>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('responsivas.responsable', ['id' => $usuario->id, 'formato' => 'word']) }}">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-file-word"></i></span>
                            <div>
                                <span class="d-block fw-semibold">Descargar Word (.docx)</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Editable con tabla de bienes asignados</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <div class="card-body p-0">
            @if($productos->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($productos as $producto)
                <div class="list-group-item border-0 py-3 px-4 hover-bg-light transition-all">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="flex-grow-1">
                            <a href="{{ $rutaDetalle($producto) }}"
                                class="text-decoration-none text-dark hover-text-primary">
                                <h6 class="mb-1 fw-semibold">{{ $producto->producto->nombre }}</h6>
                            </a>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $producto->producto->ubicacion->nombre ?? 'Sin ubicación' }}
                                </span>
                                @if($producto->producto->codigoBarra)
                                <span class="badge bg-info bg-opacity-10 text-info small">
                                    <i class="fas fa-barcode me-1"></i>
                                    {{ $producto->producto->codigoBarra }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end d-flex align-items-center gap-2">
                            <a href="{{ route('responsivas.fijo', ['id' => $producto->id, 'formato' => 'pdf']) }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-success rounded-pill px-2"
                                title="Carta Responsiva de este activo">
                                <i class="fas fa-file-signature"></i>
                            </a>
                            <a href="{{ $rutaDetalle($producto) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Ver <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($productos->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <nav aria-label="Paginación de productos">
                    <ul class="pagination justify-content-center mb-0">
                        {{-- Previous --}}
                        @if($productos->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $productos->previousPageUrl() }}" rel="prev">&laquo;</a>
                        </li>
                        @endif

                        {{-- Pages --}}
                        @foreach(range(1, $productos->lastPage()) as $i)
                        @if($i == $productos->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                        @else
                        <li class="page-item"><a class="page-link" href="{{ $productos->url($i) }}">{{ $i }}</a></li>
                        @endif
                        @endforeach

                        {{-- Next --}}
                        @if($productos->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $productos->nextPageUrl() }}" rel="next">&raquo;</a>
                        </li>
                        @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
            @endif

            @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-box-open fa-3x text-secondary opacity-25"></i>
                </div>
                <h5 class="text-muted mb-2">No hay productos asignados</h5>
                <p class="text-muted small">Este usuario no tiene productos asignados actualmente</p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }

    .hover-text-primary:hover {
        color: var(--bs-primary) !important;
    }

    .transition-all {
        transition: all 0.2s ease;
    }

    .page-item.active .page-link {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
    }

    .page-link {
        color: var(--bs-primary);
    }
</style>