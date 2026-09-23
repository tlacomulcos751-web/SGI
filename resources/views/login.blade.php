<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inicio de Sesión - Nodo Group</title>
    
    <!-- Google Fonts: Inter para máxima legibilidad -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Iconos y Animaciones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <link rel="icon" type="image/jpeg" href="{{ asset('logos/nodo.jpeg') }}">

    <style>
        :root {
            /* Colores Base (Modo Claro) */
            --primary-color: #0f172a;
            --primary-hover: #1e293b;
            --accent-color: #3b82f6;
            --bg-color: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --input-bg: #f8fafc;
        }

        [data-theme="dark"] {
            /* Colores Base (Modo Oscuro) */
            --primary-color: #ffffff;
            --primary-hover: #f1f5f9;
            --accent-color: #3b82f6;
            --bg-color: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --input-bg: #1e293b;
        }

        /* MAGIA PARA EL LOGO JPEG EN TODAS PARTES */
        img[src*="nodo.jpeg"] {
            mix-blend-mode: multiply;
            transition: filter 0.3s ease;
        }

        [data-theme="dark"] img[src*="nodo.jpeg"] {
            filter: invert(1) brightness(1.5);
            mix-blend-mode: screen;
        }
        
        .logo-color-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-color: var(--primary-color);
            mix-blend-mode: lighten;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        [data-theme="dark"] .logo-color-overlay {
            opacity: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Layout Dividido (Split Screen) */
        .split-layout {
            display: flex;
            width: 100vw;
            min-height: 100vh;
        }

        /* Lado Izquierdo: Branding */
        .split-left {
            flex: 1;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--input-bg) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: var(--text-main);
            position: relative;
            overflow: hidden;
            border-right: 1px solid var(--border-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Decoración abstracta sutil en el lado oscuro */
        .split-left::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(59,130,246,0.10) 0%, transparent 60%);
            border-radius: 50%;
        }
        
        .split-left::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -10%;
            width: 40vw;
            height: 40vw;
            background: radial-gradient(circle, rgba(16,185,129,0.05) 0%, transparent 60%);
            border-radius: 50%;
        }

        .brand-content {
            text-align: center;
            z-index: 2;
            max-width: 480px;
        }

        .brand-logo-wrapper {
            display: inline-block;
            margin-bottom: 32px;
            position: relative;
        }

        .brand-logo {
            height: 80px;
            width: auto;
            display: block;
        }

        .brand-title {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            color: #94a3b8;
            font-weight: 400;
            line-height: 1.6;
        }

        /* Lado Derecho: Formulario */
        .split-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            position: relative;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 16px;
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            background-color: var(--bg-color);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
        }

        .toggle-password:hover {
            color: var(--text-main);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: var(--bg-color);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        [data-theme="dark"] .btn-submit {
            color: #0f172a;
        }

        /* Alerta de Error */
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            color: #ef4444;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Botón Modo Oscuro */
        .theme-toggle-wrapper {
            position: absolute;
            top: 32px;
            right: 32px;
            z-index: 10;
        }

        .theme-toggle {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.1rem;
        }

        .theme-toggle:hover {
            background: var(--border-color);
            transform: rotate(15deg);
        }

        /* Responsividad: Ocultar panel izquierdo en móviles */
        @media (max-width: 991px) {
            .split-left {
                display: none;
            }
            .split-right {
                padding: 24px;
            }
            
            .mobile-logo {
                display: block !important;
                margin-bottom: 32px;
            }
            .mobile-logo img {
                height: 56px;
                border-radius: 12px;
            }
        }

        .mobile-logo {
            display: none;
            position: relative;
        }
    </style>
</head>
<body>

    <div class="split-layout">
        <!-- Lado Izquierdo: Branding Visual -->
        <div class="split-left">
            <div class="brand-content animate__animated animate__fadeIn">
                <div class="brand-logo-wrapper">
                    <img src="{{ asset('logos/nodo.jpeg') }}" class="brand-logo" alt="Logo Nodo Group">
                    <div class="logo-color-overlay"></div>
                </div>
                <h1 class="brand-title">Sistema de Gestión de Inventario</h1>
                <p class="brand-subtitle">Plataforma integral para el control de activos fijos, consumibles y vehículos de la empresa.</p>
            </div>
        </div>

        <!-- Lado Derecho: Formulario de Autenticación -->
        <div class="split-right">
            <!-- Botón Tema -->
            <div class="theme-toggle-wrapper">
                <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>

            <div class="form-container animate__animated animate__fadeInRight">
                <!-- Logo solo visible en móvil -->
                <div class="mobile-logo">
                    <img src="{{ asset('logos/nodo.jpeg') }}" alt="Logo Nodo">
                    <div class="logo-color-overlay"></div>
                </div>

                <div class="login-header">
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus credenciales para acceder a tu cuenta.</p>
                </div>

                @if(session('error'))
                <div class="alert-error animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle mt-1"></i>
                    <div>{{ session('error') }}</div>
                </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Correo electrónico</label>
                        <input type="email" name="email" id="email" class="form-control-custom" placeholder="ejemplo@nodo.com" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control-custom" placeholder="••••••••" required>
                            <button type="button" class="toggle-password" onclick="togglePassword()" tabindex="-1" title="Mostrar/Ocultar contraseña">
                                <i id="toggleIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Ingresar al sistema <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script aislado para el login (sin dependencias de helpers externos) -->
    <script>
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const btn = document.querySelector('.theme-toggle i');
            if (btn) {
                btn.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        document.addEventListener('DOMContentLoaded', initTheme);

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>