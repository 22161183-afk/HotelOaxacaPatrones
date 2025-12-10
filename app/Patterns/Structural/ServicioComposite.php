<?php

namespace App\Patterns\Structural;

use App\Models\Servicio;

/**
 * Patrón Composite para agrupar servicios en paquetes
 *
 * Permite tratar servicios individuales y paquetes de servicios de manera uniforme.
 * Viñeta 9: Agrupar servicios o productos en paquetes (ejemplo: paquete romántico)
 */
interface ServicioComponent
{
    public function getPrecio(): float;

    public function getNombre(): string;

    public function getDescripcion(): string;

    public function getServicios(): array;
}

/**
 * Hoja - Servicio individual
 */
class ServicioIndividual implements ServicioComponent
{
    private Servicio $servicio;

    private int $cantidad;

    public function __construct(Servicio $servicio, int $cantidad = 1)
    {
        $this->servicio = $servicio;
        $this->cantidad = $cantidad;
    }

    public function getPrecio(): float
    {
        return $this->servicio->precio * $this->cantidad;
    }

    public function getNombre(): string
    {
        $nombre = $this->servicio->nombre;
        if ($this->cantidad > 1) {
            $nombre .= " (x{$this->cantidad})";
        }

        return $nombre;
    }

    public function getDescripcion(): string
    {
        return $this->servicio->descripcion ?? '';
    }

    public function getServicios(): array
    {
        return [
            [
                'id' => $this->servicio->id,
                'nombre' => $this->servicio->nombre,
                'cantidad' => $this->cantidad,
                'precio' => $this->servicio->precio,
                'subtotal' => $this->getPrecio(),
            ],
        ];
    }

    public function getServicio(): Servicio
    {
        return $this->servicio;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }
}

/**
 * Compuesto - Paquete de servicios
 */
class PaqueteServicios implements ServicioComponent
{
    private string $nombre;

    private string $descripcion;

    private array $componentes = [];

    private float $descuento = 0; // Porcentaje de descuento

    public function __construct(string $nombre, string $descripcion, float $descuento = 0)
    {
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->descuento = $descuento;
    }

    /**
     * Agregar servicio al paquete
     */
    public function agregar(ServicioComponent $componente): self
    {
        $this->componentes[] = $componente;

        return $this;
    }

    /**
     * Remover servicio del paquete
     */
    public function remover(ServicioComponent $componente): self
    {
        $key = array_search($componente, $this->componentes, true);
        if ($key !== false) {
            unset($this->componentes[$key]);
            $this->componentes = array_values($this->componentes);
        }

        return $this;
    }

    /**
     * Obtener precio total con descuento
     */
    public function getPrecio(): float
    {
        $total = 0;
        foreach ($this->componentes as $componente) {
            $total += $componente->getPrecio();
        }

        // Aplicar descuento si existe
        if ($this->descuento > 0) {
            $total = $total * (1 - ($this->descuento / 100));
        }

        return $total;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        $desc = $this->descripcion;
        if ($this->descuento > 0) {
            $desc .= " (Descuento: {$this->descuento}%)";
        }

        return $desc;
    }

    /**
     * Obtener todos los servicios del paquete
     */
    public function getServicios(): array
    {
        $servicios = [];
        foreach ($this->componentes as $componente) {
            $servicios = array_merge($servicios, $componente->getServicios());
        }

        return $servicios;
    }

    /**
     * Obtener componentes del paquete
     */
    public function getComponentes(): array
    {
        return $this->componentes;
    }

    public function getDescuento(): float
    {
        return $this->descuento;
    }

    public function setDescuento(float $descuento): self
    {
        $this->descuento = $descuento;

        return $this;
    }
}

/**
 * Catálogo de paquetes predefinidos
 */
class PaquetesCatalogo
{
    /**
     * Paquete Romántico
     */
    public static function paqueteRomantico(): PaqueteServicios
    {
        $paquete = new PaqueteServicios(
            'Paquete Romántico',
            'Experiencia romántica completa para parejas',
            15 // 15% de descuento
        );

        // Obtener servicios (simulado - en producción vendría de BD)
        $spa = Servicio::where('nombre', 'like', '%Spa%')->first();
        $desayuno = Servicio::where('nombre', 'like', '%Desayuno%')->first();
        $champagne = Servicio::where('nombre', 'like', '%Champagne%')->orWhere('nombre', 'like', '%Vino%')->first();

        if ($spa) {
            $paquete->agregar(new ServicioIndividual($spa, 1));
        }
        if ($desayuno) {
            $paquete->agregar(new ServicioIndividual($desayuno, 2));
        }
        if ($champagne) {
            $paquete->agregar(new ServicioIndividual($champagne, 1));
        }

        return $paquete;
    }

    /**
     * Paquete Familiar
     */
    public static function paqueteFamiliar(): PaqueteServicios
    {
        $paquete = new PaqueteServicios(
            'Paquete Familiar',
            'Diversión para toda la familia',
            10 // 10% de descuento
        );

        $desayuno = Servicio::where('nombre', 'like', '%Desayuno%')->first();
        $entretenimiento = Servicio::where('nombre', 'like', '%Tour%')
            ->orWhere('nombre', 'like', '%Actividad%')->first();

        if ($desayuno) {
            $paquete->agregar(new ServicioIndividual($desayuno, 4));
        }
        if ($entretenimiento) {
            $paquete->agregar(new ServicioIndividual($entretenimiento, 1));
        }

        return $paquete;
    }

    /**
     * Paquete Business
     */
    public static function paqueteBusiness(): PaqueteServicios
    {
        $paquete = new PaqueteServicios(
            'Paquete Business',
            'Todo lo necesario para viajes de negocios',
            12 // 12% de descuento
        );

        $desayuno = Servicio::where('nombre', 'like', '%Desayuno%')->first();
        $lavanderia = Servicio::where('nombre', 'like', '%Lavandería%')->first();
        $transporte = Servicio::where('nombre', 'like', '%Transporte%')->first();

        if ($desayuno) {
            $paquete->agregar(new ServicioIndividual($desayuno, 3));
        }
        if ($lavanderia) {
            $paquete->agregar(new ServicioIndividual($lavanderia, 1));
        }
        if ($transporte) {
            $paquete->agregar(new ServicioIndividual($transporte, 2));
        }

        return $paquete;
    }

    /**
     * Paquete Completo - Paquete de paquetes
     */
    public static function paqueteCompleto(): PaqueteServicios
    {
        $paquete = new PaqueteServicios(
            'Paquete All Inclusive',
            'Experiencia completa con todos los servicios',
            20 // 20% de descuento
        );

        // Agregar otros paquetes (composición anidada)
        $paquete->agregar(self::paqueteRomantico());
        $paquete->agregar(self::paqueteFamiliar());

        return $paquete;
    }

    /**
     * Obtener todos los paquetes disponibles
     */
    public static function obtenerTodos(): array
    {
        return [
            'romantico' => self::paqueteRomantico(),
            'familiar' => self::paqueteFamiliar(),
            'business' => self::paqueteBusiness(),
            'completo' => self::paqueteCompleto(),
        ];
    }
}
