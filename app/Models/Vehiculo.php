<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';
    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'marca',
        'modelo',
        'version',
        'año',
        'niv',
        'placas',
        'color',
        'carroceria',
        'combustible',
        'transmision',
        'ubicacion_id',
        'responsable',
        'eliminado',
    ];

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function usuarioResponsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable');
    }

    public function especificaciones()
    {
        return $this->hasOne(EspecificacionVehicular::class, 'vehiculo_id');
    }

    public function documentaciones()
    {
        return $this->hasMany(DocumentacionVehiculo::class, 'vehiculo_id');
    }

    public function mantenimientos()
    {
        return $this->hasMany(MantenimientoVehiculo::class, 'vehiculo_id');
    }

}
