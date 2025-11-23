<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Reserva;

class NotificacionService
{
    public function crearNotificacion($reservaId, $tipo, $datos)
    {
        $reserva = Reserva::with('cliente.usuario')->findOrFail($reservaId);

        $mensajes = [
            'reserva_creada' => [
                'asunto' => 'Reserva creada - Hotel Oaxaca',
                'mensaje' => "Tu reserva ha sido creada exitosamente para la habitación {$reserva->habitacion->numero} del {$reserva->fecha_inicio->format('d/m/Y')} al {$reserva->fecha_fin->format('d/m/Y')}",
            ],
            'reserva_confirmada' => [
                'asunto' => 'Reserva confirmada - Hotel Oaxaca',
                'mensaje' => '¡Tu reserva ha sido confirmada! Total a pagar: $'.$reserva->precio_total,
            ],
            'reserva_cancelada' => [
                'asunto' => 'Reserva cancelada - Hotel Oaxaca',
                'mensaje' => 'Tu reserva ha sido cancelada. '.($datos['razon'] ?? ''),
            ],
            'pago_recibido' => [
                'asunto' => 'Pago recibido - Hotel Oaxaca',
                'mensaje' => 'Hemos recibido tu pago de $'.$datos['monto'].'. Transacción: '.$datos['transaccion_id'],
            ],
        ];

        $notif = $mensajes[$tipo] ?? null;

        if (! $notif) {
            throw new \Exception("Tipo de notificación no válida: $tipo");
        }

        return Notificacion::create([
            'reserva_id' => $reservaId,
            'tipo' => $tipo,
            'canal' => $datos['canal'] ?? 'correo',
            'destinatario' => $reserva->cliente->email ?? 'cliente@example.com',
            'asunto' => $notif['asunto'],
            'mensaje' => $notif['mensaje'],
            'estado' => 'pendiente',
        ]);
    }

    public function enviarNotificacion($notificacionId)
    {
        $notif = Notificacion::findOrFail($notificacionId);

        // Simular envío según canal
        match ($notif->canal) {
            'correo' => $this->enviarCorreo($notif),
            'sms' => $this->enviarSMS($notif),
            'whatsapp' => $this->enviarWhatsApp($notif),
            default => throw new \Exception("Canal no soportado: {$notif->canal}")
        };

        $notif->update(['estado' => 'enviada']);

        return $notif;
    }

    private function enviarCorreo($notif)
    {
        \Log::info("📧 Correo enviado a {$notif->destinatario}: {$notif->asunto}");
    }

    private function enviarSMS($notif)
    {
        \Log::info("📱 SMS enviado a {$notif->destinatario}: {$notif->mensaje}");
    }

    private function enviarWhatsApp($notif)
    {
        \Log::info("💬 WhatsApp enviado a {$notif->destinatario}: {$notif->mensaje}");
    }

    public function obtenerNotificacionesPorReserva($reservaId)
    {
        return Notificacion::where('reserva_id', $reservaId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
