@props([
'vehiculos',
'usuario',
'titulo' => 'Vehículos del Usuario',
'rutaDetalle' => fn($vehiculo) => route('vehiculos.ver', $vehiculo->id),
])

<div id="usuario-vehiculos-wrapper" class="mb-4">
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold text-primary">
                    <i class="fas fa-car me-2"></i>{{ $titulo }}
                </h5>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fs-6">
                Total: {{ $vehiculos->total() }}
            </span>
        </div>

        <div class="card-body p-0">
            @if($vehiculos->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($vehiculos as $vehiculo)
                <div class="list-group-item border-0 py-3 px-4 hover-bg-light transition-all">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="flex-grow-1">
                            <a href="{{ $rutaDetalle($vehiculo) }}"
                                class="text-decoration-none text-dark hover-text-primary">
                                <h6 class="mb-1 fw-semibold">
                                    {{ $vehiculo->marca }} {{ $vehiculo->modelo }} - {{ $vehiculo->placas }}
                                </h6>
                            </a>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $vehiculo->ubicacion->nombre ?? 'Sin ubicación' }}
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary small">
                                    {{ ucfirst($vehiculo->color) }} / {{ $vehiculo->año }}
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ $rutaDetalle($vehiculo) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Ver <i class="fas fa-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($vehiculos->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <nav aria-label="Paginación de vehículos">
                    <ul class="pagination justify-content-center mb-0">
                        {{-- Previous --}}
                        @if($vehiculos->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $vehiculos->previousPageUrl() }}" rel="prev">&laquo;</a>
                        </li>
                        @endif

                        {{-- Pages --}}
                        @foreach(range(1, $vehiculos->lastPage()) as $i)
                        @if($i == $vehiculos->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                        @else
                        <li class="page-item"><a class="page-link" href="{{ $vehiculos->url($i) }}">{{ $i }}</a></li>
                        @endif
                        @endforeach

                        {{-- Next --}}
                        @if($vehiculos->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $vehiculos->nextPageUrl() }}" rel="next">&raquo;</a>
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
                    <i class="fas fa-car-slash fa-3x text-secondary opacity-25"></i>
                </div>
                <h5 class="text-muted mb-2">No hay vehículos asignados</h5>
                <p class="text-muted small">Este usuario no tiene vehículos asignados actualmente</p>
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