<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleImportacionModel extends Model
{
    use HasFactory;

    protected $table = 'detalle_importacion';
    protected $primaryKey = 'id_detalle_importacion';
    public $timestamps = false;

    protected $fillable = [
        'id_importacion',
        'id_producto',
        'cantidad',
        'precio_unitario_fob_usd',
        'gasto_internacion_unitario_bs',
        'costo_final_unitario_bs'
    ];

    public function importacion()
    {
        return $this->belongsTo(ImportacionModel::class, 'id_importacion', 'id_importacion');
    }

    public function producto()
    {
        return $this->belongsTo(ProductoModel::class, 'id_producto', 'id_producto');
    }
}
