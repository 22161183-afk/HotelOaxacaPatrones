<?php

namespace App\Patterns\Behavioral;

use Illuminate\Database\Eloquent\Builder;

/**
 * Patrón Interpreter para búsqueda avanzada de reservas
 *
 * Define una gramática para búsquedas complejas de reservas y proporciona un intérprete
 * que usa la gramática para interpretar criterios de búsqueda.
 */

/**
 * Expresión: Búsqueda por estado de reserva
 */
class EstadoReservaExpression implements SearchExpression
{
    private $estado;

    public function __construct($estado)
    {
        $this->estado = $estado;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->estado) {
            return $query->where('estado', $this->estado);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por cliente
 */
class ClienteReservaExpression implements SearchExpression
{
    private $clienteNombre;

    public function __construct($clienteNombre)
    {
        $this->clienteNombre = $clienteNombre;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->clienteNombre) {
            return $query->whereHas('cliente', function ($q) {
                $q->where('nombre', 'like', "%{$this->clienteNombre}%")
                    ->orWhere('apellido', 'like', "%{$this->clienteNombre}%");
            });
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por habitación
 */
class HabitacionReservaExpression implements SearchExpression
{
    private $habitacionNumero;

    public function __construct($habitacionNumero)
    {
        $this->habitacionNumero = $habitacionNumero;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->habitacionNumero) {
            return $query->whereHas('habitacion', function ($q) {
                $q->where('numero', 'like', "%{$this->habitacionNumero}%");
            });
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por rango de fechas
 */
class FechaReservaExpression implements SearchExpression
{
    private $fechaInicio;

    private $fechaFin;

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function interpret(Builder $query): Builder
    {
        if ($this->fechaInicio) {
            $query = $query->where('fecha_inicio', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query = $query->where('fecha_fin', '<=', $this->fechaFin);
        }

        return $query;
    }
}

/**
 * Expresión: Búsqueda por rango de precio total
 */
class PrecioReservaExpression implements SearchExpression
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
            $query = $query->where('precio_total', '>=', $this->precioMin);
        }

        if ($this->precioMax) {
            $query = $query->where('precio_total', '<=', $this->precioMax);
        }

        return $query;
    }
}

/**
 * Intérprete principal de búsqueda de reservas
 */
class ReservaSearchInterpreter
{
    private $expressions = [];

    /**
     * Agregar una expresión de búsqueda
     */
    public function addExpression(SearchExpression $expression): self
    {
        $this->expressions[] = $expression;

        return $this;
    }

    /**
     * Interpretar todas las expresiones en el query
     */
    public function interpret(Builder $query): Builder
    {
        foreach ($this->expressions as $expression) {
            $query = $expression->interpret($query);
        }

        return $query;
    }

    /**
     * Factory method: Crear intérprete desde request
     */
    public static function fromRequest(array $params): self
    {
        $interpreter = new self;

        // Estado de la reserva
        if (! empty($params['estado'])) {
            $interpreter->addExpression(new EstadoReservaExpression($params['estado']));
        }

        // Cliente
        if (! empty($params['cliente'])) {
            $interpreter->addExpression(new ClienteReservaExpression($params['cliente']));
        }

        // Habitación
        if (! empty($params['habitacion'])) {
            $interpreter->addExpression(new HabitacionReservaExpression($params['habitacion']));
        }

        // Rango de fechas
        if (! empty($params['fecha_inicio']) || ! empty($params['fecha_fin'])) {
            $interpreter->addExpression(new FechaReservaExpression(
                $params['fecha_inicio'] ?? null,
                $params['fecha_fin'] ?? null
            ));
        }

        // Rango de precio
        if (! empty($params['precio_min']) || ! empty($params['precio_max'])) {
            $interpreter->addExpression(new PrecioReservaExpression(
                $params['precio_min'] ?? null,
                $params['precio_max'] ?? null
            ));
        }

        return $interpreter;
    }
}
