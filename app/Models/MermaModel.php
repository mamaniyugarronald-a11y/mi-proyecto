<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MermaModel extends Model
{
    use HasFactory;

    protected $table = 'merma';
    protected $primaryKey = 'id_merma';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'id_usuario',
        'cantidad',
        'motivo',
        'descripcion_dano',
        'fecha'
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }
}
