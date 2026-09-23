@php
use Illuminate\Pagination\LengthAwarePaginator;

$currentPage = request()->get('page', 1);
$perPage = 5;

$currentItems = $vehiculo->mantenimientos->sortByDesc('fecha')->slice(($currentPage - 1) * $perPage, $perPage)->values();

$mantenimientos = new LengthAwarePaginator(
$currentItems,
$vehiculo->mantenimientos->count(),
$perPage,
$currentPage,
['path' => request()->url(), 'query' => request()->query()]
);
@endphp

<div class="card border-0 shadow-lg overflow-hidden mb-4 animate__animated animate__fadeIn" id="mantenimientos-container">
    <div class="card-header py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold text-primary">
            <i class="fas fa-tools me-2"></i>Mantenimientos
        </h5>
        @if(tienePermiso('vehiculo - modificar') && !$vehiculo->eliminado)
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarMantenimiento">
            <i class="fas fa-plus-circle me-1"></i> Añadir
        </button>
        @else
        <button class="btn btn-sm btn-secondary" disabled>
            <i class="fas fa-plus-circle me-1"></i> Añadir
        </button>
        @endif
    </div>

    <div class="card-body p-4">
        @if($mantenimientos->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-tools fa-3x mb-3 text-muted"></i>
            <p class="mb-0 text-muted">No hay mantenimientos registrados para este vehículo.</p>
        </div>
        @else
        <div class="maintenance-list">
            @foreach($mantenimientos as $item)
            <div class="maintenance-item mb-3 p-3 border rounded-3 card">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $item->tipo_servicio }}</h6>
                        <div class="small text-muted mb-2">
                            <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y') }} |
                            <i class="fas fa-road me-1"></i> {{ number_format($item->kilometraje) }} km
                        </div>
                        <p class="mb-1 text-muted">{{ $item->descripcion }}</p>
                        <p class="mb-0 text-muted">
                            <i class="fas fa-wrench me-1"></i> Taller: <strong>{{ $item->taller }}</strong>
                        </p>
                    </div>
                </div>
                @if(isset($item->comentario) && $item->comentario)
                <div class="mt-2 pt-2 border-top">
                    <small class="text-muted"><i class="fas fa-sticky-note me-1"></i> {{ $item->comentario }}</small>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card-footer bg-transparent">
        <div class="d-flex justify-content-center">
            {!! $mantenimientos->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>

<style>
    .maintenance-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 147, 74, 0.2);
        transition: all 0.3s ease;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const wrapper = document.getElementById("mantenimientos-container");

        wrapper.addEventListener("click", function(e) {
            if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                e.preventDefault();

                const url = e.target.getAttribute('href');
                if (!url) return;

                wrapper.classList.remove("animate__fadeIn");
                wrapper.classList.add("animate__fadeOut");

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.querySelector('#mantenimientos-container');

                        if (newContent) {
                            setTimeout(() => {
                                wrapper.innerHTML = newContent.innerHTML;
                                wrapper.classList.remove("animate__fadeOut");
                                wrapper.classList.add("animate__fadeIn");
                                window.scrollTo({
                                    top: wrapper.offsetTop - 100,
                                    behavior: 'smooth'
                                });
                            }, 300);
                        }
                    })
                    .catch(error => console.error('Error al paginar mantenimientos:', error));
            }
        });
    });
</script>