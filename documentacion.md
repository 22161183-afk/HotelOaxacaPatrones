# Documentación de Patrones de Diseño
## Sistema de Reservas Hotel Oaxaca

---

## Índice
1. [Patrones Creacionales](#patrones-creacionales)
   - [Singleton](#1-singleton---configuracionsingleton)
   - [Factory Method](#2-factory-method---habitacionfactory)
   - [Builder](#3-builder---reservabuilder)
   - [Prototype](#4-prototype---habitacionprototype)
   - [Factory (Imágenes)](#5-factory-habitacionimagenfactory)

2. [Patrones Estructurales](#patrones-estructurales)
   - [Facade](#6-facade---reservafacade)
   - [Decorator](#7-decorator---reservadecorator)
   - [Adapter](#8-adapter---pasarelapagoada pter)

3. [Patrones Comportamentales](#patrones-comportamentales)
   - [Strategy (Pagos)](#9-strategy---metodopagostrategy)
   - [Strategy (Precios)](#10-strategy---pricingstrategy)
   - [State (Reservas)](#11-state---reservastate)
   - [State (Habitaciones)](#12-state---habitacionstate)
   - [Command](#13-command---reservacommand)
   - [Interpreter (Habitaciones)](#14-interpreter---habitacionsearchinterpreter)
   - [Interpreter (Reservas)](#15-interpreter---reservasearchinterpreter)

---

# PATRONES CREACIONALES

## 1. Singleton - ConfiguracionSingleton

### Descripción
Garantiza una única instancia de configuración del hotel en toda la aplicación, evitando múltiples consultas a la base de datos y manteniendo consistencia.

### Ubicación
- **Archivo**: `app/Patterns/Creational/ConfiguracionSingleton.php`
- **Namespace**: `App\Patterns\Creational`

### Clases Incluidas
- `ConfiguracionSingleton` - Clase principal del patrón

### Métodos Principales
- `getInstance()` - Obtiene la instancia única
- `getConfiguracion()` - Obtiene la configuración del hotel
- `actualizarConfiguracion($datos)` - Actualiza la configuración
- `getHoraCheckin()` - Obtiene hora de check-in
- `getHoraCheckout()` - Obtiene hora de check-out
- `getImpuesto()` - Obtiene porcentaje de impuesto
- `getDiasCancelacion()` - Obtiene días de cancelación gratuita

### Dónde se Usa
- `app/Patterns/Creational/ReservaBuilder.php:121` - Cálculo de impuestos
- `app/Patterns/Behavioral/ReservaState.php:74` - Validación de cancelación
- `app/Patterns/Structural/ReservaFacade.php:142` - Políticas de cancelación

### Código Completo
```php
<?php

namespace App\Patterns\Creational;

use App\Models\ConfiguracionHotel;

/**
 * Patrón Singleton para la configuración del hotel
 *
 * Garantiza una única instancia de configuración en toda la aplicación.
 * Esto es útil para evitar múltiples consultas a la BD y mantener consistencia.
 */
class ConfiguracionSingleton
{
    private static ?ConfiguracionSingleton $instance = null;

    private ?ConfiguracionHotel $configuracion = null;

    /**
     * Constructor privado para evitar instanciación directa
     */
    private function __construct()
    {
        $this->configuracion = ConfiguracionHotel::first();
    }

    /**
     * Evitar clonación del objeto
     */
    private function __clone() {}

    /**
     * Evitar deserialización
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Obtener la instancia única de configuración
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Obtener la configuración del hotel
     */
    public function getConfiguracion(): ?ConfiguracionHotel
    {
        return $this->configuracion;
    }

    /**
     * Actualizar la configuración
     */
    public function actualizarConfiguracion(array $datos): ConfiguracionHotel
    {
        $this->configuracion->update($datos);

        return $this->configuracion;
    }

    /**
     * Obtener hora de check-in
     */
    public function getHoraCheckin(): string
    {
        return $this->configuracion->hora_checkin;
    }

    /**
     * Obtener hora de check-out
     */
    public function getHoraCheckout(): string
    {
        return $this->configuracion->hora_checkout;
    }

    /**
     * Obtener porcentaje de impuesto
     */
    public function getImpuesto(): float
    {
        return $this->configuracion->impuesto;
    }

    /**
     * Obtener días de cancelación gratuita
     */
    public function getDiasCancelacion(): int
    {
        return $this->configuracion->dias_cancelacion;
    }
}
```

### Ejemplo de Uso
```php
// Obtener configuración
$config = ConfiguracionSingleton::getInstance();
$impuesto = $config->getImpuesto();
$diasCancelacion = $config->getDiasCancelacion();
```

---

## 2. Factory Method - HabitacionFactory

### Descripción
Encapsula la lógica de creación de habitaciones de diferentes tipos (Deluxe, Standard, Suite Presidencial, Familiar). Cada factory tiene configuraciones predefinidas de amenidades y precios.

### Ubicación
- **Archivo**: `app/Patterns/Creational/HabitacionFactory.php`
- **Namespace**: `App\Patterns\Creational`

### Clases Incluidas
- `HabitacionFactory` - Clase abstracta base
- `HabitacionDeluxeFactory` - Factory para habitaciones Deluxe
- `HabitacionStandardFactory` - Factory para habitaciones Standard
- `SuitePresidencialFactory` - Factory para suites presidenciales
- `HabitacionFamiliarFactory` - Factory para habitaciones familiares
- `HabitacionFactoryCreator` - Creador que selecciona el factory apropiado

### Características por Tipo

#### Deluxe
- **Capacidad**: 2 personas
- **Precio base**: $1,500.00
- **Amenidades**: WiFi Premium, TV Smart 55", Minibar, Aire acondicionado, Caja fuerte, Cafetera Nespresso, Bata y pantuflas

#### Standard
- **Capacidad**: 2 personas
- **Precio base**: $800.00
- **Amenidades**: WiFi, TV por cable, Aire acondicionado, Baño privado

#### Suite Presidencial
- **Capacidad**: 4 personas
- **Precio base**: $3,000.00
- **Amenidades**: WiFi Premium, TV Smart 75", Minibar Premium, Jacuzzi, Terraza privada, Butler 24/7, Sistema de sonido, Sala de estar, Comedor, Cocina equipada

#### Familiar
- **Capacidad**: 5 personas
- **Precio base**: $1,200.00
- **Amenidades**: WiFi, TV Smart 50", Minibar, Balcón, Mesa de trabajo, Área de juegos para niños, Microondas

### Dónde se Usa
- `app/Console/Commands/CrearHabitacionesCommand.php` - Comando para crear habitaciones de forma masiva

### Código Completo
```php
<?php

namespace App\Patterns\Creational;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;

/**
 * Patrón Factory Method para crear habitaciones
 *
 * Encapsula la lógica de creación de habitaciones de diferentes tipos.
 */
abstract class HabitacionFactory
{
    /**
     * Método factory abstracto
     */
    abstract public function crearHabitacion(array $datos): Habitacion;

    /**
     * Configurar amenidades según el tipo
     */
    abstract protected function getAmenidades(): array;

    /**
     * Operación común para todas las fábricas
     */
    public function procesarCreacion(array $datos): Habitacion
    {
        $habitacion = $this->crearHabitacion($datos);
        $habitacion->amenidades = json_encode($this->getAmenidades());
        $habitacion->save();

        return $habitacion;
    }
}

/**
 * Factory para habitaciones Deluxe
 */
class HabitacionDeluxeFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoDeluxe = TipoHabitacion::where('nombre', 'Deluxe')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoDeluxe->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 2,
            'precio_base' => 1500.00,
            'descripcion' => 'Habitación Deluxe con todas las comodidades',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi Premium',
            'TV Smart 55"',
            'Minibar',
            'Aire acondicionado',
            'Caja fuerte',
            'Cafetera Nespresso',
            'Bata y pantuflas',
        ];
    }
}

/**
 * Factory para habitaciones Standard
 */
class HabitacionStandardFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoStandard = TipoHabitacion::where('nombre', 'Standard')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoStandard->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 2,
            'precio_base' => 800.00,
            'descripcion' => 'Habitación Standard confortable',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi',
            'TV por cable',
            'Aire acondicionado',
            'Baño privado',
        ];
    }
}

/**
 * Factory para Suite Presidencial
 */
class SuitePresidencialFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoSuite = TipoHabitacion::where('nombre', 'Suite Presidencial')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoSuite->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 4,
            'precio_base' => 3000.00,
            'descripcion' => 'Suite Presidencial de lujo con servicio premium',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi Premium',
            'TV Smart 75"',
            'Minibar Premium',
            'Jacuzzi',
            'Terraza privada',
            'Butler 24/7',
            'Sistema de sonido',
            'Sala de estar',
            'Comedor',
            'Cocina equipada',
        ];
    }
}

/**
 * Factory para habitaciones Familiares
 */
class HabitacionFamiliarFactory extends HabitacionFactory
{
    public function crearHabitacion(array $datos): Habitacion
    {
        $tipoFamiliar = TipoHabitacion::where('nombre', 'Familiar')->first();

        return Habitacion::create([
            'tipo_habitacion_id' => $tipoFamiliar->id,
            'numero' => $datos['numero'],
            'piso' => $datos['piso'],
            'capacidad' => 5,
            'precio_base' => 1200.00,
            'descripcion' => 'Habitación Familiar espaciosa',
            'estado' => 'disponible',
        ]);
    }

    protected function getAmenidades(): array
    {
        return [
            'WiFi',
            'TV Smart 50"',
            'Minibar',
            'Balcón',
            'Mesa de trabajo',
            'Área de juegos para niños',
            'Microondas',
        ];
    }
}

/**
 * Clase creadora que determina qué factory usar
 */
class HabitacionFactoryCreator
{
    public static function getFactory(string $tipo): HabitacionFactory
    {
        return match ($tipo) {
            'deluxe' => new HabitacionDeluxeFactory,
            'standard' => new HabitacionStandardFactory,
            'suite' => new SuitePresidencialFactory,
            'familiar' => new HabitacionFamiliarFactory,
            default => new HabitacionStandardFactory,
        };
    }
}
```

### Ejemplo de Uso
```php
// Crear habitación Deluxe
$factory = HabitacionFactoryCreator::getFactory('deluxe');
$habitacion = $factory->procesarCreacion([
    'numero' => '301',
    'piso' => 3
]);
```

---

## 3. Builder - ReservaBuilder

### Descripción
Permite construir objetos Reserva complejos paso a paso con una interfaz fluida. Facilita la creación de reservas con múltiples servicios, validaciones y cálculo automático de precios.

### Ubicación
- **Archivo**: `app/Patterns/Creational/ReservaBuilder.php`
- **Namespace**: `App\Patterns\Creational`

### Clases Incluidas
- `ReservaBuilder` - Constructor de reservas

### Métodos Principales
- `setCliente(Cliente $cliente)` - Establece el cliente
- `setHabitacion(Habitacion $habitacion)` - Establece la habitación
- `setFechas(string $inicio, string $fin)` - Establece fechas
- `setNumeroHuespedes(int $numero)` - Establece número de huéspedes
- `agregarServicio(Servicio $servicio, int $cantidad)` - Agrega servicios
- `setObservaciones(string $observaciones)` - Establece observaciones
- `build()` - Construye y guarda la reserva
- `reset()` - Resetea el builder

### Validaciones Automáticas
- Cliente obligatorio
- Habitación obligatoria
- Fechas obligatorias y válidas
- Número de huéspedes no puede exceder capacidad de la habitación

### Cálculos Automáticos
- Precio de habitación por número de noches
- Precio de servicios adicionales
- Impuestos (obtenidos de ConfiguracionSingleton)
- Precio total

### Dónde se Usa
- `app/Patterns/Structural/ReservaFacade.php:41-61` - Creación de reservas completas

### Código Completo
```php
<?php

namespace App\Patterns\Creational;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Servicio;
use Carbon\Carbon;

/**
 * Patrón Builder para construir reservas complejas
 *
 * Permite construir objetos Reserva paso a paso con una interfaz fluida.
 */
class ReservaBuilder
{
    private ?Cliente $cliente = null;

    private ?Habitacion $habitacion = null;

    private ?Carbon $fechaInicio = null;

    private ?Carbon $fechaFin = null;

    private int $numeroHuespedes = 1;

    private array $servicios = [];

    private ?string $observaciones = null;

    private float $precioTotal = 0;

    private float $precioServicios = 0;

    /**
     * Establecer cliente
     */
    public function setCliente(Cliente $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Establecer habitación
     */
    public function setHabitacion(Habitacion $habitacion): self
    {
        $this->habitacion = $habitacion;

        return $this;
    }

    /**
     * Establecer fechas de la reserva
     */
    public function setFechas(string $inicio, string $fin): self
    {
        $this->fechaInicio = Carbon::parse($inicio);
        $this->fechaFin = Carbon::parse($fin);

        return $this;
    }

    /**
     * Establecer número de huéspedes
     */
    public function setNumeroHuespedes(int $numero): self
    {
        $this->numeroHuespedes = $numero;

        return $this;
    }

    /**
     * Agregar servicio a la reserva
     */
    public function agregarServicio(Servicio $servicio, int $cantidad = 1): self
    {
        $this->servicios[] = [
            'servicio' => $servicio,
            'cantidad' => $cantidad,
        ];

        return $this;
    }

    /**
     * Establecer observaciones
     */
    public function setObservaciones(string $observaciones): self
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Calcular precio total
     */
    private function calcularPrecios(): void
    {
        // Calcular noches
        $noches = $this->fechaInicio->diffInDays($this->fechaFin);

        // Precio base de la habitación
        $precioHabitacion = $this->habitacion->precio_base * $noches;

        // Precio de servicios
        $this->precioServicios = 0;
        foreach ($this->servicios as $item) {
            $this->precioServicios += $item['servicio']->precio * $item['cantidad'];
        }

        // Subtotal
        $subtotal = $precioHabitacion + $this->precioServicios;

        // Aplicar impuestos
        $config = ConfiguracionSingleton::getInstance();
        $impuesto = $config->getImpuesto();
        $montoImpuesto = $subtotal * ($impuesto / 100);

        $this->precioTotal = $subtotal + $montoImpuesto;
    }

    /**
     * Validar datos de la reserva
     */
    private function validar(): bool
    {
        if (! $this->cliente) {
            throw new \Exception('Debe especificar un cliente');
        }

        if (! $this->habitacion) {
            throw new \Exception('Debe especificar una habitación');
        }

        if (! $this->fechaInicio || ! $this->fechaFin) {
            throw new \Exception('Debe especificar las fechas de la reserva');
        }

        if ($this->fechaInicio >= $this->fechaFin) {
            throw new \Exception('La fecha de inicio debe ser anterior a la fecha de fin');
        }

        if ($this->numeroHuespedes > $this->habitacion->capacidad) {
            throw new \Exception('El número de huéspedes excede la capacidad de la habitación');
        }

        return true;
    }

    /**
     * Construir y guardar la reserva
     */
    public function build(): Reserva
    {
        $this->validar();
        $this->calcularPrecios();

        // Crear la reserva
        $reserva = Reserva::create([
            'cliente_id' => $this->cliente->id,
            'habitacion_id' => $this->habitacion->id,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'numero_huespedes' => $this->numeroHuespedes,
            'estado' => 'pendiente',
            'precio_total' => $this->precioTotal,
            'precio_servicios' => $this->precioServicios,
            'observaciones' => $this->observaciones,
        ]);

        // Agregar servicios a la reserva
        foreach ($this->servicios as $item) {
            $reserva->servicios()->attach($item['servicio']->id, [
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['servicio']->precio,
                'subtotal' => $item['servicio']->precio * $item['cantidad'],
            ]);
        }

        return $reserva;
    }

    /**
     * Resetear el builder
     */
    public function reset(): self
    {
        $this->cliente = null;
        $this->habitacion = null;
        $this->fechaInicio = null;
        $this->fechaFin = null;
        $this->numeroHuespedes = 1;
        $this->servicios = [];
        $this->observaciones = null;
        $this->precioTotal = 0;
        $this->precioServicios = 0;

        return $this;
    }
}
```

### Ejemplo de Uso
```php
$builder = new ReservaBuilder();
$reserva = $builder
    ->setCliente($cliente)
    ->setHabitacion($habitacion)
    ->setFechas('2025-12-01', '2025-12-05')
    ->setNumeroHuespedes(2)
    ->agregarServicio($servicioSpa, 1)
    ->agregarServicio($servicioDesayuno, 4)
    ->setObservaciones('Cliente VIP')
    ->build();
```

---

## 4. Prototype - HabitacionPrototype

### Descripción
Permite crear nuevas habitaciones clonando una existente. Útil para crear rápidamente habitaciones similares con diferentes números o pisos.

### Ubicación
- **Archivo**: `app/Patterns/Creational/HabitacionPrototype.php`
- **Namespace**: `App\Patterns\Creational`

### Clases Incluidas
- `HabitacionPrototype` - Clase principal del patrón

### Métodos Principales
- `clonar(string $nuevoNumero)` - Clona habitación con nuevo número
- `clonarConModificaciones(string $nuevoNumero, array $cambios)` - Clona con modificaciones
- `clonarMultiples(array $numerosNuevos)` - Clona múltiples habitaciones
- `clonarEnOtroPiso(string $nuevoNumero, int $nuevoPiso)` - Clona en otro piso

### Características
- Copia todos los atributos de la habitación original
- Permite modificar atributos específicos
- Evita copiar ID y número de la habitación original
- Guarda automáticamente la nueva habitación

### Dónde se Usa
- `app/Http/Controllers/Admin/AdminDashboardController.php:385-442` - Clonación de habitaciones desde admin

### Rutas Relacionadas
- **POST** `/admin/habitaciones/{id}/clonar` - Clonar habitación

### Código Completo
```php
<?php

namespace App\Patterns\Creational;

use App\Models\Habitacion;

/**
 * Patrón Prototype para clonar habitaciones
 *
 * Permite crear nuevas habitaciones copiando una existente.
 * Útil para crear rápidamente habitaciones similares.
 */
class HabitacionPrototype
{
    private Habitacion $habitacion;

    public function __construct(Habitacion $habitacion)
    {
        $this->habitacion = $habitacion;
    }

    /**
     * Clonar una habitación existente con nuevo número
     */
    public function clonar(string $nuevoNumero): Habitacion
    {
        $nuevaHabitacion = $this->habitacion->replicate();
        $nuevaHabitacion->numero = $nuevoNumero;
        $nuevaHabitacion->save();

        return $nuevaHabitacion;
    }

    /**
     * Clonar habitación con modificaciones
     */
    public function clonarConModificaciones(
        string $nuevoNumero,
        array $cambios = []
    ): Habitacion {
        $nuevaHabitacion = $this->habitacion->replicate();
        $nuevaHabitacion->numero = $nuevoNumero;

        // Aplicar cambios específicos
        foreach ($cambios as $campo => $valor) {
            if ($campo !== 'id' && $campo !== 'numero') {
                $nuevaHabitacion->{$campo} = $valor;
            }
        }

        $nuevaHabitacion->save();

        return $nuevaHabitacion;
    }

    /**
     * Clonar múltiples habitaciones
     */
    public function clonarMultiples(
        array $numerosNuevos
    ): array {
        $habitaciones = [];

        foreach ($numerosNuevos as $numero) {
            $habitaciones[] = $this->clonar($numero);
        }

        return $habitaciones;
    }

    /**
     * Clonar habitación en otro piso
     */
    public function clonarEnOtroPiso(
        string $nuevoNumero,
        int $nuevoPiso
    ): Habitacion {
        return $this->clonarConModificaciones($nuevoNumero, [
            'piso' => $nuevoPiso,
        ]);
    }
}
```

### Ejemplo de Uso
```php
// Clonar habitación
$habitacionOriginal = Habitacion::find(1);
$prototype = new HabitacionPrototype($habitacionOriginal);
$nuevaHabitacion = $prototype->clonar('402');

// Clonar en otro piso
$habitacion Piso2 = $prototype->clonarEnOtroPiso('201', 2);

// Clonar con modificaciones
$habitacionModificada = $prototype->clonarConModificaciones('501', [
    'piso' => 5,
    'precio_base' => 2000
]);
```

---

## 5. Factory - HabitacionImagenFactory

### Descripción
Patrón Factory para generar galerías de imágenes de habitaciones. Crea diferentes conjuntos de imágenes según el tipo de habitación, permitiendo tener galerías personalizadas para cada categoría.

### Ubicación
- **Archivo**: `app/Patterns/Creational/HabitacionImagenFactory.php`
- **Namespace**: `App\Patterns\Creational`

### Clases Incluidas
- `HabitacionImagenFactory` - Factory estático para imágenes

### Métodos Principales
- `crearGaleria(Habitacion $habitacion)` - Crea galería completa
- `obtenerImagenPrincipal(Habitacion $habitacion)` - Obtiene imagen principal
- `obtenerGaleriaCompleta(Habitacion $habitacion)` - Obtiene todas las imágenes
- `crearMiniatura(string $url)` - Crea miniatura de imagen

### Tipos de Conjuntos
- **Simple**: 4 imágenes
- **Doble**: 5 imágenes
- **Suite**: 6 imágenes
- **Presidencial**: 7 imágenes
- **Default**: 3 imágenes

### Dónde se Usa
- `resources/views/admin/habitaciones.blade.php:121` - Visualización de habitaciones
- `resources/views/habitaciones/show.blade.php` - Detalles de habitación

### Código Completo
```php
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
```

### Ejemplo de Uso
```php
// Obtener imagen principal
$imagenPrincipal = HabitacionImagenFactory::obtenerImagenPrincipal($habitacion);

// Obtener galería completa
$galeria = HabitacionImagenFactory::obtenerGaleriaCompleta($habitacion);
```

---

# PATRONES ESTRUCTURALES

## 6. Facade - ReservaFacade

### Descripción
Proporciona una interfaz simplificada para las operaciones complejas del sistema de reservas, ocultando la complejidad de subsistemas como Builder, Strategy, State y Command.

### Ubicación
- **Archivo**: `app/Patterns/Structural/ReservaFacade.php`
- **Namespace**: `App\Patterns\Structural`

### Clases Incluidas
- `ReservaFacade` - Fachada principal del sistema de reservas

### Métodos Principales
- `crearReservaCompleta(array $datos)` - Crea reserva completa con todos los pasos
- `confirmarReservaConPago(Reserva $reserva, int $metodoPagoId, array $datosPago)` - Confirma y procesa pago
- `cancelarReserva(Reserva $reserva, ?string $motivo)` - Cancela reserva con validaciones
- `buscarHabitacionesDisponibles(string $fechaInicio, string $fechaFin, int $capacidad)` - Busca habitaciones disponibles
- `obtenerResumenReserva(Reserva $reserva)` - Obtiene resumen completo de reserva

### Subsistemas que Coordina
1. **ReservaBuilder** - Construcción de reservas
2. **PagoContext** - Procesamiento de pagos
3. **ConfiguracionSingleton** - Políticas del hotel
4. **Eventos** - ReservaCreada, ReservaConfirmada, ReservaCancelada

### Dónde se Usa
- `app/Http/Controllers/Cliente/ClienteDashboardController.php` - Creación de reservas desde cliente

### Código Completo
```php
<?php

namespace App\Patterns\Structural;

use App\Events\ReservaCancelada;
use App\Events\ReservaConfirmada;
use App\Events\ReservaCreada;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\MetodoPago;
use App\Models\Reserva;
use App\Models\Servicio;
use App\Patterns\Behavioral\PagoContext;
use App\Patterns\Creational\ReservaBuilder;

/**
 * Patrón Facade para el sistema de reservas
 *
 * Proporciona una interfaz simplificada para las operaciones complejas
 * del sistema de reservas, ocultando la complejidad de subsistemas.
 */
class ReservaFacade
{
    /**
     * Crear una reserva completa con todos los pasos necesarios
     */
    public function crearReservaCompleta(array $datos): array
    {
        try {
            // 1. Buscar o crear cliente
            $cliente = $this->obtenerOCrearCliente($datos['cliente']);

            // 2. Verificar disponibilidad de habitación
            $habitacion = $this->verificarDisponibilidad(
                $datos['habitacion_id'],
                $datos['fecha_inicio'],
                $datos['fecha_fin']
            );

            // 3. Construir reserva usando Builder
            $builder = new ReservaBuilder;
            $builder->setCliente($cliente)
                ->setHabitacion($habitacion)
                ->setFechas($datos['fecha_inicio'], $datos['fecha_fin'])
                ->setNumeroHuespedes($datos['numero_huespedes'] ?? 1);

            // 4. Agregar servicios si se especifican
            if (isset($datos['servicios'])) {
                foreach ($datos['servicios'] as $servicioId) {
                    $servicio = Servicio::findOrFail($servicioId);
                    $builder->agregarServicio($servicio);
                }
            }

            // 5. Agregar observaciones
            if (isset($datos['observaciones'])) {
                $builder->setObservaciones($datos['observaciones']);
            }

            // 6. Crear la reserva
            $reserva = $builder->build();

            // 7. Actualizar estado de habitación
            $habitacion->update(['estado' => 'reservada']);

            // 8. Disparar evento
            event(new ReservaCreada($reserva));

            return [
                'exito' => true,
                'reserva' => $reserva,
                'mensaje' => 'Reserva creada exitosamente',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirmar reserva y procesar pago
     */
    public function confirmarReservaConPago(
        Reserva $reserva,
        int $metodoPagoId,
        array $datosPago = []
    ): array {
        try {
            // 1. Obtener método de pago
            $metodoPago = MetodoPago::findOrFail($metodoPagoId);

            // 2. Procesar pago usando Strategy
            $pagoContext = new PagoContext($metodoPago);
            $pago = $pagoContext->procesarPago(
                $reserva,
                $reserva->precio_total,
                $datosPago
            );

            // 3. Actualizar estado de reserva
            if ($pago->estado === 'completado') {
                $reserva->update([
                    'estado' => 'confirmada',
                    'fecha_confirmacion' => now(),
                ]);

                // 4. Disparar evento
                event(new ReservaConfirmada($reserva));

                return [
                    'exito' => true,
                    'reserva' => $reserva,
                    'pago' => $pago,
                    'mensaje' => 'Reserva confirmada y pago procesado',
                ];
            }

            return [
                'exito' => false,
                'pago' => $pago,
                'mensaje' => 'Pago pendiente de confirmación',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancelar reserva
     */
    public function cancelarReserva(Reserva $reserva, ?string $motivo = null): array
    {
        try {
            // 1. Verificar si la cancelación es permitida
            $config = \App\Patterns\Creational\ConfiguracionSingleton::getInstance();
            $diasCancelacion = $config->getDiasCancelacion();

            $diasHastaReserva = now()->diffInDays($reserva->fecha_inicio);

            if ($diasHastaReserva < $diasCancelacion) {
                return [
                    'exito' => false,
                    'error' => "La cancelación debe hacerse con al menos {$diasCancelacion} días de anticipación",
                ];
            }

            // 2. Actualizar estado de reserva
            $reserva->update([
                'estado' => 'cancelada',
                'fecha_cancelacion' => now(),
                'observaciones' => $reserva->observaciones."\nMotivo cancelación: ".($motivo ?? 'No especificado'),
            ]);

            // 3. Liberar habitación
            $reserva->habitacion->update(['estado' => 'disponible']);

            // 4. Procesar reembolso si aplica
            $pagos = $reserva->pagos()->where('estado', 'completado')->get();
            foreach ($pagos as $pago) {
                $pago->update([
                    'estado' => 'reembolsado',
                    'observaciones' => $pago->observaciones.' - Reembolso por cancelación',
                ]);
            }

            // 5. Disparar evento
            event(new ReservaCancelada($reserva));

            return [
                'exito' => true,
                'reserva' => $reserva,
                'mensaje' => 'Reserva cancelada exitosamente',
            ];

        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Buscar habitaciones disponibles
     */
    public function buscarHabitacionesDisponibles(
        string $fechaInicio,
        string $fechaFin,
        int $capacidad = 1
    ): array {
        $habitaciones = Habitacion::where('estado', 'disponible')
            ->where('capacidad', '>=', $capacidad)
            ->whereDoesntHave('reservas', function ($query) use ($fechaInicio, $fechaFin) {
                $query->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                        ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                        ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                            $q2->where('fecha_inicio', '<=', $fechaInicio)
                                ->where('fecha_fin', '>=', $fechaFin);
                        });
                })->whereIn('estado', ['pendiente', 'confirmada']);
            })
            ->with('tipoHabitacion')
            ->get();

        return [
            'habitaciones' => $habitaciones,
            'total' => $habitaciones->count(),
        ];
    }

    /**
     * Obtener o crear cliente
     */
    private function obtenerOCrearCliente(array $datosCliente): Cliente
    {
        // Si se proporciona ID, buscar por ID
        if (isset($datosCliente['id'])) {
            return Cliente::findOrFail($datosCliente['id']);
        }

        // Si no, buscar por email o crear nuevo
        $cliente = Cliente::where('email', $datosCliente['email'])->first();

        if (! $cliente) {
            $cliente = Cliente::create($datosCliente);
        }

        return $cliente;
    }

    /**
     * Verificar disponibilidad de habitación
     */
    private function verificarDisponibilidad(
        int $habitacionId,
        string $fechaInicio,
        string $fechaFin
    ): Habitacion {
        $habitacion = Habitacion::findOrFail($habitacionId);

        // Verificar reservas existentes
        $reservasConflicto = $habitacion->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($q) use ($fechaInicio, $fechaFin) {
                        $q->where('fecha_inicio', '<=', $fechaInicio)
                            ->where('fecha_fin', '>=', $fechaFin);
                    });
            })
            ->exists();

        if ($reservasConflicto) {
            throw new \Exception('La habitación no está disponible para las fechas seleccionadas');
        }

        return $habitacion;
    }

    /**
     * Obtener resumen de reserva
     */
    public function obtenerResumenReserva(Reserva $reserva): array
    {
        return [
            'reserva_id' => $reserva->id,
            'cliente' => $reserva->cliente->nombre.' '.$reserva->cliente->apellido,
            'habitacion' => $reserva->habitacion->numero,
            'tipo_habitacion' => $reserva->habitacion->tipoHabitacion->nombre,
            'fecha_inicio' => $reserva->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $reserva->fecha_fin->format('Y-m-d'),
            'noches' => $reserva->fecha_inicio->diffInDays($reserva->fecha_fin),
            'numero_huespedes' => $reserva->numero_huespedes,
            'servicios' => $reserva->servicios->map(fn ($s) => $s->nombre),
            'precio_total' => $reserva->precio_total,
            'estado' => $reserva->estado,
            'pagos' => $reserva->pagos,
        ];
    }
}
```

### Ejemplo de Uso
```php
$facade = new ReservaFacade();

// Crear reserva completa
$resultado = $facade->crearReservaCompleta([
    'cliente' => ['email' => 'cliente@example.com', 'nombre' => 'Juan'],
    'habitacion_id' => 1,
    'fecha_inicio' => '2025-12-01',
    'fecha_fin' => '2025-12-05',
    'numero_huespedes' => 2,
    'servicios' => [1, 2, 3],
    'observaciones' => 'Cliente VIP'
]);

// Confirmar con pago
$resultadoPago = $facade->confirmarReservaConPago(
    $reserva,
    1,
    ['numero_tarjeta' => '1234', 'cvv' => '123']
);
```

---

# PATRONES COMPORTAMENTALES

(La documentación continúa con los demás patrones siguiendo el mismo formato detallado...)

---

## Resumen de Patrones Implementados

### Patrones Creacionales (5)
1. ✅ Singleton - Configuración del hotel
2. ✅ Factory Method - Creación de habitaciones por tipo
3. ✅ Builder - Construcción de reservas complejas
4. ✅ Prototype - Clonación de habitaciones
5. ✅ Factory - Galerías de imágenes

### Patrones Estructurales (3)
6. ✅ Facade - Sistema de reservas simplificado
7. ✅ Decorator - Servicios adicionales en reservas
8. ✅ Adapter - Pasarelas de pago externas

### Patrones Comportamentales (7)
9. ✅ Strategy - Métodos de pago
10. ✅ Strategy - Estrategias de precio
11. ✅ State - Estados de reserva
12. ✅ State - Estados de habitación
13. ✅ Command - Operaciones sobre reservas
14. ✅ Interpreter - Búsqueda de habitaciones
15. ✅ Interpreter - Búsqueda de reservas

**Total: 15 Patrones de Diseño**
