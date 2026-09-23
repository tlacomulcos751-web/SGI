<?php

namespace App\Imports;

use App\Models\EspecificacionVehicular;
use App\Models\Vehiculo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Models\Movimiento;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class VehiculosImport implements
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

        // Si la fila NO tiene tipo, marca, modelo ni placas → ignorar fila completa
        $isTotallyEmpty =
            empty($clean['tipo']) &&
            empty($clean['marca']) &&
            empty($clean['modelo']) &&
            empty($clean['placas']) &&
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
            if (!in_array($key, ['tipo', 'marca', 'modelo', 'placas'])) {
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

                // Ubicación
                $ubicacion = null;
                if (!empty($row['ubicacion'])) {
                    $ubicacion = Ubicacion::where('nombre', $row['ubicacion'])->first();
                }

                // Responsable
                $responsable = null;

                if (!empty($row['responsable']) && $row['responsable'] !== 'Sin responsable') {
                    $nombreParts = explode(' ', trim($row['responsable']));

                    $responsable = Usuario::where(function ($q) use ($nombreParts, $row) {
                        if (count($nombreParts) >= 2) {
                            $q->where('nombre', 'LIKE', '%' . $nombreParts[0] . '%')
                                ->where('apellido', 'LIKE', '%' . $nombreParts[1] . '%');
                        } else {
                            $q->where('nombre', 'LIKE', '%' . $row['responsable'] . '%');
                        }
                    })->first();
                }

                $responsableId = $responsable ? $responsable->id : Session::get('usuario')->id;
                Log::info('Responsable ID asignado: ' . $responsableId);

                // Crear vehículo
                $vehiculo = Vehiculo::create([
                    'tipo' => $row['tipo'] ?? null,
                    'marca' => $row['marca'] ?? null,
                    'modelo' => $row['modelo'] ?? null,
                    'version' => $row['version'] ?? null,
                    'año' => $row['ano'] ?? null,
                    'placas' => $row['placas'] ?? null,
                    'color' => $row['color'] ?? null,
                    'carroceria' => $row['carroceria'] ?? null,
                    'combustible' => $row['combustible'] ?? null,
                    'transmision' => $row['transmision'] ?? null,
                    'niv' => $row['niv'] ?? null,
                    'ubicacion_id' => $ubicacion?->id,
                    'responsable' => $responsableId
                ]);

                // Crear especificaciones opcionales
                if ($this->tieneEspecificaciones($row)) {
                    EspecificacionVehicular::create([
                        'vehiculo_id' => $vehiculo->id,
                        'cilindraje' => $this->parseValor($row['cilindraje'] ?? null),
                        'potencia' => $this->parseValor($row['potencia'] ?? null),
                        'torque' => $this->parseValor($row['torque'] ?? null),
                        'ejes' => $this->parseValor($row['ejes'] ?? null),
                        'ruedas' => $this->parseValor($row['ruedas'] ?? null),
                        'capacidad_carga' => $this->parseValor($row['capacidad_de_carga'] ?? null),
                        'largo' => $this->parseValor($row['largo'] ?? null),
                        'ancho' => $this->parseValor($row['ancho'] ?? null),
                        'alto' => $this->parseValor($row['alto'] ?? null)
                    ]);
                }

                // Registrar movimiento
                Movimiento::create([
                    'id_user' => Session::get('usuario')->id,
                    'id_producto' => $vehiculo->id,
                    'categoria' => 'vehiculo',
                    'accion' => 'Registro',
                    'comentario' => 'Registro mediante importación masiva',
                    'documentacion' => null
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al importar vehículos: " . $e->getMessage());
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            '*.tipo' => 'required|string',
            '*.marca' => 'required|string',
            '*.modelo' => 'required|string',
            '*.ano' => 'required|integer',
            '*.placas' => 'required|string',
            '*.niv' => 'required|string'
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.tipo.required' => 'El tipo de vehículo es obligatorio',
            '*.marca.required' => 'La marca es obligatoria',
            '*.modelo.required' => 'El modelo es obligatorio',
            '*.ano.required' => 'El año es obligatorio',
            '*.placas.required' => 'Las placas son obligatorias',
            '*.niv.required' => 'El NIV es obligatorio'
        ];
    }

    private function tieneEspecificaciones($row)
    {
        $campos = [
            'cilindraje',
            'potencia',
            'torque',
            'ejes',
            'ruedas',
            'capacidad_de_carga',
            'largo',
            'ancho',
            'alto'
        ];

        foreach ($campos as $campo) {
            if (!empty($row[$campo]) && $row[$campo] !== 'N/A') {
                return true;
            }
        }

        return false;
    }

    private function parseValor($valor)
    {
        return (empty($valor) || $valor === 'N/A') ? null : $valor;
    }
}
