<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Auditorios UTM</title>
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body>
    <div class="login-container">
        <h2>SISTEMA DE AUDITORIOS</h2>
        <form id="loginForm" action="modules/autenticacion.php" method="POST">
            <label for="matricula">Matrícula</label>
            <input type="text" name="matricula" id="matricula" required> <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" id="btnEntrar">ENTRAR</button>
        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnEntrar');
            btn.innerHTML = "Cargando..."; // Cambia el texto dinámicamente
            btn.style.backgroundColor = "#6c757d"; //
        });
    </script>
</body>
</html>