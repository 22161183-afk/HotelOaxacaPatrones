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
    /**
     * Clonar una habitación existente con nuevo número
     */
    public function clonar(Habitacion $habitacion, string $nuevoNumero): Habitacion
    {
        $nuevaHabitacion = $habitacion->replicate();
        $nuevaHabitacion->numero = $nuevoNumero;
        $nuevaHabitacion->save();

        return $nuevaHabitacion;
    }

    /**
     * Clonar habitación con modificaciones
     */
    public function clonarConModificaciones(
        Habitacion $habitacion,
        string $nuevoNumero,
        array $cambios = []
    ): Habitacion {
        $nuevaHabitacion = $habitacion->replicate();
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
        Habitacion $habitacion,
        array $numerosNuevos
    ): array {
        $habitaciones = [];

        foreach ($numerosNuevos as $numero) {
            $habitaciones[] = $this->clonar($habitacion, $numero);
        }

        return $habitaciones;
    }

    /**
     * Clonar habitación en otro piso
     */
    public function clonarEnOtroPiso(
        Habitacion $habitacion,
        string $nuevoNumero,
        int $nuevoPiso
    ): Habitacion {
        return $this->clonarConModificaciones($habitacion, $nuevoNumero, [
            'piso' => $nuevoPiso,
        ]);
    }
}
