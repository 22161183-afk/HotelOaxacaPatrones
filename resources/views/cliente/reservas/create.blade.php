@extends('layouts.cliente')

@section('page-title', 'Crear Nueva Reserva')

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-plus"></i> Crear Nueva Reserva
                </h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('cliente.reservas.store') }}" method="POST" id="reservaForm">
                    @csrf

                    @if($habitacion)
                        <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">

                        <div class="alert alert-info mb-3">
                            <h5 class="mb-2">Habitación Seleccionada</h5>
                            <p class="mb-1"><strong>Habitación:</strong> {{ $habitacion->numero }}</p>
                            <p class="mb-1"><strong>Tipo:</strong> {{ $habitacion->tipoHabitacion->nombre }}</p>
                            <p class="mb-1"><strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas</p>
                            <p class="mb-1"><strong>Precio:</strong> ${{ number_format($habitacion->precio_base, 2) }}/noche</p>
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="habitacion_id" class="form-label">Seleccionar Habitación <span class="text-danger">*</span></label>
                            <a href="{{ route('cliente.habitaciones.index') }}" class="btn btn-sm btn-primary float-end">
                                <i class="fas fa-search"></i> Ver Habitaciones Disponibles
                            </a>
                            <select
                                class="form-select @error('habitacion_id') is-invalid @enderror"
                                id="habitacion_id"
                                name="habitacion_id"
                                required>
                                <option value="">Seleccione una habitación</option>
                            </select>
                            @error('habitacion_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Por favor, seleccione una habitación desde el listado de habitaciones disponibles</small>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-control @error('fecha_inicio') is-invalid @enderror"
                                    id="fecha_inicio"
                                    name="fecha_inicio"
                                    value="{{ old('fecha_inicio') }}"
                                    min="{{ date('Y-m-d') }}"
                                    required
                                    onchange="calcularPrecio()">
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Hora de entrada: 15:00 hrs</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Salida <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-control @error('fecha_fin') is-invalid @enderror"
                                    id="fecha_fin"
                                    name="fecha_fin"
                                    value="{{ old('fecha_fin') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    required
                                    onchange="calcularPrecio()">
                                @error('fecha_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Hora de salida: 12:00 hrs</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="numero_huespedes" class="form-label">Número de Huéspedes <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    class="form-control @error('numero_huespedes') is-invalid @enderror"
                                    id="numero_huespedes"
                                    name="numero_huespedes"
                                    value="{{ old('numero_huespedes', 1) }}"
                                    min="1"
                                    @if($habitacion) max="{{ $habitacion->capacidad }}" @endif
                                    required>
                                @error('numero_huespedes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($habitacion)
                                    <small class="form-text text-muted">Capacidad máxima: {{ $habitacion->capacidad }} personas</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SERVICIOS ADICIONALES -->
                    @if(isset($serviciosDisponibles) && $serviciosDisponibles->count() > 0)
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-concierge-bell"></i> <strong>Servicios Adicionales (Opcional)</strong></label>
                            <p class="text-muted small">Seleccione los servicios que desea agregar a su reserva:</p>

                            <div class="row">
                                @foreach($serviciosDisponibles->groupBy('tipo') as $tipo => $servicios)
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">{{ ucfirst($tipo) }}</h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach($servicios as $servicio)
                                                    <div class="form-check mb-2">
                                                        <input
                                                            class="form-check-input servicio-check"
                                                            type="checkbox"
                                                            name="servicios[]"
                                                            value="{{ $servicio->id }}"
                                                            id="servicio_{{ $servicio->id }}"
                                                            data-precio="{{ $servicio->precio }}"
                                                            onchange="calcularPrecio()">
                                                        <label class="form-check-label" for="servicio_{{ $servicio->id }}">
                                                            <strong>{{ $servicio->nombre }}</strong>
                                                            <span class="text-success">- ${{ number_format($servicio->precio, 2) }}</span>
                                                            <br>
                                                            <small class="text-muted">{{ $servicio->descripcion }}</small>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($habitacion)
                        <div class="alert alert-secondary">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Noches:</strong> <span id="totalNoches">-</span></p>
                                    <p class="mb-1"><strong>Precio por noche:</strong> ${{ number_format($habitacion->precio_base, 2) }}</p>
                                    <p class="mb-1"><strong>Servicios adicionales:</strong> $<span id="precioServicios">0.00</span></p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <p class="mb-0"><strong>TOTAL ESTIMADO:</strong></p>
                                    <h4 class="text-success mb-0">$<span id="precioTotal">0.00</span></h4>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Confirmar Reserva
                        </button>
                        <a href="{{ route('cliente.habitaciones.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Importante</h5>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-clock text-primary"></i> Horarios</h6>
                <p class="small mb-3">
                    <strong>Entrada:</strong> 15:00 hrs<br>
                    <strong>Salida:</strong> 12:00 hrs
                </p>

                <h6><i class="fas fa-credit-card text-success"></i> Pagos</h6>
                <p class="small mb-3">
                    Aceptamos tarjetas de crédito/débito, transferencias y efectivo.
                </p>

                <h6><i class="fas fa-ban text-danger"></i> Políticas</h6>
                <p class="small mb-3">
                    Cancelación gratuita hasta 48 hrs antes de la fecha de entrada.
                </p>

                <h6><i class="fas fa-shield-alt text-warning"></i> Estado de Reserva</h6>
                <p class="small mb-0">
                    Su reserva quedará en estado <strong>"Pendiente"</strong> hasta que se confirme el pago.
                </p>
            </div>
        </div>
    </div>
</div>

@if($habitacion)
<script>
    const precioPorNoche = {{ $habitacion->precio_base }};

    function calcularPrecio() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        if (fechaInicio && fechaFin) {
            const inicio = new Date(fechaInicio);
            const fin = new Date(fechaFin);
            const diferencia = fin - inicio;
            const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));

            if (dias > 0) {
                const precioHabitacion = dias * precioPorNoche;

                // Calcular precio de servicios
                let precioServicios = 0;
                document.querySelectorAll('.servicio-check:checked').forEach(checkbox => {
                    precioServicios += parseFloat(checkbox.dataset.precio);
                });

                const total = precioHabitacion + precioServicios;

                document.getElementById('totalNoches').textContent = dias;
                document.getElementById('precioServicios').textContent = precioServicios.toFixed(2);
                document.getElementById('precioTotal').textContent = total.toFixed(2);
            } else {
                document.getElementById('totalNoches').textContent = '-';
                document.getElementById('precioServicios').textContent = '0.00';
                document.getElementById('precioTotal').textContent = '0.00';
            }
        }
    }

    // Calcular al cargar si hay fechas
    document.addEventListener('DOMContentLoaded', calcularPrecio);
</script>
@endif

@endsection
