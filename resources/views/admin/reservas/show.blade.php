@extends('layouts.admin')

@section('page-title', 'Detalles de Reserva #' . $reserva->id)

@section('content')

<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.reservas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Reservas
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Información de la Reserva</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-hashtag"></i> ID de Reserva:</strong> #{{ $reserva->id }}</p>
                        <p><strong><i class="fas fa-calendar-check"></i> Fecha de Entrada:</strong> {{ $reserva->fecha_inicio->format('d/m/Y') }}</p>
                        <p><strong><i class="fas fa-calendar-times"></i> Fecha de Salida:</strong> {{ $reserva->fecha_fin->format('d/m/Y') }}</p>
                        <p><strong><i class="fas fa-moon"></i> Noches:</strong> {{ abs($reserva->calcularNoches()) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-users"></i> Número de Huéspedes:</strong> {{ $reserva->numero_huespedes }}</p>
                        <p><strong><i class="fas fa-calendar-plus"></i> Fecha de Creación:</strong> {{ $reserva->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong><i class="fas fa-info-circle"></i> Estado:</strong>
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
                        </p>
                    </div>
                </div>

                <hr>

                <h5><i class="fas fa-door-open"></i> Habitación</h5>
                <div class="row">
                    <div class="col-md-4">
                        @php
                            $imagenPrincipal = \App\Patterns\Creational\HabitacionImagenFactory::obtenerImagenPrincipal($reserva->habitacion);
                        @endphp
                        <img src="{{ $imagenPrincipal }}" class="img-fluid rounded" alt="Habitación">
                    </div>
                    <div class="col-md-8">
                        <p><strong>Número:</strong> {{ $reserva->habitacion->numero }}</p>
                        <p><strong>Tipo:</strong> {{ $reserva->habitacion->tipoHabitacion->nombre }}</p>
                        <p><strong>Piso:</strong> {{ $reserva->habitacion->piso }}</p>
                        <p><strong>Capacidad:</strong> {{ $reserva->habitacion->capacidad }} personas</p>
                        <p><strong>Precio por noche:</strong> ${{ number_format($reserva->habitacion->precio_base, 2) }}</p>
                    </div>
                </div>

                <hr>

                <h5><i class="fas fa-concierge-bell"></i> Servicios Adicionales</h5>
                @if($reserva->servicios->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reserva->servicios as $servicio)
                                <tr>
                                    <td>{{ $servicio->nombre }}</td>
                                    <td>{{ $servicio->pivot->cantidad }}</td>
                                    <td>${{ number_format($servicio->pivot->precio_unitario, 2) }}</td>
                                    <td class="text-end">${{ number_format($servicio->pivot->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">No hay servicios adicionales</p>
                @endif

                <hr>

                <h5><i class="fas fa-dollar-sign"></i> Resumen de Precios</h5>
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Habitación:</strong></td>
                                <td class="text-end">${{ number_format($reserva->precio_total - $reserva->precio_servicios, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Servicios:</strong></td>
                                <td class="text-end">${{ number_format($reserva->precio_servicios, 2) }}</td>
                            </tr>
                            <tr class="table-active">
                                <td><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong>${{ number_format($reserva->precio_total, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGOS -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-credit-card"></i> Pagos</h4>
            </div>
            <div class="card-body">
                @if($reserva->pagos->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reserva->pagos as $pago)
                                <tr>
                                    <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $pago->metodoPago->nombre }}</td>
                                    <td>{{ $pago->referencia }}</td>
                                    <td>${{ number_format($pago->monto, 2) }}</td>
                                    <td>
                                        @if($pago->estado === 'completado')
                                            <span class="badge bg-success">Completado</span>
                                        @elseif($pago->estado === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($pago->estado) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No hay pagos registrados para esta reserva
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Cliente</h5>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> {{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</p>
                <p><strong>Email:</strong> {{ $reserva->cliente->email }}</p>
                @if($reserva->cliente->telefono)
                    <p><strong>Teléfono:</strong> {{ $reserva->cliente->telefono }}</p>
                @endif
                @if($reserva->cliente->documento)
                    <p><strong>Documento:</strong> {{ $reserva->cliente->documento }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Línea de Tiempo</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="mb-3">
                        <small class="text-muted">Creada</small>
                        <p class="mb-0">{{ $reserva->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($reserva->fecha_confirmacion)
                        <div class="mb-3">
                            <small class="text-muted">Confirmada</small>
                            <p class="mb-0">{{ $reserva->fecha_confirmacion->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    @if($reserva->fecha_cancelacion)
                        <div class="mb-3">
                            <small class="text-muted">Cancelada</small>
                            <p class="mb-0">{{ $reserva->fecha_cancelacion->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    <div>
                        <small class="text-muted">Última actualización</small>
                        <p class="mb-0">{{ $reserva->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD DE ACCIONES (Command Pattern) -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Acciones (Command Pattern)</h5>
            </div>
            <div class="card-body">
                @if($reserva->estado === 'pendiente')
                    <form method="POST" action="{{ route('admin.reservas.confirmar', $reserva->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm w-100 mb-2" onclick="return confirm('¿Confirmar esta reserva?')">
                            <i class="fas fa-check"></i> Confirmar Reserva
                        </button>
                    </form>
                @endif

                @if(in_array($reserva->estado, ['pendiente', 'confirmada']))
                    <button type="button" class="btn btn-danger btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#cancelarModal">
                        <i class="fas fa-times"></i> Cancelar Reserva
                    </button>

                    <button type="button" class="btn btn-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#cambiarHabitacionModal">
                        <i class="fas fa-exchange-alt"></i> Cambiar Habitación
                    </button>
                @endif

                <p class="text-muted small mb-0 mt-2">
                    <i class="fas fa-info-circle"></i> Las acciones usan el patrón Command para operaciones complejas
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar Reserva -->
<div class="modal fade" id="cancelarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reservas.cancelar', $reserva->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-times"></i> Cancelar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta acción cancelará la reserva y liberará la habitación.
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo de cancelación</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" placeholder="Opcional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger">Cancelar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Habitación -->
<div class="modal fade" id="cambiarHabitacionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reservas.cambiar-habitacion', $reserva->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Cambiar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Habitación actual:</strong> {{ $reserva->habitacion->numero }} ({{ $reserva->habitacion->tipoHabitacion->nombre }})</p>
                    <div class="mb-3">
                        <label for="nueva_habitacion_id" class="form-label">Nueva Habitación</label>
                        <select class="form-select" id="nueva_habitacion_id" name="nueva_habitacion_id" required>
                            <option value="">Seleccionar habitación...</option>
                            @foreach(\App\Models\Habitacion::where('estado', 'disponible')->where('id', '!=', $reserva->habitacion_id)->get() as $hab)
                                <option value="{{ $hab->id }}">
                                    #{{ $hab->numero }} - {{ $hab->tipoHabitacion->nombre }} - Piso {{ $hab->piso }} - ${{ number_format($hab->precio_base, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Cambiar Habitación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
