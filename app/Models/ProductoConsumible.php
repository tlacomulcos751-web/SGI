<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoConsumible extends Model
{
    protected $table = 'productos_consumibles';
    public $timestamps = false;

    protected $fillable = ['producto_id', 'existencia'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(NotificacionConsumible::class, 'id_productos_consumibles');
    }
}
