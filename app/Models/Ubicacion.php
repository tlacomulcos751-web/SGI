<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table = 'ubicacion';
    protected $fillable = ['clave', 'nombre', 'estado', 'empresa_id'];
    public $timestamps = false;

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function permisosOtros()
    {
        return $this->hasMany(PermisoOtro::class);
    }

    public function permisosUbicacion()
    {
        return $this->hasMany(PermisoUbicacion::class);
    }

    public function cantidadProductos()
    {
        return $this->productos()->count();
    }

    public function nombreProductos()
    {
        return $this->productos()->pluck('nombre')->toArray();
    }
}
