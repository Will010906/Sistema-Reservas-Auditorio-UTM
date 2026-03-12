<?php
include '../config/db_local.php';
header('Content-Type: application/json');

// Recibimos los datos en formato JSON
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['estado'])) {
    $id = mysqli_real_escape_string($conexion, $data['id']);
    $nuevoEstado = mysqli_real_escape_string($conexion, $data['estado']);

    // Actualizamos la columna 'estado' con los valores del ENUM
    $query = "UPDATE solicitudes SET estado = '$nuevoEstado' WHERE id_solicitud = '$id'";
    
    if (mysqli_query($conexion, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
}
?>