@extends('layouts.admin')

@section('page-title', 'Gestión de Reservas')

@section('content')

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Todas las Reservas</h5>
        <a href="/api/reservas" class="btn btn-sm btn-light" target="_blank">
            <i class="fas fa-plus"></i> Nueva Reserva (API)
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Habitación</th>
                        <th>Cliente</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td><strong>#{{ $reserva->id }}</strong></td>
                            <td>{{ $reserva->habitacion->numero }}</td>
                            <td>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</td>
                            <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                            <td>${{ number_format($reserva->precio_total, 2) }}</td>
                            <td>
                                @if($reserva->estado === 'confirmada')
                                    <span class="badge bg-success">Confirmada</span>
                                @elseif($reserva->estado === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @elseif($reserva->estado === 'completada')
                                    <span class="badge bg-info">Pagada</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($reserva->estado) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="/api/reservas/{{ $reserva->id }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay reservas</td>
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

@endsection
