<?php

namespace App\Imports;

use App\Models\Ubicacion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ConsumiblesImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsEmptyRows,
    SkipsOnError,
    WithMultipleSheets
{
    use SkipsErrors;

    public function sheets(): array
    {
        // Solo leer la primera hoja
        return [
            0 => $this,
        ];
    }

    /**
     * Limpia filas ANTES de validar
     */
    public function prepareForValidation(array $row, $index)
    {
        Log::info("Preparando fila $index para validación:", $row);

        // Normalizar valores: eliminar espacios
        $clean = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        // Si la fila NO tiene nombre, código ni precio → ignorar fila completa
        $isTotallyEmpty =
            empty($clean['nombre_del_producto']) &&
            empty($clean['codigo_de_barra']) &&
            empty($clean['precio']) &&
            $this->allOtherValuesNull($clean);

        if ($isTotallyEmpty) {
            Log::info("Fila $index ignorada completamente (vacía o inválida).");
            return null;
        }

        return $clean;
    }

    /**
     * Auxiliar: verifica si las demás columnas están vacías/null
     */
    private function allOtherValuesNull($row)
    {
        foreach ($row as $key => $value) {
            if (!in_array($key, ['nombre_del_producto', 'codigo_de_barra', 'precio'])) {
                if (!is_null($value) && trim((string)$value) !== '') {
                    return false;
                }
            }
        }
        return true;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            Log::info("Filas recibidas para procesar: " . $rows->count());

            foreach ($rows as $index => $row) {
                Log::info("Procesando fila $index:", $row->toArray());

                // Seguridad adicional
                if ($row->filter()->isEmpty()) {
                    Log::info("Fila $index ignorada en collection() (vacía).");
                    continue;
                }

                // Buscar ubicación existente
                $ubicacion = null;
                if (!empty($row['ubicacion'])) {
                    $ubicacion = Ubicacion::where('nombre', $row['ubicacion'])->first();
                }
                $ubicacionId = $ubicacion?->id ?? null;

                // Empresa
                $empresaId = null;
                if (!empty($row['empresa'])) {
                    $empresa = \App\Models\Empresa::where('nombre', 'LIKE', '%' . trim($row['empresa']) . '%')->first();
                    $empresaId = $empresa?->id;
                }

                // Llamar procedimiento almacenado (10 parámetros)
                DB::statement("
                    CALL insertar_producto_consumible(
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ", [
                    $row['nombre_del_producto'],
                    $row['descripcion'] ?? '',
                    $row['codigo_de_barra'],
                    $ubicacionId,
                    $this->parsePrecio($row['precio'] ?? null),
                    $row['existencia'] ?? 0,
                    null,
                    'Registro mediante importación masiva',
                    Session::get('usuario')->id,
                    $empresaId
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al importar consumibles: " . $e->getMessage());
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            '*.nombre_del_producto' => 'required|string',
            '*.codigo_de_barra' => 'required',
            '*.precio' => 'nullable',
            '*.existencia' => 'required|integer|min:0',
            '*.ubicacion' => 'required|string|exists:ubicacion,nombre'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nombre_del_producto.required' => 'El nombre del producto es obligatorio',
            '*.codigo_de_barra.required' => 'El código de barra es obligatorio',
            '*.existencia.required' => 'La existencia es obligatoria',
            '*.ubicacion.required' => 'La ubicación es obligatoria',
            '*.ubicacion.exists' => 'La ubicación no existe en el catálogo'
        ];
    }

    private function parsePrecio($precio)
    {
        if (is_null($precio) || trim((string)$precio) === '' || strtoupper(trim((string)$precio)) === 'N/A' || strtoupper(trim((string)$precio)) === 'SIN COSTO') {
            return null;
        }
        return (float) str_replace([',', '$', ' '], '', $precio);
    }
}
