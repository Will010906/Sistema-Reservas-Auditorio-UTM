<?php
/**
 * ACTUALIZACIÓN DE ESTADO DE SOLICITUDES
 * Descripción: Recibe datos mediante Fetch API (JSON) para cambiar el estatus de una reservación.
 */
include '../config/db_local.php';

// Captura del flujo de datos JSON proveniente del frontend (JavaScript)
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (isset($input['id']) && isset($input['estado'])) {
    // Escaneo de variables para prevenir ataques
    $id = mysqli_real_escape_string($conexion, $input['id']);
    $estado = mysqli_real_escape_string($conexion, $input['estado']);
    $comentario = mysqli_real_escape_string($conexion, $input['comentario']);

    /**
     * Ejecución de la actualización:
     * Cambia el valor de la columna 'estado' para la solicitud específica.
     */
    $sql = "UPDATE solicitudes SET estado = '$estado' WHERE id_solicitud = '$id'";

    if (mysqli_query($conexion, $sql)) {
        // Respuesta exitosa en formato JSON para el JavaScript
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($conexion)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
}
?>