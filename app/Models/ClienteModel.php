<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteModel extends Model
{
    use HasFactory;
    protected $table = "cliente";
    protected $primaryKey = "id_cliente";

    public $timestamps = false;

    protected $fillable = [
        'tipo_registro',
        'telefono',
        'email',
        'direccion',
        'fecha_registro'
    ];

    // Relación con cliente unipersonal
    public function unipersonal()
    {
        return $this->hasOne(ClienteUnipersonalModel::class, 'id_cliente', 'id_cliente');
    }

    // Relación con cliente institucional
    public function institucional()
    {
        return $this->hasOne(ClienteInstitucionalModel::class, 'id_cliente', 'id_cliente');
    }

    public function ventas()
    {
        return $this->hasMany(VentaModel::class, 'id_cliente', 'id_cliente');
    }

    public function getNombreCompletoAttribute()
    {
        if ($this->tipo_registro === 'Unipersonal' && $this->unipersonal) {
            return $this->unipersonal->nombre . ' ' . $this->unipersonal->apellido . ' (CI: ' . $this->unipersonal->ci . ')';
        } elseif ($this->tipo_registro === 'Institucional' && $this->institucional) {
            return ($this->institucional->nombre_empresa ?: $this->institucional->razon_social) . ' (NIT: ' . $this->institucional->nit . ')';
        }
        return 'Cliente #' . $this->id_cliente;
    }
}
