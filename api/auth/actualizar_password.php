<?php
// 1. Configuración de cabeceras y conexión
header('Content-Type: application/json');
include("../../config/db_local.php");

// 2. Obtener y decodificar datos
$data = json_decode(file_get_contents("php://input"), true);

// Validación de seguridad: Verificar que los datos existan
if (!isset($data['token']) || !isset($data['password'])) {
    echo json_encode(["success" => false, "error" => "Datos incompletos para procesar la solicitud."]);
    exit();
}

$token = $data['token'];
// Encriptamos la contraseña de forma segura
$nueva_pass = password_hash($data['password'], PASSWORD_BCRYPT);

try {
    // 3. Verificar si el token es válido y no ha expirado
    // NOW() usa la hora del servidor de la base de datos
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE reset_token = ? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // 4. Token válido -> Actualizar password y LIMPIAR TOKEN
        $update = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, token_expira = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $nueva_pass, $token);
        
        if ($update->execute()) {
            echo json_encode(["success" => true, "message" => "¡Contraseña actualizada! Ya puedes iniciar sesión."]);
        } else {
            echo json_encode(["success" => false, "error" => "Error interno al actualizar la base de datos."]);
        }
    } else {
        // El token no existe, ya se usó o la fecha ya pasó
        echo json_encode(["success" => false, "error" => "El enlace es inválido o ha expirado. Por seguridad, solicita uno nuevo."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Error en el servidor: " . $e->getMessage()]);
}

$conexion->close();