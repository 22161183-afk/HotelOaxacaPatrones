@extends('layouts.cliente')

@section('page-title', 'Principal')

@section('content')

<!-- MENSAJE DE BIENVENIDA -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-primary alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-hand-wave"></i> ¡Bienvenido, {{ $cliente->nombre }}!</h4>
            <p class="mb-0">Estamos encantados de tenerte de vuelta. Aquí puedes gestionar todas tus reservas y explorar nuestras habitaciones disponibles.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- ESTADÍSTICAS DEL CLIENTE -->
<div class="row">
    <div class="col-md-4">
        <div class="card stat-card primary">
            <h5><i class="fas fa-calendar-check"></i> Reservas Activas</h5>
            <div class="stat-value">{{ $reservasActivas->count() }}</div>
            <small class="text-muted">Reservas próximas</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card success">
            <h5><i class="fas fa-history"></i> Total Reservas</h5>
            <div class="stat-value">{{ $totalReservas }}</div>
            <small class="text-muted">Historial completo</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card warning">
            <h5><i class="fas fa-dollar-sign"></i> Total Gastado</h5>
            <div class="stat-value">${{ number_format($totalGastado, 0) }}</div>
            <small class="text-muted">En reservas completadas</small>
        </div>
    </div>
</div>

<!-- MIS RESERVAS ACTIVAS -->
@if($reservasActivas->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Mis Próximas Reservas</h5>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservasActivas as $reserva)
                                <tr>
                                    <td><strong>{{ $reserva->habitacion->numero }}</strong> - {{ $reserva->habitacion->tipoHabitacion->nombre }}</td>
                                    <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                                    <td>{{ abs($reserva->calcularNoches()) }}</td>
                                    <td><strong>${{ number_format($reserva->precio_total, 2) }}</strong></td>
                                    <td>
                                        @if($reserva->estado === 'confirmada')
                                            <span class="badge bg-success">Confirmada</span>
                                        @elseif($reserva->estado === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($reserva->estado) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- HABITACIONES DISPONIBLES -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-door-open"></i> Habitaciones Disponibles para Reservar</h5>
                <a href="{{ route('cliente.habitaciones.index') }}" class="btn btn-sm btn-light">
                    Ver Todas
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    @forelse($habitacionesDisponibles as $habitacion)
                        <div class="col-md-6 col-lg-3">
                            <div class="card mb-3 h-100 shadow-sm">
                                @php
                                    $imagenPrincipal = \App\Patterns\Creational\HabitacionImagenFactory::obtenerImagenPrincipal($habitacion);
                                @endphp
                                <img src="{{ $imagenPrincipal }}"
                                     class="card-img-top"
                                     alt="Habitación {{ $habitacion->numero }}"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">Habitación {{ $habitacion->numero }}</h6>
                                    <p class="card-text small flex-grow-1">
                                        <strong>{{ $habitacion->tipoHabitacion->nombre }}</strong><br>
                                        <i class="fas fa-users"></i> {{ $habitacion->capacidad }} personas<br>
                                        <strong class="text-primary">${{ number_format($habitacion->precio_base, 2) }}/noche</strong>
                                    </p>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('habitaciones.show', $habitacion->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </a>
                                        <a href="{{ route('cliente.reservas.create', ['habitacion_id' => $habitacion->id]) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-calendar-plus"></i> Reservar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-center text-muted">No hay habitaciones disponibles en este momento</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HISTORIAL RECIENTE -->
@if($reservasPasadas->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-history"></i> Historial Reciente</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Habitación</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservasPasadas as $reserva)
                                <tr>
                                    <td>{{ $reserva->habitacion->numero }}</td>
                                    <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                                    <td>${{ number_format($reserva->precio_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
