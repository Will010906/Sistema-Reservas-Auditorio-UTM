<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>SIRA - Registro de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/login_style.css">
</head>

<body class="bg-light d-flex align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg border-0 rounded-4 p-4">
                    <div class="text-center mb-4">
                        <img src="assets/img/logo_app_web_RA.png" style="max-width: 70px;">
                        <h4 class="fw-bold mt-3">Crea tu cuenta</h4>
                        <p class="text-muted small">Portal de Reservas UTM</p>
                    </div>

                    <form id="registroForm" action="modules/procesar_registro.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Andrea Urueta" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Matrícula</label>
                            <input type="text" name="matricula" id="reg_matricula" class="form-control" placeholder="UTMXXXXXXTI" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Correo Institucional</label>
                            <input type="email" name="correo" class="form-control" placeholder="usuario@utm.mx" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Teléfono / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" name="telefono" id="reg_tel" class="form-control border-start-0"
                                    placeholder="4431234567" maxlength="10" required>
                            </div>
                            <div class="form-text" style="font-size: 0.7rem;">10 dígitos sin espacios.</div>
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
                            <input type="password" name="password" id="reg_pass" class="form-control" placeholder="••••••••" required>
                            <div class="form-text" style="font-size: 0.7rem;">Mín. 8 caracteres, números y un símbolo.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mt-2" style="background-color: #5B3D66; border: none;">
                            REGISTRARME
                        </button>

                        <div class="text-center mt-3">
                            <a href="login.php" class="small text-decoration-none text-muted">¿Ya tienes cuenta? Inicia sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/registro.js"></script>
</body>

</html>