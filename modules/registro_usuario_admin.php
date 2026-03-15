<?php
/**
 * MÓDULO DE REGISTRO DE USUARIOS (ADMIN)
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Permite al administrador crear nuevas cuentas asignando roles específicos.
 */
include '../config/db_local.php'; // Conexión a la base de datos local

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Saneamiento de entradas para prevenir Inyección SQL
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo_electronico']);
    $carrera = mysqli_real_escape_string($conexion, $_POST['carrera_area']);
    $perfil = mysqli_real_escape_string($conexion, $_POST['perfil']);
    
    // Encriptación de seguridad mediante BCRYPT
    $password_plana = $_POST['password'];
    $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

    // Inserción en la tabla 'usuarios'
    $sql = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, perfil, carrera_area) 
            VALUES ('$matricula', '$nombre', '$correo', '$password_hash', '$perfil', '$carrera')";

    if (mysqli_query($conexion, $sql)) {
        // Redirección con confirmación de estado
        header("Location: ../admin_usuarios.php?status=created");
    } else {
        echo "Error al insertar: " . mysqli_error($conexion);
    }
}
?>