<?php

namespace App\Models;
use App\Models\ProductoModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompraModel extends Model
{
    use HasFactory;
    protected $table = 'detalle_compra';

    protected $primaryKey = 'id_detalle_compra';

    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'precio_compra'
    ];

    public function compra()
    {
        return $this->belongsTo(
            CompraModel::class,
            'id_compra',
            'id_compra'
        );
    }

    public function producto()
    {
        return $this->belongsTo(
            ProductoModel::class,
            'id_producto',
            'id_producto'
        );
    }
}
