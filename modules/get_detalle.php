<?php
// modules/get_detalle.php
include '../config/db_local.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    
    // Consultamos la solicitud y unimos con usuarios para traer el nombre del solicitante
    $query = "SELECT s.*, u.nombre 
              FROM solicitudes s 
              JOIN usuarios u ON s.id_usuario = u.id_usuario 
              WHERE s.id_solicitud = '$id'";
              
    $resultado = mysqli_query($conexion, $query);
    
    if ($row = mysqli_fetch_assoc($resultado)) {
        echo json_encode($row); // Esto es lo que JavaScript recibe y procesa
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró la solicitud']);
    }
}
?>