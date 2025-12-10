@extends('layouts.cliente')

@section('page-title', 'Editar Reserva #' . $reserva->id)

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-edit"></i> Editar Reserva #{{ $reserva->id }}
                </h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Nota:</strong> Al editar tu reserva, puedes cambiar las fechas, número de huéspedes y servicios adicionales. La habitación se mantendrá reservada.
                </div>

                <form action="{{ route('cliente.reservas.update', $reserva->id) }}" method="POST" id="reservaForm">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info mb-3">
                        <h5 class="mb-2">Habitación Reservada</h5>
                        <p class="mb-1"><strong>Habitación:</strong> {{ $habitacion->numero }}</p>
                        <p class="mb-1"><strong>Tipo:</strong> {{ $habitacion->tipoHabitacion->nombre }}</p>
                        <p class="mb-1"><strong>Capacidad:</strong> {{ $habitacion->capacidad }} personas</p>
                        <p class="mb-1"><strong>Precio:</strong> ${{ number_format($habitacion->precio_base, 2) }}/noche</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Entrada <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-control @error('fecha_inicio') is-invalid @enderror"
                                    id="fecha_inicio"
                                    name="fecha_inicio"
                                    value="{{ old('fecha_inicio', $reserva->fecha_inicio->format('Y-m-d')) }}"
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
                                    value="{{ old('fecha_fin', $reserva->fecha_fin->format('Y-m-d')) }}"
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
                                    value="{{ old('numero_huespedes', $reserva->numero_huespedes) }}"
                                    min="1"
                                    max="{{ $habitacion->capacidad }}"
                                    required>
                                @error('numero_huespedes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Capacidad máxima: {{ $habitacion->capacidad }} personas</small>
                            </div>
                        </div>
                    </div>

                    <!-- SERVICIOS ADICIONALES -->
                    @if(isset($serviciosDisponibles) && $serviciosDisponibles->count() > 0)
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-concierge-bell"></i> <strong>Servicios Adicionales (Opcional)</strong></label>
                            <p class="text-muted small">Seleccione los servicios que desea agregar a su reserva:</p>

                            @php
                                $serviciosReserva = $reserva->servicios->pluck('id')->toArray();
                            @endphp

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
                                                            onchange="calcularPrecio()"
                                                            {{ in_array($servicio->id, old('servicios', $serviciosReserva)) ? 'checked' : '' }}>
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

                    <div class="alert alert-secondary">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Noches:</strong> <span id="totalNoches">-</span></p>
                                <p class="mb-1"><strong>Precio por noche:</strong> ${{ number_format($habitacion->precio_base, 2) }}</p>
                                <p class="mb-1"><strong>Servicios adicionales:</strong> $<span id="precioServicios">0.00</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-0"><strong>NUEVO TOTAL ESTIMADO:</strong></p>
                                <h4 class="text-success mb-0">$<span id="precioTotal">0.00</span></h4>
                                <small class="text-muted">Total anterior: ${{ number_format($reserva->precio_total, 2) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="{{ route('cliente.reservas.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar Cambios
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

                <h6><i class="fas fa-edit text-primary"></i> Edición de Reserva</h6>
                <p class="small mb-3">
                    Puedes editar tu reserva siempre y cuando esté en estado <strong>"Pendiente"</strong>. Una vez confirmada con el pago, no podrá ser editada.
                </p>

                <h6><i class="fas fa-ban text-danger"></i> Políticas</h6>
                <p class="small mb-3">
                    Cancelación gratuita hasta 48 hrs antes de la fecha de entrada.
                </p>

                <h6><i class="fas fa-shield-alt text-warning"></i> Estado de Reserva</h6>
                <p class="small mb-0">
                    Tu reserva se mantendrá en estado <strong>"Pendiente"</strong> y la habitación seguirá reservada a tu nombre.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script>
    const precioPorNoche = {{ $habitacion->precio_base }};

    function calcularPrecio() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        console.log('Calculando precio - Inicio:', fechaInicio, 'Fin:', fechaFin);

        if (fechaInicio && fechaFin) {
            const inicio = new Date(fechaInicio + 'T00:00:00');
            const fin = new Date(fechaFin + 'T00:00:00');
            const diferencia = fin - inicio;
            const dias = Math.ceil(diferencia / (1000 * 60 * 60 * 24));

            console.log('Días calculados:', dias);

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

                console.log('Precio calculado - Total:', total);
            } else {
                document.getElementById('totalNoches').textContent = '-';
                document.getElementById('precioServicios').textContent = '0.00';
                document.getElementById('precioTotal').textContent = '0.00';
            }
        }
    }

    // Configurar Flatpickr en español
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando Flatpickr...');

        // Fecha de inicio
        const fechaInicioInput = document.getElementById('fecha_inicio');
        const fpInicio = flatpickr(fechaInicioInput, {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: "today",
            defaultDate: fechaInicioInput.value,
            onChange: function(selectedDates, dateStr, instance) {
                console.log('Fecha inicio cambiada:', dateStr);
                calcularPrecio();

                // Actualizar fecha mínima de fecha_fin
                if (selectedDates.length > 0) {
                    const minDateFin = new Date(selectedDates[0]);
                    minDateFin.setDate(minDateFin.getDate() + 1);
                    fpFin.set('minDate', minDateFin);
                }
            }
        });

        // Fecha de fin
        const fechaFinInput = document.getElementById('fecha_fin');
        const fechaInicioValue = fechaInicioInput.value;
        let minDateFin = new Date();
        minDateFin.setDate(minDateFin.getDate() + 1);

        if (fechaInicioValue) {
            const inicioDate = new Date(fechaInicioValue + 'T00:00:00');
            minDateFin = new Date(inicioDate);
            minDateFin.setDate(minDateFin.getDate() + 1);
        }

        const fpFin = flatpickr(fechaFinInput, {
            locale: "es",
            dateFormat: "Y-m-d",
            minDate: minDateFin,
            defaultDate: fechaFinInput.value,
            onChange: function(selectedDates, dateStr, instance) {
                console.log('Fecha fin cambiada:', dateStr);
                calcularPrecio();
            }
        });

        // Agregar eventos a los checkboxes de servicios
        document.querySelectorAll('.servicio-check').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                console.log('Servicio cambiado');
                calcularPrecio();
            });
        });

        // Calcular al cargar con valores existentes
        console.log('Calculando precio inicial...');
        calcularPrecio();
    });
</script>

@endsection
