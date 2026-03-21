<?php
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matricula = trim(mysqli_real_escape_string($conexion, $_POST['matricula']));
    $password_ingresado = trim($_POST['password']);

    // Agregamos 'carrera_area' a la consulta para poder filtrarla después
    $query = "SELECT id_usuario, nombre, perfil, carrera_area, password FROM usuarios WHERE matricula='$matricula'";
    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        
        if (password_verify($password_ingresado, $usuario['password'])) {

            $token_acceso = bin2hex(random_bytes(32));

            // 1. Guardar datos en la sesión
            $_SESSION['id_usuario']   = $usuario['id_usuario'];
            $_SESSION['nombre']       = $usuario['nombre'];
            $_SESSION['perfil']       = strtolower($usuario['perfil']); 
            $_SESSION['carrera_area'] = $usuario['carrera_area']; // CRÍTICO para el subdirector

            // 2. Lógica de redirección específica por perfil
            if ($_SESSION['perfil'] == 'administrador') {
                $url_destino = "../panel_admin.php";
            } else if ($_SESSION['perfil'] == 'subdirector') {
                $url_destino = "../panel_subdirector.php"; // <--- Ahora sí tiene su propia ruta
            } else {
                $url_destino = "../panel_usuario.php";
            }
            
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