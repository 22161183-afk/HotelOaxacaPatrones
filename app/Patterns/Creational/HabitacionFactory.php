<?php

namespace App\Patterns\Creational;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;

/**
 * Patrón Factory Method para crear habitaciones
 *
 * Encapsula la lógica de creación de habitaciones de diferentes tipos.
 */
abstract class HabitacionFactory
{
    /**
     * Método factory abstracto
     */
    abstract public function crearHabitacion(array $datos): Habitacion;

    /**
     * Configurar amenidades según el tipo
     */
    abstract protected function getAmenidades(): array;

    /**
     * Operación común para todas las fábricas
     */
    public function procesarCreacion(array $datos): Habitacion
    {
        $habitacion = $this->crearHabitacion($datos);
        $habitacion->amenidades = json_encode($this->getAmenidades());
        $habitacion->save();

        return $habitacion;
    }
}

/**
 * Factory para habitaciones Deluxe
 */
class HabitacionDeluxeFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoDeluxe = TipoHabitacion::where('nombre', 'Deluxe')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoDeluxe->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 2,
            'precio_base' => 1500.00,
            'descripcion' => 'Habitación Deluxe con todas las comodidades',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi Premium',
            'TV Smart 55"',
            'Minibar',
            'Aire acondicionado',
            'Caja fuerte',
            'Cafetera Nespresso',
            'Bata y pantuflas',
        ];
    }
}

/**
 * Factory para habitaciones Standard
 */
class HabitacionStandardFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoStandard = TipoHabitacion::where('nombre', 'Standard')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoStandard->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 2,
            'precio_base' => 800.00,
            'descripcion' => 'Habitación Standard confortable',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi',
            'TV por cable',
            'Aire acondicionado',
            'Baño privado',
        ];
    }
}

/**
 * Factory para Suite Presidencial
 */
class SuitePresidencialFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoSuite = TipoHabitacion::where('nombre', 'Suite Presidencial')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoSuite->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 4,
            'precio_base' => 3000.00,
            'descripcion' => 'Suite Presidencial de lujo con servicio premium',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi Premium',
            'TV Smart 75"',
            'Minibar Premium',
            'Jacuzzi',
            'Terraza privada',
            'Butler 24/7',
            'Sistema de sonido',
            'Sala de estar',
            'Comedor',
            'Cocina equipada',
        ];
    }
}

/**
 * Factory para habitaciones Familiares
 */
class HabitacionFamiliarFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoFamiliar = TipoHabitacion::where('nombre', 'Familiar')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoFamiliar->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 5,
            'precio_base' => 1200.00,
            'descripcion' => 'Habitación Familiar espaciosa',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi',
            'TV Smart 50"',
            'Minibar',
            'Balcón',
            'Mesa de trabajo',
            'Área de juegos para niños',
            'Microondas',
        ];
    }
}

/**
 * Clase creadora que determina qué factory usar
 */
class HabitacionFactoryCreator
{
    public static function getFactory(string $tipo): HabitacionFactory
    {
        return match ($tipo) {
            'deluxe' => new HabitacionDeluxeFactory,
            'standard' => new HabitacionStandardFactory,
            'suite' => new SuitePresidencialFactory,
            'familiar' => new HabitacionFamiliarFactory,
            default => new HabitacionStandardFactory,
        };
    }
}
