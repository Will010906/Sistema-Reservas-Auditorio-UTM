<?php
include '../config/db_local.php';

$id_auditorio = $_GET['id'];
$fecha = $_GET['fecha'];

// Solo contamos las que ya fueron ACEPTADAS por el Admin (Wilmer)
$query = "SELECT hora_inicio, hora_fin FROM solicitudes 
          WHERE id_auditorio = '$id_auditorio' 
          AND fecha_evento = '$fecha' 
          AND estado = 'Aceptada'";

$res = mysqli_query($conexion, $query);
$ocupados = [];

while($row = mysqli_fetch_assoc($res)) {
    $ocupados[] = [
        'inicio' => $row['hora_inicio'],
        'fin' => $row['hora_fin']
    ];
}

echo json_encode($ocupados);