<?php
/**
 * MÓDULO DE REGISTRO DE USUARIOS (ADMIN) - VERSIÓN FINAL
 */
include '../config/db_local.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recepción y Saneamiento (Asegura que estos coincidan con el 'name' de tu modal)
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula'] ?? '');
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo_electronico'] ?? '');
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? ''); // ¡Añadido!
    $carrera   = mysqli_real_escape_string($conexion, $_POST['carrera_area'] ?? '');
    $perfil    = mysqli_real_escape_string($conexion, $_POST['perfil'] ?? 'usuario');

    // 2. VERIFICACIÓN DE DUPLICADOS
    $checkSql = "SELECT matricula FROM usuarios WHERE matricula = '$matricula'";
    $checkRes = mysqli_query($conexion, $checkSql);

    if (mysqli_num_rows($checkRes) > 0) {
        header("Location: ../admin_usuarios.php?status=error_duplicate&matricula=$matricula");
        exit();
    }

    // 3. Encriptación
    $password_plana = $_POST['password'] ?? '123456'; // Password por defecto si viene vacío
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

    // 4. Inserción con todos los campos (Incluyendo teléfono)
    $sql = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area) 
            VALUES ('$matricula', '$nombre', '$correo', '$telefono', '$password_hash', '$perfil', '$carrera')";

    if (mysqli_query($conexion, $sql)) {
        // ÉXITO: Redirige de vuelta (Esto evita que el mensaje se repita al refrescar)
        header("Location: ../admin_usuarios.php?status=created");
    } else {
        header("Location: ../admin_usuarios.php?status=db_error");
    }
    exit();
}
?>