<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PlantillasExport;

class PlantillasController extends Controller
{
    public function descargarFijos()
    {
        $export = new PlantillasExport('fijos');
        return $export->download('plantilla_fijos.xlsx');
    }

    public function descargarVehiculos()
    {
        $export = new PlantillasExport('vehiculos');
        return $export->download('plantilla_vehiculos.xlsx');
    }

    public function descargarConsumibles()
    {
        $export = new PlantillasExport('consumibles');
        return $export->download('plantilla_consumibles.xlsx');
    }

    public function descargarCompraVenta()
    {
        $export = new PlantillasExport('compraventa');
        return $export->download('plantilla_compraventa.xlsx');
    }
}
