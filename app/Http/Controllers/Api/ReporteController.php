<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Patterns\Behavioral\ReporteGenerator;
use Illuminate\Http\Request;

/**
 * Controlador de Reportes y Estadísticas
 *
 * Expone los endpoints para generar reportes usando el patrón Visitor
 */
class ReporteController extends Controller
{
    /**
     * Generar reporte de ingresos
     */
    public function ingresos(Request $request)
    {
        try {
            $reporte = ReporteGenerator::generarReporteIngresos();

            return response()->json([
                'success' => true,
                'reporte' => $reporte,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar reporte de ocupación
     */
    public function ocupacion(Request $request)
    {
        try {
            $reporte = ReporteGenerator::generarReporteOcupacion();

            return response()->json([
                'success' => true,
                'reporte' => $reporte,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar reporte de clientes
     */
    public function clientes(Request $request)
    {
        try {
            $reporte = ReporteGenerator::generarReporteClientes();

            return response()->json([
                'success' => true,
                'reporte' => $reporte,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar reporte de métodos de pago
     */
    public function metodosPago(Request $request)
    {
        try {
            $reporte = ReporteGenerator::generarReporteMetodosPago();

            return response()->json([
                'success' => true,
                'reporte' => $reporte,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar reporte consolidado
     */
    public function consolidado(Request $request)
    {
        try {
            $reporte = ReporteGenerator::generarReporteConsolidado();

            return response()->json([
                'success' => true,
                'reporte' => $reporte,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener resumen rápido del dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $consolidado = ReporteGenerator::generarReporteConsolidado();

            // Extraer métricas clave para el dashboard
            $dashboard = [
                'ingresos_totales' => $consolidado['ingresos']['total_ingresos'] ?? 0,
                'reservas_activas' => $consolidado['ingresos']['total_reservas'] ?? 0,
                'tasa_cancelacion' => $consolidado['ingresos']['tasa_cancelacion'] ?? 0,
                'ocupacion_actual' => $consolidado['ocupacion']['tasa_ocupacion'] ?? 0,
                'habitaciones_disponibles' => $consolidado['ocupacion']['disponibles'] ?? 0,
                'clientes_vip' => $consolidado['clientes']['clientes_vip'] ?? 0,
                'total_clientes' => $consolidado['clientes']['total_clientes'] ?? 0,
            ];

            return response()->json([
                'success' => true,
                'dashboard' => $dashboard,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
