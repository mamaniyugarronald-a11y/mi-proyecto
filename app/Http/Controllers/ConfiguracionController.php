<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $empresa = ConfiguracionModel::getEmpresa();
        return view('configuracion', compact('empresa'));
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'razon_social'       => 'required|string|max:200',
            'nit'                => 'required|string|max:50',
            'direccion'          => 'required|string|max:255',
            'telefono'           => 'required|string|max:50',
            'email'              => 'required|email|max:150',
            'ciudad'             => 'required|string|max:100',
            'moneda_principal'   => 'required|string|max:20',
            'mensaje_pie_pagina' => 'nullable|string',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048'
        ]);

        $empresa = ConfiguracionModel::getEmpresa();

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $nombreLogo = 'logo_pebie_' . time() . '.' . $logo->getClientOriginalExtension();
            $ruta = public_path('uploads/empresa');
            if (!File::exists($ruta)) {
                File::makeDirectory($ruta, 0755, true);
            }
            $logo->move($ruta, $nombreLogo);
            $empresa->logo_path = 'uploads/empresa/' . $nombreLogo;
        }

        $empresa->razon_social       = $request->razon_social;
        $empresa->nit                = $request->nit;
        $empresa->direccion          = $request->direccion;
        $empresa->telefono           = $request->telefono;
        $empresa->email              = $request->email;
        $empresa->ciudad             = $request->ciudad;
        $empresa->moneda_principal   = $request->moneda_principal;
        $empresa->mensaje_pie_pagina = $request->mensaje_pie_pagina;
        $empresa->save();

        return redirect()->back()->with('mensaje', 'Identidad corporativa y datos comerciales actualizados con éxito.');
    }
}
