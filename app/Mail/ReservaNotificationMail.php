<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Reserva $reserva,
        public string $evento,
        public array $datos
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $asunto = match ($this->evento) {
            'reserva_creada' => 'Confirmación de Reserva',
            'reserva_confirmada' => 'Reserva Confirmada - Pago Recibido',
            'reserva_cancelada' => 'Reserva Cancelada',
            'reserva_modificada' => 'Reserva Modificada',
            'pago_procesado' => 'Pago Procesado',
            'proxima_llegada' => 'Recordatorio: Su llegada es mañana',
            default => 'Notificación de Reserva',
        };

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $asunto.' - Reserva #'.$this->reserva->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reserva-notification',
            with: [
                'reserva' => $this->reserva,
                'evento' => $this->evento,
                'datos' => $this->datos,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
