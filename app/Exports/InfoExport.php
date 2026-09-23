<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InfoExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $info;

    public function __construct($info)
    {
        $this->info = $info;
    }

    public function collection()
    {
        return collect($this->info);
    }

    public function headings(): array
    {
        return !empty($this->info) ? array_keys($this->info[0]) : [];
    }

    /**
     * Aplica estilos al archivo Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Estilos para la primera fila (encabezados)
        $sheet->getStyle('1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '4F81BD'], // azul
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // Bordes para todo el rango de datos
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        return [
            // Puedes aplicar más estilos a filas o columnas específicas
            'A' => ['alignment' => ['horizontal' => 'left']],
        ];
    }
}