<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoCategoria extends Model
{
    protected $table = 'permisos_categoria';
    protected $fillable = ['rol_id', 'categoria'];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
