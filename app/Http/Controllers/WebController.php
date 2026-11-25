<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\ProductoFijo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index()
    {
        if (Session::has('usuario')) {
            return redirect()->route('web.home'); 
        }
        return view('login');
    }

    public function verificarLogin(Request $request)
    {
        $login = trim($request->input('login', $request->input('email', '')));
        $password = $request->input('password');

        try {
            // 1. Buscar al usuario por correo o por nombre de usuario
            $usuario = \App\Models\Usuario::where(function ($query) use ($login) {
                $query->where('gmail', $login)
                      ->orWhere('nombre_user', $login);
            })->first();

            // 2. Verificar si existe y si la contraseña coincide
            if (!$usuario) {
                return redirect()->back()->withInput()->with('error', 'Usuario/correo o contraseña incorrectos, verifica tus credenciales.');
            }

            try {
                $passwordValida = Hash::check($password, $usuario->password);
            } catch (\RuntimeException $e) {
                $passwordValida = false;
            }

            // Si Bcrypt no funcionó, probar formatos legacy
            if (!$passwordValida) {
                if (
                    $usuario->password === hash('sha256', $password . $usuario->uuid) ||
                    $usuario->password === hash('sha256', $password) ||
                    $usuario->password === md5($password) ||
                    $usuario->password === sha1($password) ||
                    $usuario->password === $password  // texto plano
                ) {
                    $passwordValida = true;
                    // Auto-actualizar a Bcrypt para futuras sesiones
                    $usuario->password = Hash::make($password);
                    $usuario->save();
                }
            }

            if (!$passwordValida) {
                return redirect()->back()->withInput()->with('error', 'Usuario/correo o contraseña incorrectos, verifica tus credenciales.');
            }

            // 3. Preparar el objeto del usuario para la sesión (emulando lo que devolvía el Stored Procedure)
            $datosUsuario = (object) $usuario->toArray();
            $datosUsuario->error = 0;
            $datosUsuario->mensaje = 'Inicio de sesión exitoso';

            // 4. Obtener los permisos del usuario usando su rol
            $permisosUnificados = \Illuminate\Support\Facades\DB::select('
                SELECT vp.*
                FROM vista_permisos vp
                WHERE vp.rol = ?
            ', [$usuario->id_rol]);

            // 5. Guardar en sesión
            Session::put('usuario', $datosUsuario);
            Session::put('usuario_permisos', $permisosUnificados);
            
            // Forzamos a que el servidor escriba la sesión antes del redirect
            Session::save(); 

            // Redirección directa al dashboard
            return redirect()->route('web.home'); 

        } catch (\Throwable $e) {
            Log::error('Error en inicio de sesión: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo completar el inicio de sesión en este momento. Por favor, intenta nuevamente más tarde.');
        }
    }

    public function home()
    {
        // Si la sesión se perdió en el salto, nos avisa
        if (!Session::has('usuario')) {
            return redirect(url('/'))->with('error', 'La sesión se perdió en el servidor. Vuelve a intentar.');
        }

        try {
            $usuario = Session::get('usuario');
            if ($usuario && isset($usuario->id_rol)) {
                $permisosUnificados = \Illuminate\Support\Facades\DB::select('
                    SELECT vp.*
                    FROM vista_permisos vp
                    WHERE vp.rol = ?
                ', [$usuario->id_rol]);
                Session::put('usuario_permisos', $permisosUnificados);
            }

            $registros7Dias = Movimiento::registrosUltimos7Dias();
            $movimientos7Dias = Movimiento::totalMovimientosUltimos7Dias();
            $usuariosActivos = (esSuperAdmin() || tienePermiso('leerUsuarios')) ? Usuario::totalActivosNoEliminados() : 0;
            
            // Consumibles con stock bajo / crítico (≤ 10% / ≤ 10 unidades)
            $consumiblesCriticos = \Illuminate\Support\Facades\DB::table('productos_consumibles as pc')
                ->join('productos as p', 'pc.producto_id', '=', 'p.id')
                ->leftJoin('ubicacion as u', 'p.ubicacion_id', '=', 'u.id')
                ->select(
                    'pc.id as consumible_id',
                    'p.id as producto_id',
                    'p.nombre',
                    'pc.existencia',
                    'u.nombre as ubicacion_nombre'
                )
                ->where('pc.existencia', '<=', 10)
                ->orderBy('pc.existencia', 'asc')
                ->get();

            $totalConsumiblesCriticos = $consumiblesCriticos->count();
            $consumiblesAgotados = $consumiblesCriticos->where('existencia', '<=', 0)->count();
            $consumiblesPorAgotar = $consumiblesCriticos->where('existencia', '>', 0)->count();

            // Mantenimientos preventivos próximos (≤ 1 mes / 30 días o vencidos)
            $mantenimientosProximos = function_exists('obtenerMantenimientosProximos') 
                ? obtenerMantenimientosProximos(30, 18) 
                : collect();
            $totalMantenimientosProximos = function_exists('contarMantenimientosProximos') 
                ? contarMantenimientosProximos(30) 
                : 0;
            $mantenimientosVencidos = $mantenimientosProximos->where('dias_restantes', '<', 0)->count();
            $mantenimientosUrgentes = $mantenimientosProximos->filter(function($item) {
                return $item->dias_restantes >= 0 && $item->dias_restantes <= 15;
            })->count();
            $mantenimientosPorVenir = $mantenimientosProximos->filter(function($item) {
                return $item->dias_restantes > 15;
            })->count();

            // Bienes y activos a cargo del usuario autenticado
            $userId = $usuario?->id;
            $misVehiculos = collect();
            $misActivosFijos = collect();

            if ($userId) {
                // Vehículos asignados al usuario con mantenimientos recientes
                $misVehiculos = Vehiculo::where('responsable', $userId)
                    ->where('eliminado', 0)
                    ->with([
                        'ubicacion',
                        'mantenimientos' => function ($q) {
                            $q->orderBy('fecha', 'desc')->take(3);
                        }
                    ])
                    ->get();

                // Activos Fijos asignados al usuario
                $misActivosFijos = ProductoFijo::where('responsable', $userId)
                    ->with([
                        'producto.ubicacion',
                        'producto.empresa',
                        'mantenimientos' => function ($q) {
                            $q->orderBy('fecha', 'desc');
                        }
                    ])
                    ->get();
            }

            $totalCosasACargo = $misVehiculos->count() + $misActivosFijos->count();

            // Datos para el modal "Agregar producto a mi cargo"
            $empresas = \App\Models\Empresa::orderBy('nombre')->get();
            $ubicaciones = \App\Models\Ubicacion::orderBy('nombre')->get();
            $activosFijosDisponibles = ProductoFijo::with(['producto.ubicacion'])
                ->where('estado', '!=', 'baja')
                ->get();
            $vehiculosDisponibles = Vehiculo::where('eliminado', 0)->with('ubicacion')->get();
            $todosUsuarios = puedeAsignarArticulos() 
                ? Usuario::where('eliminado', 1)->orderBy('nombre')->get() 
                : collect();

            return view('web.home', compact(
                'registros7Dias', 
                'movimientos7Dias', 
                'usuariosActivos',
                'consumiblesCriticos',
                'totalConsumiblesCriticos',
                'consumiblesAgotados',
                'consumiblesPorAgotar',
                'mantenimientosProximos',
                'totalMantenimientosProximos',
                'mantenimientosVencidos',
                'mantenimientosUrgentes',
                'mantenimientosPorVenir',
                'misVehiculos',
                'misActivosFijos',
                'totalCosasACargo',
                'empresas',
                'ubicaciones',
                'activosFijosDisponibles',
                'vehiculosDisponibles',
                'todosUsuarios'
            ));

        } catch (\Throwable $e) {
            Log::error('Error al cargar el panel principal: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'No se pudo cargar el panel principal en este momento. Intenta nuevamente más tarde.');
        }
    }

    /**
     * Asigna un artículo o vehículo existente a cargo de un usuario.
     * Si el usuario no es Super Admin, solo puede asignarse a sí mismo.
     */
    public function asignarProductoACargo(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'tipo' => 'required|in:fijo,vehiculo',
            'item_id' => 'required|integer',
            'responsable_id' => 'nullable|exists:usuario,id',
            'proyecto' => 'nullable|string|max:255',
            'comentario' => 'nullable|string|max:255',
        ]);

        $usuarioActual = Session::get('usuario');
        
        // Si no tiene permiso para asignar artículos, solo puede asignarse a sí mismo
        if (!puedeAsignarArticulos()) {
            $responsableId = $usuarioActual->id;
        } else {
            $responsableId = $request->responsable_id ? (int)$request->responsable_id : $usuarioActual->id;
        }

        $targetUsuario = Usuario::find($responsableId);
        $nombreTarget = $targetUsuario ? trim($targetUsuario->nombre . ' ' . ($targetUsuario->apellido ?? '')) : 'el usuario';
        $proyectoSeleccionado = $request->filled('proyecto') ? trim($request->proyecto) : null;

        if ($request->tipo === 'fijo') {
            $fijo = ProductoFijo::with('producto')->findOrFail($request->item_id);
            $fijo->responsable = $responsableId;
            if ($proyectoSeleccionado) {
                $fijo->proyecto = $proyectoSeleccionado;
            }
            $fijo->save();

            $comentarioFinal = $request->comentario ?: "Artículo '{$fijo->producto?->nombre}' puesto a cargo de {$nombreTarget}.";
            if ($proyectoSeleccionado) {
                $comentarioFinal .= " [Proyecto: {$proyectoSeleccionado}]";
            }

            Movimiento::create([
                'id_user' => $usuarioActual->id,
                'id_producto' => $fijo->id,
                'categoria' => 'fijos',
                'accion' => 'Asignación a resguardo',
                'fecha' => now(),
                'proyecto' => $proyectoSeleccionado,
                'comentario' => $comentarioFinal,
            ]);

            return redirect()->back()->with('success', "Activo fijo puesto a cargo de {$nombreTarget} correctamente.");
        } else {
            $vehiculo = Vehiculo::findOrFail($request->item_id);
            $vehiculo->responsable = $responsableId;
            if ($proyectoSeleccionado) {
                $vehiculo->proyecto = $proyectoSeleccionado;
            }
            $vehiculo->save();

            $comentarioFinal = $request->comentario ?: "Vehículo '{$vehiculo->marca} {$vehiculo->modelo}' ({$vehiculo->placas}) puesto a cargo de {$nombreTarget}.";
            if ($proyectoSeleccionado) {
                $comentarioFinal .= " [Proyecto: {$proyectoSeleccionado}]";
            }

            Movimiento::create([
                'id_user' => $usuarioActual->id,
                'id_producto' => $vehiculo->id,
                'categoria' => 'vehiculo',
                'accion' => 'Asignación a resguardo',
                'fecha' => now(),
                'proyecto' => $proyectoSeleccionado,
                'comentario' => $comentarioFinal,
            ]);

            return redirect()->back()->with('success', "Vehículo puesto a cargo de {$nombreTarget} correctamente.");
        }
    }

    /**
     * Registra un nuevo activo fijo directamente asignado al usuario.
     * Si no es Super Admin, se asigna únicamente a sí mismo.
     */
    public function registrarNuevoProductoACargo(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'codigoBarra' => 'nullable|string|max:100',
            'empresa_id' => 'required|exists:empresa,id',
            'ubicacion_id' => 'required|exists:ubicacion,id',
            'precio' => 'nullable|numeric|min:0|max:99999999.99',
            'estado' => 'required|in:activo,baja,reparacion',
            'calidad' => 'required|in:bueno,regular,malo',
            'responsable_id' => 'nullable|exists:usuario,id',
            'proyecto' => 'nullable|string|max:255',
            'comentario' => 'nullable|string',
        ]);

        $usuarioActual = Session::get('usuario');
        
        // Si no tiene permiso para asignar artículos, solo puede asignarse a sí mismo
        if (!puedeAsignarArticulos()) {
            $responsableId = $usuarioActual->id;
        } else {
            $responsableId = $request->responsable_id ? (int)$request->responsable_id : $usuarioActual->id;
        }

        $targetUsuario = Usuario::find($responsableId);
        $nombreTarget = $targetUsuario ? trim($targetUsuario->nombre . ' ' . ($targetUsuario->apellido ?? '')) : 'el usuario';
        $precioFijo = $request->filled('precio') ? $request->precio : null;

        $proyectoSeleccionado = $request->filled('proyecto') ? trim($request->proyecto) : null;
        $comentarioFinal = $request->comentario ?: "Registrado y puesto a cargo de {$nombreTarget}";
        if ($proyectoSeleccionado) {
            $comentarioFinal .= " [Proyecto: {$proyectoSeleccionado}]";
        }

        // Llamar procedimiento almacenado insertar_producto_fijo (15 parámetros)
        DB::statement('CALL insertar_producto_fijo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $request->nombre,
            $request->descripcion ?? '',
            $request->codigoBarra ?? null,
            $request->ubicacion_id,
            $precioFijo,
            1, // cantidad
            $request->estado,
            $request->calidad,
            $responsableId,
            null, // fotografias
            now()->format('Y-m-d'),
            null, // documentacion
            $comentarioFinal,
            $usuarioActual->id,
            $request->empresa_id,
        ]);

        $ultimoProductoId = DB::table('productos')->orderBy('id', 'desc')->value('id');
        if ($ultimoProductoId && $proyectoSeleccionado) {
            DB::table('productos_fijos')->where('producto_id', $ultimoProductoId)->update([
                'proyecto' => $proyectoSeleccionado
            ]);

            $ultimoFijo = DB::table('productos_fijos')->where('producto_id', $ultimoProductoId)->first();
            if ($ultimoFijo) {
                DB::table('movimiento')
                    ->where('id_producto', $ultimoFijo->id)
                    ->where('categoria', 'fijos')
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->update(['proyecto' => $proyectoSeleccionado]);
            }
        }

        return redirect()->back()->with('success', "Artículo registrado y puesto a cargo de {$nombreTarget} exitosamente.");
    }

    /**
     * Obtiene los proyectos contratados (ND) desde la API de Nodo Group.
     * Almacena en caché por 5 minutos para una respuesta instantánea.
     */
    public function obtenerProyectosNodo()
    {
        try {
            $proyectos = Cache::remember('proyectos_nodo_activos', 300, function () {
                $response = Http::withoutVerifying()
                    ->timeout(4)
                    ->get('https://nodogroup.com.mx/pr/api/proyectos.php?etapa=ND&formato=plano&incluir_metricas=0');

                if ($response->successful()) {
                    $json = $response->json();
                    $items = $json['data'] ?? [];
                    $formateados = [];

                    foreach ($items as $item) {
                        $folio = $item['folio'] ?? '';
                        $nombre = $item['nombre'] ?? '';
                        $cliente = $item['cliente_referencia'] ?? '';
                        $empresa = $item['empresa']['nombre'] ?? '';

                        $label = $folio ? "[{$folio}] {$nombre}" : $nombre;
                        if ($cliente && !str_contains($nombre, $cliente)) {
                            $label .= " — {$cliente}";
                        }

                        $formateados[] = [
                            'id' => $item['id'],
                            'folio' => $folio,
                            'nombre' => $nombre,
                            'cliente' => $cliente,
                            'empresa' => $empresa,
                            'label' => $label,
                            'valor' => $label,
                        ];
                    }
                    return $formateados;
                }
                return [];
            });

            return response()->json([
                'success' => true,
                'data' => $proyectos
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al consultar proyectos API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [],
                'error' => 'No se pudieron consultar los proyectos en este momento.'
            ], 500);
        }
    }

    /**
     * Retira un artículo o vehículo del cargo de un usuario.
     * Empleados normales solo pueden retirar sus propios artículos a cargo.
     */
    public function quitarProductoACargo(Request $request)
    {
        if (!Session::has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'tipo' => 'required|in:fijo,vehiculo',
            'item_id' => 'required|integer',
            'nuevo_responsable_id' => 'nullable|exists:usuario,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        $usuarioActual = Session::get('usuario');
        $nuevoResponsableId = $request->nuevo_responsable_id ? (int)$request->nuevo_responsable_id : 1;
        $destUsuario = Usuario::find($nuevoResponsableId);
        $nombreDestino = $destUsuario ? trim($destUsuario->nombre . ' ' . ($destUsuario->apellido ?? '')) : 'Almacén General';

        if ($request->tipo === 'fijo') {
            $fijo = ProductoFijo::with('producto')->findOrFail($request->item_id);

            // Regla: si no tiene permiso para asignar artículos, solo puede liberar artículos bajo su propio cargo
            if (!puedeAsignarArticulos() && $fijo->responsable != $usuarioActual->id) {
                return redirect()->back()->with('error', 'Solo tienes permiso para liberar artículos bajo tu propio cargo.');
            }

            $fijo->responsable = $nuevoResponsableId;
            $fijo->save();

            Movimiento::create([
                'id_user' => $usuarioActual->id,
                'id_producto' => $fijo->id,
                'categoria' => 'fijos',
                'accion' => 'Liberación de resguardo',
                'fecha' => now(),
                'comentario' => $request->motivo ?: "Artículo '{$fijo->producto?->nombre}' retirado de cargo y transferido a {$nombreDestino}.",
            ]);

            return redirect()->back()->with('success', 'El artículo ha sido retirado de tu cargo y devuelto a almacén.');
        } else {
            $vehiculo = Vehiculo::findOrFail($request->item_id);

            if (!puedeAsignarArticulos() && $vehiculo->responsable != $usuarioActual->id) {
                return redirect()->back()->with('error', 'Solo tienes permiso para liberar vehículos bajo tu propio cargo.');
            }

            $vehiculo->responsable = $nuevoResponsableId;
            $vehiculo->save();

            Movimiento::create([
                'id_user' => $usuarioActual->id,
                'id_producto' => $vehiculo->id,
                'categoria' => 'vehiculo',
                'accion' => 'Liberación de resguardo',
                'fecha' => now(),
                'comentario' => $request->motivo ?: "Vehículo '{$vehiculo->marca} {$vehiculo->modelo}' retirado de cargo y transferido a {$nombreDestino}.",
            ]);

            return redirect()->back()->with('success', 'El vehículo ha sido retirado de tu cargo y devuelto a flotilla.');
        }
    }
}