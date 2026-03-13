<?php
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);

    $query = "SELECT id_usuario, nombre, perfil FROM usuarios 
              WHERE matricula='$matricula' AND password='$password'";

    $resultado = mysqli_query($conexion, $query);

    if ($usuario = mysqli_fetch_assoc($resultado)) {

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
        echo "<script>alert('Matrícula o contraseña incorrecta'); window.location='../index.php';</script>";
    }
}
