<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use App\Models\EtiquetaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EtiquetasController extends Controller
{
    public function index()
    {
        if (!tienePermiso('leerEtiquetas') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $etiquetas = Etiqueta::where('eliminado', 1)->paginate(10);
            $etiquetasProductos = EtiquetaProducto::paginate(10);
            return view('etiquetas.index', compact('etiquetas', 'etiquetasProductos'));
        } catch (\Exception $e) {
            Log::error('Error al obtener etiquetas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las etiquetas.');
        }
    }

    public function insertar(Request $request)
    {
        if (!tienePermiso('insertarEtiquetas') || !Session::has('usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No cuenta con los permisos necesarios para realizar esta acción.'
                ], 403);
            }
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $request->validate([
            'nombre' => 'required|string|max:100|unique:etiquetas,nombre',
        ]);

        try {
            $etiqueta = Etiqueta::create([
                'nombre' => trim($request->nombre),
                'eliminado' => 1,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Etiqueta creada exitosamente.',
                    'etiqueta' => $etiqueta,
                ]);
            }

            return redirect()->back()->with('success', 'Etiqueta creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al insertar etiqueta: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al crear la etiqueta: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al crear la etiqueta.');
        }
    }

    public function actualizar(Request $request, $id = null)
    {
        if (!tienePermiso('modificarEtiquetas') || !Session::has('usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No cuenta con los permisos necesarios para realizar esta acción.'
                ], 403);
            }
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $id = $id ?? $request->input('id');
            $request->validate([
                'nombre' => 'required|string|max:100|unique:etiquetas,nombre,' . $id,
            ]);
            $etiqueta = Etiqueta::findOrFail($id);
            $etiqueta->update(['nombre' => trim($request->nombre)]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Etiqueta actualizada exitosamente.',
                    'etiqueta' => $etiqueta,
                ]);
            }

            return redirect()->route('etiquetas.index')->with('success', 'Etiqueta actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar etiqueta: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al actualizar la etiqueta: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al actualizar la etiqueta.');
        }
    }

    public function eliminar(Request $request, $id = null)
    {
        if (!tienePermiso('desactivarEtiquetas') || !Session::has('usuario')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No cuenta con los permisos necesarios para realizar esta acción.'
                ], 403);
            }
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $id = $id ?? $request->input('id');
            if (!$id) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'error', 'message' => 'ID de etiqueta no especificado.'], 400);
                }
                return redirect()->back()->with('error', 'ID de etiqueta no especificado.');
            }

            $etiqueta = Etiqueta::findOrFail($id);
            $etiqueta->update(['eliminado' => 0]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Etiqueta eliminada correctamente.'
                ]);
            }

            return redirect()->route('etiquetas.index')->with('success', 'Etiqueta eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar etiqueta: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al eliminar la etiqueta: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al eliminar la etiqueta.');
        }
    }

    public function ver($id)
    {
        if (!tienePermiso('leerEtiquetas') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $etiqueta = Etiqueta::findOrFail($id);
            return view('etiquetas.view', compact('etiqueta'));
        } catch (\Exception $e) {
            Log::error('Error al ver etiqueta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la etiqueta.');
        }
    }
}
