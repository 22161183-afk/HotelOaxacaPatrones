<?php

namespace App\Services;

use App\Events\ReservaCancelada;
use App\Events\ReservaConfirmada;
use App\Events\ReservaCreada;
use App\Models\Habitacion;
use App\Models\Reserva;

class ReservaService
{
    public function crearReserva($datos)
    {
        // Validaciones
        if (! $this->verificarDisponibilidad(
            $datos['habitacion_id'],
            $datos['fecha_inicio'],
            $datos['fecha_fin']
        )) {
            throw new \Exception('Habitación no disponible en esas fechas');
        }

        // Crear reserva
        $reserva = Reserva::create([
            'cliente_id' => $datos['cliente_id'],
            'habitacion_id' => $datos['habitacion_id'],
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'precio_total' => 0,
            'estado' => 'pendiente',
            'observaciones' => $datos['observaciones'] ?? null,
            'numero_huespedes' => $datos['numero_huespedes'] ?? 1,
        ]);

        // Agregar servicios si vienen
        if (isset($datos['servicios'])) {
            foreach ($datos['servicios'] as $servicioId) {
                $reserva->servicios()->attach($servicioId);
            }
        }

        // Calcular precio final
        $reserva->precio_total = $reserva->calcularPrecioFinal();
        $reserva->save();

        // Disparar evento
        event(new ReservaCreada($reserva));

        return $reserva;
    }

    public function confirmarReserva($reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);

        if ($reserva->estado !== 'pendiente') {
            throw new \Exception('Solo se pueden confirmar reservas pendientes');
        }

        $reserva->update(['estado' => 'confirmada']);

        event(new ReservaConfirmada($reserva));

        return $reserva;
    }

    public function cancelarReserva($reservaId, $razon = null)
    {
        $reserva = Reserva::findOrFail($reservaId);

        $reserva->update(['estado' => 'cancelada']);

        event(new ReservaCancelada($reserva));

        return $reserva;
    }

    public function modificarReserva($reservaId, $datos)
    {
        $reserva = Reserva::findOrFail($reservaId);

        // Validar si la reserva se puede modificar
        if ($reserva->estado !== 'pendiente') {
            throw new \Exception('Solo se pueden modificar reservas pendientes');
        }

        // Verificar nueva disponibilidad
        if (isset($datos['fecha_inicio'], $datos['fecha_fin'])) {
            if (! $this->verificarDisponibilidad(
                $reserva->habitacion_id,
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $reserva->id
            )) {
                throw new \Exception('Las nuevas fechas no están disponibles');
            }
        }

        $reserva->update($datos);

        return $reserva;
    }

    public function verificarDisponibilidad($habitacionId, $fechaInicio, $fechaFin, $exceptoReservaId = null)
    {
        $habitacion = Habitacion::findOrFail($habitacionId);

        $query = Reserva::where('habitacion_id', $habitacionId)
            ->where('estado', '!=', 'cancelada')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($subQ) use ($fechaInicio, $fechaFin) {
                        $subQ->where('fecha_inicio', '<', $fechaInicio)
                            ->where('fecha_fin', '>', $fechaFin);
                    });
            });

        if ($exceptoReservaId) {
            $query->where('id', '!=', $exceptoReservaId);
        }

        return $query->count() === 0;
    }

    public function agregarServicio($reservaId, $servicioId)
    {
        $reserva = Reserva::findOrFail($reservaId);

        if ($reserva->servicios->contains($servicioId)) {
            throw new \Exception('El servicio ya está agregado a esta reserva');
        }

        $reserva->servicios()->attach($servicioId);
        $reserva->precio_total = $reserva->calcularPrecioFinal();
        $reserva->save();

        return $reserva;
    }

    public function quitarServicio($reservaId, $servicioId)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $reserva->servicios()->detach($servicioId);
        $reserva->precio_total = $reserva->calcularPrecioFinal();
        $reserva->save();

        return $reserva;
    }

    public function obtenerReservasPorFechas($fechaInicio, $fechaFin)
    {
        return Reserva::whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
            ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
            ->with('cliente', 'habitacion', 'servicios')
            ->orderBy('fecha_inicio')
            ->get();
    }
}
