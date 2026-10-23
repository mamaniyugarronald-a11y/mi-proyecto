@extends('adm_plantilla')

@section('content')

<style>
    .card-custom {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .btn-primary-custom {
        background-color: #0d3b66;
        border-color: #0d3b66;
        color: #ffffff;
    }
    .btn-primary-custom:hover {
        background-color: #092846;
        border-color: #092846;
        color: #ffffff;
    }
    .text-navy {
        color: #0d3b66;
    }
</style>

<!-- ENCABEZADO -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
    <div>
        <h2 class="text-navy fw-bold mb-0">👥 Gestión de Clientes</h2>
        <small class="text-muted">Administración centralizada de clientes unipersonales e institucionales</small>
    </div>
    <div class="d-flex align-items-center">
        <span class="fw-semibold me-3">Bienvenido, {{ session('nom') }}</span>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3">
            Cerrar sesión
        </a>
    </div>
</div>

<div class="container-fluid px-0">

    <!-- ALERTAS LARAVEL -->
    @if(session('mensaje'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('mensaje') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrige los siguientes errores:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- TABLA PRINCIPAL -->
    <div class="card card-custom mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="text-navy fw-bold mb-0">Listado de Registros</h4>
                <button class="btn btn-primary-custom fw-semibold px-3" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                    + Nuevo Cliente
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark" style="background-color: #0d3b66;">
                        <tr>
                            <th class="py-3">ID</th>
                            <th class="py-3">Cliente / Nombre</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Teléfono</th>
                            <th class="py-3">Email</th>
                            <th class="py-3">Dirección</th>
                            <th class="py-3">Fecha Registro</th>
                            <th class="py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $c)
                            <tr>
                                <td class="fw-bold text-secondary">#{{ $c->id_cliente }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $c->nombre_completo }}</div>
                                    <small class="text-muted">
                                        {{ $c->tipo_registro == "Unipersonal" ? ('CI: ' . ($c->unipersonal->ci ?? '-')) : ('NIT: ' . ($c->institucional->nit ?? '-')) }}
                                    </small>
                                </td>
                                <td>
                                    @if($c->tipo_registro == "Unipersonal")
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                            Unipersonal
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                            Institucional
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $c->telefono }}</td>
                                <td>{{ $c->email }}</td>
                                <td>{{ $c->direccion }}</td>
                                <td>{{ \Carbon\Carbon::parse($c->fecha_registro)->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#info{{ $c->id_cliente }}" title="Detalles">
                                            Info
                                        </button>
                                        <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editar{{ $c->id_cliente }}" title="Editar">
                                            Editar
                                        </button>
                                        <button class="btn btn-outline-danger btn-eliminar-cliente" data-url="{{ url('eliminarcliente', $c->id_cliente) }}" title="Eliminar">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No se encontraron clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODALES DE TABLA (Información y Edición) -->
@foreach($clientes as $c)
    <!-- MODAL INFORMACIÓN -->
    <div class="modal fade" id="info{{ $c->id_cliente }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #0d3b66;">
                    <h5 class="modal-title fw-bold">Detalles del Cliente #{{ $c->id_cliente }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-sm table-striped border mb-3">
                        <tr><th width="40%">Tipo:</th><td>{{ $c->tipo_registro }}</td></tr>
                        <tr><th>Teléfono:</th><td>{{ $c->telefono }}</td></tr>
                        <tr><th>Email:</th><td>{{ $c->email }}</td></tr>
                        <tr><th>Dirección:</th><td>{{ $c->direccion }}</td></tr>
                    </table>

                    @if($c->tipo_registro == "Unipersonal" && $c->unipersonal)
                        <h6 class="text-navy fw-bold border-bottom pb-2 mt-3">Datos Unipersonales</h6>
                        <table class="table table-sm table-bordered mb-0">
                            <tr><th width="40%">Nombre:</th><td>{{ $c->unipersonal->nombre }}</td></tr>
                            <tr><th>Apellido:</th><td>{{ $c->unipersonal->apellido }}</td></tr>
                            <tr><th>CI:</th><td>{{ $c->unipersonal->ci }}</td></tr>
                        </table>
                    @endif

                    @if($c->tipo_registro == "Institucional" && $c->institucional)
                        <h6 class="text-navy fw-bold border-bottom pb-2 mt-3">Datos Institucionales</h6>
                        <table class="table table-sm table-bordered mb-0">
                            <tr><th width="40%">Nombre de empresa:</th><td>{{ $c->institucional->nombre_empresa ?? $c->institucional->razon_social }}</td></tr>
                            <tr><th>NIT:</th><td>{{ $c->institucional->nit }}</td></tr>
                            <tr><th>Nombre del empleado:</th><td>{{ $c->institucional->nombre_empleado ?? $c->institucional->contacto_nombre }}</td></tr>
                            <tr><th>Cargo o área:</th><td>{{ $c->institucional->cargo_area ?? 'No especificado' }}</td></tr>
                        </table>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ACTUALIZAR -->
    <div class="modal fade" id="editar{{ $c->id_cliente }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ url('actualizarcliente') }}" method="POST" class="form-editar-cliente">
                    @csrf
                    <input type="hidden" name="id_cliente" value="{{ $c->id_cliente }}">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title fw-bold">Actualizar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="text" class="form-control solo-numeros" value="{{ $c->telefono }}" name="telefono" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" value="{{ $c->email }}" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Dirección</label>
                            <input type="text" class="form-control" value="{{ $c->direccion }}" name="direccion" required>
                        </div>

                        @if($c->tipo_registro == "Unipersonal" && $c->unipersonal)
                            <hr>
                            <h6 class="text-primary fw-bold mb-3">Datos Personales</h6>
                            <div class="mb-2">
                                <input type="text" class="form-control solo-letras" value="{{ $c->unipersonal->nombre }}" name="nombre" placeholder="Nombre" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control solo-letras" value="{{ $c->unipersonal->apellido }}" name="apellido" placeholder="Apellido" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" value="{{ $c->unipersonal->ci }}" name="ci" placeholder="CI" required>
                            </div>
                        @endif

                        @if($c->tipo_registro == "Institucional" && $c->institucional)
                            <hr>
                            <h6 class="text-success fw-bold mb-3">Datos Institucionales</h6>
                            <div class="mb-2"><label class="form-label small">Nombre de empresa</label>
                                <input type="text" class="form-control" value="{{ $c->institucional->nombre_empresa ?? $c->institucional->razon_social }}" name="nombre_empresa" placeholder="Nombre de empresa" required>
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control solo-numeros" value="{{ $c->institucional->nit }}" name="nit" placeholder="NIT" required>
                            </div>
                            <div class="mb-2"><label class="form-label small">Nombre del empleado</label>
                                <input type="text" class="form-control" value="{{ $c->institucional->nombre_empleado ?? $c->institucional->contacto_nombre }}" name="nombre_empleado" placeholder="Nombre del empleado" required>
                            </div>
                            <div class="mb-2"><label class="form-label small">Cargo o área del empleado</label>
                                <input type="text" class="form-control" value="{{ $c->institucional->cargo_area }}" name="cargo_area" placeholder="Cargo o área" required>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning fw-bold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- MODAL NUEVO CLIENTE -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ url('guardarcliente') }}" method="POST" id="formNuevoCliente">
                @csrf
                <div class="modal-header text-white" style="background-color: #0d3b66;">
                    <h5 class="modal-title fw-bold">Registrar Nuevo Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">

                    <!-- ALERTA DE ERRORES / DUPLICADOS DENTRO DEL MODAL -->
                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3" role="alert" style="background-color: #f8d7da; color: #842029;">
                            @foreach($errors->all() as $error)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                    <div>{{ $error }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Cliente <span class="text-danger">*</span></label>
                            <select name="tipo_registro" id="tipo_registro" class="form-select" required>
                                <option value="">Seleccione una opción...</option>
                                <option value="Unipersonal" {{ old('tipo_registro') == 'Unipersonal' ? 'selected' : '' }}>Unipersonal</option>
                                <option value="Institucional" {{ old('tipo_registro') == 'Institucional' ? 'selected' : '' }}>Institucional</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="form-control solo-numeros" required placeholder="Ej. 76543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" required placeholder="ejemplo@dominio.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dirección <span class="text-danger">*</span></label>
                            <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" class="form-control" required placeholder="Av. Principal #123">
                        </div>
                    </div>

                    <!-- DATOS UNIPERSONAL -->
                    <div id="datosUnipersonal" class="mt-4 pt-3 border-top" style="display:none;">
                        <h6 class="text-primary fw-bold mb-3">Datos Personales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="form-control input-uni solo-letras" placeholder="Nombre(s)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Apellido <span class="text-danger">*</span></label>
                                <input type="text" name="apellido" id="apellido" value="{{ old('apellido') }}" class="form-control input-uni solo-letras" placeholder="Apellido(s)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">C.I. <span class="text-danger">*</span></label>
                                <input type="text" name="ci" id="ci" value="{{ old('ci') }}" class="form-control input-uni" placeholder="Cédula de Identidad">
                            </div>
                        </div>
                    </div>

                    <!-- DATOS INSTITUCIONAL -->
                    <div id="datosInstitucional" class="mt-4 pt-3 border-top" style="display:none;">
                        <h6 class="text-success fw-bold mb-3">Datos Institucionales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nombre de empresa <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_empresa" id="nombre_empresa" value="{{ old('nombre_empresa') }}" class="form-control input-ins" placeholder="Nombre de empresa">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">NIT <span class="text-danger">*</span></label>
                                <input type="text" name="nit" id="nit" value="{{ old('nit') }}" class="form-control input-ins solo-numeros" placeholder="Número NIT">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nombre del empleado <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_empleado" id="nombre_empleado" value="{{ old('nombre_empleado') }}" class="form-control input-ins" placeholder="Nombre del empleado">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Cargo o área <span class="text-danger">*</span></label>
                                <input type="text" name="cargo_area" id="cargo_area" value="{{ old('cargo_area') }}" class="form-control input-ins" placeholder="Cargo o área del empleado">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- LIBRERÍA SWEETALERT2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Reabre el modal en caso de errores de validación
        @if(session('modal_error') || $errors->any())
            let modalElement = document.getElementById('modalNuevo');
            if (modalElement) {
                let modalNuevo = new bootstrap.Modal(modalElement);
                modalNuevo.show();
            }
        @endif

        let tipo = document.getElementById("tipo_registro");
        let uni = document.getElementById("datosUnipersonal");
        let ins = document.getElementById("datosInstitucional");
        let inputsUni = document.querySelectorAll(".input-uni");
        let inputsIns = document.querySelectorAll(".input-ins");

        // 1. Alternar vistas y atributos required según el tipo de cliente seleccionado
        function alternarCampos() {
            if (tipo.value === "Unipersonal") {
                uni.style.display = "block";
                ins.style.display = "none";
                
                inputsUni.forEach(i => i.setAttribute("required", "required"));
                inputsIns.forEach(i => {
                    i.removeAttribute("required");
                    i.value = "";
                });
            } else if (tipo.value === "Institucional") {
                uni.style.display = "none";
                ins.style.display = "block";
                
                inputsIns.forEach(i => i.setAttribute("required", "required"));
                inputsUni.forEach(i => {
                    i.removeAttribute("required");
                    i.value = "";
                });
            } else {
                uni.style.display = "none";
                ins.style.display = "none";
                inputsUni.forEach(i => i.removeAttribute("required"));
                inputsIns.forEach(i => i.removeAttribute("required"));
            }
        }

        if (tipo) {
            tipo.addEventListener("change", alternarCampos);
            alternarCampos();
        }

        // 2. RESTRICCIÓN: Impedir caracteres no permitidos
        document.addEventListener("input", function(e) {
            if (e.target.classList.contains("solo-letras")) {
                e.target.value = e.target.value.replace(/[0-9]/g, "");
            }
            if (e.target.classList.contains("solo-numeros")) {
                e.target.value = e.target.value.replace(/[^0-9]/g, "");
            }
        });

        // 3. CONFIRMACIÓN Y VALIDACIÓN SWEETALERT AL GUARDAR CLIENTE
        let formNuevo = document.getElementById("formNuevoCliente");
        if (formNuevo) {
            formNuevo.addEventListener("submit", function(e) {
                e.preventDefault();

                let tipoVal = tipo.value;
                let tel = document.getElementById("telefono").value.trim();
                let mail = document.getElementById("email").value.trim();
                let dir = document.getElementById("direccion").value.trim();

                if (!tipoVal || !tel || !mail || !dir) {
                    Swal.fire('Atención', 'Por favor completa todos los campos base obligatorios.', 'warning');
                    return;
                }

                if (tipoVal === "Unipersonal") {
                    let nom = document.getElementById("nombre").value.trim();
                    let ape = document.getElementById("apellido").value.trim();
                    let ci = document.getElementById("ci").value.trim();

                    if (!nom || !ape || !ci) {
                        Swal.fire('Campos Faltantes', 'Por favor completa el nombre, apellido y C.I. del cliente unipersonal.', 'warning');
                        return;
                    }
                }

                if (tipoVal === "Institucional") {
                    let empresa = document.getElementById("nombre_empresa").value.trim();
                    let nit = document.getElementById("nit").value.trim();
                    let empleado = document.getElementById("nombre_empleado").value.trim();
                    let cargoArea = document.getElementById("cargo_area").value.trim();

                    if (!empresa || !nit || !empleado || !cargoArea) {
                        Swal.fire('Campos Faltantes', 'Por favor completa el nombre de empresa, NIT, nombre del empleado y cargo o área.', 'warning');
                        return;
                    }
                }

                Swal.fire({
                    title: '¿Deseas guardar los datos del cliente?',
                    text: "Asegúrate de haber ingresado correctamente la información.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d3b66',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formNuevo.submit();
                    }
                });
            });
        }

        // 4. CONFIRMACIÓN SWEETALERT PARA ACTUALIZAR
        document.querySelectorAll(".form-editar-cliente").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Guardar cambios?',
                    text: "Se actualizará la información del cliente seleccionado.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // 5. CONFIRMACIÓN SWEETALERT PARA ELIMINAR
        document.querySelectorAll(".btn-eliminar-cliente").forEach(btn => {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                let url = this.getAttribute("data-url");
                Swal.fire({
                    title: '¿Confirma que desea eliminar este cliente?',
                    text: "Esta acción no se podrá deshacer.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    });
</script>

@endsection
