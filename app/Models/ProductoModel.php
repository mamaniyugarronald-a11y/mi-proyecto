<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoModel extends Model
{
    use HasFactory;
    protected $table = 'producto';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'marca',
        'precio_menor',
        'precio_mayor',
        'costo_compra',
        'cantidad_min_mayor',
        'stock',
        'stock_minimo',
        'id_tipo_producto',
        'id_proveedor',
        'estado'
    ];

    public function tipoProducto()
    {
        return $this->belongsTo(
            TipoProductoModel::class,
            'id_tipo_producto',
            'id_tipo_producto'
        );
    }

    public function proveedor()
    {
        return $this->belongsTo(
            ProveedorModel::class,
            'id_proveedor',
            'id_proveedor'
        );
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVentaModel::class, 'id_producto', 'id_producto');
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetalleCompraModel::class, 'id_producto', 'id_producto');
    }

    public function detallesUbicacion()
    {
        return $this->hasMany(DetalleUbicacionModel::class, 'id_producto', 'id_producto');
    }

    public function mermas()
    {
        return $this->hasMany(MermaModel::class, 'id_producto', 'id_producto');
    }

    public function reservas()
    {
        return $this->hasMany(ReservaProductoModel::class, 'id_producto', 'id_producto');
    }

    public function getImagenUrlAttribute()
    {
        if ($this->imagen && file_exists(public_path('img/productos/' . $this->imagen))) {
            return asset('img/productos/' . $this->imagen);
        }
        return null;
    }
}
