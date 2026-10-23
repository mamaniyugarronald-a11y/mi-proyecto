<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCajaModel extends Model
{
    use HasFactory;

    protected $table = 'cierre_caja';
    protected $primaryKey = 'id_cierre';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'fecha_cierre',
        'total_efectivo',
        'total_transferencia_qr',
        'total_general',
        'total_ventas_conteo',
        'observaciones',
        'fecha_registro'
    ];

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }
}
