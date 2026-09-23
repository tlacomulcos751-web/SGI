@php
use Illuminate\Pagination\LengthAwarePaginator;

$currentPage = request()->get('page', 1);
$perPage = 5;

$currentItems = $movimientos->slice(($currentPage - 1) * $perPage, $perPage)->values();

$paginatedMovimientos = new LengthAwarePaginator(
$currentItems,
$movimientos->count(),
$perPage,
$currentPage,
['path' => request()->url(), 'query' => request()->query()]
);
@endphp

<div class="card border-0 shadow-lg mb-4 {{ $class ?? '' }} animate__animated animate__fadeIn" id="movimientos-container">
    <div class="card-header bg-gradient-primary py-3 position-relative overflow-hidden">
        <div class="bg-pattern"></div>
        <h5 class="mb-0 fw-semibold position-relative">
            <i class="fas fa-exchange-alt me-2 animate__animated animate__swing animate__slow animate__infinite"></i>
            <span class="text-shadow">Movimiento realizados</span>
        </h5>
    </div>

    <div class="card-body p-4">
        @if($paginatedMovimientos->isEmpty())
        <div class="text-center py-5 animate__animated animate__pulse">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted fs-5">No hay movimientos registrados.</p>
        </div>
        @else
        <ul class="list-group list-group-flush timeline">
            @foreach($paginatedMovimientos as $index => $mov)
            <li class="list-group-item animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 0.1 }}">
                <div class="timeline-item">
                    <div class="timeline-badge">
                        <i class="fas fa-circle text-primary"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-user-circle text-primary me-2 fs-5"></i>
                            @if(isset($enlaceUsuario) && $enlaceUsuario && $mov->usuario && esSuperAdmin())
                            <strong>
                                <a href="{{route('usuarios.ver', $mov->usuario->id) }}"
                                    class="text-decoration-none text-primary hover-underline"
                                    title="Ver usuario: {{ $mov->usuario->nombreCompleto() }}">
                                    {{ $mov->usuario->nombreCompleto() }}
                                </a>
                            </strong>
                            @else
                            <strong class="text-dark">{{ $mov->usuario->nombreCompleto() }}</strong>
                            @endif
                        </div>
                        @php
                        $ruta = "";
                        switch ($mov->categoria){
                        case 'fijos':
                        $ruta = route('productos_fijos.viewFijos', $mov->id_producto);
                        break;
                        case 'consumibles':
                        $ruta = route('productos_consumibles.view', $mov->id_producto);
                        break;
                        case 'compraventa':
                        $ruta = route('productos_compra_venta.view', $mov->id_producto);
                        break;
                        case 'vehiculo':
                        $ruta = route('vehiculos.ver', $mov->id_producto);
                        break;
                        }
                        @endphp
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="me-2">realizó la acción</span>
                            <span class="badge bg-primary-gradient animate__animated animate__pulse animate__infinite animate__slower">
                                {{ strtolower($mov->accion) }}
                            </span>
                            <span class="me-2">al producto</span>
                            <span class="text-secondary ms-2">
                                <a href="{{ $ruta }}" class="text-decoration-none text-primary hover-underline">
                                    <i class="fas fa-box-open me-1"></i>
                                    <strong>{{ $mov->nombreProducto() }}</strong>
                                </a>
                            </span>
                            <span class="text-muted ms-2">
                                <i class="far fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }} a las
                                {{ \Carbon\Carbon::parse($mov->fecha)->format('H:i:s') }}
                            </span>
                        </div>

                        @if($mov->comentario)
                        <div class="mt-3 p-3 bg-light rounded animate__animated animate__fadeIn animate__delay-1s">
                            <div class="d-flex align-items-center text-secondary">
                                <i class="fas fa-comment-dots me-2"></i>
                                <em class="me-2">Comentario:</em>
                                <span class="fst-italic">"{{ $mov->comentario }}"</span>
                            </div>
                        </div>
                        @endif
                        @if($mov->documentacion)
                        <div class="mt-2">
                            <a href="{{ asset($mov->documentacion) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary animate__animated animate__bounceIn animate__delay-2s">
                                <i class="fas fa-file-alt me-1"></i> Ver documento
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    <div class="card-footer bg-transparent">
        <div class="d-flex justify-content-center">
            {!! $paginatedMovimientos->withPath(request()->url())->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>

<style>
    /* Estilos personalizados */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
    }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }

    .text-shadow {
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd' opacity='0.2'%3E%3Cg fill='%23ffffff' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .hover-underline {
        position: relative;
    }

    .hover-underline:after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: currentColor;
        transition: width 0.3s ease;
    }

    .hover-underline:hover:after {
        width: 100%;
    }

    /* Estilos para la línea de tiempo */
    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline:before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 2px;
        background: linear-gradient(to bottom, #4e73df, #224abe);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-badge {
        position: absolute;
        left: -40px;
        top: 0;
        width: 40px;
        text-align: center;
        font-size: 1.5em;
    }

    .timeline-content {
        padding: 10px 15px;
        background: var(--bg-card);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const wrapper = document.getElementById("movimientos-container");

        // Efecto de carga inicial
        setTimeout(() => {
            wrapper.classList.add("animate__fadeIn");
        }, 100);

        // Manejo de la paginación con animación
        wrapper.addEventListener("click", function(e) {
            if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                e.preventDefault();

                const url = e.target.getAttribute('href');
                if (!url) return;

                // Agregar animación de salida
                wrapper.classList.remove("animate__fadeIn");
                wrapper.classList.add("animate__fadeOut");

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Extrae solo el HTML del componente y reemplaza el wrapper
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const movimientosHtml = doc.querySelector('#movimientos-container');

                        if (movimientosHtml) {
                            setTimeout(() => {
                                wrapper.innerHTML = movimientosHtml.outerHTML;
                                // Agregar animación de entrada
                                wrapper.classList.remove("animate__fadeOut");
                                wrapper.classList.add("animate__fadeIn");

                                // Scroll suave
                                window.scrollTo({
                                    top: wrapper.offsetTop - 100,
                                    behavior: 'smooth'
                                });
                            }, 300);
                        }
                    })
                    .catch(err => console.error('Error cargando movimientos paginados:', err));
            }
        });
    });
</script>