<?php

namespace App\Http\Controllers;

use App\Models\PermisoRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PermisosController extends Controller
{

    public function actualizarPermisos(Request $request)
    {
        if (!Session::has('usuario')  || !tienePermiso('modificarRol')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }
        if($request->rol_id == 1) {
            return redirect()->back()->with('error', 'No se pueden modificar los permisos del rol de administrador.');
        }

        try {
            $request->validate([
                'rol_id'   => 'required|integer',
                'permisos' => 'nullable|array',
            ]);

            // 1. Marcar todos los permisos del rol en inactivo
            PermisoRol::where('rol_id', $request->rol_id)->update(['status' => 'inactivo']);

            // 2. Si se marcaron permisos, activarlos
            if (!empty($request->permisos)) {
                PermisoRol::whereIn('id', $request->permisos)->update(['status' => 'activo']);
            }

            // 3. Si el usuario actual tiene el rol que se acaba de modificar, refrescar su sesión
            if (Session::has('usuario') && (int)(Session::get('usuario')->id_rol ?? 0) === (int)$request->rol_id) {
                try {
                    $nuevosPermisos = \Illuminate\Support\Facades\DB::select('
                        SELECT vp.*
                        FROM vista_permisos vp
                        WHERE vp.rol = ?
                    ', [$request->rol_id]);
                    Session::put('usuario_permisos', $nuevosPermisos);
                    Session::save();
                } catch (\Throwable $t) {}
            }

            return redirect()->back()->with('success', 'Permisos actualizados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return redirect()->back()->with('error', 'Datos inválidos al enviar el formulario.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar permisos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar los permisos. Intenta nuevamente.');
        }
    }
}
