<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVentaModel extends Model
{
    use HasFactory;

    protected $table = 'detalle_venta';
    protected $primaryKey = 'id_detalle_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_aplicado'
    ];

    public function venta()
    {
        return $this->belongsTo(VentaModel::class, 'id_venta', 'id_venta');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }

    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_aplicado;
    }
}
