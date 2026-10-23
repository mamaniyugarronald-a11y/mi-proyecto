<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioModel extends Model
{
    use HasFactory;

    protected $table = "usuario";
    protected $primaryKey = "id_usuario";

    public $timestamps = false;

    protected $fillable=[
        'nombre',
        'apellido',
        'usuario',
        'email',
        'password',
        'id_rol',
        'estado'
    ];

    public function rol()
    {
        return $this->belongsTo(RolModel::class, 'id_rol', 'id_rol');
    }

    public function ventas()
    {
        return $this->hasMany(VentaModel::class, 'id_usuario', 'id_usuario');
    }

    public function compras()
    {
        return $this->hasMany(CompraModel::class, 'id_usuario', 'id_usuario');
    }

    public function historial()
    {
        return $this->hasMany(HistorialSesionModel::class, 'id_usuario', 'id_usuario');
    }
}
