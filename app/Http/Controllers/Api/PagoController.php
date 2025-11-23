<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Services\PagoService;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    protected $pagoService;

    public function __construct(PagoService $pagoService)
    {
        $this->pagoService = $pagoService;
    }

    public function index(Request $request)
    {
        if ($request->reserva_id) {
            $pagos = $this->pagoService->obtenerPagosPorReserva($request->reserva_id);

            return response()->json($pagos);
        }

        $pagos = Pago::with('reserva', 'metodoPago')->paginate(10);

        return response()->json($pagos);
    }

    public function store(Request $request)
    {
        try {
            $pago = $this->pagoService->procesarPago(
                $request->reserva_id,
                $request->metodo_pago_id,
                $request->all()
            );

            return response()->json([
                'mensaje' => 'Pago procesado exitosamente',
                'pago' => $pago,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $pago = Pago::with('reserva', 'metodoPago')->findOrFail($id);

        return response()->json($pago);
    }

    public function refund($id)
    {
        try {
            $reembolso = $this->pagoService->procesarReembolso($id);

            return response()->json([
                'mensaje' => 'Reembolso procesado',
                'reembolso' => $reembolso,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
