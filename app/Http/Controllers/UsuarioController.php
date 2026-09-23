<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\ProductoFijo;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        if (!esSuperAdmin() || !tienePermiso('leerUsuarios') || !Session::has('usuario')) {
            return appRedirectToHome('No tienes permiso para ver los usuarios.');
        }

        try {
            $query = Usuario::with('rol')->where('eliminado', 1);

            if ($request->filled('nombre')) {
                $query->where('nombre', 'like', '%' . $request->nombre . '%');
            }

            if ($request->filled('apellido')) {
                $query->where('apellido', 'like', '%' . $request->apellido . '%');
            }

            if ($request->filled('usuario')) {
                $query->where('nombre_user', 'like', '%' . $request->usuario . '%');
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }
            if ($request->filled('correo')) {
                $query->where('gmail', 'like', '%' . $request->correo . '%');
            }
            if ($request->filled('rol')) {
                $query->whereHas('rol', function ($q) use ($request) {
                    $q->where('nombre', 'like', '%' . $request->rol . '%');
                });
            }

            $usuarios = $query->paginate(15)->appends($request->all());
            $roles = Rol::where('eliminado', 1)
                ->where('id', '!=', 1) // Excluir el rol de administrador
                ->get();

            return view('usuarios.index', compact('usuarios', 'roles'));
        } catch (\Exception $e) {
            return appRedirectToHome('Error al cargar los usuarios: ' . $e->getMessage());
        }
    }

    public function insertar(Request $request)
    {
        if (!esSuperAdmin() || !tienePermiso('insertarUsuarios') || !Session::has('usuario')) {
            return appRedirectToHome('No tienes permiso para crear usuarios.');
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'apellido_m' => 'nullable|string|max:100',
            'gmail' => 'required|email|max:150',
            'id_rol' => 'required|integer|exists:rol,id',
        ]);

        try {
            $results = DB::select("CALL insertar_usuario(?, ?, ?, ?, ?, @nombre_user, @password_plain)", [
                $request->nombre,
                $request->apellido,
                $request->apellido_m,
                $request->gmail,
                $request->id_rol
            ]);

            $output = DB::select("SELECT @nombre_user AS nombre_user, @password_plain AS password_plain");

            return redirect()->back()->with('success', 'Usuario creado: ' . $output[0]->nombre_user . ', contraseña: ' . $output[0]->password_plain);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al insertar usuario: ' . $e->getMessage());
        }
    }

    public function ver($id)
    {
        if (!esSuperAdmin() || !tienePermiso('leerUsuarios') || !Session::has('usuario')) {
            return appRedirectToHome('No tienes permiso para ver los usuarios.');
        }

        try {
            $usuario = Usuario::with('rol')->findOrFail($id);
            if ($usuario->eliminado == 0) {
                return redirect()->route('usuarios.index')->with('error', 'Usuario eliminado.');
            }
            $movimientos = $usuario->movimientos()->orderBy('fecha', 'desc')->get();
            $productosACargo = ProductoFijo::where('responsable', $id)
                ->where('estado', '!=', 'baja')
                ->paginate(10);

            $roles = Rol::where('eliminado', 1)
                ->where('id', '!=', 1) // Excluir el rol de administrador
                ->get();

            return view('usuarios.ver', compact('usuario', 'movimientos', 'productosACargo', 'roles'));
        } catch (\Exception $e) {
            Log::error('Error al obtener el usuario: ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('error', 'Usuario no encontrado: ');
        }
    }

    public function actualizar($id, Request $request)
    {
        if (!esSuperAdmin() || !tienePermiso('modificarUsuarios') || !Session::has('usuario')) {
            return appRedirectToHome('No tienes permiso para actualizar usuarios.');
        }

        $request->validate([
            'estado' => 'required|String|in:activo,inactivo',
            'gmail' => 'required|email|max:150',
            'password' => 'nullable|string|min:6|max:100',
            'rol' => 'required|integer|exists:rol,id',
        ]);

        try {
            $usuario = Usuario::findOrFail($id);

            $usuario->estado = $request->estado;
            $usuario->gmail = $request->gmail;
            $usuario->id_rol = $request->rol;

            if ($request->filled('password')) {
                $usuario->password = \Illuminate\Support\Facades\Hash::make($request->password);
            }

            $usuario->save();

            return redirect()->back()->with('success', 'Usuario actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Usuario no encontrado', $e);
            return redirect()->route('usuarios.index')->with('error', 'Usuario no encontrado ');
        }
    }

    public function eliminar($id)
    {
        if (!esSuperAdmin() || !tienePermiso('desactivarUsuarios') || !Session::has('usuario')) {
            return appRedirectToHome('No tienes permiso para eliminar usuarios.');
        }

        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->eliminado = 0; // Marcar como eliminado
            $usuario->save();

            return redirect()->back()->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el usuario', $e);
            return redirect()->route('usuarios.index')->with('error', 'Error al eliminar usuario: ');
        }
    }

    public function perfil()
    {
        if (!Session::has('usuario')) {
            return appRedirectToHome('No estas logueado.');
        }
        try {
            $usuario = Usuario::where('id', Session::get('usuario')->id)->firstOrFail();
            $rol = Rol::where('id', $usuario->id_rol)->where('eliminado', 1)->firstOrFail();
            $misObjetos = ProductoFijo::where('responsable', $usuario->id)->paginate(10);
            $vehiculosACargo = $usuario->vehiculosACargo()->where('eliminado', 0)->paginate(10);
            $mov = Movimiento::where('id_user', $usuario->id)
                ->orderBy('fecha', 'desc')
                ->paginate(10);
            return view('usuarios.perfil', compact('usuario', 'rol', 'misObjetos', 'mov', 'vehiculosACargo'));
        } catch (\Exception $e) {
            Log::error('Error al acceder a tu perfil ' . $e);
            return appRedirectToHome('Error al acceder a tu perfil ');
        }
    }

    public function actualizarPerfil(Request $request)
    {
        if (!Session::has('usuario')) {
            return appRedirectToHome('No estas logueado.');
        }

        try {
            $request->validate([
                'gmail' => 'required|email|max:150',
                'password' => 'nullable|string|min:6|max:100',
            ]);

            $usuario = Usuario::findOrFail(Session::get('usuario')->id);
            $usuario->gmail = $request->gmail;

            if ($request->filled('password')) {
                $usuario->password = \Illuminate\Support\Facades\Hash::make($request->password);
            }

            $usuario->save();

            return redirect()->back()->with('success', 'Perfil actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al cambiar la informaciòn', $e);
            return redirect()->route('usuarios.index')->with('error', 'Ocurrio un error, comunicate con sistemas');
        }
    }
}
