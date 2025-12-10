<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Log;

/**
 * Servicio de Envío de WhatsApp usando Twilio WhatsApp API
 *
 * Para activar WhatsApp con Twilio:
 * 1. Configurar sandbox de Twilio WhatsApp
 * 2. Agregar en .env las mismas credenciales de Twilio
 * 3. El número "from" debe ser: whatsapp:+14155238886 (sandbox) o tu número verificado
 */
class WhatsAppService
{
    /**
     * Envía un mensaje de WhatsApp
     */
    public static function enviar(string $telefono, string $mensaje): array
    {
        // Validar y formatear el teléfono
        if (! TelefonoValidator::validar($telefono)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido. Debe ser un número mexicano de 10 dígitos (ej: 9512342422)',
            ];
        }

        $telefonoFormateado = TelefonoValidator::formatear($telefono);

        // Verificar si Twilio está configurado
        if (self::isTwilioConfigured()) {
            return self::enviarConTwilio($telefonoFormateado, $mensaje);
        }

        // Modo simulación
        return self::simularEnvio($telefonoFormateado, $mensaje);
    }

    /**
     * Verifica si Twilio está configurado
     */
    private static function isTwilioConfigured(): bool
    {
        return config('services.twilio.sid') !== null &&
               config('services.twilio.token') !== null &&
               config('services.twilio.whatsapp_from') !== null;
    }

    /**
     * Envía WhatsApp real usando Twilio
     */
    private static function enviarConTwilio(string $telefono, string $mensaje): array
    {
        try {
            // Verificar si el paquete de Twilio está instalado
            if (! class_exists(\Twilio\Rest\Client::class)) {
                Log::warning('Twilio SDK no está instalado. Ejecuta: composer require twilio/sdk');

                return self::simularEnvio($telefono, $mensaje);
            }

            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.whatsapp_from', 'whatsapp:+14155238886'); // Sandbox por defecto

            $twilio = new \Twilio\Rest\Client($sid, $token);

            $message = $twilio->messages->create(
                'whatsapp:'.$telefono, // Formato: whatsapp:+529512342422
                [
                    'from' => $from,
                    'body' => $mensaje,
                ]
            );

            Log::info("💬 WhatsApp enviado vía Twilio a {$telefono}: {$mensaje}", [
                'sid' => $message->sid,
                'status' => $message->status,
            ]);

            return [
                'success' => true,
                'message' => 'WhatsApp enviado correctamente',
                'sid' => $message->sid,
                'telefono' => $telefono,
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp con Twilio: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error al enviar WhatsApp: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Simula el envío de WhatsApp (para desarrollo)
     */
    private static function simularEnvio(string $telefono, string $mensaje): array
    {
        Log::info("💬 [SIMULADO] WhatsApp enviado a {$telefono}: {$mensaje}");

        return [
            'success' => true,
            'message' => 'WhatsApp simulado correctamente (modo desarrollo)',
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'simulado' => true,
        ];
    }

    /**
     * Envía un WhatsApp con template y emojis
     */
    public static function enviarTemplate(string $telefono, string $template, array $variables = []): array
    {
        $mensaje = self::renderTemplate($template, $variables);

        return self::enviar($telefono, $mensaje);
    }

    /**
     * Renderiza un template de mensaje
     */
    private static function renderTemplate(string $template, array $variables): string
    {
        $mensaje = $template;

        foreach ($variables as $key => $value) {
            $mensaje = str_replace("{{{$key}}}", $value, $mensaje);
        }

        return $mensaje;
    }

    /**
     * Envía un mensaje con imagen (requiere URL pública)
     */
    public static function enviarConImagen(string $telefono, string $mensaje, string $urlImagen): array
    {
        // Validar teléfono
        if (! TelefonoValidator::validar($telefono)) {
            return [
                'success' => false,
                'error' => 'Número de teléfono inválido',
            ];
        }

        $telefonoFormateado = TelefonoValidator::formatear($telefono);

        if (! self::isTwilioConfigured()) {
            Log::info("💬 [SIMULADO] WhatsApp con imagen enviado a {$telefonoFormateado}");

            return [
                'success' => true,
                'message' => 'WhatsApp con imagen simulado',
                'simulado' => true,
            ];
        }

        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.whatsapp_from', 'whatsapp:+14155238886');

            $twilio = new \Twilio\Rest\Client($sid, $token);

            $message = $twilio->messages->create(
                'whatsapp:'.$telefonoFormateado,
                [
                    'from' => $from,
                    'body' => $mensaje,
                    'mediaUrl' => [$urlImagen],
                ]
            );

            return [
                'success' => true,
                'message' => 'WhatsApp con imagen enviado correctamente',
                'sid' => $message->sid,
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp con imagen: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
