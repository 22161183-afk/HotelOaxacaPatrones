<?php

namespace App\Patterns\Creational;

use App\Models\Habitacion;

/**
 * Patrón Factory para generar galerías de imágenes de habitaciones
 *
 * Este patrón crea diferentes conjuntos de imágenes según el tipo de habitación,
 * permitiendo tener galerías personalizadas para cada categoría.
 */
class HabitacionImagenFactory
{
    /**
     * Mapeo de tipos de habitación a conjuntos de imágenes
     */
    private const IMAGEN_SETS = [
        'simple' => [
            'principal' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
            'galeria' => [
                'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800&h=600&fit=crop',
            ],
        ],
        'doble' => [
            'principal' => 'https://images.unsplash.com/photo-1631049035180-6f0c4e7b6163?w=800&h=600&fit=crop',
            'galeria' => [
                'https://images.unsplash.com/photo-1631049035180-6f0c4e7b6163?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
            ],
        ],
        'suite' => [
            'principal' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800&h=600&fit=crop',
            'galeria' => [
                'https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1591088398332-8a7791972843?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1616594266889-5d907ea6903c?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1596178060671-7a80f66a3c1e?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1566195992011-5f6b21e539aa?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=800&h=600&fit=crop',
            ],
        ],
        'presidencial' => [
            'principal' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800&h=600&fit=crop',
            'galeria' => [
                'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1560185127-6a5dd6f6d8c4?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049421450-348ccd7f8949?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800&h=600&fit=crop',
            ],
        ],
        'default' => [
            'principal' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
            'galeria' => [
                'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800&h=600&fit=crop',
            ],
        ],
    ];

    /**
     * Crear galería de imágenes para una habitación
     */
    public static function crearGaleria(Habitacion $habitacion): array
    {
        $tipoNormalizado = self::normalizarTipo($habitacion->tipoHabitacion->nombre);

        return self::IMAGEN_SETS[$tipoNormalizado] ?? self::IMAGEN_SETS['default'];
    }

    /**
     * Obtener imagen principal de una habitación
     */
    public static function obtenerImagenPrincipal(Habitacion $habitacion): string
    {
        // Si tiene imagen personalizada, usarla primero
        if (! empty($habitacion->imagen_url)) {
            return $habitacion->imagen_url;
        }

        $galeria = self::crearGaleria($habitacion);

        return $galeria['principal'];
    }

    /**
     * Obtener galería completa de imágenes
     */
    public static function obtenerGaleriaCompleta(Habitacion $habitacion): array
    {
        $galeria = self::crearGaleria($habitacion);

        return $galeria['galeria'];
    }

    /**
     * Normalizar nombre del tipo de habitación
     */
    private static function normalizarTipo(string $tipoNombre): string
    {
        $tipoLower = strtolower($tipoNombre);

        // Buscar coincidencias parciales
        if (str_contains($tipoLower, 'presidencial')) {
            return 'presidencial';
        }

        if (str_contains($tipoLower, 'suite')) {
            return 'suite';
        }

        if (str_contains($tipoLower, 'doble')) {
            return 'doble';
        }

        if (str_contains($tipoLower, 'simple') || str_contains($tipoLower, 'sencilla')) {
            return 'simple';
        }

        return 'default';
    }

    /**
     * Crear miniatura de imagen
     */
    public static function crearMiniatura(string $url): string
    {
        return str_replace('w=800&h=600', 'w=400&h=300', $url);
    }
}
