<?php
include '../config/db_local.php'; 

// 1. Identificamos al usuario que ya existe
$matricula = '2026002'; 

// 2. Definimos los nuevos valores
$nombre = 'Andy';
$correo = 'andy.alumno@utm.mx';
$password_hash = password_hash('654321', PASSWORD_DEFAULT);
$rol = 'alumno'; // Asegúrate de que este valor esté en tu ENUM de MySQL

// 3. Cambiamos el INSERT por un UPDATE
// Usamos SET para asignar los nuevos valores y WHERE para no afectar a todos los usuarios
$sql = "UPDATE usuarios 
        SET nombre = '$nombre', 
            correo_electronico = '$correo', 
            password = '$password_hash', 
            perfil = '$rol' 
        WHERE matricula = '$matricula'";

if(mysqli_query($conexion, $sql)) {
    echo "¡Usuario actualizado con éxito! El perfil ahora es: <strong>$rol</strong>";
} else {
    echo "Error al actualizar: " . mysqli_error($conexion);
}
?>