<?php

namespace App\Patterns\Behavioral;

use Illuminate\Database\Eloquent\Builder;

/**
 * Patrón Interpreter para búsqueda avanzada de habitaciones
 *
 * Define una gramática para búsquedas complejas y proporciona un intérprete
 * que usa la gramática para interpretar criterios de búsqueda.
 */
interface SearchExpression
{
    public function interpret(Builder $query): Builder;
}

/**
 * Expresión: Búsqueda por tipo de habitación
 */
class TipoHabitacionExpression implements SearchExpression
{
    private $tipoId;

    public function __construct($tipoId)
    {
        $this->tipoId = $tipoId;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->tipoId) {
            return $query->where('tipo_habitacion_id', $this->tipoId);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por capacidad
 */
class CapacidadExpression implements SearchExpression
{
    private $capacidad;

    public function __construct($capacidad)
    {
        $this->capacidad = $capacidad;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->capacidad) {
            return $query->where('capacidad', '>=', $this->capacidad);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por rango de precio
 */
class PrecioExpression implements SearchExpression
{
    private $precioMin;

    private $precioMax;

    public function __construct($precioMin = null, $precioMax = null)
    {
        $this->precioMin = $precioMin;
        $this->precioMax = $precioMax;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->precioMin) {
            $query = $query->where('precio_base', '>=', $this->precioMin);
        }

        if ($this->precioMax) {
            $query = $query->where('precio_base', '<=', $this->precioMax);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por piso
 */
class PisoExpression implements SearchExpression
{
    private $piso;

    public function __construct($piso)
    {
        $this->piso = $piso;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->piso) {
            return $query->where('piso', $this->piso);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por amenidades
 */
class AmenidadesExpression implements SearchExpression
{
    private $amenidades;

    public function __construct(array $amenidades = [])
    {
        $this->amenidades = $amenidades;
    }

    public function interpret(Builder $query): Builder
    {
        if (! empty($this->amenidades)) {
            foreach ($this->amenidades as $amenidad) {
                $query = $query->whereJsonContains('amenidades', $amenidad);
            }
        }

        return $query;
    }
}

/**
 * Expresión: Solo habitaciones disponibles
 */
class DisponibleExpression implements SearchExpression
{
    public function interpret(Builder $query): Builder
    {
        return $query->where('estado', 'disponible');
    }
}

/**
 * Expresión AND: Combina múltiples expresiones con lógica AND
 */
class AndExpression implements SearchExpression
{
    private array $expressions;

    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    public function interpret(Builder $query): Builder
    {
        foreach ($this->expressions as $expression) {
            $query = $expression->interpret($query);
        }

        return $query;
    }
}

/**
 * Context - Intérprete de búsqueda de habitaciones
 */
class HabitacionSearchInterpreter
{
    private array $expressions = [];

    /**
     * Agregar expresión de búsqueda
     */
    public function addExpression(SearchExpression $expression): self
    {
        $this->expressions[] = $expression;

        return $this;
    }

    /**
     * Construir búsqueda desde parámetros de request
     */
    public static function fromRequest(array $params): self
    {
        $interpreter = new self;

        // Siempre filtrar solo disponibles
        $interpreter->addExpression(new DisponibleExpression);

        // Tipo de habitación
        if (! empty($params['tipo'])) {
            $interpreter->addExpression(new TipoHabitacionExpression($params['tipo']));
        }

        // Capacidad
        if (! empty($params['capacidad'])) {
            $interpreter->addExpression(new CapacidadExpression($params['capacidad']));
        }

        // Rango de precio
        if (! empty($params['precio_min']) || ! empty($params['precio_max'])) {
            $interpreter->addExpression(new PrecioExpression(
                $params['precio_min'] ?? null,
                $params['precio_max'] ?? null
            ));
        }

        // Piso
        if (! empty($params['piso'])) {
            $interpreter->addExpression(new PisoExpression($params['piso']));
        }

        // Amenidades
        if (! empty($params['amenidades']) && is_array($params['amenidades'])) {
            $interpreter->addExpression(new AmenidadesExpression($params['amenidades']));
        }

        return $interpreter;
    }

    /**
     * Ejecutar interpretación sobre query
     */
    public function interpret(Builder $query): Builder
    {
        $andExpression = new AndExpression($this->expressions);

        return $andExpression->interpret($query);
    }
}
