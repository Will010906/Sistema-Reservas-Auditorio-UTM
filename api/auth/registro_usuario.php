<?php
/**
 * ENDPOINT API: AUTO-REGISTRO PÚBLICO - SIRA UTM
 * Implementa: Recepción JSON, Verificación de Duplicados y Hash de Seguridad.
 */
header('Content-Type: application/json');
include '../config/db_local.php';

// 1. LEER DATOS JSON (Desde el Fetch del Formulario de Registro)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    
    // Saneamiento de datos (Prevención de Inyección SQL)
    $matricula = mysqli_real_escape_string($conexion, $data['matricula'] ?? '');
    $nombre    = mysqli_real_escape_string($conexion, $data['nombre'] ?? '');
    $correo    = mysqli_real_escape_string($conexion, $data['correo'] ?? '');
    $telefono  = mysqli_real_escape_string($conexion, $data['telefono'] ?? '');
    $carrera   = mysqli_real_escape_string($conexion, $data['carrera'] ?? '');
    
    // 2. VERIFICACIÓN DE MATRÍCULA (Seguridad de Datos)
    $sql_check = "SELECT matricula FROM usuarios WHERE matricula = '$matricula'";
    $res_check = mysqli_query($conexion, $sql_check);

    if (mysqli_num_rows($res_check) > 0) {
        echo json_encode([
            "success" => false, 
            "error" => "La matrícula '$matricula' ya se encuentra registrada en el sistema."
        ]);
        exit();
    }

    // 3. CIFRADO DE CONTRASEÑA (Estándar BCRYPT)
    $password_plano = $data['password'];
    $password_hash = password_hash($password_plano, PASSWORD_DEFAULT);
    $perfil = 'Usuario'; // Perfil asignado por defecto al registrarse solo

    // 4. INSERCIÓN SEGURA (Estatus activo por defecto)
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, password, telefono, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$password_hash', '$telefono', '$perfil', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        // Respuesta Éxito (Requisito de la maestra)
        echo json_encode([
            "success" => true,
            "message" => "¡Registro exitoso! Bienvenido al SIRA.",
            "redirect" => "index.php?status=registered"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "error" => "Error técnico en el servidor: " . mysqli_error($conexion)
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Petición inválida o datos vacíos."]);
}

exit;