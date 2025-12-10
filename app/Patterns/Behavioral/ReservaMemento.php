<?php

namespace App\Patterns\Behavioral;

use App\Models\Reserva;
use Carbon\Carbon;

/**
 * Patrón Memento para guardar y restaurar estados de reservas
 *
 * Viñeta 15: El sistema deberá permitir deshacer o rehacer cambios en las reservas
 * Viñeta 19: El sistema deberá poder guardar y restaurar estados previos de una reserva
 */

/**
 * Memento - Almacena el estado de una reserva
 */
class ReservaMemento
{
    private int $reservaId;

    private array $estado;

    private Carbon $timestamp;

    private string $operacion;

    public function __construct(Reserva $reserva, string $operacion = 'snapshot')
    {
        $this->reservaId = $reserva->id;
        $this->timestamp = now();
        $this->operacion = $operacion;

        // Guardar estado completo
        $this->estado = [
            'cliente_id' => $reserva->cliente_id,
            'habitacion_id' => $reserva->habitacion_id,
            'fecha_inicio' => $reserva->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $reserva->fecha_fin->format('Y-m-d'),
            'numero_huespedes' => $reserva->numero_huespedes,
            'estado' => $reserva->estado,
            'precio_total' => $reserva->precio_total,
            'precio_servicios' => $reserva->precio_servicios,
            'observaciones' => $reserva->observaciones,
            'fecha_confirmacion' => $reserva->fecha_confirmacion?->format('Y-m-d H:i:s'),
            'fecha_cancelacion' => $reserva->fecha_cancelacion?->format('Y-m-d H:i:s'),
            // Guardar también servicios asociados
            'servicios' => $reserva->servicios->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'cantidad' => $servicio->pivot->cantidad,
                    'precio_unitario' => $servicio->pivot->precio_unitario,
                ];
            })->toArray(),
        ];
    }

    public function getEstado(): array
    {
        return $this->estado;
    }

    public function getReservaId(): int
    {
        return $this->reservaId;
    }

    public function getTimestamp(): Carbon
    {
        return $this->timestamp;
    }

    public function getOperacion(): string
    {
        return $this->operacion;
    }

    public function getDescripcion(): string
    {
        return "{$this->operacion} - {$this->timestamp->format('Y-m-d H:i:s')}";
    }
}

/**
 * Originator - Reserva que puede crear y restaurar mementos
 */
class ReservaOriginator
{
    private Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Crear memento del estado actual
     */
    public function guardar(string $operacion = 'manual'): ReservaMemento
    {
        return new ReservaMemento($this->reserva, $operacion);
    }

    /**
     * Restaurar desde memento
     */
    public function restaurar(ReservaMemento $memento): bool
    {
        if ($memento->getReservaId() !== $this->reserva->id) {
            throw new \Exception('El memento no corresponde a esta reserva');
        }

        $estado = $memento->getEstado();

        // Restaurar datos básicos
        $this->reserva->update([
            'cliente_id' => $estado['cliente_id'],
            'habitacion_id' => $estado['habitacion_id'],
            'fecha_inicio' => $estado['fecha_inicio'],
            'fecha_fin' => $estado['fecha_fin'],
            'numero_huespedes' => $estado['numero_huespedes'],
            'estado' => $estado['estado'],
            'precio_total' => $estado['precio_total'],
            'precio_servicios' => $estado['precio_servicios'],
            'observaciones' => $estado['observaciones'],
            'fecha_confirmacion' => $estado['fecha_confirmacion'],
            'fecha_cancelacion' => $estado['fecha_cancelacion'],
        ]);

        // Restaurar servicios
        $this->reserva->servicios()->detach();
        foreach ($estado['servicios'] as $servicio) {
            $this->reserva->servicios()->attach($servicio['id'], [
                'cantidad' => $servicio['cantidad'],
                'precio_unitario' => $servicio['precio_unitario'],
                'subtotal' => $servicio['cantidad'] * $servicio['precio_unitario'],
            ]);
        }

        $this->reserva->refresh();

        return true;
    }

    public function getReserva(): Reserva
    {
        return $this->reserva;
    }
}

/**
 * Caretaker - Gestiona los mementos
 */
class ReservaCaretaker
{
    private array $mementos = [];

    private int $posicionActual = -1;

    private int $maxHistorial = 20;

    /**
     * Guardar memento
     */
    public function guardar(ReservaMemento $memento): void
    {
        // Si estamos en medio del historial, eliminar mementos posteriores
        if ($this->posicionActual < count($this->mementos) - 1) {
            $this->mementos = array_slice($this->mementos, 0, $this->posicionActual + 1);
        }

        // Agregar nuevo memento
        $this->mementos[] = $memento;
        $this->posicionActual++;

        // Limitar tamaño del historial
        if (count($this->mementos) > $this->maxHistorial) {
            array_shift($this->mementos);
            $this->posicionActual--;
        }
    }

    /**
     * Deshacer (undo) - volver al estado anterior
     */
    public function deshacer(): ?ReservaMemento
    {
        if ($this->posicionActual > 0) {
            $this->posicionActual--;

            return $this->mementos[$this->posicionActual];
        }

        return null;
    }

    /**
     * Rehacer (redo) - volver al estado posterior
     */
    public function rehacer(): ?ReservaMemento
    {
        if ($this->posicionActual < count($this->mementos) - 1) {
            $this->posicionActual++;

            return $this->mementos[$this->posicionActual];
        }

        return null;
    }

    /**
     * Obtener memento actual
     */
    public function getActual(): ?ReservaMemento
    {
        if ($this->posicionActual >= 0 && $this->posicionActual < count($this->mementos)) {
            return $this->mementos[$this->posicionActual];
        }

        return null;
    }

    /**
     * Obtener historial completo
     */
    public function getHistorial(): array
    {
        return array_map(function ($memento, $index) {
            return [
                'index' => $index,
                'es_actual' => $index === $this->posicionActual,
                'descripcion' => $memento->getDescripcion(),
                'timestamp' => $memento->getTimestamp()->format('Y-m-d H:i:s'),
                'operacion' => $memento->getOperacion(),
            ];
        }, $this->mementos, array_keys($this->mementos));
    }

    /**
     * Ir a un punto específico del historial
     */
    public function irA(int $indice): ?ReservaMemento
    {
        if ($indice >= 0 && $indice < count($this->mementos)) {
            $this->posicionActual = $indice;

            return $this->mementos[$indice];
        }

        return null;
    }

    /**
     * Puede deshacer?
     */
    public function puedeDeshacer(): bool
    {
        return $this->posicionActual > 0;
    }

    /**
     * Puede rehacer?
     */
    public function puedeRehacer(): bool
    {
        return $this->posicionActual < count($this->mementos) - 1;
    }

    /**
     * Limpiar historial
     */
    public function limpiar(): void
    {
        $this->mementos = [];
        $this->posicionActual = -1;
    }

    /**
     * Obtener estadísticas
     */
    public function getEstadisticas(): array
    {
        return [
            'total_mementos' => count($this->mementos),
            'posicion_actual' => $this->posicionActual,
            'puede_deshacer' => $this->puedeDeshacer(),
            'puede_rehacer' => $this->puedeRehacer(),
            'max_historial' => $this->maxHistorial,
        ];
    }
}

/**
 * Gestor de historial de reservas (combina Originator y Caretaker)
 */
class ReservaHistorialManager
{
    private static array $caretakers = [];

    /**
     * Obtener caretaker para una reserva
     */
    private static function getCaretaker(int $reservaId): ReservaCaretaker
    {
        if (! isset(self::$caretakers[$reservaId])) {
            self::$caretakers[$reservaId] = new ReservaCaretaker;
        }

        return self::$caretakers[$reservaId];
    }

    /**
     * Guardar checkpoint de una reserva
     */
    public static function guardarCheckpoint(Reserva $reserva, string $operacion = 'checkpoint'): void
    {
        $originator = new ReservaOriginator($reserva);
        $memento = $originator->guardar($operacion);

        $caretaker = self::getCaretaker($reserva->id);
        $caretaker->guardar($memento);
    }

    /**
     * Deshacer último cambio
     */
    public static function deshacer(Reserva $reserva): bool
    {
        $caretaker = self::getCaretaker($reserva->id);
        $memento = $caretaker->deshacer();

        if ($memento) {
            $originator = new ReservaOriginator($reserva);
            $originator->restaurar($memento);

            return true;
        }

        return false;
    }

    /**
     * Rehacer cambio
     */
    public static function rehacer(Reserva $reserva): bool
    {
        $caretaker = self::getCaretaker($reserva->id);
        $memento = $caretaker->rehacer();

        if ($memento) {
            $originator = new ReservaOriginator($reserva);
            $originator->restaurar($memento);

            return true;
        }

        return false;
    }

    /**
     * Restaurar a un punto específico del historial
     */
    public static function restaurarA(Reserva $reserva, int $indice): bool
    {
        $caretaker = self::getCaretaker($reserva->id);
        $memento = $caretaker->irA($indice);

        if ($memento) {
            $originator = new ReservaOriginator($reserva);
            $originator->restaurar($memento);

            return true;
        }

        return false;
    }

    /**
     * Obtener historial de una reserva
     */
    public static function getHistorial(Reserva $reserva): array
    {
        $caretaker = self::getCaretaker($reserva->id);

        return $caretaker->getHistorial();
    }

    /**
     * Verificar si puede deshacer
     */
    public static function puedeDeshacer(Reserva $reserva): bool
    {
        $caretaker = self::getCaretaker($reserva->id);

        return $caretaker->puedeDeshacer();
    }

    /**
     * Verificar si puede rehacer
     */
    public static function puedeRehacer(Reserva $reserva): bool
    {
        $caretaker = self::getCaretaker($reserva->id);

        return $caretaker->puedeRehacer();
    }

    /**
     * Limpiar historial de una reserva
     */
    public static function limpiarHistorial(Reserva $reserva): void
    {
        $caretaker = self::getCaretaker($reserva->id);
        $caretaker->limpiar();
    }

    /**
     * Obtener estadísticas de una reserva
     */
    public static function getEstadisticas(Reserva $reserva): array
    {
        $caretaker = self::getCaretaker($reserva->id);

        return $caretaker->getEstadisticas();
    }

    /**
     * Limpiar todos los historiales
     */
    public static function limpiarTodos(): void
    {
        self::$caretakers = [];
    }
}
