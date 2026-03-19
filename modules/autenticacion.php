<?php
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = trim(mysqli_real_escape_string($conexion, $_POST['matricula']));
    $password_ingresado = trim($_POST['password']);

    $query = "SELECT id_usuario, nombre, perfil, password FROM usuarios WHERE matricula='$matricula'";
    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        
        // Verifica si la contraseña coincide (usando hash para seguridad)
        if (password_verify($password_ingresado, $usuario['password'])) {

            // 1. Generar un Token de sesión único para LocalStorage
            $token_acceso = bin2hex(random_bytes(32));

            // 2. Guardar datos básicos en la sesión de PHP (Respaldo del servidor)
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['perfil'] = strtolower($usuario['perfil']); 

            // 3. Redirección enviando el Token en la URL
            $url_destino = ($_SESSION['perfil'] == 'administrador' || $_SESSION['perfil'] == 'subdirector') 
                           ? "../panel_admin.php" 
                           : "../panel_usuario.php";
            
            // Enviamos el token como parámetro para que el JS lo guarde
            header("Location: " . $url_destino . "?token=" . $token_acceso);
            exit();

        } else {
            header("Location: ../login.php?error=auth");
            exit();
        }
    } else {
        header("Location: ../login.php?error=auth");
        exit();
    }
}
?>