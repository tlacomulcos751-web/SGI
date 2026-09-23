<?php

namespace App\Http\Controllers;

use App\Models\DocumentacionVehiculo;
use App\Models\MantenimientoVehiculo;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\VistaPermisosRolEmpresaUbicacion;
use Illuminate\Support\Facades\Log;

class VehiculosController extends Controller
{
    public function index(Request $request)
    {
        if (!tienePermiso('vehiculo - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $cantidadPorPagina = 15;

        // Ubicaciones permitidas por rol
        $ubicacionesPermitidas = VistaPermisosRolEmpresaUbicacion::where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_leer_ubicacion', true)
            ->pluck('ubicacion_id')
            ->toArray();

        // Query de vehículos
        $query = Vehiculo::with(['ubicacion', 'usuarioResponsable'])
            ->select(
                'vehiculos.*',
                'ubicacion.nombre as ubicacion',
                DB::raw("CONCAT(u.nombre, ' ', u.apellido) as responsable_nombre"),
                DB::raw("IF(vehiculos.eliminado = 0, 'activo', 'eliminado') as estado")
            )
            ->join('ubicacion', 'vehiculos.ubicacion_id', '=', 'ubicacion.id')
            ->join('usuario as u', 'vehiculos.responsable', '=', 'u.id')
            ->where('vehiculos.eliminado', 0)
            ->whereIn('vehiculos.ubicacion_id', $ubicacionesPermitidas);

        // Filtros
        if ($request->filled('marca')) {
            $query->where('vehiculos.marca', 'like', '%' . $request->marca . '%');
        }

        if ($request->filled('modelo')) {
            $query->where('vehiculos.modelo', 'like', '%' . $request->modelo . '%');
        }

        if ($request->filled('ubicacion')) {
            $query->where('ubicacion.nombre', 'like', '%' . $request->ubicacion . '%');
        }

        if ($request->filled('responsable_id')) {
            $query->where('vehiculos.responsable', $request->responsable_id);
        }

        if ($request->filled('estado')) {
            $query->having('estado', $request->estado);
        }

        if ($request->filled('año')) {
            $query->where('vehiculos.año', $request->año);
        }

        $vehiculos = $query->paginate($cantidadPorPagina)->appends($request->all());
        $vehiculosTodos = $query->get();

        $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();

        $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
            ->where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_insertar_ubicacion', true)
            ->get();

        return view('productos.vehiculos.index', compact('vehiculos', 'ubicaciones', 'usuarios', 'vehiculosTodos'));
    }

    public function crear()
    {
        if (!tienePermiso('vehiculo - insertar') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $ubicaciones = VistaPermisosRolEmpresaUbicacion::with('ubicacion')
            ->where('rol_id', Session::get('usuario')->id_rol)
            ->where('puede_insertar_ubicacion', true)
            ->get();

        $usuarios = Usuario::where('estado', 'activo')->orderBy('nombre')->get();
        return view('productos.vehiculos.create', compact('ubicaciones', 'usuarios'));
    }

    public function insertar(Request $request)
    {
        if (!tienePermiso('vehiculo - insertar') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }
        // Validar los campos principales (puedes expandir la validación según necesites)
        $validated = $request->validate([
            'tipo' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'año' => 'required|numeric|min:1900',
            'niv' => 'required|string|max:255',
            'placas' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
            'carroceria' => 'nullable|string|max:255',
            'combustible' => 'nullable|string|max:255',
            'transmision' => 'nullable|string|max:255',
            'ubicacion_id' => 'required|exists:ubicacion,id',
            'responsable' => 'required|exists:usuario,id',
        ]);

        try {
            // Crear Vehículo
            $vehiculo = Vehiculo::create([
                ...$validated,
                'eliminado' => false, // si es un booleano en tu base de datos
            ]);

            // Crear Especificaciones
            if ($request->has('especificaciones')) {
                $vehiculo->especificaciones()->create($request->input('especificaciones'));
            }

            // Crear Documentos múltiples
            if ($request->has('documentos')) {
                foreach ($request->input('documentos') as $doc) {
                    if (!empty($doc['tipo']) && !empty($doc['numero_documento'])) {
                        $vehiculo->documentaciones()->create([
                            'tipo' => $doc['tipo'],
                            'numero_documento' => $doc['numero_documento'],
                            'vigencia_inicio' => $doc['vigencia_inicio'] ?? null,
                            'vigencia_fin' => $doc['vigencia_fin'] ?? null,
                            'estatus' => $doc['estatus'] ?? 'Pendiente',
                        ]);
                    }
                }
            }

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $vehiculo->id,
                'categoria' => 'vehiculo',
                'accion' => 'Registro',
                'comentario' => $request->comentario,
                'documentacion' => $documento ?? null,
            ]);

            return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al insertar vehículo: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al crear el vehículo   ');
        }
    }

    public function ver($id)
    {
        if (!tienePermiso('vehiculo - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $vehiculo = Vehiculo::with(['ubicacion', 'usuarioResponsable', 'especificaciones', 'documentaciones'])
                ->findOrFail($id);

            $mantenimientos = $vehiculo->mantenimientos()->orderBy('fecha', 'desc')->paginate(10);
            $movimientos = Movimiento::where('id_producto', $vehiculo->id)
                ->where('categoria', 'vehiculo')
                ->orderBy('id', 'desc')
                ->get();

            return view('productos.vehiculos.ver', compact('vehiculo', 'mantenimientos', 'movimientos'));
        } catch (\Exception $e) {
            Log::error('Error al obtener el vehículo: ' . $e->getMessage());
            return redirect()->route('vehiculos.index')->withErrors('Error al cargar el vehículo');
        }
    }

    public function generarValeSalida($id)
    {
        if (!tienePermiso('vehiculo - leer') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        try {
            $vehiculo = Vehiculo::with(['ubicacion', 'usuarioResponsable.rol'])->findOrFail($id);
            $responsable = $vehiculo->usuarioResponsable;
            $ubicacion = $vehiculo->ubicacion;

            $templatePath = base_path('word/Plantilla_Profesional_Vale_Salida_NAO.docx');

            if (!file_exists($templatePath)) {
                Log::error('No se encontró la plantilla del vale de salida en: ' . $templatePath);
                return back()->with('error', 'No se encontró la plantilla del vale de salida. Contacte al administrador.');
            }

            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            $getText = function ($value, $default = 'N/A') {
                if (is_string($value)) {
                    $trimmed = trim($value);
                    return $trimmed !== '' ? $trimmed : $default;
                }
                return $value !== null ? $value : $default;
            };

            $templateProcessor->setValue('fecha', now()->format('d/m/Y'));
            $templateProcessor->setValue('hora', now()->format('H:i'));
            $templateProcessor->setValue('clave_producto', $getText($vehiculo->placas));
            $templateProcessor->setValue('nombre_producto', $getText($vehiculo->tipo . ' ' . $vehiculo->marca . ' ' . $vehiculo->modelo));
            $templateProcessor->setValue('descripcion_producto', $getText('Color: ' . $vehiculo->color . ', Carrocería: ' . $vehiculo->carroceria . ', Versión: ' . $vehiculo->version));
            $templateProcessor->setValue('marca_producto', $getText($vehiculo->marca));
            $templateProcessor->setValue('modelo_producto', $getText($vehiculo->modelo));
            $templateProcessor->setValue('serie_producto', $getText($vehiculo->niv));
            $templateProcessor->setValue('precio_producto', 'N/A');
            $templateProcessor->setValue('responsable_nombre', $getText($responsable ? $responsable->nombreCompleto() : null));
            $templateProcessor->setValue('responsable_puesto', $getText($responsable && $responsable->rol ? $responsable->rol->nombre : null));
            $templateProcessor->setValue('ubicacion_actual', $getText(optional($ubicacion)->nombre));

            $fileName = 'Vale_Salida_Vehiculo_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', $getText($vehiculo->placas, 'vehiculo'))) . '_' . now()->format('Ymd') . '.docx';
            $tempDir = storage_path('app/temp');
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            if (!\Illuminate\Support\Facades\File::exists($tempDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($tempDir, 0755, true);
            }

            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error al generar vale de salida de vehículo: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el vale de salida.');
        }
    }

    public function insertarDocumentacion(Request $request, $id)
    {
        if (!tienePermiso('vehiculo - modificar') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        // Validar cada documento (puedes ajustar las reglas según tu modelo)
        $validated = $request->validate([
            'documentos' => 'required|array',
            'documentos.*.tipo' => 'required|string|max:255',
            'documentos.*.numero_documento' => 'required|string|max:255',
            'documentos.*.vigencia_inicio' => 'nullable|date',
            'documentos.*.vigencia_fin' => 'nullable|date',
            'documentos.*.estatus' => 'nullable|string|max:255',
        ]);

        try {
            $idVehiculo = $id;
            foreach ($validated['documentos'] as $doc) {
                DocumentacionVehiculo::create([
                    'vehiculo_id' => $id,
                    'tipo' => $doc['tipo'],
                    'numero_documento' => $doc['numero_documento'],
                    'vigencia_inicio' => $doc['vigencia_inicio'] ?? null,
                    'vigencia_fin' => $doc['vigencia_fin'] ?? null,
                    'estatus' => $doc['estatus'] ?? 'Pendiente',
                ]);
            }

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $idVehiculo,
                'categoria' => 'vehiculo',
                'accion' => 'Registro de documentación. No. de registros: ' . count($validated['documentos']),
                'comentario' => $request->comentario,
                'documentacion' => $documento ?? null,
            ]);

            return redirect()->back()->with('success', 'Documentos agregrado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al insertar documentos: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al agregrar los documentos');
        }
    }

    public function insertarMantenimiento(Request $request, $id)
    {
        if (!tienePermiso('vehiculo - modificar') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        // Validar los campos del mantenimiento
        $validated = $request->validate([
            'fecha'           => 'required|date',
            'kilometraje'     => 'required|integer|min:0',
            'tipo_servicio'   => 'required|string|max:100',
            'descripcion'     => 'nullable|string|max:1000',
            'taller'          => 'nullable|string|max:255',
        ]);

        try {
            $mantenimiento = new MantenimientoVehiculo($validated);
            $mantenimiento->vehiculo_id = $id;
            $mantenimiento->save();

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $id,
                'categoria' => 'vehiculo',
                'accion' => 'Registro de mantenimiento',
                'comentario' => $request->comentario,
                'documentacion' => null,
            ]);

            return redirect()->back()->with('success', 'Mantenimiento agregado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al insertar mantenimiento: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al agregar el mantenimiento');
        }
    }

    public function eliminar($id, Request $request)
    {
        if (!tienePermiso('vehiculo - desactivar') || !Session::has('usuario')) {
            return appRedirectToHome('No cuenta con los permisos necesarios');
        }

        $request->validate([
            'archivo_respaldo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->eliminado = 1; // Marcar como eliminado
            $vehiculo->save();

            $archivo = $request->file('archivo_respaldo');
            $archivo = $request->file('archivo_respaldo');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();

            // Mover directamente a public/respaldos/vehiculo
            $archivo->move(public_path('respaldos/vehiculo'), $nombreArchivo);

            // Ruta accesible públicamente
            $ruta = asset('respaldos/vehiculo/' . $nombreArchivo);

            Movimiento::create([
                'id_user' => session('usuario')->id,
                'id_producto' => $vehiculo->id,
                'categoria' => 'vehiculo',
                'accion' => 'Eliminación',
                'comentario' => 'Vehículo eliminado',
                'documentacion' => $ruta,
            ]);

            return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar vehículo: ' . $e->getMessage());
            return redirect()->back()->withErrors('Error al eliminar el vehículo');
        }
    }
}
