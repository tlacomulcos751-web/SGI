<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentacionVehiculo extends Model
{
    protected $table = 'documentacion_vehiculo';
    public $timestamps = false;

    protected $fillable = [
        'vehiculo_id',
        'tipo',
        'numero_documento',
        'vigencia_inicio',
        'vigencia_fin',
        'estatus',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}
