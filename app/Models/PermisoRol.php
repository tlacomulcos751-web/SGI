<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoRol extends Model
{
    protected $table = 'permisos_rol';

    public $timestamps = false;

    protected $fillable = [
        'rol_id',
        'permiso_id',
        'permiso_tipo',
        'status',
    ];

    /**
     * Relación con el rol asociado a este permiso.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Scope para obtener solo permisos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('status', 'activo');
    }

    public function p()
    {


    }
}
