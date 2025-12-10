<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #6b7280;
        }
        .value {
            color: #111827;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏨 Hotel Reservas</h1>
        <p>{{ $datos['mensaje'] ?? 'Notificación de Reserva' }}</p>
    </div>

    <div class="content">
        <h2>Hola {{ $reserva->cliente->nombre }},</h2>

        @if($evento === 'reserva_creada')
            <p>¡Gracias por tu reserva! Aquí están los detalles:</p>
        @elseif($evento === 'reserva_confirmada')
            <p>¡Excelente noticia! Tu reserva ha sido confirmada.</p>
        @elseif($evento === 'reserva_cancelada')
            <p>Tu reserva ha sido cancelada.</p>
            @if(isset($datos['motivo']))
                <p><strong>Motivo:</strong> {{ $datos['motivo'] }}</p>
            @endif
        @elseif($evento === 'reserva_modificada')
            <p>Tu reserva ha sido modificada.</p>
        @elseif($evento === 'pago_procesado')
            <p>¡Pago recibido exitosamente!</p>
        @elseif($evento === 'proxima_llegada')
            <p>Este es un recordatorio de que tu llegada es mañana. ¡Te esperamos!</p>
        @endif

        <div class="details">
            <h3>Detalles de la Reserva</h3>
            <div class="detail-row">
                <span class="label">Número de Reserva:</span>
                <span class="value">#{{ $reserva->id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Habitación:</span>
                <span class="value">{{ $reserva->habitacion->numero }} - {{ $reserva->habitacion->tipoHabitacion->nombre ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-in:</span>
                <span class="value">{{ $reserva->fecha_inicio->format('d/m/Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Check-out:</span>
                <span class="value">{{ $reserva->fecha_fin->format('d/m/Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Número de Huéspedes:</span>
                <span class="value">{{ $reserva->numero_huespedes }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Estado:</span>
                <span class="value">{{ ucfirst($reserva->estado) }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Total:</span>
                <span class="value">${{ number_format($reserva->precio_total, 2) }}</span>
            </div>
        </div>

        @if($reserva->servicios->count() > 0)
            <div class="details">
                <h3>Servicios Incluidos</h3>
                @foreach($reserva->servicios as $servicio)
                    <div class="detail-row">
                        <span class="label">{{ $servicio->nombre }}</span>
                        <span class="value">${{ number_format($servicio->pivot->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($datos['pago']))
            <div class="details">
                <h3>Información de Pago</h3>
                <div class="detail-row">
                    <span class="label">Monto:</span>
                    <span class="value">${{ number_format($datos['pago']->monto, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Método:</span>
                    <span class="value">{{ $datos['pago']->metodo_pago }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Referencia:</span>
                    <span class="value">{{ $datos['pago']->referencia_pago }}</span>
                </div>
            </div>
        @endif

        @if($evento === 'proxima_llegada')
            <p><strong>Horario de Check-in:</strong> 3:00 PM</p>
            <p><strong>Horario de Check-out:</strong> 12:00 PM</p>
        @endif

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
    </div>

    <div class="footer">
        <p>Hotel Reservas</p>
        <p>Email: {{ config('mail.from.address') }}</p>
        <p>Este es un correo automático, por favor no responder.</p>
    </div>
</body>
</html>
