<?php
include '../config/db_local.php'; // Ajusta la ruta a tu conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpieza de datos básicos
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $carrera = mysqli_real_escape_string($conexion, $_POST['carrera']);
    
    // Encriptación de la contraseña
    $password_plano = $_POST['password'];
    $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

    // Por defecto, los registros nuevos son perfil 'Usuario'
    $perfil = 'Usuario';

    // Insertar en la base de datos
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area) 
    VALUES ('$matricula', '$nombre', '$correo', '$password_hash', '$telefono', '$perfil', '$carrera')";

    if (mysqli_query($conexion, $query)) {
        echo "<script>alert('Registro exitoso. Ya puedes iniciar sesión.'); window.location='../index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}
?>