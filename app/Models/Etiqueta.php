<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    protected $table = 'etiquetas';
    protected $fillable = ['nombre', 'eliminado'];

    public $timestamps = false;

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'etiquetas_productos', 'clasificacion_id', 'producto_id');
    }

    public function totalExistenciaProductos(): int
    {
        return \App\Models\VistaProductosUnificada::whereIn('id', function ($query) {
            $query->select('producto_id')
                ->from('etiquetas_productos')
                ->where('clasificacion_id', $this->id);
        })->sum('existencia');
    }

    public function existenciaProducto(int $productoId): int
    {
        return \App\Models\VistaProductosUnificada::where('id', $productoId)->sum('existencia');
    }

    public static function obtenerEtiquetasActivas()
    {
        return self::where('eliminado', 1)->orderBy('nombre')->get();
    }
}
