<?php
/**
 * INTERFAZ DE INICIO DE SESIÓN (LOGIN)
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Formulario para la entrada de usuarios mediante matrícula y contraseña.
 * Envía los datos al módulo 'autenticacion.php' para su validación.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Auditorios UTM</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body>
    <div class="login-container">
        <h2>SISTEMA DE AUDITORIOS</h2>
        
        <form id="loginForm" action="modules/autenticacion.php" method="POST">
            <label for="matricula">Matrícula</label>
            <input type="text" name="matricula" id="matricula" placeholder="Ingresa tu matrícula" required> 
            
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>

            <button type="submit" id="btnEntrar">ENTRAR</button>
        </form>
    </div>

    <script>
        /**
         * Lógica de Feedback Visual en el Login
         * Al enviar el formulario, el botón cambia su estado para indicar que el sistema está procesando.
         */
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnEntrar');
            btn.innerHTML = "Cargando..."; // Cambia el texto para evitar clics repetidos
            btn.style.backgroundColor = "#6c757d"; // Cambia a un color neutro (gris) durante la carga
        });
    </script>
</body>
</html>