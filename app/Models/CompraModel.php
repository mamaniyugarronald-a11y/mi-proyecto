<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraModel extends Model
{
    use HasFactory;
     protected $table = 'compra';

    protected $primaryKey = 'id_compra';

    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'id_usuario',
        'fecha',
        'nro_comprobante'
    ];

    public function proveedor()
    {
        return $this->belongsTo(
            ProveedorModel::class,
            'id_proveedor',
            'id_proveedor'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            UsuarioModel::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function detalles()
    {
        return $this->hasMany(
            DetalleCompraModel::class,
            'id_compra',
            'id_compra'
        );
    }
}
