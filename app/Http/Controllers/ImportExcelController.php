<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\CompraVentaImport;
use App\Imports\ConsumiblesImport;
use App\Imports\FijosImport;
use App\Imports\VehiculosImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ImportExcelController extends Controller
{
    /**
     * Importar productos de compra-venta desde Excel
     */
    public function importarCompraVenta(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:xlsx,xls,csv|max:10240'
            ]);

            $archivo = $request->file('archivo');

            Excel::import(new CompraVentaImport, $archivo);

            return redirect()->back()->with('success', 'Productos de compra-venta importados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error al importar compra-venta: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al importar el archivo de compra-venta. Verifica el formato e intenta nuevamente.');
        }
    }

    /**
     * Importar productos fijos desde Excel
     */
    public function importarFijos(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:xlsx,xls,csv|max:10240'
            ]);

            $archivo = $request->file('archivo');

            Excel::import(new FijosImport, $archivo);

            return redirect()->back()->with('success', 'Productos fijos importados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error al importar productos fijos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al importar el archivo de activos fijos. Verifica el formato e intenta nuevamente.');
        }
    }

    /**
     * Importar productos consumibles desde Excel
     */
    public function importarConsumibles(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:xlsx,xls,csv|max:10240'
            ]);

            $archivo = $request->file('archivo');

            Excel::import(new ConsumiblesImport, $archivo);

            return redirect()->back()->with('success', 'Productos consumibles importados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error al importar consumibles: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al importar el archivo de consumibles. Verifica el formato e intenta nuevamente.');
        }
    }

    /**
     * Importar vehículos desde Excel
     */
    public function importarVehiculos(Request $request)
    {
        try {
            $request->validate([
                'archivo' => 'required|mimes:xlsx,xls,csv|max:10240'
            ]);

            $archivo = $request->file('archivo');

            Excel::import(new VehiculosImport, $archivo);

            return redirect()->back()->with('success', 'Vehículos importados correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Error al importar vehículos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al importar el archivo de vehículos. Verifica el formato e intenta nuevamente.');
        }
    }
}
