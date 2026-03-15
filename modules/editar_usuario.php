<?php
/**
 * MÓDULO DE EDICIÓN DE USUARIOS
 * Proyecto: Sistema de Reservación de Auditorios - UTM
 * Descripción: Permite modificar los datos personales y permisos de los usuarios registrados.
 */
session_start();
include '../config/db_local.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Saneamiento riguroso de entradas para prevenir inyecciones SQL
    $id = mysqli_real_escape_string($conexion, $_POST['id_usuario']);
    $matricula = mysqli_real_escape_string($conexion, $_POST['matricula']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo_electronico']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $perfil = mysqli_real_escape_string($conexion, $_POST['perfil']); 
    $carrera = mysqli_real_escape_string($conexion, $_POST['carrera_area']);

    /**
     * Sentencia SQL UPDATE:
     * Mantiene la integridad de los datos respetando el esquema de la tabla 'usuarios'.
     */
    $sql = "UPDATE usuarios SET 
            matricula = '$matricula',
            nombre = '$nombre', 
            correo_electronico = '$correo',
            telefono = '$telefono',
            perfil = '$perfil', 
            carrera_area = '$carrera' 
            WHERE id_usuario = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // Redirección exitosa hacia la gestión de usuarios
        header("Location: ../admin_usuarios.php?status=updated");
    } else {
        // Reporte de error en caso de fallo en la consulta
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>