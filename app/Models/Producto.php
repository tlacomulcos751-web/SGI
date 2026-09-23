<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $fillable = ['nombre', 'descripcion', 'codigoBarra', 'ubicacion_id', 'empresa_id', 'precio'];
    public $timestamps = false;

    public function getNombre(){
        return $this->nombre;
    }
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_producto');
    }

    public function fijo()
    {
        return $this->hasOne(ProductoFijo::class, 'producto_id');
    }

    public function consumible()
    {
        return $this->hasOne(ProductoConsumible::class, 'producto_id');
    }

    public function compraVenta()
    {
        return $this->hasOne(ProductoCompraVenta::class, 'producto_id');
    }

    public function etiquetas()
    {
        return $this->belongsToMany(Etiqueta::class, 'etiquetas_productos', 'producto_id', 'clasificacion_id');
    }

    public static function etiquetasPorProducto($productoId)
    {
        return Etiqueta::whereHas('productos', function ($query) use ($productoId) {
            $query->where('producto_id', $productoId);
        })->where('eliminado', 1)->get();
    }
}
