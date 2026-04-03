<?php
// SEGURIDAD: Si no hay token en la URL, mandarlo al login
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: index.php?error=sin_token");
    exit();
}
$token_url = $_GET['token'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SIRA - Nueva Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sira-purple: #5B3D66; --sira-gold: #E5C38E; }
        body { background-color: #EBEFF2; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; }
        .card-reset { background: white; border-radius: 30px; max-width: 450px; width: 90%; padding: 3rem; border-top: 6px solid var(--sira-gold); }
        .form-control { background-color: #F4F7F9; border: 1px solid rgba(91, 61, 102, 0.1); border-radius: 12px; }
    </style>
</head>
<body>
    <div class="card-reset shadow-lg text-center">
        <img src="assets/img/logo_app_web_RA.png" style="max-width: 70px;" class="mb-3">
        <h4 class="fw-bold mb-3">Nueva Contraseña</h4>
        <p class="text-muted small mb-4">Ingresa tu nueva clave de acceso para SIRA UTM.</p>
        
        <form id="formNuevaPass">
            <input type="hidden" id="token_reset" value="<?php echo htmlspecialchars($token_url); ?>">

            <div class="mb-3 text-start">
                <label class="form-label small fw-bold text-muted">NUEVA CONTRASEÑA</label>
                <div class="input-group position-relative sira-password-toggle">
                    <input type="password" id="pass1" class="form-control form-control-lg" placeholder="••••••••" required style="padding-right: 45px;">
                    <button type="button" id="togglePassword" class="btn position-absolute top-50 translate-middle-y end-0 me-1 p-1 text-muted" style="border: none; background: none; z-index: 10;">
                        <i class="bi bi-eye-slash fs-5" id="iconoOjo"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label small fw-bold text-muted">CONFIRMAR CONTRASEÑA</label>
                <input type="password" id="pass2" class="form-control form-control-lg" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm" style="background-color: var(--sira-purple); border:none;">
                ACTUALIZAR CONTRASEÑA
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/contrasena_toggle.js"></script> 
    <script src="assets/js/restablecer.js"></script>
</body>
</html>