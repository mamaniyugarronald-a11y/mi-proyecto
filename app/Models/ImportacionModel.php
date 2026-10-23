<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportacionModel extends Model
{
    use HasFactory;

    protected $table = 'importacion';
    protected $primaryKey = 'id_importacion';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'id_usuario',
        'codigo_embarque',
        'fecha_pedido',
        'estado_logistico',
        'monto_fob_usd',
        'costo_flete',
        'costo_aduana_aranceles',
        'costo_seguro',
        'otros_gastos',
        'total_gastos_internacion',
        'tipo_cambio',
        'pais_origen',
        'fecha_llegada_estimada',
        'fecha_recibido',
        'observaciones'
    ];

    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'id_proveedor', 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo(UsuarioModel::class, 'id_usuario', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleImportacionModel::class, 'id_importacion', 'id_importacion');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoImportacionModel::class, 'id_importacion', 'id_importacion')->orderBy('fecha_hora', 'desc');
    }

    public function getTotalGastosCalculadoAttribute()
    {
        return (float)$this->costo_flete + (float)$this->costo_aduana_aranceles + (float)$this->costo_seguro + (float)$this->otros_gastos;
    }

    public function getTotalInversionBsAttribute()
    {
        $fobBs = (float)$this->monto_fob_usd * (float)($this->tipo_cambio ?: 6.96);
        return $fobBs + (float)$this->total_gastos_internacion;
    }
}
