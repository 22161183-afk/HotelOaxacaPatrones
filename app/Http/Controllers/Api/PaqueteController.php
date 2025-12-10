<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Patterns\Structural\PaquetesCatalogo;
use Illuminate\Http\Request;

/**
 * Controlador de Paquetes de Servicios
 *
 * Maneja los paquetes de servicios usando el patrón Composite
 */
class PaqueteController extends Controller
{
    /**
     * Listar todos los paquetes disponibles
     */
    public function index(Request $request)
    {
        try {
            $paquetes = [
                PaquetesCatalogo::crearPaqueteRomantico(),
                PaquetesCatalogo::crearPaqueteFamiliar(),
                PaquetesCatalogo::crearPaqueteBusiness(),
                PaquetesCatalogo::crearPaqueteAllInclusive(),
            ];

            $resultado = [];
            foreach ($paquetes as $paquete) {
                $resultado[] = [
                    'nombre' => $paquete->getNombre(),
                    'descripcion' => $paquete->getDescripcion(),
                    'precio' => $paquete->getPrecio(),
                    'descuento' => $paquete->getDescuento(),
                    'servicios' => $paquete->listarServicios(),
                ];
            }

            return response()->json([
                'success' => true,
                'paquetes' => $resultado,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener detalles de un paquete específico
     */
    public function show(Request $request, $tipo)
    {
        try {
            $paquete = match ($tipo) {
                'romantico' => PaquetesCatalogo::crearPaqueteRomantico(),
                'familiar' => PaquetesCatalogo::crearPaqueteFamiliar(),
                'business' => PaquetesCatalogo::crearPaqueteBusiness(),
                'all-inclusive' => PaquetesCatalogo::crearPaqueteAllInclusive(),
                default => null,
            };

            if (! $paquete) {
                return response()->json([
                    'success' => false,
                    'error' => 'Paquete no encontrado',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'paquete' => [
                    'nombre' => $paquete->getNombre(),
                    'descripcion' => $paquete->getDescripcion(),
                    'precio_original' => $paquete->getPrecioSinDescuento(),
                    'precio_final' => $paquete->getPrecio(),
                    'descuento' => $paquete->getDescuento(),
                    'ahorro' => $paquete->getPrecioSinDescuento() - $paquete->getPrecio(),
                    'servicios' => $paquete->listarServicios(),
                    'servicios_detallados' => $paquete->mostrarDetalles(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Comparar paquetes
     */
    public function comparar(Request $request)
    {
        try {
            $paquetes = [
                'romantico' => PaquetesCatalogo::crearPaqueteRomantico(),
                'familiar' => PaquetesCatalogo::crearPaqueteFamiliar(),
                'business' => PaquetesCatalogo::crearPaqueteBusiness(),
                'all-inclusive' => PaquetesCatalogo::crearPaqueteAllInclusive(),
            ];

            $comparacion = [];
            foreach ($paquetes as $key => $paquete) {
                $comparacion[$key] = [
                    'nombre' => $paquete->getNombre(),
                    'precio' => $paquete->getPrecio(),
                    'descuento' => $paquete->getDescuento(),
                    'cantidad_servicios' => count($paquete->listarServicios()),
                    'servicios' => $paquete->listarServicios(),
                ];
            }

            return response()->json([
                'success' => true,
                'comparacion' => $comparacion,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcular precio de paquete personalizado
     */
    public function calcularPersonalizado(Request $request)
    {
        $request->validate([
            'servicios' => 'required|array|min:1',
            'servicios.*' => 'required|string',
            'descuento' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            // Aquí podrías crear un paquete personalizado dinámicamente
            // usando los servicios seleccionados por el usuario

            $servicios = $request->servicios;
            $descuento = $request->descuento ?? 0;

            // Simular precios de servicios
            $preciosBase = [
                'Spa' => 1200.00,
                'Desayuno' => 250.00,
                'Champagne' => 450.00,
                'Transporte' => 400.00,
                'Lavandería' => 300.00,
                'Entretenimiento' => 500.00,
            ];

            $precioTotal = 0;
            $serviciosDetalle = [];

            foreach ($servicios as $servicio) {
                if (isset($preciosBase[$servicio])) {
                    $precioTotal += $preciosBase[$servicio];
                    $serviciosDetalle[] = [
                        'nombre' => $servicio,
                        'precio' => $preciosBase[$servicio],
                    ];
                }
            }

            $precioConDescuento = $precioTotal * (1 - ($descuento / 100));

            return response()->json([
                'success' => true,
                'paquete_personalizado' => [
                    'servicios' => $serviciosDetalle,
                    'precio_base' => $precioTotal,
                    'descuento' => $descuento,
                    'precio_final' => $precioConDescuento,
                    'ahorro' => $precioTotal - $precioConDescuento,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
