<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Etiqueta;
use App\Models\EtiquetaProducto;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\ProductoCompraVenta;
use App\Models\ProductoConsumible;
use App\Models\ProductoFijo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\VistaPermisosRolEmpresaUbicacion;
use App\Models\VistaProductosUnificada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ApiController extends Controller
{
    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Respuesta JSON exitosa estandarizada.
     */
    private function ok($data, array $meta = []): JsonResponse
    {
        $response = ['success' => true, 'data' => $data];
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        return response()->json($response);
    }

    /**
     * Respuesta JSON de error estandarizada.
     */
    private function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Convierte un paginador de Laravel a un array con meta de paginación.
     */
    private function paginated($paginator): JsonResponse
    {
        return $this->ok($paginator->items(), [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
        ]);
    }

    /**
     * Obtiene las ubicaciones permitidas para el usuario actual.
     */
    private function ubicacionesPermitidas(): array
    {
        return VistaPermisosRolEmpresaUbicacion::where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_leer_ubicacion', true)
            ->pluck('ubicacion_id')
            ->toArray();
    }


    /**
     * Per page con límite máximo de seguridad.
     */
    private function perPage(Request $request, int $default = 15): int
    {
        return min((int) $request->input('per_page', $default), 100);
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================

    /**
     * GET /api/dashboard
     * Estadísticas generales para el dashboard.
     */
    public function dashboard(): JsonResponse
    {
        try {
            $stats = Cache::remember('api_dashboard_stats', 120, function () {
                return [
                    'registros_7_dias'    => Movimiento::registrosUltimos7Dias(),
                    'movimientos_7_dias'  => Movimiento::totalMovimientosUltimos7Dias(),
                    'usuarios_activos'    => Usuario::totalActivosNoEliminados(),
                    'total_existencias'   => DB::table('vista_productos_detalle')->sum('existencia'),
                ];
            });

            if (!esSuperAdmin()) {
                unset($stats['usuarios_activos']);
            }

            return $this->ok($stats);
        } catch (\Throwable $e) {
            Log::error('API dashboard error: ' . $e->getMessage());
            return $this->error('Error al obtener estadísticas del dashboard.', 500);
        }
    }

    // =========================================================================
    // CATÁLOGO GENERAL (vista unificada)
    // =========================================================================

    /**
     * GET /api/productos
     * Catálogo general de productos con filtros y paginación.
     */
    public function productos(Request $request): JsonResponse
    {
        try {
            $query = DB::table('vista_productos_detalle')
                ->leftJoin('productos', 'vista_productos_detalle.id', '=', 'productos.id')
                ->select('vista_productos_detalle.*', 'productos.codigoBarra');

            // Filtrar por permisos de tipo
            $tiposPermitidos = [];
            if (tienePermiso('fijos - leer'))          $tiposPermitidos[] = 'fijo';
            if (tienePermiso('consumible - leer'))      $tiposPermitidos[] = 'consumible';
            if (tienePermiso('compra/venta - leer'))    $tiposPermitidos[] = 'compraventa';

            if (empty($tiposPermitidos)) {
                return $this->error('No cuenta con permisos para ver productos.', 403);
            }
            $query->whereIn('tipo', $tiposPermitidos);

            // Filtros
            if ($request->filled('nombre'))     $query->where('nombre', 'like', '%' . trim($request->nombre) . '%');
            if ($request->filled('precio') && is_numeric($request->precio))         $query->where('precio', '=', $request->precio);
            if ($request->filled('existencia') && is_numeric($request->existencia)) $query->where('existencia', '=', $request->existencia);
            if ($request->filled('ubicacion'))  $query->where('ubicacion_nombre', 'like', '%' . trim($request->ubicacion) . '%');
            if ($request->filled('empresa'))    $query->where('empresa_nombre', 'like', '%' . trim($request->empresa) . '%');
            if ($request->filled('estado'))     $query->where('estado', 'like', '%' . trim($request->estado) . '%');
            if ($request->filled('tipo'))       $query->where('tipo', 'like', '%' . trim($request->tipo) . '%');

            // Filtro por responsable
            if ($request->filled('responsable_id')) {
                $productoIds = ProductoFijo::where('responsable', $request->responsable_id)->pluck('producto_id');
                $query->whereIn('id', $productoIds->isEmpty() ? [0] : $productoIds);
            }

            // Filtro por etiquetas
            if ($request->filled('etiquetas')) {
                $etiquetas = is_array($request->etiquetas)
                    ? array_filter($request->etiquetas, fn($e) => !empty(trim($e)))
                    : [trim($request->etiquetas)];

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

            $paginator = $query->orderBy('nombre', 'asc')->paginate($this->perPage($request, 30));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API productos error: ' . $e->getMessage());
            return $this->error('Error al obtener productos.', 500);
        }
    }

    // =========================================================================
    // PRODUCTOS FIJOS
    // =========================================================================

    /**
     * GET /api/productos/fijos
     */
    public function productosFijos(Request $request): JsonResponse
    {
        if (!tienePermiso('fijos - leer')) {
            return $this->error('No cuenta con permisos para ver productos fijos.', 403);
        }

        try {
            $ubicacionesPermitidas = $this->ubicacionesPermitidas();

            $query = DB::table('productos')
                ->join('productos_fijos', 'productos_fijos.producto_id', '=', 'productos.id')
                ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
                ->leftJoin('empresa', 'productos.empresa_id', '=', 'empresa.id')
                ->leftJoin('usuario', 'productos_fijos.responsable', '=', 'usuario.id')
                ->select(
                    'productos.id as id_producto',
                    'productos_fijos.id as id',
                    'productos.nombre as nombre',
                    'productos.precio as precio',
                    'productos.codigoBarra as codigoBarra',
                    'productos.empresa_id as empresa_id',
                    'empresa.nombre as empresa_nombre',
                    'productos_fijos.clave as clave',
                    'productos_fijos.estado as estado',
                    'productos_fijos.calidad as calidad',
                    'productos_fijos.fechaEntrada as fecha_entrada',
                    'productos_fijos.fotografias',
                    'ubicacion.nombre as ubicacion',
                    DB::raw("CONCAT(usuario.nombre, ' ', usuario.apellido) as responsable_nombre"),
                    DB::raw("'fijo' as tipo_producto")
                )
                ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas);

            // Filtros
            if ($request->filled('clave'))    $query->where('productos_fijos.clave', 'like', '%' . $request->clave . '%');
            if ($request->filled('nombre'))   $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
            if ($request->filled('empresa'))  $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
            if ($request->filled('ubicacion')) $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
            if ($request->filled('responsable_id')) $query->where('productos_fijos.responsable', $request->responsable_id);
            if ($request->filled('fechaEntrada'))   $query->whereDate('productos_fijos.fechaEntrada', $request->fechaEntrada);

            if ($request->filled('estado')) {
                $query->where('productos_fijos.estado', $request->estado);
            } else {
                $query->where('productos_fijos.estado', '!=', 'baja');
            }

            // Filtro de etiquetas
            if ($request->filled('etiquetas')) {
                $etiquetas = is_array($request->etiquetas) ? array_filter($request->etiquetas) : [$request->etiquetas];
                if (count($etiquetas)) {
                    $query->whereIn('productos.id', function ($q) use ($etiquetas) {
                        $q->select('producto_id')
                            ->from('etiquetas_productos')
                            ->join('etiquetas', 'etiquetas.id', '=', 'etiquetas_productos.clasificacion_id')
                            ->whereIn(DB::raw('LOWER(etiquetas.nombre)'), array_map('strtolower', $etiquetas));
                    });
                }
            }

            $paginator = $query->orderBy('productos.nombre', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API productos fijos error: ' . $e->getMessage());
            return $this->error('Error al obtener productos fijos.', 500);
        }
    }

    /**
     * GET /api/productos/fijos/{id}
     */
    public function productoFijo(int $id): JsonResponse
    {
        if (!tienePermiso('fijos - leer')) {
            return $this->error('No cuenta con permisos para ver productos fijos.', 403);
        }

        try {
            $productoFijo = ProductoFijo::with(['producto.ubicacion', 'usuarioResponsable.rol'])->find($id);

            if (!$productoFijo) {
                return $this->error('Producto fijo no encontrado.', 404);
            }

            $producto   = $productoFijo->producto;
            $etiquetas  = Producto::etiquetasPorProducto($producto->id);
            $movimientos = Movimiento::where('id_producto', $productoFijo->id)
                ->where('categoria', 'fijos')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();

            return $this->ok([
                'producto_fijo' => $productoFijo,
                'producto'      => $producto,
                'etiquetas'     => $etiquetas,
                'movimientos'   => $movimientos,
            ]);

        } catch (\Throwable $e) {
            Log::error('API producto fijo detalle error: ' . $e->getMessage());
            return $this->error('Error al obtener el producto fijo.', 500);
        }
    }

    // =========================================================================
    // PRODUCTOS CONSUMIBLES
    // =========================================================================

    /**
     * GET /api/productos/consumibles
     */
    public function productosConsumibles(Request $request): JsonResponse
    {
        if (!tienePermiso('consumible - leer')) {
            return $this->error('No cuenta con permisos para ver productos consumibles.', 403);
        }

        try {
            $ubicacionesPermitidas = $this->ubicacionesPermitidas();

            $query = DB::table('productos')
                ->join('productos_consumibles', 'productos_consumibles.producto_id', '=', 'productos.id')
                ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
                ->leftJoin('empresa', 'productos.empresa_id', '=', 'empresa.id')
                ->select(
                    'productos.id as id_producto',
                    'productos_consumibles.id as id',
                    'productos.nombre as nombre',
                    'productos.precio as precio',
                    'productos.codigoBarra as codigoBarra',
                    'productos.empresa_id as empresa_id',
                    'empresa.nombre as empresa_nombre',
                    'productos_consumibles.existencia as existencia',
                    'ubicacion.nombre as ubicacion',
                    DB::raw("IF(productos_consumibles.existencia > 0, 'activo', 'agotado') as estado"),
                    'productos.fecha_registro as fecha_registro',
                    DB::raw("'consumible' as tipo_producto")
                )
                ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas);

            // Filtros
            if ($request->filled('nombre'))    $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
            if ($request->filled('empresa'))   $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
            if ($request->filled('ubicacion')) $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
            if ($request->filled('estado'))    $query->where(DB::raw("IF(productos_consumibles.existencia > 0, 'activo', 'agotado')"), $request->estado);
            if ($request->filled('fecha_registro')) $query->whereDate('productos.fecha_registro', $request->fecha_registro);

            // Filtro de etiquetas
            if ($request->filled('etiquetas')) {
                $etiquetas = is_array($request->etiquetas) ? array_filter($request->etiquetas) : [$request->etiquetas];
                if (count($etiquetas)) {
                    $query->whereIn('productos.id', function ($q) use ($etiquetas) {
                        $q->select('producto_id')
                            ->from('etiquetas_productos')
                            ->join('etiquetas', 'etiquetas.id', '=', 'etiquetas_productos.clasificacion_id')
                            ->whereIn(DB::raw('LOWER(etiquetas.nombre)'), array_map('strtolower', $etiquetas));
                    });
                }
            }

            $paginator = $query->orderBy('productos.nombre', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API productos consumibles error: ' . $e->getMessage());
            return $this->error('Error al obtener productos consumibles.', 500);
        }
    }

    /**
     * GET /api/productos/consumibles/{id}
     */
    public function productoConsumible(int $id): JsonResponse
    {
        if (!tienePermiso('consumible - leer')) {
            return $this->error('No cuenta con permisos para ver productos consumibles.', 403);
        }

        try {
            $consumible = ProductoConsumible::with('producto.ubicacion')->find($id);

            if (!$consumible) {
                return $this->error('Producto consumible no encontrado.', 404);
            }

            $producto   = $consumible->producto;
            $etiquetas  = Producto::etiquetasPorProducto($producto->id);
            $movimientos = Movimiento::where('id_producto', $consumible->id)
                ->where('categoria', 'consumibles')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();
            $notificaciones = $consumible->notificaciones ?? collect();

            return $this->ok([
                'producto_consumible' => $consumible,
                'producto'            => $producto,
                'etiquetas'           => $etiquetas,
                'movimientos'         => $movimientos,
                'notificaciones'      => $notificaciones,
            ]);

        } catch (\Throwable $e) {
            Log::error('API producto consumible detalle error: ' . $e->getMessage());
            return $this->error('Error al obtener el producto consumible.', 500);
        }
    }

    // =========================================================================
    // PRODUCTOS COMPRA/VENTA
    // =========================================================================

    /**
     * GET /api/productos/compra-venta
     */
    public function productosCompraVenta(Request $request): JsonResponse
    {
        if (!tienePermiso('compra/venta - leer')) {
            return $this->error('No cuenta con permisos para ver productos de compra/venta.', 403);
        }

        try {
            $ubicacionesPermitidas = $this->ubicacionesPermitidas();

            $query = DB::table('productos')
                ->join('productos_compraventa', 'productos_compraventa.producto_id', '=', 'productos.id')
                ->join('empresa', 'productos_compraventa.empresaVenta_id', '=', 'empresa.id')
                ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
                ->select(
                    'productos.id as id_producto',
                    'productos_compraventa.id as id',
                    'productos.nombre as nombre',
                    'productos.precio as precio',
                    'productos.codigoBarra as codigoBarra',
                    'empresa.nombre as empresa',
                    'productos_compraventa.existencia',
                    'productos_compraventa.estado',
                    'productos_compraventa.fechaEntrada as fecha_entrada',
                    'productos_compraventa.fechaSalida as fecha_salida',
                    'ubicacion.nombre as ubicacion',
                    DB::raw("'compraventa' as tipo_producto")
                )
                ->whereIn('productos.ubicacion_id', $ubicacionesPermitidas);

            // Filtros
            if ($request->filled('nombre'))    $query->where('productos.nombre', 'like', '%' . $request->nombre . '%');
            if ($request->filled('empresa'))   $query->where('empresa.nombre', 'like', '%' . $request->empresa . '%');
            if ($request->filled('ubicacion')) $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
            if ($request->filled('fechaEntrada')) $query->whereDate('productos_compraventa.fechaEntrada', $request->fechaEntrada);
            if ($request->filled('fechaSalida'))  $query->whereDate('productos_compraventa.fechaSalida', $request->fechaSalida);

            if ($request->filled('estado')) {
                $query->where('productos_compraventa.estado', $request->estado);
            } else {
                $query->where('productos_compraventa.estado', '!=', 'vendido');
            }

            // Filtro de etiquetas
            if ($request->filled('etiquetas')) {
                $etiquetas = is_array($request->etiquetas) ? array_filter($request->etiquetas) : [$request->etiquetas];
                if (count($etiquetas)) {
                    $query->whereIn('productos.id', function ($q) use ($etiquetas) {
                        $q->select('producto_id')
                            ->from('etiquetas_productos')
                            ->join('etiquetas', 'etiquetas.id', '=', 'etiquetas_productos.clasificacion_id')
                            ->whereIn(DB::raw('LOWER(etiquetas.nombre)'), array_map('strtolower', $etiquetas));
                    });
                }
            }

            $paginator = $query->orderBy('productos.nombre', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API productos compra/venta error: ' . $e->getMessage());
            return $this->error('Error al obtener productos de compra/venta.', 500);
        }
    }

    /**
     * GET /api/productos/compra-venta/{id}
     */
    public function productoCompraVenta(int $id): JsonResponse
    {
        if (!tienePermiso('compra/venta - leer')) {
            return $this->error('No cuenta con permisos para ver productos de compra/venta.', 403);
        }

        try {
            $pcv = ProductoCompraVenta::with(['producto.ubicacion', 'empresa'])->find($id);

            if (!$pcv) {
                return $this->error('Producto de compra/venta no encontrado.', 404);
            }

            $producto   = $pcv->producto;
            $etiquetas  = Producto::etiquetasPorProducto($producto->id);
            $movimientos = Movimiento::where('id_producto', $pcv->id)
                ->where('categoria', 'compraVenta')
                ->with('usuario')
                ->orderByDesc('fecha')
                ->get();

            return $this->ok([
                'producto_compra_venta' => $pcv,
                'producto'              => $producto,
                'etiquetas'             => $etiquetas,
                'movimientos'           => $movimientos,
            ]);

        } catch (\Throwable $e) {
            Log::error('API producto compra/venta detalle error: ' . $e->getMessage());
            return $this->error('Error al obtener el producto de compra/venta.', 500);
        }
    }

    // =========================================================================
    // VEHÍCULOS
    // =========================================================================

    /**
     * GET /api/vehiculos
     */
    public function vehiculos(Request $request): JsonResponse
    {
        if (!tienePermiso('vehiculo - leer')) {
            return $this->error('No cuenta con permisos para ver vehículos.', 403);
        }

        try {
            $ubicacionesPermitidas = $this->ubicacionesPermitidas();

            $query = Vehiculo::query()
                ->select(
                    'vehiculos.*',
                    'ubicacion.nombre as ubicacion_nombre',
                    DB::raw("CONCAT(u.nombre, ' ', u.apellido) as responsable_nombre")
                )
                ->join('ubicacion', 'vehiculos.ubicacion_id', '=', 'ubicacion.id')
                ->join('usuario as u', 'vehiculos.responsable', '=', 'u.id')
                ->where('vehiculos.eliminado', 0)
                ->whereIn('vehiculos.ubicacion_id', $ubicacionesPermitidas);

            // Filtros
            if ($request->filled('marca'))     $query->where('vehiculos.marca', 'like', '%' . $request->marca . '%');
            if ($request->filled('modelo'))    $query->where('vehiculos.modelo', 'like', '%' . $request->modelo . '%');
            if ($request->filled('ubicacion')) $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
            if ($request->filled('responsable_id')) $query->where('vehiculos.responsable', $request->responsable_id);
            if ($request->filled('año'))       $query->where('vehiculos.año', $request->año);

            $paginator = $query->orderBy('vehiculos.marca', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API vehículos error: ' . $e->getMessage());
            return $this->error('Error al obtener vehículos.', 500);
        }
    }

    /**
     * GET /api/vehiculos/{id}
     */
    public function vehiculo(int $id): JsonResponse
    {
        if (!tienePermiso('vehiculo - leer')) {
            return $this->error('No cuenta con permisos para ver vehículos.', 403);
        }

        try {
            $vehiculo = Vehiculo::with(['ubicacion', 'usuarioResponsable', 'especificaciones', 'documentaciones'])
                ->where('eliminado', 0)
                ->find($id);

            if (!$vehiculo) {
                return $this->error('Vehículo no encontrado.', 404);
            }

            $mantenimientos = $vehiculo->mantenimientos()->orderBy('fecha', 'desc')->get();
            $movimientos = Movimiento::where('id_producto', $vehiculo->id)
                ->where('categoria', 'vehiculo')
                ->orderBy('id', 'desc')
                ->get();

            return $this->ok([
                'vehiculo'       => $vehiculo,
                'mantenimientos' => $mantenimientos,
                'movimientos'    => $movimientos,
            ]);

        } catch (\Throwable $e) {
            Log::error('API vehículo detalle error: ' . $e->getMessage());
            return $this->error('Error al obtener el vehículo.', 500);
        }
    }

    // =========================================================================
    // EMPRESAS
    // =========================================================================

    /**
     * GET /api/empresas
     */
    public function empresas(Request $request): JsonResponse
    {
        try {
            $query = Empresa::query();

            if ($request->filled('status'))  $query->where('status', $request->status);
            if ($request->filled('nombre'))  $query->where('nombre', 'like', '%' . $request->nombre . '%');

            $paginator = $query->orderBy('nombre', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API empresas error: ' . $e->getMessage());
            return $this->error('Error al obtener empresas.', 500);
        }
    }

    // =========================================================================
    // USUARIOS
    // =========================================================================

    /**
     * GET /api/usuarios
     */
    public function usuarios(Request $request): JsonResponse
    {
        if (!esSuperAdmin()) {
            return $this->error('Acceso denegado. Solo super administradores pueden ver usuarios.', 403);
        }

        try {
            $query = Usuario::with('rol')
                ->where('estado', 'activo')
                ->where('eliminado', 1);

            if ($request->filled('nombre')) {
                $nombre = trim($request->nombre);
                $query->where(function ($q) use ($nombre) {
                    $q->where('nombre', 'like', "%{$nombre}%")
                      ->orWhere('apellido', 'like', "%{$nombre}%")
                      ->orWhere('apellido_m', 'like', "%{$nombre}%");
                });
            }

            $paginator = $query->orderBy('nombre', 'asc')->paginate($this->perPage($request));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API usuarios error: ' . $e->getMessage());
            return $this->error('Error al obtener usuarios.', 500);
        }
    }

    // =========================================================================
    // ETIQUETAS
    // =========================================================================

    /**
     * GET /api/etiquetas
     */
    public function etiquetas(): JsonResponse
    {
        try {
            $etiquetas = Etiqueta::where('eliminado', 1)->orderBy('nombre')->get();
            return $this->ok($etiquetas);

        } catch (\Throwable $e) {
            Log::error('API etiquetas error: ' . $e->getMessage());
            return $this->error('Error al obtener etiquetas.', 500);
        }
    }

    // =========================================================================
    // MOVIMIENTOS
    // =========================================================================

    /**
     * GET /api/movimientos
     */
    public function movimientos(Request $request): JsonResponse
    {
        try {
            $query = Movimiento::with('usuario');

            if ($request->filled('categoria'))  $query->where('categoria', $request->categoria);
            if ($request->filled('accion'))     $query->where('accion', 'like', '%' . $request->accion . '%');
            if ($request->filled('fecha_desde')) $query->whereDate('fecha', '>=', $request->fecha_desde);
            if ($request->filled('fecha_hasta')) $query->whereDate('fecha', '<=', $request->fecha_hasta);
            if ($request->filled('id_producto')) $query->where('id_producto', $request->id_producto);

            $paginator = $query->orderByDesc('fecha')->paginate($this->perPage($request, 25));
            return $this->paginated($paginator);

        } catch (\Throwable $e) {
            Log::error('API movimientos error: ' . $e->getMessage());
            return $this->error('Error al obtener movimientos.', 500);
        }
    }

    // =========================================================================
    // CÓDIGO DE BARRAS & PISTOLA LECTORA
    // =========================================================================

    /**
     * GET /api/buscar-codigo-barra
     * Búsqueda ultrarrápida para pistola lectora (código de barras o clave).
     */
    public function buscarCodigoBarra(Request $request): JsonResponse
    {
        try {
            $codigo = trim($request->input('codigo', ''));
            if (empty($codigo)) {
                return $this->error('Debe proporcionar un código de barras o clave.', 422);
            }

            // Buscar producto por codigoBarra exacto o por clave de activo fijo
            $producto = Producto::with(['ubicacion', 'empresa', 'fijo', 'consumible', 'compraVenta'])
                ->where(function ($q) use ($codigo) {
                    $q->where('codigoBarra', $codigo)
                      ->orWhereHas('fijo', function ($qFijo) use ($codigo) {
                          $qFijo->where('clave', $codigo);
                      });
                })
                ->first();

            // Si no se encontró exacto, intentar coincidencia parcial
            if (!$producto) {
                $producto = Producto::with(['ubicacion', 'empresa', 'fijo', 'consumible', 'compraVenta'])
                    ->where(function ($q) use ($codigo) {
                        $q->where('codigoBarra', 'LIKE', "%{$codigo}%")
                          ->orWhereHas('fijo', function ($qFijo) use ($codigo) {
                              $qFijo->where('clave', 'LIKE', "%{$codigo}%");
                          });
                    })
                    ->first();
            }

            if (!$producto) {
                return $this->error("No se encontró ningún producto con el código o clave: {$codigo}", 404);
            }

            // Determinar tipo y URLs de acción
            $tipo = 'general';
            $tipoLabel = 'Producto General';
            $urlVer = route('productos', ['nombre' => $producto->nombre]);
            $urlVale = null;
            $clave = null;
            $existencia = null;
            $estado = null;
            $foto = asset('logos/NAO2.png');

            if ($producto->fijo) {
                $tipo = 'fijo';
                $tipoLabel = 'Activo Fijo';
                $urlVer = route('productos_fijos.viewFijos', $producto->fijo->id);
                $urlVale = route('productos.fijos.generarVale', $producto->fijo->id);
                $urlResponsiva = route('responsivas.fijo', ['id' => $producto->fijo->id, 'formato' => 'pdf']);
                $urlResponsivaWord = route('responsivas.fijo', ['id' => $producto->fijo->id, 'formato' => 'word']);
                $clave = $producto->fijo->clave;
                $estado = $producto->fijo->estado;
                if (!empty($producto->fijo->fotografias)) {
                    $fotos = explode(',', $producto->fijo->fotografias);
                    if (!empty($fotos[0])) {
                        $foto = $fotos[0];
                    }
                }
            } elseif ($producto->consumible) {
                $tipo = 'consumible';
                $tipoLabel = 'Consumible';
                $urlVer = route('productos_consumibles.view', $producto->consumible->id);
                $existencia = $producto->consumible->existencia;
            } elseif ($producto->compraVenta) {
                $tipo = 'compraventa';
                $tipoLabel = 'Compra / Venta';
                $urlVer = route('productos_compra_venta.view', $producto->compraVenta->id);
                $existencia = $producto->compraVenta->existencia;
                $estado = $producto->compraVenta->estado;
            }

            $precioStr = is_null($producto->precio) ? 'N/A' : '$' . number_format($producto->precio, 2);

            return $this->ok([
                'id'          => $producto->id,
                'nombre'      => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'codigoBarra' => $producto->codigoBarra,
                'clave'       => $clave,
                'tipo'        => $tipo,
                'tipo_label'  => $tipoLabel,
                'empresa'     => $producto->empresa->nombre ?? 'Sin empresa asignada',
                'ubicacion'   => $producto->ubicacion->nombre ?? 'Sin ubicación asignada',
                'precio'      => $producto->precio,
                'precio_formateado' => $precioStr,
                'foto'        => $foto,
                'estado'      => $estado,
                'existencia'  => $existencia,
                'url_ver'     => $urlVer,
                'url_vale'    => $urlVale,
                'url_responsiva' => $urlResponsiva ?? null,
                'url_responsiva_word' => $urlResponsivaWord ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('API buscarCodigoBarra error: ' . $e->getMessage());
            return $this->error('Error al buscar código de barras.', 500);
        }
    }

    /**
     * GET /api/generar-codigo-barra
     * Genera un código de barras único EAN-13 válido que no colisiona con la BD.
     */
    public function generarCodigoBarra(): JsonResponse
    {
        try {
            $prefix = '20' . date('y'); // Prefijo de uso interno restringido GS1 (20 + 26 = 2026)
            $intento = 0;
            $codigoCompleto = '';
            
            do {
                // 8 dígitos aleatorios para sumar 12 caracteres base con el prefijo (4 + 8 = 12)
                $randomDigits = str_pad((string)mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
                $base12 = $prefix . $randomDigits;
                
                // Calcular dígito verificador EAN-13 (posiciones impares x1, pares x3)
                $sum = 0;
                for ($i = 0; $i < 12; $i++) {
                    $weight = ($i % 2 === 0) ? 1 : 3;
                    $sum += (int)$base12[$i] * $weight;
                }
                $checkDigit = (10 - ($sum % 10)) % 10;
                $codigoCompleto = $base12 . $checkDigit;

                $existe = Producto::where('codigoBarra', $codigoCompleto)->exists();
                $intento++;
            } while ($existe && $intento < 20);

            return $this->ok([
                'codigo' => $codigoCompleto,
                'formato' => 'EAN13',
            ]);

        } catch (\Throwable $e) {
            Log::error('API generarCodigoBarra error: ' . $e->getMessage());
            return $this->error('Error al autogenerar código de barras.', 500);
        }
    }
}
