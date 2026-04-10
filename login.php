<?php
/**
 * SIRA - SISTEMA INTEGRAL DE RESERVA DE AUDITORIOS
 * * MÓDULO: PORTAL DE AUTENTICACIÓN CENTRAL (LOGIN)
 * * @package     Frontend_Security
 * @subpackage  Authentication_View
 * @version     3.5.0
 * @copyright   2026 Universidad Tecnológica de Morelia
 * * DESCRIPCIÓN TÉCNICA:
 * Implementa la interfaz de acceso restringido mediante validación de credenciales.
 * Gestiona estados asíncronos para el envío de datos al backend y proporciona
 * una experiencia de usuario (UX) reactiva mediante feedback visual dinámico.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Login UTM</title>
    <?php include 'includes/head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="assets/css/login.css">
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