<?php

namespace App\Imports;

use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FijosImport implements
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
        // 🚨 Esto obliga a SOLO LEER LA PRIMERA HOJA
        return [
            0 => $this,
        ];
    }

    /**
     * 🚨 Limpia filas ANTES de validar
     */
    public function prepareForValidation(array $row, $index)
    {
        Log::info("Preparando fila $index para validación:", $row);

        // Normalizar valores: eliminar espacios
        $clean = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $row);

        // Si la fila NO tiene nombre_del_producto, código ni precio → ignorar fila completa
        // (Corrección total del problema)
        $isTotallyEmpty =
            empty($clean['nombre_del_producto']) &&
            empty($clean['codigo_de_barra']) &&
            empty($clean['precio']) &&
            $this->allOtherValuesNull($clean);

        if ($isTotallyEmpty) {
            Log::info("Fila $index ignorada completamente (vacía o inválida).");
            return null; // ⬅ Esto la saca del proceso
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

    /**
     * PROCESAR LAS FILAS VALIDAS
     */
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

                // Ubicación
                $ubicacion = Ubicacion::firstOrCreate([
                    'nombre' => $row['ubicacion'] ?? 'Sin ubicación'
                ]);

                // Responsable (búsqueda por nombre)
                $responsableId = Session::get('usuario')->id;
                if (!empty($row['responsable']) && $row['responsable'] !== 'Sin responsable') {
                    $parts = explode(' ', trim($row['responsable']));
                    $responsable = Usuario::where(function ($q) use ($parts) {
                        $q->where('nombre', 'LIKE', '%' . $parts[0] . '%');
                        if (isset($parts[1])) {
                            $q->where('apellido', 'LIKE', '%' . $parts[1] . '%');
                        }
                    })->first();

                    $responsableId = $responsable?->id;
                }


                // Empresa
                $empresaId = null;
                if (!empty($row['empresa'])) {
                    $empresa = \App\Models\Empresa::where('nombre', 'LIKE', '%' . trim($row['empresa']) . '%')->first();
                    $empresaId = $empresa?->id;
                }

                // Insertar por stored procedure (15 parámetros)
                DB::statement("
                    CALL insertar_producto_fijo(
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ", [
                    $row['nombre_del_producto'],
                    $row['descripcion'] ?? '',
                    $row['codigo_de_barra'],
                    $ubicacion->id,
                    $this->parsePrecio($row['precio'] ?? null),
                    $row['cantidad'] ?? 1,
                    strtolower($row['estado'] ?? 'activo'),
                    strtolower($row['calidad'] ?? 'bueno'),
                    $responsableId,
                    $row['fotografias'] ?? null,
                    $this->parseFecha($row['fecha_de_entrada']),
                    null,
                    $row['comentario'] ?? null,
                    session('usuario')->id,
                    $empresaId
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Error al importar productos fijos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            '*.nombre_del_producto' => 'required|string',
            '*.codigo_de_barra'     => 'required',
            '*.precio'              => 'nullable',
            '*.cantidad'            => 'required|integer|min:1',
            '*.fecha_de_entrada'    => 'required',
            '*.estado'              => 'nullable|in:activo,baja,reparacion',
            '*.calidad'             => 'nullable|in:bueno,regular,malo',
        ];
    }

    private function parsePrecio($precio)
    {
        if (is_null($precio) || trim((string)$precio) === '' || strtoupper(trim((string)$precio)) === 'N/A' || strtoupper(trim((string)$precio)) === 'SIN COSTO') {
            return null;
        }
        return (float) str_replace([',', '$', ' '], '', $precio);
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
