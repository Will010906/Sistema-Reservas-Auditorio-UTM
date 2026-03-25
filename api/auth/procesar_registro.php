<?php
/**
 * ENDPOINT API: REGISTRO DE NUEVOS ALUMNOS - SIRA UTM
 * Implementa: Recepción JSON, Hash de Seguridad y Respuesta Estándar.
 */
header('Content-Type: application/json');
include("../config/db_local.php");

// 1. LEER DATOS JSON (Desde el Fetch del Frontend)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $data) {
    
    // Saneamiento y Formateo (Pensamiento TSU)
    $nombre = mysqli_real_escape_string($conexion, $data['nombre']);
    $matricula = strtoupper(trim(mysqli_real_escape_string($conexion, $data['matricula'])));
    $correo = mysqli_real_escape_string($conexion, $data['correo']);
    $carrera = mysqli_real_escape_string($conexion, $data['carrera']);
    $pass_plana = $data['password'];
    
    // LIMPIEZA DE TELÉFONO: Solo números
    $telefono = preg_replace('/\D/', '', $data['telefono']); 
    $telefono = mysqli_real_escape_string($conexion, $telefono);

    // 2. VERIFICACIÓN DE DUPLICADOS (Seguridad 30%)
    $checkQuery = "SELECT id_usuario FROM usuarios WHERE matricula = '$matricula'";
    $resCheck = mysqli_query($conexion, $checkQuery);

    if (mysqli_num_rows($resCheck) > 0) {
        echo json_encode([
            "success" => false, 
            "error" => "La matrícula '$matricula' ya se encuentra registrada."
        ]);
        exit();
    }

    // 3. ENCRIPTACIÓN (Regla de Oro de Seguridad)
    $pass_hash = password_hash($pass_plana, PASSWORD_DEFAULT);

    // 4. INSERCIÓN CON ESTATUS ACTIVO (1)
    $query = "INSERT INTO usuarios (matricula, nombre, correo_electronico, telefono, password, perfil, carrera_area, estatus) 
              VALUES ('$matricula', '$nombre', '$correo', '$telefono', '$pass_hash', 'alumno', '$carrera', 1)";

    if (mysqli_query($conexion, $query)) {
        // Respuesta exitosa en JSON (Requisito de la maestra)
        echo json_encode([
            "success" => true,
            "message" => "¡Registro exitoso! Ya puedes iniciar sesión.",
            "redirect" => "index.php?status=reg_success"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false, 
            "error" => "Error interno en la base de datos: " . mysqli_error($conexion)
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Petición inválida o datos incompletos."]);
}

exit;