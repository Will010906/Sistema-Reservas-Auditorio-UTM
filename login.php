<?php
/**
 * VISTA DE LOGIN - SIRA UTM
 * Actualizado con: Spinner de carga, Efectos de Focus y Footer Institucional.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Login UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sira-purple-deep: #5B3D66;
            --sira-purple-med: #845C93;
            --sira-bg-main: #EBEFF2;
            --sira-card-white: #F4F7F9;
            --sira-silver: #BDC3C7; 
            --sira-black: #2D2D2D;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at center, #ffffff 0%, var(--sira-bg-main) 100%);
            height: 100vh;
            display: flex;
            flex-direction: column; /* Para acomodar el footer abajo */
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--sira-black);
            overflow: hidden;
        }

        .btn-back-home {
            position: fixed;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: var(--sira-purple-deep);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            z-index: 1000;
            background: white;
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid transparent;
        }

        .btn-back-home:hover {
            color: var(--sira-purple-med);
            transform: translateX(-5px);
            border-color: var(--sira-silver); 
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 30px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(91, 61, 102, 0.15);
            border: none;
            padding: 3.5rem !important;
            border-top: 6px solid var(--sira-silver); 
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .form-label-custom {
            font-weight: 700;
            color: var(--sira-purple-deep);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-sira {
            background-color: var(--sira-purple-deep);
            color: white;
            border: none;
            padding: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-sira:hover:not(:disabled) {
            background-color: var(--sira-purple-med);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91, 61, 102, 0.2);
        }

        .btn-sira:disabled {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .form-control {
            background-color: var(--sira-card-white);
            border: 2px solid transparent;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--sira-silver); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transform: scale(1.02); /* Efecto de escala suave */
        }

        .input-group-text {
            background-color: var(--sira-card-white);
            border: 2px solid transparent;
            color: var(--sira-purple-med);
            border-radius: 12px 0 0 12px;
            transition: all 0.3s ease;
        }

        .input-group:focus-within .input-group-text {
            border-color: var(--sira-silver); 
            background-color: #fff;
        }

        .custom-link {
            color: var(--sira-purple-med);
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 600;
        }

        .custom-link:hover { color: var(--sira-purple-deep); }

        footer {
            margin-top: 2rem;
            text-align: center;
            color: #7f8c8d;
            font-size: 0.8rem;
            line-height: 1.5;
        }
    </style>
</head>

<body>

    <a href="index.php" class="btn-back-home">
        <i class="bi bi-house-door-fill"></i>
        <span>Inicio</span>
    </a>

    <div class="container d-flex flex-column align-items-center">
        <div class="card login-card text-center">
            <div class="card-body p-0">
                <img src="assets/img/logo_app_web_RA.png" alt="Logo SIRA UTM" class="logo-img img-fluid" style="max-width: 160px; margin-bottom: 25px;">

                <div class="mb-4">
                    <h4 class="fw-bold" style="color: var(--sira-purple-deep); letter-spacing: -1px;">BIENVENIDO A SIRA</h4>
                    <p class="text-muted small">Gestión de Auditorios UTM</p>
                </div>

                <form id="loginForm" novalidate>
                    <div class="mb-4 text-start">
                        <label for="matricula" class="form-label form-label-custom">Matrícula / ID Trabajador</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="matricula" id="matricula"
                                class="form-control form-control-lg border-start-0 ps-0"
                                placeholder="Tu matrícula" required>
                        </div>
                    </div>

                    <div class="mb-4 text-start">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="password" class="form-label form-label-custom mb-0">Contraseña</label>
                            <a href="recuperar.php" class="custom-link" style="font-size: 0.75rem;">¿La olvidaste?</a>
                        </div>
                        <div class="input-group position-relative sira-password-toggle">
                            <span class="input-group-text border-end-0"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password"
                                class="form-control form-control-lg border-start-0 ps-0"
                                placeholder="••••••••" required 
                                style="padding-right: 45px;">
                            
                            <button type="button" id="togglePassword" 
                                class="btn position-absolute top-50 translate-middle-y end-0 me-1 p-1 text-muted" 
                                style="border: none; background: none; z-index: 10;">
                                <i class="bi bi-eye-slash fs-5" id="iconoOjo"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="btnEntrar" class="btn btn-sira btn-lg w-100 fw-bold rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                        <span id="btnText">ACCEDER AL SISTEMA</span>
                        <div id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></div>
                    </button>
                </form>

                <div class="mt-5 text-center pt-3 border-top" style="border-color: rgba(91, 61, 102, 0.1) !important;">
                    <p class="text-muted small mb-0">¿Eres nuevo en la plataforma? <br>
                        <a href="registro.php" class="fw-bold custom-link">Crea tu cuenta aquí</a>
                    </p>
                </div>
            </div>
        </div>

        <footer>
            &copy; <?php echo date('Y'); ?> Universidad Tecnológica de Morelia.<br>
            Desarrollado para la Gestión Eficiente de Auditorios.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="assets/js/contrasena_toggle.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function() {
                if (this.checkValidity()) {
                    $('#btnText').text('CARGANDO...');
                    $('#btnSpinner').removeClass('d-none');
                    $('#btnEntrar').prop('disabled', true);
                }
            });
        });
    </script>
    <script src="assets/js/login.js"></script>
</body>

</html>