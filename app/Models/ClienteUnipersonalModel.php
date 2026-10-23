<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\ClienteModel;
class ClienteUnipersonalModel extends Model
{
    use HasFactory;
    protected $table = "cliente_unipersonal";

    protected $primaryKey = "id_cliente";

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'id_cliente',
        'nombre',
        'apellido',
        'ci'
    ];

    // Relación con cliente
    public function cliente()
    {
        return $this->belongsTo(ClienteModel::class, 'id_cliente', 'id_cliente');
    }
}
