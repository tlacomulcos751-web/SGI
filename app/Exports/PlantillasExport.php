<?php

namespace App\Exports;

use App\Models\Empresa;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Log;

class PlantillasExport implements WithMultipleSheets
{
    protected string $type;
    protected array $archivoMap = [
        'fijos' => 'activosfijos.xlsx',
        'vehiculos' => 'vehiculos.xlsx',
        'consumibles' => 'consumibles.xlsx',
        'compraventa' => 'compra_venta.xlsx',
    ];

    public function __construct(string $type = 'fijos')
    {
        $this->type = strtolower($type);
    }

    public function sheets(): array
    {
        return [new ReferenceSheet()];
    }

    /**
     * Método mejorado con validación y manejo de errores
     */
    public function download(string $nombreArchivo)
    {
        $archivoBase = $this->archivoMap[$this->type] ?? 'activosfijos.xlsx';
        $rutaPlantilla = public_path('plantillas/' . $archivoBase);

        if (!file_exists($rutaPlantilla)) {
            Log::error("Plantilla no encontrada: {$archivoBase}");
            abort(404, "Plantilla no encontrada: {$archivoBase}");
        }

        try {
            // Intentar cargar el archivo existente
            $spreadsheet = $this->cargarPlantillaSegura($rutaPlantilla);
            
        } catch (\Exception $e) {
            Log::error("Error al cargar plantilla {$archivoBase}: " . $e->getMessage());
            
            // Si falla, crear spreadsheet nuevo desde cero
            Log::info("Creando spreadsheet desde cero debido al error");
            $spreadsheet = $this->crearPlantillaDesdeReferencias();
        }

        // Eliminar hoja "Referencias" si existe
        if ($spreadsheet->getSheetByName('Referencias')) {
            $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName('Referencias'));
            $spreadsheet->removeSheetByIndex($sheetIndex);
        }

        // Crear nueva hoja de Referencias con datos actualizados
        $this->agregarHojaReferencias($spreadsheet);

        // Retornar como descarga
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Intenta cargar la plantilla de forma segura, evitando errores de XML
     */
    protected function cargarPlantillaSegura(string $rutaPlantilla): Spreadsheet
    {
        // Configurar opciones de carga para ignorar imágenes/dibujos corruptos
        $reader = IOFactory::createReaderForFile($rutaPlantilla);
        
        // Si es un lector Xlsx, configurar para no cargar imágenes
        if (method_exists($reader, 'setIncludeCharts')) {
            $reader->setIncludeCharts(false);
        }
        
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true); // Solo leer datos, ignorar formato/imágenes
        }

        return $reader->load($rutaPlantilla);
    }

    /**
     * Crea un spreadsheet básico desde cero con solo la hoja de referencias
     */
    protected function crearPlantillaDesdeReferencias(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Datos');
        
        // Encabezados básicos según el tipo
        $encabezados = $this->obtenerEncabezadosBasicos();
        $col = 'A';
        foreach ($encabezados as $encabezado) {
            $sheet->setCellValue($col . '1', $encabezado);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        
        return $spreadsheet;
    }

    /**
     * Obtiene encabezados básicos según el tipo de plantilla
     */
    protected function obtenerEncabezadosBasicos(): array
    {
        $encabezados = [
            'fijos' => ['Código', 'Descripción', 'Marca', 'Modelo', 'Ubicación', 'Responsable', 'Estado'],
            'vehiculos' => ['Placa', 'Marca', 'Modelo', 'Año', 'Ubicación', 'Responsable', 'Estado'],
            'consumibles' => ['Código', 'Descripción', 'Cantidad', 'Ubicación', 'Calidad'],
            'compraventa' => ['Folio', 'Descripción', 'Empresa Externa', 'Fecha', 'Monto'],
        ];

        return $encabezados[$this->type] ?? $encabezados['fijos'];
    }

    /**
     * Agrega la hoja de Referencias con datos actualizados
     */
    protected function agregarHojaReferencias(Spreadsheet $spreadsheet): void
    {
        $hojaReferencias = $spreadsheet->createSheet();
        $hojaReferencias->setTitle('Referencias');

        // Obtener datos de la BD
        $ubicaciones = Ubicacion::orderBy('nombre')->pluck('nombre')->toArray();
$responsables = Usuario::orderBy('nombre')->get()->map(function ($u) {
    // Construir nombre completo
    $nombreCompleto = trim(
        ($u->nombre ?? '') . ' ' .
        ($u->apellido ?? '') . ' ' .
        ($u->apellido_m ?? '')
    );

    // Si no hay nombre, usar username o "Usuario {id}"
    if ($nombreCompleto === '') {
        $nombreCompleto = $u->username ?? ('Usuario ' . $u->id);
    }

    return $nombreCompleto;
})->toArray();
        $empresas = Empresa::orderBy('nombre')->pluck('nombre')->toArray();
        $calidadEnum = ['bueno', 'regular', 'malo'];

        $maxFilas = max(count($ubicaciones), count($responsables), count($empresas), count($calidadEnum));

        // Escribir encabezados
        $hojaReferencias->setCellValue('A1', 'UBICACIONES');
        $hojaReferencias->setCellValue('B1', 'RESPONSABLES');
        $hojaReferencias->setCellValue('C1', 'EMPRESAS EXTERNAS');
        $hojaReferencias->setCellValue('D1', 'CALIDAD');
        $hojaReferencias->getStyle('A1:D1')->getFont()->setBold(true);

        // Escribir datos
        for ($i = 0; $i < $maxFilas; $i++) {
            $fila = $i + 2;
            
            if (isset($ubicaciones[$i])) {
                $hojaReferencias->setCellValue('A' . $fila, $ubicaciones[$i]);
            }
            if (isset($responsables[$i])) {
                $hojaReferencias->setCellValue('B' . $fila, $responsables[$i]);
            }
            if (isset($empresas[$i])) {
                $hojaReferencias->setCellValue('C' . $fila, $empresas[$i]);
            }
            if (isset($calidadEnum[$i])) {
                $hojaReferencias->setCellValue('D' . $fila, $calidadEnum[$i]);
            }
        }

        // Formato
        foreach (['A', 'B', 'C', 'D'] as $col) {
            $hojaReferencias->getColumnDimension($col)->setAutoSize(true);
        }
        $hojaReferencias->freezePane('A2');
    }
}

class ReferenceSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->pluck('nombre')->toArray();
        $responsables = Usuario::orderBy('nombre')->get()->map(function ($u) {
            return trim($u->nombre . ' ' . ($u->apellido ?? '')) ?: $u->username ?? ('Usuario ' . $u->id);
        })->toArray();
        $empresas = Empresa::orderBy('nombre')->pluck('nombre')->toArray();
        $calidadEnum = ['bueno', 'regular', 'malo'];

        $max = max(count($ubicaciones), count($responsables), count($empresas), count($calidadEnum));

        $rows = [
            ['UBICACIONES', 'RESPONSABLES', 'EMPRESAS EXTERNAS', 'CALIDAD']
        ];

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $ubicaciones[$i] ?? '',
                $responsables[$i] ?? '',
                $empresas[$i] ?? '',
                $calidadEnum[$i] ?? '',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Referencias';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('1:1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
            }
        ];
    }
}