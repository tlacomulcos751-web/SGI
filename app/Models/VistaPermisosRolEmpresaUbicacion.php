<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaPermisosRolEmpresaUbicacion extends Model
{
    protected $table = 'vista_permisos_rol_empresa_ubicacion';

    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'puede_leer_empresa' => 'boolean',
        'puede_leer_ubicacion' => 'boolean',
        'puede_insertar_ubicacion' => 'boolean',
        'puede_modificar_ubicacion' => 'boolean',
        'puede_desactivar_ubicacion' => 'boolean',
    ];

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }
}
