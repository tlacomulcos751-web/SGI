<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('esSuperAdmin')) {
    function esSuperAdmin(): bool
    {
        $usuario = Session::get('usuario');
        return $usuario && (int)($usuario->id_rol ?? 0) === 1;
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
