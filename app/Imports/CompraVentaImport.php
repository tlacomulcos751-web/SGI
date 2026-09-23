<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Ubicacion;
use Carbon\Carbon;
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

class CompraVentaImport implements
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

        // Si la fila NO tiene nombre, código, precio ni empresa → ignorar fila completa
        $isTotallyEmpty =
            empty($clean['nombre_del_producto']) &&
            empty($clean['codigo_de_barra']) &&
            empty($clean['precio']) &&
            empty($clean['empresa_a_vender']) &&
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
            if (!in_array($key, ['nombre_del_producto', 'codigo_de_barra', 'precio', 'empresa_a_vender'])) {
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

                // Ubicación (solo buscar)
                $ubicacion = null;
                if (!empty($row['ubicacion'])) {
                    $ubicacion = Ubicacion::where('nombre', $row['ubicacion'])->first();
                }
                $ubicacionId = $ubicacion?->id ?? null;

                // Empresa (solo buscar)
                $empresa = Empresa::where('nombre', $row['empresa_a_vender'])->first();
                if (!$empresa) {
                    throw new \Exception("La empresa '{$row['empresa_a_vender']}' no existe");
                }

                // Llamar procedimiento almacenado insertar_producto_compraventa
                DB::statement("
                    CALL insertar_producto_compraventa(
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ", [
                    $row['nombre_del_producto'],
                    $row['descripcion'] ?? '',
                    $row['codigo_de_barra'],
                    $ubicacionId,
                    $this->parsePrecio($row['precio']),
                    $empresa->id,
                    $row['existencia'] ?? 0,
                    $this->parseFecha($row['fecha_de_entrada']),
                    null,
                    'Registro mediante importación masiva',
                    Session::get('usuario')->id
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al importar productos de compra-venta: " . $e->getMessage());
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            '*.nombre_del_producto' => 'required|string',
            '*.codigo_de_barra' => 'required',
            '*.precio' => 'required',
            '*.empresa_a_vender' => 'required|string',
            '*.existencia' => 'required|integer|min:0',
            '*.fecha_de_entrada' => 'required',
            '*.ubicacion' => 'required|string|exists:ubicacion,nombre'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nombre_del_producto.required' => 'El nombre del producto es obligatorio',
            '*.codigo_de_barra.required' => 'El código de barra es obligatorio',
            '*.empresa_a_vender.required' => 'La empresa es obligatoria'
        ];
    }

    private function parsePrecio($precio)
    {
        return (float) str_replace([',', '$'], '', $precio);
    }

    private function parseFecha($fecha)
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            return Carbon::now()->format('Y-m-d');
        }
    }
}
