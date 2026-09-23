<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoOtro extends Model
{
    protected $table = 'permisos_otros';
    protected $fillable = ['rol_id', 'ubicacion_id', 'status'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }
}
