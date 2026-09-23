<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionConsumible extends Model
{
    protected $table = 'notificaciones_consumibles';

    protected $fillable = [
        'id_user',
        'id_productos_consumibles',
        'limite',
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_user');
    }
}
