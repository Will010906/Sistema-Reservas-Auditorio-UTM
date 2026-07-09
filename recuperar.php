<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRA - Recuperar Acceso</title>
    <?php include 'includes/head.php'; ?>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        :root { --sira-purple: #5B3D66; }
        body { background-color: #EBEFF2; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 0; }
        .card-recuperar { background: white; border-radius: 20px; max-width: 450px; width: 90%; padding: 3rem; border-top: 6px solid #E5C38E; }

        @media (max-width: 480px) {
            .card-recuperar { padding: 2rem 1.5rem; border-radius: 18px; }
        }
    </style>
</head>
<body>
    <div class="card-recuperar shadow-lg text-center">
        <img src="assets/img/logo_app_web_RA.png" style="max-width: 70px;" class="mb-3">
        <h4 class="fw-bold mb-2">Recuperar Acceso</h4>
        <p class="text-muted small mb-4">Ingresa tu correo y te enviaremos las instrucciones para restablecer tu contraseña.</p>
        
        <form id="formRecuperar">
            <div class="mb-4 text-start">
                <label class="form-label small fw-bold text-uppercase">Correo Electrónico</label>
                <input type="email" id="email_recuperar" class="form-control form-control-lg rounded-3" placeholder="tu@correo.com" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2" style="background-color: var(--sira-purple); border:none;">
                ENVIAR ENLACE
            </button>
            <div class="mt-4">
                <a href="login.php" class="small text-muted text-decoration-none">← Volver al login</a>
            </div>
        </form>
    </div>

    <script src="assets/vendor/jquery/jquery-3.7.0.min.js"></script>
    <script src="assets/vendor/sweetalert2/sweetalert2.min.js"></script>
    <script src="assets/js/recuperar.js"></script>
</body>
</html>