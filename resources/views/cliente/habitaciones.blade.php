@extends('layouts.cliente')

@section('page-title', 'Habitaciones Disponibles')

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <strong>Reserva tu habitación ideal</strong> - Todas las habitaciones mostradas están disponibles para reservar
        </div>
    </div>
</div>

<div class="row">
    @forelse($habitaciones as $habitacion)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">Habitación {{ $habitacion->numero }}</h5>
                        <span class="badge bg-success">Disponible</span>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-primary">{{ $habitacion->tipoHabitacion->nombre }}</h6>
                    </div>

                    <p class="card-text">
                        <i class="fas fa-building"></i> <strong>Piso:</strong> {{ $habitacion->piso }}<br>
                        <i class="fas fa-users"></i> <strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas<br>
                        <i class="fas fa-dollar-sign"></i> <strong>Precio:</strong> <span class="text-success fw-bold">${{ number_format($habitacion->precio_base, 2) }}</span>/noche
                    </p>

                    @if($habitacion->descripcion)
                        <p class="card-text small text-muted">
                            {{ $habitacion->descripcion }}
                        </p>
                    @endif

                    <div class="mb-3">
                        <small class="text-muted"><strong>Amenidades:</strong></small><br>
                        @if($habitacion->amenidades && is_array($habitacion->amenidades))
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                @foreach($habitacion->amenidades as $amenidad)
                                    <span class="badge bg-light text-dark">{{ $amenidad }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">No especificadas</span>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('habitaciones.show', $habitacion->id) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Ver Detalles Completos
                        </a>
                        <a href="{{ route('cliente.reservas.create', ['habitacion_id' => $habitacion->id]) }}" class="btn btn-success">
                            <i class="fas fa-calendar-plus"></i> Reservar Ahora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i> No hay habitaciones disponibles en este momento
            </div>
        </div>
    @endforelse
</div>

<!-- PAGINACIÓN -->
@if($habitaciones->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $habitaciones->links() }}
    </div>
@endif

<!-- INFORMACIÓN ADICIONAL -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información Importante</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-clock text-primary"></i> Horarios</h6>
                        <p class="small mb-2">
                            <strong>Entrada:</strong> 15:00 hrs<br>
                            <strong>Salida:</strong> 12:00 hrs
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-credit-card text-success"></i> Pagos</h6>
                        <p class="small mb-2">
                            Aceptamos tarjetas de crédito/débito, transferencias y efectivo.
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-ban text-danger"></i> Políticas</h6>
                        <p class="small mb-2">
                            Cancelación gratuita hasta 48 hrs antes de la fecha de entrada.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
