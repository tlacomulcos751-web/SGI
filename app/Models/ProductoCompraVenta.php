<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoCompraVenta extends Model
{
    protected $table = 'productos_compraventa';
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'empresaVenta_id',
        'existencia',
        'estado',
        'fechaEntrada',
        'fechaSalida'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresaVenta_id');
    }
}
