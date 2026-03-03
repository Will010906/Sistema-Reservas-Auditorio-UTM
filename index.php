<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Reservación de Auditorios UTM</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="login-container">
        <h2>SISTEMA DE AUDITORIOS</h2>
        <p>Inicia sesión con tu matrícula</p>

        <form action="modules/autenticacion.php" method="POST">
            
            <label for="matricula">Matrícula</label>
            <input type="text" name="matricula" id="matricula" placeholder="Ingresa tu matrícula" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="********" required>

            <button type="submit">ENTRAR</button>

            <div style="margin-top: 15px; font-size: 0.8em;">
                <a href="#" style="color: var(--secondary-color); text-decoration: none;">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>

    <script src="assets/js/main.js"></script>
    
</body>
</html>