<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ApiSessionAuth
{
    /**
     * Verifica que exista una sesión activa para las peticiones API.
     * Si no hay sesión, devuelve 401 JSON en vez de redirect.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('usuario')) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado. Inicie sesión para acceder al API.',
            ], 401);
        }

        return $next($request);
    }
}
