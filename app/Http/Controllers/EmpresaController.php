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
        if (!Session::has('usuario')) {
            return appRedirectToLogin('Debe iniciar sesión para continuar.');
        }

        try {
            $empresa = Empresa::find($id);

            if (!$empresa) {
                abort(404, 'Empresa no encontrada');
            }

            $permisoRequerido = ($empresa->status === 'externa') ? 'leerEmpresaExterna' : 'leerEmpresaInterna';
            if (!esSuperAdmin() && !tienePermiso($permisoRequerido)) {
                return appRedirectToHome('No cuenta con los permisos necesarios');
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
        $esExterna = ($request->tipo === 'externa');
        $permisoRequerido = $esExterna ? 'insertarEmpresaExterna' : 'insertarEmpresaInterna';

        if (!esSuperAdmin() && (!tienePermiso($permisoRequerido) || !Session::has('usuario'))) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nombre' => 'required|string|max:80',
            'nombre_registrado' => 'required|string|max:150',
            'rfc' => 'required|string|max:13',
            'direccion' => 'required|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'fotografias.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error en el formulario: ' . $validator->errors()->first());
        }

        try {
            $empresa = new Empresa();
            $empresa->nombre = trim($request->nombre);
            $empresa->nombre_registrado = trim($request->nombre_registrado);
            $empresa->rfc = strtoupper(trim($request->rfc));
            $empresa->direccion = trim($request->direccion);
            $empresa->latitud = $request->filled('latitud') ? (float)$request->latitud : 19.432608;
            $empresa->longitud = $request->filled('longitud') ? (float)$request->longitud : -99.133209;
            $empresa->status = in_array($request->tipo, ['interna', 'externa']) ? $request->tipo : 'interna';
            $empresa->clave = substr(uniqid(), -6); // Generar una clave única de 6 caracteres

            // Procesar las imágenes
            $urls = [];
            if ($request->hasFile('fotografias')) {
                $dir = public_path('empresa');
                if (!file_exists($dir)) {
                    mkdir($dir, 0775, true);
                }
                foreach ($request->file('fotografias') as $foto) {
                    $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '', $foto->getClientOriginalName());
                    $nombreArchivo = time() . '_' . $nombreLimpio;

                    // Mueve la imagen directamente a public/empresa
                    $foto->move($dir, $nombreArchivo);

                    // Genera la URL accesible desde el navegador
                    $urls[] = asset('empresa/' . $nombreArchivo);
                }
            }

            $empresa->fotografias = implode(',', $urls);
            $empresa->save();

            // Asegurar permisos en permisos_empresa y asignación en permisos_rol
            try {
                $acciones = ['leer', 'insertar', 'modificar', 'desactivar'];
                foreach ($acciones as $accion) {
                    $permisoEmp = \App\Models\PermisoEmpresa::firstOrCreate([
                        'empresa_id' => $empresa->id,
                        'accion' => $accion,
                    ], [
                        'status' => 'activo'
                    ]);

                    // Asignar al rol 1 (Super Admin) y rol 4 (Administrador) por defecto
                    foreach ([1, 4] as $rolId) {
                        \App\Models\PermisoRol::firstOrCreate([
                            'rol_id' => $rolId,
                            'permiso_id' => $permisoEmp->id,
                            'permiso_tipo' => 'empresa'
                        ], [
                            'status' => 'activo'
                        ]);
                    }
                }
            } catch (\Throwable $pe) {
                Log::warning('No se pudieron crear los permisos automáticos de la empresa: ' . $pe->getMessage());
            }

            $ruta = $empresa->status != 'externa' ? 'empresa.index' : 'empresa.indexExterna';
            return redirect()->route($ruta)->with('success', 'Empresa creada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al insertar empresa: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al crear la empresa. Intenta nuevamente.');
        }
    }

    public function actualizar(Request $request)
    {
        $esExterna = ($request->tipo === 'externa');
        $permisoRequerido = $esExterna ? 'modificarEmpresaExterna' : 'modificarEmpresaInterna';

        if (!esSuperAdmin() && (!tienePermiso($permisoRequerido) || !Session::has('usuario'))) {
            return appRedirectToLogin('No cuenta con los permisos necesarios');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id' => 'required|exists:empresa,id',
            'nombre' => 'required|string|max:80',
            'nombre_registrado' => 'required|string|max:150',
            'rfc' => 'required|string|max:13',
            'direccion' => 'required|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'fotografias.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Error en el formulario: ' . $validator->errors()->first());
        }

        try {
            $empresa = Empresa::findOrFail($request->id);

            $empresa->nombre = trim($request->nombre);
            $empresa->nombre_registrado = trim($request->nombre_registrado);
            $empresa->rfc = strtoupper(trim($request->rfc));
            $empresa->direccion = trim($request->direccion);
            if ($request->filled('latitud')) {
                $empresa->latitud = (float)$request->latitud;
            }
            if ($request->filled('longitud')) {
                $empresa->longitud = (float)$request->longitud;
            }

            // Manejo de imágenes
            $imagenesActuales = $empresa->fotografias ? array_filter(explode(',', $empresa->fotografias)) : [];

            if ($request->hasFile('fotografias')) {
                $dir = public_path('empresa');
                if (!file_exists($dir)) {
                    mkdir($dir, 0775, true);
                }
                foreach ($request->file('fotografias') as $foto) {
                    $nombreLimpio = preg_replace('/[^a-zA-Z0-9._-]/', '', $foto->getClientOriginalName());
                    $nombreArchivo = time() . '_' . $nombreLimpio;

                    // Mueve la imagen directamente a public/empresa
                    $foto->move($dir, $nombreArchivo);

                    // Genera la URL accesible desde el navegador
                    $imagenesActuales[] = asset('empresa/' . $nombreArchivo);
                }
            }

            $empresa->fotografias = implode(',', $imagenesActuales);
            $empresa->save();

            return redirect()->back()->with('success', 'Empresa actualizada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar empresa: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar la empresa. Intenta nuevamente.');
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
            $empresas = Empresa::where('status', 'interna')->get();
            $ubicaciones = Ubicacion::paginate(10); // ✅ Correcto
            return view('empresa.ubicaciones.index', compact('ubicaciones', 'empresas'));
        } catch (\Exception $e) {
            Log::error('Error al obtener ubicaciones: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar las ubicaciones.');
        }
    }

    public function insertarUbicacion(Request $request)
    {
        if (!Session::has('usuario') || !tienePermiso('agregarUbicaciones')) {
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
        if (!Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $ubicacion = Ubicacion::findOrFail($id);

        if (!tienePermiso('modificarUbicaciones') && !tienePermiso($ubicacion->nombre . ' - modificar')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        if ($request->estado == 'baja' && !tienePermiso('desactivarUbicaciones') && !tienePermiso($ubicacion->nombre . ' - desactivar')) {
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
