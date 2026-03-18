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
            /* Nueva Paleta SIRA */
            --sira-purple-deep: #5B3D66;   /* Morado Oscuro */
            --sira-purple-med: #845C93;    /* Morado Medio */
            --sira-bg-main: #EBEFF2;       /* Gris azulado claro (Fondo) */
            --sira-card-white: #F4F7F9;    /* Blanco Azulado */
            --sira-gold: #E5C38E;          /* Crema / Dorado (Acentos) */
            --sira-black: #2D2D2D;         /* Texto Principal */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sira-bg-main); 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--sira-black);
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 30px; 
            max-width: 480px; 
            width: 90%;
            /* Sombra suave con el tono morado de la paleta */
            box-shadow: 0 20px 50px rgba(91, 61, 102, 0.1); 
            border: none;
            padding: 3rem !important; 
            /* Detalle superior en dorado */
            border-top: 6px solid var(--sira-gold);
        }

        .form-label-custom {
            font-weight: 700;
            color: var(--sira-purple-deep); 
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Botón con el morado profundo de la nueva paleta */
        .btn-sira {
            background-color: var(--sira-purple-deep); 
            color: white;
            border: none;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .btn-sira:hover {
            background-color: var(--sira-purple-med); 
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(91, 61, 102, 0.2);
            color: white;
        }

        .btn-sira:active {
            transform: translateY(0);
        }

        .logo-img {
            max-width: 220px; 
            margin-bottom: 30px;
            border-radius: 15px;
        }

        .form-control {
            background-color: var(--sira-card-white);
            border: 1px solid rgba(91, 61, 102, 0.1);
        }

        .form-control:focus {
            border-color: var(--sira-purple-soft);
            box-shadow: 0 0 0 0.25rem rgba(132, 92, 147, 0.15);
            background-color: #fff;
        }

        .input-group-text {
            background-color: var(--sira-card-white);
            border: 1px solid rgba(91, 61, 102, 0.1);
            color: var(--sira-purple-med);
        }

        .custom-link {
            color: var(--sira-purple-med);
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .custom-link:hover {
            color: var(--sira-purple-deep);
            text-decoration: underline !important;
        }

        .form-check-input:checked {
            background-color: var(--sira-gold);
            border-color: var(--sira-gold);
        }

        .border-top-custom {
            border-top: 1px solid rgba(91, 61, 102, 0.1) !important;
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center">
        <div class="card login-card text-center">
            <div class="card-body p-0">
                
                <img src="assets/img/logo_app_web_RA.png" alt="Logo SIRA UTM" class="logo-img img-fluid">

                <div class="mb-5">
                    <h5 class="fw-bold mt-2" style="color: var(--sira-purple-deep); letter-spacing: -0.5px;">BIENVENIDO A SIRA</h5>
                    <p class="text-muted small">Gestión de Auditorios UTM</p>
                </div>

                <form id="loginForm" action="modules/autenticacion.php" method="POST">
                    
                    <div class="mb-4 text-start">
                        <label for="matricula" class="form-label form-label-custom">Matrícula / Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="matricula" id="matricula" class="form-control form-control-lg border-start-0 ps-0" placeholder="Tu matrícula" required>
                        </div>
                    </div>

                    <div class="mb-4 text-start">
                        <label for="password" class="form-label form-label-custom">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" class="form-control form-control-lg border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check text-start">
                            <input class="form-check-input" type="checkbox" value="" id="recordarSesion">
                            <label class="form-check-label text-muted small" for="recordarSesion">
                                Recordar
                            </label>
                        </div>
                        <a href="#" class="text-decoration-none fw-bold custom-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" id="btnEntrar" class="btn btn-sira btn-lg w-100 fw-bold rounded-pill">
                        ACCEDER AL SISTEMA
                    </button>
                    
                </form>

                <div class="mt-5 text-center border-top-custom pt-3">
                    <p class="text-muted small mb-0">¿Eres nuevo en la plataforma? <br> 
                        <a href="registro_usuario.php" class="text-decoration-none fw-bold custom-link">Crea tu cuenta aquí</a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnEntrar');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Validando...'; 
            btn.style.opacity = '0.8';
            btn.disabled = true;
        });
    </script>
</body>
</html>