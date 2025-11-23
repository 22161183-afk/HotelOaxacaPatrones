<?php

namespace App\Patterns\Behavioral;

use App\Models\Reserva;

/**
 * Patrón State para gestionar estados de reserva
 *
 * Permite que un objeto altere su comportamiento cuando su estado interno cambia.
 */
interface ReservaState
{
    public function confirmar(Reserva $reserva): bool;

    public function cancelar(Reserva $reserva): bool;

    public function completar(Reserva $reserva): bool;

    public function getNombre(): string;

    public function puedeModificar(): bool;
}

/**
 * Estado: Pendiente
 */
class PendienteState implements ReservaState
{
    public function confirmar(Reserva $reserva): bool
    {
        $reserva->update(['estado' => 'confirmada', 'fecha_confirmacion' => now()]);
        $reserva->setState(new ConfirmadaState);

        return true;
    }

    public function cancelar(Reserva $reserva): bool
    {
        $reserva->update(['estado' => 'cancelada', 'fecha_cancelacion' => now()]);
        $reserva->setState(new CanceladaState);

        return true;
    }

    public function completar(Reserva $reserva): bool
    {
        return false; // No se puede completar sin confirmar
    }

    public function getNombre(): string
    {
        return 'pendiente';
    }

    public function puedeModificar(): bool
    {
        return true;
    }
}

/**
 * Estado: Confirmada
 */
class ConfirmadaState implements ReservaState
{
    public function confirmar(Reserva $reserva): bool
    {
        return false; // Ya está confirmada
    }

    public function cancelar(Reserva $reserva): bool
    {
        $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
        $diasCancelacion = $config->getDiasCancelacion();
        $diasHastaReserva = now()->diffInDays($reserva->fecha_inicio);

        if ($diasHastaReserva >= $diasCancelacion) {
            $reserva->update(['estado' => 'cancelada', 'fecha_cancelacion' => now()]);
            $reserva->setState(new CanceladaState);

            return true;
        }

        return false; // Fuera del período de cancelación gratuita
    }

    public function completar(Reserva $reserva): bool
    {
        // Solo se puede completar después de la fecha de fin
        if (now()->greaterThanOrEqualTo($reserva->fecha_fin)) {
            $reserva->update(['estado' => 'completada']);
            $reserva->setState(new CompletadaState);

            return true;
        }

        return false;
    }

    public function getNombre(): string
    {
        return 'confirmada';
    }

    public function puedeModificar(): bool
    {
        return true;
    }
}

/**
 * Estado: Cancelada
 */
class CanceladaState implements ReservaState
{
    public function confirmar(Reserva $reserva): bool
    {
        return false; // No se puede confirmar una reserva cancelada
    }

    public function cancelar(Reserva $reserva): bool
    {
        return false; // Ya está cancelada
    }

    public function completar(Reserva $reserva): bool
    {
        return false; // No se puede completar una cancelada
    }

    public function getNombre(): string
    {
        return 'cancelada';
    }

    public function puedeModificar(): bool
    {
        return false;
    }
}

/**
 * Estado: Completada
 */
class CompletadaState implements ReservaState
{
    public function confirmar(Reserva $reserva): bool
    {
        return false; // Ya está completada
    }

    public function cancelar(Reserva $reserva): bool
    {
        return false; // No se puede cancelar una completada
    }

    public function completar(Reserva $reserva): bool
    {
        return false; // Ya está completada
    }

    public function getNombre(): string
    {
        return 'completada';
    }

    public function puedeModificar(): bool
    {
        return false;
    }
}

/**
 * Context - Gestiona el estado de la reserva
 */
class ReservaContext
{
    private ReservaState $state;

    private Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
        $this->state = $this->getStateFromReserva($reserva);
    }

    /**
     * Obtener estado según el string en la BD
     */
    private function getStateFromReserva(Reserva $reserva): ReservaState
    {
        return match ($reserva->estado) {
            'pendiente' => new PendienteState,
            'confirmada' => new ConfirmadaState,
            'cancelada' => new CanceladaState,
            'completada' => new CompletadaState,
            default => new PendienteState,
        };
    }

    /**
     * Cambiar estado
     */
    public function setState(ReservaState $state): void
    {
        $this->state = $state;
    }

    /**
     * Confirmar reserva
     */
    public function confirmar(): bool
    {
        return $this->state->confirmar($this->reserva);
    }

    /**
     * Cancelar reserva
     */
    public function cancelar(): bool
    {
        return $this->state->cancelar($this->reserva);
    }

    /**
     * Completar reserva
     */
    public function completar(): bool
    {
        return $this->state->completar($this->reserva);
    }

    /**
     * Verificar si se puede modificar
     */
    public function puedeModificar(): bool
    {
        return $this->state->puedeModificar();
    }

    /**
     * Obtener nombre del estado actual
     */
    public function getEstadoActual(): string
    {
        return $this->state->getNombre();
    }
}
