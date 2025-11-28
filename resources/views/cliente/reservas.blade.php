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
                            <td><strong>${{ number_format($reserva->precio_total, 2) }}</strong></td>
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
                                    <span class="badge bg-secondary">{{ ucfirst($reserva->estado) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reserva->estado === 'pendiente')
                                    <div class="btn-group-vertical gap-1" role="group">
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
                                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pagada</span>
                                @elseif($reserva->estado === 'cancelada')
                                    <span class="badge bg-danger"><i class="fas fa-ban"></i> Cancelada</span>
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

        <!-- PAGINACIÓN -->
        @if($reservas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reservas->links() }}
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
                <ul class="small mb-0">
                    <li><span class="badge bg-warning">Pendiente</span> - Reserva creada, pendiente de pago</li>
                    <li><span class="badge bg-success">Confirmada</span> - Pago recibido, reserva confirmada</li>
                    <li><span class="badge bg-info">Completada</span> - Estancia finalizada</li>
                    <li><span class="badge bg-danger">Cancelada</span> - Reserva cancelada</li>
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
