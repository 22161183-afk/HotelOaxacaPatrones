@extends('layouts.admin')

@section('page-title', 'Documentación de API REST')

@section('content')

<style>
    .api-endpoint {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    .api-endpoint-header {
        padding: 15px;
        cursor: pointer;
        transition: background-color 0.3s;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .api-endpoint-header:hover {
        background-color: #f8f9fa;
    }
    .method-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: bold;
        font-size: 12px;
        min-width: 70px;
        text-align: center;
    }
    .method-get { background-color: #61affe; color: white; }
    .method-post { background-color: #49cc90; color: white; }
    .method-put { background-color: #fca130; color: white; }
    .method-delete { background-color: #f93e3e; color: white; }
    .api-endpoint-body {
        padding: 20px;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    .code-block {
        background-color: #2d2d2d;
        color: #f8f8f2;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
        font-family: 'Courier New', monospace;
        font-size: 13px;
    }
    .param-table {
        font-size: 14px;
    }
    .param-table td {
        padding: 8px;
    }
    .required-badge {
        background-color: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-book"></i> Documentación de API REST</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Información General</h5>
                    <p><strong>URL Base:</strong> <code>{{ url('/api') }}</code></p>
                    <p><strong>Formato de Respuesta:</strong> JSON</p>
                    <p><strong>Autenticación:</strong> Bearer Token (JWT)</p>
                    <p class="mb-0"><strong>Versión:</strong> 1.0</p>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-key"></i> Autenticación</h6>
                    <p>La mayoría de los endpoints requieren autenticación mediante token JWT. Incluye el token en el header:</p>
                    <pre class="mb-0"><code>Authorization: Bearer {tu_token}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUTENTICACIÓN -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-lock"></i> Autenticación</h5>
    </div>
    <div class="card-body">
        <!-- Login -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#auth-login">
                <span class="method-badge method-post">POST</span>
                <strong>/api/auth/login</strong>
                <span class="text-muted ms-auto">Iniciar sesión</span>
            </div>
            <div class="collapse api-endpoint-body" id="auth-login">
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>email</code> <span class="required-badge">REQUERIDO</span></td><td>Email del usuario</td></tr>
                    <tr><td><code>password</code> <span class="required-badge">REQUERIDO</span></td><td>Contraseña</td></tr>
                </table>
                <h6>Ejemplo de Request</h6>
                <div class="code-block">
{
  "email": "admin@hotel.com",
  "password": "password123"
}</div>
                <h6 class="mt-3">Ejemplo de Respuesta</h6>
                <div class="code-block">
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@hotel.com"
  }
}</div>
            </div>
        </div>

        <!-- Register -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#auth-register">
                <span class="method-badge method-post">POST</span>
                <strong>/api/auth/register</strong>
                <span class="text-muted ms-auto">Registrar nuevo usuario</span>
            </div>
            <div class="collapse api-endpoint-body" id="auth-register">
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>name</code> <span class="required-badge">REQUERIDO</span></td><td>Nombre completo</td></tr>
                    <tr><td><code>email</code> <span class="required-badge">REQUERIDO</span></td><td>Email</td></tr>
                    <tr><td><code>password</code> <span class="required-badge">REQUERIDO</span></td><td>Contraseña (mínimo 8 caracteres)</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- HABITACIONES -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-door-open"></i> Habitaciones</h5>
    </div>
    <div class="card-body">
        <!-- Listar Habitaciones -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#hab-index">
                <span class="method-badge method-get">GET</span>
                <strong>/api/habitaciones</strong>
                <span class="text-muted ms-auto">Listar todas las habitaciones</span>
            </div>
            <div class="collapse api-endpoint-body" id="hab-index">
                <h6>Ejemplo de Respuesta</h6>
                <div class="code-block">
{
  "data": [
    {
      "id": 1,
      "numero": "101",
      "tipo_habitacion_id": 1,
      "piso": 1,
      "capacidad": 2,
      "precio_base": 1500.00,
      "estado": "disponible",
      "amenidades": ["WiFi", "TV", "Aire Acondicionado"]
    }
  ]
}</div>
            </div>
        </div>

        <!-- Crear Habitación -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#hab-store">
                <span class="method-badge method-post">POST</span>
                <strong>/api/habitaciones</strong>
                <span class="text-muted ms-auto">Crear nueva habitación</span>
            </div>
            <div class="collapse api-endpoint-body" id="hab-store">
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>numero</code> <span class="required-badge">REQUERIDO</span></td><td>Número de habitación</td></tr>
                    <tr><td><code>tipo_habitacion_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID del tipo</td></tr>
                    <tr><td><code>piso</code> <span class="required-badge">REQUERIDO</span></td><td>Número de piso</td></tr>
                    <tr><td><code>capacidad</code> <span class="required-badge">REQUERIDO</span></td><td>Capacidad de personas</td></tr>
                    <tr><td><code>precio_base</code> <span class="required-badge">REQUERIDO</span></td><td>Precio por noche</td></tr>
                    <tr><td><code>estado</code></td><td>Estado (disponible, ocupada, mantenimiento)</td></tr>
                </table>
            </div>
        </div>

        <!-- Buscar Disponibles -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#hab-search">
                <span class="method-badge method-get">GET</span>
                <strong>/api/habitaciones/disponibles/search</strong>
                <span class="text-muted ms-auto">Buscar habitaciones disponibles</span>
            </div>
            <div class="collapse api-endpoint-body" id="hab-search">
                <h6>Parámetros Query</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>fecha_inicio</code></td><td>Fecha de inicio (Y-m-d)</td></tr>
                    <tr><td><code>fecha_fin</code></td><td>Fecha de fin (Y-m-d)</td></tr>
                    <tr><td><code>capacidad</code></td><td>Capacidad mínima</td></tr>
                    <tr><td><code>tipo</code></td><td>ID del tipo de habitación</td></tr>
                </table>
            </div>
        </div>

        <!-- Duplicar Habitación (Prototype Pattern) -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#hab-duplicate">
                <span class="method-badge method-post">POST</span>
                <strong>/api/habitaciones/{id}/duplicar</strong>
                <span class="text-muted ms-auto">Duplicar habitación (Patrón Prototype)</span>
            </div>
            <div class="collapse api-endpoint-body" id="hab-duplicate">
                <div class="alert alert-info">
                    <strong><i class="fas fa-code"></i> Patrón de Diseño:</strong> Prototype Pattern<br>
                    Este endpoint utiliza el patrón Prototype para clonar una habitación existente con sus configuraciones.
                </div>
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>nuevo_numero</code> <span class="required-badge">REQUERIDO</span></td><td>Número para la nueva habitación</td></tr>
                    <tr><td><code>piso</code></td><td>Piso (opcional, mantiene el original si no se especifica)</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- RESERVAS -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Reservas</h5>
    </div>
    <div class="card-body">
        <!-- Listar Reservas -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#res-index">
                <span class="method-badge method-get">GET</span>
                <strong>/api/reservas</strong>
                <span class="text-muted ms-auto">Listar todas las reservas</span>
            </div>
            <div class="collapse api-endpoint-body" id="res-index">
                <h6>Parámetros Query (Opcionales)</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>cliente_id</code></td><td>Filtrar por cliente</td></tr>
                    <tr><td><code>estado</code></td><td>Filtrar por estado</td></tr>
                    <tr><td><code>fecha_inicio</code></td><td>Desde fecha</td></tr>
                </table>
            </div>
        </div>

        <!-- Crear Reserva -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#res-store">
                <span class="method-badge method-post">POST</span>
                <strong>/api/reservas</strong>
                <span class="text-muted ms-auto">Crear nueva reserva</span>
            </div>
            <div class="collapse api-endpoint-body" id="res-store">
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>cliente_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID del cliente</td></tr>
                    <tr><td><code>habitacion_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID de la habitación</td></tr>
                    <tr><td><code>fecha_inicio</code> <span class="required-badge">REQUERIDO</span></td><td>Fecha de entrada (Y-m-d)</td></tr>
                    <tr><td><code>fecha_fin</code> <span class="required-badge">REQUERIDO</span></td><td>Fecha de salida (Y-m-d)</td></tr>
                    <tr><td><code>numero_huespedes</code> <span class="required-badge">REQUERIDO</span></td><td>Número de huéspedes</td></tr>
                </table>
            </div>
        </div>

        <!-- Verificar Disponibilidad -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#res-check">
                <span class="method-badge method-get">GET</span>
                <strong>/api/reservas/disponibilidad/check</strong>
                <span class="text-muted ms-auto">Verificar disponibilidad</span>
            </div>
            <div class="collapse api-endpoint-body" id="res-check">
                <h6>Parámetros Query</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>habitacion_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID de la habitación</td></tr>
                    <tr><td><code>fecha_inicio</code> <span class="required-badge">REQUERIDO</span></td><td>Fecha de inicio</td></tr>
                    <tr><td><code>fecha_fin</code> <span class="required-badge">REQUERIDO</span></td><td>Fecha de fin</td></tr>
                </table>
                <h6>Ejemplo de Respuesta</h6>
                <div class="code-block">
{
  "disponible": true,
  "mensaje": "La habitación está disponible para las fechas seleccionadas"
}</div>
            </div>
        </div>

        <!-- Confirmar Reserva -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#res-confirm">
                <span class="method-badge method-post">POST</span>
                <strong>/api/reservas/{id}/confirmar</strong>
                <span class="text-muted ms-auto">Confirmar reserva (Patrón State)</span>
            </div>
            <div class="collapse api-endpoint-body" id="res-confirm">
                <div class="alert alert-info">
                    <strong><i class="fas fa-code"></i> Patrón de Diseño:</strong> State Pattern<br>
                    Este endpoint cambia el estado de la reserva utilizando el patrón State para gestionar las transiciones válidas.
                </div>
            </div>
        </div>

        <!-- Agregar Servicios -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#res-servicios">
                <span class="method-badge method-post">POST</span>
                <strong>/api/reservas/{id}/servicios</strong>
                <span class="text-muted ms-auto">Agregar servicios (Patrón Decorator)</span>
            </div>
            <div class="collapse api-endpoint-body" id="res-servicios">
                <div class="alert alert-info">
                    <strong><i class="fas fa-code"></i> Patrón de Diseño:</strong> Decorator Pattern<br>
                    Este endpoint utiliza el patrón Decorator para agregar servicios adicionales a una reserva de forma dinámica.
                </div>
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>servicio_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID del servicio</td></tr>
                    <tr><td><code>cantidad</code></td><td>Cantidad (default: 1)</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- PAGOS -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-credit-card"></i> Pagos</h5>
    </div>
    <div class="card-body">
        <!-- Crear Pago -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#pago-store">
                <span class="method-badge method-post">POST</span>
                <strong>/api/pagos</strong>
                <span class="text-muted ms-auto">Procesar pago (Patrón Strategy)</span>
            </div>
            <div class="collapse api-endpoint-body" id="pago-store">
                <div class="alert alert-info">
                    <strong><i class="fas fa-code"></i> Patrón de Diseño:</strong> Strategy Pattern<br>
                    Este endpoint utiliza el patrón Strategy para procesar pagos con diferentes métodos de pago de forma intercambiable.
                </div>
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>reserva_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID de la reserva</td></tr>
                    <tr><td><code>metodo_pago_id</code> <span class="required-badge">REQUERIDO</span></td><td>ID del método de pago</td></tr>
                    <tr><td><code>monto</code> <span class="required-badge">REQUERIDO</span></td><td>Monto a pagar</td></tr>
                </table>
            </div>
        </div>

        <!-- Reembolso -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#pago-refund">
                <span class="method-badge method-post">POST</span>
                <strong>/api/pagos/{id}/refund</strong>
                <span class="text-muted ms-auto">Procesar reembolso</span>
            </div>
            <div class="collapse api-endpoint-body" id="pago-refund">
                <h6>Parámetros del Request</h6>
                <table class="table table-sm param-table">
                    <tr><td><code>monto</code></td><td>Monto a reembolsar (opcional, por defecto el total)</td></tr>
                    <tr><td><code>motivo</code></td><td>Motivo del reembolso</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CONFIGURACIÓN -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-cog"></i> Configuración (Patrón Singleton)</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong><i class="fas fa-code"></i> Patrón de Diseño:</strong> Singleton Pattern<br>
            Los endpoints de configuración utilizan el patrón Singleton para garantizar una única instancia de la configuración del sistema.
        </div>

        <!-- Obtener Configuración -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#config-get">
                <span class="method-badge method-get">GET</span>
                <strong>/api/config</strong>
                <span class="text-muted ms-auto">Obtener configuración del sistema</span>
            </div>
        </div>

        <!-- Actualizar Configuración -->
        <div class="api-endpoint">
            <div class="api-endpoint-header" data-bs-toggle="collapse" data-bs-target="#config-update">
                <span class="method-badge method-put">PUT</span>
                <strong>/api/config</strong>
                <span class="text-muted ms-auto">Actualizar configuración</span>
            </div>
        </div>
    </div>
</div>

<!-- CÓDIGOS DE RESPUESTA -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-list-ol"></i> Códigos de Respuesta HTTP</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Significado</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <td><strong>200</strong></td>
                    <td>OK</td>
                    <td>Solicitud exitosa</td>
                </tr>
                <tr class="table-success">
                    <td><strong>201</strong></td>
                    <td>Created</td>
                    <td>Recurso creado exitosamente</td>
                </tr>
                <tr class="table-warning">
                    <td><strong>400</strong></td>
                    <td>Bad Request</td>
                    <td>Error en los parámetros de la solicitud</td>
                </tr>
                <tr class="table-warning">
                    <td><strong>401</strong></td>
                    <td>Unauthorized</td>
                    <td>No autenticado o token inválido</td>
                </tr>
                <tr class="table-warning">
                    <td><strong>403</strong></td>
                    <td>Forbidden</td>
                    <td>No tiene permisos para esta acción</td>
                </tr>
                <tr class="table-warning">
                    <td><strong>404</strong></td>
                    <td>Not Found</td>
                    <td>Recurso no encontrado</td>
                </tr>
                <tr class="table-danger">
                    <td><strong>500</strong></td>
                    <td>Internal Server Error</td>
                    <td>Error interno del servidor</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
