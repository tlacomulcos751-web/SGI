<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'id_user',
        'categoria',
        'accion',
        'fecha',
        'documentacion',
        'comentario'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'categoria' => 'string',
    ];

    // Accesor personalizado
    public function nombreProducto()
    {
        switch ($this->categoria) {
            case 'fijos':
                return ProductoFijo::find($this->id_producto)?->producto->nombre ?? 'Producto Fijo';
            case 'consumibles':
                return ProductoConsumible::find($this->id_producto)?->producto->nombre ?? 'Producto Consumible';
            case 'compraventa':
                return ProductoCompraVenta::find($this->id_producto)?->producto->nombre ?? 'Producto Compra Venta';
            case 'vehiculo':
                $vehiculo = Vehiculo::find($this->id_producto);
                return $vehiculo?->marca . ' ' . $vehiculo?->modelo ?? 'Vehículo';
            default:
                return 'Producto desconocido';
        }
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_user');
    }
    
        public static function registrosUltimos7Dias()
    {
        return self::where('accion', 'registro')
            ->where('fecha', '>=', now()->subDays(7))
            ->count();
    }

    public static function totalMovimientosUltimos7Dias()
    {
        return self::where('fecha', '>=', now()->subDays(7))->count();
    }
}
