<?php
include '../../config/db_local.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id > 0) {
    // Apagamos la notificación (volvemos a 0) y aseguramos el estado Aceptada
    $query = "UPDATE solicitudes SET notificacion_admin = 0, estado = 'Aceptada' WHERE id_solicitud = $id";
    
    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
}
exit;