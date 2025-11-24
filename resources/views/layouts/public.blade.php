<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title', 'Hotel Oaxaca') - Hotel Oaxaca</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-custom .navbar-brand {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.85);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
        }

        .navbar-custom .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }

        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            background: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .page-header h1 {
            margin: 0;
            color: var(--primary-color);
        }

        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        footer a:hover {
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-hotel"></i> Hotel Oaxaca
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.habitaciones.index') }}">
                                    <i class="fas fa-door-open"></i> Habitaciones
                                </a>
                            </li>
                        @elseif(auth()->user()->isCliente())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cliente.dashboard') }}">
                                    <i class="fas fa-home"></i> Principal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cliente.habitaciones.index') }}">
                                    <i class="fas fa-door-open"></i> Habitaciones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cliente.reservas.index') }}">
                                    <i class="fas fa-calendar-alt"></i> Mis Reservas
                                </a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="{{ route('auth.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('auth.register') }}">
                                <i class="fas fa-user-plus"></i> Registrarse
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>@yield('page-title', 'Hotel Oaxaca')</h1>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-hotel"></i> Hotel Oaxaca</h5>
                    <p class="small">Experiencia de lujo en el corazón de Oaxaca</p>
                </div>
                <div class="col-md-4">
                    <h6>Contacto</h6>
                    <p class="small">
                        <i class="fas fa-phone"></i> +52 951 123 4567<br>
                        <i class="fas fa-envelope"></i> info@hoteloaxaca.com<br>
                        <i class="fas fa-map-marker-alt"></i> Centro, Oaxaca, México
                    </p>
                </div>
                <div class="col-md-4">
                    <h6>Enlaces</h6>
                    <ul class="list-unstyled small">
                        @auth
                            @if(auth()->user()->isCliente())
                                <li><a href="{{ route('cliente.habitaciones.index') }}">Habitaciones</a></li>
                                <li><a href="{{ route('cliente.reservas.index') }}">Mis Reservas</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}">Iniciar Sesión</a></li>
                            <li><a href="{{ route('auth.register') }}">Registrarse</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center small">
                <p class="mb-0">&copy; {{ date('Y') }} Hotel Oaxaca. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
