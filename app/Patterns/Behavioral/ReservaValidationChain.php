<?php

namespace App\Patterns\Behavioral;

use App\Models\Cliente;
use App\Models\Habitacion;
use Carbon\Carbon;

/**
 * Patrón Chain of Responsibility para validación de reservas
 *
 * Viñeta 14: El proceso de validación de una reserva deberá ejecutarse por etapas
 * y ser fácilmente extensible
 */

/**
 * Resultado de validación
 */
class ValidationResult
{
    private bool $valido;

    private array $errores = [];

    private array $advertencias = [];

    private array $info = [];

    public function __construct(bool $valido = true)
    {
        $this->valido = $valido;
    }

    public function agregarError(string $error): self
    {
        $this->errores[] = $error;
        $this->valido = false;

        return $this;
    }

    public function agregarAdvertencia(string $advertencia): self
    {
        $this->advertencias[] = $advertencia;

        return $this;
    }

    public function agregarInfo(string $info): self
    {
        $this->info[] = $info;

        return $this;
    }

    public function esValido(): bool
    {
        return $this->valido;
    }

    public function getErrores(): array
    {
        return $this->errores;
    }

    public function getAdvertencias(): array
    {
        return $this->advertencias;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function getTodosMensajes(): array
    {
        return [
            'valido' => $this->valido,
            'errores' => $this->errores,
            'advertencias' => $this->advertencias,
            'info' => $this->info,
        ];
    }
}

/**
 * Handler abstracto
 */
abstract class ReservaValidationHandler
{
    protected ?ReservaValidationHandler $siguiente = null;

    /**
     * Establecer el siguiente handler en la cadena
     */
    public function setSiguiente(ReservaValidationHandler $handler): ReservaValidationHandler
    {
        $this->siguiente = $handler;

        return $handler;
    }

    /**
     * Procesar validación
     */
    public function validar(array $datos, ValidationResult $result): ValidationResult
    {
        // Ejecutar validación específica
        $this->validarEspecifico($datos, $result);

        // Pasar al siguiente si existe
        if ($this->siguiente !== null) {
            return $this->siguiente->validar($datos, $result);
        }

        return $result;
    }

    /**
     * Validación específica del handler (debe ser implementada por subclases)
     */
    abstract protected function validarEspecifico(array $datos, ValidationResult $result): void;

    /**
     * Obtener nombre del validador
     */
    abstract public function getNombre(): string;
}

/**
 * Handler 1: Validación de cliente
 */
class ClienteValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        if (! isset($datos['cliente_id']) && ! isset($datos['cliente'])) {
            $result->agregarError('Debe proporcionar información del cliente');

            return;
        }

        // Validar cliente existente o datos para nuevo cliente
        if (isset($datos['cliente_id'])) {
            $cliente = Cliente::find($datos['cliente_id']);
            if (! $cliente) {
                $result->agregarError('El cliente especificado no existe');
            } else {
                $result->agregarInfo("Cliente: {$cliente->nombre} {$cliente->apellido}");

                // Verificar historial del cliente
                $reservasAnteriores = $cliente->reservas()->count();
                if ($reservasAnteriores > 10) {
                    $result->agregarInfo('⭐ Cliente VIP - más de 10 reservas');
                }
            }
        } elseif (isset($datos['cliente'])) {
            // Validar datos de nuevo cliente
            if (empty($datos['cliente']['nombre'])) {
                $result->agregarError('El nombre del cliente es obligatorio');
            }
            if (empty($datos['cliente']['email'])) {
                $result->agregarError('El email del cliente es obligatorio');
            }
            if (empty($datos['cliente']['telefono'])) {
                $result->agregarAdvertencia('Se recomienda proporcionar teléfono del cliente');
            }
        }
    }

    public function getNombre(): string
    {
        return 'Validación de Cliente';
    }
}

/**
 * Handler 2: Validación de fechas
 */
class FechasValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        if (! isset($datos['fecha_inicio']) || ! isset($datos['fecha_fin'])) {
            $result->agregarError('Las fechas de inicio y fin son obligatorias');

            return;
        }

        try {
            $fechaInicio = Carbon::parse($datos['fecha_inicio']);
            $fechaFin = Carbon::parse($datos['fecha_fin']);

            // Validar que la fecha de inicio sea futura
            if ($fechaInicio->isPast()) {
                $result->agregarError('La fecha de inicio debe ser futura');
            }

            // Validar que fecha fin sea posterior a fecha inicio
            if ($fechaFin->lte($fechaInicio)) {
                $result->agregarError('La fecha de fin debe ser posterior a la fecha de inicio');
            }

            // Validar duración mínima (1 noche)
            $noches = $fechaInicio->diffInDays($fechaFin);
            if ($noches < 1) {
                $result->agregarError('La reserva debe ser de al menos 1 noche');
            }

            // Advertencia para estancias muy largas
            if ($noches > 30) {
                $result->agregarAdvertencia('Estancia mayor a 30 noches - considerar descuento especial');
            }

            // Advertencia para reservas muy adelantadas
            $diasHastaReserva = now()->diffInDays($fechaInicio);
            if ($diasHastaReserva > 365) {
                $result->agregarAdvertencia('Reserva con más de 1 año de anticipación');
            }

            // Info sobre temporada
            if (in_array($fechaInicio->month, [7, 8, 12])) {
                $result->agregarInfo('⚠️ Temporada alta - aplicar precio de temporada');
            }

            $result->agregarInfo("Duración: {$noches} noche(s)");

        } catch (\Exception $e) {
            $result->agregarError('Formato de fecha inválido');
        }
    }

    public function getNombre(): string
    {
        return 'Validación de Fechas';
    }
}

/**
 * Handler 3: Validación de habitación
 */
class HabitacionValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        if (! isset($datos['habitacion_id'])) {
            $result->agregarError('Debe especificar una habitación');

            return;
        }

        $habitacion = Habitacion::find($datos['habitacion_id']);
        if (! $habitacion) {
            $result->agregarError('La habitación especificada no existe');

            return;
        }

        // Validar estado de la habitación
        if ($habitacion->estado === 'mantenimiento') {
            $result->agregarError('La habitación está en mantenimiento');
        }

        // Validar capacidad
        $numeroHuespedes = $datos['numero_huespedes'] ?? 1;
        if ($numeroHuespedes > $habitacion->capacidad) {
            $result->agregarError("La habitación solo tiene capacidad para {$habitacion->capacidad} personas");
        }

        // Advertencia si está cerca del límite
        if ($numeroHuespedes == $habitacion->capacidad) {
            $result->agregarAdvertencia('Habitación al máximo de capacidad');
        }

        $result->agregarInfo("Habitación: {$habitacion->numero} - Tipo: {$habitacion->tipoHabitacion->nombre}");
    }

    public function getNombre(): string
    {
        return 'Validación de Habitación';
    }
}

/**
 * Handler 4: Validación de disponibilidad
 */
class DisponibilidadValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        if (! isset($datos['habitacion_id']) || ! isset($datos['fecha_inicio']) || ! isset($datos['fecha_fin'])) {
            return; // Ya validado en handlers anteriores
        }

        $habitacion = Habitacion::find($datos['habitacion_id']);
        if (! $habitacion) {
            return; // Ya validado en handler anterior
        }

        $fechaInicio = Carbon::parse($datos['fecha_inicio']);
        $fechaFin = Carbon::parse($datos['fecha_fin']);

        // Verificar conflictos con otras reservas
        $conflictos = $habitacion->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                    });
            })
            ->count();

        if ($conflictos > 0) {
            $result->agregarError('La habitación no está disponible para las fechas seleccionadas');
        } else {
            $result->agregarInfo('✓ Habitación disponible');
        }
    }

    public function getNombre(): string
    {
        return 'Validación de Disponibilidad';
    }
}

/**
 * Handler 5: Validación de servicios adicionales
 */
class ServiciosValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        if (! isset($datos['servicios']) || empty($datos['servicios'])) {
            $result->agregarInfo('Sin servicios adicionales');

            return;
        }

        $serviciosIds = $datos['servicios'];
        $servicios = \App\Models\Servicio::whereIn('id', $serviciosIds)->get();

        if ($servicios->count() !== count($serviciosIds)) {
            $result->agregarAdvertencia('Algunos servicios seleccionados no existen');
        }

        $totalServicios = $servicios->sum('precio');
        $result->agregarInfo('Servicios adicionales: $'.number_format($totalServicios, 2));
    }

    public function getNombre(): string
    {
        return 'Validación de Servicios';
    }
}

/**
 * Handler 6: Validación de políticas y reglas de negocio
 */
class PoliticasValidationHandler extends ReservaValidationHandler
{
    protected function validarEspecifico(array $datos, ValidationResult $result): void
    {
        $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();

        // Validar horarios de check-in/check-out
        if (isset($datos['hora_llegada'])) {
            $horaCheckin = $config->getHoraCheckin();
            $result->agregarInfo("Check-in desde las {$horaCheckin}");
        }

        // Información sobre políticas de cancelación
        $diasCancelacion = $config->getDiasCancelacion();
        $result->agregarInfo("Cancelación gratuita hasta {$diasCancelacion} días antes");

        // Advertencia si la reserva es muy pronto
        if (isset($datos['fecha_inicio'])) {
            $fechaInicio = Carbon::parse($datos['fecha_inicio']);
            $diasHasta = now()->diffInDays($fechaInicio);

            if ($diasHasta < 2) {
                $result->agregarAdvertencia('Reserva de última hora - verificar disponibilidad inmediata');
            }
        }
    }

    public function getNombre(): string
    {
        return 'Validación de Políticas';
    }
}

/**
 * Builder para construir la cadena de validación
 */
class ReservaValidationChainBuilder
{
    private array $handlers = [];

    /**
     * Agregar handler a la cadena
     */
    public function agregarHandler(ReservaValidationHandler $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * Construir cadena completa por defecto
     */
    public function construirCadenaCompleta(): ReservaValidationHandler
    {
        $this->handlers = [
            new ClienteValidationHandler,
            new FechasValidationHandler,
            new HabitacionValidationHandler,
            new DisponibilidadValidationHandler,
            new ServiciosValidationHandler,
            new PoliticasValidationHandler,
        ];

        return $this->construir();
    }

    /**
     * Construir cadena personalizada
     */
    public function construir(): ReservaValidationHandler
    {
        if (empty($this->handlers)) {
            throw new \Exception('No hay handlers en la cadena');
        }

        // Encadenar handlers
        for ($i = 0; $i < count($this->handlers) - 1; $i++) {
            $this->handlers[$i]->setSiguiente($this->handlers[$i + 1]);
        }

        return $this->handlers[0];
    }

    /**
     * Obtener handlers agregados
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}

/**
 * Servicio de validación de reservas
 */
class ReservaValidationService
{
    /**
     * Validar reserva con cadena completa
     */
    public static function validar(array $datos): ValidationResult
    {
        $builder = new ReservaValidationChainBuilder;
        $cadena = $builder->construirCadenaCompleta();

        $result = new ValidationResult;

        return $cadena->validar($datos, $result);
    }

    /**
     * Validar con cadena personalizada
     */
    public static function validarConHandlers(array $datos, array $handlers): ValidationResult
    {
        $builder = new ReservaValidationChainBuilder;

        foreach ($handlers as $handler) {
            $builder->agregarHandler($handler);
        }

        $cadena = $builder->construir();
        $result = new ValidationResult;

        return $cadena->validar($datos, $result);
    }

    /**
     * Validación rápida (solo campos obligatorios)
     */
    public static function validacionRapida(array $datos): ValidationResult
    {
        return self::validarConHandlers($datos, [
            new ClienteValidationHandler,
            new FechasValidationHandler,
            new HabitacionValidationHandler,
        ]);
    }
}
