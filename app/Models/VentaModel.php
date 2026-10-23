<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaModel extends Model
{
    use HasFactory;

    protected $table = 'venta';
    protected $primaryKey = 'id_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'fecha',
        'tipo_comprobante',
        'tipo_cliente',
        'es_venta_mayor',
        'promocion',
        'descuento',
        'metodo_pago',
        'estado',
        'estado_despacho',
        'guia_despacho',
        'fecha_despacho',
        'fecha_entrega',
        'tipo_venta'
    ];

    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_cliente', 'id_cliente');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentaModel::class, 'id_venta', 'id_venta');
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedidoModel::class, 'id_venta', 'id_venta');
    }

    public function getSubtotalAttribute()
    {
        if ($this->detalles->isNotEmpty()) {
            return $this->detalles->sum(function ($item) {
                return $item->cantidad * $item->precio_aplicado;
            });
        }
        if (in_array($this->tipo_venta, ['Pedido', 'Reserva'])) {
            return $this->detallesPedido->sum(function ($item) {
                return $item->cantidad * $item->precio_estimado;
            });
        }
        return 0;
    }

    public function getTotalAttribute()
    {
        $sub = $this->subtotal;
        $desc = (float)($this->descuento ?? 0);
        $total = $sub - $desc;
        return $total > 0 ? $total : 0;
    }
}
