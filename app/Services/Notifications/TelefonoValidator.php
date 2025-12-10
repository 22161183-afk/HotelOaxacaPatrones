<?php

namespace App\Services\Notifications;

/**
 * Validador y Formateador de Teléfonos Mexicanos
 *
 * Formatos soportados:
 * - 10 dígitos: 9512342422
 * - Con código de país: +529512342422
 * - Con código de país sin +: 529512342422
 */
class TelefonoValidator
{
    /**
     * Valida si un número es un teléfono mexicano válido
     */
    public static function validar(string $telefono): bool
    {
        // Limpiar el número de espacios y caracteres especiales
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);

        // Patrones válidos para México
        $patrones = [
            '/^\+52[0-9]{10}$/',  // +52 + 10 dígitos
            '/^52[0-9]{10}$/',    // 52 + 10 dígitos
            '/^[0-9]{10}$/',      // 10 dígitos
        ];

        foreach ($patrones as $patron) {
            if (preg_match($patron, $telefono)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formatea un teléfono mexicano al formato internacional E.164
     * Retorna: +52XXXXXXXXXX (ejemplo: +529512342422)
     */
    public static function formatear(string $telefono): ?string
    {
        if (! self::validar($telefono)) {
            return null;
        }

        // Limpiar el número
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);

        // Si ya tiene +52, retornarlo
        if (str_starts_with($telefono, '+52')) {
            return $telefono;
        }

        // Si tiene 52 pero sin +, agregarlo
        if (str_starts_with($telefono, '52') && strlen($telefono) === 12) {
            return '+'.$telefono;
        }

        // Si tiene 10 dígitos, agregar +52
        if (strlen($telefono) === 10) {
            return '+52'.$telefono;
        }

        return null;
    }

    /**
     * Formatea para visualización
     * Retorna: (951) 234-2422
     */
    public static function formatearLegible(string $telefono): ?string
    {
        $formateado = self::formatear($telefono);

        if (! $formateado) {
            return null;
        }

        // Quitar +52
        $numero = substr($formateado, 3);

        // Formato: (XXX) XXX-XXXX
        return '('.substr($numero, 0, 3).') '.substr($numero, 3, 3).'-'.substr($numero, 6, 4);
    }

    /**
     * Valida que el teléfono sea de una región específica de México
     */
    public static function obtenerRegion(string $telefono): ?string
    {
        $formateado = self::formatear($telefono);

        if (! $formateado) {
            return null;
        }

        // Obtener los primeros 3 dígitos (código de área)
        $codigo = substr($formateado, 3, 3);

        // Códigos de área principales de México
        return match (true) {
            in_array($codigo, ['951', '952', '953', '954', '958', '971']) => 'Oaxaca',
            in_array($codigo, ['55', '56']) => 'Ciudad de México',
            in_array($codigo, ['33']) => 'Guadalajara',
            in_array($codigo, ['81']) => 'Monterrey',
            in_array($codigo, ['442']) => 'Querétaro',
            in_array($codigo, ['222']) => 'Puebla',
            in_array($codigo, ['998', '999']) => 'Cancún/Mérida',
            default => 'México',
        };
    }
}
