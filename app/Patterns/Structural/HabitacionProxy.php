<?php

namespace App\Patterns\Structural;

use App\Models\Habitacion;

/**
 * Patrón Proxy para carga diferida de recursos pesados
 *
 * Viñeta 13: Cargar recursos pesados (imágenes, detalles) solo cuando el usuario los solicite
 * Implementa lazy loading de imágenes y detalles de habitaciones
 */

/**
 * Subject - Interfaz común para RealSubject y Proxy
 */
interface HabitacionInterface
{
    public function getId(): int;

    public function getNumero(): string;

    public function getTipoNombre(): string;

    public function getPrecio(): float;

    public function getEstado(): string;

    public function getImagenes(): array;

    public function getDetallesCompletos(): array;

    public function getAmenidades(): array;
}

/**
 * RealSubject - Habitación real con todos los datos
 */
class HabitacionReal implements HabitacionInterface
{
    private Habitacion $habitacion;

    private ?array $imagenes = null;

    private ?array $detalles = null;

    private ?array $amenidades = null;

    public function __construct(int $habitacionId)
    {
        // Cargar solo datos básicos inicialmente
        $this->habitacion = Habitacion::select('id', 'numero', 'tipo_habitacion_id', 'precio_base', 'estado')
            ->findOrFail($habitacionId);
    }

    public function getId(): int
    {
        return $this->habitacion->id;
    }

    public function getNumero(): string
    {
        return $this->habitacion->numero;
    }

    public function getTipoNombre(): string
    {
        return $this->habitacion->tipoHabitacion->nombre;
    }

    public function getPrecio(): float
    {
        return $this->habitacion->precio_base;
    }

    public function getEstado(): string
    {
        return $this->habitacion->estado;
    }

    /**
     * Carga diferida de imágenes (recurso pesado)
     */
    public function getImagenes(): array
    {
        if ($this->imagenes === null) {
            // Simular carga pesada de imágenes
            $this->cargarImagenes();
        }

        return $this->imagenes;
    }

    /**
     * Carga diferida de detalles completos
     */
    public function getDetallesCompletos(): array
    {
        if ($this->detalles === null) {
            $this->cargarDetalles();
        }

        return $this->detalles;
    }

    /**
     * Carga diferida de amenidades
     */
    public function getAmenidades(): array
    {
        if ($this->amenidades === null) {
            $this->cargarAmenidades();
        }

        return $this->amenidades;
    }

    /**
     * Cargar imágenes de la galería
     */
    private function cargarImagenes(): void
    {
        // Recargar habitación con relaciones necesarias
        $habitacion = Habitacion::with('tipoHabitacion')->find($this->habitacion->id);

        $this->imagenes = [
            'principal' => \App\Patterns\Creational\HabitacionImagenFactory::obtenerImagenPrincipal($habitacion),
            'galeria' => \App\Patterns\Creational\HabitacionImagenFactory::obtenerGaleriaCompleta($habitacion),
            'miniaturas' => array_map(
                fn ($url) => \App\Patterns\Creational\HabitacionImagenFactory::crearMiniatura($url),
                \App\Patterns\Creational\HabitacionImagenFactory::obtenerGaleriaCompleta($habitacion)
            ),
        ];
    }

    /**
     * Cargar detalles completos de la habitación
     */
    private function cargarDetalles(): void
    {
        $habitacion = Habitacion::with(['tipoHabitacion', 'reservas' => function ($query) {
            $query->whereIn('estado', ['pendiente', 'confirmada'])
                ->orderBy('fecha_inicio');
        }])->find($this->habitacion->id);

        $this->detalles = [
            'id' => $habitacion->id,
            'numero' => $habitacion->numero,
            'tipo' => $habitacion->tipoHabitacion->nombre,
            'tipo_descripcion' => $habitacion->tipoHabitacion->descripcion ?? '',
            'piso' => $habitacion->piso,
            'capacidad' => $habitacion->capacidad,
            'precio_base' => $habitacion->precio_base,
            'estado' => $habitacion->estado,
            'descripcion' => $habitacion->descripcion ?? '',
            'imagen_url' => $habitacion->imagen_url,
            'created_at' => $habitacion->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $habitacion->updated_at->format('Y-m-d H:i:s'),
            'reservas_futuras' => $habitacion->reservas->count(),
            'proxima_disponibilidad' => $this->calcularProximaDisponibilidad($habitacion),
        ];
    }

    /**
     * Cargar amenidades
     */
    private function cargarAmenidades(): void
    {
        $habitacion = Habitacion::find($this->habitacion->id);
        $this->amenidades = is_array($habitacion->amenidades) ? $habitacion->amenidades : [];
    }

    /**
     * Calcular próxima disponibilidad
     */
    private function calcularProximaDisponibilidad(Habitacion $habitacion): ?string
    {
        $proximaReserva = $habitacion->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where('fecha_inicio', '>', now())
            ->orderBy('fecha_inicio')
            ->first();

        if ($proximaReserva) {
            return $proximaReserva->fecha_fin->format('Y-m-d');
        }

        return null;
    }
}

/**
 * Proxy - Control de acceso y carga diferida
 */
class HabitacionProxy implements HabitacionInterface
{
    private ?HabitacionReal $habitacionReal = null;

    private int $habitacionId;

    private array $cache = [];

    private array $accessLog = [];

    public function __construct(int $habitacionId)
    {
        $this->habitacionId = $habitacionId;
    }

    /**
     * Lazy initialization del objeto real
     */
    private function getHabitacionReal(): HabitacionReal
    {
        if ($this->habitacionReal === null) {
            $this->log('Inicializando habitación real');
            $this->habitacionReal = new HabitacionReal($this->habitacionId);
        }

        return $this->habitacionReal;
    }

    public function getId(): int
    {
        return $this->getHabitacionReal()->getId();
    }

    public function getNumero(): string
    {
        if (! isset($this->cache['numero'])) {
            $this->log('Cargando número');
            $this->cache['numero'] = $this->getHabitacionReal()->getNumero();
        }

        return $this->cache['numero'];
    }

    public function getTipoNombre(): string
    {
        if (! isset($this->cache['tipo'])) {
            $this->log('Cargando tipo');
            $this->cache['tipo'] = $this->getHabitacionReal()->getTipoNombre();
        }

        return $this->cache['tipo'];
    }

    public function getPrecio(): float
    {
        if (! isset($this->cache['precio'])) {
            $this->log('Cargando precio');
            $this->cache['precio'] = $this->getHabitacionReal()->getPrecio();
        }

        return $this->cache['precio'];
    }

    public function getEstado(): string
    {
        if (! isset($this->cache['estado'])) {
            $this->log('Cargando estado');
            $this->cache['estado'] = $this->getHabitacionReal()->getEstado();
        }

        return $this->cache['estado'];
    }

    /**
     * Carga diferida de imágenes con logging
     */
    public function getImagenes(): array
    {
        if (! isset($this->cache['imagenes'])) {
            $this->log('⚠️ RECURSO PESADO: Cargando imágenes');
            $this->cache['imagenes'] = $this->getHabitacionReal()->getImagenes();
        }

        return $this->cache['imagenes'];
    }

    /**
     * Carga diferida de detalles completos con logging
     */
    public function getDetallesCompletos(): array
    {
        if (! isset($this->cache['detalles'])) {
            $this->log('⚠️ RECURSO PESADO: Cargando detalles completos');
            $this->cache['detalles'] = $this->getHabitacionReal()->getDetallesCompletos();
        }

        return $this->cache['detalles'];
    }

    /**
     * Carga diferida de amenidades con logging
     */
    public function getAmenidades(): array
    {
        if (! isset($this->cache['amenidades'])) {
            $this->log('Cargando amenidades');
            $this->cache['amenidades'] = $this->getHabitacionReal()->getAmenidades();
        }

        return $this->cache['amenidades'];
    }

    /**
     * Logging de accesos
     */
    private function log(string $accion): void
    {
        $this->accessLog[] = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'accion' => $accion,
            'habitacion_id' => $this->habitacionId,
        ];
    }

    /**
     * Obtener log de accesos
     */
    public function getAccessLog(): array
    {
        return $this->accessLog;
    }

    /**
     * Limpiar cache
     */
    public function limpiarCache(): void
    {
        $this->cache = [];
        $this->log('Cache limpiado');
    }

    /**
     * Obtener estadísticas de cache
     */
    public function getEstadisticasCache(): array
    {
        return [
            'items_cacheados' => count($this->cache),
            'keys_cacheadas' => array_keys($this->cache),
            'total_accesos' => count($this->accessLog),
        ];
    }
}

/**
 * Factory para crear proxies de habitaciones
 */
class HabitacionProxyFactory
{
    private static array $proxies = [];

    /**
     * Obtener proxy (singleton por habitación)
     */
    public static function obtenerProxy(int $habitacionId): HabitacionProxy
    {
        if (! isset(self::$proxies[$habitacionId])) {
            self::$proxies[$habitacionId] = new HabitacionProxy($habitacionId);
        }

        return self::$proxies[$habitacionId];
    }

    /**
     * Obtener múltiples proxies
     */
    public static function obtenerProxies(array $habitacionIds): array
    {
        $proxies = [];
        foreach ($habitacionIds as $id) {
            $proxies[] = self::obtenerProxy($id);
        }

        return $proxies;
    }

    /**
     * Limpiar todos los proxies
     */
    public static function limpiarTodos(): void
    {
        foreach (self::$proxies as $proxy) {
            $proxy->limpiarCache();
        }
        self::$proxies = [];
    }

    /**
     * Obtener estadísticas globales
     */
    public static function getEstadisticasGlobales(): array
    {
        return [
            'total_proxies_creados' => count(self::$proxies),
            'habitaciones_ids' => array_keys(self::$proxies),
        ];
    }
}
