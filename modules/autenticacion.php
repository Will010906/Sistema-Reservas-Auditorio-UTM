<?php
include '../config/db_local.php'; // Usa la conexión que definimos en db_local.php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos del formulario de login
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);

    // Consulta para verificar el usuario y su contraseña
    $query = "SELECT id_usuario, nombre, perfil FROM usuarios WHERE matricula = '$matricula' AND password = '$password'";
    $resultado = mysqli_query($conexion, $query);
    // Verificamos si se encontró un usuario con esa matrícula
    if ($usuario = mysqli_fetch_assoc($resultado)) {
        // Validación de sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['perfil'] = $usuario['perfil'];

        // Redirección según el perfil (Admin o Alumno/Docente)
        if ($usuario['perfil'] == 'Administrador') {
            header("Location: ../panel_admin.php");
        } else {
            header("Location: ../panel_usuario.php");
        }
    } else {
        echo "<script>alert('Matrícula o contraseña incorrecta'); window.location='../index.php';</script>";
    }
}
