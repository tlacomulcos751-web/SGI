@php
use Illuminate\Pagination\LengthAwarePaginator;

$currentPage = request()->get('page', 1);
$perPage = 5;

$currentItems = $vehiculo->documentaciones->sortByDesc('created_at')->slice(($currentPage - 1) * $perPage, $perPage)->values();

$documentaciones = new LengthAwarePaginator(
$currentItems,
$vehiculo->documentaciones->count(),
$perPage,
$currentPage,
['path' => request()->url(), 'query' => request()->query()]
);
@endphp

<div class="card border-0 shadow-lg overflow-hidden mb-4 animate__animated animate__fadeIn" id="documentaciones-container">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 fw-semibold text-primary">
            <i class="fas fa-file-alt me-2"></i>Documentación
        </h5>
    </div>

    <div class="card-body p-4">
        @if($documentaciones->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">No hay documentación asociada a este vehículo.</p>
        </div>
        @else
        <div class="documentation-list">
            @foreach($documentaciones as $doc)
            <div class="documentation-item mb-3 p-3 rounded-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="document-icon me-3">
                            @if(str_contains(strtolower($doc->tipo), 'circulación'))
                            <i class="fas fa-car text-primary"></i>
                            @elseif(str_contains(strtolower($doc->tipo), 'seguro'))
                            <i class="fas fa-shield-alt text-success"></i>
                            @elseif(str_contains(strtolower($doc->tipo), 'verificación'))
                            <i class="fas fa-check-circle text-warning"></i>
                            @else
                            <i class="fas fa-file-alt text-info"></i>
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">{{ $doc->tipo }}</h6>
                            <div class="d-flex flex-wrap align-items-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2 mb-1">
                                    <i class="fas fa-hashtag me-1"></i>{{ $doc->numero_documento }}
                                </span>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary me-2 mb-1">
                                    <i class="far fa-calendar-alt me-1"></i>{{ $doc->vigencia_inicio }}
                                </span>
                                @if($doc->vigencia_fin)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary me-2 mb-1">
                                    <i class="far fa-calendar-check me-1"></i>{{ $doc->vigencia_fin }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($doc->estatus == 'vigente')
                        <span class="badge bg-opacity-20 text-success">
                            <i class="fas fa-check-circle me-1"></i> {{ $doc->estatus }}
                        </span>
                        @elseif($doc->estatus == 'vencido')
                        <span class="badge bg-opacity-20 text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ $doc->estatus }}
                        </span>
                        @else
                        <span class="badge bg-opacity-20 text-secondary">
                            <i class="fas fa-question-circle me-1"></i> {{ $doc->estatus }}
                        </span>
                        @endif
                    </div>
                </div>
                @if($doc->notas)
                <div class="mt-2 pt-2 border-top">
                    <small class="text-muted"><i class="fas fa-sticky-note me-1"></i> {{ $doc->notas }}</small>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card-footer bg-transparent">
        <div class="d-flex justify-content-center">
            {!! $documentaciones->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const wrapper = document.getElementById("documentaciones-container");

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
                        const newContent = doc.querySelector('#documentaciones-container');

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
                    .catch(error => console.error('Error cargando documentación paginada:', error));
            }
        });
    });
</script>