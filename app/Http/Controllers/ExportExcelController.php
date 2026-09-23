<?php

namespace App\Http\Controllers;

use App\Exports\InfoExport;
use App\Http\Controllers\Controller;
use App\Models\ProductoCompraVenta;
use App\Models\ProductoConsumible;
use App\Models\ProductoFijo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;


class ExportExcelController  extends Controller
{

    public function imprimirExcel(Request $request)
    {
        $informacion = $request->input('data'); // Aquí asumes que envías la información por Request

        return Excel::download(new InfoExport($informacion), 'informacion.xlsx');
    }

    public function imprimirCompraVenta(Request $request)
    {
        try {
            $productoJson = $request->input('producto');
            $productos = json_decode($productoJson, true);
            $ids = collect($productos)->pluck('id')->toArray();

            $productosCompraVenta = ProductoCompraVenta::with(['empresa', 'producto.ubicacion'])
                ->whereIn('id', $ids)
                ->get();

            $informacion = [];

            foreach ($productosCompraVenta as $productoCompraVenta) {
                $informacion[] = [
                    'Nombre del Producto' => $productoCompraVenta->producto->nombre,
                    'Código de Barra' => $productoCompraVenta->producto->codigoBarra,
                    'Descripción' => $productoCompraVenta->producto->descripcion,
                    'Ubicación' => $productoCompraVenta->producto->ubicacion->nombre ?? 'Sin ubicación',
                    'Precio' => number_format($productoCompraVenta->producto->precio, 2),
                    'Fecha de Registro' => \Carbon\Carbon::parse($productoCompraVenta->producto->fecha_registro)->format('d/m/Y'),
                    'Empresa a Vender' => $productoCompraVenta->empresa->nombre,
                    'Existencia' => $productoCompraVenta->existencia,
                    'Estado' => $productoCompraVenta->estado,
                    'Fecha de Entrada' => \Carbon\Carbon::parse($productoCompraVenta->fechaEntrada)->format('d/m/Y'),
                    'Fecha de Salida' => $productoCompraVenta->fechaSalida
                        ? \Carbon\Carbon::parse($productoCompraVenta->fechaSalida)->format('d/m/Y')
                        : 'Activo'
                ];
            }

            return Excel::download(new InfoExport($informacion), 'informacion.xlsx');
        } catch (Exception $e) {
            Log::error('Error al generar compra-venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el archivo de compra-venta.');
        }
    }

    public function imprimirFijos(Request $request)
    {
        try {
            $ids = [];
            if ($request->filled('producto')) {
                $productoJson = $request->input('producto');
                $productos = json_decode($productoJson, true);
                if (is_array($productos)) {
                    $ids = collect($productos)->pluck('id')->filter()->toArray();
                }
            }

            $query = ProductoFijo::with(['producto.ubicacion', 'usuarioResponsable']);

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                if ($request->filled('responsable_id')) {
                    $query->where('responsable', $request->responsable_id);
                }
                if ($request->filled('estado')) {
                    $query->where('estado', $request->estado);
                } else {
                    $query->where('estado', '!=', 'baja');
                }
                if ($request->filled('clave')) {
                    $query->where('clave', 'like', '%' . $request->clave . '%');
                }
            }

            $productoFijos = $query->get();

            $informacion = [];

            foreach ($productoFijos as $productoFijo) {
                $informacion[] = [
                    'Nombre del Producto' => $productoFijo->producto->nombre ?? '',
                    'Código de Barra' => $productoFijo->producto->codigoBarra ?? '',
                    'Descripción' => $productoFijo->producto->descripcion ?? '',
                    'Ubicación' => $productoFijo->producto->ubicacion->nombre ?? 'Sin ubicación',
                    'Precio' => number_format($productoFijo->producto->precio ?? 0, 2),
                    'Fecha de Registro' => optional($productoFijo->producto)->fecha_registro ? \Carbon\Carbon::parse($productoFijo->producto->fecha_registro)->format('d/m/Y') : 'N/A',
                    'Clave' => $productoFijo->clave,
                    'Estado' => $productoFijo->estado,
                    'Calidad' => $productoFijo->calidad,
                    'Responsable' => optional($productoFijo->usuarioResponsable)->nombreCompleto() ?? 'Sin responsable',
                    'Fecha de Entrada' => $productoFijo->fechaEntrada ? \Carbon\Carbon::parse($productoFijo->fechaEntrada)->format('d/m/Y') : 'N/A',
                    'Fecha de Salida' => $productoFijo->fechaSalida
                        ? \Carbon\Carbon::parse($productoFijo->fechaSalida)->format('d/m/Y')
                        : 'Activo'
                ];
            }

            return Excel::download(new InfoExport($informacion), 'informacion.xlsx');
        } catch (Exception $e) {
            Log::error('Error al generar productos fijos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el archivo de productos fijos.');
        }
    }

    public function imprimirFijosResponsable(Request $request)
    {
        try {
            $request->validate([
                'responsable_id' => 'required|exists:usuario,id'
            ]);

            $responsableId = $request->input('responsable_id');

            $productoFijos = ProductoFijo::with(['producto.ubicacion', 'usuarioResponsable'])
                ->where('responsable', $responsableId)
                ->get();

            $informacion = [];

            foreach ($productoFijos as $productoFijo) {
                $informacion[] = [
                    'Nombre del Producto' => $productoFijo->producto->nombre,
                    'Clave' => $productoFijo->clave,
                    'Ubicación' => $productoFijo->producto->ubicacion->nombre ?? 'Sin ubicación',
                    'Estado' => $productoFijo->estado,
                    'Calidad' => $productoFijo->calidad,
                    'Responsable' => optional($productoFijo->usuarioResponsable)->nombreCompleto() ?? 'Sin responsable',
                    'Fecha de Entrada' => \Carbon\Carbon::parse($productoFijo->fechaEntrada)->format('d/m/Y'),
                ];
            }

            $responsable = Usuario::find($responsableId);
            $nombreArchivo = 'productos_responsable_' . Str::slug($responsable->nombre . '_' . $responsable->apellido) . '.xlsx';

            return Excel::download(new InfoExport($informacion), $nombreArchivo);
        } catch (Exception $e) {
            Log::error('Error al generar reporte de productos fijos por responsable: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el reporte.');
        }
    }

    public function imprimirGeneralResponsable(Request $request)
    {
        try {
            $request->validate([
                'responsable_id' => 'required|exists:usuario,id'
            ]);

            $responsableId = $request->input('responsable_id');
            $responsable = \App\Models\Usuario::findOrFail($responsableId);

            $productoIds = \App\Models\ProductoFijo::where('responsable', $responsableId)
                ->pluck('producto_id');

            if ($productoIds->isEmpty()) {
                $productos = collect([]);
            } else {
                $productos = \Illuminate\Support\Facades\DB::table('vista_productos_detalle')
                    ->whereIn('id', $productoIds)
                    ->orderBy('nombre', 'asc')
                    ->get();
            }

            $informacion = [];

            foreach ($productos as $producto) {
                $informacion[] = [
                    'Nombre del Producto' => $producto->nombre,
                    'Ubicación' => $producto->ubicacion_nombre ?? 'Sin ubicación',
                    'Empresa' => $producto->empresa_nombre ?? 'Sin empresa',
                    'Precio' => $producto->precio,
                    'Existencia' => $producto->existencia,
                    'Tipo' => $producto->tipo,
                    'Estado' => $producto->estado,
                    'Responsable' => $responsable->nombreCompleto(),
                    'Fecha de Registro' => \Carbon\Carbon::parse($producto->fecha_registro)->format('d/m/Y'),
                ];
            }

            $nombreArchivo = 'productos_general_responsable_' . \Illuminate\Support\Str::slug($responsable->nombre . '_' . $responsable->apellido) . '.xlsx';

            return Excel::download(new InfoExport($informacion), $nombreArchivo);
        } catch (Exception $e) {
            Log::error('Error al generar reporte de productos generales por responsable: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el reporte.');
        }
    }

    public function imprimirConsumibles(Request $request)
    {
        try {
            $productoJson = $request->input('producto');
            $productos = json_decode($productoJson, true);
            $ids = collect($productos)->pluck('id')->toArray();

            $consumibles = ProductoConsumible::with(['producto.ubicacion'])
                ->whereIn('id', $ids)
                ->get();

            $informacion = [];

            foreach ($consumibles as $consumible) {
                $informacion[] = [
                    'Nombre del Producto' => $consumible->producto->nombre,
                    'Código de Barra' => $consumible->producto->codigoBarra,
                    'Descripción' => $consumible->producto->descripcion,
                    'Ubicación' => $consumible->producto->ubicacion->nombre ?? 'Sin ubicación',
                    'Precio' => number_format($consumible->producto->precio, 2),
                    'Fecha de Registro' => \Carbon\Carbon::parse($consumible->producto->fecha_registro)->format('d/m/Y'),
                    'Existencia' => $consumible->existencia
                ];
            }

            return Excel::download(new InfoExport($informacion), 'informacion.xlsx');
        } catch (Exception $e) {
            Log::error('Error al generar consumibles: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el archivo de productos consumibles.');
        }
    }

    public function imprimirVehiculos(Request $request)
    {
        try {
            $vehiculoJson = $request->input('producto');
            $vehiculos = json_decode($vehiculoJson, true);
            $ids = collect($vehiculos)->pluck('id')->toArray();

            $vehiculosDB = Vehiculo::with([
                'ubicacion',
                'usuarioResponsable',
                'especificaciones'
            ])
                ->whereIn('id', $ids)
                ->get();

            $informacion = [];

            foreach ($vehiculosDB as $vehiculo) {
                $especificaciones = $vehiculo->especificaciones;

                $informacion[] = [
                    'Tipo' => $vehiculo->tipo,
                    'Marca' => $vehiculo->marca,
                    'Modelo' => $vehiculo->modelo,
                    'Versión' => $vehiculo->version,
                    'Año' => $vehiculo->año,
                    'Placas' => $vehiculo->placas,
                    'Color' => $vehiculo->color,
                    'Carrocería' => $vehiculo->carroceria,
                    'Combustible' => $vehiculo->combustible,
                    'Transmisión' => $vehiculo->transmision,
                    'NIV' => $vehiculo->niv,
                    'Ubicación' => $vehiculo->ubicacion->nombre ?? 'Sin ubicación',
                    'Responsable' => optional($vehiculo->usuarioResponsable)->nombreCompleto() ?? 'Sin responsable',
                    'Cilindraje' => $especificaciones->cilindraje ?? 'N/A',
                    'Potencia' => $especificaciones->potencia ?? 'N/A',
                    'Torque' => $especificaciones->torque ?? 'N/A',
                    'Ejes' => $especificaciones->ejes ?? 'N/A',
                    'Ruedas' => $especificaciones->ruedas ?? 'N/A',
                    'Capacidad de Carga' => $especificaciones->capacidad_carga ?? 'N/A',
                    'Largo' => $especificaciones->largo ?? 'N/A',
                    'Ancho' => $especificaciones->ancho ?? 'N/A',
                    'Alto' => $especificaciones->alto ?? 'N/A',
                ];
            }

            return Excel::download(new InfoExport($informacion), 'vehiculos.xlsx');
        } catch (Exception $e) {
            Log::error('Error al generar vehículos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el archivo de vehículos.');
        }
    }
    public function imprimirVehiculosResponsable(Request $request)
    {
        try {
            $request->validate([
                'responsable_id' => 'required|exists:usuario,id'
            ]);

            $responsableId = $request->input('responsable_id');
            $responsable = \App\Models\Usuario::findOrFail($responsableId);

            $vehiculosDB = \App\Models\Vehiculo::with([
                'ubicacion',
                'usuarioResponsable',
                'especificaciones'
            ])
                ->where('responsable', $responsableId)
                ->get();

            $informacion = [];

            foreach ($vehiculosDB as $vehiculo) {
                $especificaciones = $vehiculo->especificaciones;

                $informacion[] = [
                    'Tipo' => $vehiculo->tipo,
                    'Marca' => $vehiculo->marca,
                    'Modelo' => $vehiculo->modelo,
                    'Versión' => $vehiculo->version,
                    'Año' => $vehiculo->año,
                    'Placas' => $vehiculo->placas,
                    'Color' => $vehiculo->color,
                    'Carrocería' => $vehiculo->carroceria,
                    'Combustible' => $vehiculo->combustible,
                    'Transmisión' => $vehiculo->transmision,
                    'NIV' => $vehiculo->niv,
                    'Ubicación' => $vehiculo->ubicacion->nombre ?? 'Sin ubicación',
                    'Responsable' => optional($vehiculo->usuarioResponsable)->nombreCompleto() ?? 'Sin responsable',
                    'Cilindraje' => $especificaciones->cilindraje ?? 'N/A',
                    'Potencia' => $especificaciones->potencia ?? 'N/A',
                    'Torque' => $especificaciones->torque ?? 'N/A',
                    'Ejes' => $especificaciones->ejes ?? 'N/A',
                    'Ruedas' => $especificaciones->ruedas ?? 'N/A',
                    'Capacidad de Carga' => $especificaciones->capacidad_carga ?? 'N/A',
                    'Largo' => $especificaciones->largo ?? 'N/A',
                    'Ancho' => $especificaciones->ancho ?? 'N/A',
                    'Alto' => $especificaciones->alto ?? 'N/A',
                ];
            }

            $nombreArchivo = 'vehiculos_responsable_' . \Illuminate\Support\Str::slug($responsable->nombre . '_' . $responsable->apellido) . '.xlsx';

            return Excel::download(new InfoExport($informacion), $nombreArchivo);
        } catch (Exception $e) {
            Log::error('Error al generar vehículos por responsable: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo generar el archivo de vehículos por responsable.');
        }
    }
}
