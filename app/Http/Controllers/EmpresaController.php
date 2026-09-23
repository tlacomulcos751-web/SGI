<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Ubicacion;
use App\Models\VistaProductosUnificada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{

    public function index()
    {
        if (!tienePermiso('leerEmpresaInterna') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $empresas = Empresa::where('status', 'interna')->paginate(10);
            $tipo = 'interna';
            return view('empresa.index', compact('empresas', 'tipo'));
        } catch (\Exception $e) {
            Log::error('Error al obtener empresas internas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las empresas.');
        }
    }

    public function indexExterna()
    {
        if (!tienePermiso('leerEmpresaExterna') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $empresas = Empresa::where('status', 'externa')->paginate(10);
            $tipo = 'externa';
            return view('empresa.index', compact('empresas', 'tipo'));
        } catch (\Exception $e) {
            Log::error('Error al obtener empresas externas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las empresas.');
        }
    }

    public function ver($id)
    {
        if (!tienePermiso('leerEmpresaInterna') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $empresa = Empresa::find($id);

            if (!$empresa) {
                abort(404, 'Empresa no encontrada');
            }
            $empresas = [];
            $tipo = $empresa->status;
            return view('empresa.view', compact('empresa', 'tipo', 'empresas'));
        } catch (\Exception $e) {
            Log::error('Error al visualizar empresa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al visualizar la empresa.');
        }
    }

    public function insertar(Request $request)
    {
        if (!tienePermiso('insertarEmpresaInterna') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $request->validate([
            'nombre' => 'required|max:80',
            'nombre_registrado' => 'required|max:150',
            'rfc' => 'required|max:13',
            'direccion' => 'required',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'fotografias.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        try {
            $empresa = new Empresa();
            $empresa->nombre = $request->nombre;
            $empresa->nombre_registrado = $request->nombre_registrado;
            $empresa->rfc = $request->rfc;
            $empresa->direccion = $request->direccion;
            $empresa->latitud = $request->latitud;
            $empresa->longitud = $request->longitud;
            $empresa->status = $request->tipo;
            $empresa->clave = substr(uniqid(), -6); // Generar una clave única de 6 caracteres

            // Procesar las imágenes
            $urls = [];
            if ($request->hasFile('fotografias')) {
                foreach ($request->file('fotografias') as $foto) {
                    $nombreArchivo = time() . '_' . $foto->getClientOriginalName();

                    // Mueve la imagen directamente a public/empresa
                    $foto->move(public_path('empresa'), $nombreArchivo);

                    // Genera la URL accesible desde el navegador
                    $urls[] = asset('empresa/' . $nombreArchivo);
                }
            }

            $empresa->fotografias = implode(',', $urls);
            $empresa->save();


            $ruta = $empresa->status != 'externa' ? 'empresa.index' : 'empresa.indexExterna';
            return redirect()->route($ruta)->with('success', 'Empresa creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al insertar empresa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la empresa.');
        }
    }

    public function actualizar(Request $request)
    {
        if (!tienePermiso('modificarEmpresaInterna') || !Session::has('usuario')) {
            return appRedirectToLogin('No cuenta con los permisos necesarios');
        }

        try {
            $request->validate([
                'id' => 'required|exists:empresa,id',
                'nombre' => 'required|max:80',
                'nombre_registrado' => 'required|max:150',
                'rfc' => 'required|max:13',
                'direccion' => 'required',
                'latitud' => 'required|numeric',
                'longitud' => 'required|numeric',
                'fotografias.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);
            $empresa = Empresa::findOrFail($request->id);

            $empresa->nombre = $request->nombre;
            $empresa->nombre_registrado = $request->nombre_registrado;
            $empresa->rfc = $request->rfc;
            $empresa->direccion = $request->direccion;
            $empresa->latitud = $request->latitud;
            $empresa->longitud = $request->longitud;

            // Manejo de imágenes
            $imagenesActuales = $empresa->fotografias ? explode(',', $empresa->fotografias) : [];

            if ($request->hasFile('fotografias')) {
                foreach ($request->file('fotografias') as $foto) {
                    $nombreArchivo = time() . '_' . $foto->getClientOriginalName();

                    // Mueve la imagen directamente a public/empresa
                    $foto->move(public_path('empresa'), $nombreArchivo);

                    // Genera la URL accesible desde el navegador
                    $urls[] = asset('empresa/' . $nombreArchivo);
                }
            }

            $empresa->fotografias = implode(',', array_merge($imagenesActuales, $urls));
            $empresa->save();

            return redirect()->back()->with('success', 'Empresa actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar empresa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la empresa.');
        }
    }

    public function eliminarImagenesEmpresa(Request $request)
    {
        if (!tienePermiso('modificarEmpresaInterna') || !Session::has('usuario')) {
            return appRedirectToLogin('No cuenta con los permisos necesarios');
        }

        $request->validate([
            'empresa_id' => 'required|exists:empresa,id',
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'url'
        ]);

        try {
            $empresa = Empresa::findOrFail($request->empresa_id);
            $imagenesActuales = $empresa->fotografias ? explode(',', $empresa->fotografias) : [];
            $imagenesAEliminar = $request->imagenes;

            // Eliminar físicamente las imágenes seleccionadas
            foreach ($imagenesAEliminar as $url) {
                // Elimina el dominio y obtiene solo el path relativo
                $path = str_replace(asset('empresa') . '/', '', $url);
            
                // Genera la ruta completa física del archivo
                $archivo = public_path('empresa/' . $path);
            
                // Verifica si el archivo existe y lo elimina
                if (file_exists($archivo)) {
                    unlink($archivo);
                }
            }

            // Actualizar la lista de imágenes en la base de datos
            $imagenesRestantes = array_diff($imagenesActuales, $imagenesAEliminar);
            $empresa->fotografias = implode(',', $imagenesRestantes);
            $empresa->save();

            return back()->with('success', 'Imágenes eliminadas correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar imágenes de empresa: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar imágenes.');
        }
    }

    public function indexUbicaciones()
    {
        if (!tienePermiso('leerUbicaciones') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $usuario = Session::get('usuario');
            $empresas = Empresa::obtenerEmpresasConPermisos($usuario->id_rol);
            $ubicaciones = Ubicacion::paginate(10); // ✅ Correcto
            return view('empresa.ubicaciones.index', compact('ubicaciones', 'empresas'));
        } catch (\Exception $e) {
            Log::error('Error al obtener ubicaciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las ubicaciones.');
        }
    }

    public function insertarUbicacion(Request $request)
    {
        if (
            !tienePermiso('agregarUbicaciones') || tienePermiso('insertar' . $request->nombre)
            || !Session::has('usuario')
        ) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $request->validate([
                'clave' => 'required|unique:ubicacion,clave',
                'nombre' => 'required',
                'estado' => 'required|in:activo,baja',
                'empresa_id' => 'required|exists:empresa,id',
            ]);

            Ubicacion::create($request->only(['clave', 'nombre', 'estado', 'empresa_id']));

            return redirect()->back()->with('success', 'Ubicación creada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al insertar ubicación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la ubicación.');
        }
    }

    public function actualizarUbicacion(Request $request, $id)
    {
        if (
            !tienePermiso('modificarUbicaciones') || tienePermiso('actualizar' . $request->nombre)
            || !Session::has('usuario')
        ) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        if ($request->estado == 'baja' && !tienePermiso('desactivarUbicaciones')) {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para dar de baja ubicaciones.');
        }
        if ($request->estado == 'baja' && VistaProductosUnificada::contarPorUbicacion($id) > 0) {
            return redirect()->back()->with('error', 'No se puede dar de baja la ubicación porque tiene productos asociados.');
        }

        try {
            $request->validate([
                'clave' => 'required|unique:ubicacion,clave,' . $id, // Permite la clave actual
                'nombre' => 'required',
                'estado' => 'required|in:activo,baja',
                'empresa_id' => 'required|exists:empresa,id',
            ]);

            $ubicacion = Ubicacion::findOrFail($id);
            $ubicacion->update($request->only(['clave', 'nombre', 'estado', 'empresa_id']));

            return redirect()->back()->with('success', 'Ubicación actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar ubicación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar la ubicación.');
        }
    }

    public function verUbicacion($id)
    {
        if (!tienePermiso('leerUbicaciones') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $usuario = Session::get('usuario');
            $empresas = Empresa::obtenerEmpresasConPermisos($usuario->id_rol);
            $ubicacion = Ubicacion::findOrFail($id);
            return view('empresa.ubicaciones.ver', compact('ubicacion', 'empresas'));
        } catch (\Exception $e) {
            Log::error('Error al visualizar ubicación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al visualizar la ubicación.');
        }
    }
}
