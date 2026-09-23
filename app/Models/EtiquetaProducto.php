<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiquetaProducto extends Model
{
    protected $table = 'etiquetas_productos';
    public $timestamps = false;

    protected $fillable = ['producto_id', 'clasificacion_id'];

    /**
     * Producto asociado
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Etiqueta asociada
     */
    public function etiqueta()
    {
        return $this->belongsTo(Etiqueta::class, 'clasificacion_id');
    }
}
