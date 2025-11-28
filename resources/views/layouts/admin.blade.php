<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Hotel Oaxaca</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --primary-color: #1a1f36;
            --secondary-color: #3b82f6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-gray: #f8fafc;
            --dark-gray: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR MEJORADO */
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            padding: 0;
            position: fixed;
            width: 280px;
            left: 0;
            top: 0;
            box-shadow: 4px 0 24px rgba(0,0,0,0.12);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 200px;
            background: linear-gradient(180deg, rgba(59,130,246,0.1) 0%, transparent 100%);
            pointer-events: none;
        }

        .sidebar .logo {
            color: white;
            font-size: 22px;
            font-weight: 700;
            padding: 32px 24px 16px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            position: relative;
            text-align: center;
        }

        .sidebar .logo > div {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar .logo i {
            font-size: 28px;
            background: linear-gradient(135deg, var(--secondary-color), var(--info-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 2px 4px rgba(59,130,246,0.4));
        }

        .sidebar .role-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(239,68,68,0.3);
            margin-bottom: 12px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .sidebar .nav {
            padding: 12px;
        }

        .sidebar .nav-item {
            margin-bottom: 4px;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.65);
            padding: 14px 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            border-left: 3px solid transparent;
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, rgba(59,130,246,0.15) 0%, transparent 100%);
            transition: width 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.08);
            transform: translateX(4px);
            border-left-color: var(--secondary-color);
        }

        .sidebar .nav-link:hover::before {
            width: 100%;
        }

        .sidebar .nav-link.active {
            color: white;
            background: rgba(59,130,246,0.15);
            border-left-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(59,130,246,0.2);
            font-weight: 600;
        }

        .sidebar .nav-link i {
            margin-right: 14px;
            width: 20px;
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        /* MAIN CONTENT MEJORADO */
        .main-content {
            margin-left: 280px;
            padding: 24px 32px;
            min-height: 100vh;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* TOPBAR MEJORADO */
        .topbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 20px 28px;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.02);
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid rgba(226,232,240,0.8);
        }

        .topbar h1 {
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 8px;
            background: rgba(248,250,252,0.8);
            border-radius: 12px;
        }

        .user-info .admin-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(239,68,68,0.25);
        }

        .user-info span {
            color: var(--primary-color);
            font-weight: 500;
            font-size: 14px;
        }

        .user-info .fa-circle {
            color: var(--success-color);
            font-size: 10px;
            margin-right: 6px;
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* CARDS MEJORADOS */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 0 0 1px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 24px;
            background: white;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.03);
            transform: translateY(-4px);
        }

        .card-header {
            border-bottom: 1px solid rgba(226,232,240,0.8);
            padding: 20px 24px;
            font-weight: 600;
            font-size: 16px;
        }

        .card-body {
            padding: 24px;
        }

        /* STAT CARDS MEJORADOS */
        .stat-card {
            padding: 24px;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            right: -20px;
            top: -20px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.05;
            transition: all 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scale(1.2);
            opacity: 0.08;
        }

        .stat-card.primary {
            border-left-color: var(--secondary-color);
            background: linear-gradient(135deg, rgba(59,130,246,0.05) 0%, transparent 100%);
        }

        .stat-card.primary::before {
            background: var(--secondary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, rgba(16,185,129,0.05) 0%, transparent 100%);
        }

        .stat-card.success::before {
            background: var(--success-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
            background: linear-gradient(135deg, rgba(245,158,11,0.05) 0%, transparent 100%);
        }

        .stat-card.warning::before {
            background: var(--warning-color);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
            background: linear-gradient(135deg, rgba(239,68,68,0.05) 0%, transparent 100%);
        }

        .stat-card.danger::before {
            background: var(--danger-color);
        }

        .stat-card h5 {
            color: var(--dark-gray);
            font-size: 13px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }

        .stat-card .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -1px;
        }

        /* TABLES MEJORADAS */
        .table-responsive {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 0 0 1px rgba(0,0,0,0.02);
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, #334155 100%);
            color: white;
        }

        .table thead th {
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 18px 16px;
            border: none;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-color: rgba(226,232,240,0.6);
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: rgba(59,130,246,0.03);
            transform: scale(1.005);
        }

        /* BADGES MEJORADOS */
        .badge {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* BUTTONS MEJORADOS */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59,130,246,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59,130,246,0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239,68,68,0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16,185,129,0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245,158,11,0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245,158,11,0.4);
        }

        /* CHARTS MEJORADOS */
        .chart-container {
            position: relative;
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04), 0 0 0 1px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }

        /* FOOTER MEJORADO */
        .footer {
            text-align: center;
            padding: 32px 20px;
            color: var(--dark-gray);
            font-size: 13px;
            margin-top: 48px;
            border-top: 1px solid rgba(226,232,240,0.8);
            font-weight: 500;
        }

        /* ALERTS MEJORADOS */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-weight: 500;
        }

        /* RESPONSIVE MEJORADO */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .topbar {
                flex-direction: column;
                gap: 16px;
                padding: 16px;
            }

            .topbar h1 {
                font-size: 24px;
            }

            .stat-card .stat-value {
                font-size: 28px;
            }
        }

        /* SCROLLBAR PERSONALIZADO */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(226,232,240,0.5);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--secondary-color) 0%, #2563eb 100%);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="logo">
            <div>
                <i class="fas fa-hotel"></i>
                <span>Hotel Oaxaca</span>
            </div>
            <span class="role-badge">ADMIN</span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'admin.dashboard') active @endif" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-home"></i> Principal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'admin.reservas.index') active @endif" href="{{ route('admin.reservas.index') }}">
                    <i class="fas fa-calendar-alt"></i> Reservas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'admin.habitaciones.index') active @endif" href="{{ route('admin.habitaciones.index') }}">
                    <i class="fas fa-door-open"></i> Habitaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'admin.pagos.index') active @endif" href="{{ route('admin.pagos.index') }}">
                    <i class="fas fa-credit-card"></i> Pagos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(str_contains(Route::currentRouteName() ?? '', 'admin.metodos-pago')) active @endif" href="{{ route('admin.metodos-pago.index') }}">
                    <i class="fas fa-wallet"></i> Métodos de Pago
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link @if(Route::currentRouteName() === 'admin.api-docs') active @endif" href="{{ route('admin.api-docs') }}">
                    <i class="fas fa-plug"></i> Documentación API
                </a>
            </li>
        </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1>@yield('page-title', 'Dashboard Admin')</h1>
            <div class="user-info">
                <span class="admin-badge">ADMINISTRADOR</span>
                <span><i class="fas fa-circle" style="color: #27ae60; font-size: 8px;"></i> {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('auth.logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger" title="Cerrar sesión">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- MENSAJES DE ALERTA -->
        <div class="container-fluid px-4 py-3">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ session('warning') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>{{ session('info') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <!-- CONTENIDO -->
        @yield('content')

        <!-- FOOTER -->
        <div class="footer">
            <p>&copy; 2025 Sistema de Reservas Hotel Oaxaca - Panel de Administrador. Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('scripts')
</body>
</html>
