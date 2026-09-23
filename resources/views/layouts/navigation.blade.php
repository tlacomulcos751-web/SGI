<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Iconos FontAwesome y Bootstrap Icons CDN (Cargados después de Vite para máxima prioridad) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* Garantizar compatibilidad absoluta de iconos FontAwesome y Bootstrap Icons */
        .fa, .fas, .far, .fa-solid, .fa-regular {
            font-family: "Font Awesome 6 Free", "Font Awesome 7 Free", "FontAwesome" !important;
            font-weight: 900;
        }
        .fab, .fa-brands {
            font-family: "Font Awesome 6 Brands", "Font Awesome 7 Brands" !important;
            font-weight: 400;
        }
        .bi, [class^="bi-"], [class*=" bi-"] {
            font-family: "bootstrap-icons" !important;
        }

        /* MAGIA PARA EL LOGO DEL NAVBAR */
        .navbar-brand img {
            mix-blend-mode: multiply;
            transition: filter 0.3s ease;
        }
        [data-theme="dark"] .navbar-brand img {
            filter: invert(1) brightness(1.5);
            mix-blend-mode: screen;
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('logos/NAO2.png') }}">

    {{-- Script de tema inline: se ejecuta inmediatamente sin depender de Vite --}}
    <script>
        (function() {
            // Aplicar tema guardado al instante (evita flash de tema incorrecto)
            var savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.setAttribute('data-bs-theme', savedTheme);

            // Definir toggleTheme globalmente
            window.toggleTheme = function() {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', next);
                document.documentElement.setAttribute('data-bs-theme', next);
                localStorage.setItem('theme', next);
                // Actualizar icono del botón
                var btn = document.querySelector('.theme-toggle');
                if (btn) {
                    btn.innerHTML = next === 'light'
                        ? '<i class="fas fa-moon"></i>'
                        : '<i class="fas fa-sun"></i>';
                }
            };

            // Actualizar icono cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                var btn = document.querySelector('.theme-toggle');
                if (btn) {
                    var theme = document.documentElement.getAttribute('data-theme');
                    btn.innerHTML = theme === 'light'
                        ? '<i class="fas fa-moon"></i>'
                        : '<i class="fas fa-sun"></i>';
                }
            });
        })();
    </script>
</head>

<body>
    <!-- LOADING OVERLAY GLOBAL -->
    <div id="sgi-loading-overlay" aria-hidden="true">
        <div class="sgi-loading-box">
            <div class="sgi-spinner">
                <div class="sgi-spinner-ring"></div>
                <div class="sgi-spinner-ring sgi-spinner-ring--2"></div>
            </div>
            <p class="sgi-loading-title">Procesando...</p>
            <p class="sgi-loading-subtitle">Por favor espere, no cierre esta ventana.</p>
        </div>
    </div>
    <!-- FIN LOADING OVERLAY -->


    <div class="container-fluid px-3 px-xl-4" style="max-width: 1440px;">
        <!-- Navbar optimizada -->
        <nav class="navbar navbar-expand-xl navbar-light mb-4 shadow-sm">
            <div class="container-fluid px-0">
                <a class="navbar-brand brand me-3" href="{{ route('web.home') }}">
                    <img src="{{ asset('logos/nodo.jpeg') }}" alt="Logo Nodo" style="height: 42px; width: auto; border-radius: 8px;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        {{-- Inicio --}}
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="{{ route('web.home') }}">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>

                        {{-- Empresas --}}
                        @php
                        $mostrarEmpresas = tienePermiso('leerEmpresaInterna') || tienePermiso('leerEmpresaExterna') || tienePermiso('leerUbicaciones');
                        @endphp
                        @if ($mostrarEmpresas)
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-building me-2"></i> Empresas
                            </a>
                            <ul class="dropdown-menu shadow-lg dropdown-menu-end">
                                @if (tienePermiso('leerEmpresaInterna'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('empresa.index') }}">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-cube"></i></span>
                                        <span class="flex-grow-1">Nuestras Empresas</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                                @if (tienePermiso('leerEmpresaExterna'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('empresa.indexExterna') }}">
                                        <span class="badge bg-success bg-opacity-10 text-success me-2"><i class="fas fa-tint"></i></span>
                                        <span class="flex-grow-1">Empresas Externas</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                                @if (tienePermiso('leerUbicaciones'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('empresa.ubicaciones') }}">
                                        <span class="badge bg-info bg-opacity-10 text-info me-2"><i class="fas fa-map-marker-alt"></i></span>
                                        <span class="flex-grow-1">Ubicaciones</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        {{-- Productos --}}
                        @php
                        $mostrarProductos = tienePermiso('fijos - leer') || tienePermiso('consumible - leer') || tienePermiso('compra/venta - leer') || tienePermiso('vehiculo - leer');
                        @endphp
                        @if ($mostrarProductos)
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-boxes me-2"></i> Productos
                            </a>
                            <ul class="dropdown-menu shadow-lg dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('productos') }}">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2"><i class="fas fa-cubes"></i></span>
                                        <span class="flex-grow-1">Todos los Productos</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider mx-3">
                                </li>

                                @if (tienePermiso('fijos - leer'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('productos.indexFijos') }}">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-cube"></i></span>
                                        <span class="flex-grow-1">Activos Fijos</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif

                                @if (tienePermiso('consumible - leer'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('productos_consumibles.index') }}">
                                        <span class="badge bg-danger bg-opacity-10 text-danger me-2"><i class="fas fa-utensils"></i></span>
                                        <span class="flex-grow-1">Consumibles</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif

                                @if (tienePermiso('compra/venta - leer'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('productos_compra_venta.index') }}">
                                        <span class="badge bg-warning bg-opacity-10 text-warning me-2"><i class="fas fa-exchange-alt"></i></span>
                                        <span class="flex-grow-1">Compra/Venta</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif

                                @if(tienePermiso('vehiculo - leer'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('vehiculos.index') }}">
                                        <span class="badge bg-info bg-opacity-10 text-info me-2"><i class="fas fa-car"></i></span>
                                        <span class="flex-grow-1">Vehículos</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif

                        {{-- Usuarios y Roles (Solo Super Admin) --}}
                        @if (esSuperAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-users me-2"></i> Usuarios
                            </a>
                            <ul class="dropdown-menu shadow-lg dropdown-menu-end">
                                @if (tienePermiso('leerRol'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('roles.index') }}">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-user-tag"></i></span>
                                        <span class="flex-grow-1">Roles</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                                @if (esSuperAdmin() && tienePermiso('leerUsuarios'))
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('usuarios.index') }}">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary me-2"><i class="fas fa-users"></i></span>
                                        <span class="flex-grow-1">Usuarios</span>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        {{-- Categorías / Etiquetas --}}
                        @if (tienePermiso('leerEtiquetas') || esSuperAdmin())
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-custom dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-tags me-2"></i> Categorías
                            </a>
                            <ul class="dropdown-menu shadow-lg dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('etiquetas.index') }}">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2"><i class="fas fa-tags"></i></span>
                                        <div class="flex-grow-1">
                                            <span class="d-block fw-semibold">Categorías del Sistema</span>
                                            <small class="text-muted" style="font-size: 0.75rem;">Administrar clasificación de productos</small>
                                        </div>
                                        <i class="fas fa-arrow-right text-muted ms-2"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        {{-- Configuración --}}
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="{{route('usuarios.perfil')}}">
                                <i class="bi bi-person-fill-gear"></i> Mi Perfil
                            </a>
                        </li>
                    </ul>

                    {{-- Notificaciones de Stock --}}
                    @if(isset($alertasStockCritico))
                        <div class="nav-item dropdown me-3 list-unstyled">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);">
                                <i class="fas fa-bell fs-5"></i>
                                @if($alertasStockCritico->count() > 0)
                                    <span class="position-absolute top-10 start-90 translate-middle badge rounded-pill bg-danger shadow-sm animate__animated animate__pulse animate__infinite" style="font-size: 0.65em;">
                                        {{ $alertasStockCritico->count() }}
                                        <span class="visually-hidden">alertas</span>
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 320px; max-height: 400px; overflow-y: auto;">
                                <li><h6 class="dropdown-header text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Stock Crítico (<= 5)</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                @forelse($alertasStockCritico as $alerta)
                                    <li>
                                        <a class="dropdown-item py-2 text-wrap hover-bg-light" href="{{ route('productos', ['nombre' => $alerta->nombre]) }}">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <strong class="me-2" style="font-size: 0.9em;">{{ $alerta->nombre }}</strong>
                                                <span class="badge bg-danger rounded-pill">{{ $alerta->existencia }}</span>
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.8em;">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $alerta->ubicacion_nombre ?? 'Sin ubicación' }}
                                            </small>
                                        </a>
                                    </li>
                                @empty
                                    <li><span class="dropdown-item text-muted text-center py-3"><i class="fas fa-check-circle text-success me-2"></i>Todo el stock es estable.</span></li>
                                @endforelse
                            </ul>
                        </div>
                    @endif

                    {{-- Botón Pistola Lectora / Escáner de Código de Barras --}}
                    <div class="d-flex align-items-center me-2">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 d-flex align-items-center gap-2 shadow-xs" 
                                type="button" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEscanerPistola"
                                title="Escanear con Pistola Lectora (Atajo: F2 o Alt+B)">
                            <i class="fas fa-barcode"></i>
                            <span class="d-none d-xl-inline fw-medium">Escanear</span>
                            <kbd class="bg-primary text-white border-0 py-0 px-1 rounded small d-none d-xxl-inline" style="font-size:0.68rem;">F2</kbd>
                        </button>
                    </div>

                    {{-- Theme Toggle --}}
                    <div class="d-flex align-items-center me-3">
                        <button class="theme-toggle" onclick="toggleTheme()" type="button" aria-label="Cambiar tema">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>

                    {{-- Cerrar sesión --}}
                    <form action="{{ route('logout') }}" method="POST" class="d-flex m-0 p-0 flex-shrink-0">
                        @csrf
                        <button type="submit" class="logout-btn rounded-pill d-flex align-items-center gap-2">
                            <i class="fas fa-sign-out-alt"></i> <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Contenido principal con animación -->
        <main class="main-content mb-5">
            <div class="breadcrumbs">
                {{ Breadcrumbs::render() }}
            </div>

            @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @elseif(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>

    {{-- Modales Globales de Códigos de Barras y Pistola Lectora --}}
    @include('components.modal-escaner-pistola')
    @include('components.modal-imprimir-etiqueta')

    @stack('modals')

    {{-- Librerías Globales para Códigos de Barras --}}
    <script src="{{ asset('js/JsBarcode.all.min.js') }}"></script>
    <script src="{{ asset('js/barcode-helper.js') }}"></script>

    <script>
        // Asegurar que al cerrar modales nunca quede un backdrop huérfano
        document.addEventListener('hidden.bs.modal', function () {
            if (!document.querySelector('.modal.show')) {
                document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });
    </script>
    @stack('scripts')
</body>

</html>