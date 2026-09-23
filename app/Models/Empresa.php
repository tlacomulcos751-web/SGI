<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'nombre',
        'nombre_registrado',
        'rfc',
        'direccion',
        'latitud',
        'longitud',
        'fotografias',
        'status',
        'clave'
    ];

    public $timestamps = false;

    // Relaciones
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'empresa_id');
    }

    public function productosCompraVenta()
    {
        return $this->hasMany(ProductoCompraVenta::class, 'empresaVenta_id');
    }

    // Métodos personalizados
    public function cantidadUbicaciones()
    {
        return $this->ubicaciones()->count();
    }

    public function cantidadProductos()
    {
        return $this->ubicaciones()->withCount('productos')->get()->sum('productos_count');
    }

    public function cantidadProductosCompraVenta()
    {
        return $this->productosCompraVenta()->count();
    }

    public function existenciaProductos()
    {
        return $this->hasMany(ProductoCompraVenta::class, 'empresaVenta_id')
            ->where('estado', 'almacen')
            ->sum('existencia');
    }

    public function existenciaProductosVendidos()
    {
        return $this->hasMany(ProductoCompraVenta::class, 'empresaVenta_id')
            ->where('estado', 'vendido')
            ->sum('existencia');
    }

    public static function obtenerEmpresasConPermisos(int $rolId)
    {
        $permisos = VistaPermisosRolEmpresaUbicacion::where('rol_id', $rolId);
        $empresas = [];

        foreach ($permisos->get() as $permiso) {
            $empresa = Empresa::find($permiso->empresa_id);
            if ($empresa && !in_array($empresa->id, array_column($empresas, 'id'))) {
                $empresas[] = $empresa;
            }
        }

        return $empresas;
    }

    public function getImagenes() { return explode(',', $this->fotografias); }

}
