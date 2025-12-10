<?php

namespace App\Patterns\Structural;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;

/**
 * Patrón Flyweight para optimizar memoria compartiendo información común
 *
 * Viñeta 12: Optimizar uso de memoria compartiendo información común entre habitaciones similares
 * Separa estado intrínseco (compartido) del extrínseco (único)
 */

/**
 * Flyweight - Contiene estado intrínseco (compartido entre habitaciones del mismo tipo)
 */
class TipoHabitacionFlyweight
{
    private int $tipoId;

    private string $nombre;

    private string $descripcion;

    private array $amenidades;

    private float $precioBase;

    private int $capacidad;

    private string $imagenUrl;

    public function __construct(TipoHabitacion $tipo)
    {
        $this->tipoId = $tipo->id;
        $this->nombre = $tipo->nombre;
        $this->descripcion = $tipo->descripcion ?? '';
        $this->amenidades = $tipo->amenidades_incluidas ?? [];
        $this->precioBase = $tipo->precio_base ?? 0;
        $this->capacidad = $tipo->capacidad_maxima ?? 2;
        $this->imagenUrl = $tipo->imagen_url ?? '';
    }

    /**
     * Operación que usa estado intrínseco y extrínseco
     */
    public function mostrarInfo(string $numero, int $piso, string $estado): array
    {
        return [
            'tipo_id' => $this->tipoId,
            'tipo_nombre' => $this->nombre,
            'numero' => $numero,
            'piso' => $piso,
            'estado' => $estado,
            'descripcion' => $this->descripcion,
            'amenidades' => $this->amenidades,
            'precio_base' => $this->precioBase,
            'capacidad' => $this->capacidad,
            'imagen_url' => $this->imagenUrl,
        ];
    }

    public function getTipoId(): int
    {
        return $this->tipoId;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getAmenidades(): array
    {
        return $this->amenidades;
    }

    public function getPrecioBase(): float
    {
        return $this->precioBase;
    }

    public function getCapacidad(): int
    {
        return $this->capacidad;
    }

    public function getImagenUrl(): string
    {
        return $this->imagenUrl;
    }
}

/**
 * Flyweight Factory - Gestiona y reutiliza flyweights
 */
class HabitacionFlyweightFactory
{
    private static ?HabitacionFlyweightFactory $instance = null;

    private array $flyweights = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Obtener flyweight (lo crea si no existe)
     */
    public function getFlyweight(int $tipoId): TipoHabitacionFlyweight
    {
        if (! isset($this->flyweights[$tipoId])) {
            $tipo = TipoHabitacion::find($tipoId);
            if (! $tipo) {
                throw new \Exception("Tipo de habitación no encontrado: {$tipoId}");
            }
            $this->flyweights[$tipoId] = new TipoHabitacionFlyweight($tipo);
        }

        return $this->flyweights[$tipoId];
    }

    /**
     * Obtener estadísticas de uso de memoria
     */
    public function getEstadisticas(): array
    {
        return [
            'flyweights_creados' => count($this->flyweights),
            'tipos_cargados' => array_keys($this->flyweights),
            'memoria_aproximada' => count($this->flyweights) * 1024, // Estimado en bytes
        ];
    }

    /**
     * Limpiar cache de flyweights
     */
    public function limpiarCache(): void
    {
        $this->flyweights = [];
    }

    /**
     * Precargar todos los tipos de habitación
     */
    public function precargarTodos(): void
    {
        $tipos = TipoHabitacion::all();
        foreach ($tipos as $tipo) {
            if (! isset($this->flyweights[$tipo->id])) {
                $this->flyweights[$tipo->id] = new TipoHabitacionFlyweight($tipo);
            }
        }
    }
}

/**
 * Contexto - Representa una habitación específica usando flyweight
 */
class HabitacionContext
{
    private TipoHabitacionFlyweight $tipo;

    // Estado extrínseco (único para cada habitación)
    private string $numero;

    private int $piso;

    private string $estado;

    private ?int $habitacionId;

    public function __construct(Habitacion $habitacion)
    {
        $factory = HabitacionFlyweightFactory::getInstance();
        $this->tipo = $factory->getFlyweight($habitacion->tipo_habitacion_id);

        $this->numero = $habitacion->numero;
        $this->piso = $habitacion->piso;
        $this->estado = $habitacion->estado;
        $this->habitacionId = $habitacion->id;
    }

    /**
     * Obtener información completa de la habitación
     */
    public function getInfo(): array
    {
        $info = $this->tipo->mostrarInfo($this->numero, $this->piso, $this->estado);
        $info['habitacion_id'] = $this->habitacionId;

        return $info;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getPiso(): int
    {
        return $this->piso;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getTipo(): TipoHabitacionFlyweight
    {
        return $this->tipo;
    }

    /**
     * Actualizar estado extrínseco
     */
    public function setEstado(string $estado): void
    {
        $this->estado = $estado;
    }
}

/**
 * Gestor de habitaciones usando Flyweight
 */
class HabitacionFlyweightManager
{
    /**
     * Obtener múltiples habitaciones optimizadas
     */
    public static function obtenerHabitaciones(array $habitaciones): array
    {
        $contextos = [];
        foreach ($habitaciones as $habitacion) {
            $contextos[] = new HabitacionContext($habitacion);
        }

        return $contextos;
    }

    /**
     * Obtener información de todas las habitaciones
     */
    public static function obtenerInformacion(array $habitaciones): array
    {
        $contextos = self::obtenerHabitaciones($habitaciones);
        $informacion = [];

        foreach ($contextos as $contexto) {
            $informacion[] = $contexto->getInfo();
        }

        return $informacion;
    }

    /**
     * Mostrar estadísticas de optimización
     */
    public static function mostrarEstadisticas(): array
    {
        $factory = HabitacionFlyweightFactory::getInstance();
        $stats = $factory->getEstadisticas();

        $totalHabitaciones = Habitacion::count();
        $memoriaConFlyweight = $stats['memoria_aproximada'];
        $memoriaSinFlyweight = $totalHabitaciones * 2048; // Estimado

        return [
            'total_habitaciones' => $totalHabitaciones,
            'flyweights_creados' => $stats['flyweights_creados'],
            'memoria_con_flyweight' => $memoriaConFlyweight,
            'memoria_sin_flyweight' => $memoriaSinFlyweight,
            'ahorro_memoria' => $memoriaSinFlyweight - $memoriaConFlyweight,
            'porcentaje_ahorro' => round((($memoriaSinFlyweight - $memoriaConFlyweight) / $memoriaSinFlyweight) * 100, 2),
        ];
    }
}
