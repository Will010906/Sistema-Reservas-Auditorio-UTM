<?php
/**
 * API: REPORTE DE CIERRE E INCIDENTES - SIRA UTM
 * Registra cómo se entrega el espacio al finalizar el evento.
 */
header('Content-Type: application/json');
require_once '../../config/db_local.php';

$headers = apache_request_headers();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
    exit;
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['id']) || !isset($data['incidentes'])) {
        throw new Exception("Datos incompletos para el reporte.");
    }

    $id_sol = (int)$data['id'];
    $incidentes = mysqli_real_escape_string($conexion, $data['incidentes']);

    // 1. Obtener ID de usuario del Token para seguridad
    $jwt = $matches[1];
    $payload = json_decode(base64_decode(explode('.', $jwt)[1]), true);
    $id_user_token = $payload['id'];

    // 2. Actualizar la solicitud: Guardamos el reporte y la fecha de cierre
    // Solo permitimos cerrar si la solicitud pertenece al usuario logueado
    $sql = "UPDATE solicitudes 
            SET incidentes_cierre = '$incidentes', 
                fecha_cierre = NOW() 
            WHERE id_solicitud = $id_sol 
            AND id_usuario = '$id_user_token'";

    if (mysqli_query($conexion, $sql)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(["success" => true, "message" => "Reporte de cierre guardado."]);
        } else {
            throw new Exception("No se encontró la solicitud o no tienes permiso.");
        }
    } else {
        throw new Exception(mysqli_error($conexion));
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
exit;