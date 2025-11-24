# Documentación de Patrones de Diseño - Sistema de Reservas de Hotel

Este documento detalla todos los patrones de diseño implementados en el proyecto, incluyendo su ubicación, clases principales y propósito.

---

## Índice
1. [Patrones Creacionales](#patrones-creacionales)
2. [Patrones Estructurales](#patrones-estructurales)
3. [Patrones Comportamentales](#patrones-comportamentales)

---

## Patrones Creacionales

### 1. Factory Pattern - HabitacionFactory

**📁 Ubicación:** `app/Patterns/Creational/HabitacionFactory.php`

**🎯 Propósito:**
Encapsula la creación de habitaciones con diferentes configuraciones predefinidas según el tipo de habitación (Deluxe, Suite Presidencial, Standard, Familiar).

**📋 Clases Principales:**
- `HabitacionFactory` - Clase principal del Factory

**🔧 Acción:**
- Proporciona métodos estáticos para crear habitaciones de diferentes tipos
- `crearDeluxe()` - Crea habitación tipo Deluxe
- `crearSuitePresidencial()` - Crea habitación Suite Presidencial
- `crearStandard()` - Crea habitación Standard
- `crearFamiliar()` - Crea habitación Familiar
- `crear($tipo, $data)` - Método genérico que delega a los métodos específicos

**💡 Uso en el Proyecto:**
- Se utiliza en seeders y migraciones para crear habitaciones de forma consistente
- Facilita la creación de habitaciones con configuraciones predefinidas

---

### 2. Builder Pattern - ReservaBuilder

**📁 Ubicación:** `app/Patterns/Creational/ReservaBuilder.php`

**🎯 Propósito:**
Permite construir objetos de Reserva complejos paso a paso, separando la construcción del objeto de su representación.

**📋 Clases Principales:**
- `ReservaBuilder` - Clase builder principal
- `ReservaDirector` - Dirige el proceso de construcción

**🔧 Acción:**
- `setCliente($cliente)` - Establece el cliente
- `setHabitacion($habitacion)` - Establece la habitación
- `setFechas($fechaInicio, $fechaFin)` - Establece las fechas
- `setNumeroHuespedes($numero)` - Establece número de huéspedes
- `agregarServicio($servicio)` - Agrega servicios adicionales
- `build()` - Construye y retorna la reserva

**💡 Uso en el Proyecto:**
- Construcción de reservas complejas con múltiples servicios
- Permite crear reservas de forma fluida y flexible
- Utilizado en controladores para crear reservas con diferentes configuraciones

---

### 3. Prototype Pattern - HabitacionPrototype

**📁 Ubicación:** `app/Patterns/Creational/HabitacionPrototype.php`

**🎯 Propósito:**
Permite clonar habitaciones existentes para crear nuevas instancias similares, útil para duplicar configuraciones.

**📋 Clases Principales:**
- `HabitacionPrototype` - Implementa el patrón Prototype

**🔧 Acción:**
- `clonar(Habitacion $habitacion)` - Clona una habitación existente
- `clonarConModificaciones(Habitacion $habitacion, array $modificaciones)` - Clona y aplica modificaciones
- Mantiene las amenidades y configuraciones de la habitación original

**💡 Uso en el Proyecto:**
- Creación rápida de habitaciones similares con ligeras variaciones
- Útil para duplicar habitaciones en diferentes pisos o con pequeñas diferencias

---

### 4. Singleton Pattern - ConfiguracionSingleton

**📁 Ubicación:** `app/Patterns/Creational/ConfiguracionSingleton.php`

**🎯 Propósito:**
Garantiza que exista una única instancia de la configuración del sistema y proporciona un punto de acceso global.

**📋 Clases Principales:**
- `ConfiguracionSingleton` - Clase Singleton

**🔧 Acción:**
- `getInstance()` - Obtiene la única instancia de la configuración
- `get($key)` - Obtiene un valor de configuración
- `set($key, $value)` - Establece un valor de configuración
- `all()` - Obtiene todas las configuraciones
- Constructor privado y método `__clone()` privado para evitar múltiples instancias

**💡 Uso en el Proyecto:**
- Gestión centralizada de configuraciones del sistema
- Acceso global a configuraciones sin necesidad de pasarlas por parámetros

---

### 5. Factory Pattern - HabitacionImagenFactory

**📁 Ubicación:** `app/Patterns/Creational/HabitacionImagenFactory.php`

**🎯 Propósito:**
Genera galerías de imágenes para habitaciones basándose en su tipo, utilizando URLs de Unsplash.

**📋 Clases Principales:**
- `HabitacionImagenFactory` - Factory de imágenes

**🔧 Acción:**
- `obtenerImagenPrincipal(Habitacion $habitacion)` - Obtiene la imagen principal de una habitación
- `obtenerGaleriaCompleta(Habitacion $habitacion)` - Obtiene galería completa de 5-6 imágenes
- `crearGaleria(Habitacion $habitacion)` - Crea el conjunto de imágenes según el tipo
- `normalizarTipo($tipoNombre)` - Normaliza el nombre del tipo de habitación

**💡 Uso en el Proyecto:**
- **Vista:** `resources/views/habitaciones/show.blade.php` - Galería de imágenes en detalles
- **Vista:** `resources/views/cliente/habitaciones.blade.php` - Imagen principal en listado
- **Vista:** `resources/views/cliente/dashboard.blade.php` - Imagen principal en dashboard
- Proporciona consistencia visual para cada tipo de habitación

---

## Patrones Estructurales

### 6. Adapter Pattern - PasarelaPagoAdapter

**📁 Ubicación:** `app/Patterns/Structural/Adapters/PasarelaPagoAdapter.php`

**🎯 Propósito:**
Adapta diferentes pasarelas de pago (Stripe, PayPal, Conekta) a una interfaz común para el sistema.

**📋 Clases Principales:**
- `PasarelaPagoInterface` - Interfaz común
- `StripeAdapter` - Adaptador para Stripe
- `PayPalAdapter` - Adaptador para PayPal
- `ConektaAdapter` - Adaptador para Conekta (México)

**🔧 Acción:**
- `procesarPago($monto, $metodoPago)` - Procesa un pago
- `verificarPago($transaccionId)` - Verifica el estado de un pago
- `reembolsar($transaccionId, $monto)` - Procesa un reembolso
- Cada adaptador implementa la lógica específica de su pasarela

**💡 Uso en el Proyecto:**
- Permite cambiar de pasarela de pago sin modificar el código del sistema
- Facilita la integración con múltiples proveedores de pago
- Se puede utilizar en el proceso de pago de reservas

---

### 7. Facade Pattern - ReservaFacade

**📁 Ubicación:** `app/Patterns/Structural/ReservaFacade.php`

**🎯 Propósito:**
Proporciona una interfaz simplificada para el complejo proceso de creación, modificación y cancelación de reservas.

**📋 Clases Principales:**
- `ReservaFacade` - Fachada principal

**🔧 Acción:**
- `crearReserva($data)` - Simplifica la creación de reservas
- `agregarServicios($reservaId, $servicios)` - Agrega servicios a una reserva
- `calcularPrecioTotal($reservaId)` - Calcula el precio total
- `cancelarReserva($reservaId)` - Cancela una reserva
- `confirmarReserva($reservaId)` - Confirma una reserva
- Coordina la interacción entre múltiples subsistemas (validación, cálculo de precios, servicios)

**💡 Uso en el Proyecto:**
- Simplifica la lógica en los controladores
- Encapsula la complejidad del proceso de reservas
- Facilita el mantenimiento al centralizar la lógica de negocio

---

### 8. Decorator Pattern - ReservaDecorator

**📁 Ubicación:** `app/Patterns/Structural/ReservaDecorator.php`

**🎯 Propósito:**
Permite agregar funcionalidades adicionales a las reservas de forma dinámica sin modificar su estructura base.

**📋 Clases Principales:**
- `ReservaComponent` - Componente base
- `ReservaBase` - Implementación base
- `ReservaDecorator` - Decorador abstracto
- `ServicioDecorator` - Agrega servicios adicionales
- `DescuentoDecorator` - Aplica descuentos
- `SeguroDecorator` - Agrega seguro de cancelación

**🔧 Acción:**
- `getPrecio()` - Obtiene el precio con las decoraciones aplicadas
- `getDescripcion()` - Obtiene la descripción completa
- Cada decorador agrega su funcionalidad manteniendo la interfaz base

**💡 Uso en el Proyecto:**
- **Controlador:** `ClienteDashboardController.php` - Líneas 224-236
- Agrega servicios adicionales a las reservas de forma flexible
- Permite combinar múltiples servicios sin modificar el modelo base
- Se utiliza al crear y actualizar reservas con servicios adicionales

---

## Patrones Comportamentales

### 9. Command Pattern - ReservaCommand

**📁 Ubicación:** `app/Patterns/Behavioral/ReservaCommand.php`

**🎯 Propósito:**
Encapsula operaciones sobre reservas como objetos, permitiendo parametrizar, encolar y deshacer acciones.

**📋 Clases Principales:**
- `ReservaCommand` - Interfaz de comando
- `CrearReservaCommand` - Comando para crear reserva
- `CancelarReservaCommand` - Comando para cancelar reserva
- `ModificarReservaCommand` - Comando para modificar reserva
- `ReservaInvoker` - Invocador de comandos

**🔧 Acción:**
- `execute()` - Ejecuta el comando
- `undo()` - Deshace el comando (opcional)
- Permite encapsular operaciones complejas en objetos reutilizables
- Facilita el historial de operaciones y la implementación de undo/redo

**💡 Uso en el Proyecto:**
- Gestión de operaciones sobre reservas de forma desacoplada
- Permite implementar historial de cambios y auditoría
- Facilita la implementación de colas de procesamiento

---

### 10. State Pattern - ReservaState

**📁 Ubicación:** `app/Patterns/Behavioral/ReservaState.php`

**🎯 Propósito:**
Permite que una reserva altere su comportamiento cuando su estado interno cambia (Pendiente, Confirmada, Completada, Cancelada).

**📋 Clases Principales:**
- `ReservaState` - Interfaz de estado
- `PendienteState` - Estado pendiente de pago
- `ConfirmadaState` - Estado confirmada
- `CompletadaState` - Estado completada
- `CanceladaState` - Estado cancelada
- `ReservaContext` - Contexto que mantiene el estado

**🔧 Acción:**
- `confirmar()` - Intenta confirmar la reserva
- `cancelar()` - Intenta cancelar la reserva
- `completar()` - Marca la reserva como completada
- `getNombre()` - Obtiene el nombre del estado actual
- `puedeModificarse()` - Verifica si la reserva puede modificarse

**💡 Uso en el Proyecto:**
- Gestiona las transiciones de estado de las reservas
- Valida qué acciones son permitidas en cada estado
- Mantiene la consistencia del ciclo de vida de las reservas

---

### 11. Strategy Pattern - MetodoPagoStrategy

**📁 Ubicación:** `app/Patterns/Behavioral/MetodoPagoStrategy.php`

**🎯 Propósito:**
Define una familia de algoritmos de pago, encapsula cada uno y los hace intercambiables.

**📋 Clases Principales:**
- `MetodoPagoStrategy` - Interfaz de estrategia
- `TarjetaCreditoStrategy` - Pago con tarjeta de crédito
- `TarjetaDebitoStrategy` - Pago con tarjeta de débito
- `TransferenciaStrategy` - Pago por transferencia
- `EfectivoStrategy` - Pago en efectivo
- `PagoContext` - Contexto que usa la estrategia

**🔧 Acción:**
- `procesarPago($monto, $datos)` - Procesa el pago según el método
- `validar($datos)` - Valida los datos del método de pago
- `obtenerComision()` - Obtiene la comisión aplicable
- Cada estrategia implementa su lógica de procesamiento específica

**💡 Uso en el Proyecto:**
- **Controlador:** `ClienteDashboardController.php` - Método `storePago()`
- Permite elegir diferentes métodos de pago en tiempo de ejecución
- Facilita agregar nuevos métodos de pago sin modificar código existente

---

### 12. Strategy Pattern - PricingStrategy

**📁 Ubicación:** `app/Patterns/Behavioral/PricingStrategy.php`

**🎯 Propósito:**
Define diferentes estrategias de cálculo de precios (Temporada alta, Temporada baja, Descuentos especiales, etc.).

**📋 Clases Principales:**
- `PricingStrategy` - Interfaz de estrategia
- `TemporadaAltaStrategy` - Precios de temporada alta
- `TemporadaBajaStrategy` - Precios de temporada baja
- `DescuentoGrupoStrategy` - Descuentos por grupo
- `DescuentoEstanciaLargaStrategy` - Descuentos por estancias largas
- `PrecioContext` - Contexto que aplica la estrategia

**🔧 Acción:**
- `calcularPrecio($precioBase, $dias, $huespedes)` - Calcula el precio según la estrategia
- `aplicable($fechaInicio, $fechaFin)` - Verifica si la estrategia es aplicable
- Permite cambiar dinámicamente la estrategia de precios

**💡 Uso en el Proyecto:**
- Cálculo dinámico de precios según temporada y condiciones
- Facilita la implementación de promociones y descuentos
- Se puede utilizar en el proceso de creación de reservas

---

### 13. State Pattern - HabitacionState

**📁 Ubicación:** `app/Patterns/Behavioral/HabitacionState.php`

**🎯 Propósito:**
Gestiona los estados de las habitaciones (Disponible, Reservada, Ocupada, Mantenimiento) y las transiciones válidas entre ellos.

**📋 Clases Principales:**
- `HabitacionState` - Interfaz de estado
- `DisponibleState` - Estado disponible
- `ReservadaState` - Estado reservada
- `OcupadaState` - Estado ocupada
- `MantenimientoState` - Estado en mantenimiento
- `HabitacionContext` - Contexto que gestiona el estado

**🔧 Acción:**
- `reservar($habitacion)` - Intenta reservar la habitación
- `ocupar($habitacion)` - Intenta marcar como ocupada
- `liberar($habitacion)` - Libera la habitación
- `mantenimiento($habitacion)` - Pone en mantenimiento
- `puedeReservar()` - Verifica si se puede reservar

**💡 Uso en el Proyecto:**
- **Controlador:** `ClienteDashboardController.php` - Líneas 218-221, 279-282, 359-362
- **Migración:** `2025_11_24_024357_add_reservada_estado_to_habitacions_table.php`
- Gestiona automáticamente los cambios de estado al crear, cancelar y pagar reservas
- Valida transiciones de estado permitidas
- Mantiene la integridad de los estados de las habitaciones

---

### 14. Interpreter Pattern - HabitacionSearchInterpreter

**📁 Ubicación:** `app/Patterns/Behavioral/HabitacionSearchInterpreter.php`

**🎯 Propósito:**
Define una gramática para búsquedas complejas de habitaciones y proporciona un intérprete que usa la gramática para interpretar criterios de búsqueda avanzada.

**📋 Clases Principales:**
- `SearchExpression` - Interfaz de expresión
- `TipoHabitacionExpression` - Búsqueda por tipo
- `CapacidadExpression` - Búsqueda por capacidad
- `PrecioExpression` - Búsqueda por rango de precio
- `PisoExpression` - Búsqueda por piso
- `AmenidadesExpression` - Búsqueda por amenidades
- `DisponibleExpression` - Filtra solo disponibles
- `AndExpression` - Combina expresiones con AND
- `HabitacionSearchInterpreter` - Intérprete principal

**🔧 Acción:**
- `interpret(Builder $query)` - Interpreta y aplica la expresión al query
- `fromRequest(array $params)` - Construye el intérprete desde parámetros de request
- `addExpression($expression)` - Agrega una expresión de búsqueda
- Combina múltiples criterios de búsqueda de forma flexible

**💡 Uso en el Proyecto:**
- **Controlador:** `ClienteDashboardController.php` - Líneas 111-113
- **Vista:** `resources/views/cliente/habitaciones.blade.php` - Formulario de búsqueda avanzada
- Permite búsquedas complejas combinando múltiples criterios
- Filtra automáticamente solo habitaciones disponibles
- Soporta búsqueda por: tipo, capacidad, precio, piso y amenidades

---

## Resumen de Uso por Archivo

### Controladores

**`app/Http/Controllers/Cliente/ClienteDashboardController.php`**
- **Builder Pattern** (ReservaBuilder) - Construcción de reservas
- **Decorator Pattern** (ReservaDecorator) - Líneas 224-236: Agregar servicios
- **State Pattern** (HabitacionState) - Líneas 218-221, 279-282, 359-362: Cambios de estado
- **Strategy Pattern** (MetodoPagoStrategy) - Método `storePago()`: Procesamiento de pagos
- **Interpreter Pattern** (HabitacionSearchInterpreter) - Líneas 111-113: Búsqueda avanzada

### Vistas

**`resources/views/habitaciones/show.blade.php`**
- **Factory Pattern** (HabitacionImagenFactory) - Líneas 7-10: Galería de imágenes

**`resources/views/cliente/habitaciones.blade.php`**
- **Factory Pattern** (HabitacionImagenFactory) - Líneas 19-25: Imagen principal
- **Interpreter Pattern** (HabitacionSearchInterpreter) - Líneas 7-111: Formulario de búsqueda

**`resources/views/cliente/dashboard.blade.php`**
- **Factory Pattern** (HabitacionImagenFactory) - Líneas 107-113: Imagen principal

### Migraciones

**`database/migrations/2025_11_24_024357_add_reservada_estado_to_habitacions_table.php`**
- **State Pattern** (HabitacionState) - Agrega estado 'reservada' al enum

---

## Beneficios de los Patrones Implementados

### ✅ Mantenibilidad
- Código organizado y modular
- Fácil de entender y modificar
- Cambios localizados en componentes específicos

### ✅ Escalabilidad
- Fácil agregar nuevos tipos de habitaciones (Factory)
- Fácil agregar nuevos métodos de pago (Strategy, Adapter)
- Fácil agregar nuevos servicios (Decorator)

### ✅ Reutilización
- Componentes reutilizables en diferentes contextos
- Lógica de negocio encapsulada y desacoplada

### ✅ Flexibilidad
- Cambio de estrategias en tiempo de ejecución
- Composición dinámica de funcionalidades
- Fácil integración con nuevos proveedores externos

### ✅ Testabilidad
- Componentes aislados fáciles de probar
- Interfaces bien definidas para mocking
- Lógica de negocio separada de la infraestructura

---

## Diagrama de Relaciones

```
┌─────────────────────────────────────────────────────────────────┐
│                     SISTEMA DE RESERVAS                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐     ┌──────────────┐     ┌──────────────┐   │
│  │  CREACIÓN    │────▶│  GESTIÓN     │────▶│   PAGO       │   │
│  └──────────────┘     └──────────────┘     └──────────────┘   │
│       │                     │                     │             │
│       ▼                     ▼                     ▼             │
│  Factory/Builder       State/Command         Strategy/Adapter  │
│  Prototype/Singleton   Interpreter           Facade            │
│                        Decorator                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Conclusión

Este proyecto demuestra la aplicación correcta de **14 patrones de diseño** distribuidos en las tres categorías principales:

- **5 Patrones Creacionales**: Factory (x2), Builder, Prototype, Singleton
- **3 Patrones Estructurales**: Adapter, Facade, Decorator
- **6 Patrones Comportamentales**: Command, State (x2), Strategy (x2), Interpreter

Cada patrón ha sido implementado siguiendo las mejores prácticas y principios SOLID, proporcionando una arquitectura robusta, mantenible y escalable para el sistema de reservas de hotel.

---

**Fecha de documentación:** Noviembre 2025
**Versión del proyecto:** Laravel 12
**Autor:** Sistema de Reservas Hotel Oaxaca
