<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultaClienteModel extends Model
{
    use HasFactory;

    protected $table = 'consulta_cliente';
    protected $primaryKey = 'id_consulta';
    public $timestamps = false;

    protected $fillable = [
        'nombre_cliente',
        'telefono',
        'canal',
        'id_producto',
        'mensaje_solicitud',
        'monto_cotizado',
        'estado',
        'fecha'
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }
}
