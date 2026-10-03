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

function tienePermiso(string $nombrePermiso): bool
{
    if (esSuperAdmin()) {
        return true;
    }

    $permisos = Session::get('usuario_permisos', []);

    foreach ($permisos as $permiso) {
        if (
            $permiso->permiso_nombre === $nombrePermiso &&
            $permiso->permiso_otorgado === 'activo' &&
            $permiso->permiso_activo === 'activo'
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
        return ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $text));
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

