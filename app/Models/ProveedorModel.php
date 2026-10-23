<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorModel extends Model
{
    use HasFactory;
    protected $table = 'proveedor';

    protected $primaryKey = 'id_proveedor';

    public $timestamps = false;

    protected $fillable = [
        'nombre_empresa',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'pais',
        'calificacion',
        'notas',
        'estado'
    ];

    public function compras()
    {
        return $this->hasMany(
            CompraModel::class,
            'id_proveedor',
            'id_proveedor'
        );
    }

    public function productos()
    {
        return $this->hasMany(
            ProductoModel::class,
            'id_proveedor',
            'id_proveedor'
        );
    }

    public function importaciones()
    {
        return $this->hasMany(
            ImportacionModel::class,
            'id_proveedor',
            'id_proveedor'
        );
    }
}
