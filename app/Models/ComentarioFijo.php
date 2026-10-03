<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComentarioFijo extends Model
{
    protected $table = 'comentarios_fijo';

    protected $fillable = [
        'producto_fijo_id',
        'usuario_id',
        'comentario',
    ];

    public function productoFijo()
    {
        return $this->belongsTo(ProductoFijo::class, 'producto_fijo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

