@extends('layouts.admin')

@section('page-title', 'Principal')

@section('content')

<!-- MENSAJE DE BIENVENIDA -->
<div class="alert alert-info mb-4">
    <h4><i class="fas fa-user-shield"></i> ¡Bienvenido, {{ Auth::user()->name }}!</h4>
    <p class="mb-0">Panel de control administrativo del Hotel Oaxaca. Aquí puedes gestionar reservas, habitaciones, pagos y métodos de pago.</p>
</div>

<!-- ESTADÍSTICAS PRINCIPALES -->
<div class="row">
    <div class="col-md-3">
        <div class="card stat-card primary">
            <h5><i class="fas fa-calendar-check"></i> Reservas Hoy</h5>
            <div class="stat-value">{{ $reservasHoy }}</div>
            <small class="text-muted">Nuevas reservas hoy</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success">
            <h5><i class="fas fa-door-open"></i> Disponibles</h5>
            <div class="stat-value">{{ $habitacionesDisponibles }}/{{ $habitacionesTotales }}</div>
            <small class="text-muted">Habitaciones libres</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning">
            <h5><i class="fas fa-percent"></i> Ocupación</h5>
            <div class="stat-value">{{ $ocupacion }}%</div>
            <small class="text-muted">Tasa de ocupación</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger">
            <h5><i class="fas fa-dollar-sign"></i> Ingresos Hoy</h5>
            <div class="stat-value">${{ number_format($ingresosHoy, 2) }}</div>
            <small class="text-muted">Total hoy</small>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="row">
    <div class="col-md-8">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Ingresos Últimos 7 Días</h5>
            <canvas id="ingresoChart"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-door-open"></i> Ocupación por Tipo</h5>
            <canvas id="ocupacionChart"></canvas>
        </div>
    </div>
</div>

<!-- SERVICIOS MÁS VENDIDOS -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-star"></i> Servicios Más Vendidos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Vendidos</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviciosMasVendidos as $servicio)
                                <tr>
                                    <td>{{ $servicio->nombre }}</td>
                                    <td><span class="badge bg-primary">{{ $servicio->cantidad }}</span></td>
                                    <td>${{ number_format($servicio->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-users"></i> Top Clientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Reservas</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clientesTop as $cliente)
                                <tr>
                                    <td>{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                                    <td><span class="badge bg-success">{{ $cliente->reservas_count }}</span></td>
                                    <td>
                                        @if($cliente->reservas_count >= 5)
                                            <span class="badge bg-warning">VIP</span>
                                        @else
                                            <span class="badge bg-secondary">Regular</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Sin datos</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PRÓXIMAS RESERVAS -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Próximas Reservas (7 días)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Habitación</th>
                                <th>Cliente</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Noches</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proximasReservas as $reserva)
                                <tr>
                                    <td><strong>{{ $reserva->habitacion->numero }}</strong></td>
                                    <td>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</td>
                                    <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                                    <td>{{ $reserva->calcularNoches() }}</td>
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
                                        <a href="{{ route('admin.reservas.show', $reserva->id) }}" class="btn btn-sm btn-primary" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay reservas próximas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Gráfico de Ingresos
    const ingresoCtx = document.getElementById('ingresoChart').getContext('2d');
    const ingresosData = @json($ingresosUltimaSemana);

    new Chart(ingresoCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(ingresosData),
            datasets: [{
                label: 'Ingresos ($)',
                data: Object.values(ingresosData),
                backgroundColor: 'rgba(52, 152, 219, 0.6)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => '$' + value }
                }
            }
        }
    });

    // Gráfico de Ocupación
    const ocupacionCtx = document.getElementById('ocupacionChart').getContext('2d');
    const ocupacionData = @json($ocupacionPorTipo);

    new Chart(ocupacionCtx, {
        type: 'doughnut',
        data: {
            labels: ocupacionData.map(d => d.nombre),
            datasets: [{
                data: ocupacionData.map(d => d.total - d.disponibles),
                backgroundColor: [
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(39, 174, 96, 0.8)',
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endsection
