@extends('adm_plantilla')

@section('content')

<div class="container-fluid py-2">

    <!-- Encabezado Principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-fingerprint text-primary me-2"></i>Auditoría de Accesos & Sesiones (HU5)</h3>
            <p class="text-muted small mb-0">Control estricto de seguridad informática con registro cronológico de fechas, horas y cuentas de usuario que ingresan a la plataforma</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-dark shadow-sm">
            <i class="bi bi-printer me-1"></i> Imprimir Reporte de Auditoría
        </button>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-shield-lock me-2"></i>Historial de Inicios de Sesión (Últimos 100 accesos)</h6>
            <span class="badge bg-primary-subtle text-primary fw-semibold">{{ count($sesiones) }} Registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">ID Log</th>
                        <th>Fecha & Hora de Ingreso</th>
                        <th>Usuario / Empleado</th>
                        <th>Nombre de Cuenta</th>
                        <th>Rol Asignado</th>
                        <th>Correo Electrónico</th>
                        <th class="text-center pe-4">Estado del Acceso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sesiones as $log)
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-light text-dark border">#{{ $log->id_historial }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ date('d/m/Y', strtotime($log->fecha_hora)) }}</div>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ date('H:i:s', strtotime($log->fecha_hora)) }}</small>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $log->usuario->nombre ?? 'Usuario' }} {{ $log->usuario->apellido ?? '' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace">
                                @ {{ $log->usuario->usuario ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            @if(($log->usuario->id_rol ?? 2) == 1)
                                <span class="badge bg-danger">Administrador</span>
                            @else
                                <span class="badge bg-primary">Vendedor</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $log->usuario->email ?? '-' }}
                        </td>
                        <td class="text-center pe-4">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-shield-check me-1"></i> Sesión Autenticada
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            No hay registros de auditoría de sesiones.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
