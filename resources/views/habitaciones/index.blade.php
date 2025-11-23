@extends('layouts.app')

@section('page-title', 'Gestión de Habitaciones')

@section('content')

<div class="row">
    @forelse($habitaciones as $habitacion)
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title">Habitación {{ $habitacion->numero }}</h5>
                        @if($habitacion->estado === 'disponible')
                            <span class="badge bg-success">Disponible</span>
                        @elseif($habitacion->estado === 'ocupada')
                            <span class="badge bg-danger">Ocupada</span>
                        @else
                            <span class="badge bg-warning">Mantenimiento</span>
                        @endif
                    </div>

                    <p class="card-text">
                        <strong>Tipo:</strong> {{ $habitacion->tipoHabitacion->nombre }}<br>
                        <strong>Piso:</strong> {{ $habitacion->piso }}<br>
                        <strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas<br>
                        <strong>Precio:</strong> ${{ number_format($habitacion->precio_base, 2) }}/noche
                    </p>

                    <div class="mb-2">
                        <small class="text-muted"><strong>Amenidades:</strong></small><br>
                        @if($habitacion->amenidades && is_array($habitacion->amenidades))
                            @foreach($habitacion->amenidades as $amenidad)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $amenidad }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>

                    <a href="/api/habitaciones/{{ $habitacion->id }}" class="btn btn-sm btn-primary" target="_blank">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No hay habitaciones registradas
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

@endsection
