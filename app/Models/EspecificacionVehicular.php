<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EspecificacionVehicular extends Model
{
    protected $table = 'especificaciones_vehiculares';
    public $timestamps = false;

    protected $fillable = [
        'vehiculo_id',
        'cilindraje',
        'potencia',
        'torque',
        'ejes',
        'ruedas',
        'capacidad_carga',
        'largo',
        'ancho',
        'alto',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
