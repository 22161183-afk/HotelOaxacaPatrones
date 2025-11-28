<?php

namespace App\Patterns\Creational;

use App\Models\Habitacion;

/**
 * Patrón Prototype para clonar habitaciones
 *
 * Permite crear nuevas habitaciones copiando una existente.
 * Útil para crear rápidamente habitaciones similares.
 */
class HabitacionPrototype
{
    private Habitacion $habitacion;

    public function __construct(Habitacion $habitacion)
    {
        $this->habitacion = $habitacion;
    }

    /**
     * Clonar una habitación existente con nuevo número
     */
    public function clonar(string $nuevoNumero): Habitacion
    {
        $nuevaHabitacion = $this->habitacion->replicate();
        $nuevaHabitacion->numero = $nuevoNumero;
        $nuevaHabitacion->save();

        return $nuevaHabitacion;
    }

    /**
     * Clonar habitación con modificaciones
     */
    public function clonarConModificaciones(
        string $nuevoNumero,
        array $cambios = []
    ): Habitacion {
        $nuevaHabitacion = $this->habitacion->replicate();
        $nuevaHabitacion->numero = $nuevoNumero;

        // Aplicar cambios específicos
        foreach ($cambios as $campo => $valor) {
            if ($campo !== 'id' && $campo !== 'numero') {
                $nuevaHabitacion->{$campo} = $valor;
            }
        }

        $nuevaHabitacion->save();

        return $nuevaHabitacion;
    }

    /**
     * Clonar múltiples habitaciones
     */
    public function clonarMultiples(
        array $numerosNuevos
    ): array {
        $habitaciones = [];

        foreach ($numerosNuevos as $numero) {
            $habitaciones[] = $this->clonar($numero);
        }

        return $habitaciones;
    }

    /**
     * Clonar habitación en otro piso
     */
    public function clonarEnOtroPiso(
        string $nuevoNumero,
        int $nuevoPiso
    ): Habitacion {
        return $this->clonarConModificaciones($nuevoNumero, [
            'piso' => $nuevoPiso,
        ]);
    }
}
