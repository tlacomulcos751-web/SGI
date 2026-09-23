<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VistaProductosUnificada extends Model
{
    protected $table = 'vista_productos_detalle'; // Nombre de la vista

    public $timestamps = false; // Las vistas no manejan timestamps

    protected $primaryKey = 'id';
    public $incrementing = false; // Las vistas no tienen auto-increment

    protected $fillable = [
        'id',
        'nombre',
        'ubicacion_nombre',
        'empresa_nombre',
        'precio',
        'fecha_registro',
        'tipo',
        'estado',
        'existencia',
    ];

    protected $casts = [
        'precio' => 'float',
        'fecha_registro' => 'datetime',
        'existencia' => 'integer',
    ];

    // Métodos estáticos auxiliares

    /**
     * Suma total de existencia de productos.
     */
    public static function total(): int
    {
        return static::where('estado', 'activo')->sum('existencia');
    }

    /**
     * Total de existencia por varias ubicaciones.
     */
    public static function contarPorUbicaciones($ubicacionIds)
    {
        return self::where('estado', '!=', 'baja') // Excluir registros con estado "baja"
            ->whereIn('ubicacion_nombre', function ($query) use ($ubicacionIds) {
                $query->select('nombre')
                    ->from('ubicacion')
                    ->whereIn('id', $ubicacionIds)
                    ->where('estado', 'activo'); // Filtrar ubicaciones activas
            })->sum('existencia');
    }

    /**
     * Total de existencia por una ubicación específica.
     */
    public static function contarPorUbicacion($ubicacionId)
    {
        return self::where('estado', '!=', 'baja') // Excluir registros con estado "baja"
            ->where('estado', '!=', 'inexistente')
            ->where('estado', '!=', 'eliminado')
            ->whereIn('ubicacion_nombre', function ($query) use ($ubicacionId) {
                $query->select('nombre')
                    ->from('ubicacion')
                    ->where('id', $ubicacionId)
                    ->where('estado', 'activo'); // Filtrar ubicaciones activas
            })->sum('existencia');
    }

    /**
     * Obtiene la existencia por nombre de producto.
     */
    public static function existenciaPorNombre($nombreProducto)
    {
        return self::where('nombre', $nombreProducto)->value('existencia');
    }

    public static function obtenerActivosEmpresa($empresaNombre)
    {
        return self::where('estado', '!=', 'baja')
            ->where('estado', '!=', 'inexistente')
            ->where('estado', '!=', 'eliminado')
            ->where('empresa_nombre', $empresaNombre)
            ->get();
    }

    public static function obtenerActivosUbicacion($ubicacionNombre)
    {
        return self::where('estado', '!=', 'baja')
            ->where('estado', '!=', 'inexistente')
            ->where('estado', '!=', 'eliminado')
            ->where('ubicacion_nombre', $ubicacionNombre)
            ->get();
    }
}
