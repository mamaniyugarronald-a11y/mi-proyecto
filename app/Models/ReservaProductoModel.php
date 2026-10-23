<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaProductoModel extends Model
{
    use HasFactory;

    protected $table = 'reserva_producto';
    protected $primaryKey = 'id_reserva';
    public $timestamps = false;

    protected $fillable = [
        'codigo_reserva',
        'grupo_reserva',
        'id_producto',
        'nombre_cliente',
        'telefono_cliente',
        'cantidad',
        'precio_unitario',
        'total_reserva',
        'estado',
        'fecha_limite',
        'fecha_creacion'
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }
}
