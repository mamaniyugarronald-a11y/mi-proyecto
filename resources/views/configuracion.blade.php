@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-building-gear text-primary me-2"></i>Identidad Corporativa & Parámetros Generales (HU30)</h3>
            <p class="text-muted small mb-0">Configura los datos fiscales, logotipo, encabezados y pie de página que aparecen en los comprobantes y notas de venta</p>
        </div>
    </div>

    <!-- Alertas Flash -->
    @if(session('mensaje'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('mensaje') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <strong class="d-block mb-1">Por favor corrige los siguientes errores:</strong>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Formulario de Configuración -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders me-2"></i>Datos Comerciales de la Empresa</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('guardar.configuracion') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Razón Social / Nombre Comercial *</label>
                                <input type="text" name="razon_social" class="form-control" value="{{ old('razon_social', $empresa->razon_social) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">NIT / Documento Fiscal *</label>
                                <input type="text" name="nit" class="form-control" value="{{ old('nit', $empresa->nit) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Dirección Comercial Principal *</label>
                                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $empresa->direccion) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ciudad / País *</label>
                                <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $empresa->ciudad) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Teléfono / Celular WhatsApp *</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $empresa->telefono) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Correo Electrónico *</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Moneda Principal</label>
                                <input type="text" name="moneda_principal" class="form-control" value="{{ old('moneda_principal', $empresa->moneda_principal) }}" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Actualizar Logotipo (Opcional)</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Formato recomendado PNG, JPG transparente.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Mensaje de Pie de Página en Notas de Venta / Comprobantes</label>
                                <textarea name="mensaje_pie_pagina" class="form-control" rows="3">{{ old('mensaje_pie_pagina', $empresa->mensaje_pie_pagina) }}</textarea>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Vista Previa de Comprobante / Membrete -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-light">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-eye me-2"></i>Vista Previa de Membrete</h6>
                </div>
                <div class="card-body p-4 text-center">
                    @if($empresa->logo_path && file_exists(public_path($empresa->logo_path)))
                        <img src="{{ asset($empresa->logo_path) }}" alt="Logo" class="img-fluid mb-3 rounded" style="max-height: 80px;">
                    @else
                        <div class="bg-white border rounded p-3 d-inline-block shadow-sm mb-3">
                            <i class="bi bi-box-seam text-primary fs-1"></i>
                        </div>
                    @endif
                    <h5 class="fw-bold text-dark mb-1">{{ $empresa->razon_social }}</h5>
                    <div class="badge bg-primary mb-2">NIT: {{ $empresa->nit }}</div>
                    <p class="text-muted small mb-1">{{ $empresa->direccion }}</p>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i> {{ $empresa->telefono }} | <i class="bi bi-envelope me-1"></i> {{ $empresa->email }}</p>
                    <hr>
                    <div class="p-2 border rounded bg-white small text-muted fst-italic">
                        "{{ $empresa->mensaje_pie_pagina }}"
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
