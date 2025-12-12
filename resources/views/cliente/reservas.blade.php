@extends('layouts.cliente')

@section('page-title', 'Mis Reservas')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Todas Mis Reservas</h5>
        <a href="{{ route('cliente.habitaciones.index') }}" class="btn btn-sm btn-light">
            <i class="fas fa-plus"></i> Nueva Reserva
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Habitación</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Noches</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td>
                                <strong>{{ $reserva->habitacion->numero }}</strong><br>
                                <small class="text-muted">{{ $reserva->habitacion->tipoHabitacion->nombre }}</small>
                            </td>
                            <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ abs($reserva->calcularNoches()) }}</td>
                            <td>
                                <strong>${{ number_format($reserva->precio_total, 2) }}</strong>
                                <small class="d-block text-muted">IVA incluido</small>

                                @if($reserva->monto_diferencia && $reserva->tipo_diferencia === 'pagar' && !$reserva->fecha_diferencia_pagada)
                                    <span class="badge bg-warning mt-1">
                                        <i class="fas fa-exclamation-circle"></i> Diferencia pendiente: ${{ number_format($reserva->monto_diferencia, 2) }}
                                    </span>
                                @elseif($reserva->monto_diferencia && $reserva->tipo_diferencia === 'reembolsar' && !$reserva->fecha_diferencia_pagada)
                                    <span class="badge bg-info mt-1">
                                        <i class="fas fa-undo"></i> Reembolso pendiente: ${{ number_format($reserva->monto_diferencia, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($reserva->estado === 'confirmada')
                                    <span class="badge bg-success">Confirmada</span>
                                @elseif($reserva->estado === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @elseif($reserva->estado === 'completada')
                                    <span class="badge bg-info">Completada</span>
                                @elseif($reserva->estado === 'cancelada')
                                    <span class="badge bg-danger">Cancelada</span>
                                @else
                                    <span class="badge bg-secondary">{{ $reserva->estado_formateado }}</span>
                                @endif
                            </td>
                            <td style="min-width: 150px;">
                                @if($reserva->estado === 'pendiente')
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('cliente.reservas.edit', $reserva->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <a href="{{ route('cliente.pagos.create', ['reserva_id' => $reserva->id]) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-credit-card"></i> Pagar
                                        </a>
                                        <form action="{{ route('cliente.reservas.cancelar', $reserva->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-danger w-100">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                        </form>
                                    </div>
                                @elseif($reserva->estado === 'confirmada')
                                    @if($reserva->monto_diferencia && $reserva->tipo_diferencia === 'pagar' && !$reserva->fecha_diferencia_pagada)
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pagada</span>
                                            <a href="{{ route('cliente.pagos.create', ['reserva_id' => $reserva->id, 'diferencia' => true]) }}"
                                               class="btn btn-sm btn-warning text-nowrap">
                                                <i class="fas fa-dollar-sign"></i> Pagar
                                            </a>
                                            <small class="text-muted text-center">${{ number_format($reserva->monto_diferencia, 2) }}</small>
                                        </div>
                                    @elseif($reserva->monto_diferencia && $reserva->tipo_diferencia === 'reembolsar' && !$reserva->fecha_diferencia_pagada)
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pagada</span>
                                            <form action="{{ route('cliente.reservas.aceptar-reembolso-diferencia', $reserva->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info w-100 text-nowrap"
                                                        onclick="return confirm('¿Aceptar reembolso de ${{ number_format($reserva->monto_diferencia, 2) }}? El dinero se procesará en 3-5 días hábiles.')">
                                                    <i class="fas fa-undo"></i> Aceptar
                                                </button>
                                            </form>
                                            <small class="text-muted text-center">${{ number_format($reserva->monto_diferencia, 2) }}</small>
                                        </div>
                                    @else
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pagada</span>
                                    @endif
                                @elseif($reserva->estado === 'cancelada')
                                    <span class="badge bg-danger"><i class="fas fa-ban"></i> Cancelada</span>
                                @elseif($reserva->estado === 'en_proceso_reembolso')
                                    <form action="{{ route('cliente.reservas.aceptar-reembolso', $reserva->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap"
                                                onclick="return confirm('¿Aceptar reembolso de ${{ number_format($reserva->monto_reembolso, 2) }}? El dinero se procesará en 3-5 días hábiles.')">
                                            <i class="fas fa-check"></i> Aceptar
                                        </button>
                                    </form>
                                @elseif($reserva->estado === 'reembolsado')
                                    <span class="badge bg-info"><i class="fas fa-undo"></i> Reembolsado</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No tienes reservas aún</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN PERSONALIZADA -->
        @if($reservas->hasPages())
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Botones de navegación -->
                    <div>
                        @if($reservas->onFirstPage())
                            <span class="text-muted">« Volver</span>
                        @else
                            <a href="{{ $reservas->previousPageUrl() }}" class="text-decoration-none">« Volver</a>
                        @endif

                        <span class="mx-3">|</span>

                        @if($reservas->hasMorePages())
                            <a href="{{ $reservas->nextPageUrl() }}" class="text-decoration-none">Siguiente »</a>
                        @else
                            <span class="text-muted">Siguiente »</span>
                        @endif
                    </div>

                    <!-- Información de resultados -->
                    <div class="text-muted small">
                        Mostrando {{ $reservas->firstItem() ?? 0 }} a {{ $reservas->lastItem() ?? 0 }} de {{ $reservas->total() }} resultados
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- INFORMACIÓN ÚTIL -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle text-primary"></i> Estados de Reserva</h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <span class="badge bg-warning text-dark">Pendiente</span>
                        <span class="ms-2">Reserva creada, pendiente de pago</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-success">Confirmada</span>
                        <span class="ms-2">Pago recibido, reserva confirmada</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info">Completada</span>
                        <span class="ms-2">Estancia finalizada</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-danger">Cancelada</span>
                        <span class="ms-2">Reserva cancelada</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-secondary">En Proceso de Reembolso</span>
                        <span class="ms-2">Cancelada por admin, pendiente de aceptar reembolso</span>
                    </li>
                    <li class="mb-2">
                        <span class="badge bg-info">Reembolsado</span>
                        <span class="ms-2">Reembolso aceptado, en proceso de devolución</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-phone text-success"></i> ¿Necesitas Ayuda?</h6>
                <p class="small mb-0">
                    Para modificar o cancelar una reserva, por favor contacta a nuestro equipo:<br>
                    <i class="fas fa-envelope"></i> reservas@hoteloaxaca.com<br>
                    <i class="fas fa-phone"></i> +52 951 123 4567
                </p>
            </div>
        </div>
    </div>
</div>

@endsection
