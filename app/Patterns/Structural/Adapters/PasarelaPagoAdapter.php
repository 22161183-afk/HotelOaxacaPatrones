<?php

namespace App\Patterns\Structural\Adapters;

/**
 * Patrón Adapter para integrar diferentes pasarelas de pago
 *
 * Adapta diferentes interfaces de pasarelas de pago a una interfaz común.
 */
interface PasarelaPagoInterface
{
    public function procesarPago(float $monto, array $datos): array;

    public function verificarEstado(string $transaccionId): string;

    public function reembolsar(string $transaccionId, float $monto): bool;
}

/**
 * Servicio externo de Stripe (simulado)
 */
class StripeService
{
    public function charge(int $amountInCents, array $cardData): object
    {
        // Simulación de respuesta de Stripe
        return (object) [
            'id' => 'ch_'.uniqid(),
            'status' => 'succeeded',
            'amount' => $amountInCents,
            'currency' => 'mxn',
        ];
    }

    public function getCharge(string $chargeId): object
    {
        return (object) ['status' => 'succeeded'];
    }

    public function refund(string $chargeId, int $amountInCents): object
    {
        return (object) ['status' => 'refunded'];
    }
}

/**
 * Adapter para Stripe
 */
class StripeAdapter implements PasarelaPagoInterface
{
    private StripeService $stripeService;

    public function __construct()
    {
        $this->stripeService = new StripeService;
    }

    public function procesarPago(float $monto, array $datos): array
    {
        // Convertir pesos a centavos
        $montoCentavos = (int) ($monto * 100);

        $resultado = $this->stripeService->charge($montoCentavos, [
            'card_number' => $datos['numero_tarjeta'] ?? '',
            'cvv' => $datos['cvv'] ?? '',
            'exp_date' => $datos['fecha_expiracion'] ?? '',
        ]);

        return [
            'exito' => $resultado->status === 'succeeded',
            'transaccion_id' => $resultado->id,
            'monto' => $monto,
            'estado' => $resultado->status,
        ];
    }

    public function verificarEstado(string $transaccionId): string
    {
        $charge = $this->stripeService->getCharge($transaccionId);

        return $charge->status;
    }

    public function reembolsar(string $transaccionId, float $monto): bool
    {
        $montoCentavos = (int) ($monto * 100);
        $resultado = $this->stripeService->refund($transaccionId, $montoCentavos);

        return $resultado->status === 'refunded';
    }
}

/**
 * Servicio externo de PayPal (simulado)
 */
class PayPalService
{
    public function createPayment(float $amount, array $data): array
    {
        return [
            'payment_id' => 'PAY-'.uniqid(),
            'state' => 'approved',
            'amount' => $amount,
        ];
    }

    public function getPaymentStatus(string $paymentId): string
    {
        return 'approved';
    }

    public function refundPayment(string $paymentId, float $amount): array
    {
        return ['state' => 'completed'];
    }
}

/**
 * Adapter para PayPal
 */
class PayPalAdapter implements PasarelaPagoInterface
{
    private PayPalService $paypalService;

    public function __construct()
    {
        $this->paypalService = new PayPalService;
    }

    public function procesarPago(float $monto, array $datos): array
    {
        $resultado = $this->paypalService->createPayment($monto, [
            'email' => $datos['email_paypal'] ?? '',
        ]);

        return [
            'exito' => $resultado['state'] === 'approved',
            'transaccion_id' => $resultado['payment_id'],
            'monto' => $monto,
            'estado' => $resultado['state'],
        ];
    }

    public function verificarEstado(string $transaccionId): string
    {
        return $this->paypalService->getPaymentStatus($transaccionId);
    }

    public function reembolsar(string $transaccionId, float $monto): bool
    {
        $resultado = $this->paypalService->refundPayment($transaccionId, $monto);

        return $resultado['state'] === 'completed';
    }
}

/**
 * Factory para obtener el adapter correcto
 */
class PasarelaPagoAdapterFactory
{
    public static function getAdapter(string $tipo): PasarelaPagoInterface
    {
        return match ($tipo) {
            'stripe' => new StripeAdapter,
            'paypal' => new PayPalAdapter,
            default => throw new \Exception("Pasarela de pago no soportada: {$tipo}")
        };
    }
}
