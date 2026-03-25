<?php
header('Content-Type: application/json');
include '../../config/db_local.php';

// Verificamos la conexión
if (!$conexion) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Nota: En tu modal usas la tabla 'auditorio', asegúrate que el nombre sea exacto
$query = "SELECT id_auditorio, nombre_espacio, capacidad_maxima, ubicacion, equipamiento_fijo FROM auditorio WHERE disponibilidad = 1";
$res = mysqli_query($conexion, $query);

$datos = [];
while ($row = mysqli_fetch_assoc($res)) {
    $datos[] = $row;
}

echo json_encode($datos);
exit;