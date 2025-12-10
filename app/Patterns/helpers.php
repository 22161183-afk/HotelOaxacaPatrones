<?php

/**
 * Helper file para cargar clases de patrones de diseño
 * que están en archivos con múltiples clases
 */

// Cargar todas las clases del archivo ReservaCommand.php
require_once __DIR__.'/Behavioral/ReservaCommand.php';

// Cargar todas las clases del archivo MetodoPagoStrategy.php
require_once __DIR__.'/Behavioral/MetodoPagoStrategy.php';

// Cargar todas las clases del archivo ReservaValidationChain.php
require_once __DIR__.'/Behavioral/ReservaValidationChain.php';

// Cargar todas las clases del archivo ReservaObserver.php
require_once __DIR__.'/Behavioral/ReservaObserver.php';

// Cargar todas las clases del archivo ServicioComposite.php
require_once __DIR__.'/Structural/ServicioComposite.php';

// Cargar todas las clases del archivo HabitacionFlyweight.php
require_once __DIR__.'/Structural/HabitacionFlyweight.php';

// Cargar todas las clases del archivo PasarelaPagoAdapter.php
require_once __DIR__.'/Structural/Adapters/PasarelaPagoAdapter.php';

// Cargar todas las clases del archivo PricingStrategy.php
require_once __DIR__.'/Behavioral/PricingStrategy.php';

// Cargar la interfaz SearchExpression y clases del HabitacionSearchInterpreter.php
require_once __DIR__.'/Behavioral/HabitacionSearchInterpreter.php';

// Cargar todas las clases del archivo ReservaSearchInterpreter.php
require_once __DIR__.'/Behavioral/ReservaSearchInterpreter.php';
