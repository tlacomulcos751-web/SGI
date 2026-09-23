<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoEmpresa extends Model
{
    protected $table = 'permisos_empresa';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'accion',
        'status',
    ];

    /**
     * Relación con la empresa a la que pertenece el permiso.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Scope para filtrar solo permisos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('status', 'activo');
    }
}
