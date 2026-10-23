<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleUbicacionModel extends Model
{
    use HasFactory;

    protected $table = 'detalle_ubicacion';
    protected $primaryKey = 'id_detalle_ubicacion';
    public $timestamps = false;

    protected $fillable = [
        'id_ubicacion',
        'id_producto',
        'cantidad'
    ];

    public function ubicacion()
    {
        return $this->belongsTo(UbicacionModel::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }
}
