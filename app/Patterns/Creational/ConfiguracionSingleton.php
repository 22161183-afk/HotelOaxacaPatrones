<?php

namespace App\Patterns\Creational;

use App\Models\ConfiguracionHotel;

/**
 * Patrón Singleton para la configuración del hotel
 *
 * Garantiza una única instancia de configuración en toda la aplicación.
 * Esto es útil para evitar múltiples consultas a la BD y mantener consistencia.
 */
class ConfiguracionSingleton
{
    private static ?ConfiguracionSingleton $instance = null;

    private ?ConfiguracionHotel $configuracion = null;

    /**
     * Constructor privado para evitar instanciación directa
     */
    private function __construct()
    {
        $this->configuracion = ConfiguracionHotel::first();
    }

    /**
     * Evitar clonación del objeto
     */
    private function __clone() {}

    /**
     * Evitar deserialización
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Obtener la instancia única de configuración
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Obtener la configuración del hotel
     */
    public function getConfiguracion(): ?ConfiguracionHotel
    {
        return $this->configuracion;
    }

    /**
     * Actualizar la configuración
     */
    public function actualizarConfiguracion(array $datos): ConfiguracionHotel
    {
        $this->configuracion->update($datos);

        return $this->configuracion;
    }

    /**
     * Obtener hora de check-in
     */
    public function getHoraCheckin(): string
    {
        return $this->configuracion->hora_checkin;
    }

    /**
     * Obtener hora de check-out
     */
    public function getHoraCheckout(): string
    {
        return $this->configuracion->hora_checkout;
    }

    /**
     * Obtener porcentaje de impuesto
     */
    public function getImpuesto(): float
    {
        return $this->configuracion->impuesto;
    }

    /**
     * Obtener días de cancelación gratuita
     */
    public function getDiasCancelacion(): int
    {
        return $this->configuracion->dias_cancelacion;
    }
}
