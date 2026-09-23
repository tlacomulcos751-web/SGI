<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Etiqueta;
use App\Models\EtiquetaProducto;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\ProductoCompraVenta;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\VistaPermisosRolEmpresaUbicacion;
use Illuminate\Support\Facades\Log;

class ProductoCompraVentaController extends Controller
{
    public function index(Request $request)
    {
        if (!tienePermiso('compra/venta - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        // Obtener ubicaciones permitidas según el rol con permiso de lectura
        $ubicacionesPermitidas = VistaPermisosRolEmpresaUbicacion::where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_leer_ubicacion', true)
            ->pluck('ubicacion_id')
            ->toArray();

        $query = DB::table('productos')
            ->join('productos_compraventa', 'productos_compraventa.producto_id', '=', 'productos.id')
            ->join('empresa', 'productos_compraventa.empresaVenta_id', '=', 'empresa.id')
            ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id') // Agregar la relación con ubicación
            ->select(
                'productos.id as id_producto',
                'productos_compraventa.id as id',
                'productos.nombre as nombre',
                'empresa.nombre as empresa',
                'productos_compraventa.existencia',
                'productos_compraventa.estado',
                'productos_compraventa.fechaEntrada',
                'productos_compraventa.fechaSalida',
                'ubicacion.nombre as ubicacion' // Añadir el nombre de la ubicación
            )
            ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas);

        if ($request->filled('nombre')) {
            $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('empresa')) {
            $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
        }

        if ($request->filled('estado')) {
            $query->where('productos_compraventa.estado', $request->estado);
        } else {
            $query->where('productos_compraventa.estado', '!=', 'vendido');
        }

        if ($request->filled('fechaEntrada')) {
            $query->whereDate('productos_compraventa.fechaEntrada', $request->fechaEntrada);
        }

        if ($request->filled('fechaSalida')) {
            $query->whereDate('productos_compraventa.fechaSalida', $request->fechaSalida);
        }

        if ($request->filled('ubicacion')) {
            $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
        }

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

        $productosCompraVenta = $query->paginate(15)->appends($request->all());
        $productosCompraVentaTodos = $query->get();
        $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
            ->where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_insertar_ubicacion', true)
            ->get();
        $empresas = Empresa::where('status', 'externa')->get();
        $empresasInternas = Empresa::where('status', 'interna')->orderBy('nombre')->get();

        return view('productos.compraVenta.index', compact('productosCompraVenta', 'ubicaciones', 'empresas', 'productosCompraVentaTodos', 'empresasInternas'));
    }

    public function insertar(Request $request)
    {
        if (!tienePermiso('compra/venta - insertar') || !Session::has('usuario')) {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para crear un producto de compra-venta.');
        }

        try {
            // Validación
            $request->validate([
                'nombre' => 'required|string|max:150',
                'descripcion' => 'nullable|string',
                'codigoBarra' => 'nullable|string|max:100',
                'empresa_id' => 'required|exists:empresa,id',
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'precio' => 'nullable|numeric|min:0|max:999999.99',
                'empresaVenta_id' => 'required|exists:empresa,id',
                'existencia' => 'required|integer|min:1|max:100000',
                'fechaEntrada' => 'required|date|before_or_equal:today',
                'comentario' => 'nullable|string',
                'etiquetasInsertar' => 'array',
                'etiquetasInsertar.*' => 'exists:etiquetas,id',
            ]);

            // Obtener ID del usuario desde sesión
            $usuarioId = session()->get('usuario')->id;
            $precioCV = $request->filled('precio') ? $request->precio : null;

            // Llamar procedimiento almacenado
            DB::statement('CALL insertar_producto_compraventa(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->descripcion,
                $request->codigoBarra,
                $request->ubicacion_id,
                $precioCV,
                $request->empresaVenta_id,
                $request->existencia,
                $request->fechaEntrada,
                null, // documentacion (por ahora se envía null)
                $request->comentario,
                $usuarioId,
                $request->empresa_id,
            ]);

            // Obtener ID del nuevo producto ingresado
            $productoId = DB::table('productos')->orderBy('id', 'desc')->value('id');

            // Asociar etiquetas al producto si hay
            $etiquetas = $request->input('etiquetasInsertar', []);
            if (!empty($etiquetas)) {
                $datosInsertar = array_map(fn($etiquetaId) => [
                    'producto_id' => $productoId,
                    'clasificacion_id' => $etiquetaId,
                ], $etiquetas);

                EtiquetaProducto::insert($datosInsertar);
            }

            return redirect()->back()->with('success', 'Producto de compra-venta ingresado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al insertar producto de compra-venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al insertar el producto de compra-venta: ' . $e->getMessage());
        }
    }

    public function view($id)
    {
        if (!tienePermiso('compra/venta - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $productoCompraVenta = ProductoCompraVenta::with('empresa')->findOrFail($id);
            $producto = Producto::findOrFail($productoCompraVenta->producto_id);
            $etiquetas = EtiquetaProducto::where('producto_id', $productoCompraVenta->producto_id)->get();
            $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();
            $movimientos = Movimiento::where('id_producto', $productoCompraVenta->id)
                ->where('categoria', 'compraVenta')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();
            $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
                ->where('rol_id', Session::get('usuario')->id_rol)
                ->where('puede_leer_ubicacion', true)
                ->get();
            $todasLasEtiquetas = Etiqueta::where('eliminado', 1)->orderBy('nombre')->get();

            return view('productos.compraVenta.view', compact(
                'productoCompraVenta',
                'producto',
                'etiquetas',
                'usuarios',
                'movimientos',
                'ubicaciones',
                'todasLasEtiquetas'
            ));
        } catch (\Exception $e) {
            Log::error('Error al obtener producto de compra/venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al obtener información del producto.');
        }
    }

    public function actualizarInfo(Request $request, $id)
    {
        try {
            if (!tienePermiso('compra/venta - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'nombre' => 'required|string|max:150',
                'codigoBarra' => 'nullable|string|max:13',
                'descripcion' => 'nullable|string',
                'precio' => 'nullable|numeric|min:0|max:999999.99',
                'comentario' => 'nullable|string',
            ]);

            $pcv = ProductoCompraVenta::findOrFail($id);
            $producto = $pcv->producto;

            $producto->update([
                'nombre' => $request->nombre,
                'codigoBarra' => $request->codigoBarra,
                'descripcion' => $request->descripcion,
                'precio' => $request->filled('precio') ? $request->precio : null,
            ]);

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $pcv->id,
                'categoria' => 'compraventa',
                'accion' => 'Actualización de información',
                'comentario' => $request->comentario,
                'documentacion' => $documento ?? null,
            ]);

            return back()->with('success', 'Información actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }

    public function actualizarUbicacion(Request $request, $id)
    {
        try {
            if (!tienePermiso('compra/venta - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'comentario' => 'nullable|string',
            ]);

            $pcv = ProductoCompraVenta::findOrFail($id);
            $producto = $pcv->producto;

            $cantidadAsociados = ProductoCompraVenta::where('producto_id', $producto->id)->count();

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
                'categoria' => 'compraventa',
                'accion' => 'Cambio de ubicación',
                'comentario' => $request->comentario,
            ]);

            return back()->with('success', 'Ubicación actualizada correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }

    public function actualizarEstado(Request $request, $id)
    {
        try {
            if (!tienePermiso('compra/venta - desactivar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'estado' => 'required|in:almacen,vendido',
                'comentario' => 'nullable|string',
                'documento_baja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);

            $pcv = ProductoCompraVenta::findOrFail($id);
            $pcv->estado = $request->estado;

            if ($request->estado === 'vendido' && $request->hasFile('documento_baja')) {
                $archivo = $request->file('documento_baja');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            
                // Guarda directamente en public/productos/compraventa/doc
                $archivo->move(public_path('productos/compraventa/doc'), $nombreArchivo);
            
                // Genera la URL accesible
                $ruta = asset('productos/compraventa/doc/' . $nombreArchivo);
            }

            $pcv->fechaSalida = now();
            $pcv->save();

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $pcv->id,
                'categoria' => 'compraventa',
                'accion' => 'Cambio de estado',
                'comentario' => $request->comentario,
                'documentacion' => $ruta ?? null,
            ]);

            return back()->with('success', 'Estado actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Ocurrió un error: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error. Comuniquese con el desarrollador. ');
        }
    }
}
