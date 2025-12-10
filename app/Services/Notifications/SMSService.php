<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Log;

/**
 * Servicio de Envío de SMS usando Twilio
 *
 * Para activar Twilio:
 * 1. Instalar: composer require twilio/sdk
 * 2. Configurar en .env:
 *    TWILIO_SID=tu_account_sid
 *    TWILIO_TOKEN=tu_auth_token
 *    TWILIO_FROM=+521234567890
 */
class SMSService
{
    /**
     * Envía un SMS a un número de teléfono mexicano
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

        // Modo simulación si Twilio no está configurado
        return self::simularEnvio($telefonoFormateado, $mensaje);
    }

    /**
     * Verifica si Twilio está configurado
     */
    private static function isTwilioConfigured(): bool
    {
        return config('services.twilio.sid') !== null &&
               config('services.twilio.token') !== null &&
               config('services.twilio.from') !== null;
    }

    /**
     * Envía SMS real usando Twilio
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
            $from = config('services.twilio.from');

            $twilio = new \Twilio\Rest\Client($sid, $token);

            $message = $twilio->messages->create(
                $telefono,
                [
                    'from' => $from,
                    'body' => $mensaje,
                ]
            );

            Log::info("📱 SMS enviado vía Twilio a {$telefono}: {$mensaje}", [
                'sid' => $message->sid,
                'status' => $message->status,
            ]);

            return [
                'success' => true,
                'message' => 'SMS enviado correctamente',
                'sid' => $message->sid,
                'telefono' => $telefono,
            ];
        } catch (\Exception $e) {
            Log::error('Error al enviar SMS con Twilio: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Error al enviar SMS: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Simula el envío de SMS (para desarrollo)
     */
    private static function simularEnvio(string $telefono, string $mensaje): array
    {
        Log::info("📱 [SIMULADO] SMS enviado a {$telefono}: {$mensaje}");

        return [
            'success' => true,
            'message' => 'SMS simulado correctamente (modo desarrollo)',
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'simulado' => true,
        ];
    }

    /**
     * Envía un SMS con template
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
}
