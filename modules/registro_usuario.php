<?php
/**
 * MÓDULO DE AUTO-REGISTRO (PÚBLICO)
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Permite a los alumnos crear su propia cuenta. Por defecto asigna el perfil 'Usuario'.
 */
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpieza de datos básicos
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $carrera = mysqli_real_escape_string($conexion, $_POST['carrera']);
    
    // Cifrado de contraseña para protección de datos personales
    $password_plano = $_POST['password'];
    $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

    // Perfil asignado por defecto para registros externos
    $perfil = 'Usuario';

    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area) 
    VALUES ('$matricula', '$nombre', '$correo', '$password_hash', '$telefono', '$perfil', '$carrera')";

    if (mysqli_query($conexion, $query)) {
        // Alerta de éxito y redirección al login
        echo "<script>alert('Registro exitoso. Ya puedes iniciar sesión.'); window.location='../index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>