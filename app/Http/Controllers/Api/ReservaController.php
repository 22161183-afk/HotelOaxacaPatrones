<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    protected $reservaService;

    public function __construct(ReservaService $reservaService)
    {
        $this->reservaService = $reservaService;
    }

    public function index(Request $request)
    {
        $query = Reserva::with('cliente', 'habitacion', 'servicios');

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->cliente_id) {
            $query->where('cliente_id', $request->cliente_id);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        try {
            $reserva = $this->reservaService->crearReserva($request->all());

            return response()->json($reserva->load('cliente', 'habitacion', 'servicios'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $reserva = Reserva::with('cliente', 'habitacion', 'servicios', 'pagos')
            ->findOrFail($id);

        return response()->json($reserva);
    }

    public function update(Request $request, $id)
    {
        try {
            $reserva = $this->reservaService->modificarReserva($id, $request->all());

            return response()->json($reserva);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->reservaService->cancelarReserva($id);

            return response()->json(['mensaje' => 'Reserva cancelada']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function confirmar($id)
    {
        try {
            $reserva = $this->reservaService->confirmarReserva($id);

            return response()->json(['mensaje' => 'Reserva confirmada', 'reserva' => $reserva]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function agregarServicio(Request $request, $id)
    {
        try {
            $reserva = $this->reservaService->agregarServicio($id, $request->servicio_id);

            return response()->json(['mensaje' => 'Servicio agregado', 'reserva' => $reserva]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function quitarServicio($id, $servicioId)
    {
        try {
            $reserva = $this->reservaService->quitarServicio($id, $servicioId);

            return response()->json(['mensaje' => 'Servicio quitado', 'reserva' => $reserva]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
