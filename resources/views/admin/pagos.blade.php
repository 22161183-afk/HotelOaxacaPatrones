@extends('layouts.admin')

@section('page-title', 'Gestión de Pagos')

@section('content')

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Todos los Pagos</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reserva</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Transacción</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        <tr>
                            <td><strong>#{{ $pago->id }}</strong></td>
                            <td>
                                <a href="/api/reservas/{{ $pago->reserva_id }}" target="_blank">
                                    #{{ $pago->reserva_id }}
                                </a>
                            </td>
                            <td>
                                <strong>${{ number_format($pago->monto, 2) }}</strong>
                            </td>
                            <td>{{ $pago->metodoPago->nombre ?? 'N/A' }}</td>
                            <td><code>{{ substr($pago->transaccion_id, 0, 20) }}...</code></td>
                            <td>
                                @if($pago->estado === 'completado')
                                    <span class="badge bg-success">Pagado</span>
                                @elseif($pago->estado === 'pendiente')
                                    <span class="badge bg-warning">Pendiente</span>
                                @elseif($pago->estado === 'reembolsado')
                                    <span class="badge bg-danger">Reembolsado</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($pago->estado) }}</span>
                                @endif
                            </td>
                            <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="/api/pagos/{{ $pago->id }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay pagos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if($pagos->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $pagos->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
