<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoVehiculo extends Model
{
    protected $table = 'mantenimiento_vehiculo';
    public $timestamps = false;

    protected $fillable = [
        'vehiculo_id',
        'fecha',
        'kilometraje',
        'tipo_servicio',
        'descripcion',
        'taller',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
