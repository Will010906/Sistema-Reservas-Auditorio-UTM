<?php
/**
 * MÓDULO DE AUTO-REGISTRO (PÚBLICO) - PROTEGIDO
 */
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Saneamiento de datos con limpieza de nulos (??)
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula'] ?? '');
    $nombre    = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $correo    = mysqli_real_escape_string($conexion, $_POST['correo'] ?? '');
    $telefono  = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
    $carrera   = mysqli_real_escape_string($conexion, $_POST['carrera'] ?? '');
    
    // 2. VERIFICACIÓN DE MATRÍCULA EXISTENTE
    $sql_check = "SELECT matricula FROM usuarios WHERE matricula = '$matricula'";
    $res_check = mysqli_query($conexion, $sql_check);

    if (mysqli_num_rows($res_check) > 0) {
        // Si ya existe, avisamos sin romper el flujo
        echo "<script>
            alert('Error: La matrícula $matricula ya se encuentra registrada.');
            window.history.back();
        </script>";
        exit();
    }

    // 3. Cifrado de contraseña
    $password_plano = $_POST['password'];
    $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);
    $perfil = 'Usuario';

    // 4. Inserción segura
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area) 
              VALUES ('$matricula', '$nombre', '$correo', '$password_hash', '$telefono', '$perfil', '$carrera')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Registro exitoso. Ya puedes iniciar sesión.'); window.location='../index.php';</script>";
    } else {
        // Error genérico de base de datos
        echo "<script>alert('Error interno en el servidor. Inténtalo más tarde.'); window.history.back();</script>";
    }
}
?>