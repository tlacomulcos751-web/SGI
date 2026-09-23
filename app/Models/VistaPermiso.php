<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaPermiso extends Model
{
    // Nombre de la vista en la base de datos
    protected $table = 'vista_permisos';

    // La vista no tiene clave primaria real
    protected $primaryKey = null;
    public $incrementing = false;

    // No tiene timestamps (created_at, updated_at)
    public $timestamps = false;

    // Campos disponibles para asignación masiva
    protected $fillable = [
        'id',
        'rol',
        'permiso_nombre',
        'permiso_activo',
        'permiso_otorgado',
        'tipo',
    ];
}
