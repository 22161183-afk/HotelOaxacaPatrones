<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Hotel Oaxaca</title>
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
        .register-wrapper {
            width: 100%;
            max-width: 480px;
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

        .register-container {
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
        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #27ae60 0%, #2ecc71 50%, #1abc9c 100%);
        }

        /* Header del registro */
        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header .hotel-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            position: relative;
            animation: bounce 2s ease-in-out infinite;
        }

        .register-header .hotel-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #27ae60;
            box-shadow: 0 8px 24px rgba(39, 174, 96, 0.3);
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .register-header p {
            color: #7f8c8d;
            font-size: 14px;
            margin: 0;
        }

        /* Formulario */
        .form-group {
            margin-bottom: 20px;
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
            border-color: #27ae60;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(39, 174, 96, 0.1);
        }

        .form-control:focus + i {
            color: #27ae60;
        }

        .form-control.is-invalid {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        /* Indicador de fortaleza de contraseña */
        .password-strength {
            height: 4px;
            background: #e0e6ed;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
            display: none;
        }

        .password-strength.active {
            display: block;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #e74c3c;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #f39c12;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #27ae60;
        }

        /* Botón de registro */
        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        .btn-register:active {
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

        /* Link de login */
        .login-link {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 25px;
        }

        .login-link p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }

        .login-link a {
            color: #27ae60;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #229954;
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

        /* Requisitos de contraseña */
        .password-requirements {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }

        .password-requirements.active {
            display: block;
        }

        .password-requirements ul {
            margin: 5px 0 0 0;
            padding-left: 20px;
        }

        .password-requirements li {
            margin: 3px 0;
        }

        .password-requirements li.valid {
            color: #27ae60;
        }

        .password-requirements li.valid::marker {
            content: '✓ ';
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 20px 0;
            }

            .register-wrapper {
                padding: 15px;
            }

            .register-container {
                padding: 35px 25px;
            }

            .register-header h1 {
                font-size: 24px;
            }
        }

        /* Loading spinner para el botón */
        .btn-register.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-register.loading::after {
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
    <div class="register-wrapper">
        <div class="register-container">
            <!-- Header -->
            <div class="register-header">
                <div class="hotel-icon">
                    <img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=400&h=400&fit=crop" alt="Hotel Oaxaca">
                </div>
                <h1>Crear Cuenta</h1>
                <p>Únete a Hotel Oaxaca y disfruta de nuestros servicios</p>
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
            <form method="POST" action="{{ route('auth.register') }}" id="registerForm">
                @csrf

                <!-- Nombre -->
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Nombre Completo
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Juan Pérez"
                            required
                            autocomplete="name"
                        >
                        <i class="fas fa-user"></i>
                    </div>
                    @error('name')
                        <span class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

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
                            placeholder="Mínimo 8 caracteres"
                            required
                            autocomplete="new-password"
                        >
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-requirements" id="passwordRequirements">
                        <strong>La contraseña debe tener:</strong>
                        <ul>
                            <li id="req-length">Al menos 8 caracteres</li>
                        </ul>
                    </div>
                    @error('password')
                        <span class="error-message">
                            <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                        </span>
                    @enderror
                </div>

                <!-- Confirmar Contraseña -->
                <div class="form-group">
                    <label for="password_confirmation">
                        <i class="fas fa-lock"></i> Confirmar Contraseña
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            class="form-control"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Repite tu contraseña"
                            required
                            autocomplete="new-password"
                        >
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <!-- Botón de Registro -->
                <button type="submit" class="btn btn-register" id="registerButton">
                    <i class="fas fa-user-plus"></i> Crear Cuenta
                </button>
            </form>

            <!-- Divider -->
            <div class="divider">
                <span>O</span>
            </div>

            <!-- Link de login -->
            <div class="login-link">
                <p>
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('login') }}">
                        <i class="fas fa-sign-in-alt"></i> Inicia sesión aquí
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Añadir efecto de loading al botón de registro
        document.getElementById('registerForm').addEventListener('submit', function() {
            const button = document.getElementById('registerButton');
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando cuenta...';
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

        // Validación de fortaleza de contraseña
        const passwordInput = document.getElementById('password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const passwordRequirements = document.getElementById('passwordRequirements');
        const reqLength = document.getElementById('req-length');

        passwordInput.addEventListener('focus', function() {
            passwordRequirements.classList.add('active');
            passwordStrength.classList.add('active');
        });

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            // Check length
            if (password.length >= 8) {
                strength++;
                reqLength.classList.add('valid');
            } else {
                reqLength.classList.remove('valid');
            }

            // Check for numbers
            if (/\d/.test(password)) {
                strength++;
            }

            // Check for letters
            if (/[a-zA-Z]/.test(password)) {
                strength++;
            }

            // Update strength bar
            passwordStrengthBar.className = 'password-strength-bar';
            if (strength === 1) {
                passwordStrengthBar.classList.add('weak');
            } else if (strength === 2) {
                passwordStrengthBar.classList.add('medium');
            } else if (strength >= 3) {
                passwordStrengthBar.classList.add('strong');
            }
        });

        // Validación de confirmación de contraseña
        const passwordConfirmation = document.getElementById('password_confirmation');
        passwordConfirmation.addEventListener('input', function() {
            if (this.value !== passwordInput.value && this.value !== '') {
                this.style.borderColor = '#e74c3c';
            } else {
                this.style.borderColor = '';
            }
        });
    </script>
</body>
</html>
