<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Etiqueta;
use App\Models\EtiquetaProducto;
use App\Models\Movimiento;
use App\Models\NotificacionConsumible;
use App\Models\Producto;
use App\Models\ProductoConsumible;
use App\Models\Usuario;
use App\Models\VistaPermisosRolEmpresaUbicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ConsumibleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!tienePermiso('consumible - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $cantidadPorPagina = 15;

        // Obtener ubicaciones permitidas según el rol con permiso de lectura
        $ubicacionesPermitidas = VistaPermisosRolEmpresaUbicacion::where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_leer_ubicacion', true)
            ->pluck('ubicacion_id')
            ->toArray();

        $query = DB::table('productos')
            ->join('productos_consumibles', 'productos_consumibles.producto_id', '=', 'productos.id')
            ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
            ->leftJoin('empresa', 'productos.empresa_id', '=', 'empresa.id')
            ->select(
                'productos.id as id_producto',
                'productos_consumibles.id as id',
                'productos.nombre as nombre',
                'productos.precio as precio',
                'productos.empresa_id as empresa_id',
                'empresa.nombre as empresa_nombre',
                'productos_consumibles.existencia as existencia',
                'ubicacion.nombre as ubicacion',
                DB::raw("IF(productos_consumibles.existencia > 0, 'activo', 'agotado') as estado"),
                'productos.fecha_registro as fechaEntrada',
                DB::raw("'consumible' as tipo_producto")
            )
            ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas);

        // Filtros básicos
        if ($request->filled('nombre')) {
            $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('empresa')) {
            $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
        }
        
        if ($request->filled('ubicacion')) {
            $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
        }
        
        if ($request->filled('estado')) {
            $query->where(DB::raw("IF(productos_consumibles.existencia > 0, 'activo', 'agotado')"), $request->estado);
        }
        
        if ($request->filled('fecha_registro')) {
            $query->whereDate('productos.fecha_registro', $request->fecha_registro);
        }

        // Filtro por etiquetas
        if ($request->filled('etiquetas')) {
            $etiquetas = array_filter($request->etiquetas);
            if (count($etiquetas)) {
                $query->whereIn('productos.id', function ($q) use ($etiquetas) {
                    $q->select('producto_id')
                        ->from('etiquetas_productos')
                        ->join('etiquetas', 'etiquetas.id', '=', 'etiquetas_productos.clasificacion_id')
                        ->whereIn(DB::raw('lower(etiquetas.nombre)'), array_map('strtolower', $etiquetas));
                });
            }
        }

        $productosConsumibles = $query->paginate($cantidadPorPagina)->appends($request->all());
        $productosConsumiblesTodos = $query->get();

        $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();

        $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
            ->where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_insertar_ubicacion', true)
            ->get();

        $empresasInternas = Empresa::where('status', 'interna')->orderBy('nombre')->get();

        return view('productos.consumibles.index', compact('productosConsumibles', 'ubicaciones', 'usuarios', 'productosConsumiblesTodos', 'empresasInternas'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function insertar(Request $request)
    {
        if (!tienePermiso('consumible - insertar') || !Session::has('usuario')) {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para crear un producto consumible.');
        }

        try {
            $request->validate([
                'nombre' => 'required|string|max:150',
                'descripcion' => 'nullable|string',
                'codigoBarra' => 'nullable|string|max:100',
                'empresa_id' => 'required|exists:empresa,id',
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'precio' => 'nullable|numeric|min:0|max:99999999.99',
                'existencia' => 'required|integer|min:1|max:10000',
                'documentacion' => 'nullable|string',
                'comentario' => 'nullable|string',
            ]);

            $usuarioId = session()->get('usuario')->id;
            $precioConsumible = $request->filled('precio') ? $request->precio : null;

            // Llamar procedimiento almacenado
            DB::statement('CALL insertar_producto_consumible(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->descripcion,
                $request->codigoBarra,
                $request->ubicacion_id,
                $precioConsumible,
                $request->existencia,
                $request->documentacion,
                $request->comentario,
                $usuarioId,
                $request->empresa_id,
            ]);

            // Obtener ID del nuevo producto
            $ultimoId = DB::table('productos')->orderBy('id', 'desc')->value('id');

            // Insertar etiquetas si se enviaron
            $etiquetas = $request->input('etiquetasInsertar', []);
            if (!empty($etiquetas)) {
                $datosInsertar = array_map(fn($etiquetaId) => [
                    'producto_id' => $ultimoId,
                    'clasificacion_id' => $etiquetaId,
                ], $etiquetas);

                EtiquetaProducto::insert($datosInsertar);
            }

            return redirect()->back()->with('success', 'Producto consumible ingresado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al insertar producto consumible: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al insertar el producto consumible: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function view($id)
    {
        if (!tienePermiso('consumible - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $productoConsumible = ProductoConsumible::with(['producto.empresa', 'producto.ubicacion'])->findOrFail($id);
            $producto = Producto::with('empresa')->findOrFail($productoConsumible->producto_id);
            $etiquetas = EtiquetaProducto::where('producto_id', $productoConsumible->producto_id)->get();
            $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();
            $movimientos = Movimiento::where('id_producto', $productoConsumible->id)
                ->where('categoria', 'consumibles')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();
            $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
                ->where('rol_id', Session::get('usuario')->id_rol)
                ->where('puede_leer_ubicacion', true)
                ->get();
            $todasLasEtiquetas = Etiqueta::where('eliminado', 1)->orderBy('nombre')->get();
            $todasLasNotificaciones = NotificacionConsumible::where('id_productos_consumibles', $id)->get();

            return view('productos.consumibles.view', compact(
                'productoConsumible',
                'producto',
                'etiquetas',
                'usuarios',
                'movimientos',
                'ubicaciones',
                'todasLasEtiquetas',
                'todasLasNotificaciones'
            ));
        } catch (\Exception $e) {
            Log::error('Error al obtener producto de consumible: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al obtener información del producto.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarInfo(Request $request, $id)
    {
        try {
            if (!tienePermiso('consumible - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'nombre' => 'required|string|max:150',
                'codigoBarra' => 'nullable|string|max:13',
                'descripcion' => 'nullable|string',
                'precio' => 'nullable|numeric|min:0|max:99999999.99',
                'comentario' => 'nullable|string',
                'existencia' => 'required|integer|min:0|max:10000',
            ]);

            $pcv = ProductoConsumible::findOrFail($id);
            $producto = $pcv->producto;

            $producto->update([
                'nombre' => $request->nombre,
                'codigoBarra' => $request->codigoBarra,
                'descripcion' => $request->descripcion,
                'precio' => $request->filled('precio') ? $request->precio : null,
            ]);

            $pcv->update([
                'existencia' => $request->existencia,
            ]);

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $pcv->id,
                'categoria' => 'consumibles',
                'accion' => 'Actualización de información',
                'comentario' => $request->comentario,
                'documentacion' => $documento ?? null,
            ]);

            $this->notificacion($id);
            return back()->with('success', 'Información actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }


    public function actualizarUbicacion(Request $request, $id)
    {
        try {
            if (!tienePermiso('consumible - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'comentario' => 'nullable|string',
            ]);

            $pcv = ProductoConsumible::findOrFail($id);
            $producto = $pcv->producto;

            $cantidadAsociados = ProductoConsumible::where('producto_id', $producto->id)->count();

            if ($cantidadAsociados > 1) {
                $nuevoProducto = $producto->replicate();
                $nuevoProducto->ubicacion_id = $request->ubicacion_id;
                $nuevoProducto->save();

                $pcv->producto_id = $nuevoProducto->id;
                $pcv->save();
            } else {
                $producto->ubicacion_id = $request->ubicacion_id;
                $producto->save();
            }

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $pcv->id,
                'categoria' => 'consumibles',
                'accion' => 'Cambio de ubicación',
                'comentario' => $request->comentario,
            ]);

            return back()->with('success', 'Ubicación actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }

    public function notificacion($id)
    {
        $producto = ProductoConsumible::findOrFail($id);

        // Verificar si alguna notificación debe activarse
        $notificaciones = NotificacionConsumible::where('id_productos_consumibles', $producto->id)->get();

        foreach ($notificaciones as $notificacion) {
            // El límite para desabastecimiento es 5
            if ($producto->existencia <= 5 && $producto->existencia <= $notificacion->limite) {
                $usuario = Usuario::find($notificacion->id_user);
                if ($usuario && $usuario->gmail) {
                    Mail::raw("El producto '{$producto->producto->nombre}' ha alcanzado un nivel de stock crítico: {$producto->existencia} unidades restantes.", function ($message) use ($usuario, $producto) {
                        $message->to($usuario->gmail)
                            ->subject("Alerta de Stock Crítico: {$producto->producto->nombre}");
                    });
                }
            }
        }

        return redirect()->back()->with('success', 'Existencia actualizada.');
    }

    public function store(Request $request)
    {
        if (!tienePermiso('consumible - modificar') || !Session::has('usuario')) {
            return appRedirectToLogin('Permiso denegado');
        }
        try {
            $request->validate([
                'id_user' => 'required|exists:usuario,id',
                'id_productos_consumibles' => 'required|exists:productos_consumibles,id',
                'limite' => 'required|integer|min:0',
            ]);

            NotificacionConsumible::create([
                'id_user' => $request->id_user,
                'id_productos_consumibles' => $request->id_productos_consumibles,
                'limite' => $request->limite,
            ]);
            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $request->id_productos_consumibles,
                'categoria' => 'consumibles',
                'accion' => 'Agregro una notificación',
                'comentario' => '',
            ]);
            return redirect()->back()->with('success', 'Notificación creada correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }

    public function destroy($id)
    {
        if (!tienePermiso('consumible - desactivar') || !Session::has('usuario')) {
            return appRedirectToLogin('Permiso denegado');
        }
        try {
            $notificacion = NotificacionConsumible::findOrFail($id);
            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $notificacion->id_productos_consumibles,
                'categoria' => 'consumibles',
                'accion' => 'Elimino una notificación',
                'comentario' => '',
            ]);
            $notificacion->delete();

            return redirect()->back()->with('success', 'Notificación eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar notificación: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo eliminar la notificación.');
        }
    }

    public function ajustarExistencia(Request $request, $id)
    {
        try {
            if (!tienePermiso('consumible - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'cantidad' => 'required|integer|min:1|max:10000',
                'tipo' => 'required|in:aumentar,disminuir',
                'comentario' => 'required|string',
            ]);

            $pcv = ProductoConsumible::findOrFail($id);
            $ajuste = $request->tipo === 'aumentar'
                ? $pcv->existencia + $request->cantidad
                : $pcv->existencia - $request->cantidad;

            if ($ajuste < 0) {
                return back()->with('error', 'No se puede reducir por debajo de 0 la existencia.');
            }

            $pcv->update(['existencia' => $ajuste]);

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $pcv->id,
                'categoria' => 'consumibles',
                'accion' => ucfirst($request->tipo) . ' existencia',
                'comentario' => $request->comentario,
            ]);

            $this->notificacion($id);
            
            return back()->with('success', 'Existencia actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al ajustar existencia: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador.');
        }
    }
}
