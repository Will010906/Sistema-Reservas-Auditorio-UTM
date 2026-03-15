<?php
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // trim() elimina espacios accidentales al inicio o final
    $matricula = trim(mysqli_real_escape_string($conexion, $_POST['matricula']));
    $password_ingresado = trim($_POST['password']);

    $query = "SELECT id_usuario, nombre, perfil, password FROM usuarios WHERE matricula='$matricula'";
    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {
        // password_verify compara el texto plano con el hash de la BD
        if (password_verify($password_ingresado, $usuario['password'])) {

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['perfil'] = $usuario['perfil'];

            if ($usuario['perfil'] == 'administrador' || $usuario['perfil'] == 'subdirector') {
                header("Location: ../panel_admin.php");
            } else {
                header("Location: ../panel_usuario.php");
            }
            exit();
        } else {
            echo "<script>alert('La contraseña no coincide con el registro.'); window.location='../index.php';</script>";
        }
    } else {
        echo "<script>alert('La matrícula $matricula no existe.'); window.location='../index.php';</script>";
    }
}
