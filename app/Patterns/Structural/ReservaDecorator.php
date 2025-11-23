<?php

namespace App\Patterns\Structural;

use App\Models\Reserva;
use App\Models\Servicio;

/**
 * Patrón Decorator para agregar servicios a las reservas
 *
 * Permite agregar funcionalidades (servicios) a las reservas de forma dinámica.
 */
interface ReservaComponente
{
    public function getPrecioTotal(): float;

    public function getDescripcion(): string;
}

/**
 * Componente concreto - Reserva base
 */
class ReservaBase implements ReservaComponente
{
    protected Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function getPrecioTotal(): float
    {
        return $this->reserva->precio_total;
    }

    public function getDescripcion(): string
    {
        return "Reserva #{$this->reserva->id} - Habitación {$this->reserva->habitacion->numero}";
    }

    public function getReserva(): Reserva
    {
        return $this->reserva;
    }
}

/**
 * Decorador abstracto
 */
abstract class ReservaDecorator implements ReservaComponente
{
    protected ReservaComponente $reserva;

    public function __construct(ReservaComponente $reserva)
    {
        $this->reserva = $reserva;
    }

    public function getPrecioTotal(): float
    {
        return $this->reserva->getPrecioTotal();
    }

    public function getDescripcion(): string
    {
        return $this->reserva->getDescripcion();
    }
}

/**
 * Decorador concreto - Desayuno
 */
class DesayunoDecorator extends ReservaDecorator
{
    private float $precioDesayuno = 250.00;

    public function getPrecioTotal(): float
    {
        return parent::getPrecioTotal() + $this->precioDesayuno;
    }

    public function getDescripcion(): string
    {
        return parent::getDescripcion().' + Desayuno Buffet';
    }
}

/**
 * Decorador concreto - Spa
 */
class SpaDecorator extends ReservaDecorator
{
    private float $precioSpa = 1200.00;

    public function getPrecioTotal(): float
    {
        return parent::getPrecioTotal() + $this->precioSpa;
    }

    public function getDescripcion(): string
    {
        return parent::getDescripcion().' + Paquete Spa Completo';
    }
}

/**
 * Decorador concreto - Traslado
 */
class TrasladoDecorator extends ReservaDecorator
{
    private float $precioTraslado = 400.00;

    public function getPrecioTotal(): float
    {
        return parent::getPrecioTotal() + $this->precioTraslado;
    }

    public function getDescripcion(): string
    {
        return parent::getDescripcion().' + Traslado Aeropuerto';
    }
}

/**
 * Decorador concreto - Servicio personalizado
 */
class ServicioPersonalizadoDecorator extends ReservaDecorator
{
    private Servicio $servicio;

    private int $cantidad;

    public function __construct(ReservaComponente $reserva, Servicio $servicio, int $cantidad = 1)
    {
        parent::__construct($reserva);
        $this->servicio = $servicio;
        $this->cantidad = $cantidad;
    }

    public function getPrecioTotal(): float
    {
        return parent::getPrecioTotal() + ($this->servicio->precio * $this->cantidad);
    }

    public function getDescripcion(): string
    {
        $descripcion = parent::getDescripcion();
        $descripcion .= " + {$this->servicio->nombre}";

        if ($this->cantidad > 1) {
            $descripcion .= " (x{$this->cantidad})";
        }

        return $descripcion;
    }
}

/**
 * Gestor de decoradores para facilitar el uso
 */
class ReservaDecoratorManager
{
    /**
     * Agregar servicios a una reserva usando decoradores
     */
    public function agregarServicios(Reserva $reserva, array $servicios): ReservaComponente
    {
        $reservaDecorada = new ReservaBase($reserva);

        foreach ($servicios as $servicio) {
            if ($servicio instanceof Servicio) {
                $reservaDecorada = new ServicioPersonalizadoDecorator(
                    $reservaDecorada,
                    $servicio,
                    $servicio->pivot->cantidad ?? 1
                );
            }
        }

        return $reservaDecorada;
    }

    /**
     * Crear paquete predefinido
     */
    public function crearPaqueteRomantico(Reserva $reserva): ReservaComponente
    {
        $reservaDecorada = new ReservaBase($reserva);
        $reservaDecorada = new DesayunoDecorator($reservaDecorada);
        $reservaDecorada = new SpaDecorator($reservaDecorada);

        return $reservaDecorada;
    }

    /**
     * Crear paquete completo
     */
    public function crearPaqueteCompleto(Reserva $reserva): ReservaComponente
    {
        $reservaDecorada = new ReservaBase($reserva);
        $reservaDecorada = new DesayunoDecorator($reservaDecorada);
        $reservaDecorada = new SpaDecorator($reservaDecorada);
        $reservaDecorada = new TrasladoDecorator($reservaDecorada);

        return $reservaDecorada;
    }
}
