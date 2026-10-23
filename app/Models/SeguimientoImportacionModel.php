<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoImportacionModel extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_importacion';
    protected $primaryKey = 'id_seguimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_importacion',
        'fecha_hora',
        'ubicacion',
        'estado',
        'detalle'
    ];

    public function importacion()
    {
        return $this->belongsTo(ImportacionModel::class, 'id_importacion', 'id_importacion');
    }
}
