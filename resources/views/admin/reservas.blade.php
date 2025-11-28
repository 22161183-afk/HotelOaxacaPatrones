@extends('layouts.admin')

@section('page-title', 'Gestión de Reservas')

@section('content')

<!-- BARRA DE BÚSQUEDA CON FILTROS (Patrón Interpreter) -->
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-search"></i> Búsqueda Avanzada de Reservas</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reservas.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                        <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
                        <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cliente</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Nombre del cliente" value="{{ request('cliente') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Habitación</label>
                    <input type="text" name="habitacion" class="form-control" placeholder="Número" value="{{ request('habitacion') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Precio Mín.</label>
                    <input type="number" name="precio_min" class="form-control" placeholder="$0" value="{{ request('precio_min') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Precio Máx.</label>
                    <input type="number" name="precio_max" class="form-control" placeholder="$9999" value="{{ request('precio_max') }}">
                </div>
                <div class="col-md-8 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.reservas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                    @if(request()->hasAny(['estado', 'cliente', 'habitacion', 'fecha_inicio', 'fecha_fin', 'precio_min', 'precio_max']))
                        <div class="alert alert-info mb-0 py-2 px-3">
                            <i class="fas fa-filter"></i> Filtros aplicados
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-list"></i> Todas las Reservas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
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
                    @forelse($reservas as $reserva)
                        <tr>
                            <td>
                                <strong>{{ $reserva->habitacion->numero }}</strong><br>
                                <small class="text-muted">{{ $reserva->habitacion->tipoHabitacion->nombre }}</small>
                            </td>
                            <td>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</td>
                            <td>{{ $reserva->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $reserva->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ $reserva->calcularNoches() }} noches</td>
                            <td><strong>${{ number_format($reserva->precio_total, 2) }}</strong></td>
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
                                <a href="{{ route('admin.reservas.show', $reserva->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver Detalles
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
