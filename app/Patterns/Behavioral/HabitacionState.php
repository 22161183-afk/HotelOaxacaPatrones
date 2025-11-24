<?php

namespace App\Patterns\Behavioral;

use App\Models\Habitacion;

/**
 * Patrón State para gestionar estados de habitación
 *
 * Permite que una habitación altere su comportamiento cuando su estado interno cambia.
 * Estados posibles: disponible, reservada, ocupada, mantenimiento
 */
interface HabitacionState
{
    public function reservar(Habitacion $habitacion): bool;

    public function ocupar(Habitacion $habitacion): bool;

    public function liberar(Habitacion $habitacion): bool;

    public function mantenimiento(Habitacion $habitacion): bool;

    public function getNombre(): string;

    public function puedeReservar(): bool;
}

/**
 * Estado: Disponible
 */
class DisponibleState implements HabitacionState
{
    public function reservar(Habitacion $habitacion): bool
    {
        $habitacion->update(['estado' => 'reservada']);

        return true;
    }

    public function ocupar(Habitacion $habitacion): bool
    {
        $habitacion->update(['estado' => 'ocupada']);

        return true;
    }

    public function liberar(Habitacion $habitacion): bool
    {
        return false; // Ya está libre
    }

    public function mantenimiento(Habitacion $habitacion): bool
    {
        $habitacion->update(['estado' => 'mantenimiento']);

        return true;
    }

    public function getNombre(): string
    {
        return 'disponible';
    }

    public function puedeReservar(): bool
    {
        return true;
    }
}

/**
 * Estado: Reservada
 */
class ReservadaState implements HabitacionState
{
    public function reservar(Habitacion $habitacion): bool
    {
        return false; // Ya está reservada
    }

    public function ocupar(Habitacion $habitacion): bool
    {
        // Pasar de reservada a ocupada cuando el cliente hace check-in
        $habitacion->update(['estado' => 'ocupada']);

        return true;
    }

    public function liberar(Habitacion $habitacion): bool
    {
        // Si se cancela la reserva, vuelve a disponible
        $habitacion->update(['estado' => 'disponible']);

        return true;
    }

    public function mantenimiento(Habitacion $habitacion): bool
    {
        return false; // No se puede poner en mantenimiento si está reservada
    }

    public function getNombre(): string
    {
        return 'reservada';
    }

    public function puedeReservar(): bool
    {
        return false;
    }
}

/**
 * Estado: Ocupada
 */
class OcupadaState implements HabitacionState
{
    public function reservar(Habitacion $habitacion): bool
    {
        return false; // No se puede reservar si está ocupada
    }

    public function ocupar(Habitacion $habitacion): bool
    {
        return false; // Ya está ocupada
    }

    public function liberar(Habitacion $habitacion): bool
    {
        // Cuando el cliente hace checkout
        $habitacion->update(['estado' => 'disponible']);

        return true;
    }

    public function mantenimiento(Habitacion $habitacion): bool
    {
        return false; // No se puede poner en mantenimiento si está ocupada
    }

    public function getNombre(): string
    {
        return 'ocupada';
    }

    public function puedeReservar(): bool
    {
        return false;
    }
}

/**
 * Estado: Mantenimiento
 */
class MantenimientoState implements HabitacionState
{
    public function reservar(Habitacion $habitacion): bool
    {
        return false; // No se puede reservar en mantenimiento
    }

    public function ocupar(Habitacion $habitacion): bool
    {
        return false; // No se puede ocupar en mantenimiento
    }

    public function liberar(Habitacion $habitacion): bool
    {
        // Terminar mantenimiento
        $habitacion->update(['estado' => 'disponible']);

        return true;
    }

    public function mantenimiento(Habitacion $habitacion): bool
    {
        return false; // Ya está en mantenimiento
    }

    public function getNombre(): string
    {
        return 'mantenimiento';
    }

    public function puedeReservar(): bool
    {
        return false;
    }
}

/**
 * Context - Gestiona el estado de la habitación
 */
class HabitacionContext
{
    private HabitacionState $state;

    private Habitacion $habitacion;

    public function __construct(Habitacion $habitacion)
    {
        $this->habitacion = $habitacion;
        $this->state = $this->getStateFromHabitacion($habitacion);
    }

    /**
     * Obtener estado según el string en la BD
     */
    private function getStateFromHabitacion(Habitacion $habitacion): HabitacionState
    {
        return match ($habitacion->estado) {
            'disponible' => new DisponibleState,
            'reservada' => new ReservadaState,
            'ocupada' => new OcupadaState,
            'mantenimiento' => new MantenimientoState,
            default => new DisponibleState,
        };
    }

    /**
     * Cambiar estado
     */
    public function setState(HabitacionState $state): void
    {
        $this->state = $state;
    }

    /**
     * Reservar habitación
     */
    public function reservar(): bool
    {
        return $this->state->reservar($this->habitacion);
    }

    /**
     * Ocupar habitación
     */
    public function ocupar(): bool
    {
        return $this->state->ocupar($this->habitacion);
    }

    /**
     * Liberar habitación
     */
    public function liberar(): bool
    {
        return $this->state->liberar($this->habitacion);
    }

    /**
     * Poner en mantenimiento
     */
    public function mantenimiento(): bool
    {
        return $this->state->mantenimiento($this->habitacion);
    }

    /**
     * Verificar si se puede reservar
     */
    public function puedeReservar(): bool
    {
        return $this->state->puedeReservar();
    }

    /**
     * Obtener nombre del estado actual
     */
    public function getEstadoActual(): string
    {
        return $this->state->getNombre();
    }
}
