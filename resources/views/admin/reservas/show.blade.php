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
                            @elseif($reserva->estado === 'en_proceso_reembolso')
                                <span class="badge bg-secondary"><i class="fas fa-exclamation-triangle"></i> En Proceso de Reembolso</span>
                            @elseif($reserva->estado === 'reembolsado')
                                <span class="badge bg-info"><i class="fas fa-undo"></i> Reembolsado</span>
                            @else
                                <span class="badge bg-secondary">{{ $reserva->estado_formateado }}</span>
                            @endif
                        </p>
                        @if(in_array($reserva->estado, ['en_proceso_reembolso', 'reembolsado']))
                            <p><strong><i class="fas fa-undo"></i> Monto de Reembolso:</strong> ${{ number_format($reserva->monto_reembolso, 2) }}</p>
                        @endif
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
                @php
                    $noches = $reserva->calcularNoches();
                    $precioHabitacion = $reserva->habitacion->precio_base * $noches;
                    $precioServicios = $reserva->calcularPrecioServicios();
                    $subtotal = $precioHabitacion + $precioServicios;
                    $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
                    $impuesto = $config->getImpuesto();
                    $montoIVA = $subtotal * ($impuesto / 100);
                    $totalCalculado = $subtotal + $montoIVA;
                @endphp
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td>Habitación:</td>
                                <td class="text-end">${{ number_format($reserva->habitacion->precio_base, 2) }} × {{ $noches }} {{ $noches == 1 ? 'noche' : 'noches' }}</td>
                                <td class="text-end"><strong>${{ number_format($precioHabitacion, 2) }}</strong></td>
                            </tr>
                            @if($precioServicios > 0)
                            <tr>
                                <td>Servicios adicionales:</td>
                                <td></td>
                                <td class="text-end"><strong>${{ number_format($precioServicios, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <td><strong>Subtotal (sin IVA):</strong></td>
                                <td></td>
                                <td class="text-end">${{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>IVA ({{ $impuesto }}%):</td>
                                <td class="text-muted small">Sobre ${{ number_format($subtotal, 2) }}</td>
                                <td class="text-end">${{ number_format($montoIVA, 2) }}</td>
                            </tr>
                            <tr class="table-active border-top">
                                <td><strong>TOTAL (IVA incluido):</strong></td>
                                <td></td>
                                <td class="text-end"><strong class="text-primary">${{ number_format($reserva->precio_total, 2) }}</strong></td>
                            </tr>
                        </table>

                        @if(abs($totalCalculado - $reserva->precio_total) > 0.01)
                            <div class="alert alert-warning alert-sm">
                                <small><i class="fas fa-exclamation-triangle"></i> Diferencia de cálculo: ${{ number_format(abs($totalCalculado - $reserva->precio_total), 2) }}</small>
                            </div>
                        @endif

                        {{-- Diferencia de precio por cambio de habitación --}}
                        @if($reserva->monto_diferencia && !$reserva->fecha_diferencia_pagada)
                            <div class="alert {{ $reserva->tipo_diferencia === 'pagar' ? 'alert-warning' : 'alert-info' }} mt-3">
                                <h6 class="mb-3">
                                    <i class="fas {{ $reserva->tipo_diferencia === 'pagar' ? 'fa-exclamation-circle' : 'fa-info-circle' }}"></i>
                                    Diferencia por Cambio de Habitación - Pendiente
                                </h6>
                                <p class="mb-2">
                                    <strong>Monto:</strong> ${{ number_format($reserva->monto_diferencia, 2) }}
                                </p>
                                <p class="mb-3">
                                    @if($reserva->tipo_diferencia === 'pagar')
                                        <span class="badge bg-warning">El cliente debe pagar esta diferencia</span>
                                        <br><small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle"></i> Cuando el cliente pague, el pago aparecerá "En Proceso" en la
                                            <a href="{{ route('admin.pagos.index') }}">vista de pagos</a> para que lo apruebes.
                                        </small>
                                    @else
                                        <span class="badge bg-info">Se debe reembolsar al cliente</span>
                                        <br><small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle"></i> Cuando el cliente acepte el reembolso, se marcará automáticamente como procesado.
                                        </small>
                                    @endif
                                </p>

                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.reservas.cancelar-diferencia', $reserva->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('¿Cancelar la gestión de esta diferencia?')">
                                            <i class="fas fa-times"></i> Cancelar Diferencia
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif($reserva->monto_diferencia && $reserva->fecha_diferencia_pagada)
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-check-circle"></i>
                                <strong>Diferencia {{ $reserva->tipo_diferencia === 'pagar' ? 'pagada' : 'reembolsada' }}:</strong>
                                ${{ number_format($reserva->monto_diferencia, 2) }}
                                el {{ $reserva->fecha_diferencia_pagada->format('d/m/Y H:i') }}
                            </div>
                        @endif
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
                    @if($reserva->fecha_solicitud_reembolso)
                        <div class="mb-3">
                            <small class="text-muted">Reembolso Solicitado</small>
                            <p class="mb-0">{{ $reserva->fecha_solicitud_reembolso->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    @if($reserva->fecha_reembolso)
                        <div class="mb-3">
                            <small class="text-muted">Reembolso Aceptado</small>
                            <p class="mb-0">{{ $reserva->fecha_reembolso->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    <div>
                        <small class="text-muted">Última actualización</small>
                        <p class="mb-0">{{ $reserva->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD DE ACCIONES -->
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Acciones</h5>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.reservas.cambiar-habitacion', $reserva->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Cambiar Habitación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $noches = $reserva->calcularNoches();
                        $precioActualHab = $reserva->habitacion->precio_base * $noches;
                        $servicios = $reserva->calcularPrecioServicios();
                        $subtotalActual = $precioActualHab + $servicios;
                        $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
                        $impuesto = $config->getImpuesto();
                        $montoImpuestoActual = $subtotalActual * ($impuesto / 100);
                    @endphp

                    <div class="alert alert-info">
                        <h6 class="mb-3"><strong>Precio Actual de la Reserva:</strong></h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Habitación #{{ $reserva->habitacion->numero }}:</td>
                                <td class="text-end">${{ number_format($reserva->habitacion->precio_base, 2) }} × {{ $noches }} {{ $noches == 1 ? 'noche' : 'noches' }} = <strong>${{ number_format($precioActualHab, 2) }}</strong></td>
                            </tr>
                            @if($servicios > 0)
                            <tr>
                                <td>Servicios adicionales:</td>
                                <td class="text-end"><strong>${{ number_format($servicios, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <td>Subtotal:</td>
                                <td class="text-end">${{ number_format($subtotalActual, 2) }}</td>
                            </tr>
                            <tr>
                                <td>IVA ({{ $impuesto }}%):</td>
                                <td class="text-end">${{ number_format($montoImpuestoActual, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Total:</strong></td>
                                <td class="text-end"><strong>${{ number_format($reserva->precio_total, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="mb-3">
                        <label for="nueva_habitacion_id" class="form-label">Nueva Habitación</label>
                        <select class="form-select" id="nueva_habitacion_id" name="nueva_habitacion_id" required onchange="calcularDiferencia()">
                            <option value="">Seleccionar habitación...</option>
                            @foreach(\App\Models\Habitacion::where('estado', 'disponible')->where('id', '!=', $reserva->habitacion_id)->get() as $hab)
                                <option value="{{ $hab->id }}" data-precio="{{ $hab->precio_base }}">
                                    #{{ $hab->numero }} - {{ $hab->tipoHabitacion->nombre }} - Piso {{ $hab->piso }} - ${{ number_format($hab->precio_base, 2) }}/noche
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="diferenciaPrecio" class="alert alert-secondary d-none">
                        <h6 class="mb-3"><strong>Nuevo Precio Estimado:</strong></h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Habitación seleccionada:</td>
                                <td class="text-end"><span id="nuevoPrecioHab"></span></td>
                            </tr>
                            @if($servicios > 0)
                            <tr>
                                <td>Servicios adicionales:</td>
                                <td class="text-end"><strong>${{ number_format($servicios, 2) }}</strong></td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <td>Subtotal:</td>
                                <td class="text-end"><span id="nuevoSubtotal"></span></td>
                            </tr>
                            <tr>
                                <td>IVA ({{ $impuesto }}%):</td>
                                <td class="text-end"><span id="nuevoImpuesto"></span></td>
                            </tr>
                            <tr class="border-top">
                                <td><strong>Nuevo Total:</strong></td>
                                <td class="text-end"><strong><span id="nuevoTotal"></span></strong></td>
                            </tr>
                            <tr class="border-top bg-warning-subtle">
                                <td><strong>Diferencia:</strong></td>
                                <td class="text-end"><strong><span id="montoDiferencia"></span></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Confirmar Cambio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calcularDiferencia() {
    const select = document.getElementById('nueva_habitacion_id');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption.value) {
        document.getElementById('diferenciaPrecio').classList.add('d-none');
        return;
    }

    const nuevoPrecioNoche = parseFloat(selectedOption.dataset.precio);
    const noches = {{ $noches }};
    const servicios = {{ $servicios }};
    const impuesto = {{ $impuesto }};

    const nuevoPrecioHab = nuevoPrecioNoche * noches;
    const nuevoSubtotal = nuevoPrecioHab + servicios;
    const nuevoImpuesto = nuevoSubtotal * (impuesto / 100);
    const nuevoTotal = nuevoSubtotal + nuevoImpuesto;

    const precioActual = {{ $reserva->precio_total }};
    const diferencia = nuevoTotal - precioActual;

    document.getElementById('nuevoPrecioHab').textContent = '$' + nuevoPrecioNoche.toFixed(2) + ' × ' + noches + ' = $' + nuevoPrecioHab.toFixed(2);
    document.getElementById('nuevoSubtotal').textContent = '$' + nuevoSubtotal.toFixed(2);
    document.getElementById('nuevoImpuesto').textContent = '$' + nuevoImpuesto.toFixed(2);
    document.getElementById('nuevoTotal').textContent = '$' + nuevoTotal.toFixed(2);

    let diferenciaTexto = '';
    if (Math.abs(diferencia) < 0.01) {
        diferenciaTexto = 'Sin cambio';
    } else if (diferencia > 0) {
        diferenciaTexto = '+$' + diferencia.toFixed(2) + ' (Cliente debe pagar)';
    } else {
        diferenciaTexto = '-$' + Math.abs(diferencia).toFixed(2) + ' (Se reembolsará al cliente)';
    }

    document.getElementById('montoDiferencia').textContent = diferenciaTexto;
    document.getElementById('diferenciaPrecio').classList.remove('d-none');
}
</script>

@endsection
