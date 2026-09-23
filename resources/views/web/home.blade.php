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

    @if(esSuperAdmin())
    <!-- Bento Grid Estadísticas (Solo Super Administradores) -->
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

        <!-- Usuarios (Solo Super Admin) -->
        <a href="{{ route('usuarios.index') }}" class="bento-card success animate__animated animate__fadeInUp text-decoration-none" style="animation-delay: 0.3s; color: inherit;">
            <div class="bento-card-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-label">Usuarios Activos</div>
                <div class="stat-value" id="stat-usuarios">{{ $usuariosActivos }}</div>
                <div style="font-size: 0.8rem; color: var(--text-tertiary); margin-top: 8px;">En el sistema</div>
            </div>
        </a>
        
        <!-- Tarjeta Grande Informativa CTA -->
        <div class="bento-card info bento-large animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
            <div style="display: flex; justify-content: space-between; align-items: center; height: 100%;">
                <div>
                    <h3 style="margin-bottom: 12px; font-weight: 700; color: var(--text-main);">Gestión Inteligente</h3>
                    <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 450px; margin-bottom: 24px; line-height: 1.6;">
                        Administra el inventario corporativo, controla los activos fijos, consumibles, vehículos y da seguimiento a todas las operaciones del grupo en tiempo real.
                    </p>
                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        @if (tienePermiso('fijos - leer') || tienePermiso('consumible - leer') || tienePermiso('compra/venta - leer'))
                        <a href="{{ route('productos') }}" class="btn btn-primary"><i class="fas fa-boxes"></i> Ver Inventario</a>
                        @endif
                        <a href="{{ route('usuarios.perfil') }}" class="btn btn-secondary"><i class="fas fa-user-cog"></i> Mi Perfil</a>
                    </div>
                </div>
                <div class="d-none d-md-block" style="padding-right: 20px;">
                    <i class="fas fa-cubes" style="font-size: 8rem; color: var(--info-bg); transform: rotate(-10deg);"></i>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Cards para Agregar Tipos de Productos -->
    <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1" style="color: var(--text-main);">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Registrar Productos en el Inventario
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
</div>

@if(esSuperAdmin())
<!--AJAX para obtener datos del dashboard (Solo Super Admin)  -->
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