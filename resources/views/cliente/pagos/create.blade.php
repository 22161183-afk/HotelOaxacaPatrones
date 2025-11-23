@extends('layouts.cliente')

@section('page-title', 'Realizar Pago')

@section('content')

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="fas fa-credit-card"></i> Realizar Pago de Reserva
                </h4>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                <!-- Información de la Reserva -->
                <div class="alert alert-info mb-4">
                    <h5 class="mb-3">Detalles de la Reserva</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Reserva #:</strong> {{ $reserva->id }}</p>
                            <p class="mb-1"><strong>Habitación:</strong> {{ $reserva->habitacion->numero }}</p>
                            <p class="mb-1"><strong>Tipo:</strong> {{ $reserva->habitacion->tipoHabitacion->nombre }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Fecha de Entrada:</strong> {{ $reserva->fecha_inicio->format('d/m/Y') }}</p>
                            <p class="mb-1"><strong>Fecha de Salida:</strong> {{ $reserva->fecha_fin->format('d/m/Y') }}</p>
                            <p class="mb-1"><strong>Noches:</strong> {{ abs($reserva->calcularNoches()) }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="text-end">
                        <h4 class="text-success mb-0">
                            <strong>Total a Pagar:</strong> ${{ number_format($reserva->precio_total, 2) }}
                        </h4>
                    </div>
                </div>

                <!-- Formulario de Pago -->
                <form action="{{ route('cliente.pagos.store') }}" method="POST" id="pagoForm">
                    @csrf
                    <input type="hidden" name="reserva_id" value="{{ $reserva->id }}">

                    <div class="mb-4">
                        <label class="form-label"><strong>Seleccione Método de Pago</strong> <span class="text-danger">*</span></label>

                        <div class="row g-3">
                            @foreach($metodosPago as $metodo)
                                <div class="col-md-6">
                                    <div class="card metodo-pago-card" style="cursor: pointer;">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="radio"
                                                    name="metodo_pago_id"
                                                    id="metodo_{{ $metodo->id }}"
                                                    value="{{ $metodo->id }}"
                                                    required>
                                                <label class="form-check-label w-100" for="metodo_{{ $metodo->id }}" style="cursor: pointer;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-1">
                                                                @if($metodo->nombre === 'Tarjeta de Crédito')
                                                                    <i class="fas fa-credit-card text-primary"></i>
                                                                @elseif($metodo->nombre === 'Tarjeta de Débito')
                                                                    <i class="fas fa-credit-card text-info"></i>
                                                                @elseif($metodo->nombre === 'PayPal')
                                                                    <i class="fab fa-paypal text-primary"></i>
                                                                @elseif($metodo->nombre === 'Transferencia Bancaria')
                                                                    <i class="fas fa-university text-success"></i>
                                                                @else
                                                                    <i class="fas fa-money-bill-wave text-success"></i>
                                                                @endif
                                                                {{ $metodo->nombre }}
                                                            </h6>
                                                            @if($metodo->descripcion)
                                                                <small class="text-muted">{{ $metodo->descripcion }}</small>
                                                            @endif
                                                        </div>
                                                        @if($metodo->comision > 0)
                                                            <span class="badge bg-warning">Comisión: {{ $metodo->comision }}%</span>
                                                        @else
                                                            <span class="badge bg-success">Sin comisión</span>
                                                        @endif
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('metodo_pago_id')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> Este es un pago simulado con fines demostrativos.
                        En producción, se integrará con pasarelas de pago reales.
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success btn-lg me-2">
                            <i class="fas fa-check-circle"></i> Confirmar Pago
                        </button>
                        <a href="{{ route('cliente.reservas.index') }}" class="btn btn-secondary btn-lg">
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
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Pago Seguro</h5>
            </div>
            <div class="card-body">
                <p class="small">
                    <i class="fas fa-lock text-success"></i> Tu pago está protegido con encriptación SSL
                </p>
                <p class="small">
                    <i class="fas fa-check text-success"></i> Procesamiento seguro de datos
                </p>
                <p class="small">
                    <i class="fas fa-shield-alt text-success"></i> Garantía de satisfacción
                </p>
                <hr>
                <p class="small mb-0">
                    <strong>¿Necesitas ayuda?</strong><br>
                    <i class="fas fa-phone"></i> +52 951 123 4567<br>
                    <i class="fas fa-envelope"></i> pagos@hoteloaxaca.com
                </p>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Resumen</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Habitación ({{ abs($reserva->calcularNoches()) }} noches):</td>
                        <td class="text-end">${{ number_format($reserva->precio_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Servicios adicionales:</td>
                        <td class="text-end">${{ number_format($reserva->precio_servicios ?? 0, 2) }}</td>
                    </tr>
                    <tr class="fw-bold border-top">
                        <td>Total:</td>
                        <td class="text-end text-success">${{ number_format($reserva->precio_total + ($reserva->precio_servicios ?? 0), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.metodo-pago-card {
    transition: all 0.3s;
    border: 2px solid #e0e0e0;
}

.metodo-pago-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102,126,234,0.2);
    transform: translateY(-2px);
}

.form-check-input:checked ~ .form-check-label {
    font-weight: bold;
}

.form-check-input:checked ~ .form-check-label .card {
    border-color: #28a745;
    background-color: #f0f9f0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hacer click en la card para seleccionar el radio button
    document.querySelectorAll('.metodo-pago-card').forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;

                // Remover estilo de todas las cards
                document.querySelectorAll('.metodo-pago-card').forEach(c => {
                    c.style.borderColor = '#e0e0e0';
                    c.style.backgroundColor = 'white';
                });

                // Agregar estilo a la card seleccionada
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#f0f9f0';
            }
        });
    });
});
</script>

@endsection
