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
                'permisos' => 'required|array',
            ]);

            PermisoRol::where('rol_id', $request->rol_id)->update(['status' => 'inactivo']);

            PermisoRol::whereIn('id', $request->permisos)->update(['status' => 'activo']);
            return redirect()->back()->with('success', 'Permisos actualizados correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar permisos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar los permisos.');
        }
    }
}
