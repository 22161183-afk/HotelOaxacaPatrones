<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Hotel Oaxaca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            position: relative;
            overflow-y: auto;
            padding: 40px 0;
        }

        /* Fondo con imagen de hotel */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=1080&fit=crop');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.7);
            z-index: -2;
        }

        /* Overlay oscuro para mejorar contraste */
        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.85) 0%, rgba(52, 73, 94, 0.85) 100%);
            z-index: -1;
        }

        /* Contenedor principal con efecto glassmorphism */
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4),
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            padding: 45px 40px;
            position: relative;
            overflow: hidden;
        }

        /* Decoración superior */
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #3498db 0%, #2980b9 50%, #1abc9c 100%);
        }

        /* Header del login */
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header .hotel-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        .login-header .hotel-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #3498db;
            box-shadow: 0 8px 24px rgba(52, 152, 219, 0.3);
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .form-control:focus + i {
            color: #3498db;
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        /* Checkbox Remember Me */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            border: 2px solid #95a5a6;
            border-radius: 4px;
        }

        .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }

        .form-check-label {
            color: #2c3e50;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        /* Botón de login */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e0e6ed;
        }

        .divider span {
            padding: 0 15px;
            color: #95a5a6;
            font-size: 13px;
            font-weight: 500;
        }

        /* Link de registro */
        .register-link {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 25px;
        }

        .register-link p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        /* Alertas */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background: #ffe5e5;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }

        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 6px;
            display: block;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 20px 0;
            }

            .login-wrapper {
                padding: 15px;
            }

            .login-container {
                padding: 35px 25px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }

        /* Loading spinner para el botón */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <div class="hotel-icon">
                    <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=400&h=400&fit=crop" alt="Hotel Oaxaca">
                </div>
                <h1>Hotel Oaxaca</h1>
                <p>Bienvenido de vuelta, inicia sesión para continuar</p>
            </div>

            <!-- Errores -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Formulario -->
            <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            class="form-control @error('email') is-invalid @enderror"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            required
                            autocomplete="email"
                        >
                        <i class="fas fa-envelope"></i>
                    </div>
                    @error('email')
                        <span class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <i class="fas fa-lock"></i>
                    </div>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="remember-forgot">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="remember">
                            Recuérdame
                        </label>
                    </div>
                </div>

                <!-- Botón de Login -->
                <button type="submit" class="btn btn-login" id="loginButton">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>O</span>
            </div>

            <!-- Link de registro -->
            <div class="register-link">
                <p>
                    ¿No tienes una cuenta?
                    <a href="{{ route('auth.register') }}">
                        <i class="fas fa-user-plus"></i> Regístrate aquí
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Añadir efecto de loading al botón de login
        document.getElementById('loginForm').addEventListener('submit', function() {
            const button = document.getElementById('loginButton');
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
        });

        // Efecto de focus para inputs
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>
