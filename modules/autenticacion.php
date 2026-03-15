<?php
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Limpiamos solo la matrícula (el password no se limpia igual para no alterar caracteres especiales)
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $password_ingresado = $_POST['password'];

    // 2. Buscamos al usuario solo por su matrícula
    $query = "SELECT id_usuario, nombre, perfil, password FROM usuarios WHERE matricula='$matricula'";
    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        
        // 3. Verificamos la contraseña usando la función segura de PHP
        if (password_verify($password_ingresado, $usuario['password'])) {
            
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['perfil'] = $usuario['perfil'];

            if ($usuario['perfil'] == 'Administrador') {
                header("Location: ../panel_admin.php");
                exit();
            } else {
                header("Location: ../panel_usuario.php");
                exit();
            }
        } else {
            // Contraseña incorrecta
            echo "<script>alert('Matrícula o contraseña incorrecta'); window.location='../index.php';</script>";
        }
    } else {
        // Usuario no encontrado
        echo "<script>alert('Matrícula o contraseña incorrecta'); window.location='../index.php';</script>";
    }
}