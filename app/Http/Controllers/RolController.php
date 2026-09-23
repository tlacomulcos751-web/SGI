<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\PermisoRol;

class RolController extends Controller
{
    public function index()
    {
        if (!esSuperAdmin() || !tienePermiso('leerRol') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $roles = Rol::where('eliminado', 1)->paginate(10);
            return view('roles.index', compact('roles'));
        } catch (\Exception $e) {
            Log::error('Error al obtener los roles: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al obtener los roles.');
        }
    }

    public function insertar(Request $request)
    {
        if (!esSuperAdmin() || !tienePermiso('insertarRol') || !Session::has('usuario')) {
            return redirect()->route('roles.index')->with('error', 'No cuenta con los permisos necesarios');
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'status' => 'required|in:activo,inactivo',
        ]);

        try {
            DB::beginTransaction();

            // Crear el nuevo rol
            $nuevoRol = Rol::create([
                'nombre' => $request->nombre,
                'status' => $request->status,
                'eliminado' => 1
            ]);

            // Obtener los permisos del rol con ID 1
            $permisos = PermisoRol::where('rol_id', 1)->get();

            // Duplicar los permisos para el nuevo rol
            foreach ($permisos as $permiso) {
                PermisoRol::create([
                    'rol_id' => $nuevoRol->id,
                    'permiso_id' => $permiso->permiso_id,
                    'permiso_tipo' => $permiso->permiso_tipo,
                    'status' => 'inactivo', // Asignar el estado por defecto
                ]);
            }

            DB::commit();

            return redirect()->route('roles.index')->with('success', 'Rol creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al insertar el rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al insertar el rol.');
        }
    }

    public function ver($id)
    {
        if (!esSuperAdmin() || !Session::has('usuario')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $rol = Rol::where('id', $id)->where('eliminado', 1)->firstOrFail();
            return response()->json($rol);
        } catch (\Exception $e) {
            Log::error('Error al obtener el rol: ' . $e->getMessage());
            return response()->json(['error' => 'Rol no encontrado'], 404);
        }
    }

    public function actualizar(Request $request, $id)
    {
        if (!esSuperAdmin() || !tienePermiso('modificarRol') || !Session::has('usuario')) {
            return redirect()->route('roles.index')->with('error', 'No cuenta con los permisos necesarios');
        }

        if (!$request->has('nombre') || !$request->has('status')) {
            return redirect()->back()->with('error', 'Datos incompletos para actualizar el rol.');
        }

        if ($request->id == 1) {
            return redirect()->back()->with('error', 'No se puede modificar el rol de  Super Admin.');
        }
        $request->validate([
            'nombre' => 'required|string|max:100',
            'status' => 'required|in:activo,inactivo',
        ]);

        try {
            $rol = Rol::where('id', $id)->where('eliminado', 1)->firstOrFail();
            $rol->update([
                'nombre' => $request->nombre,
                'status' => $request->status,
            ]);

            return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el rol.');
        }
    }

    public function eliminar($id)
    {
        if (!esSuperAdmin() || !tienePermiso('desactivarRol') || !Session::has('usuario')) {
            return redirect()->route('roles.index')->with('error', 'No cuenta con los permisos necesarios');
        }
        if ($id == 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el rol de Super Admin.');
        }

        try {
            $rol = Rol::findOrFail($id);
            $rol->update(['eliminado' => 0]);

            return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el rol: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el rol.');
        }
    }
}
