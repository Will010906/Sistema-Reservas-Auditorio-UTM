<?php
/**
 * CONSULTA DE DISPONIBILIDAD - SIRA UTM
 * Retorna un JSON con los bloques de tiempo ocupados.
 */
include("../config/db_local.php");

$id_auditorio = isset($_GET['id']) ? mysqli_real_escape_string($conexion, $_GET['id']) : null;
$fecha = isset($_GET['fecha']) ? mysqli_real_escape_string($conexion, $_GET['fecha']) : null;

$ocupados = [];

if ($id_auditorio && $fecha) {
    // Solo consultamos solicitudes Aceptadas o Pendientes (para evitar traslapes)
    $sql = "SELECT hora_inicio, hora_fin FROM solicitudes 
            WHERE id_auditorio = '$id_auditorio' 
            AND fecha_evento = '$fecha' 
            AND estado != 'Rechazada'";
            
    $res = mysqli_query($conexion, $sql);
    
    while ($fila = mysqli_fetch_assoc($res)) {
        $ocupados[] = [
            'inicio' => $fila['hora_inicio'],
            'fin'    => $fila['hora_fin']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($ocupados);