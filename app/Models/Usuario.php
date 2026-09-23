<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $fillable = [
        'nombre',
        'apellido',
        'apellido_m',
        'nombre_user',
        'gmail',
        'password',
        'uuid',
        'clave_recuperacion',
        'estado',
        'eliminado',
        'id_rol'
    ];

    protected $hidden = ['password', 'clave_recuperacion'];
    public $timestamps = false;


    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_user');
    }

    public function nombreCompleto()
    {
        return $this->nombre . ' ' . $this->apellido . ' ' . $this->apellido_m;
    }

    public function productosFijos()
    {
        return $this->hasMany(ProductoFijo::class, 'responsable');
    }

    public function numeroACargo()
    {
        return $this->productosFijos()->where('estado', '!=', 'baja')->count();
    }

    public function vehiculosACargo()
    {
        return $this->hasMany(Vehiculo::class, 'responsable');
    }
    
        public static function totalActivosNoEliminados()
    {
        return self::where('estado', 'activo')
            ->where('eliminado', 1)
            ->count();
    }
}
