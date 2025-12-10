<?php

namespace App\Patterns\Behavioral;

use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Support\Collection;

/**
 * Patrón Iterator para recorrer colecciones de manera uniforme
 *
 * Viñeta 17: Los usuarios deberán poder recorrer colecciones de reservas
 * o habitaciones de manera uniforme
 */

/**
 * Iterator abstracto
 */
interface CollectionIterator extends \Iterator
{
    public function filtrar(callable $callback): self;

    public function ordenar(callable $callback): self;

    public function aplicar(callable $callback): self;

    public function toArray(): array;

    public function count(): int;
}

/**
 * Iterator concreto para Reservas
 */
class ReservaIterator implements CollectionIterator
{
    private Collection $items;

    private int $posicion = 0;

    public function __construct(Collection $reservas)
    {
        $this->items = $reservas->values(); // Reiniciar índices
        $this->posicion = 0;
    }

    /**
     * Crear iterator desde query
     */
    public static function crearDesdeQuery($query): self
    {
        return new self($query->get());
    }

    /**
     * Crear iterator de todas las reservas
     */
    public static function todas(): self
    {
        return new self(Reserva::with(['cliente', 'habitacion', 'servicios'])->get());
    }

    /**
     * Iterator de reservas por estado
     */
    public static function porEstado(string $estado): self
    {
        return new self(Reserva::where('estado', $estado)
            ->with(['cliente', 'habitacion', 'servicios'])
            ->get());
    }

    /**
     * Iterator de reservas futuras
     */
    public static function futuras(): self
    {
        return new self(Reserva::where('fecha_inicio', '>', now())
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->with(['cliente', 'habitacion', 'servicios'])
            ->orderBy('fecha_inicio')
            ->get());
    }

    /**
     * Iterator de reservas de un cliente
     */
    public static function porCliente(int $clienteId): self
    {
        return new self(Reserva::where('cliente_id', $clienteId)
            ->with(['habitacion', 'servicios'])
            ->orderBy('created_at', 'desc')
            ->get());
    }

    // Métodos de Iterator
    public function current(): mixed
    {
        return $this->items[$this->posicion];
    }

    public function key(): mixed
    {
        return $this->posicion;
    }

    public function next(): void
    {
        $this->posicion++;
    }

    public function rewind(): void
    {
        $this->posicion = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->posicion]);
    }

    // Métodos personalizados
    public function filtrar(callable $callback): self
    {
        $this->items = $this->items->filter($callback)->values();
        $this->rewind();

        return $this;
    }

    public function ordenar(callable $callback): self
    {
        $this->items = $this->items->sort($callback)->values();
        $this->rewind();

        return $this;
    }

    public function aplicar(callable $callback): self
    {
        $this->items = $this->items->map($callback)->values();
        $this->rewind();

        return $this;
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Obtener primera reserva
     */
    public function primera(): ?Reserva
    {
        return $this->items->first();
    }

    /**
     * Obtener última reserva
     */
    public function ultima(): ?Reserva
    {
        return $this->items->last();
    }

    /**
     * Calcular total de ingresos
     */
    public function calcularIngresos(): float
    {
        return $this->items->sum('precio_total');
    }
}

/**
 * Iterator concreto para Habitaciones
 */
class HabitacionIterator implements CollectionIterator
{
    private Collection $items;

    private int $posicion = 0;

    public function __construct(Collection $habitaciones)
    {
        $this->items = $habitaciones->values();
        $this->posicion = 0;
    }

    /**
     * Crear iterator de todas las habitaciones
     */
    public static function todas(): self
    {
        return new self(Habitacion::with('tipoHabitacion')->get());
    }

    /**
     * Iterator de habitaciones disponibles
     */
    public static function disponibles(): self
    {
        return new self(Habitacion::where('estado', 'disponible')
            ->with('tipoHabitacion')
            ->get());
    }

    /**
     * Iterator por tipo
     */
    public static function porTipo(int $tipoId): self
    {
        return new self(Habitacion::where('tipo_habitacion_id', $tipoId)
            ->with('tipoHabitacion')
            ->get());
    }

    /**
     * Iterator por piso
     */
    public static function porPiso(int $piso): self
    {
        return new self(Habitacion::where('piso', $piso)
            ->with('tipoHabitacion')
            ->get());
    }

    // Métodos de Iterator
    public function current(): mixed
    {
        return $this->items[$this->posicion];
    }

    public function key(): mixed
    {
        return $this->posicion;
    }

    public function next(): void
    {
        $this->posicion++;
    }

    public function rewind(): void
    {
        $this->posicion = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->posicion]);
    }

    // Métodos personalizados
    public function filtrar(callable $callback): self
    {
        $this->items = $this->items->filter($callback)->values();
        $this->rewind();

        return $this;
    }

    public function ordenar(callable $callback): self
    {
        $this->items = $this->items->sort($callback)->values();
        $this->rewind();

        return $this;
    }

    public function aplicar(callable $callback): self
    {
        $this->items = $this->items->map($callback)->values();
        $this->rewind();

        return $this;
    }

    public function toArray(): array
    {
        return $this->items->toArray();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * Filtrar por capacidad mínima
     */
    public function conCapacidad(int $capacidad): self
    {
        return $this->filtrar(fn ($h) => $h->capacidad >= $capacidad);
    }

    /**
     * Filtrar por rango de precio
     */
    public function enRangoPrecio(float $min, float $max): self
    {
        return $this->filtrar(fn ($h) => $h->precio_base >= $min && $h->precio_base <= $max);
    }

    /**
     * Agrupar por piso
     */
    public function agruparPorPiso(): array
    {
        return $this->items->groupBy('piso')->toArray();
    }

    /**
     * Agrupar por tipo
     */
    public function agruparPorTipo(): array
    {
        return $this->items->groupBy('tipo_habitacion_id')->toArray();
    }
}

/**
 * Aggregate - Colección de reservas
 */
class ReservaCollection
{
    private Collection $reservas;

    public function __construct(Collection $reservas)
    {
        $this->reservas = $reservas;
    }

    /**
     * Crear iterator
     */
    public function getIterator(): ReservaIterator
    {
        return new ReservaIterator($this->reservas);
    }

    /**
     * Iterator inverso (más recientes primero)
     */
    public function getReverseIterator(): ReservaIterator
    {
        return new ReservaIterator($this->reservas->reverse());
    }

    /**
     * Agregar reserva
     */
    public function agregar(Reserva $reserva): void
    {
        $this->reservas->push($reserva);
    }

    /**
     * Remover reserva
     */
    public function remover(int $reservaId): void
    {
        $this->reservas = $this->reservas->reject(fn ($r) => $r->id === $reservaId);
    }

    public function count(): int
    {
        return $this->reservas->count();
    }
}

/**
 * Aggregate - Colección de habitaciones
 */
class HabitacionCollection
{
    private Collection $habitaciones;

    public function __construct(Collection $habitaciones)
    {
        $this->habitaciones = $habitaciones;
    }

    /**
     * Crear iterator
     */
    public function getIterator(): HabitacionIterator
    {
        return new HabitacionIterator($this->habitaciones);
    }

    /**
     * Iterator ordenado por número
     */
    public function getIteratorOrdenadoPorNumero(): HabitacionIterator
    {
        $ordenadas = $this->habitaciones->sortBy('numero');

        return new HabitacionIterator($ordenadas);
    }

    /**
     * Iterator ordenado por precio
     */
    public function getIteratorOrdenadoPorPrecio(): HabitacionIterator
    {
        $ordenadas = $this->habitaciones->sortBy('precio_base');

        return new HabitacionIterator($ordenadas);
    }

    /**
     * Agregar habitación
     */
    public function agregar(Habitacion $habitacion): void
    {
        $this->habitaciones->push($habitacion);
    }

    public function count(): int
    {
        return $this->habitaciones->count();
    }
}
