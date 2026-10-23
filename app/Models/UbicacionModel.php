<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionModel extends Model
{
    use HasFactory;

    protected $table = 'ubicacion';
    protected $primaryKey = 'id_ubicacion';
    public $timestamps = false;

    protected $fillable = [
        'nombre_ubicacion',
        'direccion'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleUbicacionModel::class, 'id_ubicacion', 'id_ubicacion');
    }
}
