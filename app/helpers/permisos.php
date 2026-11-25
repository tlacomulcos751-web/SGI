<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('esSuperAdmin')) {
    function esSuperAdmin(): bool
    {
        $usuario = Session::get('usuario');
        return $usuario && (int)($usuario->id_rol ?? 0) === 1;
    }
}

if (!function_exists('esAdmin')) {
    function esAdmin(): bool
    {
        if (esSuperAdmin()) {
            return true;
        }
        $usuario = Session::get('usuario');
        return $usuario && (int)($usuario->id_rol ?? 0) === 4;
    }
}

if (!function_exists('puedeAsignarArticulos')) {
    function puedeAsignarArticulos(): bool
    {
        if (esSuperAdmin()) {
            return true;
        }

        $usuario = Session::get('usuario');
        if (!$usuario) {
            return false;
        }

        $rolId = (int)($usuario->id_rol ?? 0);

        // Si la sesión tiene activo el permiso asignarArticulos
        if (tienePermiso('asignarArticulos')) {
            return true;
        }

        // Consultar en base de datos en caso de que los permisos del rol hayan cambiado con sesión abierta
        try {
            $otorgado = \Illuminate\Support\Facades\DB::table('vista_permisos')
                ->where('rol', $rolId)
                ->where('permiso_nombre', 'asignarArticulos')
                ->value('permiso_otorgado');
            if (strtolower(trim((string)$otorgado)) === 'activo') {
                return true;
            }
        } catch (\Throwable $e) {}

        return false;
    }
}

function tienePermiso(string $nombrePermiso): bool
{
    if (esSuperAdmin()) {
        return true;
    }

    $usuario = Session::get('usuario');
    $rolId = (int)($usuario?->id_rol ?? 0);

    // Administrador (Rol 4): Acceso operativo total excepto gestión estructural de roles
    if (esAdmin()) {
        if (in_array($nombrePermiso, ['insertarRol', 'modificarRol', 'desactivarRol'])) {
            return false;
        }
        return true;
    }

    // Almacenistas (Rol 3): Control operativo total de inventario por código
    if ($rolId === 3) {
        $permisosAlmacen = [
            'consumible - leer', 'consumible - insertar', 'consumible - modificar',
            'fijos - leer', 'fijos - insertar', 'fijos - modificar',
            'compra/venta - leer', 'compra/venta - insertar', 'compra/venta - modificar',
            'vehiculo - leer', 'vehiculo - insertar', 'vehiculo - modificar',
            'leerEmpresaInterna', 'leerEmpresaExterna', 'leerUbicaciones', 'agregarUbicaciones', 'leerEtiquetas'
        ];
        if (in_array($nombrePermiso, $permisosAlmacen)) {
            return true;
        }
    }

    // Auxiliares (Rol 2): Captura y lectura diaria
    if ($rolId === 2) {
        $permisosAuxiliar = [
            'consumible - leer', 'consumible - insertar',
            'fijos - leer', 'fijos - insertar',
            'compra/venta - leer', 'compra/venta - insertar',
            'vehiculo - leer',
            'leerEmpresaInterna', 'leerEmpresaExterna', 'leerUbicaciones', 'leerEtiquetas'
        ];
        if (in_array($nombrePermiso, $permisosAuxiliar)) {
            return true;
        }
    }

    $permisos = Session::get('usuario_permisos', []);

    // Si la sesión no tiene permisos cargados, intentar cargarlos
    if (empty($permisos) && Session::has('usuario')) {
        $usuario = Session::get('usuario');
        if ($usuario && isset($usuario->id_rol)) {
            try {
                $permisos = \Illuminate\Support\Facades\DB::select('
                    SELECT vp.*
                    FROM vista_permisos vp
                    WHERE vp.rol = ?
                ', [$usuario->id_rol]);
                Session::put('usuario_permisos', $permisos);
            } catch (\Throwable $e) {}
        }
    }

    foreach ($permisos as $permiso) {
        $nombre = $permiso->permiso_nombre ?? '';
        $otorgado = strtolower(trim((string)($permiso->permiso_otorgado ?? '')));
        $activo = strtolower(trim((string)($permiso->permiso_activo ?? '')));

        if (
            strcasecmp($nombre, $nombrePermiso) === 0 &&
            ($otorgado === 'activo' || $otorgado === '1' || $otorgado === 'true') &&
            ($activo === 'activo' || $activo === '1' || $activo === 'true')
        ) {
            return true;
        }
    }

    return false;
}


function nombreCompletoUsuario(): string
{
    $usuario = Session::get('usuario');
    if ($usuario) {
        return "{$usuario->nombre} {$usuario->apellido} {$usuario->apellido_m}";
    }
    return 'Invitado';
}

// app/helpers.php

if (!function_exists('formatCamelCase')) {
    function formatCamelCase($text)
    {
        $formatted = ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $text));
        $formatted = str_ireplace('Etiquetas', 'Categorías', $formatted);
        $formatted = str_ireplace('Etiqueta', 'Categoría', $formatted);
        $formatted = str_ireplace('Articulos', 'Artículos', $formatted);
        return $formatted;
    }
}

if (!function_exists('getPermisoIcon')) {
    function getPermisoIcon($permiso)
    {
        return match (true) {
            str_contains($permiso, 'leer') => 'bi-book text-info',
            str_contains($permiso, 'insertar') => 'bi-plus-circle text-success',
            str_contains($permiso, 'modificar') => 'bi-pencil-square text-warning',
            str_contains($permiso, 'desactivar') => 'bi-slash-circle text-danger',
            str_contains($permiso, 'asignar') => 'bi-person-check-fill text-primary',
            default => 'bi-shield-lock text-primary'
        };
    }
}

if (!function_exists('cleanAction')) {
    function cleanAction($permisoNombre)
    {
        return trim(explode('-', $permisoNombre)[1] ?? $permisoNombre);
    }
}

if (!function_exists('appRedirect')) {
    function appRedirect(string $path = '', array $params = []): \Illuminate\Http\RedirectResponse
    {
        $url = rtrim(config('app.url'), '/') . '/' . ltrim($path, '/');
        $response = redirect($url);
        foreach ($params as $key => $value) {
            $response = $response->with($key, $value);
        }
        return $response;
    }
}

if (!function_exists('appRedirectToLogin')) {
    function appRedirectToLogin(?string $error = null): \Illuminate\Http\RedirectResponse
    {
        $response = appRedirect();
        if ($error) {
            $response = $response->with('error', $error);
        }
        return $response;
    }
}

if (!function_exists('appRedirectToHome')) {
    function appRedirectToHome(?string $error = null): \Illuminate\Http\RedirectResponse
    {
        $response = appRedirect('home');
        if ($error) {
            $response = $response->with('error', $error);
        }
        return $response;
    }
}

if (!function_exists('obtenerConsumiblesCriticos')) {
    function obtenerConsumiblesCriticos()
    {
        return \Illuminate\Support\Facades\Cache::remember('consumibles_criticos_nav', 60, function() {
            return \Illuminate\Support\Facades\DB::table('productos_consumibles as pc')
                ->join('productos as p', 'pc.producto_id', '=', 'p.id')
                ->leftJoin('ubicacion as u', 'p.ubicacion_id', '=', 'u.id')
                ->select('pc.id as consumible_id', 'p.nombre', 'pc.existencia', 'u.nombre as ubicacion_nombre')
                ->where('pc.existencia', '<=', 10)
                ->orderBy('pc.existencia', 'asc')
                ->limit(10)
                ->get();
        });
    }
}

if (!function_exists('contarConsumiblesCriticos')) {
    function contarConsumiblesCriticos()
    {
        return \Illuminate\Support\Facades\Cache::remember('count_consumibles_criticos', 60, function() {
            return \Illuminate\Support\Facades\DB::table('productos_consumibles')
                ->where('existencia', '<=', 10)
                ->count();
        });
    }
}

if (!function_exists('obtenerMantenimientosProximos')) {
    function obtenerMantenimientosProximos($diasLimite = 30, $limiteRegistros = 10)
    {
        $cacheKey = 'mantenimientos_proximos_nav_' . $diasLimite . '_' . $limiteRegistros;
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function() use ($diasLimite, $limiteRegistros) {
            $fechaLimite = \Carbon\Carbon::today()->addDays($diasLimite)->toDateString();

            return \Illuminate\Support\Facades\DB::table('mantenimientos_fijos as mf')
                ->join(\Illuminate\Support\Facades\DB::raw('(SELECT producto_fijo_id, MAX(id) as max_id FROM mantenimientos_fijos GROUP BY producto_fijo_id) as latest'), 'mf.id', '=', 'latest.max_id')
                ->join('productos_fijos as pf', 'mf.producto_fijo_id', '=', 'pf.id')
                ->join('productos as p', 'pf.producto_id', '=', 'p.id')
                ->leftJoin('ubicacion as u', 'p.ubicacion_id', '=', 'u.id')
                ->leftJoin('usuario as us', 'pf.responsable', '=', 'us.id')
                ->select(
                    'mf.id as mantenimiento_id',
                    'mf.producto_fijo_id',
                    'mf.fecha as ultima_fecha',
                    'mf.proxima_fecha',
                    'mf.tipo_servicio',
                    'mf.frecuencia_meses',
                    'pf.clave',
                    'pf.estado as estado_fijo',
                    'p.nombre as producto_nombre',
                    'p.descripcion as producto_descripcion',
                    'p.codigoBarra',
                    'u.nombre as ubicacion_nombre',
                    \Illuminate\Support\Facades\DB::raw("CONCAT_WS(' ', us.nombre, us.apellido) as responsable_nombre"),
                    \Illuminate\Support\Facades\DB::raw("DATEDIFF(mf.proxima_fecha, CURDATE()) as dias_restantes")
                )
                ->where(function($q) {
                    $q->whereNull('pf.estado')->orWhere('pf.estado', '!=', 'baja');
                })
                ->whereNotNull('mf.proxima_fecha')
                ->where('mf.proxima_fecha', '<=', $fechaLimite)
                ->orderBy('mf.proxima_fecha', 'asc')
                ->limit($limiteRegistros)
                ->get();
        });
    }
}

if (!function_exists('contarMantenimientosProximos')) {
    function contarMantenimientosProximos($diasLimite = 30)
    {
        $cacheKey = 'count_mantenimientos_proximos_' . $diasLimite;
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function() use ($diasLimite) {
            $fechaLimite = \Carbon\Carbon::today()->addDays($diasLimite)->toDateString();

            return \Illuminate\Support\Facades\DB::table('mantenimientos_fijos as mf')
                ->join(\Illuminate\Support\Facades\DB::raw('(SELECT producto_fijo_id, MAX(id) as max_id FROM mantenimientos_fijos GROUP BY producto_fijo_id) as latest'), 'mf.id', '=', 'latest.max_id')
                ->join('productos_fijos as pf', 'mf.producto_fijo_id', '=', 'pf.id')
                ->where(function($q) {
                    $q->whereNull('pf.estado')->orWhere('pf.estado', '!=', 'baja');
                })
                ->whereNotNull('mf.proxima_fecha')
                ->where('mf.proxima_fecha', '<=', $fechaLimite)
                ->count();
        });
    }
}

