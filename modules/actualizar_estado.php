<?php
ob_start();
header('Content-Type: application/json');
include("../config/db_local.php");

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $id = intval($data['id']);
    $estado = mysqli_real_escape_string($conexion, $data['estado']);
    $motivo = mysqli_real_escape_string($conexion, $data['comentario']);

    // Intentamos la actualización
    $query = "UPDATE solicitudes SET estado = '$estado', observaciones_admin = '$motivo' WHERE id_solicitud = $id";
    
    if (mysqli_query($conexion, $query)) {
        // Si sale bien, buscamos datos para WhatsApp
        $sqlInfo = "SELECT s.titulo_event, u.telefono, u.nombre 
                    FROM solicitudes s 
                    JOIN usuarios u ON s.id_usuario = u.id_usuario 
                    WHERE s.id_solicitud = $id";
        $info = mysqli_query($conexion, $sqlInfo);
        $row = mysqli_fetch_assoc($info);

        $respuesta = [
            "success" => true,
            "telefono" => $row['telefono'] ?? '',
            "evento" => $row['titulo_event'] ?? '',
            "usuario" => $row['nombre'] ?? ''
        ];
    } else {
        // Si el SQL falla (como pasó ahora), mandamos el error en formato JSON
        $respuesta = ["success" => false, "error" => "Error SQL: " . mysqli_error($conexion)];
    }
} else {
    $respuesta = ["success" => false, "error" => "No se recibieron datos"];
}

ob_end_clean();
echo json_encode($respuesta);
exit;