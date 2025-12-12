<?php

namespace App\Patterns\Behavioral;

use App\Events\ReservaCancelada;
use App\Events\ReservaConfirmada;
use App\Models\Reserva;

/**
 * Patrón Command para encapsular operaciones sobre reservas
 *
 * Permite parametrizar objetos con operaciones, encolar solicitudes
 * y soportar operaciones reversibles (undo).
 */
interface ReservaCommand
{
    public function execute(): bool;

    public function undo(): bool;

    public function getDescripcion(): string;
}

/**
 * Command para confirmar una reserva
 */
class ConfirmarReservaCommand implements ReservaCommand
{
    private Reserva $reserva;

    private ?string $estadoAnterior = null;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function execute(): bool
    {
        $this->estadoAnterior = $this->reserva->estado;

        $this->reserva->update([
            'estado' => 'confirmada',
            'fecha_confirmacion' => now(),
        ]);

        $this->reserva->habitacion->update(['estado' => 'ocupada']);

        event(new ReservaConfirmada($this->reserva));

        return true;
    }

    public function undo(): bool
    {
        if ($this->estadoAnterior) {
            $this->reserva->update([
                'estado' => $this->estadoAnterior,
                'fecha_confirmacion' => null,
            ]);

            $this->reserva->habitacion->update(['estado' => 'disponible']);

            return true;
        }

        return false;
    }

    public function getDescripcion(): string
    {
        return "Confirmar reserva #{$this->reserva->id}";
    }
}

/**
 * Command para cancelar una reserva
 */
class CancelarReservaCommand implements ReservaCommand
{
    private Reserva $reserva;

    private string $motivo;

    private ?string $estadoAnterior = null;

    public function __construct(Reserva $reserva, string $motivo = '')
    {
        $this->reserva = $reserva;
        $this->motivo = $motivo;
    }

    public function execute(): bool
    {
        $this->estadoAnterior = $this->reserva->estado;

        $this->reserva->update([
            'estado' => 'cancelada',
            'fecha_cancelacion' => now(),
            'observaciones' => $this->reserva->observaciones."\nCancelada: ".$this->motivo,
        ]);

        $this->reserva->habitacion->update(['estado' => 'disponible']);

        event(new ReservaCancelada($this->reserva));

        return true;
    }

    public function undo(): bool
    {
        if ($this->estadoAnterior) {
            $this->reserva->update([
                'estado' => $this->estadoAnterior,
                'fecha_cancelacion' => null,
            ]);

            $this->reserva->habitacion->update(['estado' => 'ocupada']);

            return true;
        }

        return false;
    }

    public function getDescripcion(): string
    {
        return "Cancelar reserva #{$this->reserva->id}";
    }
}

/**
 * Command para cambiar habitación de una reserva
 */
class CambiarHabitacionCommand implements ReservaCommand
{
    private Reserva $reserva;

    private int $nuevaHabitacionId;

    private ?int $habitacionAnteriorId = null;

    public function __construct(Reserva $reserva, int $nuevaHabitacionId)
    {
        $this->reserva = $reserva;
        $this->nuevaHabitacionId = $nuevaHabitacionId;
    }

    public function execute(): bool
    {
        $this->habitacionAnteriorId = $this->reserva->habitacion_id;

        // Obtener habitación anterior antes de cambiar
        $habitacionAnterior = $this->reserva->habitacion;

        // Actualizar reserva con nueva habitación
        $this->reserva->update(['habitacion_id' => $this->nuevaHabitacionId]);

        // Refrescar la relación para obtener la nueva habitación
        $this->reserva->refresh();
        $this->reserva->load('habitacion');

        // Liberar habitación anterior
        $habitacionAnterior->update(['estado' => 'disponible']);

        // Ocupar nueva habitación
        $this->reserva->habitacion->update(['estado' => 'ocupada']);

        return true;
    }

    public function undo(): bool
    {
        if ($this->habitacionAnteriorId) {
            // Liberar habitación actual
            $this->reserva->habitacion->update(['estado' => 'disponible']);

            // Restaurar habitación anterior
            $this->reserva->update(['habitacion_id' => $this->habitacionAnteriorId]);
            $this->reserva->habitacion->update(['estado' => 'ocupada']);

            return true;
        }

        return false;
    }

    public function getDescripcion(): string
    {
        return "Cambiar habitación de reserva #{$this->reserva->id}";
    }
}

/**
 * Invoker - Gestiona la ejecución de comandos
 */
class ReservaCommandInvoker
{
    private array $historial = [];

    private int $posicionActual = -1;

    /**
     * Ejecutar un comando
     */
    public function ejecutar(ReservaCommand $command): bool
    {
        $resultado = $command->execute();

        if ($resultado) {
            // Limpiar historial posterior si estamos en medio
            if ($this->posicionActual < count($this->historial) - 1) {
                $this->historial = array_slice($this->historial, 0, $this->posicionActual + 1);
            }

            // Agregar al historial
            $this->historial[] = $command;
            $this->posicionActual++;
        }

        return $resultado;
    }

    /**
     * Deshacer último comando
     */
    public function deshacer(): bool
    {
        if ($this->posicionActual >= 0) {
            $command = $this->historial[$this->posicionActual];
            $resultado = $command->undo();

            if ($resultado) {
                $this->posicionActual--;
            }

            return $resultado;
        }

        return false;
    }

    /**
     * Rehacer comando
     */
    public function rehacer(): bool
    {
        if ($this->posicionActual < count($this->historial) - 1) {
            $this->posicionActual++;
            $command = $this->historial[$this->posicionActual];

            return $command->execute();
        }

        return false;
    }

    /**
     * Obtener historial de comandos
     */
    public function getHistorial(): array
    {
        return array_map(fn ($cmd) => $cmd->getDescripcion(), $this->historial);
    }

    /**
     * Limpiar historial
     */
    public function limpiarHistorial(): void
    {
        $this->historial = [];
        $this->posicionActual = -1;
    }
}
