<?php
// 1. Evitar que errores de texto ensucien el JSON
error_reporting(0); 
header('Content-Type: application/json');

// 2. Conexión a la base de datos
include '../../config/db_local.php';
// 3. Leer datos del JS
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    
    $matricula = mysqli_real_escape_string($conexion, $data['matricula']);
    $nombre    = mysqli_real_escape_string($conexion, $data['nombre']);
    $correo    = mysqli_real_escape_string($conexion, $data['correo']);
    $telefono  = mysqli_real_escape_string($conexion, $data['telefono']);
    $carrera   = mysqli_real_escape_string($conexion, $data['carrera']);
    $password  = password_hash($data['password'], PASSWORD_DEFAULT);
    $perfil    = 'alumno'; // Valor de tu ENUM

    // 4. EL INSERT (Usando correo_electronico del diagrama)
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$password', '$telefono', '$perfil', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        echo json_encode(["success" => true]);
    } else {
        // Si falla, mandamos el error en formato JSON para que el JS no se rompa
        echo json_encode(["success" => false, "error" => mysqli_error($conexion)]);
    }
}
exit;