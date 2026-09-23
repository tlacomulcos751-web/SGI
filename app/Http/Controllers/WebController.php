<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        if (Session::has('usuario')) {
            return redirect()->route('web.home'); 
        }
        return view('login');
    }

    public function verificarLogin(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            // 1. Buscar al usuario por correo
            $usuario = \App\Models\Usuario::where('gmail', $email)->first();

            // 2. Verificar si existe y si la contraseña coincide
            if (!$usuario) {
                return redirect()->back()->with('error', 'Correo o contraseña incorrectos, verifica tus credenciales.');
            }

            try {
                $passwordValida = Hash::check($password, $usuario->password);
            } catch (\RuntimeException $e) {
                $passwordValida = false;
            }

            // Si Bcrypt no funcionó, probar formatos legacy
            if (!$passwordValida) {
                if (
                    $usuario->password === hash('sha256', $password . $usuario->uuid) ||
                    $usuario->password === hash('sha256', $password) ||
                    $usuario->password === md5($password) ||
                    $usuario->password === sha1($password) ||
                    $usuario->password === $password  // texto plano
                ) {
                    $passwordValida = true;
                    // Auto-actualizar a Bcrypt para futuras sesiones
                    $usuario->password = Hash::make($password);
                    $usuario->save();
                }
            }

            if (!$passwordValida) {
                return redirect()->back()->with('error', 'Correo o contraseña incorrectos, verifica tus credenciales.');
            }

            // 3. Preparar el objeto del usuario para la sesión (emulando lo que devolvía el Stored Procedure)
            $datosUsuario = (object) $usuario->toArray();
            $datosUsuario->error = 0;
            $datosUsuario->mensaje = 'Inicio de sesión exitoso';

            // 4. Obtener los permisos del usuario usando su rol
            $permisosUnificados = \Illuminate\Support\Facades\DB::select('
                SELECT vp.*
                FROM vista_permisos vp
                WHERE vp.rol = ?
            ', [$usuario->id_rol]);

            // 5. Guardar en sesión
            Session::put('usuario', $datosUsuario);
            Session::put('usuario_permisos', $permisosUnificados);
            
            // Forzamos a que el servidor escriba la sesión antes del redirect
            Session::save(); 

            // Redirección directa al dashboard
            return redirect()->route('web.home'); 

        } catch (\Throwable $e) { 
            return redirect()->back()->with('error', 'Error crítico del sistema: ' . $e->getMessage());
        }
    }

    public function home()
    {
        // Si la sesión se perdió en el salto, nos avisa
        if (!Session::has('usuario')) {
            return redirect(url('/'))->with('error', 'La sesión se perdió en el servidor. Vuelve a intentar.');
        }

        try {
            $usuario = Session::get('usuario');
            if ($usuario && isset($usuario->id_rol)) {
                $permisosUnificados = \Illuminate\Support\Facades\DB::select('
                    SELECT vp.*
                    FROM vista_permisos vp
                    WHERE vp.rol = ?
                ', [$usuario->id_rol]);
                Session::put('usuario_permisos', $permisosUnificados);
            }

            $registros7Dias = Movimiento::registrosUltimos7Dias();
            $movimientos7Dias = Movimiento::totalMovimientosUltimos7Dias();
            $usuariosActivos = esSuperAdmin() ? Usuario::totalActivosNoEliminados() : 0;
            
            return view('web.home', compact('registros7Dias', 'movimientos7Dias', 'usuariosActivos'));

        } catch (\Throwable $e) {
            // Si hay error en los modelos (por nombres de tablas), lo arroja aquí
            dd('Error en los Modelos de la Base de Datos: ' . $e->getMessage());
        }
    }
}