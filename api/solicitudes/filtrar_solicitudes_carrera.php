<?php
error_reporting(0); 
include("../../config/db_local.php");
header('Content-Type: application/json');

$area = isset($_GET['area']) ? mysqli_real_escape_string($conexion, $_GET['area']) : '';

if (empty($area)) {
    echo json_encode([]);
    exit;
}

// Usamos LIKE para que si el nombre varía un poco, igual traiga los datos
$query = "SELECT s.id_solicitud, s.folio, s.titulo_event, s.fecha_evento, s.estado, u.nombre 
          FROM solicitudes s 
          INNER JOIN usuarios u ON s.id_usuario = u.id_usuario 
          WHERE u.carrera_area LIKE '%$area%' 
          ORDER BY s.id_solicitud DESC";

$resultado = mysqli_query($conexion, $query);
$solicitudes = [];

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $solicitudes[] = $fila;
    }
}
echo json_encode($solicitudes);