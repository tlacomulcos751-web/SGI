<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductoFijo extends Model
{
    protected $table = 'productos_fijos'; // Corrige el nombre de la tabla (minúsculas y con guion bajo)
    public $timestamps = false;

    protected $fillable = [
        'producto_id',
        'clave',
        'estado',
        'calidad',
        'responsable',
        'fotografias',
        'fechaEntrada',
        'fechaSalida',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuarioResponsable()
    {
        return $this->belongsTo(Usuario::class, 'responsable');
    }

    // Método de consulta para inventario paginado (ajustado con alias correctos)
    public static function obtenerInventarioPaginado($cantidadPorPagina = 10)
    {
        return DB::table('productos')
            ->join('productos_fijos', 'productos_fijos.producto_id', '=', 'productos.id')
            ->leftJoin('ubicacion', 'productos.ubicacion_id', '=', 'ubicacion.id')
            ->select(
                'productos.id as id_fijo',
                'productos_fijos.id as id',
                'productos.nombre as nombre',
                'productos.precio as precio',
                'productos_fijos.clave as clave',
                DB::raw('COUNT(productos_fijos.producto_id) as existencia'),
                'ubicacion.nombre as ubicacion',
                'productos_fijos.estado as estado',
                'productos_fijos.fechaEntrada as fechaEntrada',
                DB::raw("'fijo' as tipo_producto")
            )
            ->groupBy(
                'productos.id',
                'productos_fijos.id',
                'productos.nombre',
                'productos.precio',
                'productos_fijos.clave',
                'productos_fijos.estado',
                'productos_fijos.fechaEntrada',
                'ubicacion.nombre'
            )
            ->paginate($cantidadPorPagina);
    }

    public function getImagenes()
    {
        return explode(',', $this->fotografias);
    }
}
