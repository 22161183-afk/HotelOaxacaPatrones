<?php

namespace App\Patterns\Creational;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;

/**
 * Patrón Builder para construir reservas complejas
 *
 * Permite construir objetos Reserva paso a paso con una interfaz fluida.
 */
class ReservaBuilder
{
    private ?Cliente $cliente = null;

    private ?Habitacion $habitacion = null;

    private ?Carbon $fechaInicio = null;

    private ?Carbon $fechaFin = null;

    private int $numeroHuespedes = 1;

    private array $servicios = [];

    private ?string $observaciones = null;

    private float $precioTotal = 0;

    private float $precioServicios = 0;

    /**
     * Establecer cliente
     */
    public function setCliente(Cliente $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Establecer habitación
     */
    public function setHabitacion(Habitacion $habitacion): self
    {
        $this->habitacion = $habitacion;

        return $this;
    }

    /**
     * Establecer fechas de la reserva
     */
    public function setFechas(string $inicio, string $fin): self
    {
        $this->fechaInicio = Carbon::parse($inicio);
        $this->fechaFin = Carbon::parse($fin);

        return $this;
    }

    /**
     * Establecer número de huéspedes
     */
    public function setNumeroHuespedes(int $numero): self
    {
        $this->numeroHuespedes = $numero;

        return $this;
    }

    /**
     * Agregar servicio a la reserva
     */
    public function agregarServicio(Servicio $servicio, int $cantidad = 1): self
    {
        $this->servicios[] = [
            'servicio' => $servicio,
            'cantidad' => $cantidad,
        ];

        return $this;
    }

    /**
     * Establecer observaciones
     */
    public function setObservaciones(string $observaciones): self
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Calcular precio total
     */
    private function calcularPrecios(): void
    {
        // Calcular noches
        $noches = $this->fechaInicio->diffInDays($this->fechaFin);

        // Precio base de la habitación
        $precioHabitacion = $this->habitacion->precio_base * $noches;

        // Precio de servicios
        $this->precioServicios = 0;
        foreach ($this->servicios as $item) {
            $this->precioServicios += $item['servicio']->precio * $item['cantidad'];
        }

        // Subtotal
        $subtotal = $precioHabitacion + $this->precioServicios;

        // Aplicar impuestos
        $config = ConfiguracionSingleton::getInstance();
        $impuesto = $config->getImpuesto();
        $montoImpuesto = $subtotal * ($impuesto / 100);

        $this->precioTotal = $subtotal + $montoImpuesto;
    }

    /**
     * Validar datos de la reserva
     */
    private function validar(): bool
    {
        if (! $this->cliente) {
            throw new \Exception('Debe especificar un cliente');
        }

        if (! $this->habitacion) {
            throw new \Exception('Debe especificar una habitación');
        }

        if (! $this->fechaInicio || ! $this->fechaFin) {
            throw new \Exception('Debe especificar las fechas de la reserva');
        }

        if ($this->fechaInicio >= $this->fechaFin) {
            throw new \Exception('La fecha de inicio debe ser anterior a la fecha de fin');
        }

        if ($this->numeroHuespedes > $this->habitacion->capacidad) {
            throw new \Exception('El número de huéspedes excede la capacidad de la habitación');
        }

        return true;
    }

    /**
     * Construir y guardar la reserva
     */
    public function build(): Reserva
    {
        $this->validar();
        $this->calcularPrecios();

        // Crear la reserva
        $reserva = Reserva::create([
            'cliente_id' => $this->cliente->id,
            'habitacion_id' => $this->habitacion->id,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'numero_huespedes' => $this->numeroHuespedes,
            'estado' => 'pendiente',
            'precio_total' => $this->precioTotal,
            'precio_servicios' => $this->precioServicios,
            'observaciones' => $this->observaciones,
        ]);

        // Agregar servicios a la reserva
        foreach ($this->servicios as $item) {
            $reserva->servicios()->attach($item['servicio']->id, [
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['servicio']->precio,
                'subtotal' => $item['servicio']->precio * $item['cantidad'],
            ]);
        }

        return $reserva;
    }

    /**
     * Resetear el builder
     */
    public function reset(): self
    {
        $this->cliente = null;
        $this->habitacion = null;
        $this->fechaInicio = null;
        $this->fechaFin = null;
        $this->numeroHuespedes = 1;
        $this->servicios = [];
        $this->observaciones = null;
        $this->precioTotal = 0;
        $this->precioServicios = 0;

        return $this;
    }
}
