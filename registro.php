<?php
/**
 * VISTA: REGISTRO DE USUARIOS - SIRA UTM
 * Actualizado: Diseño Pro, Aviso Docente y Estética Unificada.
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Registro de Alumnos</title>
    <?php include 'includes/head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { --sira-purple: #5B3D66; }
        body { background-color: #f8f9fa; }
        .card { border-radius: 20px; }
        .sira-password-toggle .btn:focus { box-shadow: none !important; outline: none !important; }
        .sira-password-toggle .btn:hover { color: var(--sira-purple) !important; }
        .form-control:focus, .form-select:focus { border-color: var(--sira-purple); box-shadow: 0 0 0 0.25rem rgba(91, 61, 102, 0.25); }
        /* Estilo para la alerta institucional */
        .alert-utm { background-color: rgba(91, 61, 102, 0.05); border-left: 4px solid var(--sira-purple); color: #444; }
    </style>
</head>

<body class="d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 p-4">
                    <div class="text-center mb-4">
                        <img src="assets/img/logo_app_web_RA.png" style="max-width: 70px;" alt="UTM Logo">
                        <h4 class="fw-bold mt-3">Crea tu cuenta</h4>
                        <p class="text-muted small">Portal de Reservas UTM</p>
                    </div>

                    <div class="alert alert-utm shadow-sm mb-4">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2 mt-1" style="color: var(--sira-purple);"></i>
                            <div style="font-size: 0.85rem; line-height: 1.4;">
                                <strong>Registro exclusivo para estudiantes.</strong><br>
                                Si es docente, por favor envíe un correo para su registro o acuda con el encargado administrativo para su alta.
                            </div>
                        </div>
                    </div>

                    <form id="registroForm" novalidate>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Andrea Urueta" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Matrícula</label>
                            <input type="text" name="matricula" id="reg_matricula" class="form-control" placeholder="UTMXXXXXXTI" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" placeholder="tu@correo.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Teléfono / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted border-end-0">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <input type="tel" name="telefono" id="reg_tel" class="form-control border-start-0"
                                    placeholder="4431234567" maxlength="12" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Carrera</label>
                            <select name="carrera" class="form-select" required>
                                <option value="" selected disabled>Selecciona tu carrera...</option>
                                <option value="Enfermería">Enfermería</option>
                                <option value="Electromovilidad">Electromovilidad</option>
                                <option value="Asesor Financiero">Asesor Financiero</option>
                                <option value="Tecnologías de la Información e Innovación Digital">Tecnologías de la Información e Innovación Digital</option>
                                <option value="Mecatrónica">Mecatrónica</option>
                                <option value="Mantenimiento Industrial">Mantenimiento Industrial</option>
                                <option value="Gastronomía">Gastronomía</option>
                                <option value="Energía y Desarrollo Sostenible">Energía y Desarrollo Sostenible</option>
                                <option value="Diseño Textil y Moda">Diseño Textil y Moda</option>
                                <option value="Biotecnología">Biotecnología</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Contraseña</label>
                            <div class="position-relative sira-password-toggle">
                                <input type="password" name="password" id="reg_pass" class="form-control" 
                                       placeholder="••••••••" required style="padding-right: 45px;">
                                <button type="button" id="togglePassword" 
                                        class="btn position-absolute top-50 translate-middle-y end-0 me-1 p-1 text-muted" 
                                        style="border: none; background: none; z-index: 10;">
                                    <i class="bi bi-eye-slash fs-5" id="iconoOjo"></i>
                                </button>
                            </div>
                            <div class="form-text" style="font-size: 0.7rem;">Mín. 8 caracteres, letras y números.</div>
                        </div>

                        <button type="submit" id="btnRegistro" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mt-2" style="background-color: var(--sira-purple); border: none;">
                            REGISTRARME
                        </button>

                        <div class="text-center mt-4">
    <p class="small text-muted mb-0">
        ¿Ya tienes cuenta? 
        <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--sira-purple);">
            Inicia sesión
        </a>
    </p>
</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="assets/js/contrasena_toggle.js"></script>
    <script src="assets/js/registro.js"></script>
</body>

</html>