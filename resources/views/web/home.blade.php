@extends('layouts.navigation')

@section('title', 'Inicio - Nodo Group')

@section('content')
<div class="container main-content">
    <!-- Welcome Section Premium -->
    <div class="welcome-section animate__animated animate__fadeInUp">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 class="welcome-title">Bienvenido, <span style="color: var(--primary);">{{ nombreCompletoUsuario() }}</span>!</h1>
                <p class="welcome-subtitle">Panel principal del gestor de inventario NODO.</p>
            </div>
            <a href="https://sgi.naoproyectos.com/guias/GUIA%20DEL%20PRIMER%20USO%20DEL%20SISTEMA%20DE%20GESTI%C3%93N%20DE%20INVENTARIOS%20(SIG).pdf" 
               class="btn btn-primary" 
               target="_blank" 
               rel="noopener noreferrer">
               <i class="fas fa-file-pdf"></i> Guía del Sistema
            </a>
        </div>
    </div>

    <!-- Bento Grid Estadísticas -->
    <div class="bento-grid">
        <!-- Registros -->
        <div class="bento-card primary animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="bento-card-icon"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="stat-label">Registros</div>
                <div class="stat-value" id="stat-registros">{{ $registros7Dias }}</div>
                <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 8px;">Últimos 7 días</div>
            </div>
        </div>

        <!-- Movimientos -->
        <div class="bento-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="bento-card-icon"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <div class="stat-label">Movimientos</div>
                <div class="stat-value" id="stat-movimientos">{{ $movimientos7Dias }}</div>
                <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 8px;">Últimos 7 días</div>
            </div>
        </div>

        @if(esSuperAdmin() || tienePermiso('leerUsuarios'))
        <!-- Usuarios (Solo Super Admin) -->
        <a href="{{ route('usuarios.index') }}" class="bento-card success animate__animated animate__fadeInUp text-decoration-none" style="animation-delay: 0.3s; color: inherit;">
            <div class="bento-card-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-label">Usuarios Activos</div>
                <div class="stat-value" id="stat-usuarios">{{ $usuariosActivos }}</div>
                <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 8px;">En el sistema</div>
            </div>
        </a>
        @else
        <!-- Artículos a mi cargo Stat -->
        <a href="#seccionCosasACargo" class="bento-card success animate__animated animate__fadeInUp text-decoration-none" style="animation-delay: 0.3s; color: inherit;">
            <div class="bento-card-icon"><i class="fas fa-user-shield"></i></div>
            <div>
                <div class="stat-label">Bajo Mi Resguardo</div>
                <div class="stat-value">{{ $totalCosasACargo ?? 0 }}</div>
                <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 8px;">Artículos y vehículos</div>
            </div>
        </a>
        @endif
        
        <!-- Tarjeta Grande Informativa CTA -->
        <div class="bento-card info bento-large animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div style="display: flex; justify-content: space-between; align-items: center; height: 100%;">
                <div>
                    <h3 style="margin-bottom: 12px; font-weight: 700; color: var(--text-main);">Gestión Inteligente</h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 450px; margin-bottom: 24px; line-height: 1.6;">
                        Administra el inventario corporativo, controla los activos fijos, consumibles, vehículos y da seguimiento a todas las operaciones del grupo en tiempo real.
                    </p>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <a href="{{ route('productos') }}" class="btn btn-primary"><i class="fas fa-boxes"></i> Ver Inventario</a>
                        <a href="{{ route('usuarios.perfil') }}" class="btn btn-secondary"><i class="fas fa-user-cog"></i> Mi Perfil</a>
                    </div>
                </div>
                <div class="d-none d-md-block" style="padding-right: 20px;">
                    <i class="fas fa-cubes" style="font-size: 8rem; color: var(--info-bg); transform: rotate(-10deg);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Cards para Registrar Artículos en el Inventario -->
    <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.15s;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Registrar Artículos en el Inventario
                </h4>
                <p class="text-muted small mb-0">Selecciona el tipo de producto que deseas agregar al sistema:</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Card 1: Activos Fijos -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card primary h-100 shadow-sm p-4 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bento-card-icon" style="width: 52px; height: 52px; font-size: 1.6rem;">
                                <i class="fas fa-archive"></i>
                            </div>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small fw-semibold">
                                Activo Permanente
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2" style="color: var(--text-main);">Activos Fijos</h4>
                        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; min-height: 48px;">
                            Equipos de cómputo, mobiliario, maquinaria y bienes duraderos asignados con responsable y número de serie.
                        </p>
                    </div>

                    <div class="pt-3 border-top mt-3" style="border-color: var(--border-color) !important;">
                        @if (tienePermiso('fijos - insertar'))
                        <a href="{{ route('productos.indexFijos', ['crear' => 1]) }}" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm mb-2">
                            <i class="fas fa-plus-circle"></i> Agregar Activo Fijo
                        </a>
                        @endif
                        @if (tienePermiso('fijos - leer'))
                        <a href="{{ route('productos.indexFijos') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.85rem;">
                            <i class="fas fa-boxes"></i> Ver Activos Fijos
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Consumibles -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card warning h-100 shadow-sm p-4 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bento-card-icon" style="width: 52px; height: 52px; font-size: 1.6rem;">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill small fw-semibold">
                                Insumos y Suministros
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2" style="color: var(--text-main);">Consumibles</h4>
                        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; min-height: 48px;">
                            Materiales de uso recurrente, papelería, cafetería e insumos operativos con control de existencias mínimas y máximas.
                        </p>
                    </div>

                    <div class="pt-3 border-top mt-3" style="border-color: var(--border-color) !important;">
                        @if (tienePermiso('consumible - insertar'))
                        <a href="{{ route('productos_consumibles.index', ['crear' => 1]) }}" class="btn text-white w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm mb-2" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none;">
                            <i class="fas fa-plus-circle"></i> Agregar Consumible
                        </a>
                        @endif
                        @if (tienePermiso('consumible - leer'))
                        <a href="{{ route('productos_consumibles.index') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.85rem;">
                            <i class="fas fa-layer-group"></i> Ver Consumibles
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 3: Compra / Venta -->
            <div class="col-lg-4 col-md-6">
                <div class="bento-card success h-100 shadow-sm p-4 d-flex flex-column justify-content-between position-relative">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bento-card-icon" style="width: 52px; height: 52px; font-size: 1.6rem;">
                                <i class="fas fa-cart-plus"></i>
                            </div>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill small fw-semibold">
                                Comercialización
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2" style="color: var(--text-main);">Compra-Venta</h4>
                        <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; min-height: 48px;">
                            Mercancía para venta, adquisición para proyectos específicos o comercialización con empresas y clientes externos.
                        </p>
                    </div>

                    <div class="pt-3 border-top mt-3" style="border-color: var(--border-color) !important;">
                        @if (tienePermiso('compra/venta - insertar'))
                        <a href="{{ route('productos_compra_venta.index', ['crear' => 1]) }}" class="btn text-white w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm mb-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                            <i class="fas fa-plus-circle"></i> Agregar Compra-Venta
                        </a>
                        @endif
                        @if (tienePermiso('compra/venta - leer'))
                        <a href="{{ route('productos_compra_venta.index') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.85rem;">
                            <i class="fas fa-store"></i> Ver Compra-Venta
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2.1 Apartado de Notificaciones: Alerta de Mantenimientos Preventivos (≤ 1 mes / Vencidos) -->
    @php
        $rolUserHome = (int)(Session::get('usuario')?->id_rol ?? 0);
        $esStaffHome = esSuperAdmin() || esAdmin() || ($rolUserHome > 0 && $rolUserHome <= 3);
    @endphp
    @if($esStaffHome || tienePermiso('fijos - leer') || (isset($totalMantenimientosProximos) && $totalMantenimientosProximos > 0))
    <div id="seccion-mantenimientos-alertas" class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.15s;">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" 
             style="background: var(--card-bg, #fff); border-left: 5px solid {{ ($totalMantenimientosProximos ?? 0) > 0 ? '#f59e0b' : '#10b981' }} !important; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08) !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" 
                             style="width: 54px; height: 54px; font-size: 1.5rem; background-color: {{ ($totalMantenimientosProximos ?? 0) > 0 ? 'rgba(245, 158, 11, 0.15)' : 'rgba(16, 185, 129, 0.12)' }}; color: {{ ($totalMantenimientosProximos ?? 0) > 0 ? '#d97706' : '#10b981' }};">
                            <iconify-icon icon="lucide:wrench" width="26" height="26"></iconify-icon>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="fw-bold mb-0 text-dark">
                                    Alertas de Mantenimiento Preventivo (≤ 1 mes)
                                </h4>
                                @if(($totalMantenimientosProximos ?? 0) > 0)
                                <span class="badge rounded-pill px-3 py-1 shadow-xs text-white" style="background-color: #f59e0b;">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $totalMantenimientosProximos }} {{ $totalMantenimientosProximos === 1 ? 'activo requiere' : 'activos requieren' }} servicio
                                </span>
                                @else
                                <span class="badge bg-success rounded-pill px-3 py-1">
                                    <i class="fas fa-check me-1"></i> Todos los activos al día
                                </span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0 mt-1">
                                Monitoreo inteligente de equipos y activos fijos con servicio preventivo programado a 30 días o menos de su vencimiento.
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-xs text-dark" onclick="activarPushNotificaciones()">
                            <i class="fas fa-bell me-1 text-warning"></i> Alertas Push
                        </button>
                        <a href="{{ route('productos.indexFijos') }}" class="btn btn-sm rounded-pill px-3 shadow-xs text-white" style="background-color: #f59e0b;">
                            <i class="fas fa-desktop me-1"></i> Ver Activos Fijos
                        </a>
                    </div>
                </div>

                @if(($totalMantenimientosProximos ?? 0) > 0)
                <!-- Indicadores Rápidos con Colores que Resaltan -->
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="p-2.5 rounded-3 bg-light d-flex align-items-center justify-content-between border" style="border-left: 4px solid #ef4444 !important;">
                            <span class="text-muted small"><i class="fas fa-clock text-danger me-1"></i> Vencidos (Atención Inmediata):</span>
                            <span class="badge bg-danger fw-bold fs-6">{{ $mantenimientosVencidos ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2.5 rounded-3 bg-light d-flex align-items-center justify-content-between border" style="border-left: 4px solid #ea580c !important;">
                            <span class="text-muted small"><i class="fas fa-hourglass-half text-warning me-1"></i> Urgentes (1 a 15 días):</span>
                            <span class="badge text-white fw-bold fs-6" style="background-color: #ea580c;">{{ $mantenimientosUrgentes ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2.5 rounded-3 bg-light d-flex align-items-center justify-content-between border" style="border-left: 4px solid #f59e0b !important;">
                            <span class="text-muted small"><i class="fas fa-calendar-alt me-1" style="color: #f59e0b;"></i> Próximos (16 a 30 días):</span>
                            <span class="badge text-white fw-bold fs-6" style="background-color: #f59e0b;">{{ $mantenimientosPorVenir ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Grilla de Activos Próximos a Mantenimiento -->
                <div class="row g-3">
                    @foreach($mantenimientosProximos->take(6) as $itemMnt)
                    @php
                        $diasRest = (int)$itemMnt->dias_restantes;
                        $fechaProxima = \Carbon\Carbon::parse($itemMnt->proxima_fecha);
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between shadow-xs" 
                             style="background: #fffdfa; border-left: 4px solid {{ $diasRest < 0 ? '#ef4444' : ($diasRest <= 15 ? '#ea580c' : '#f59e0b') }} !important;">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-1.5">
                                    <h6 class="fw-bold mb-0 text-dark text-truncate me-2" title="{{ $itemMnt->producto_nombre }}">
                                        <a href="{{ route('productos_fijos.viewFijos', $itemMnt->producto_fijo_id) }}" class="text-decoration-none text-dark hover-primary">
                                            {{ $itemMnt->producto_nombre }}
                                        </a>
                                    </h6>
                                    @if($diasRest < 0)
                                        <span class="badge bg-danger rounded-pill flex-shrink-0" style="font-size: 0.72rem;">
                                            Vencido ({{ abs($diasRest) }}d)
                                        </span>
                                    @elseif($diasRest == 0)
                                        <span class="badge text-white rounded-pill flex-shrink-0" style="background-color: #ea580c; font-size: 0.72rem;">
                                            ¡Vence Hoy!
                                        </span>
                                    @elseif($diasRest <= 15)
                                        <span class="badge text-dark rounded-pill flex-shrink-0" style="background-color: #fde047; font-size: 0.72rem;">
                                            En {{ $diasRest }} días
                                        </span>
                                    @else
                                        <span class="badge text-white rounded-pill flex-shrink-0" style="background-color: #f59e0b; font-size: 0.72rem;">
                                            En {{ $diasRest }} días
                                        </span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                        {{ $itemMnt->clave }}
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="far fa-calendar-alt text-warning me-1"></i>{{ $fechaProxima->format('d/m/Y') }}
                                    </span>
                                </div>

                                <div class="small text-muted mb-1" style="font-size: 0.78rem;">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <span>{{ $itemMnt->ubicacion_nombre ?? 'Almacén General' }}</span>
                                </div>

                                @if(!empty($itemMnt->responsable_nombre))
                                <div class="small text-muted mb-2" style="font-size: 0.78rem;">
                                    <i class="fas fa-user-circle text-primary me-1"></i>
                                    <span>{{ $itemMnt->responsable_nombre }}</span>
                                </div>
                                @endif

                                @if(!empty($itemMnt->tipo_servicio))
                                <div class="p-1.5 px-2 bg-white rounded border text-muted small mb-2" style="font-size: 0.72rem;">
                                    <i class="fas fa-wrench me-1 text-warning"></i>
                                    <span>{{ $itemMnt->tipo_servicio }} (cada {{ $itemMnt->frecuencia_meses ?? 6 }}m)</span>
                                </div>
                                @endif
                            </div>

                            <div class="pt-2 border-top d-flex justify-content-between align-items-center mt-2">
                                <a href="{{ route('productos.fijos.generarMantenimiento', $itemMnt->producto_fijo_id) }}" 
                                   class="btn btn-xs btn-outline-secondary d-inline-flex align-items-center gap-1" 
                                   style="font-size: 0.75rem; padding: 2px 8px;" title="Descargar Orden de Mantenimiento en Word">
                                    <iconify-icon icon="lucide:file-text" width="13" height="13" class="text-primary"></iconify-icon>
                                    <span>Orden Word</span>
                                </a>

                                <a href="{{ route('productos_fijos.viewFijos', $itemMnt->producto_fijo_id) }}" 
                                   class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1 shadow-xs" 
                                   style="font-size: 0.75rem; padding: 2px 10px; background-color: #0f172a; border-color: #0f172a;">
                                    <span>Ver y registrar</span>
                                    <i class="fas fa-chevron-right" style="font-size: 0.65rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 bg-light bg-opacity-50 rounded-3 border border-dashed">
                    <iconify-icon icon="lucide:check-circle-2" width="36" height="36" class="text-success mb-2"></iconify-icon>
                    <h6 class="fw-semibold text-slate-800 mb-1">Mantenimientos al día</h6>
                    <p class="text-muted small mb-0" style="max-width: 480px; margin: 0 auto;">
                        Ningún activo fijo está programado para mantenimiento en los próximos 30 días. El sistema te notificará automáticamente con 1 mes de anticipación.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- 2.2 Apartado de Notificaciones / Alertas de Consumibles al 10% -->
    @if(isset($consumiblesCriticos) && ($esStaffHome || tienePermiso('consumible - leer') || ($totalConsumiblesCriticos ?? 0) > 0))
    <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden {{ $totalConsumiblesCriticos > 0 ? 'border-start border-danger border-4' : 'border-start border-success border-4' }}" style="background: var(--card-bg, #fff);">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-3 {{ $totalConsumiblesCriticos > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-success bg-opacity-10 text-success' }}" style="width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                            <i class="fas {{ $totalConsumiblesCriticos > 0 ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="fw-bold mb-0 text-dark">Alerta de Consumibles Críticos (Stock ≤ 10%)</h4>
                                @if($totalConsumiblesCriticos > 0)
                                <span class="badge bg-danger rounded-pill px-3 py-1">{{ $totalConsumiblesCriticos }} productos requieren atención</span>
                                @else
                                <span class="badge bg-success rounded-pill px-3 py-1">Stock saludable</span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0 mt-1">Monitoreo automático de insumos agotados o con existencias mínimas para compra y reabastecimiento.</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" onclick="activarPushNotificaciones()">
                            <i class="fas fa-bell me-1"></i> Activar Notificaciones Push
                        </button>
                        @if (tienePermiso('consumible - leer'))
                        <a href="{{ route('productos_consumibles.index', ['estado' => 'critico']) }}" class="btn btn-sm btn-danger rounded-pill px-3 shadow-xs">
                            <i class="fas fa-exclamation-triangle me-1"></i> Ver Críticos ({{ $totalConsumiblesCriticos }})
                        </a>
                        <a href="{{ route('productos_consumibles.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs">
                            <i class="fas fa-boxes me-1"></i> Todos
                        </a>
                        @endif
                    </div>
                </div>

                @if($totalConsumiblesCriticos > 0)
                <!-- Indicadores Rápidos -->
                <div class="row g-2 mb-3">
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('productos_consumibles.index', ['estado' => 'agotado']) }}" class="text-decoration-none">
                            <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-between border hover-border-danger" style="transition: all 0.2s ease;">
                                <span class="text-muted small"><i class="fas fa-ban text-danger me-1"></i> Completamente Agotados:</span>
                                <span class="badge bg-danger fw-bold fs-6">{{ $consumiblesAgotados }}</span>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('productos_consumibles.index', ['estado' => 'critico']) }}" class="text-decoration-none">
                            <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-between border hover-border-warning" style="transition: all 0.2s ease;">
                                <span class="text-muted small"><i class="fas fa-battery-quarter text-warning me-1"></i> Por Agotarse (1 a 10):</span>
                                <span class="badge bg-warning text-dark fw-bold fs-6">{{ $consumiblesPorAgotar }}</span>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Grilla de Productos Faltantes / Críticos -->
                <div class="row g-3">
                    @foreach($consumiblesCriticos->take(6) as $item)
                    <div class="col-lg-4 col-md-6">
                        <div class="border rounded-3 p-3 bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0 text-dark text-truncate me-2" title="{{ $item->nombre }}">
                                        <a href="{{ route('productos_consumibles.view', $item->consumible_id) }}" class="text-decoration-none text-dark hover-primary">
                                            {{ $item->nombre }}
                                        </a>
                                    </h6>
                                    @if($item->existencia <= 0)
                                        <span class="badge bg-danger px-2 py-1 rounded-pill">0 unidades</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">{{ $item->existencia }} rest.</span>
                                    @endif
                                </div>
                                <small class="text-muted d-block mb-2">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $item->ubicacion_nombre ?? 'Almacén General' }}
                                </small>
                            </div>
                            
                            <div>
                                @php
                                    $porcentaje = min(100, max(0, ($item->existencia / 20) * 100));
                                    $colorBarra = $item->existencia <= 0 ? 'bg-danger' : 'bg-warning';
                                @endphp
                                <div class="progress" style="height: 6px;" title="Nivel de stock estimado">
                                    <div class="progress-bar {{ $colorBarra }}" role="progressbar" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($totalConsumiblesCriticos > 6)
                <div class="text-center mt-3 pt-2 border-top">
                    <a href="{{ route('productos_consumibles.index', ['estado' => 'critico']) }}" class="text-decoration-none fw-semibold small text-danger">
                        Ver los {{ $totalConsumiblesCriticos - 6 }} consumibles críticos restantes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @endif

                @else
                <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex align-items-center mb-0 mt-2 py-2">
                    <i class="fas fa-check-circle text-success me-2 fs-5"></i>
                    <span class="small text-success fw-medium">Excelente estado: todos los consumibles cuentan con existencias superiores al 10%.</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- 3. Sección: Artículos a mi Cargo -->
    <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.25s;" id="seccionCosasACargo">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: var(--bg-card, #fff);">
            <!-- Header de la Sección -->
            <div class="card-header border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-3" style="background: var(--bg-card, #fff);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary" style="width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-main">Artículos a mi cargo</h4>
                            @if(($totalCosasACargo ?? 0) > 0)
                                <span class="badge bg-primary rounded-pill px-3 py-1">{{ $totalCosasACargo }} artículos a mi cargo</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-25 text-muted border rounded-pill px-3 py-1">Sin artículos a mi cargo</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0 mt-1">Vehículos, equipo de cómputo y mobiliario bajo tu resguardo personal.</p>
                    </div>
                </div>

            </div>

            <div class="card-body p-4">
                @if(($totalCosasACargo ?? 0) > 0)
                <!-- Pestañas de Navegación: Vehículos vs Activos Fijos -->
                <ul class="nav nav-pills mb-4 gap-2" id="pillsCargoTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 shadow-xs" 
                                id="pills-vehiculos-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#pills-vehiculos" 
                                type="button" 
                                role="tab" 
                                aria-controls="pills-vehiculos" 
                                aria-selected="true">
                            <i class="fas fa-car"></i>
                            <span>Vehículos a mi Cargo</span>
                            <span class="badge bg-primary text-white rounded-pill ms-1">{{ $misVehiculos->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-2 shadow-xs" 
                                id="pills-fijos-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#pills-fijos" 
                                type="button" 
                                role="tab" 
                                aria-controls="pills-fijos" 
                                aria-selected="false">
                            <i class="fas fa-laptop"></i>
                            <span>Mobiliario y Activos Fijos a mi Cargo</span>
                            <span class="badge bg-secondary text-white rounded-pill ms-1">{{ $misActivosFijos->count() }}</span>
                        </button>
                    </li>
                </ul>

                <!-- Contenido de Pestañas -->
                <div class="tab-content" id="pillsCargoTabContent">
                    <!-- 1. VEHÍCULOS A MI CARGO -->
                    <div class="tab-pane fade show active" id="pills-vehiculos" role="tabpanel" aria-labelledby="pills-vehiculos-tab">
                        @if($misVehiculos->count() > 0)
                        <div class="row g-3">
                            @foreach($misVehiculos as $veh)
                            <div class="col-lg-6 col-xl-4">
                                <div class="card border rounded-3 h-100 shadow-sm overflow-hidden" style="background: var(--bg-card, #ffffff);">
                                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: var(--bg-hover, rgba(0,0,0,0.02));">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-main">
                                                <i class="fas fa-car text-primary me-1"></i> {{ $veh->marca }} {{ $veh->modelo }}
                                            </h6>
                                            <small class="text-muted">Año: {{ $veh->año ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-dark border border-secondary text-white rounded-pill px-2 py-1 font-monospace" style="letter-spacing: 1px;">
                                            {{ $veh->placas }}
                                        </span>
                                    </div>
                                    <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                        <div>
                                            <div class="row g-2 small text-muted mb-2">
                                                <div class="col-6">
                                                    <strong class="text-main">Color:</strong> {{ $veh->color ?? 'N/A' }}
                                                </div>
                                                <div class="col-6">
                                                    <strong class="text-main">Transmisión:</strong> {{ $veh->transmision ?? 'N/A' }}
                                                </div>
                                                <div class="col-12">
                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                    <strong class="text-main">Ubicación:</strong> {{ $veh->ubicacion?->nombre ?? 'Sin ubicación' }}
                                                </div>
                                            </div>

                                            <!-- Estado del Mantenimiento -->
                                            @if($veh->mantenimientos->count() > 0)
                                                @php $ultimoM = $veh->mantenimientos->first(); @endphp
                                                <div class="p-2 rounded bg-warning bg-opacity-10 border-start border-warning border-3 mb-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-warning small" style="font-size: 0.8rem;">
                                                            <i class="fas fa-wrench text-warning me-1"></i> {{ $ultimoM->tipo_servicio }}
                                                        </strong>
                                                        <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.7rem;">
                                                            {{ \Carbon\Carbon::parse($ultimoM->fecha)->format('d/m/Y') }}
                                                        </span>
                                                    </div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                                        {{ number_format($ultimoM->kilometraje) }} km en {{ $ultimoM->taller }}
                                                    </small>
                                                </div>
                                            @else
                                                <div class="p-2 rounded bg-info bg-opacity-10 border-start border-info border-3 mb-2">
                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        <i class="fas fa-check-circle text-info me-1"></i> Sin mantenimientos registrados pendientes
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Acciones del Vehículo -->
                                        <div class="d-flex gap-2 pt-2 border-top mt-2">
                                            <a href="{{ route('vehiculos.generarVale', $veh->id) }}" 
                                               class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 py-1"
                                               style="font-size: 0.75rem;">
                                                <i class="fas fa-file-word me-1"></i> Vale Salida
                                            </a>
                                            <a href="{{ route('vehiculos.generarMantenimiento', $veh->id) }}" 
                                               class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1"
                                               style="font-size: 0.75rem;" 
                                               title="Descargar Orden de Mantenimiento">
                                                <i class="fas fa-wrench"></i>
                                            </a>
                                            <a href="{{ route('vehiculos.ver', $veh->id) }}" 
                                               class="btn btn-outline-info btn-sm rounded-pill px-3 py-1"
                                               style="font-size: 0.75rem;" 
                                               title="Ver detalles del vehículo">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-car-side fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">No tienes vehículos asignados bajo tu custodia actualmente.</p>
                        </div>
                        @endif
                    </div>

                    <!-- 2. ACTIVOS FIJOS A MI CARGO -->
                    <div class="tab-pane fade" id="pills-fijos" role="tabpanel" aria-labelledby="pills-fijos-tab">
                        @if($misActivosFijos->count() > 0)
                        <div class="row g-3">
                            @foreach($misActivosFijos as $fijo)
                            <div class="col-lg-6 col-xl-4">
                                <div class="card border rounded-3 h-100 shadow-sm overflow-hidden" style="background: var(--bg-card, #ffffff);">
                                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: var(--bg-hover, rgba(0,0,0,0.02));">
                                        <h6 class="fw-bold mb-0 text-main text-truncate me-2 d-inline-flex align-items-center gap-1" title="{{ $fijo->producto?->nombre }}">
                                            <iconify-icon icon="lucide:monitor" width="16" height="16" class="text-primary flex-shrink-0"></iconify-icon>
                                            <span class="text-truncate">{{ $fijo->producto?->nombre ?? 'Activo Fijo' }}</span>
                                        </h6>
                                        <span class="badge bg-secondary rounded-pill px-2 py-1 font-monospace" style="font-size: 0.7rem;">
                                            {{ $fijo->clave }}
                                        </span>
                                    </div>
                                    <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                        <div>
                                            <div class="small text-muted mb-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    <iconify-icon icon="lucide:map-pin" width="14" height="14" class="text-danger me-1 flex-shrink-0"></iconify-icon>
                                                    <span class="text-truncate"><strong>Ubicación:</strong> {{ $fijo->producto?->ubicacion?->nombre ?? 'Sin asignar' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <iconify-icon icon="lucide:building-2" width="14" height="14" class="text-primary me-1 flex-shrink-0"></iconify-icon>
                                                    <span class="text-truncate"><strong>Empresa:</strong> {{ $fijo->producto?->empresa?->nombre ?? 'Grupo Nodo' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mt-2">
                                                    @if(strtolower($fijo->estado ?? '') === 'reparacion')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1">
                                                            <iconify-icon icon="lucide:alert-triangle" width="12" height="12"></iconify-icon>
                                                            <span>En Reparación</span>
                                                        </span>
                                                    @elseif(strtolower($fijo->estado ?? '') === 'baja')
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1">
                                                            Estado: Baja
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1">
                                                            <iconify-icon icon="lucide:check-circle-2" width="12" height="12"></iconify-icon>
                                                            <span>Activo</span>
                                                        </span>
                                                    @endif

                                                    <small class="text-muted" style="font-size: 0.75rem;">
                                                        {{ $fijo->fechaEntrada ? 'Asignado: ' . \Carbon\Carbon::parse($fijo->fechaEntrada)->format('d/m/Y') : '' }}
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Estado del Mantenimiento Preventivo -->
                                            @php
                                                $ultMant = $fijo->mantenimientos?->first();
                                            @endphp
                                            @if($ultMant)
                                                @php
                                                    $proximaF = $ultMant->proxima_fecha ? \Carbon\Carbon::parse($ultMant->proxima_fecha) : null;
                                                    $sem = $ultMant->semaforo;
                                                    $diasRest = $ultMant->dias_restantes;
                                                @endphp
                                                @if($sem === 'vencido')
                                                    <div class="p-2 rounded bg-danger bg-opacity-10 border-start border-danger border-3 mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <strong class="text-danger small d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                                                <iconify-icon icon="lucide:alert-circle" width="14" height="14" class="text-danger"></iconify-icon>
                                                                <span>Mant. Vencido</span>
                                                            </strong>
                                                            <span class="badge bg-danger text-white font-monospace" style="font-size: 0.7rem;">
                                                                {{ $proximaF ? $proximaF->format('d/m/Y') : 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                                                            Último: {{ \Carbon\Carbon::parse($ultMant->fecha)->format('d/m/Y') }} (Cada {{ $ultMant->frecuencia_meses ?? 6 }}m)
                                                        </small>
                                                    </div>
                                                @elseif($sem === 'urgente' || $sem === 'proximo')
                                                    <div class="p-2 rounded bg-warning bg-opacity-10 border-start border-warning border-3 mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <strong class="text-dark small d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                                                <iconify-icon icon="lucide:clock" width="14" height="14" class="text-warning"></iconify-icon>
                                                                <span>Próximo Servicio</span>
                                                            </strong>
                                                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.7rem;">
                                                                {{ $proximaF ? $proximaF->format('d/m/Y') : 'N/A' }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                                                            En {{ $diasRest }} días • {{ $ultMant->tipo_servicio }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <div class="p-2 rounded bg-light border-start border-success border-3 mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <strong class="text-dark small d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                                                <iconify-icon icon="lucide:check-circle-2" width="14" height="14" class="text-success"></iconify-icon>
                                                                <span>Al día</span>
                                                            </strong>
                                                            <span class="badge bg-success bg-opacity-10 text-success border border-success font-monospace" style="font-size: 0.7rem;">
                                                                {{ \Carbon\Carbon::parse($ultMant->fecha)->format('d/m/Y') }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted d-block mt-1" style="font-size: 0.73rem;">
                                                            Siguiente: {{ $proximaF ? $proximaF->format('d/m/Y') : 'N/A' }} (Cada {{ $ultMant->frecuencia_meses ?? 6 }}m)
                                                        </small>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="p-2 rounded bg-light border-start border-secondary border-3 mb-2">
                                                    <small class="text-muted d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                        <iconify-icon icon="lucide:wrench" width="14" height="14" class="text-secondary"></iconify-icon>
                                                        <span>Sin mantenimientos preventivos registrados</span>
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2 pt-2 border-top mt-2">
                                            <a href="{{ route('productos.fijos.generarVale', $fijo->id) }}" 
                                               class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 py-1 d-inline-flex align-items-center justify-content-center gap-1"
                                               style="font-size: 0.75rem;">
                                                <iconify-icon icon="lucide:file-down" width="14" height="14"></iconify-icon>
                                                <span>Vale Salida</span>
                                            </a>
                                            <a href="{{ route('productos.fijos.generarMantenimiento', $fijo->id) }}" 
                                               class="btn btn-outline-warning btn-sm rounded-pill px-2 py-1 d-inline-flex align-items-center justify-content-center"
                                               style="font-size: 0.75rem;" 
                                               title="Descargar Orden de Mantenimiento">
                                                <iconify-icon icon="lucide:wrench" width="14" height="14"></iconify-icon>
                                            </a>
                                            <a href="{{ route('productos_fijos.viewFijos', $fijo->id) }}" 
                                               class="btn btn-outline-info btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center justify-content-center"
                                               style="font-size: 0.75rem;" 
                                               title="Ver detalles y mantenimientos">
                                                <iconify-icon icon="lucide:eye" width="15" height="15" class="text-primary"></iconify-icon>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-boxes fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">No tienes mobiliario ni equipo de cómputo asignado bajo tu custodia actualmente.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <!-- Estado Vacío Cuando el Usuario no Tiene Artículos Asignados -->
                <div class="text-center py-4">
                    <div class="rounded-circle bg-light p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 64px; height: 64px;">
                        <i class="fas fa-shield-alt text-muted fa-2x"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Sin artículos a tu cargo actualmente</h6>
                    <p class="text-muted small mb-0" style="max-width: 480px; margin: 0 auto;">
                        Actualmente no tienes vehículos ni activos fijos registrados bajo tu custodia. Cuando el área de almacén o administración te asigne herramientas o unidades, aparecerán listados aquí para tu consulta y control.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

@if(esSuperAdmin() || tienePermiso('leerUsuarios'))
<!--AJAX para obtener datos del dashboard (Super Admin y Administrador)  -->
<script>
document.addEventListener('DOMContentLoaded', async function() {
    try {
        const res = await fetch("{{ route('api.dashboard') }}");
        const json = await res.json();
        if (json.success && json.data) {
            const r = document.getElementById('stat-registros');
            const m = document.getElementById('stat-movimientos');
            const u = document.getElementById('stat-usuarios');
            if (r) r.textContent = json.data.registros_7_dias ?? 0;
            if (m) m.textContent = json.data.movimientos_7_dias ?? 0;
            if (u) u.textContent = json.data.usuarios_activos ?? 0;
        }
    } catch(e) {
        console.error('Error actualizando dashboard API:', e);
    }
});
</script>
@endif
@endsection