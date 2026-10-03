<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Iconos FontAwesome, Bootstrap Icons e Iconify CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    
    <style>
        /* Garantizar visibilidad y compatibilidad del Navbar de Bootstrap */
        .navbar-collapse {
            visibility: visible !important;
        }
        .navbar {
            background-color: var(--bg-card, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            padding: 0.6rem 1rem;
        }
        [data-theme="dark"] .navbar {
            background-color: var(--bg-card, #1e293b);
            border-color: var(--border-color, #334155);
        }
        .nav-link-custom {
            color: var(--text-main, #0f172a) !important;
            font-weight: 500;
            padding: 0.5rem 0.85rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link-custom:hover, .nav-link-custom:focus {
            color: var(--primary, #3b5bdb) !important;
            background-color: var(--bg-hover, rgba(59, 91, 219, 0.08));
        }

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
        iconify-icon {
            display: inline-flex;
            align-self: center;
            vertical-align: -0.125em;
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
        <nav class="navbar navbar-expand-lg mb-4 shadow-sm">
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

                    {{-- Notificaciones de Mantenimiento y Stock Crítico --}}
                    @php
                        $puedeVerConsumibles = esSuperAdmin() || tienePermiso('consumible - leer');
                        $puedeVerMantenimiento = esSuperAdmin() || tienePermiso('fijos - leer');
                        
                        $alertasStockCriticoList = ($puedeVerConsumibles && function_exists('obtenerConsumiblesCriticos')) ? obtenerConsumiblesCriticos() : collect();
                        $countStockCritico = ($puedeVerConsumibles && function_exists('contarConsumiblesCriticos')) ? contarConsumiblesCriticos() : 0;

                        $alertasMntList = ($puedeVerMantenimiento && function_exists('obtenerMantenimientosProximos')) ? obtenerMantenimientosProximos(30, 8) : collect();
                        $countMnt = ($puedeVerMantenimiento && function_exists('contarMantenimientosProximos')) ? contarMantenimientosProximos(30) : 0;

                        $totalAlertas = $countStockCritico + $countMnt;
                    @endphp
                    @if($puedeVerConsumibles || $puedeVerMantenimiento)
                        <div class="nav-item dropdown me-3 list-unstyled">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--text-main);" title="Notificaciones del Sistema: Mantenimientos y Stock">
                                <i class="fas fa-bell fs-5"></i>
                                @if($totalAlertas > 0)
                                    <span class="position-absolute top-10 start-90 translate-middle badge rounded-pill shadow-sm animate__animated animate__pulse animate__infinite" 
                                          style="font-size: 0.65em; background-color: {{ $countStockCritico > 0 ? '#dc3545' : '#f59e0b' }}; color: white;">
                                        {{ $totalAlertas }}
                                        <span class="visually-hidden">alertas</span>
                                    </span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 360px; max-height: 480px; overflow-y: auto;">
                                <li class="p-2 border-bottom bg-white">
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded-pill py-1 d-flex align-items-center justify-content-center gap-1 shadow-xs" onclick="activarPushNotificaciones()">
                                        <i class="fas fa-bell"></i> <span style="font-size: 0.75rem;">Habilitar Alertas Push</span>
                                    </button>
                                </li>

                                {{-- SECCIÓN: Mantenimientos Preventivos Próximos (≤ 1 mes) - Remarcado en Ámbar/Naranja --}}
                                @if($puedeVerMantenimiento)
                                    <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center" style="background-color: #fffbeb; border-left: 4px solid #f59e0b;">
                                        <a href="{{ route('productos.indexFijos') }}" class="fw-bold small text-decoration-none d-flex align-items-center gap-1.5" style="color: #b45309;">
                                            <iconify-icon icon="lucide:wrench" width="14" height="14" class="text-warning"></iconify-icon>
                                            <span>Mantenimiento Próximo (≤ 1 mes)</span>
                                        </a>
                                        @if($countMnt > 0)
                                            <span class="badge rounded-pill shadow-xs" style="background-color: #f59e0b; color: #fff; font-size: 0.7rem;">
                                                {{ $countMnt }} {{ $countMnt === 1 ? 'activo' : 'activos' }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">Al día</span>
                                        @endif
                                    </li>
                                    @forelse($alertasMntList as $alertaMnt)
                                        @php
                                            $diasMnt = (int)$alertaMnt->dias_restantes;
                                            $fechaMntCarbon = \Carbon\Carbon::parse($alertaMnt->proxima_fecha);
                                        @endphp
                                        <li>
                                            <a class="dropdown-item py-2 text-wrap hover-bg-light border-bottom" href="{{ route('productos_fijos.viewFijos', $alertaMnt->producto_fijo_id) }}" style="border-left: 3px solid #f59e0b !important; background-color: #fffdfa;">
                                                <div class="d-flex justify-content-between align-items-start mb-0.5">
                                                    <strong class="me-2 text-truncate text-slate-800" style="font-size: 0.84em; max-width: 185px;" title="{{ $alertaMnt->producto_nombre }}">
                                                        {{ $alertaMnt->producto_nombre }}
                                                    </strong>
                                                    @if($diasMnt < 0)
                                                        <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;" title="Vencido el {{ $fechaMntCarbon->format('d/m/Y') }}">
                                                            Vencido ({{ abs($diasMnt) }}d)
                                                        </span>
                                                    @elseif($diasMnt == 0)
                                                        <span class="badge rounded-pill text-white" style="background-color: #ea580c; font-size: 0.65rem;">
                                                            ¡Hoy!
                                                        </span>
                                                    @elseif($diasMnt <= 15)
                                                        <span class="badge rounded-pill text-dark" style="background-color: #fde047; font-size: 0.65rem;" title="Programado: {{ $fechaMntCarbon->format('d/m/Y') }}">
                                                            En {{ $diasMnt }} días
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill text-white" style="background-color: #f59e0b; font-size: 0.65rem;" title="Programado: {{ $fechaMntCarbon->format('d/m/Y') }}">
                                                            En {{ $diasMnt }} días
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72em;">
                                                    <span class="font-monospace text-slate-600 font-semibold">{{ $alertaMnt->clave }}</span>
                                                    <span><i class="far fa-calendar-alt me-0.5 text-warning"></i> {{ $fechaMntCarbon->format('d/m/Y') }}</span>
                                                </div>
                                                <small class="text-muted d-block text-truncate mt-0.5" style="font-size: 0.72em;">
                                                    <i class="fas fa-map-marker-alt me-1 text-slate-400"></i>{{ $alertaMnt->ubicacion_nombre ?? 'Almacén General' }}
                                                    @if(!empty($alertaMnt->responsable_nombre))
                                                        <span class="mx-1">•</span><i class="fas fa-user me-0.5 text-slate-400"></i>{{ $alertaMnt->responsable_nombre }}
                                                    @endif
                                                </small>
                                            </a>
                                        </li>
                                    @empty
                                        <li>
                                            <div class="dropdown-item text-muted text-center py-2" style="font-size: 0.78rem;">
                                                <iconify-icon icon="lucide:check-circle" width="13" height="13" class="text-success me-1"></iconify-icon>
                                                <span>Todos los activos al día este mes.</span>
                                            </div>
                                        </li>
                                    @endforelse
                                @endif

                                {{-- SECCIÓN: Stock Crítico (≤ 10%) - Rojo --}}
                                @if($puedeVerConsumibles)
                                    <li class="px-3 py-2 bg-light border-bottom border-top d-flex justify-content-between align-items-center">
                                        <a href="{{ route('productos_consumibles.index', ['estado' => 'critico']) }}" class="fw-bold small text-danger text-decoration-none d-flex align-items-center gap-1">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <span>Stock Crítico (≤ 10%)</span>
                                        </a>
                                        <a href="{{ route('productos_consumibles.index', ['estado' => 'critico']) }}" class="badge bg-danger rounded-pill text-decoration-none" title="Ver consumibles críticos">
                                            {{ $countStockCritico }}
                                        </a>
                                    </li>
                                    @forelse($alertasStockCriticoList as $alerta)
                                        <li>
                                            <a class="dropdown-item py-2 text-wrap hover-bg-light border-bottom" href="{{ route('productos_consumibles.index', ['nombre' => $alerta->nombre]) }}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <strong class="me-2 text-truncate" style="font-size: 0.85em; max-width: 190px;">{{ $alerta->nombre }}</strong>
                                                    <span class="badge {{ $alerta->existencia <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                                        {{ $alerta->existencia }} rest.
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block" style="font-size: 0.75em;">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $alerta->ubicacion_nombre ?? 'Almacén general' }}
                                                </small>
                                            </a>
                                        </li>
                                    @empty
                                        <li><span class="dropdown-item text-muted text-center py-2" style="font-size: 0.78rem;"><i class="fas fa-check-circle text-success me-2"></i>Stock de consumibles estable.</span></li>
                                    @endforelse
                                @endif

                                <li class="p-2 bg-light d-flex justify-content-between align-items-center px-3 border-top">
                                    @if($puedeVerMantenimiento)
                                    <a href="{{ route('productos.indexFijos') }}" class="small text-decoration-none fw-semibold" style="color: #b45309;">
                                        <i class="fas fa-tools me-1"></i> Ver Activos
                                    </a>
                                    @endif
                                    @if($puedeVerConsumibles)
                                    <a href="{{ route('productos_consumibles.index') }}" class="small text-decoration-none fw-semibold text-primary">
                                        Consumibles <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    @endif
                                </li>
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

        // Soporte para Notificaciones Push del Navegador
        function activarPushNotificaciones() {
            if (!("Notification" in window)) {
                alert("Este navegador no soporta notificaciones de escritorio.");
                return;
            }

            if (Notification.permission === "granted") {
                enviarPushDemostracion();
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then(function(permission) {
                    if (permission === "granted") {
                        enviarPushDemostracion();
                    }
                });
            } else {
                alert("Las notificaciones están bloqueadas en tu navegador. Puedes habilitarlas en el candado junto a la URL del sitio.");
            }
        }

        function enviarPushDemostracion() {
            const countStock = {{ $countStockCritico ?? 0 }};
            const countMnt = {{ $countMnt ?? 0 }};
            let title = "✅ SGI: Notificaciones Activadas";
            let body = "Las notificaciones automáticas de inventario y mantenimiento están activas en este dispositivo.";
            let redirectUrl = "{{ route('web.home') }}";

            if (countMnt > 0 && countStock > 0) {
                title = "⚠️ SGI: Alertas de Mantenimiento y Stock";
                body = `Atención: Tienes ${countMnt} activo(s) con mantenimiento próximo y ${countStock} consumible(s) en stock crítico.`;
            } else if (countMnt > 0) {
                title = "🛠️ SGI: Alerta de Mantenimiento Próximo (≤ 1 mes)";
                body = `Atención: Tienes ${countMnt} activo(s) fijo(s) que requieren servicio de mantenimiento preventivo este mes.`;
                redirectUrl = "{{ route('web.home') }}#seccion-mantenimientos-alertas";
            } else if (countStock > 0) {
                title = "⚠️ SGI: Alerta de Stock Crítico (≤ 10%)";
                body = `Atención: Tienes ${countStock} productos consumibles con stock en nivel crítico o agotados.`;
                redirectUrl = "{{ route('productos_consumibles.index', ['estado' => 'critico']) }}";
            }

            try {
                const notif = new Notification(title, {
                    body: body,
                    icon: "https://cdn-icons-png.flaticon.com/512/564/564619.png"
                });
                notif.onclick = function() {
                    window.focus();
                    window.location.href = redirectUrl;
                };
            } catch (e) {
                console.log("Error creando notificación:", e);
            }
        }
    </script>
    @stack('scripts')
</body>

</html>