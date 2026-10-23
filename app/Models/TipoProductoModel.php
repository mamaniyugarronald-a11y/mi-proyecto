<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoProductoModel extends Model
{
    use HasFactory;
    protected $table = 'tipo_producto';

    protected $primaryKey = 'id_tipo_producto';

    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo',
        'descripcion'
    ];

    public function productos()
{
    return $this->hasMany(
        ProductoModel::class,
        'id_tipo_producto',
        'id_tipo_producto'
    );
}
}
