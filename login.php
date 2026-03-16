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
        /* Paleta Extraída Directamente del Logo SIRA */
        :root {
            --sira-purple-dark: #714B75; /* Púrpura oscuro del texto SIRA */
            --sira-purple-hover: #5A3A5D; /* Tono más oscuro para el hover del botón */
            --sira-lilac-light: #F8F4FA; /* Fondo lila muy tenue y elegante */
            --sira-text-main: #3D2C40; /* Casi negro con un toque púrpura para los títulos */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sira-lilac-light); 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 25px; 
            max-width: 520px; 
            width: 90%;
            box-shadow: 0 15px 40px rgba(113, 75, 117, 0.15); /* Sombra teñida con el color del logo */
            border: none;
            padding: 2.5rem !important; 
        }

        .form-label-custom {
            font-weight: 700;
            color: var(--sira-purple-dark); 
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-sira {
            background-color: var(--sira-purple-dark); 
            color: white;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-sira:hover {
            background-color: var(--sira-purple-hover); 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(113, 75, 117, 0.3);
            color: white;
        }

        .btn-sira:active {
            transform: translateY(0);
        }

        .logo-img {
            max-width: 250px; 
            margin-bottom: 25px;
        }

        .form-control:focus {
            border-color: var(--sira-purple-dark);
            box-shadow: 0 0 0 0.25rem rgba(113, 75, 117, 0.25);
        }

        .input-group-text i {
            color: var(--sira-purple-dark); 
            opacity: 0.8;
        }

        .custom-link {
            color: var(--sira-purple-dark);
            transition: color 0.3s ease;
        }

        .custom-link:hover {
            color: var(--sira-purple-hover);
            text-decoration: underline !important;
        }

        .form-check-input:checked {
            background-color: var(--sira-purple-dark);
            border-color: var(--sira-purple-dark);
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="card login-card text-center">
            <div class="card-body p-0">
                
                <img src="assets/img/logo_app_web_RA.png" alt="Logo SIRA UTM" class="logo-img img-fluid">

                <div class="mb-4">
                    <h5 class="fw-bold mt-2" style="color: var(--sira-text-main);">SITIO DE RESERVACIÓN DE AUDITORIOS</h5>
                    <p class="text-muted small">Universidad Tecnológica de Morelia</p>
                </div>

                <form id="loginForm" action="modules/autenticacion.php" method="POST">
                    
                    <div class="mb-4 text-start">
                        <label for="matricula" class="form-label form-label-custom">Matrícula</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" name="matricula" id="matricula" class="form-control form-control-lg border-start-0 ps-0" placeholder="Ingresa tu matrícula" required>
                        </div>
                    </div>

                    <div class="mb-3 text-start">
                        <label for="password" class="form-label form-label-custom">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control form-control-lg border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check text-start">
                            <input class="form-check-input" type="checkbox" value="" id="recordarSesion">
                            <label class="form-check-label text-muted small" for="recordarSesion">
                                Recordar sesión
                            </label>
                        </div>
                        <a href="#" class="text-decoration-none small fw-bold custom-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" id="btnEntrar" class="btn btn-sira btn-lg w-100 fw-bold rounded-pill">
                        INICIAR SESIÓN
                    </button>
                    
                </form>

                <div class="mt-4 text-center border-top pt-3">
                    <p class="text-muted small mb-0">¿No tienes cuenta? <a href="registro_usuario.php" class="text-decoration-none fw-bold custom-link">Regístrate aquí</a></p>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnEntrar');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validando...'; 
            btn.classList.replace('btn-sira', 'btn-secondary'); 
        });
    </script>

</body>
</html>