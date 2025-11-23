<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Hotel Oaxaca</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #34495e 100%);
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            width: 260px;
            left: 0;
            top: 0;
        }

        .sidebar .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            padding: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar .nav-item {
            margin: 0;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: var(--secondary-color);
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }

        .topbar {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 28px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .stat-card {
            padding: 20px;
            border-left: 4px solid;
        }

        .stat-card.primary {
            border-left-color: var(--secondary-color);
            background: linear-gradient(135deg, rgba(52,152,219,0.1) 0%, transparent 100%);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, rgba(39,174,96,0.1) 0%, transparent 100%);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
            background: linear-gradient(135deg, rgba(243,156,18,0.1) 0%, transparent 100%);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
            background: linear-gradient(135deg, rgba(231,76,60,0.1) 0%, transparent 100%);
        }

        .stat-card h5 {
            color: #555;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .table-responsive {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: linear-gradient(180deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
        }

        .table tbody tr {
            transition: all 0.2s;
        }

        .table tbody tr:hover {
            background: rgba(52,152,219,0.05);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 12px;
        }

        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52,152,219,0.4);
        }

        .chart-container {
            position: relative;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="logo">
            <i class="fas fa-hotel"></i> Hotel Oaxaca
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'dashboard') active @endif" href="{{ route('dashboard') }}">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'reservas.index') active @endif" href="{{ route('reservas.index') }}">
                    <i class="fas fa-calendar-alt"></i> Reservas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'habitaciones.index') active @endif" href="{{ route('habitaciones.index') }}">
                    <i class="fas fa-door-open"></i> Habitaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'pagos.index') active @endif" href="{{ route('pagos.index') }}">
                    <i class="fas fa-credit-card"></i> Pagos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/api/habitaciones') }}" target="_blank">
                    <i class="fas fa-plug"></i> APIs
                </a>
            </li>
        </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1>@yield('page-title', 'Dashboard')</h1>
            <div class="user-info">
                <span><i class="fas fa-circle" style="color: #27ae60; font-size: 8px;"></i> {{ Auth::user()->name ?? 'Usuario' }}</span>
                <form method="POST" action="{{ route('auth.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger" title="Cerrar sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- CONTENIDO -->
        @yield('content')

        <!-- FOOTER -->
        <div class="footer">
            <p>&copy; 2025 Sistema de Reservas Hotel Oaxaca. Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
