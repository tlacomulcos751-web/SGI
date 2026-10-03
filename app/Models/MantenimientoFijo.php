<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MantenimientoFijo extends Model
{
    protected $table = 'mantenimientos_fijos';

    protected $fillable = [
        'producto_fijo_id',
        'fecha',
        'tipo_servicio',
        'frecuencia_meses',
        'proxima_fecha',
        'tecnico',
        'costo',
        'descripcion',
        'comentarios',
    ];

    protected $casts = [
        'fecha' => 'date',
        'proxima_fecha' => 'date',
        'costo' => 'decimal:2',
        'frecuencia_meses' => 'integer',
    ];

    public function productoFijo()
    {
        return $this->belongsTo(ProductoFijo::class, 'producto_fijo_id');
    }

    /**
     * Calcula el estado del semáforo para el próximo mantenimiento
     * Retorna: 'vencido', 'urgente', 'proximo', 'al_dia', 'desconocido'
     */
    public function getSemaforoAttribute()
    {
        if (!$this->proxima_fecha) {
            return 'al_dia';
        }

        $hoy = Carbon::today();
        $proxima = Carbon::parse($this->proxima_fecha);

        if ($proxima->isPast()) {
            return 'vencido'; // Rojo
        }

        $diasRestantes = $hoy->diffInDays($proxima, false);

        if ($diasRestantes <= 15) {
            return 'urgente'; // Naranja/Amarillo fuerte
        } elseif ($diasRestantes <= 30) {
            return 'proximo'; // Amarillo
        }

        return 'al_dia'; // Verde
    }

    public function getDiasRestantesAttribute()
    {
        if (!$this->proxima_fecha) {
            return null;
        }

        $hoy = Carbon::today();
        $proxima = Carbon::parse($this->proxima_fecha);

        return (int) $hoy->diffInDays($proxima, false);
    }
}
