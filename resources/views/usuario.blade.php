@extends('adm_plantilla')

@section('content')

<div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-people me-2"></i>Gestión de Usuarios
        </h4>
        <p class="text-muted small mb-0">Administración de credenciales y roles de acceso</p>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="text-secondary small">
            <i class="bi bi-person-circle me-1"></i>{{ session('nom') }}
        </span>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
        </a>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">Control de usuarios activos e inactivos</span>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRoles"><i class="bi bi-shield-lock me-1"></i>Gestionar roles</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevo"><i class="bi bi-plus-lg me-1"></i>Nuevo Usuario</button>
    </div>
</div>

@if(session('mensaje'))
<div class="alert alert-success alert-dismissible fade show py-2" role="alert"><i class="bi bi-check-circle-fill me-2"></i>{{ session('mensaje') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show py-2" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="table-responsive">
    <table class="table table-hover border align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $u)
                <tr>
                    <td class="fw-bold">{{$u->id_usuario}}</td>
                    <td>{{$u->nombre}}</td>
                    <td>{{$u->apellido}}</td>
                    <td><code>{{$u->usuario}}</code></td>
                    <td>{{$u->email}}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $u->rol->nombre_rol ?? 'Sin rol' }}</span>
                    </td>
                    <td><span class="badge {{ $u->estado ? 'bg-success' : 'bg-danger' }}">{{ $u->estado ? 'Activo' : 'Inactivo' }}</span></td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editar{{$u->id_usuario}}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="{{url('eliminarusuario',$u->id_usuario)}}" class="btn btn-danger" onclick="return confirm('¿Confirmar eliminación del usuario?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>

                <!-- Modal Actualizar -->
                <div class="modal fade" id="editar{{$u->id_usuario}}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="{{url('actualizarusuario')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id_usuario" value="{{$u->id_usuario}}">
                                
                                <div class="modal-header">
                                    <h5 class="modal-title fs-6 fw-bold">Actualizar Usuario</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Nombre</label>
                                        <input type="text" name="nombre" value="{{$u->nombre}}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Apellido</label>
                                        <input type="text" name="apellido" value="{{$u->apellido}}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Usuario</label>
                                        <input type="text" name="usuario" value="{{$u->usuario}}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Correo Electrónico</label>
                                        <input type="email" name="email" value="{{$u->email}}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Contraseña</label>
                                        <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Repetir contraseña</label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repita la nueva contraseña" autocomplete="new-password">
                                        <small class="text-muted">Deje ambos campos vacíos para conservar la contraseña actual.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Rol de Sistema</label>
                                        <select name="id_rol" class="form-select" required>
                                            @foreach($roles as $rol)<option value="{{ $rol->id_rol }}" {{ $u->id_rol == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre_rol }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Estado de la cuenta</label>
                                        <select name="estado" class="form-select" required>
                                            <option value="1" {{ $u->estado ? 'selected' : '' }}>Activo</option>
                                            <option value="0" {{ !$u->estado ? 'selected' : '' }}>Inactivo</option>
                                        </select>
                                        <small class="text-muted">Una cuenta inactiva no puede iniciar sesión.</small>
                                    </div>
                                </div>

                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal: gestión de roles -->
<div class="modal fade" id="modalRoles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-shield-lock me-2"></i>Gestión de roles</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Los roles asignados a usuarios no pueden eliminarse.</span>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoRol"><i class="bi bi-plus-lg me-1"></i>Nuevo rol</button>
                </div>
                <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>ID</th><th>Nombre del rol</th><th class="text-center">Usuarios asignados</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                    @forelse($roles as $rol)
                    <tr>
                        <td>#{{ $rol->id_rol }}</td><td class="fw-semibold">{{ $rol->nombre_rol }}</td><td class="text-center"><span class="badge bg-secondary">{{ $rol->usuarios_count }}</span></td>
                        <td class="text-end"><div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditarRol{{ $rol->id_rol }}" title="Actualizar rol"><i class="bi bi-pencil"></i></button>
                            <form action="{{ route('roles.eliminar', $rol->id_rol) }}" method="POST" class="d-inline">@csrf<button class="btn btn-outline-danger" onclick="return confirm('¿Desea eliminar este rol?')" title="Eliminar rol" {{ $rol->usuarios_count > 0 ? 'disabled' : '' }}><i class="bi bi-trash"></i></button></form>
                        </div></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No hay roles registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button></div>
        </div>
    </div>
</div>

<!-- Modal: nuevo rol -->
<div class="modal fade" id="modalNuevoRol" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
    <form action="{{ route('roles.guardar') }}" method="POST">@csrf
        <div class="modal-header"><h5 class="modal-title fs-6 fw-bold">Registrar nuevo rol</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($errors->crearRol->any())<div class="alert alert-danger py-2 small"><strong>No se pudo registrar el rol:</strong><ul class="mb-0 ps-3">@foreach($errors->crearRol->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <label class="form-label fw-semibold">Nombre del rol *</label><input type="text" name="nombre_rol" value="{{ old('nombre_rol') }}" class="form-control" placeholder="Ej. Supervisor" required maxlength="100">
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-success">Guardar rol</button></div>
    </form>
</div></div></div>

@foreach($roles as $rol)
<div class="modal fade" id="modalEditarRol{{ $rol->id_rol }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
    <form action="{{ route('roles.actualizar') }}" method="POST">@csrf<input type="hidden" name="id_rol" value="{{ $rol->id_rol }}">
        <div class="modal-header"><h5 class="modal-title fs-6 fw-bold">Actualizar rol</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            @if($errors->editarRol->any() && old('id_rol') == $rol->id_rol)<div class="alert alert-danger py-2 small"><strong>No se pudo actualizar el rol:</strong><ul class="mb-0 ps-3">@foreach($errors->editarRol->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <label class="form-label fw-semibold">Nombre del rol *</label><input type="text" name="nombre_rol" value="{{ old('id_rol') == $rol->id_rol ? old('nombre_rol') : $rol->nombre_rol }}" class="form-control" required maxlength="100">
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary">Guardar cambios</button></div>
    </form>
</div></div></div>
@endforeach

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{url('guardarusuario')}}" method="POST" id="formNuevoUsuario">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold">Registrar Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Alerta de Error -->
                    <div id="alertaError" class="alert alert-danger d-none py-2 mb-3 small" role="alert"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre</label>
                        <input type="text" name="nombre" class="form-control solo-letras" placeholder="Ej. Juan" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Apellido</label>
                        <input type="text" name="apellido" class="form-control solo-letras" placeholder="Ej. Pérez" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Usuario</label>
                        <input type="text" name="usuario" id="nuevo_usuario" class="form-control" placeholder="Ej. jperez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Correo Electrónico</label>
                        <input type="email" name="email" id="nuevo_email" class="form-control" placeholder="ejemplo@pebie.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Repetir contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repita la contraseña" autocomplete="new-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rol de Sistema</label>
                        <select name="id_rol" class="form-select" required>
                            <option value="" disabled selected>Seleccione un rol...</option>
                            @foreach($roles as $rol)<option value="{{ $rol->id_rol }}">{{ $rol->nombre_rol }}</option>@endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Bloquear entrada de números en tiempo real en Nombre y Apellido
    document.querySelectorAll('.solo-letras').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    });

    // 2. Extraer usuarios y correos existentes en la tabla HTML de la vista
    const usuariosExistentes = [];
    const emailsExistentes = [];

    document.querySelectorAll('tbody tr').forEach(row => {
        const userCell = row.children[3]?.textContent.trim().toLowerCase();
        const emailCell = row.children[4]?.textContent.trim().toLowerCase();
        if (userCell) usuariosExistentes.push(userCell);
        if (emailCell) emailsExistentes.push(emailCell);
    });

    // 3. Validar duplicados antes de enviar el formulario
    const form = document.getElementById('formNuevoUsuario');
    const alerta = document.getElementById('alertaError');

    form.addEventListener('submit', function (e) {
        const inputUser = document.getElementById('nuevo_usuario').value.trim().toLowerCase();
        const inputEmail = document.getElementById('nuevo_email').value.trim().toLowerCase();

        alerta.classList.add('d-none');
        alerta.textContent = '';

        if (usuariosExistentes.includes(inputUser)) {
            e.preventDefault();
            alerta.textContent = 'El nombre de usuario ya está registrado en el sistema.';
            alerta.classList.remove('d-none');
            return;
        }

        if (emailsExistentes.includes(inputEmail)) {
            e.preventDefault();
            alerta.textContent = 'El correo electrónico ya está registrado en el sistema.';
            alerta.classList.remove('d-none');
            return;
        }
    });
});
</script>

@if($errors->crearRol->any())
<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoRol')).show());</script>
@endif
@if($errors->editarRol->any() && old('id_rol'))
<script>document.addEventListener('DOMContentLoaded', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarRol{{ old('id_rol') }}')).show());</script>
@endif

@endsection
