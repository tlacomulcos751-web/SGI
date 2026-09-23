<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Etiqueta;
use App\Models\EtiquetaProducto;
use App\Models\Movimiento;
use App\Models\Producto;
use Illuminate\Support\Facades\Session;
use App\Models\ProductoFijo;
use App\Models\Usuario;
use App\Models\VistaPermisosRolEmpresaUbicacion;
use App\Models\VistaProductosUnificada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // <-- AGREGADO PARA EL QR
use PhpOffice\PhpWord\TemplateProcessor;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    if (
        (!tienePermiso('fijos - leer') ||
        !tienePermiso('consumible - leer') ||
        !tienePermiso('compra/venta - leer')) && 
        !Session::has('usuario')
    ) {
        return appRedirectToHome('No cuenta con los permisos necesarios');
    }

    $query = DB::table('vista_productos_detalle');

    // Filtrar por permisos
    $tiposPermitidos = [];
    if (function_exists('tienePermiso')) {
        if (tienePermiso('fijos - leer')) $tiposPermitidos[] = 'fijo';
        if (tienePermiso('consumible - leer')) $tiposPermitidos[] = 'consumible';
        if (tienePermiso('compra/venta - leer')) $tiposPermitidos[] = 'compraventa';
        
        if (!empty($tiposPermitidos)) {
            $query->whereIn('tipo', $tiposPermitidos);
        }
    }

    // Filtros de búsqueda
    if ($request->filled('nombre')) {
        $query->where('nombre', 'like', '%' . trim($request->nombre) . '%');
    }

    if ($request->filled('precio') && is_numeric($request->precio)) {
        $query->where('precio', '=', $request->precio);
    }

    if ($request->filled('existencia') && is_numeric($request->existencia)) {
        $query->where('existencia', '=', $request->existencia);
    }

    if ($request->filled('ubicacion')) {
        $query->where('ubicacion_nombre', 'like', '%' . trim($request->ubicacion) . '%');
    }

    if ($request->filled('empresa')) {
        $query->where('empresa_nombre', 'like', '%' . trim($request->empresa) . '%');
    }

    if ($request->filled('estado')) {
        $query->where('estado', 'like', '%' . trim($request->estado) . '%');
    }

    if ($request->filled('tipo')) {
        $query->where('tipo', 'like', '%' . trim($request->tipo) . '%');
    }

    // Añadido: Filtro por responsable
    if ($request->filled('responsable_id')) {
        // Obtener los IDs de los productos fijos asignados a este responsable
        $productoIds = ProductoFijo::where('responsable', $request->responsable_id)
            ->pluck('producto_id');
            
        if ($productoIds->isEmpty()) {
            // Forzar que no retorne resultados si el responsable no tiene productos
            $query->whereIn('id', [0]);
        } else {
            $query->whereIn('id', $productoIds);
        }
    }

    // Filtro de etiquetas
    if ($request->filled('etiquetas')) {
        $etiquetas = array_filter($request->etiquetas, fn($e) => !empty(trim($e)));
        if (count($etiquetas) > 0) {
            $query->whereExists(function ($q) use ($etiquetas) {
                $q->select(DB::raw(1))
                  ->from('etiquetas_productos as ep')
                  ->join('etiquetas as et', 'et.id', '=', 'ep.clasificacion_id')
                  ->whereColumn('ep.producto_id', 'vista_productos_detalle.id')
                  ->whereIn(DB::raw('LOWER(et.nombre)'), array_map('strtolower', $etiquetas));
            });
        }
    }

    // Debug SQL
    if (config('app.debug')) {
        \Log::info('Consulta productos:', [
            'sql' => $query->toSql(),
            'params' => $query->getBindings()
        ]);
    }

    // Paginación
    try {
        $productos = $query->orderBy('nombre', 'asc')->paginate(30);
        $productos->appends($request->all());
    } catch (\Exception $e) {
        \Log::error('Error en paginación de productos: ' . $e->getMessage());
        return back()->with('error', 'Error al cargar productos: ' . $e->getMessage());
    }

    // Etiquetas
    try {
        $etiquetasUnicas = \App\Models\Etiqueta::where('eliminado', 1)->get();
    } catch (\Exception $e) {
        $etiquetasUnicas = collect();
        \Log::error('Error al cargar etiquetas: ' . $e->getMessage());
    }

    // Añadido: Cargar usuarios para el filtro
    $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();

    // Total existencias
    try {
        $totalExistencias = DB::table('vista_productos_detalle')->sum('existencia');
    } catch (\Exception $e) {
        $totalExistencias = 0;
        \Log::error('Error al calcular total existencias: ' . $e->getMessage());
    }

    return view('productos.general.index', compact('productos', 'etiquetasUnicas', 'totalExistencias', 'usuarios'));
}

public function imprimirPorResponsable(Request $request)
{
    if (!Session::has('usuario')) {
        return redirect()->route('login')->with('error', 'Inicie sesión para acceder a esta opción.');
    }

    $query = DB::table('vista_productos_detalle');

    // Mismos filtros que en index
    $tiposPermitidos = [];
    if (function_exists('tienePermiso')) {
        if (tienePermiso('fijos - leer')) $tiposPermitidos[] = 'fijo';
        if (tienePermiso('consumible - leer')) $tiposPermitidos[] = 'consumible';
        if (tienePermiso('compra/venta - leer')) $tiposPermitidos[] = 'compraventa';
        
        if (!empty($tiposPermitidos)) {
            $query->whereIn('tipo', $tiposPermitidos);
        }
    }

    if ($request->filled('nombre')) {
        $query->where('nombre', 'like', '%' . trim($request->nombre) . '%');
    }

    if ($request->filled('precio') && is_numeric($request->precio)) {
        $query->where('precio', '=', $request->precio);
    }

    if ($request->filled('existencia') && is_numeric($request->existencia)) {
        $query->where('existencia', '=', $request->existencia);
    }

    if ($request->filled('ubicacion')) {
        $query->where('ubicacion_nombre', 'like', '%' . trim($request->ubicacion) . '%');
    }

    if ($request->filled('empresa')) {
        $query->where('empresa_nombre', 'like', '%' . trim($request->empresa) . '%');
    }

    if ($request->filled('estado')) {
        $query->where('estado', 'like', '%' . trim($request->estado) . '%');
    }

    if ($request->filled('tipo')) {
        $query->where('tipo', 'like', '%' . trim($request->tipo) . '%');
    }

    if ($request->filled('responsable_id')) {
        $productoIds = ProductoFijo::where('responsable', $request->responsable_id)
            ->pluck('producto_id');
        
        if ($productoIds->isEmpty()) {
            $query->whereIn('id', [0]);
        } else {
            $query->whereIn('id', $productoIds);
        }
    }

    if ($request->filled('etiquetas')) {
        $etiquetas = array_filter($request->etiquetas, fn($e) => !empty(trim($e)));
        if (count($etiquetas) > 0) {
            $query->whereExists(function ($q) use ($etiquetas) {
                $q->select(DB::raw(1))
                  ->from('etiquetas_productos as ep')
                  ->join('etiquetas as et', 'et.id', '=', 'ep.clasificacion_id')
                  ->whereColumn('ep.producto_id', 'vista_productos_detalle.id')
                  ->whereIn(DB::raw('LOWER(et.nombre)'), array_map('strtolower', $etiquetas));
            });
        }
    }

    // Obtenemos los productos sin paginación para impresión
    $productos = $query->orderBy('nombre', 'asc')->get();

    // Nombre del responsable
    $responsable = null;
    if ($request->filled('responsable_id')) {
        $responsable = Usuario::find($request->responsable_id);
    }

    return view('productos.general.imprimir', compact('productos', 'responsable'));
}

public function indexFijos(Request $request)
{
    if (!tienePermiso('fijos - leer') || !Session::has('usuario')) {
        return appRedirectToHome('No cuenta con los permisos necesarios');
    }

    $cantidadPorPagina = 15;

    // Ubicaciones permitidas según rol
    $ubicacionesPermitidas = VistaPermisosRolEmpresaUbicacion::where(
            'rol_id',
            Session::get('usuario')->id_rol
        )
        ->where('puede_leer_ubicacion', true)
        ->pluck('ubicacion_id')
        ->toArray();

    // ===============================
    // QUERY BASE
    // ===============================
    $query = DB::table('productos')
        ->join('productos_fijos', 'productos_fijos.producto_id', '=', 'productos.id')
        ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
        ->leftJoin('empresa', 'productos.empresa_id', '=', 'empresa.id')
        ->select(
            'productos.id as id_fijo',
            'productos_fijos.id as id',
            'productos.nombre as nombre',
            'productos.precio as precio',
            'productos.empresa_id as empresa_id',
            'empresa.nombre as empresa_nombre',
            'productos_fijos.clave as clave',
            DB::raw('COUNT(productos_fijos.producto_id) as existencia'),
            'ubicacion.nombre as ubicacion',
            'productos_fijos.estado as estado',
            'productos_fijos.fechaEntrada as fechaEntrada',
            DB::raw("'fijo' as tipo_producto")
        )
        ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas)
        ->groupBy(
            'productos.id',
            'productos_fijos.id',
            'productos.nombre',
            'productos.precio',
            'productos.empresa_id',
            'empresa.nombre',
            'productos_fijos.clave',
            'productos_fijos.estado',
            'productos_fijos.fechaEntrada',
            'ubicacion.nombre'
        );

    // ===============================
    // FILTROS (WHERE, no HAVING)
    // ===============================

    if ($request->filled('clave')) {
        $query->where('productos_fijos.clave', 'like', '%' . $request->clave . '%');
    }

    if ($request->filled('empresa')) {
        $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
    }

    if ($request->filled('nombre')) {
        $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
    }

    if ($request->filled('ubicacion')) {
        $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
    }

    if ($request->filled('estado')) {
        $query->where('productos_fijos.estado', $request->estado);
    } else {
        // Por defecto solo activos
        $query->where('productos_fijos.estado', '!=', 'baja');
    }

    if ($request->filled('responsable_id')) {
        $query->where('productos_fijos.responsable', $request->responsable_id);
    }

    if ($request->filled('fechaEntrada')) {
        $query->whereDate('productos_fijos.fechaEntrada', $request->fechaEntrada);
    }

    if ($request->filled('responsable_id')) {
        $query->where('productos_fijos.responsable', $request->responsable_id);
    }

    // ===============================
    // FILTRO POR ETIQUETAS
    // ===============================
    if ($request->filled('etiquetas')) {
        $etiquetas = array_filter($request->etiquetas);

        if (count($etiquetas)) {
            $query->whereIn('productos.id', function ($q) use ($etiquetas) {
                $q->select('producto_id')
                    ->from('etiquetas_productos')
                    ->join(
                        'etiquetas',
                        'etiquetas.id',
                        '=',
                        'etiquetas_productos.clasificacion_id'
                    )
                    ->whereIn(
                        DB::raw('LOWER(etiquetas.nombre)'),
                        array_map('strtolower', $etiquetas)
                    );
            });
        }
    }

    // ===============================
    // PAGINACIÓN Y CARGA DE ETIQUETAS EN LOTE (N+1 FIX)
    // ===============================
    $productosFijos = $query
        ->paginate($cantidadPorPagina)
        ->appends($request->all());

    $productoIds = $productosFijos->pluck('id_fijo')->unique()->filter()->toArray();
    $etiquetasMap = [];
    if (!empty($productoIds)) {
        $etiquetasRel = DB::table('etiquetas_productos')
            ->join('etiquetas', 'etiquetas.id', '=', 'etiquetas_productos.clasificacion_id')
            ->whereIn('etiquetas_productos.producto_id', $productoIds)
            ->where('etiquetas.eliminado', 1)
            ->select('etiquetas_productos.producto_id', 'etiquetas.nombre', 'etiquetas.id')
            ->get();

        foreach ($etiquetasRel as $row) {
            $etiquetasMap[$row->producto_id][] = $row;
        }
    }

    foreach ($productosFijos as $item) {
        $item->etiquetas_cargadas = $etiquetasMap[$item->id_fijo] ?? [];
    }

    if ($request->ajax()) {
        return response()->json([
            'html' => view('productos.fijos.table_rows', compact('productosFijos'))->render(),
            'pagination' => view('productos.fijos.pagination', compact('productosFijos'))->render(),
            'total' => $productosFijos->total(),
            'first_item' => $productosFijos->firstItem() ?? 0,
            'last_item' => $productosFijos->lastItem() ?? 0,
        ]);
    }

    // ===============================
    // DATOS AUXILIARES
    // ===============================
    $usuarios = Usuario::where('estado', 'activo')
        ->orderBy('nombre')
        ->get();

    $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
        ->where('rol_id', Session::get('usuario')->id_rol)
        ->where('puede_insertar_ubicacion', true)
        ->get();

    $empresasInternas = Empresa::where('status', 'interna')
        ->orderBy('nombre')
        ->get();

    return view(
        'productos.fijos.index',
        compact(
            'productosFijos',
            'ubicaciones',
            'usuarios',
            'empresasInternas'
        )
    );
}

    public function verQr($id)
    {
        if (!tienePermiso('fijos - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }
        $producto = ProductoFijo::findOrFail($id);
        $qrUrl = "https://quickchart.io/qr?text=" . urlencode($producto->clave) . "&size=500";
        $filename = 'qr_' . $producto->clave . '.png';

        // LA SOLUCIÓN: Usamos Http facade en lugar de file_get_contents para brincar la seguridad del hosting
        try {
            $response = Http::timeout(10)->get($qrUrl);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                return back()->with('error', 'La API de códigos QR no respondió correctamente.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el QR: ' . $e->getMessage());
        }
    }

    // ProductoFijoController.php
    public function insertar(Request $request)
    {
        if (!tienePermiso('fijos - insertar') || !Session::has('usuario')) {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para crear un producto fijo.');
        }

        try {
            $request->validate([
                'nombre' => 'required|string|max:150',
                'descripcion' => 'nullable|string',
                'codigoBarra' => 'nullable|string|max:100',
                'empresa_id' => 'required|exists:empresa,id',
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'precio' => 'nullable|numeric|min:0|max:99999999.99',
                'cantidad' => 'required|integer|min:1|max:10000',
                'estado' => 'required|in:activo,baja,reparacion',
                'calidad' => 'required|in:bueno,regular,malo',
                'responsable' => 'required|exists:usuario,id',
                'fotografias.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
                'fechaEntrada' => 'required|date|before_or_equal:today',
                'documentacion' => 'nullable|string',
                'comentario' => 'nullable|string',
            ]);

            $urls = [];

            // Procesar imagen base64 (captura de cámara)
            if ($request->has('capturaCamara') && $request->capturaCamara) {
                $base64 = $request->capturaCamara;

                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $data = substr($base64, strpos($base64, ',') + 1);
                    $data = base64_decode($data);

                    if ($data === false) {
                        throw new \Exception('Base64 inválido.');
                    }

                    $extension = strtolower($type[1]); // png, jpeg, etc.
                    $fileName = uniqid() . '.' . $extension;
                    $path = "productos/fijos/{$fileName}";
                    $nombreArchivo = uniqid() . '.' . $extension;
                    $ruta = public_path("productos/fijos/{$nombreArchivo}");
                    
                    file_put_contents($ruta, $data);
                    
                    $urls[] = asset("productos/fijos/" . $nombreArchivo);
                }
            }

            // Procesar imágenes normales (input[type=file])
            if ($request->hasFile('fotografias')) {
                foreach ($request->file('fotografias') as $foto) {
                    $nombreArchivo = time() . '_' . $foto->getClientOriginalName();
            
                    // Mover directamente a public/productos/fijos
                    $foto->move(public_path('productos/fijos'), $nombreArchivo);
            
                    // Generar URL accesible desde el navegador
                    $urls[] = asset('productos/fijos/' . $nombreArchivo);
                }
            }

            // Unir todas las URLs en un solo string separado por comas
            $fotografias = implode(',', $urls);

            // Obtener ID del usuario desde la sesión
            $usuarioId = session()->get('usuario')->id;

            $precioFijo = $request->filled('precio') ? $request->precio : null;

            // Llamar procedimiento almacenado
            DB::statement('CALL insertar_producto_fijo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->nombre,
                $request->descripcion,
                $request->codigoBarra,
                $request->ubicacion_id,
                $precioFijo,
                $request->cantidad,
                $request->estado,
                $request->calidad,
                $request->responsable,
                $fotografias,
                $request->fechaEntrada,
                $request->documentacion,
                $request->comentario,
                $usuarioId,
                $request->empresa_id,
            ]);

            // Obtener ID del nuevo producto ingresado
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

            return redirect()->back()->with('success', 'Producto fijo ingresado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al insertar producto fijo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al insertar el producto fijo: ' . $e->getMessage());
        }
    }

    public function viewFijos($id)
    {
        if (!tienePermiso('fijos - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            // Cargar el producto fijo con todas las relaciones necesarias de forma eficiente
            $productoFijo = ProductoFijo::with(['producto.ubicacion', 'producto.empresa', 'usuarioResponsable.rol'])->findOrFail($id);
            $producto = Producto::with('empresa')->findOrFail($productoFijo->producto_id);
            $etiquetas = EtiquetaProducto::where('producto_id', $productoFijo->producto_id)->get();
            $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();
            $movimientos = Movimiento::where('id_producto', $productoFijo->id)
                ->where('categoria', 'fijos')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();
            $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
                ->where('rol_id', Session::get('usuario')->id_rol)
                ->where('puede_leer_ubicacion', true)
                ->get();
            $todasLasEtiquetas = Etiqueta::where('eliminado', 1)->orderBy('nombre')->get();
            return view('productos.fijos.viewFijos', compact(
                'productoFijo',
                'producto',
                'etiquetas',
                'movimientos',
                'ubicaciones',
                'usuarios',
                'todasLasEtiquetas'
            ));
        } catch (\Exception $e) {
            Log::error('Error al obtener producto fijo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al obtener información del producto.');
        }
    }

    public function eliminarImagenesProducto(Request $request)
    {
        if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
            return appRedirectToLogin('No cuenta con los permisos necesarios');
        }

        try {
            $request->validate([
                'productoFijo_id' => 'required|exists:productos_fijos,id',
                'imagenes' => 'required|array|min:1',
                'imagenes.*' => 'string',
            ]);

            $productoFijo = ProductoFijo::findOrFail($request->productoFijo_id);
            $imagenesActuales = $productoFijo->fotografias ? explode(',', $productoFijo->fotografias) : [];
            $imagenesAEliminar = $request->imagenes;

            foreach ($imagenesAEliminar as $url) {
                // Extraer solo el nombre del archivo desde la URL completa
                $pathRelativo = str_replace(asset('productos/fijos') . '/', '', $url);
            
                // Ruta física del archivo dentro de public
                $archivo = public_path('productos/fijos/' . $pathRelativo);
            
                // Eliminar si existe
                if (file_exists($archivo)) {
                    unlink($archivo);
                }
            }

            $imagenesRestantes = array_diff($imagenesActuales, $imagenesAEliminar);
            $productoFijo->fotografias = implode(',', $imagenesRestantes);
            $productoFijo->save();

            // Registrar movimiento
            Movimiento::create([
                'id_user' => session()->get('usuario')->id,
                'id_producto' => $productoFijo->id,
                'categoria' => 'fijos',
                'accion' => 'Eliminación de imágenes',
                'comentario' => 'Se eliminaron ' . count($imagenesAEliminar) . ' imágenes del producto.',
            ]);

            return back()->with('success', 'Imágenes eliminadas correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar imágenes de producto fijo: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar imágenes.');
        }
    }

    public function subirImagenesProducto(Request $request)
    {
        if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
            return appRedirectToLogin('No cuenta con los permisos necesarios');
        }

        $request->validate([
            'productoFijo_id' => 'required|exists:productos_fijos,id',
            'imagenes' => 'required|array|min:1',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        try {
            $productoFijo = ProductoFijo::findOrFail($request->productoFijo_id);
            $imagenesSubidas = [];

        foreach ($request->file('imagenes') as $imagen) {
            $nombreArchivo = time() . '_' . $imagen->getClientOriginalName();
        
            // Mover directamente a public/productos/fijos
            $imagen->move(public_path('productos/fijos'), $nombreArchivo);
        
            // Generar la URL pública correcta
            $imagenesSubidas[] = asset('productos/fijos/' . $nombreArchivo);
        }

            $imagenesActuales = $productoFijo->fotografias ? explode(',', $productoFijo->fotografias) : [];
            $productoFijo->fotografias = implode(',', array_merge($imagenesActuales, $imagenesSubidas));
            $productoFijo->save();

            // Registrar movimiento
            Movimiento::create([
                'id_user' => session()->get('usuario')->id,
                'id_producto' => $productoFijo->id,
                'categoria' => 'fijos',
                'accion' => 'Subida de imágenes',
                'comentario' => 'Se subieron ' . count($imagenesSubidas) . ' nuevas imágenes al producto.',
            ]);

            return back()->with('success', 'Imágenes subidas correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al subir imágenes de producto fijo: ' . $e->getMessage());
            return back()->with('error', 'Error al subir imágenes.');
        }
    }

    public function actualizarInfo(Request $request, $id)
    {
        try {
            if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'nombre' => 'required|string|max:150',
                'codigoBarra' => 'nullable|string|max:13',
                'descripcion' => 'nullable|string',
                'precio' => 'nullable|numeric|min:0|max:99999999.99',
                'estado' => 'required|in:activo,baja,reparacion',
                'calidad' => 'required|in:bueno,regular,malo',
                'comentario' => 'nullable|string',
                'documento_baja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            $productoFijo = ProductoFijo::findOrFail($id);
            $producto = $productoFijo->producto;

            $producto->update([
                'nombre' => $request->nombre,
                'codigoBarra' => $request->codigoBarra,
                'descripcion' => $request->descripcion,
                'precio' => $request->filled('precio') ? $request->precio : null,
            ]);

            $productoFijo->estado = $request->estado;
            $productoFijo->calidad = $request->calidad;

            if ($request->estado === 'baja' && $request->hasFile('documento_baja')) {
                $archivo = $request->file('documento_baja');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            
                // Mover directamente a public/productos/fijos/doc
                $archivo->move(public_path('productos/fijos/doc'), $nombreArchivo);
            
                // Generar URL pública
                $documento = asset('productos/fijos/doc/' . $nombreArchivo);
            }

            $productoFijo->save();

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $productoFijo->id,
                'categoria' => 'fijos',
                'accion' => 'Actualización de información',
                'comentario' => $request->comentario,
                'documentacion' => $documento ?? null
            ]);

            return back()->with('success', 'Información actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function actualizarUbicacion(Request $request, $id)
    {
        try {
            if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'ubicacion_id' => 'required|exists:ubicacion,id',
                'comentario' => 'nullable|string',
            ]);

            $productoFijo = ProductoFijo::findOrFail($id);
            $producto = $productoFijo->producto;

            // Verificar si hay más de un ProductoFijo asociado a este producto
            $cantidadAsociados = ProductoFijo::where('producto_id', $producto->id)->count();

            if ($cantidadAsociados > 1) {
                // Duplicar el producto
                $nuevoProducto = $producto->replicate();
                $nuevoProducto->ubicacion_id = $request->ubicacion_id; // cambiar ubicación
                $nuevoProducto->save();

                // Asignar el nuevo producto al productoFijo actual
                $productoFijo->producto_id = $nuevoProducto->id;
                $productoFijo->save();

                $productoParaMovimiento = $nuevoProducto;
            } else {
                // Si es único, solo cambiar ubicación
                $producto->ubicacion_id = $request->ubicacion_id;
                $producto->save();

                $productoParaMovimiento = $producto;
            }

            // Registrar movimiento
            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $productoFijo->id,
                'categoria' => 'fijos',
                'accion' => 'Cambio de ubicación',
                'comentario' => $request->comentario,
            ]);

            return back()->with('success', 'Ubicación actualizada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function actualizarResponsable(Request $request, $id)
    {
        try {
            if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
                return appRedirectToLogin('Permiso denegado');
            }

            $request->validate([
                'responsable_id' => 'required|exists:usuario,id',
                'comentario' => 'nullable|string',
            ]);

            $productoFijo = ProductoFijo::findOrFail($id);
            $productoFijo->responsable = $request->responsable_id;
            $productoFijo->save();

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $productoFijo->id,
                'categoria' => 'fijos',
                'accion' => 'Cambio de responsable',
                'comentario' => $request->comentario,
            ]);

            return back()->with('success', 'Responsable actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function agregarEtiqueta(Request $request)
    {
        if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
            return appRedirectToHome('Permiso denegado');
        }

        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'clasificacion_id' => 'required|exists:etiquetas,id',
        ]);

        $yaExiste = EtiquetaProducto::where('producto_id', $request->producto_id)
            ->where('clasificacion_id', $request->clasificacion_id)
            ->exists();

        if (!$yaExiste) {
            EtiquetaProducto::create([
                'producto_id' => $request->producto_id,
                'clasificacion_id' => $request->clasificacion_id,
            ]);
        }

        return back()->with('success', 'Etiqueta agregada correctamente.');
    }

    public function eliminarEtiqueta(Request $request)
    {
        if (!tienePermiso('fijos - modificar') || !Session::has('usuario')) {
            return appRedirectToHome('Permiso denegado');
        }

        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'clasificacion_id' => 'required|exists:etiquetas,id',
        ]);

        EtiquetaProducto::where('producto_id', $request->producto_id)
            ->where('clasificacion_id', $request->clasificacion_id)
            ->delete();

        return back()->with('success', 'Etiqueta eliminada correctamente.');
    }

    public function generarValeSalida($id)
    {
        if (!tienePermiso('fijos - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            // Cargamos el producto fijo con sus relaciones necesarias
            $productoFijo = ProductoFijo::with(['producto.ubicacion', 'usuarioResponsable.rol'])->findOrFail($id);
            // Obtenemos el objeto del responsable a través de la relación
            $responsable = $productoFijo->usuarioResponsable;

            $templatePath = base_path('word/Plantilla_Profesional_Vale_Salida_NAO.docx');

            if (!file_exists($templatePath)) {
                Log::error('No se encontró la plantilla del vale de salida en: ' . $templatePath);
                return back()->with('error', 'No se encontró la plantilla del vale de salida. Contacte al administrador.');
            }

            $templateProcessor = new TemplateProcessor($templatePath);

            // Reemplazar variables en la plantilla
            $producto = $productoFijo->producto;
            $ubicacion = optional($producto)->ubicacion;

            $getText = function ($value, $default = 'N/A') {
                if (is_string($value)) {
                    $trimmed = trim($value);
                    return $trimmed !== '' ? $trimmed : $default;
                }
                return $value !== null ? $value : $default;
            };

            $templateProcessor->setValue('fecha', now()->format('d/m/Y'));
            $templateProcessor->setValue('hora', now()->format('H:i'));
            $templateProcessor->setValue('clave_producto', $getText($productoFijo->clave));
            $templateProcessor->setValue('nombre_producto', $getText(optional($producto)->nombre));
            $templateProcessor->setValue('descripcion_producto', $getText(optional($producto)->descripcion, 'N/A'));
            $templateProcessor->setValue('marca_producto', $getText(optional($producto)->marca));
            $templateProcessor->setValue('modelo_producto', $getText(optional($producto)->modelo));
            $templateProcessor->setValue('serie_producto', $getText(optional($producto)->codigoBarra));
            $templateProcessor->setValue('precio_producto', $getText(optional($producto)->precio));
            $templateProcessor->setValue('responsable_nombre', $getText($responsable ? $responsable->nombreCompleto() : null));
            $templateProcessor->setValue('responsable_puesto', $getText($responsable && $responsable->rol ? $responsable->rol->nombre : null));
            $templateProcessor->setValue('ubicacion_actual', $getText(optional($ubicacion)->nombre));

            $fileName = 'Vale_Salida_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', $getText(optional($producto)->nombre, 'producto'))) . '_' . now()->format('Ymd') . '.docx';
            $tempDir = storage_path('app/temp');
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error al generar el vale de salida para el producto fijo ID ' . $id . ': ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al generar el documento. ' . $e->getMessage());
        }
    }
}