<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionModel extends Model
{
    use HasFactory;

    protected $table = 'configuracion_empresa';
    protected $primaryKey = 'id_config';
    public $timestamps = false;

    protected $fillable = [
        'razon_social',
        'nit',
        'direccion',
        'telefono',
        'email',
        'ciudad',
        'moneda_principal',
        'logo_path',
        'mensaje_pie_pagina'
    ];

    public static function getEmpresa()
    {
        $config = self::first();
        if (!$config) {
            $config = self::create([
                'razon_social'      => 'IMPORTADORA PEBIE',
                'nit'               => '7839401018',
                'direccion'         => 'Calle Bolívar N° 450 entre 6 de Octubre y Soria Galvarro, Oruro - Bolivia',
                'telefono'          => '+591 72412345',
                'email'             => 'contacto@pebie.com',
                'ciudad'            => 'Oruro, Bolivia',
                'moneda_principal'  => 'Bs.',
                'mensaje_pie_pagina'=> 'Gracias por su preferencia - Importadora PEBIE: Artículos modernos y funcionales para el hogar.'
            ]);
        }
        return $config;
    }
}
